<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SessionManagementService;
use App\Models\{User, UserSession, SessionLog};
use Illuminate\Support\Facades\{Auth, Log};

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionManagementService $sessionService)
    {
        $this->middleware(['auth', 'role:admin,super_admin']);
        $this->sessionService = $sessionService;
    }

    /**
     * แสดงหน้าจัดการ sessions ของ Admin
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'active');
        $days = $request->get('days', 7);
        
        // สถิติรวม
        $statistics = $this->sessionService->getSessionStatistics($days);
        
        // รายการ sessions
        $sessionsQuery = UserSession::with('user')
                                   ->when($search, function ($query, $search) {
                                       return $query->whereHas('user', function ($q) use ($search) {
                                           $q->where('username', 'like', "%{$search}%")
                                             ->orWhere('email', 'like', "%{$search}%");
                                       })->orWhere('ip_address', 'like', "%{$search}%");
                                   })
                                   ->when($status === 'active', function ($query) {
                                       return $query->active();
                                   })
                                   ->when($status === 'expired', function ($query) {
                                       return $query->where('is_active', false);
                                   })
                                   ->orderBy('last_activity', 'desc');

        $sessions = $sessionsQuery->paginate(20);

        // จำกัดการแสดงเฉพาะ users ที่ Admin สามารถจัดการได้
        if (Auth::user()->role === 'admin') {
            $sessions = $sessionsQuery->whereHas('user', function ($query) {
                $query->where('role', 'user');
            })->paginate(20);
        }

        return view('admin.sessions.index', compact(
            'sessions', 
            'statistics', 
            'search', 
            'status', 
            'days'
        ));
    }

    /**
     * ดูรายละเอียด session ของผู้ใช้
     */
    public function show(User $user)
    {
        // ตรวจสอบสิทธิ์การเข้าถึง
        if (!$this->canManageUser($user)) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงข้อมูลผู้ใช้นี้');
        }

        $activeSessions = $this->sessionService->getUserActiveSessions($user->id);
        $sessionHistory = $this->sessionService->getUserSessionHistory($user->id, 30);
        $sessionLogs = $this->sessionService->getSessionLogs($user->id, 30);
        
        $statistics = [
            'total_sessions' => $activeSessions->count(),
            'online_sessions' => $activeSessions->filter(fn($s) => $s->isOnline())->count(),
            'trusted_devices' => $activeSessions->where('is_trusted', true)->count(),
            'suspicious_sessions' => $activeSessions->where('is_trusted', false)->count()
        ];

        return view('admin.sessions.show', compact(
            'user', 
            'activeSessions', 
            'sessionHistory', 
            'sessionLogs', 
            'statistics'
        ));
    }

    /**
     * บังคับออกจากระบบผู้ใช้
     */
    public function forceLogout(Request $request, User $user)
    {
        if (!$this->canManageUser($user)) {
            abort(403, 'ไม่มีสิทธิ์จัดการผู้ใช้นี้');
        }

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $count = $this->sessionService->terminateAllUserSessions(
            $user->id,
            $request->reason,
            Auth::id()
        );

        Log::info('Admin forced user logout', [
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
            'sessions_terminated' => $count,
            'reason' => $request->reason
        ]);

        return back()->with('success', "บังคับออกจากระบบผู้ใช้ {$user->username} จำนวน {$count} sessions เรียบร้อยแล้ว");
    }

    /**
     * ปิด session เฉพาะ
     */
    public function terminateSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'reason' => 'nullable|string|max:255'
        ]);

        $session = UserSession::where('session_id', $request->session_id)
                              ->with('user')
                              ->first();

        if (!$session) {
            return back()->with('error', 'ไม่พบ session ที่ระบุ');
        }

        if (!$this->canManageUser($session->user)) {
            abort(403, 'ไม่มีสิทธิ์จัดการผู้ใช้นี้');
        }

        $this->sessionService->terminateSession(
            $request->session_id,
            $request->reason ?: 'Terminated by admin',
            Auth::id()
        );

        return back()->with('success', 'ปิด session เรียบร้อยแล้ว');
    }

    /**
     * ล้าง sessions ที่หมดอายุ
     */
    public function cleanupExpired()
    {
        $count = $this->sessionService->cleanupExpiredSessions();
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "ล้าง sessions หมดอายุ {$count} รายการเรียบร้อยแล้ว"
        ]);
    }

    /**
     * รายงาน session activities
     */
    public function report(Request $request)
    {
        $days = $request->get('days', 30);
        $type = $request->get('type', 'overview');
        
        $statistics = $this->sessionService->getSessionStatistics($days);
        
        // ข้อมูลเพิ่มเติมสำหรับรายงาน
        $report = [
            'daily_sessions' => UserSession::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                          ->where('created_at', '>=', now()->subDays($days))
                                          ->groupBy('date')
                                          ->orderBy('date')
                                          ->get(),
            'top_users' => UserSession::selectRaw('user_id, COUNT(*) as session_count')
                                     ->with('user:id,username,email')
                                     ->where('created_at', '>=', now()->subDays($days))
                                     ->groupBy('user_id')
                                     ->orderByDesc('session_count')
                                     ->limit(10)
                                     ->get(),
            'device_breakdown' => UserSession::selectRaw('device_type, COUNT(*) as count')
                                            ->where('created_at', '>=', now()->subDays($days))
                                            ->groupBy('device_type')
                                            ->get(),
            'location_breakdown' => UserSession::selectRaw('location_country, COUNT(*) as count')
                                              ->where('created_at', '>=', now()->subDays($days))
                                              ->whereNotNull('location_country')
                                              ->groupBy('location_country')
                                              ->orderByDesc('count')
                                              ->limit(10)
                                              ->get()
        ];

        return view('admin.sessions.report', compact('statistics', 'report', 'days', 'type'));
    }

    /**
     * ส่งออกรายงาน sessions
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $days = $request->get('days', 30);
        
        $sessions = UserSession::with('user')
                              ->where('created_at', '>=', now()->subDays($days))
                              ->orderBy('created_at', 'desc')
                              ->get();

        if ($format === 'csv') {
            return $this->exportAsCsv($sessions);
        }
        
        return back()->with('error', 'รูปแบบการส่งออกไม่ถูกต้อง');
    }

    /**
     * Private: ตรวจสอบสิทธิ์การจัดการผู้ใช้
     */
    private function canManageUser(User $user): bool
    {
        $currentUser = Auth::user();
        
        // Super Admin จัดการได้ทุกคน
        if ($currentUser->role === 'super_admin') {
            return true;
        }
        
        // Admin จัดการได้เฉพาะ users
        if ($currentUser->role === 'admin' && $user->role === 'user') {
            return true;
        }
        
        return false;
    }

    /**
     * Private: ส่งออกเป็น CSV
     */
    private function exportAsCsv($sessions)
    {
        $filename = 'session-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($sessions) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'User',
                'Email',
                'IP Address',
                'Device',
                'Platform',
                'Browser',
                'Location',
                'Login Time',
                'Last Activity',
                'Status',
                'Trusted'
            ]);

            // Data rows
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->user->username ?? 'N/A',
                    $session->user->email ?? 'N/A',
                    $session->ip_address,
                    $session->device_info,
                    $session->platform,
                    $session->browser,
                    $session->location,
                    $session->login_at->format('Y-m-d H:i:s'),
                    $session->last_activity->format('Y-m-d H:i:s'),
                    $session->is_active ? 'Active' : 'Inactive',
                    $session->is_trusted ? 'Yes' : 'No'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
