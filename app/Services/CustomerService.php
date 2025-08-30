<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\FacebookPage;
use App\Models\PageCustomer;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    /**
     * Find or create customer using phone-first strategy
     * This is the master method for customer identification
     */
    public function findOrCreateByPhone(Client $client, string $phone, ?string $name = null, ?FacebookPage $facebookPage = null, ?string $facebookUserId = null): Customer
    {
        DB::beginTransaction();
        
        try {
            // First priority: Find existing customer by phone number
            $existingCustomer = Customer::where('client_id', $client->id)
                ->where('phone', $phone)
                ->first();

            if ($existingCustomer) {
                // Update name if provided and customer has default name
                if ($name && ($existingCustomer->name === 'Facebook User' || empty($existingCustomer->name))) {
                    $existingCustomer->update(['name' => $name]);
                }

                // Create page customer relationship if Facebook page is involved
                if ($facebookPage && $facebookUserId) {
                    $this->ensurePageCustomerRelationship($existingCustomer, $facebookPage, $facebookUserId);
                }

                DB::commit();
                return $existingCustomer;
            }

            // No customer with this phone exists - create new one
            $customer = Customer::create([
                'client_id' => $client->id,
                'name' => $name ?: 'Customer',
                'phone' => $phone,
                'facebook_user_id' => $facebookUserId,
                'status' => 'active',
                'first_interaction' => now(),
                'last_interaction' => now(),
                'interaction_count' => 1,
            ]);

            // Create page customer relationship if Facebook page is involved
            if ($facebookPage && $facebookUserId) {
                $this->ensurePageCustomerRelationship($customer, $facebookPage, $facebookUserId);
            }

            Log::info('New customer created with phone-first strategy', [
                'customer_id' => $customer->id,
                'phone' => $phone,
                'facebook_page_id' => $facebookPage?->id,
                'facebook_user_id' => $facebookUserId
            ]);

            DB::commit();
            return $customer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to find or create customer by phone', [
                'phone' => $phone,
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Merge Facebook customer with phone-based customer
     * This is called when a Facebook user provides their phone number during workflow
     */
    public function mergeWithPhoneCustomer(Customer $facebookCustomer, string $phone, ?string $name = null): Customer
    {
        DB::beginTransaction();
        
        try {
            // Find existing customer with this phone number
            $phoneCustomer = Customer::where('client_id', $facebookCustomer->client_id)
                ->where('phone', $phone)
                ->where('id', '!=', $facebookCustomer->id)
                ->first();

            if (!$phoneCustomer) {
                // No existing customer with this phone - just update the Facebook customer
                $facebookCustomer->update([
                    'phone' => $phone,
                    'name' => $name ?: $facebookCustomer->name
                ]);
                
                DB::commit();
                return $facebookCustomer;
            }

            Log::info('Merging Facebook customer with phone customer', [
                'facebook_customer_id' => $facebookCustomer->id,
                'phone_customer_id' => $phoneCustomer->id,
                'phone' => $phone
            ]);

            // Store facebook_user_id before clearing it
            $facebookUserId = $facebookCustomer->facebook_user_id;
            
            // Clear facebook_user_id from duplicate customer to avoid unique constraint issues during merge
            if ($facebookUserId) {
                $facebookCustomer->update(['facebook_user_id' => null]);
            }

            // Merge the customers - phone customer becomes the master
            $this->mergeCustomers($phoneCustomer, $facebookCustomer, $name, $facebookUserId);

            DB::commit();
            return $phoneCustomer;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to merge Facebook customer with phone customer', [
                'facebook_customer_id' => $facebookCustomer->id,
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Merge two customers - master customer absorbs the duplicate
     */
    private function mergeCustomers(Customer $masterCustomer, Customer $duplicateCustomer, ?string $newName = null, ?string $facebookUserId = null): void
    {
        Log::info('Starting customer merge process', [
            'master_customer_id' => $masterCustomer->id,
            'duplicate_customer_id' => $duplicateCustomer->id
        ]);

        // Update master customer with best available data
        $updates = [];
        
        if ($newName) {
            $updates['name'] = $newName;
        } elseif ($masterCustomer->name === 'Facebook User' || $masterCustomer->name === 'Customer') {
            if ($duplicateCustomer->name && $duplicateCustomer->name !== 'Facebook User' && $duplicateCustomer->name !== 'Customer') {
                $updates['name'] = $duplicateCustomer->name;
            }
        }

        // Update facebook_user_id if provided and master customer doesn't have one
        if (!$masterCustomer->facebook_user_id && $facebookUserId) {
            // Check if this facebook_user_id is already used by another customer
            $existingFacebookCustomer = Customer::where('client_id', $masterCustomer->client_id)
                ->where('facebook_user_id', $facebookUserId)
                ->where('id', '!=', $masterCustomer->id)
                ->first();
            
            if (!$existingFacebookCustomer) {
                $updates['facebook_user_id'] = $facebookUserId;
            } else {
                Log::info('Cannot merge facebook_user_id due to existing constraint', [
                    'facebook_user_id' => $facebookUserId,
                    'existing_customer_id' => $existingFacebookCustomer->id
                ]);
            }
        }

        if (!$masterCustomer->email && $duplicateCustomer->email) {
            $updates['email'] = $duplicateCustomer->email;
        }

        if (!$masterCustomer->address && $duplicateCustomer->address) {
            $updates['address'] = $duplicateCustomer->address;
        }

        // Update interaction stats
        $updates['interaction_count'] = ($masterCustomer->interaction_count ?? 0) + ($duplicateCustomer->interaction_count ?? 0);
        $updates['first_interaction'] = $masterCustomer->first_interaction < $duplicateCustomer->first_interaction 
            ? $masterCustomer->first_interaction 
            : $duplicateCustomer->first_interaction;
        $updates['last_interaction'] = $masterCustomer->last_interaction > $duplicateCustomer->last_interaction 
            ? $masterCustomer->last_interaction 
            : $duplicateCustomer->last_interaction;

        if (!empty($updates)) {
            $masterCustomer->update($updates);
        }

        // Transfer all page customer relationships
        \App\Models\PageCustomer::where('customer_id', $duplicateCustomer->id)
            ->update(['customer_id' => $masterCustomer->id]);

        // Transfer customer messages
        \App\Models\CustomerMessage::where('customer_id', $duplicateCustomer->id)
            ->update(['customer_id' => $masterCustomer->id]);

        // Transfer conversation states
        \App\Models\ConversationState::where('customer_id', $duplicateCustomer->id)
            ->update(['customer_id' => $masterCustomer->id]);

        // Transfer orders
        \App\Models\Order::where('customer_id', $duplicateCustomer->id)
            ->update(['customer_id' => $masterCustomer->id]);

        // Delete the duplicate customer
        $duplicateCustomer->delete();

        Log::info('Customer merge completed successfully', [
            'master_customer_id' => $masterCustomer->id,
            'merged_data' => $updates
        ]);
    }

    /**
     * Ensure page customer relationship exists
     */
    private function ensurePageCustomerRelationship(Customer $customer, FacebookPage $facebookPage, string $facebookUserId): PageCustomer
    {
        return PageCustomer::firstOrCreate(
            [
                'facebook_page_id' => $facebookPage->id,
                'customer_id' => $customer->id
            ],
            [
                'facebook_user_id' => $facebookUserId,
                'first_interaction' => now(),
                'last_interaction' => now(),
                'interaction_count' => 1,
                'status' => 'active'
            ]
        );
    }

    /**
     * Update customer from workflow data collection
     */
    public function updateFromWorkflowData(Customer $customer, array $collectedData, ?FacebookPage $facebookPage = null): Customer
    {
        // Extract phone number if provided
        if (!empty($collectedData['phone'])) {
            return $this->mergeWithPhoneCustomer($customer, $collectedData['phone'], $collectedData['name'] ?? null);
        }

        // Just update the customer if no phone provided
        $updates = [];
        if (!empty($collectedData['name']) && ($customer->name === 'Facebook User' || empty($customer->name))) {
            $updates['name'] = $collectedData['name'];
        }
        if (!empty($collectedData['email']) && empty($customer->email)) {
            $updates['email'] = $collectedData['email'];
        }
        if (!empty($collectedData['address']) && empty($customer->address)) {
            $updates['address'] = $collectedData['address'];
        }

        if (!empty($updates)) {
            $customer->update($updates);
        }

        return $customer;
    }
}