<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'facebook'];
    private const MOBILE_CALLBACK = 'murwaj://auth/social';

    private function assertProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
    }

    public function redirect(string $provider)
    {
        $this->assertProvider($provider);
        request()->session()->forget('social_mobile_provider');

        return Socialite::driver($provider)->redirect();
    }

    public function mobileRedirect(string $provider)
    {
        $this->assertProvider($provider);
        request()->session()->put('social_mobile_provider', $provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $this->assertProvider($provider);

        $mobile = request()->session()->pull('social_mobile_provider') === $provider;

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->resolveSocialAccount($provider, $socialUser);
            $this->storeProviderAvatar($user, $provider, $socialUser->getAvatar());
        } catch (Throwable $error) {
            Log::warning('Social authentication could not be completed.', [
                'provider' => $provider,
                'exception_type' => get_class($error),
            ]);

            $reason = in_array($error->getMessage(), [
                'provider_email_missing',
                'account_link_required',
            ], true) ? $error->getMessage() : 'authentication_failed';

            if ($mobile) {
                return redirect()->away(
                    self::MOBILE_CALLBACK . '?error=' . rawurlencode($reason)
                );
            }

            $message = match ($reason) {
                'provider_email_missing' => 'The provider did not return an email address. Try another sign-in method.',
                'account_link_required' => 'This email already has an account. Sign in with that account first before linking a social provider.',
                default => 'Social sign-in could not be completed. Please try again.',
            };

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        if ($mobile) {
            $ticket = Str::random(64);
            $key = 'mobile-social-exchange:' . hash('sha256', $ticket);

            Cache::put($key, $user->getKey(), now()->addSeconds(90));

            return redirect()->away(
                self::MOBILE_CALLBACK . '?ticket=' . rawurlencode($ticket)
            );
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/profile');
    }

    private function resolveSocialAccount(string $provider, object $socialUser): User
    {
        $providerId = trim((string) $socialUser->getId());
        $email = Str::lower(trim((string) $socialUser->getEmail()));

        if ($providerId === '' || $email === '') {
            throw new \RuntimeException('provider_email_missing');
        }

        $raw = $socialUser->getRaw();

        $emailVerified = $provider === 'google'
            && filter_var(
                $raw['email_verified'] ?? $raw['verified_email'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

        return DB::transaction(function () use (
            $provider,
            $providerId,
            $email,
            $emailVerified,
            $socialUser
        ): User {
            $user = User::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->lockForUpdate()
                ->first();

            if (! $user) {
                $user = User::query()
                    ->where('email', $email)
                    ->lockForUpdate()
                    ->first();

                if ($user) {
                    if (
                        ! $emailVerified
                        || ($user->provider && $user->provider !== $provider)
                        || ($user->provider_id && $user->provider_id !== $providerId)
                    ) {
                        throw new \RuntimeException('account_link_required');
                    }

                    $user->forceFill([
                        'provider' => $provider,
                        'provider_id' => $providerId,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ])->save();
                } else {
                    $user = new User([
                        'name' => $socialUser->getName()
                            ?: $socialUser->getNickname()
                            ?: 'User',
                        'email' => $email,
                        'password' => Str::random(64),
                        'provider' => $provider,
                        'provider_id' => $providerId,
                    ]);

                    $user->forceFill([
                        'email_verified_at' => $emailVerified ? now() : null,
                    ])->save();
                }
            }

            if (! $user->hasRole('user')) {
                $user->addRole('user');
            }

            return $user;
        });
    }

    private function storeProviderAvatar(User $user, string $provider, ?string $avatar): void
    {
        if ($user->profile_photo_path || ! is_string($avatar)) {
            return;
        }

        $host = strtolower((string) parse_url($avatar, PHP_URL_HOST));
        $https = strtolower((string) parse_url($avatar, PHP_URL_SCHEME)) === 'https';

        $allowedHost = $provider === 'google'
            ? ($host === 'googleusercontent.com' || str_ends_with($host, '.googleusercontent.com'))
            : ($host === 'platform-lookaside.fbsbx.com' || str_ends_with($host, '.fbcdn.net'));

        if (! $https || ! $allowedHost) {
            return;
        }

        try {
            $response = Http::timeout(8)
                ->withOptions(['allow_redirects' => false])
                ->get($avatar);

            if (! $response->successful() || strlen($response->body()) > 2_000_000) {
                return;
            }

            $filename = 'profile-photos/' . Str::uuid() . '.jpg';

            Storage::disk('public')->put($filename, $response->body());
            $user->forceFill(['profile_photo_path' => $filename])->save();
        } catch (Throwable $error) {
            Log::notice('Social profile photo could not be stored.', [
                'provider' => $provider,
                'exception_type' => get_class($error),
            ]);
        }
    }
}
