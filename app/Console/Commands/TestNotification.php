<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RegistrationApproval;
use App\Notifications\NewRegistrationNotification;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ทดสอบการส่ง notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 ทดสอบระบบ Notification...');
        
        // หา admin user
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('❌ ไม่พบ Admin user');
            return;
        }
        
        $this->info("✅ พบ Admin: {$admin->first_name} {$admin->last_name}");
        
        // หา approval record
        $approval = RegistrationApproval::first();
        if (!$approval) {
            $this->error('❌ ไม่พบ Registration Approval record');
            return;
        }
        
        $this->info("✅ พบ Approval record: ID {$approval->id}");
        
        try {
            // ส่ง notification
            $admin->notify(new NewRegistrationNotification($approval));
            $this->info('🎉 ส่ง Notification สำเร็จ!');
            
            // ตรวจสอบ notifications count
            $count = $admin->notifications()->count();
            $unreadCount = $admin->unreadNotifications()->count();
            
            $this->info("📊 สถิติ Notifications:");
            $this->info("   - ทั้งหมด: {$count} รายการ");
            $this->info("   - ยังไม่อ่าน: {$unreadCount} รายการ");
            
        } catch (\Exception $e) {
            $this->error("❌ เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }
}
