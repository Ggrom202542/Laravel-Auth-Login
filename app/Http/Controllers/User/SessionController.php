<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SessionManagementService;
use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        $currentSessionId = session()->getId();
        
        try {
            // ตรวจสอบรหัสผ่าน
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'รหัสผ่านไม่ถูกต้อง'
                ], 400);
            }

            $count = $this->sessionService->terminateOtherSessions(
                $user->id, 
                $currentSessionId, 
                'Logged out from other devices by user'
            );

            return response()->json([
                'success' => true,
                'message' => "ออกจากระบบจาก {$count} อุปกรณ์อื่นเรียบร้อยแล้ว"
            ]);

        } catch (\Exception $e) {
            Log::error('Logout other devices failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการออกจากระบบอุปกรณ์อื่น'
            ], 500);
        }
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
        
        try {
            // ตรวจสอบว่า session นี้เป็นของผู้ใช้คนนี้
            $session = \App\Models\UserSession::where('session_id', $sessionId)
                                              ->where('user_id', $user->id)
                                              ->where('is_active', true)
                                              ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบ session ที่ระบุ'
                ], 404);
            }

            // ไม่ให้ logout session ปัจจุบัน
            if ($sessionId === session()->getId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถออกจากระบบ session ปัจจุบันได้'
                ], 400);
            }

            $this->sessionService->terminateSession(
                $sessionId, 
                'Terminated by user', 
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'ออกจากระบบจากอุปกรณ์ดังกล่าวเรียบร้อยแล้ว'
            ]);

        } catch (\Exception $e) {
            Log::error('Session termination failed', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการออกจากระบบ'
            ], 500);
        }
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
        
        try {
            $session = \App\Models\UserSession::where('session_id', $request->session_id)
                                              ->where('user_id', $user->id)
                                              ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบ session ที่ระบุ'
                ], 404);
            }

            $session->update(['is_trusted' => true]);

            return response()->json([
                'success' => true,
                'message' => 'ทำเครื่องหมายอุปกรณ์เป็นที่เชื่อถือได้แล้ว'
            ]);

        } catch (\Exception $e) {
            Log::error('Device trust failed', [
                'user_id' => $user->id,
                'session_id' => $request->session_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเชื่อถืออุปกรณ์'
            ], 500);
        }
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
