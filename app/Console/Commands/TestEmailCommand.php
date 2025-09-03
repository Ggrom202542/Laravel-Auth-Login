<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email} {--subject=Test Email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $subject = $this->option('subject');
        
        $this->info("Testing email to: {$email}");
        $this->info("Subject: {$subject}");
        $this->info("SMTP Settings:");
        $this->info("- Host: " . config('mail.mailers.smtp.host'));
        $this->info("- Port: " . config('mail.mailers.smtp.port'));
        $this->info("- Username: " . config('mail.mailers.smtp.username'));
        $this->info("- Encryption: " . config('mail.mailers.smtp.encryption'));
        
        try {
            Mail::raw('This is a test email from Laravel application.', function ($message) use ($email, $subject) {
                $message->to($email)
                        ->subject($subject);
            });
            
            $this->info("✅ Email sent successfully!");
            
        } catch (\Exception $e) {
            $this->error("❌ Email sending failed:");
            $this->error($e->getMessage());
            Log::error('Test email failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
        
        return 0;
    }
}
