<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminSession;
use App\Models\SecurityPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class SuperAdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('super.admin');
    }

    /**
     * Display a listing of all users (including admins and super_admins)
     */
    public function index(Request $request)
    {
        $query = User::with(['adminSessions' => function($q) {
            $q->where('status', 'active')->latest();
        }]);

        // Filter by role
        if ($request->filled('role_filter')) {
            $query->where('role', $request->role_filter);
        }

        // Filter by status
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        // Filter by 2FA status
        if ($request->filled('two_fa_filter')) {
            if ($request->two_fa_filter === 'enabled') {
                $query->where('two_factor_enabled', true);
            } else if ($request->two_fa_filter === 'disabled') {
                $query->where('two_factor_enabled', false);
            }
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $validSortColumns = ['name', 'email', 'role', 'status', 'created_at', 'updated_at', 'last_login_at'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $users = $query->paginate(20);

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'super_admin_users' => User::where('role', 'super_admin')->count(),
            'two_fa_enabled' => User::where('google2fa_enabled', true)->count(),
            'recent_logins' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
            'active_sessions' => AdminSession::where('status', 'active')->count()
        ];

        return view('admin.super-admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.super-admin.users.create');
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prefix' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended', 'pending'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            
            // Super Admin specific fields
            'two_factor_enabled' => 'boolean',
            'allowed_ip_addresses' => 'nullable|string',
            'session_timeout' => 'nullable|integer|min:5|max:480', // 5 minutes to 8 hours
            'allowed_login_methods' => 'nullable|array',
            'allowed_login_methods.*' => 'in:password,two_factor,social',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate full name from components
            $fullName = trim(
                ($request->prefix ? $request->prefix . ' ' : '') . 
                $request->first_name . ' ' . 
                $request->last_name
            );

            $userData = [
                'prefix' => $request->prefix,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                // Remove 'name' field as it doesn't exist in database
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status,
                'phone' => $request->phone,
                'address' => $request->address,
                'email_verified_at' => now(),
                
                // Super Admin fields - use correct field names
                'two_factor_enabled' => $request->boolean('two_factor_enabled'),
                'allowed_ip_addresses' => $request->allowed_ip_addresses,
                'admin_session_timeout' => $request->session_timeout, // Use correct field name
                'admin_notes' => $request->admin_notes,
                'created_by_admin' => Auth::id(),
                // Remove fields that don't exist: allowed_login_methods
            ];

            $user = User::create($userData);

            // Log this action
            Log::info('Super Admin created new user', [
                'super_admin_id' => Auth::id(),
                'created_user_id' => $user->id,
                'created_user_role' => $user->role,
                'ip_address' => $request->ip()
            ]);

            // Send welcome email if user is active
            if ($user->status === 'active') {
                try {
                    Mail::send('emails.welcome-admin-created', ['user' => $user, 'password' => $request->password], function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('ยินดีต้อนรับสู่ระบบ - บัญชีของคุณถูกสร้างโดยผู้ดูแลระบบ');
                    });
                } catch (\Exception $e) {
                    Log::warning('Failed to send welcome email', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'สร้างผู้ใช้สำเร็จ',
                    'user' => $user
                ]);
            }

            return redirect()->route('super-admin.users.index')
                ->with('success', 'สร้างผู้ใช้สำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการสร้างผู้ใช้'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาดในการสร้างผู้ใช้')
                ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with([
            'adminSessions' => function($q) {
                $q->orderBy('created_at', 'desc')->take(20);
            }
        ])->findOrFail($id);

        // Get user activity statistics
        $stats = [
            'total_sessions' => AdminSession::where('user_id', $id)->count(),
            'active_sessions' => AdminSession::where('user_id', $id)->where('status', 'active')->count(),
            'last_7_days_sessions' => AdminSession::where('user_id', $id)
                ->where('created_at', '>=', now()->subDays(7))->count(),
            'failed_logins' => AdminSession::where('user_id', $id)
                ->where('login_method', 'failed')->count(),
            'unique_ip_addresses' => AdminSession::where('user_id', $id)
                ->distinct('ip_address')->count('ip_address')
        ];

        // Get recent activities
        $recentSessions = AdminSession::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.super-admin.users.show', compact('user', 'stats', 'recentSessions'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.super-admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Debug: Log ข้อมูลที่ส่งมา
        Log::info('Update User Request Data', [
            'user_id' => $id,
            'request_data' => $request->all(),
            'super_admin_id' => Auth::id()
        ]);

        $validator = Validator::make($request->all(), [
            'prefix' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended', 'pending'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            
            // Super Admin specific fields
            'two_factor_enabled' => 'boolean',
            'allowed_ip_addresses' => 'nullable|string',
            'session_timeout' => 'nullable|integer|min:5|max:480',
            'allowed_login_methods' => 'nullable|array',
            'allowed_login_methods.*' => 'in:password,two_factor,social',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('User update validation failed', [
                'user_id' => $id,
                'errors' => $validator->errors()->toArray(),
                'super_admin_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate full name from components
            $fullName = trim(
                ($request->prefix ? $request->prefix . ' ' : '') . 
                $request->first_name . ' ' . 
                $request->last_name
            );

            $updateData = [
                'prefix' => $request->prefix,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                // Remove 'name' field as it doesn't exist in database
                'email' => $request->email,
                'username' => $request->username,
                'role' => $request->role,
                'status' => $request->status,
                'phone' => $request->phone,
                'address' => $request->address,
                
                // Super Admin fields - use correct field names that exist in database
                'two_factor_enabled' => $request->boolean('two_factor_enabled'),
                'allowed_ip_addresses' => $request->allowed_ip_addresses,
                'admin_session_timeout' => $request->session_timeout, // Use correct field name
                'admin_notes' => $request->admin_notes,
                // Remove fields that don't exist: updated_by_admin, allowed_login_methods
            ];

            // Debug: Log update data before saving
            Log::info('Update Data Prepared', [
                'user_id' => $id,
                'full_name_generated' => $fullName,
                'update_data' => $updateData,
                'super_admin_id' => Auth::id()
            ]);

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
                Log::info('Password update requested', ['user_id' => $id]);
            }

            $oldData = $user->toArray();
            
            // Debug: Log before update
            Log::info('Before Update', [
                'user_id' => $id,
                'old_data' => $oldData
            ]);
            
            $result = $user->update($updateData);
            
            // Debug: Log after update
            Log::info('After Update', [
                'user_id' => $id,
                'update_result' => $result,
                'new_data' => $user->fresh()->toArray()
            ]);

            // Log this action with changes
            $changes = array_diff_assoc($updateData, $oldData);
            Log::info('Super Admin updated user', [
                'super_admin_id' => Auth::id(),
                'updated_user_id' => $user->id,
                'changes' => $changes,
                'ip_address' => $request->ip()
            ]);

            // If status changed to suspended, terminate all active sessions
            if ($request->status === 'suspended' && $user->wasChanged('status')) {
                AdminSession::where('user_id', $id)->update(['status' => 'terminated']);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'อัปเดตข้อมูลผู้ใช้สำเร็จ',
                    'user' => $user->fresh()
                ]);
            }

            return redirect()->route('super-admin.users.show', $id)
                ->with('success', 'อัปเดตข้อมูลผู้ใช้สำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update user', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id(),
                'user_id' => $id
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลผู้ใช้'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลผู้ใช้')
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting the last super admin
            if ($user->role === 'super_admin') {
                $superAdminCount = User::where('role', 'super_admin')->count();
                if ($superAdminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ไม่สามารถลบ Super Admin คนสุดท้ายได้'
                    ], 400);
                }
            }

            DB::beginTransaction();

            // Log before deletion
            Log::info('Super Admin deleted user', [
                'super_admin_id' => Auth::id(),
                'deleted_user_id' => $user->id,
                'deleted_user_name' => $user->name,
                'deleted_user_email' => $user->email,
                'deleted_user_role' => $user->role
            ]);

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ลบผู้ใช้สำเร็จ'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete user', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบผู้ใช้'
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8|confirmed',
            'send_email' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            
            DB::beginTransaction();

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Log password reset
            Log::info('Super Admin reset user password', [
                'super_admin_id' => Auth::id(),
                'reset_user_id' => $user->id,
                'reset_user_email' => $user->email,
                'ip_address' => $request->ip()
            ]);

            // Send email notification if requested
            if ($request->boolean('send_email')) {
                try {
                    Mail::send('emails.password-reset-by-admin', [
                        'user' => $user,
                        'newPassword' => $request->new_password,
                        'adminName' => Auth::user()->name
                    ], function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('รหัสผ่านของคุณถูกรีเซ็ตโดยผู้ดูแลระบบ');
                    });
                } catch (\Exception $e) {
                    Log::warning('Failed to send password reset email', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'รีเซ็ตรหัสผ่านสำเร็จ'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to reset user password', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน'
            ], 500);
        }
    }

    /**
     * Toggle user status (active/inactive/suspended)
     */
    public function toggleStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            $oldStatus = $user->status;
            
            DB::beginTransaction();

            $user->update([
                'status' => $request->status,
                'updated_by_admin' => Auth::id()
            ]);

            // If suspending user, terminate all active sessions
            if ($request->status === 'suspended') {
                AdminSession::where('user_id', $id)->update(['status' => 'terminated']);
            }

            // Log status change
            Log::info('Super Admin changed user status', [
                'super_admin_id' => Auth::id(),
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'reason' => $request->reason,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'เปลี่ยนสถานะผู้ใช้สำเร็จ',
                'new_status' => $request->status
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to toggle user status', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะผู้ใช้'
            ], 500);
        }
    }

    /**
     * Promote user to admin or super admin
     */
    public function promoteRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            $oldRole = $user->role;
            
            DB::beginTransaction();

            $user->update([
                'role' => $request->role,
                'updated_by_admin' => Auth::id()
            ]);

            // Log role change
            Log::info('Super Admin promoted user role', [
                'super_admin_id' => Auth::id(),
                'user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $request->role,
                'reason' => $request->reason,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'เปลี่ยนบทบาทผู้ใช้สำเร็จ',
                'new_role' => $request->role
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to promote user role', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเปลี่ยนบทบาทผู้ใช้'
            ], 500);
        }
    }

    /**
     * Terminate all active sessions for a user
     */
    public function terminateSessions(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $terminatedCount = AdminSession::where('user_id', $id)
                ->where('status', 'active')
                ->update(['status' => 'terminated']);

            // Log session termination
            Log::info('Super Admin terminated user sessions', [
                'super_admin_id' => Auth::id(),
                'user_id' => $id,
                'terminated_sessions' => $terminatedCount,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "ยกเลิกเซสชันทั้งหมดสำเร็จ ({$terminatedCount} เซสชัน)"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to terminate user sessions', [
                'error' => $e->getMessage(),
                'super_admin_id' => Auth::id(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการยกเลิกเซสชัน'
            ], 500);
        }
    }

    /**
     * แสดงรายการ Sessions ที่ใช้งานทั้งหมด
     */
    public function sessions(Request $request)
    {
        try {
            // ดึงข้อมูล sessions จริงจากฐานข้อมูล
            $query = AdminSession::with(['user'])
                ->where('status', 'active')
                ->orderBy('last_activity', 'desc');

            // Filter by user role if specified
            if ($request->filled('role')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('role', $request->role);
                });
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('ip_address', 'LIKE', "%{$search}%")
                      ->orWhere('user_agent', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('first_name', 'LIKE', "%{$search}%")
                                   ->orWhere('last_name', 'LIKE', "%{$search}%")
                                   ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }

            $sessions = $query->paginate(20);

            return view('admin.super-admin.users.sessions-simple', compact('sessions'));
            
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Super Admin Sessions Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            
            // Return empty collection with error message
            $sessions = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                20,
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            
            return view('admin.super-admin.users.sessions-simple', compact('sessions'))
                ->with('error', 'ไม่สามารถโหลดข้อมูล Sessions ได้ในขณะนี้');
        }
    }
}
