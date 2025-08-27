<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = Client::first();
        
        if (!$client) {
            $this->command->error('Please create a client first using ClientSeeder');
            return;
        }

        // Get the first Facebook page for the client
        $facebookPage = $client->facebookPages()->first();
        if (!$facebookPage) {
            $this->command->error('Please create a Facebook page first using ClientSeeder');
            return;
        }

        $products = [
            [
                'client_id' => $client->id,
                'facebook_page_id' => $facebookPage->id,
                'name' => 'iPhone 15 Pro',
                'description' => '128GB, Natural Titanium - Latest iPhone with A17 Pro chip, titanium design, and Action Button.',
                'sku' => 'IP15PRO128',
                'price' => 145000.00,
                'sale_price' => 135000.00,
                'stock_quantity' => 10,
                'image_url' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400',
                'product_link' => 'https://facebook.com/yourpage/posts/iphone15pro',
                'category' => 'Smartphones',
                'tags' => ['iphone', 'apple', 'smartphone', '5g'],
                'is_active' => true,
                'track_stock' => true,
                'weight' => 0.221,
                'specifications' => [
                    'Display' => '6.1-inch Super Retina XDR',
                    'Chip' => 'A17 Pro',
                    'Storage' => '128GB',
                    'Camera' => '48MP Main + 12MP Ultra Wide + 12MP Telephoto',
                    'Color' => 'Natural Titanium'
                ],
                'sort_order' => 1
            ],
            [
                'client_id' => $client->id,
                'facebook_page_id' => $facebookPage->id,
                'name' => 'Samsung Galaxy S24',
                'description' => '256GB, Titanium Gray - Flagship Samsung with Galaxy AI, 200MP camera, and titanium build.',
                'sku' => 'S24256TG',
                'price' => 135000.00,
                'stock_quantity' => 15,
                'image_url' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400',
                'product_link' => 'https://facebook.com/yourpage/posts/galaxys24',
                'category' => 'Smartphones',
                'tags' => ['samsung', 'galaxy', 'android', '5g', 'ai'],
                'is_active' => true,
                'track_stock' => true,
                'weight' => 0.167,
                'specifications' => [
                    'Display' => '6.2-inch Dynamic AMOLED 2X',
                    'Processor' => 'Snapdragon 8 Gen 3',
                    'Storage' => '256GB',
                    'Camera' => '50MP Main + 12MP Ultra Wide + 10MP Telephoto',
                    'Color' => 'Titanium Gray'
                ],
                'sort_order' => 2
            ],
            [
                'client_id' => $client->id,
                'facebook_page_id' => $facebookPage->id,
                'name' => 'AirPods Pro (2nd Gen)',
                'description' => 'Active Noise Cancellation, Transparency mode, and Spatial Audio with MagSafe charging case.',
                'sku' => 'APPRO2MG',
                'price' => 35000.00,
                'stock_quantity' => 25,
                'image_url' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c7b28d?w=400',
                'product_link' => 'https://facebook.com/yourpage/posts/airpods-pro-2',
                'category' => 'Audio',
                'tags' => ['apple', 'airpods', 'wireless', 'earbuds', 'noise-cancelling'],
                'is_active' => true,
                'track_stock' => true,
                'weight' => 0.061,
                'specifications' => [
                    'Type' => 'In-ear wireless earbuds',
                    'Noise Cancellation' => 'Active Noise Cancellation',
                    'Battery Life' => 'Up to 6 hours (ANC on)',
                    'Charging Case' => 'MagSafe wireless charging',
                    'Features' => 'Spatial Audio, Transparency mode'
                ],
                'sort_order' => 4
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Sample products created successfully!');
    }
}
