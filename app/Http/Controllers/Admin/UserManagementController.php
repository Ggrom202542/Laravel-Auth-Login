<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use App\Services\SmsService;
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
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
    }

    /**
     * Display a listing of users (Admin จัดการเฉพาะ regular users)
     */
    public function index(Request $request)
    {
        try {
            $currentUser = Auth::user();
            
            // Admin จัดการเฉพาะ users ที่มี role = 'user' เท่านั้น
            $query = User::where('role', 'user')
                         ->with(['registrationApproval'])
                         ->orderBy('created_at', 'desc');

            // Search functionality
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Approval status filter
            if ($request->has('approval_status') && $request->approval_status !== '') {
                $query->where('approval_status', $request->approval_status);
            }

            // Date range filter
            if ($request->has('date_from') && $request->date_from !== '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to !== '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $users = $query->paginate(20);

            // Statistics - handle potential errors
            try {
                $stats = $this->getUserStatistics();
            } catch (\Exception $e) {
                Log::error('Error getting user statistics: ' . $e->getMessage());
                $stats = [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'pending_approval' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'today_registrations' => 0,
                    'recent_logins' => 0,
                ];
            }

            return view('admin.users.index', compact('users', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error in UserManagementController@index: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                           ->with('error', 'เกิดข้อผิดพลาดในการโหลดหน้าจัดการผู้ใช้: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): View
    {
        // Admin สามารถดูเฉพาะ regular users
        if ($user->role !== 'user') {
            abort(403, 'คุณไม่มีสิทธิ์ดูข้อมูลผู้ใช้นี้');
        }

        $user->load(['registrationApproval.reviewer']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user): View
    {
        // Admin สามารถแก้ไขเฉพาะ regular users
        if ($user->role !== 'user') {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขผู้ใช้นี้');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Admin สามารถแก้ไขเฉพาะ regular users
        if ($user->role !== 'user') {
            abort(403, 'คุณไม่มีสิทธิ์แก้ไขผู้ใช้นี้');
        }

        $request->validate([
            'prefix' => 'required|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id)
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users')->ignore($user->id)
            ],
            'admin_notes' => 'nullable|string|max:1000'
        ], [
            'prefix.required' => 'กรุณาเลือกคำนำหน้า',
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้มีอยู่ในระบบแล้ว',
            'phone.required' => 'กรุณากรอกหมายเลขโทรศัพท์',
            'phone.unique' => 'หมายเลขโทรศัพท์นี้มีอยู่ในระบบแล้ว',
            'username.required' => 'กรุณากรอก Username',
            'username.alpha_dash' => 'Username ใช้ได้เฉพาะตัวอักษร ตัวเลข ขีดกลาง และขีดล่าง',
            'username.unique' => 'Username นี้มีอยู่ในระบบแล้ว'
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'prefix' => $request->prefix,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => $request->username,
                'admin_notes' => $request->admin_notes,
            ]);

            // Log the action
            Log::info('Admin updated user', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'action' => 'update_user'
            ]);

            DB::commit();

            return redirect()->route('admin.users.show', $user)
                           ->with('success', 'อัพเดทข้อมูลผู้ใช้เรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Admin สามารถเปลี่ยนสถานะเฉพาะ regular users
        if ($user->role !== 'user') {
            abort(403, 'คุณไม่มีสิทธิ์เปลี่ยนสถานะผู้ใช้นี้');
        }

        try {
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            
            $user->update(['status' => $newStatus]);

            // Log the action
            Log::info('Admin toggled user status', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'old_status' => $user->status === 'active' ? 'inactive' : 'active',
                'new_status' => $newStatus,
                'action' => 'toggle_user_status'
            ]);

            $statusText = $newStatus === 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
            
            return redirect()->back()
                           ->with('success', "เปลี่ยนสถานะผู้ใช้เป็น {$statusText} เรียบร้อยแล้ว");

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password and send notification
     */
    public function resetPassword(Request $request, User $user)
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
            // Generate secure temporary password
            $newPassword = $this->generateSecurePassword();
            
            // Update user password and settings
            $user->update([
                'password' => Hash::make($newPassword),
                'password_changed_at' => now(),
                'must_change_password' => true,
                'password_reset_count' => ($user->password_reset_count ?? 0) + 1,
            ]);

            // Create password reset record
            $passwordReset = PasswordReset::create([
                'user_id' => $user->id,
                'reset_by' => Auth::id(),
                'reset_type' => 'admin_reset',
                'reason' => $request->reason,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Send notifications
            $notificationResults = [];
            $notificationMethods = [];
            
            // Send SMS if requested and phone exists
            if ($request->send_sms && $user->phone) {
                $smsService = new SmsService();
                $smsResult = $this->sendPasswordSMS($user, $newPassword, $smsService);
                $notificationResults['sms'] = $smsResult;
                $notificationMethods[] = 'SMS';
            }
            
            // Send Email if requested and email exists
            if ($request->send_email && $user->email) {
                $emailResult = $this->sendPasswordEmail($user, $newPassword);
                $notificationResults['email'] = $emailResult;
                $notificationMethods[] = 'Email';
            }

            // Update password reset record with notification results
            $passwordReset->update([
                'notification_sent' => !empty($notificationResults),
                'notification_methods' => $notificationMethods,
                'notification_results' => $notificationResults,
            ]);

            // Log the action (without password!)
            Log::info('Admin reset user password', [
                'admin_id' => Auth::id(),
                'admin_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'reason' => $request->reason,
                'notifications_sent' => array_keys(array_filter($notificationResults)),
                'action' => 'reset_password',
                'reset_id' => $passwordReset->id
            ]);

            // Return success with notification results
            $message = "รีเซ็ตรหัสผ่านเรียบร้อยแล้ว";
            
            if (!empty($notificationResults)) {
                $sentTo = [];
                if ($notificationResults['sms'] ?? false) $sentTo[] = 'SMS';
                if ($notificationResults['email'] ?? false) $sentTo[] = 'Email';
                
                if (!empty($sentTo)) {
                    $message .= " และส่งรหัสผ่านไปยัง " . implode(' และ ', $sentTo) . " แล้ว";
                } else {
                    $message .= " แต่การส่งแจ้งเตือนล้มเหลว";
                }
            } else {
                $message .= " รหัสผ่านใหม่: " . $newPassword . " (กรุณาเก็บรักษาไว้ในที่ปลอดภัย)";
            }

            // Return JSON response for AJAX or redirect for regular form submission
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'new_password' => $newPassword,
                    'sms_sent' => $notificationResults['sms'] ?? false,
                    'email_sent' => $notificationResults['email'] ?? false,
                    'notification_methods' => array_keys(array_filter($notificationResults))
                ]);
            }

            return redirect()->back()
                           ->with('success', $message)
                           ->with('show_password', empty($notificationResults))
                           ->with('temp_password', empty($notificationResults) ? $newPassword : null);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'user_id' => $user->id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return JSON response for AJAX or redirect for regular form submission
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน: ' . $e->getMessage());
        }
    }

    /**
     * Generate secure temporary password
     */
    private function generateSecurePassword(int $length = 8): string
    {
        // Create password with mixed characters (easy to read, no ambiguous characters)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }

    /**
     * Send password via SMS
     */
    private function sendPasswordSMS(User $user, string $password, SmsService $smsService): bool
    {
        try {
            $appName = config('app.name');
            $message = "รหัสผ่านใหม่ {$appName}: {$password}\nกรุณาเปลี่ยนรหัสผ่านหลังเข้าสู่ระบบ\nUsername: {$user->username}";
            
            return $smsService->send($user->phone, $message);
            
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
            // Send Email using the template we created
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

    /**
     * Get user statistics for dashboard
     */
    public function getUserStatistics(): array
    {
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('status', 'active')->count();
        $inactiveUsers = User::where('role', 'user')->where('status', 'inactive')->count();
        $pendingApprovals = User::where('role', 'user')->where('approval_status', 'pending')->count();
        $approvedUsers = User::where('role', 'user')->where('approval_status', 'approved')->count();
        $rejectedUsers = User::where('role', 'user')->where('approval_status', 'rejected')->count();
        $todayRegistrations = User::where('role', 'user')->whereDate('created_at', today())->count();
        $recentLogins = User::where('role', 'user')
                           ->where('last_login_at', '>=', now()->subDays(7))
                           ->count();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'pending_approval' => $pendingApprovals,
            'approved' => $approvedUsers,
            'rejected' => $rejectedUsers,
            'today_registrations' => $todayRegistrations,
            'recent_logins' => $recentLogins,
        ];
    }
}
