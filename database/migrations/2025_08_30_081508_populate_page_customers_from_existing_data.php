<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate page_customers table from existing customer data
        DB::transaction(function () {
            $customers = DB::table('customers')
                ->whereNotNull('facebook_user_id')
                ->get();

            foreach ($customers as $customer) {
                $profileData = json_decode($customer->profile_data ?? '{}', true);
                $sourcePageId = $profileData['source_page_id'] ?? null;

                if ($sourcePageId) {
                    // Find the facebook_page record
                    $facebookPage = DB::table('facebook_pages')
                        ->where('page_id', $sourcePageId)
                        ->where('client_id', $customer->client_id)
                        ->first();

                    if ($facebookPage) {
                        // Create page_customer record if it doesn't exist
                        DB::table('page_customers')->insertOrIgnore([
                            'facebook_page_id' => $facebookPage->id,
                            'customer_id' => $customer->id,
                            'facebook_user_id' => $customer->facebook_user_id,
                            'first_interaction' => $customer->first_interaction ?? $customer->created_at,
                            'last_interaction' => $customer->last_interaction ?? $customer->updated_at,
                            'interaction_count' => $customer->interaction_count ?? 1,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the populated data
        DB::table('page_customers')->truncate();
    }
};
