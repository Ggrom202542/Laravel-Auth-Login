<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SessionManagementService;
use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionManagementService $sessionService)
    {
        $this->middleware('auth');
        $this->sessionService = $sessionService;
    }

    /**
     * แสดงหน้าจัดการ sessions ของผู้ใช้
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get sessions with pagination from database
        $sessions = UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->paginate(10);
            
        $currentSession = $this->sessionService->getCurrentSession($user->id);
        $sessionHistory = $this->sessionService->getUserSessionHistory($user->id, 30);
        
        // Get all sessions for statistics
        $allSessions = UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        $statistics = [
            'total_sessions' => $allSessions->count(),
            'current_session' => $currentSession ? $currentSession->session_id : null,
            'online_devices' => $allSessions->where('last_activity', '>=', now()->subMinutes(5))->count(),
            'trusted_devices' => $allSessions->where('is_trusted', true)->count(),
            'suspicious_sessions' => $allSessions->where('is_suspicious', true)->count()
        ];

        return view('user.sessions.index', compact(
            'sessions', 
            'currentSession', 
            'sessionHistory', 
            'statistics'
        ));
    }

    /**
     * ออกจากระบบจาก device อื่น
     */
    public function logoutOtherDevices(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();
        
        $count = $this->sessionService->terminateOtherSessions(
            $user->id, 
            $currentSessionId, 
            'Logged out from other devices by user'
        );

        return back()->with('success', "ออกจากระบบจาก {$count} อุปกรณ์อื่นเรียบร้อยแล้ว");
    }

    /**
     * ออกจากระบบจาก session ที่เฉพาะเจาะจง
     */
    public function terminateSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $user = Auth::user();
        $sessionId = $request->session_id;
        
        // ตรวจสอบว่า session นี้เป็นของผู้ใช้คนนี้
        $session = \App\Models\UserSession::where('session_id', $sessionId)
                                          ->where('user_id', $user->id)
                                          ->where('is_active', true)
                                          ->first();

        if (!$session) {
            return back()->with('error', 'ไม่พบ session ที่ระบุ');
        }

        // ไม่ให้ logout session ปัจจุบัน
        if ($sessionId === session()->getId()) {
            return back()->with('error', 'ไม่สามารถออกจากระบบ session ปัจจุบันได้');
        }

        $this->sessionService->terminateSession(
            $sessionId, 
            'Terminated by user', 
            $user->id
        );

        return back()->with('success', 'ออกจากระบบจากอุปกรณ์ดังกล่าวเรียบร้อยแล้ว');
    }

    /**
     * เครื่องหมายอุปกรณ์เป็น trusted
     */
    public function trustDevice(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $user = Auth::user();
        $session = \App\Models\UserSession::where('session_id', $request->session_id)
                                          ->where('user_id', $user->id)
                                          ->first();

        if (!$session) {
            return back()->with('error', 'ไม่พบ session ที่ระบุ');
        }

        $session->update(['is_trusted' => true]);

        return back()->with('success', 'ทำเครื่องหมายอุปกรณ์เป็นที่เชื่อถือได้แล้ว');
    }

    /**
     * ยกเลิกการเชื่อถืออุปกรณ์
     */
    public function untrustDevice(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $user = Auth::user();
        $session = \App\Models\UserSession::where('session_id', $request->session_id)
                                          ->where('user_id', $user->id)
                                          ->first();

        if (!$session) {
            return back()->with('error', 'ไม่พบ session ที่ระบุ');
        }

        $session->update(['is_trusted' => false]);

        return back()->with('success', 'ยกเลิกการเชื่อถืออุปกรณ์แล้ว');
    }

    /**
     * ดูประวัติ session logs
     */
    public function logs()
    {
        $user = Auth::user();
        $logs = $this->sessionService->getSessionLogs($user->id, 30);
        
        return view('user.sessions.logs', compact('logs'));
    }

    /**
     * API: ดึงข้อมูล sessions แบบ real-time
     */
    public function apiGetSessions()
    {
        $user = Auth::user();
        $sessions = $this->sessionService->getUserActiveSessions($user->id);
        
        return response()->json([
            'sessions' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'device_info' => $session->device_info,
                    'location' => $session->location,
                    'last_activity' => $session->last_activity->diffForHumans(),
                    'is_current' => $session->is_current,
                    'is_trusted' => $session->is_trusted,
                    'is_online' => $session->isOnline()
                ];
            })
        ]);
    }
}
