<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    // ... existing methods ...

    /**
     * Reset user password with proper notification system
     */
    public function resetPassword(User $user, Request $request): RedirectResponse
    {
        // Admin สามารถรีเซ็ตรหัสผ่านเฉพาะ regular users
        if ($user->role !== 'user') {
            abort(403, 'คุณไม่มีสิทธิ์รีเซ็ตรหัสผ่านของผู้ใช้นี้');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
            'send_sms' => 'nullable|boolean',
            'send_email' => 'nullable|boolean',
        ]);

        try {
            // Generate secure temporary password (8 characters)
            $newPassword = $this->generateSecurePassword();
            
            $user->update([
                'password' => Hash::make($newPassword),
                'password_changed_at' => now(),
                'must_change_password' => true, // Force password change on next login
            ]);

            // Store reset record
            DB::table('password_resets')->insert([
                'user_id' => $user->id,
                'reset_by' => Auth::id(),
                'reason' => $request->reason,
                'created_at' => now(),
            ]);

            // Send notifications
            $notificationResults = [];
            
            // Send SMS if requested and phone exists
            if ($request->send_sms && $user->phone) {
                $smsResult = $this->sendPasswordSMS($user, $newPassword);
                $notificationResults['sms'] = $smsResult;
            }
            
            // Send Email if requested and email exists
            if ($request->send_email && $user->email) {
                $emailResult = $this->sendPasswordEmail($user, $newPassword);
                $notificationResults['email'] = $emailResult;
            }

            // Log the action (without password in log!)
            Log::info('Admin reset user password', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'reason' => $request->reason,
                'notifications_sent' => array_keys(array_filter($notificationResults)),
                'action' => 'reset_password'
            ]);

            // Return success with notification results
            $message = "รีเซ็ตรหัสผ่านเรียบร้อยแล้ว";
            if (!empty($notificationResults)) {
                $sentTo = [];
                if ($notificationResults['sms'] ?? false) $sentTo[] = 'SMS';
                if ($notificationResults['email'] ?? false) $sentTo[] = 'Email';
                if (!empty($sentTo)) {
                    $message .= " และส่งรหัสผ่านไปยัง " . implode(' และ ', $sentTo) . " แล้ว";
                }
            } else {
                $message .= " รหัสผ่านใหม่: " . $newPassword . " (กรุณาเก็บรักษาไว้ในที่ปลอดภัย)";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน: ' . $e->getMessage());
        }
    }

    /**
     * Generate secure temporary password
     */
    private function generateSecurePassword(int $length = 8): string
    {
        // Create password with mixed characters
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($characters), 0, $length);
    }

    /**
     * Send password via SMS
     */
    private function sendPasswordSMS(User $user, string $password): bool
    {
        try {
            // แทน SMS Service จริง (เช่น Twilio, AWS SNS, หรือผู้ให้บริการในไทย)
            // ตัวอย่างนี้จำลองการส่ง SMS
            
            $message = "รหัสผ่านใหม่ของคุณคือ: {$password} กรุณาเปลี่ยนรหัสผ่านหลังเข้าสู่ระบบ";
            
            // จำลองการส่ง SMS (ในการใช้งานจริงต้องใช้ SMS Provider)
            Log::info('SMS would be sent', [
                'to' => $user->phone,
                'message' => 'Password reset SMS sent (message hidden for security)',
                'user_id' => $user->id
            ]);
            
            // Return true if SMS sent successfully
            // return $smsProvider->send($user->phone, $message);
            return true; // จำลองว่าส่งสำเร็จ
            
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send password via Email
     */
    private function sendPasswordEmail(User $user, string $password): bool
    {
        try {
            // ส่ง Email ด้วย Laravel Mail
            Mail::send('emails.password-reset', [
                'user' => $user,
                'password' => $password,
                'admin' => Auth::user()
            ], function ($message) use ($user) {
                $message->to($user->email, $user->first_name . ' ' . $user->last_name)
                        ->subject('รหัสผ่านใหม่ - ' . config('app.name'));
            });

            return true;
            
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
