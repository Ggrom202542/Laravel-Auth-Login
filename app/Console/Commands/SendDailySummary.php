<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApprovalNotificationService;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approvals:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary of approval activities to admins';

    protected ApprovalNotificationService $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(ApprovalNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Sending daily approval summary...');
        
        $this->notificationService->sendDailySummary();
        
        $this->info('✅ Daily summary sent successfully');
        
        return 0;
    }
}
