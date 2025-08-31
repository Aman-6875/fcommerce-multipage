<?php

namespace App\Helpers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * Get a system setting value with caching
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = SystemSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Get app name from database or fallback to config
     */
    public static function getAppName()
    {
        return self::get('app_name', config('app.name', 'FCommerce'));
    }

    /**
     * Get app description
     */
    public static function getAppDescription()
    {
        return self::get('app_description', 'Facebook Commerce Automation Platform');
    }

    /**
     * Get admin email
     */
    public static function getAdminEmail()
    {
        return self::get('admin_email', 'admin@example.com');
    }

    /**
     * Get company information
     */
    public static function getCompanyAddress()
    {
        return self::get('company_address', '');
    }

    public static function getCompanyPhone()
    {
        return self::get('company_phone', '');
    }

    /**
     * Get Facebook app settings
     */
    public static function getFacebookAppId()
    {
        return self::get('facebook_app_id', config('services.facebook.app_id'));
    }

    public static function getFacebookAppSecret()
    {
        return self::get('facebook_app_secret', config('services.facebook.app_secret'));
    }

    public static function getFacebookWebhookVerifyToken()
    {
        return self::get('facebook_webhook_verify_token', config('services.facebook.webhook_verify_token'));
    }

    /**
     * Get payment settings
     */
    public static function getPaymentCurrency()
    {
        return self::get('payment_currency', 'BDT');
    }

    /**
     * Get currency settings
     */
    public static function getCurrencyCode()
    {
        return self::get('currency_code', 'BDT');
    }

    public static function getCurrencySymbol()
    {
        return self::get('currency_symbol', '৳');
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        $keys = SystemSetting::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("system_setting_{$key}");
        }
    }

    /**
     * Get all settings as array
     */
    public static function all()
    {
        return Cache::remember('all_system_settings', 3600, function () {
            return SystemSetting::pluck('value', 'key')->toArray();
        });
    }
}