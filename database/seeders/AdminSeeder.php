<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@facebookautomation.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'permissions' => [
                'manage_users',
                'manage_clients',
                'manage_orders',
                'manage_services',
                'view_reports',
                'manage_settings',
                'system_admin'
            ],
            'is_active' => true,
        ]);

        \App\Models\Admin::create([
            'name' => 'Manager',
            'email' => 'manager@facebookautomation.com', 
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'permissions' => [
                'manage_clients',
                'manage_orders',
                'manage_services',
                'view_reports'
            ],
            'is_active' => true,
        ]);

        \App\Models\Admin::create([
            'name' => 'Admin User',
            'email' => 'user@facebookautomation.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'permissions' => [
                'manage_clients',
                'manage_orders',
                'view_reports'
            ],
            'is_active' => true,
        ]);
    }
}
