<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessPendingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending notifications that could not be sent due to 24-hour messaging window';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing pending notifications...');
        
        try {
            $this->notificationService->processPendingNotifications();
            $this->info('Pending notifications processed successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to process pending notifications: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
