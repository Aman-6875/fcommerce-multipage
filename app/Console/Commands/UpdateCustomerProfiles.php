<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\FacebookPage;
use App\Services\FacebookGraphAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCustomerProfiles extends Command
{
    protected $signature = 'customers:update-profiles {--customer-id=} {--dry-run}';
    protected $description = 'Update customer profiles from Facebook data';

    public function handle()
    {
        $customerId = $this->option('customer-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no actual updates will be made');
        }

        $query = Customer::whereNotNull('facebook_user_id');
        
        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->with(['client.facebookPages' => function ($q) {
            $q->where('is_connected', true);
        }])->get();

        $this->info("Found {$customers->count()} customers to process");

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($customers as $customer) {
            $this->line("Processing customer ID {$customer->id} ({$customer->name})...");
            
            // Find a connected Facebook page for this customer
            $facebookPage = $customer->client->facebookPages->first();
            
            if (!$facebookPage) {
                $this->warn("  No connected Facebook page found for customer {$customer->id}");
                $skipped++;
                continue;
            }

            try {
                if (!$dryRun) {
                    $facebookService = app(FacebookGraphAPIService::class);
                    $facebookService->updateCustomerWithFacebookProfile($customer, $facebookPage);
                    
                    // Refresh the model to see the changes
                    $customer->refresh();
                    
                    $this->info("  Updated: {$customer->name}");
                    if ($customer->hasFacebookProfile()) {
                        $this->line("    Profile pic: " . ($customer->getFacebookProfilePicture() ? 'Yes' : 'No'));
                        $this->line("    Facebook name: " . ($customer->getFacebookName() ?? 'N/A'));
                    }
                } else {
                    $this->info("  [DRY RUN] Would update customer {$customer->id}");
                }
                
                $updated++;
            } catch (\Exception $e) {
                $this->error("  Error updating customer {$customer->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\nSummary:");
        $this->info("Updated: {$updated}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");

        return 0;
    }
}