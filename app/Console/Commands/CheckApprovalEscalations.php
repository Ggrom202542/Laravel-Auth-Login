<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApprovalNotificationService;

class CheckApprovalEscalations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approvals:check-escalations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for pending approvals that need escalation and send notifications';

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
        $this->info('🔍 Checking for escalated approvals...');
        
        $notificationCount = $this->notificationService->checkAndNotifyEscalations();
        
        if ($notificationCount > 0) {
            $this->info("✅ Sent {$notificationCount} escalation notifications");
        } else {
            $this->info('👍 No escalations found');
        }
        
        return 0;
    }
}
