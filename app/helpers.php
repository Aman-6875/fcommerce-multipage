<?php

use App\Helpers\SettingsHelper;

if (!function_exists('setting')) {
    /**
     * Get a system setting value
     */
    function setting($key, $default = null)
    {
        return SettingsHelper::get($key, $default);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name from database settings or config
     */
    function app_name()
    {
        return SettingsHelper::getAppName();
    }
}

if (!function_exists('company_info')) {
    /**
     * Get company information
     */
    function company_info($key = null)
    {
        $info = [
            'address' => SettingsHelper::getCompanyAddress(),
            'phone' => SettingsHelper::getCompanyPhone(),
        ];

        return $key ? ($info[$key] ?? null) : $info;
    }
}

if (!function_exists('facebook_app_id')) {
    /**
     * Get Facebook App ID from database settings
     */
    function facebook_app_id()
    {
        return SettingsHelper::getFacebookAppId();
    }
}

if (!function_exists('facebook_app_secret')) {
    /**
     * Get Facebook App Secret from database settings
     */
    function facebook_app_secret()
    {
        return SettingsHelper::getFacebookAppSecret();
    }
}

if (!function_exists('facebook_webhook_token')) {
    /**
     * Get Facebook Webhook Verify Token from database settings
     */
    function facebook_webhook_token()
    {
        return SettingsHelper::getFacebookWebhookVerifyToken();
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol from database settings
     */
    function currency_symbol()
    {
        return SettingsHelper::getCurrencySymbol();
    }
}

if (!function_exists('currency_code')) {
    /**
     * Get currency code from database settings
     */
    function currency_code()
    {
        return SettingsHelper::getCurrencyCode();
    }
}