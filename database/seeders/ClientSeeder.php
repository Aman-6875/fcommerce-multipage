<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a free trial client
        $freeClient = \App\Models\Client::firstOrCreate(
            ['email' => 'ahmed@example.com'],
            [
                'name' => 'Ahmed Rahman',
                'password' => bcrypt('password123'),
                'phone' => '+8801712345678',
                'status' => 'active',
                'plan_type' => 'free',
                'settings' => [
                    'language' => 'bn',
                    'notifications' => true,
                    'email_alerts' => true,
                ],
                'profile_data' => [
                    'business_type' => 'e-commerce',
                    'business_name' => 'Ahmed Electronics',
                    'location' => 'Dhaka, Bangladesh'
                ]
            ]
        );

        // Create a premium client
        $premiumClient = \App\Models\Client::firstOrCreate(
            ['email' => 'fatima@example.com'],
            [
                'name' => 'Fatima Khatun',
                'password' => bcrypt('password123'),
                'phone' => '+8801812345678',
                'status' => 'active',
                'plan_type' => 'premium',
                'subscription_expires_at' => now()->addMonths(1),
                'settings' => [
                    'language' => 'bn',
                    'notifications' => true,
                    'email_alerts' => true,
                ],
                'profile_data' => [
                    'business_type' => 'restaurant',
                    'business_name' => 'Fatima Restaurant',
                    'location' => 'Chittagong, Bangladesh'
                ]
            ]
        );

        // Create sample Facebook pages
        $facebookPage = \App\Models\FacebookPage::firstOrCreate(
            ['page_id' => '107247171625408'],
            [
                'client_id' => $freeClient->id,
                'page_name' => 'Ahmed Electronics',
                'access_token' => 'sample_access_token', // Add a sample access token
                'is_connected' => true,
                'page_data' => [
                    'followers' => 1500,
                    'category' => 'Electronics Store'
                ],
                'last_sync' => now()
            ]
        );

        // Create sample customers (testing limit tracking)
        for ($i = 1; $i <= 15; $i++) {
            \App\Models\Customer::firstOrCreate(
                [
                    'client_id' => $freeClient->id,
                    'facebook_user_id' => "fb_user_{$i}"
                ],
                [
                    'facebook_page_id' => $facebookPage->id, // Link customers to the Facebook page
                    'name' => "Customer {$i}",
                    'phone' => "+88017000000{$i}",
                    'email' => "customer{$i}@example.com",
                    'status' => 'active',
                    'interaction_count' => rand(1, 50),
                    'first_interaction' => now()->subDays(rand(1, 30)),
                    'last_interaction' => now()->subDays(rand(0, 7)),
                ]
            );
        }

        // Create sample orders
        for ($i = 1; $i <= 5; $i++) {
            $orderNumber = "ORD-" . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            // Skip if order already exists
            if (\App\Models\Order::where('order_number', $orderNumber)->exists()) {
                continue;
            }
            
            $quantity = rand(1, 5);
            $unitPrice = rand(500, 5000);
            $subtotal = $unitPrice * $quantity;
            $shippingCharge = 60; // Inside Dhaka shipping
            $totalAmount = $subtotal + $shippingCharge;

            \App\Models\Order::create([
                'client_id' => $freeClient->id,
                'facebook_page_id' => $facebookPage->id, // Add required facebook_page_id
                'customer_id' => $i,
                'facebook_user_id' => "fb_user_{$i}", // Add facebook_user_id
                'order_number' => $orderNumber,
                'product_name' => "Product {$i}",
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'shipping_charge' => $shippingCharge,
                'shipping_zone' => 'inside_dhaka',
                'total_amount' => $totalAmount,
                'customer_info' => [
                    'name' => "Customer {$i}",
                    'phone' => "+88017000000{$i}",
                    'address' => "Address {$i}, Dhaka"
                ],
                'status' => ['pending', 'confirmed', 'delivered'][rand(0, 2)],
                'payment_method' => 'cod'
            ]);
        }

        // Create sample messages (35 out of 50 limit to test limits)
        for ($i = 1; $i <= 35; $i++) {
            \App\Models\CustomerMessage::create([
                'customer_id' => rand(1, 15),
                'client_id' => $freeClient->id,
                'message_type' => $i % 2 == 0 ? 'outgoing' : 'incoming',
                'message_content' => "Sample message {$i}",
                'is_read' => rand(0, 1),
            ]);
        }
    }
}
