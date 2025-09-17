<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class SystemSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display system settings dashboard
     */
    public function index(): View
    {
        $settings = $this->getAllSettings();
        $systemInfo = $this->getSystemInfo();
        
        return view('admin.settings.index', compact('settings', 'systemInfo'));
    }

    /**
     * Display general settings
     */
    public function general(): View
    {
        $settings = $this->getGeneralSettings();
        
        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:1000',
            'app_timezone' => 'required|string',
            'app_locale' => 'required|string',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'email_verification_required' => 'boolean',
            'admin_approval_required' => 'boolean',
        ]);

        foreach ($request->only([
            'app_name', 'app_description', 'app_timezone', 'app_locale',
            'maintenance_mode', 'registration_enabled', 'email_verification_required',
            'admin_approval_required'
        ]) as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return redirect()->back()->with('success', 'อัปเดตการตั้งค่าทั่วไปเรียบร้อยแล้ว');
    }

    /**
     * Display security settings
     */
    public function security(): View
    {
        $settings = $this->getSecuritySettings();
        
        return view('admin.settings.security', compact('settings'));
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request): RedirectResponse
    {
        $request->validate([
            'session_lifetime' => 'required|integer|min:5|max:1440',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:1|max:60',
            'password_min_length' => 'required|integer|min:6|max:20',
            'password_require_uppercase' => 'boolean',
            'password_require_lowercase' => 'boolean',
            'password_require_numbers' => 'boolean',
            'password_require_symbols' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'force_https' => 'boolean',
            'ip_restriction_enabled' => 'boolean',
        ]);

        foreach ($request->only([
            'session_lifetime', 'max_login_attempts', 'lockout_duration',
            'password_min_length', 'password_require_uppercase', 'password_require_lowercase',
            'password_require_numbers', 'password_require_symbols', 'two_factor_enabled',
            'force_https', 'ip_restriction_enabled'
        ]) as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return redirect()->back()->with('success', 'อัปเดตการตั้งค่าความปลอดภัยเรียบร้อยแล้ว');
    }

    /**
     * Display email settings
     */
    public function email(): View
    {
        $settings = $this->getEmailSettings();
        
        return view('admin.settings.email', compact('settings'));
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'required_if:mail_driver,smtp|string',
            'mail_port' => 'required_if:mail_driver,smtp|integer',
            'mail_username' => 'required_if:mail_driver,smtp|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        foreach ($request->only([
            'mail_driver', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'
        ]) as $key => $value) {
            if ($key === 'mail_password' && empty($value)) {
                continue; // Skip empty password
            }
            $this->updateSetting($key, $value);
        }

        return redirect()->back()->with('success', 'อัปเดตการตั้งค่าอีเมลเรียบร้อยแล้ว');
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            // Send test email
            Mail::raw('นี่คือการทดสอบอีเมลจากระบบ Laravel Authentication', function ($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('ทดสอบการตั้งค่าอีเมล');
            });

            return redirect()->back()->with('success', 'ส่งอีเมลทดสอบเรียบร้อยแล้ว กรุณาตรวจสอบกล่องจดหมาย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ไม่สามารถส่งอีเมลได้: ' . $e->getMessage());
        }
    }

    /**
     * Display notification settings
     */
    public function notifications(): View
    {
        $settings = $this->getNotificationSettings();
        
        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->validate([
            'notification_email_enabled' => 'boolean',
            'notification_database_enabled' => 'boolean',
            'notification_approval_enabled' => 'boolean',
            'notification_security_enabled' => 'boolean',
            'notification_system_enabled' => 'boolean',
        ]);

        foreach ($request->only([
            'notification_email_enabled', 'notification_database_enabled',
            'notification_approval_enabled', 'notification_security_enabled',
            'notification_system_enabled'
        ]) as $key => $value) {
            $this->updateSetting($key, $value);
        }

        return redirect()->back()->with('success', 'อัปเดตการตั้งค่าการแจ้งเตือนเรียบร้อยแล้ว');
    }

    /**
     * Display backup settings
     */
    public function backup(): View
    {
        $settings = $this->getBackupSettings();
        $backups = $this->getBackupFiles();
        
        return view('admin.settings.backup', compact('settings', 'backups'));
    }

    /**
     * Create system backup
     */
    public function createBackup(): RedirectResponse
    {
        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            
            return redirect()->back()->with('success', 'สร้างการสำรองข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ไม่สามารถสร้างการสำรองข้อมูลได้: ' . $e->getMessage());
        }
    }

    /**
     * Clear system cache
     */
    public function clearCache(): RedirectResponse
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return redirect()->back()->with('success', 'ล้างแคชระบบเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ไม่สามารถล้างแคชได้: ' . $e->getMessage());
        }
    }

    /**
     * Optimize system
     */
    public function optimize(): RedirectResponse
    {
        try {
            Artisan::call('optimize');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            
            return redirect()->back()->with('success', 'ปรับปรุงประสิทธิภาพระบบเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ไม่สามารถปรับปรุงระบบได้: ' . $e->getMessage());
        }
    }

    /**
     * Get all system settings
     */
    protected function getAllSettings(): array
    {
        return [
            'general' => $this->getGeneralSettings(),
            'security' => $this->getSecuritySettings(),
            'email' => $this->getEmailSettings(),
            'notifications' => $this->getNotificationSettings(),
            'backup' => $this->getBackupSettings(),
        ];
    }

    /**
     * Get general settings
     */
    protected function getGeneralSettings(): array
    {
        return [
            'app_name' => $this->getSetting('app_name', config('app.name')),
            'app_description' => $this->getSetting('app_description', ''),
            'app_timezone' => $this->getSetting('app_timezone', config('app.timezone')),
            'app_locale' => $this->getSetting('app_locale', config('app.locale')),
            'maintenance_mode' => $this->getSetting('maintenance_mode', false),
            'registration_enabled' => $this->getSetting('registration_enabled', true),
            'email_verification_required' => $this->getSetting('email_verification_required', false),
            'admin_approval_required' => $this->getSetting('admin_approval_required', true),
        ];
    }

    /**
     * Get security settings
     */
    protected function getSecuritySettings(): array
    {
        return [
            'session_lifetime' => $this->getSetting('session_lifetime', 120),
            'max_login_attempts' => $this->getSetting('max_login_attempts', 5),
            'lockout_duration' => $this->getSetting('lockout_duration', 5),
            'password_min_length' => $this->getSetting('password_min_length', 8),
            'password_require_uppercase' => $this->getSetting('password_require_uppercase', true),
            'password_require_lowercase' => $this->getSetting('password_require_lowercase', true),
            'password_require_numbers' => $this->getSetting('password_require_numbers', true),
            'password_require_symbols' => $this->getSetting('password_require_symbols', false),
            'two_factor_enabled' => $this->getSetting('two_factor_enabled', false),
            'force_https' => $this->getSetting('force_https', false),
            'ip_restriction_enabled' => $this->getSetting('ip_restriction_enabled', false),
        ];
    }

    /**
     * Get email settings
     */
    protected function getEmailSettings(): array
    {
        return [
            'mail_driver' => $this->getSetting('mail_driver', config('mail.default')),
            'mail_host' => $this->getSetting('mail_host', config('mail.mailers.smtp.host')),
            'mail_port' => $this->getSetting('mail_port', config('mail.mailers.smtp.port')),
            'mail_username' => $this->getSetting('mail_username', config('mail.mailers.smtp.username')),
            'mail_password' => '****', // Never show actual password
            'mail_encryption' => $this->getSetting('mail_encryption', config('mail.mailers.smtp.encryption')),
            'mail_from_address' => $this->getSetting('mail_from_address', config('mail.from.address')),
            'mail_from_name' => $this->getSetting('mail_from_name', config('mail.from.name')),
        ];
    }

    /**
     * Get notification settings
     */
    protected function getNotificationSettings(): array
    {
        return [
            'notification_email_enabled' => $this->getSetting('notification_email_enabled', true),
            'notification_database_enabled' => $this->getSetting('notification_database_enabled', true),
            'notification_approval_enabled' => $this->getSetting('notification_approval_enabled', true),
            'notification_security_enabled' => $this->getSetting('notification_security_enabled', true),
            'notification_system_enabled' => $this->getSetting('notification_system_enabled', true),
        ];
    }

    /**
     * Get backup settings
     */
    protected function getBackupSettings(): array
    {
        return [
            'backup_enabled' => $this->getSetting('backup_enabled', false),
            'backup_frequency' => $this->getSetting('backup_frequency', 'daily'),
            'backup_retention_days' => $this->getSetting('backup_retention_days', 30),
        ];
    }

    /**
     * Get system information
     */
    protected function getSystemInfo(): array
    {
        return [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'database_type' => config('database.default'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
        ];
    }

    /**
     * Get backup files
     */
    protected function getBackupFiles(): array
    {
        $files = [];
        
        if (Storage::disk('local')->exists('backups')) {
            $backupFiles = Storage::disk('local')->files('backups');
            
            foreach ($backupFiles as $file) {
                $files[] = [
                    'name' => basename($file),
                    'size' => $this->formatBytes(Storage::disk('local')->size($file)),
                    'created_at' => date('d/m/Y H:i:s', Storage::disk('local')->lastModified($file)),
                ];
            }
        }
        
        return $files;
    }

    /**
     * Get setting value
     */
    protected function getSetting(string $key, $default = null)
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = DB::table('system_settings')->where('key', $key)->first();
            return $setting ? json_decode($setting->value, true) : $default;
        });
    }

    /**
     * Update setting value
     */
    protected function updateSetting(string $key, $value): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => json_encode($value),
                'updated_at' => now()
            ]
        );

        Cache::forget("system_setting_{$key}");
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}