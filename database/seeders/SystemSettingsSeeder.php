<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'Facebook Automation System',
                'description' => 'Application name',
                'is_public' => true,
            ],
            [
                'key' => 'default_language',
                'value' => 'bn',
                'description' => 'Default system language',
                'is_public' => true,
            ],
            [
                'key' => 'free_trial_days',
                'value' => 10,
                'description' => 'Number of free trial days',
                'is_public' => false,
            ],
            [
                'key' => 'free_subscribers_limit',
                'value' => 20,
                'description' => 'Free plan subscriber limit',
                'is_public' => false,
            ],
            [
                'key' => 'free_messages_limit',
                'value' => 50,
                'description' => 'Free plan message limit',
                'is_public' => false,
            ],
            [
                'key' => 'premium_price_monthly',
                'value' => 29,
                'description' => 'Premium plan monthly price in USD',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\SystemSetting::create($setting);
        }
    }
}
