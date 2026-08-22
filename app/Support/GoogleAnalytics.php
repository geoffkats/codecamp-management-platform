<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Resolves Google Analytics / GTM / Ads IDs from System Settings (admin UI),
 * falling back to config/services.php (.env) only when a setting has never been saved.
 */
class GoogleAnalytics
{
    public static function gtmId(): ?string
    {
        return static::resolve('google_gtm_id', 'services.google.gtm_id');
    }

    public static function ga4MeasurementId(): ?string
    {
        return static::resolve('google_ga4_measurement_id', 'services.google.ga4_measurement_id');
    }

    public static function adsConversionId(): ?string
    {
        return static::resolve('google_ads_conversion_id', 'services.google.ads_conversion_id');
    }

    public static function adsConversionLabel(): ?string
    {
        return static::resolve('google_ads_conversion_label', 'services.google.ads_conversion_label');
    }

    /**
     * Whether GTM should be loaded (preferred over direct GA4).
     */
    public static function usesGtm(): bool
    {
        return filled(static::gtmId());
    }

    /**
     * Whether direct gtag.js should be loaded (only when GTM is not configured).
     */
    public static function usesDirectGa4(): bool
    {
        return ! static::usesGtm() && filled(static::ga4MeasurementId());
    }

    private static function resolve(string $settingKey, string $configKey): ?string
    {
        $all = SystemSetting::getAllSettings();

        if (array_key_exists($settingKey, $all)) {
            $stored = trim((string) $all[$settingKey]);

            return $stored !== '' ? $stored : null;
        }

        $configured = config($configKey);

        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return null;
    }
}
