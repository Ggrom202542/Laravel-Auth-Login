<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RegistrationApproval;

class TestApprovalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:approval-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test registration approval additional_data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Clear model cache
        RegistrationApproval::clearBootedModels();
        
        $approvals = RegistrationApproval::take(5)->get();
        
        if ($approvals->isEmpty()) {
            $this->error('No registration approvals found');
            return;
        }
        
        foreach ($approvals as $approval) {
            // Force reload
            $fresh = RegistrationApproval::find($approval->id);
            
            $this->info("ID: {$approval->id}");
            $this->info("Type: " . gettype($fresh->additional_data));
            $this->info("Raw value: " . $fresh->getRawOriginal('additional_data'));
            $this->info("Cast value: " . json_encode($fresh->additional_data));
            $this->info("Is array: " . (is_array($fresh->additional_data) ? 'Yes' : 'No'));
            
            if (is_array($fresh->additional_data)) {
                $this->info("Array keys: " . implode(', ', array_keys($fresh->additional_data)));
            }
            
            $this->line('---');
        }
    }
}
