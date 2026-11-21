<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class MobileConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $setting = Setting::query()->find(1);
        $now = now();

        $textEn = trim((string) ($setting?->promo_text_en ?? ''));
        $textAr = trim((string) ($setting?->promo_text_ar ?? ''));

        $startsAt = $setting?->promo_starts_at;
        $endsAt = $setting?->promo_ends_at;

        $withinSchedule = (! $startsAt || $startsAt->lessThanOrEqualTo($now))
            && (! $endsAt || $endsAt->greaterThanOrEqualTo($now));

        $promo = null;

        if (
            (bool) ($setting?->promo_enabled ?? false)
            && ($textEn !== '' || $textAr !== '')
            && $withinSchedule
        ) {
            $animation = $setting->promo_animation ?? 'static';

            $promo = [
                'enabled' => true,
                'text' => [
                    'en' => $textEn !== '' ? $textEn : null,
                    'ar' => $textAr !== '' ? $textAr : null,
                ],
                'url' => $this->safeHttpsUrl($setting->promo_url),
                'background_color' => $this->safeHexColor(
                    $setting->promo_bg_color,
                    '#FF5517'
                ),
                'text_color' => $this->safeHexColor(
                    $setting->promo_text_color,
                    '#FFFFFF'
                ),
                'animation' => in_array($animation, ['marquee', 'fade', 'static'], true)
                    ? $animation
                    : 'static',
                'duration_seconds' => max(
                    5,
                    min(120, (int) ($setting->promo_duration ?? 20))
                ),
                'dismissible' => (bool) ($setting->promo_dismissible ?? true),
                'starts_at' => $startsAt?->toIso8601String(),
                'ends_at' => $endsAt?->toIso8601String(),
            ];
        }

        return response()->json([
            'data' => [
                'brand' => [
                    'name' => 'murwaj',
                    'name_ar' => 'مُروَّج.كم',
                    'logo_url' => $this->logoUrl($setting?->logo),
                ],
                'promo' => $promo,
            ],
            'meta' => [
                'schema_version' => 1,
                'generated_at' => $now->toIso8601String(),
            ],
        ]);
    }

    private function logoUrl(?string $filename): ?string
    {
        $filename = trim((string) $filename);

        if (
            $filename === ''
            || $filename === '.'
            || $filename === '..'
            || $filename !== basename($filename)
            || str_contains($filename, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $filename)
        ) {
            return null;
        }

        return asset('storage/setting-suggest/' . rawurlencode($filename));
    }

    private function safeHttpsUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);

        if (
            ! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return null;
        }

        return $url;
    }

    private function safeHexColor(?string $color, string $fallback): string
    {
        $color = trim((string) $color);

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color)
            ? strtoupper($color)
            : $fallback;
    }
}
