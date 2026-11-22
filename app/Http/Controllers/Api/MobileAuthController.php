<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthUserResource;
use App\Models\User;
use App\Services\MobileTokens;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class MobileAuthController extends Controller
{
    private function input(Request $request): array
    {
        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }

        return $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
            'device_name' => ['required', 'string', 'max:80'],
            'code' => ['nullable', 'string', 'regex:/^[0-9]{6}$/D', Rule::prohibitedIf($request->filled('recovery_code'))],
            'recovery_code' => ['nullable', 'string', 'max:100', Rule::prohibitedIf($request->filled('code'))],
        ]);
    }

    public function register(Request $request, CreateNewUser $creator, MobileTokens $tokens)
    {
        abort_unless(Features::enabled(Features::registration()), 404);
        $data = $this->input($request);
        [$user, $token] = DB::transaction(function () use ($request, $data, $creator, $tokens) {
            // Reuse the web account rules and role assignment; never accept client roles/verification flags.
            $user = $creator->create($request->only('name', 'email', 'password', 'password_confirmation', 'terms'));

            return [$user, $tokens->issue($user, $data['device_name'])];
        });
        $sent = $this->sendVerification($user, true);

        return response()->json(['data' => $token + ['user' => (new AuthUserResource($user))->resolve($request),
            'verification_notification_sent' => $sent]], 201);
    }

    public function login(Request $request, MobileTokens $tokens)
    {
        $data = $this->input($request);
        [$user, $token] = DB::transaction(function () use ($data, $tokens) {
            $user = User::where('email', $data['email'])->lockForUpdate()->first();
            if (! $user || ! Hash::check($data['password'], $user->password)) {
                throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']);
            }
            if ($user->hasEnabledTwoFactorAuthentication()) {
                $valid = false;
                if (! empty($data['recovery_code']) && $user->two_factor_recovery_codes) {
                    foreach ($user->recoveryCodes() as $code) {
                        if (hash_equals($code, $data['recovery_code'])) {
                            $user->replaceRecoveryCode($code);
                            $valid = true;
                            break;
                        }
                    }
                } elseif (! empty($data['code'])) {
                    $valid = app(TwoFactorAuthenticationProvider::class)->verify(
                        Fortify::currentEncrypter()->decrypt($user->two_factor_secret), $data['code']);
                }
                if (! $valid) {
                    throw ValidationException::withMessages(['code' => 'Two-factor authentication is required. Supply a valid code or recovery_code.']);
                }
            }
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }

            return [$user, $tokens->issue($user, $data['device_name'])];
        });

        return response()->json(['data' => $token + ['user' => (new AuthUserResource($user))->resolve($request)]]);
    }

    /**
     * Exchange a short-lived, single-use social callback ticket for a mobile
     * Sanctum token. The provider access token is never sent to the app.
     */
    public function socialExchange(Request $request, MobileTokens $tokens)
    {
        $data = $request->validate([
            'ticket' => ['required', 'string', 'size:64', 'regex:/\A[A-Za-z0-9]+\z/D'],
            'device_name' => ['required', 'string', 'max:80'],
        ]);

        $hash = hash('sha256', $data['ticket']);
        $cacheKey = 'mobile-social-exchange:' . $hash;
        $lockKey = 'mobile-social-exchange-lock:' . $hash;

        $userId = Cache::lock($lockKey, 10)->get(
            fn () => Cache::pull($cacheKey)
        );

        if (! $userId) {
            throw ValidationException::withMessages([
                'ticket' => 'The social sign-in ticket is invalid, expired, or already used.',
            ]);
        }

        [$user, $token, $challenge] = DB::transaction(function () use ($userId, $data, $tokens) {
            $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            if ($user->hasEnabledTwoFactorAuthentication()) {
                return [$user, null, Str::random(64)];
            }

            return [$user, $tokens->issue($user, $data['device_name']), null];
        });

        if ($challenge !== null) {
            Cache::put(
                'mobile-social-two-factor:' . hash('sha256', $challenge),
                [
                    'user_id' => $user->getKey(),
                    'device_name' => $data['device_name'],
                ],
                now()->addMinutes(5)
            );

            return response()->json([
                'data' => [
                    'two_factor_required' => true,
                    'challenge_token' => $challenge,
                ],
            ], 202);
        }

        return response()->json([
            'data' => $token + [
                'user' => (new AuthUserResource($user))->resolve($request),
            ],
        ]);
    }

    /**
     * Complete social authentication for accounts protected by two-factor authentication.
     */
    public function socialVerify(Request $request, MobileTokens $tokens)
    {
        $data = $request->validate([
            'challenge_token' => [
                'required',
                'string',
                'size:64',
                'regex:/\A[A-Za-z0-9]+\z/D',
            ],
            'code' => [
                'nullable',
                'string',
                'regex:/^[0-9]{6}$/D',
                Rule::prohibitedIf($request->filled('recovery_code')),
            ],
            'recovery_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::prohibitedIf($request->filled('code')),
            ],
        ]);

        $hash = hash('sha256', $data['challenge_token']);
        $cacheKey = 'mobile-social-two-factor:' . $hash;
        $lockKey = 'mobile-social-two-factor-lock:' . $hash;

        [$user, $token] = Cache::lock($lockKey, 10)->block(3, function () use (
            $cacheKey,
            $data,
            $tokens
        ) {
            $challenge = Cache::get($cacheKey);

            if (
                ! is_array($challenge)
                || empty($challenge['user_id'])
                || empty($challenge['device_name'])
            ) {
                throw ValidationException::withMessages([
                    'challenge_token' => 'The verification challenge is invalid or expired.',
                ]);
            }

            return DB::transaction(function () use ($challenge, $data, $tokens, $cacheKey) {
                $user = User::query()
                    ->whereKey($challenge['user_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($user->hasEnabledTwoFactorAuthentication()) {
                    $valid = false;

                    if (! empty($data['recovery_code']) && $user->two_factor_recovery_codes) {
                        foreach ($user->recoveryCodes() as $code) {
                            if (hash_equals($code, $data['recovery_code'])) {
                                $user->replaceRecoveryCode($code);
                                $valid = true;
                                break;
                            }
                        }
                    } elseif (! empty($data['code'])) {
                        $valid = app(TwoFactorAuthenticationProvider::class)->verify(
                            Fortify::currentEncrypter()->decrypt($user->two_factor_secret),
                            $data['code']
                        );
                    }

                    if (! $valid) {
                        throw ValidationException::withMessages([
                            'code' => 'A valid two-factor authentication or recovery code is required.',
                        ]);
                    }
                }

                // Consume the challenge before issuing the session token.
                Cache::forget($cacheKey);

                return [
                    $user,
                    $tokens->issue($user, $challenge['device_name']),
                ];
            });
        });

        return response()->json([
            'data' => $token + [
                'user' => (new AuthUserResource($user))->resolve($request),
            ],
        ]);
    }

    public function me(Request $request): AuthUserResource
    {
        return new AuthUserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function devices(Request $request, MobileTokens $tokens)
    {
        $rows = $tokens->query($request->user())->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('id')->get()->map(fn ($token) => [
                'id' => $token->id, 'device_name' => Str::after($token->name, 'mobile:'),
                'created_at' => $token->created_at, 'last_used_at' => $token->last_used_at,
                'expires_at' => $token->expires_at, 'current' => $token->id === $request->user()->currentAccessToken()->id,
            ]);

        return response()->json(['data' => $rows]);
    }

    public function revoke(Request $request, string $device, MobileTokens $tokens)
    {
        $tokens->query($request->user())->whereKey($device)->firstOrFail()->delete();

        return response()->noContent();
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }
        $sent = $this->sendVerification($request->user());

        return response()->json(['message' => $sent ? 'Verification email sent. Complete verification on the website.' : 'Verification email is temporarily unavailable.'], $sent ? 202 : 503);
    }

    private function sendVerification(User $user, bool $registered = false): bool
    {
        try {
            if ($registered) {
                event(new Registered($user));
            } else {
                $user->sendEmailVerificationNotification();
            }

            return true;
        } catch (\Throwable $error) {
            Log::warning('Mobile email verification notification failed.', ['exception_type' => get_class($error)]);

            return false;
        }
    }
}
