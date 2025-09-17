<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display role and permission management dashboard
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        
        // Get all users with their roles
        $query = User::query();
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $users = $query->orderBy('role')
                      ->orderBy('first_name')
                      ->paginate(20);
        
        // Get role statistics
        $roleStats = User::select('role', DB::raw('count(*) as count'))
                        ->groupBy('role')
                        ->get()
                        ->pluck('count', 'role')
                        ->toArray();
        
        // Define available roles and their descriptions
        $availableRoles = [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'มีสิทธิ์เข้าถึงและจัดการทุกส่วนของระบบ',
                'permissions' => [
                    'จัดการผู้ใช้งานทั้งหมด',
                    'จัดการบทบาทและสิทธิ์',
                    'เข้าถึงรายงานระบบทั้งหมด',
                    'จัดการการตั้งค่าระบบ',
                    'อนุมัติและปฏิเสธการสมัครสมาชิก',
                    'Override การตัดสินใจของ Admin',
                    'เข้าถึง Audit Logs ทั้งหมด',
                    'จัดการความปลอดภัยขั้นสูง'
                ],
                'color' => 'danger'
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'จัดการผู้ใช้และอนุมัติการสมัครสมาชิก',
                'permissions' => [
                    'จัดการผู้ใช้งานระดับ User',
                    'อนุมัติและปฏิเสธการสมัครสมาชิก',
                    'ดูรายงานการอนุมัติ',
                    'จัดการ Sessions ของผู้ใช้',
                    'เข้าถึงระบบความปลอดภัยพื้นฐาน',
                    'ส่งการแจ้งเตือนให้ผู้ใช้'
                ],
                'color' => 'primary'
            ],
            'user' => [
                'name' => 'User',
                'description' => 'ผู้ใช้งานทั่วไป',
                'permissions' => [
                    'จัดการโปรไฟล์ส่วนตัว',
                    'ดูประวัติการเข้าสู่ระบบ',
                    'จัดการอุปกรณ์ของตนเอง',
                    'รับการแจ้งเตือน',
                    'เปลี่ยนรหัสผ่าน',
                    'จัดการการตั้งค่าความปลอดภัย'
                ],
                'color' => 'success'
            ]
        ];
        
        return view('admin.roles.index', compact(
            'users',
            'roleStats',
            'availableRoles',
            'currentUser'
        ));
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:user,admin,super_admin',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Prevent changing own role
        if ($user->id === Auth::id()) {
            return redirect()->back()
                           ->with('error', 'ไม่สามารถเปลี่ยนบทบาทของตนเองได้');
        }

        // Store old role for logging
        $oldRole = $user->role;
        $newRole = $request->role;

        // Update role
        $user->update(['role' => $newRole]);

        // Log the role change
        DB::table('role_change_logs')->insert([
            'user_id' => $user->id,
            'changed_by' => Auth::id(),
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'reason' => $request->reason,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send notification to the user
        $user->notify(new \App\Notifications\RoleChangedNotification($oldRole, $newRole, $request->reason));

        return redirect()->back()
                       ->with('success', "เปลี่ยนบทบาทของ {$user->first_name} {$user->last_name} จาก {$oldRole} เป็น {$newRole} เรียบร้อยแล้ว");
    }

    /**
     * Get role change history
     */
    public function roleHistory(Request $request): View
    {
        $query = DB::table('role_change_logs as rcl')
                  ->join('users as u', 'rcl.user_id', '=', 'u.id')
                  ->join('users as changer', 'rcl.changed_by', '=', 'changer.id')
                  ->select([
                      'rcl.*',
                      'u.first_name as user_first_name',
                      'u.last_name as user_last_name',
                      'u.email as user_email',
                      'changer.first_name as changer_first_name',
                      'changer.last_name as changer_last_name'
                  ]);

        if ($request->filled('user_id')) {
            $query->where('rcl.user_id', $request->user_id);
        }

        if ($request->filled('role')) {
            $query->where(function($q) use ($request) {
                $q->where('rcl.old_role', $request->role)
                  ->orWhere('rcl.new_role', $request->role);
            });
        }

        $roleChanges = $query->orderBy('rcl.created_at', 'desc')
                           ->paginate(20);

        return view('admin.roles.history', compact('roleChanges'));
    }

    /**
     * Permission management overview
     */
    public function permissions(): View
    {
        // Get permission summary by role
        $permissionSummary = [
            'super_admin' => [
                'total_users' => User::where('role', 'super_admin')->count(),
                'capabilities' => 8,
                'risk_level' => 'High',
                'description' => 'สิทธิ์เต็มในการจัดการระบบ'
            ],
            'admin' => [
                'total_users' => User::where('role', 'admin')->count(),
                'capabilities' => 6,
                'risk_level' => 'Medium',
                'description' => 'สิทธิ์ในการจัดการผู้ใช้และอนุมัติ'
            ],
            'user' => [
                'total_users' => User::where('role', 'user')->count(),
                'capabilities' => 6,
                'risk_level' => 'Low',
                'description' => 'สิทธิ์พื้นฐานในการใช้งาน'
            ]
        ];

        // Recent role changes
        $recentChanges = DB::table('role_change_logs as rcl')
                          ->join('users as u', 'rcl.user_id', '=', 'u.id')
                          ->join('users as changer', 'rcl.changed_by', '=', 'changer.id')
                          ->select([
                              'rcl.*',
                              'u.first_name as user_first_name',
                              'u.last_name as user_last_name',
                              'changer.first_name as changer_first_name',
                              'changer.last_name as changer_last_name'
                          ])
                          ->orderBy('rcl.created_at', 'desc')
                          ->limit(10)
                          ->get();

        return view('admin.roles.permissions', compact('permissionSummary', 'recentChanges'));
    }

    /**
     * Bulk role update
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:user,admin,super_admin',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $userIds = $request->user_ids;
        $newRole = $request->role;
        $reason = $request->reason;

        // Prevent changing own role
        if (in_array(Auth::id(), $userIds)) {
            return redirect()->back()
                           ->with('error', 'ไม่สามารถเปลี่ยนบทบาทของตนเองได้');
        }

        $users = User::whereIn('id', $userIds)->get();
        $updated = 0;

        foreach ($users as $user) {
            $oldRole = $user->role;
            
            if ($oldRole !== $newRole) {
                $user->update(['role' => $newRole]);

                // Log the role change
                DB::table('role_change_logs')->insert([
                    'user_id' => $user->id,
                    'changed_by' => Auth::id(),
                    'old_role' => $oldRole,
                    'new_role' => $newRole,
                    'reason' => $reason,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Send notification
                $user->notify(new \App\Notifications\RoleChangedNotification($oldRole, $newRole, $reason));
                
                $updated++;
            }
        }

        return redirect()->back()
                       ->with('success', "อัปเดตบทบาทของผู้ใช้ {$updated} คน เรียบร้อยแล้ว");
    }

    /**
     * Get role statistics for API
     */
    public function roleStatistics(): \Illuminate\Http\JsonResponse
    {
        $stats = User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->role => $item->count];
                    });

        $totalUsers = User::count();
        
        return response()->json([
            'total_users' => $totalUsers,
            'role_distribution' => $stats,
            'percentages' => [
                'super_admin' => $totalUsers > 0 ? round(($stats['super_admin'] ?? 0) / $totalUsers * 100, 1) : 0,
                'admin' => $totalUsers > 0 ? round(($stats['admin'] ?? 0) / $totalUsers * 100, 1) : 0,
                'user' => $totalUsers > 0 ? round(($stats['user'] ?? 0) / $totalUsers * 100, 1) : 0,
            ]
        ]);
    }
}