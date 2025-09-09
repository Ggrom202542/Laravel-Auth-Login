<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SessionManagementService;
use App\Models\{User, UserSession, SessionLog};
use Illuminate\Support\Facades\{Auth, Log, DB, Config};

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionManagementService $sessionService)
    {
        $this->middleware(['auth', 'role:super_admin']);
        $this->sessionService = $sessionService;
    }

    /**
     * หน้าจัดการ sessions ระดับ Super Admin
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'active');
        $role = $request->get('role', 'all');
        $days = $request->get('days', 7);
        
        // สถิติรวมระบบ
        $systemStats = $this->getSystemStatistics($days);
        
        // รายการ sessions ทั้งหมด
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
                                   ->when($role !== 'all', function ($query) use ($role) {
                                       return $query->whereHas('user', function ($q) use ($role) {
                                           $q->where('role', $role);
                                       });
                                   })
                                   ->orderBy('last_activity', 'desc');

        $sessions = $sessionsQuery->paginate(50);

        return view('super-admin.sessions.index', compact(
            'sessions', 
            'systemStats', 
            'search', 
            'status', 
            'role',
            'days'
        ));
    }

    /**
     * แดชบอร์ดสถิติ session
     */
    public function dashboard()
    {
        $stats = $this->getSystemStatistics(30);
        
        $chartData = [
            'daily_sessions' => $this->getDailySessionData(30),
            'role_distribution' => $this->getRoleDistribution(),
            'device_stats' => $this->getDeviceStatistics(),
            'location_stats' => $this->getLocationStatistics()
        ];

        return view('super-admin.sessions.dashboard', compact('stats', 'chartData'));
    }

    /**
     * การจัดการ session แบบมวล
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:terminate,cleanup,trust,untrust',
            'user_ids' => 'required_unless:action,cleanup|array',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'nullable|string|max:255'
        ]);

        $action = $request->action;
        $userIds = $request->user_ids ?? [];
        $reason = $request->reason ?: 'Bulk action by Super Admin';
        
        $results = [];
        
        switch ($action) {
            case 'terminate':
                foreach ($userIds as $userId) {
                    $count = $this->sessionService->terminateAllUserSessions($userId, $reason, Auth::id());
                    $results[] = "User ID {$userId}: {$count} sessions terminated";
                }
                break;
                
            case 'cleanup':
                $count = $this->sessionService->cleanupExpiredSessions();
                $results[] = "Cleaned up {$count} expired sessions";
                break;
                
            case 'trust':
                $this->bulkTrustSessions($userIds, true);
                $results[] = "Trusted devices for " . count($userIds) . " users";
                break;
                
            case 'untrust':
                $this->bulkTrustSessions($userIds, false);
                $results[] = "Untrusted devices for " . count($userIds) . " users";
                break;
        }

        Log::info('Super Admin bulk session action', [
            'admin_id' => Auth::id(),
            'action' => $action,
            'affected_users' => $userIds,
            'reason' => $reason,
            'results' => $results
        ]);

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'การดำเนินการเรียบร้อยแล้ว'
        ]);
    }

    /**
     * การตั้งค่าระบบ session
     */
    public function settings()
    {
        $config = [
            'session_lifetime' => config('session.lifetime'),
            'max_concurrent_sessions' => config('session.max_concurrent', 5),
            'auto_cleanup_enabled' => config('session.auto_cleanup', true),
            'suspicious_activity_monitoring' => config('session.monitor_suspicious', true),
            'geo_location_tracking' => config('session.track_location', true),
            'device_trust_period' => config('session.device_trust_days', 30),
        ];

        return view('super-admin.sessions.settings', compact('config'));
    }

    /**
     * อัปเดตการตั้งค่า session
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'session_lifetime' => 'required|integer|min:5|max:1440',
            'max_concurrent_sessions' => 'required|integer|min:1|max:50',
            'auto_cleanup_enabled' => 'boolean',
            'suspicious_activity_monitoring' => 'boolean',
            'geo_location_tracking' => 'boolean',
            'device_trust_period' => 'required|integer|min:1|max:365',
        ]);

        // อัปเดตไฟล์ config (ในระบบจริงควรใช้ database config)
        $this->updateConfigFile($request->all());

        Log::info('Session settings updated by Super Admin', [
            'admin_id' => Auth::id(),
            'settings' => $request->all()
        ]);

        return back()->with('success', 'อัปเดตการตั้งค่าเรียบร้อยแล้ว');
    }

    /**
     * รายงานละเอียดของระบบ
     */
    public function systemReport(Request $request)
    {
        $period = $request->get('period', 'month');
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => 30
        };

        $report = [
            'overview' => $this->getSystemStatistics($days),
            'user_activity' => $this->getUserActivityReport($days),
            'security_alerts' => $this->getSecurityAlerts($days),
            'performance_metrics' => $this->getPerformanceMetrics($days),
            'geographic_distribution' => $this->getGeographicReport($days),
            'device_analysis' => $this->getDeviceAnalysis($days),
            'chart_data' => $this->getChartData($days)
        ];

        // Get recent sessions for the table
        $recentSessions = UserSession::with(['user'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(20)
                                    ->get();

        return view('super-admin.sessions.system-report', compact('report', 'period', 'days', 'recentSessions'));
    }

    /**
     * การจัดการ session เรียลไทม์
     */
    public function realtime()
    {
        $activeUsers = UserSession::with(['user'])
            ->where('is_active', true)
            ->where('last_activity', '>', now()->subMinutes(5))
            ->get();
            
        $realtimeStats = [
            'current_users' => $activeUsers->count(),
            'total_sessions' => UserSession::where('is_active', true)->count(),
            'suspicious_sessions' => UserSession::where('is_suspicious', true)->count(),
            'new_sessions_today' => UserSession::whereDate('created_at', today())->count(),
        ];

        return view('super-admin.sessions.realtime', compact('activeUsers', 'realtimeStats'));
    }

    /**
     * Get real-time session data for AJAX requests
     */
    public function realtimeData(Request $request)
    {
        $role = $request->get('role');
        $status = $request->get('status');

        // Base query for sessions
        $sessionsQuery = UserSession::with('user')
                                   ->when($role, function ($query) use ($role) {
                                       return $query->whereHas('user', function ($q) use ($role) {
                                           $q->where('role', $role);
                                       });
                                   })
                                   ->when($status === 'online', function ($query) {
                                       return $query->where('last_activity', '>=', now()->subMinutes(5));
                                   })
                                   ->when($status === 'active', function ($query) {
                                       return $query->active();
                                   })
                                   ->when($status === 'suspicious', function ($query) {
                                       return $query->where('is_suspicious', true);
                                   })
                                   ->orderBy('last_activity', 'desc');

        $sessions = $sessionsQuery->limit(50)->get();

        // Add computed properties
        $sessions->transform(function ($session) {
            $session->is_online = $session->isOnline();
            $session->duration_minutes = $session->login_at->diffInMinutes($session->last_activity);
            return $session;
        });

        // Real-time stats
        $stats = [
            'active_sessions' => UserSession::active()->count(),
            'online_users' => UserSession::where('last_activity', '>=', now()->subMinutes(5))->distinct('user_id')->count(),
            'avg_duration' => UserSession::active()->avg(DB::raw('TIMESTAMPDIFF(MINUTE, login_at, last_activity)')) ?: 0,
            'suspicious_sessions' => UserSession::where('is_suspicious', true)->active()->count()
        ];

        // Recent activities (last 20)
        $activities = SessionLog::with('user')
                                ->orderBy('created_at', 'desc')
                                ->limit(20)
                                ->get()
                                ->map(function ($log) {
                                    return [
                                        'user_name' => $log->user->username ?? 'System',
                                        'action' => $log->action,
                                        'description' => $this->getActivityDescription($log->action),
                                        'created_at' => $log->created_at->toISOString()
                                    ];
                                });

        // Security alerts (suspicious activities in last hour)
        $alerts = SessionLog::where('action', 'like', '%suspicious%')
                            ->where('created_at', '>=', now()->subHour())
                            ->with('user')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->map(function ($log) {
                                $details = json_decode($log->details, true) ?? [];
                                return [
                                    'title' => 'Suspicious Activity Detected',
                                    'description' => "User: {$log->user->username}, IP: {$log->ip_address}",
                                    'created_at' => $log->created_at->toISOString(),
                                    'severity' => 'warning'
                                ];
                            });

        return response()->json([
            'stats' => $stats,
            'sessions' => $sessions,
            'activities' => $activities,
            'alerts' => $alerts
        ]);
    }

    /**
     * Get human-readable activity description
     */
    private function getActivityDescription($action)
    {
        $descriptions = [
            'login' => 'logged in',
            'logout' => 'logged out',
            'session_created' => 'started a new session',
            'session_terminated' => 'session was terminated',
            'suspicious_activity' => 'performed suspicious activity',
            'device_trusted' => 'trusted a device',
            'device_untrusted' => 'untrusted a device'
        ];

        return $descriptions[$action] ?? $action;
    }

    /**
     * ส่งออกข้อมูล sessions ขั้นสูง
     */
    public function advancedExport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,json,xml',
            'period' => 'required|in:week,month,quarter,year',
            'include_logs' => 'boolean',
            'include_security_events' => 'boolean'
        ]);

        $format = $request->get('format');
        $days = match($request->get('period')) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365
        };

        $data = [
            'sessions' => UserSession::with('user')->where('created_at', '>=', now()->subDays($days))->get(),
            'logs' => $request->include_logs ? SessionLog::where('created_at', '>=', now()->subDays($days))->get() : null,
            'security_events' => $request->include_security_events ? $this->getSecurityEvents($days) : null
        ];

        return match($format) {
            'csv' => $this->exportAsAdvancedCsv($data),
            'json' => $this->exportAsJson($data),
            'xml' => $this->exportAsXml($data)
        };
    }

    /**
     * Private Methods
     */
    
    private function getSystemStatistics(int $days): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'total_sessions' => DB::table('user_sessions')->where('created_at', '>=', $startDate)->count(),
            'active_sessions' => UserSession::where('is_active', true)->count(),
            'unique_users' => DB::table('user_sessions')->where('created_at', '>=', $startDate)->distinct()->count('user_id'),
            'online_users' => UserSession::where('last_activity', '>=', now()->subMinutes(5))->distinct()->count('user_id'),
            'suspicious_sessions' => UserSession::where('is_suspicious', true)->count(),
            'total_logins' => DB::table('session_logs')->where('action', 'login')->where('created_at', '>=', $startDate)->count(),
            'failed_logins' => DB::table('session_logs')->where('action', 'failed_login')->where('created_at', '>=', $startDate)->count(),
            'avg_session_duration' => $this->getAverageSessionDuration($days),
            'peak_concurrent_users' => $this->getPeakConcurrentUsers($days)
        ];
    }

    private function getDailySessionData(int $days): array
    {
        return DB::table('user_sessions')
                 ->selectRaw('DATE(created_at) as date, COUNT(*) as sessions, COUNT(DISTINCT user_id) as unique_users')
                 ->where('created_at', '>=', now()->subDays($days))
                 ->groupBy('date')
                 ->orderBy('date')
                 ->get()
                 ->toArray();
    }

    private function getRoleDistribution(): array
    {
        return User::selectRaw('role, COUNT(*) as count')
                   ->groupBy('role')
                   ->get()
                   ->pluck('count', 'role')
                   ->toArray();
    }

    private function getDeviceStatistics(): array
    {
        return DB::table('user_sessions')
                 ->selectRaw('device_type, COUNT(*) as count')
                 ->where('created_at', '>=', now()->subDays(30))
                 ->groupBy('device_type')
                 ->get()
                 ->pluck('count', 'device_type')
                 ->toArray();
    }

    private function getLocationStatistics(): array
    {
        return DB::table('user_sessions')
                 ->selectRaw('location_country, COUNT(*) as count')
                 ->where('created_at', '>=', now()->subDays(30))
                 ->whereNotNull('location_country')
                 ->groupBy('location_country')
                 ->orderByDesc('count')
                 ->limit(10)
                 ->get()
                 ->pluck('count', 'location_country')
                 ->toArray();
    }

    private function bulkTrustSessions(array $userIds, bool $trusted): void
    {
        UserSession::whereIn('user_id', $userIds)
                   ->where('is_active', true)
                   ->update(['is_trusted' => $trusted]);
    }

    private function getSuspiciousActivities(): array
    {
        return SessionLog::where('action', 'suspicious_activity')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->toArray();
    }

    private function getSystemLoad(): array
    {
        return [
            'cpu_usage' => sys_getloadavg()[0] ?? 0,
            'memory_usage' => memory_get_usage(true),
            'active_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0
        ];
    }

    private function getAverageSessionDuration(int $days): float
    {
        // ใช้ last_activity แทน logout_at เนื่องจากอาจยังไม่มี logout_at
        $result = DB::table('user_sessions')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('login_at')
            ->whereNotNull('last_activity')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, login_at, last_activity)) as avg_minutes')
            ->first();
        
        return $result && $result->avg_minutes ? round((float) $result->avg_minutes, 2) : 0;
    }

    private function getPeakConcurrentUsers(int $days): int
    {
        // ค่าประมาณจากข้อมูลที่มี - ใช้ last_activity แทน created_at
        $dailyPeaks = DB::table('user_sessions')
            ->where('last_activity', '>=', now()->subDays($days))
            ->selectRaw('DATE(last_activity) as date, COUNT(DISTINCT user_id) as user_count')
            ->groupBy('date')
            ->get();
            
        return $dailyPeaks->max('user_count') ?? 0;
    }

    private function updateConfigFile(array $settings): void
    {
        // ในระบบจริงควรใช้ database config หรือ cache
        // นี่เป็นตัวอย่างการอัปเดต runtime config
        Config::set('session.lifetime', $settings['session_lifetime']);
        Config::set('session.max_concurrent', $settings['max_concurrent_sessions']);
        Config::set('session.auto_cleanup', $settings['auto_cleanup_enabled'] ?? false);
        Config::set('session.monitor_suspicious', $settings['suspicious_activity_monitoring'] ?? false);
        Config::set('session.track_location', $settings['geo_location_tracking'] ?? false);
        Config::set('session.device_trust_days', $settings['device_trust_period']);
    }

    private function exportAsAdvancedCsv(array $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'advanced-session-report-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Sessions data
            fputcsv($file, ['=== SESSION DATA ===']);
            fputcsv($file, ['User', 'Email', 'Role', 'IP', 'Device', 'Location', 'Login', 'Last Activity', 'Status', 'Trusted']);
            
            foreach ($data['sessions'] as $session) {
                fputcsv($file, [
                    $session->user->username ?? 'N/A',
                    $session->user->email ?? 'N/A',
                    $session->user->role ?? 'N/A',
                    $session->ip_address,
                    ($session->device_name ?? '') . ' - ' . ($session->platform ?? '') . ' - ' . ($session->browser ?? ''),
                    ($session->location_city ?? '') . ', ' . ($session->location_country ?? ''),
                    $session->login_at->format('Y-m-d H:i:s'),
                    $session->last_activity->format('Y-m-d H:i:s'),
                    $session->is_active ? 'Active' : 'Inactive',
                    $session->is_trusted ? 'Yes' : 'No'
                ]);
            }
            
            // Add logs if included
            if ($data['logs']) {
                fputcsv($file, []);
                fputcsv($file, ['=== LOG DATA ===']);
                fputcsv($file, ['User', 'Action', 'IP', 'Details', 'Timestamp']);
                
                foreach ($data['logs'] as $log) {
                    fputcsv($file, [
                        $log->user->username ?? 'N/A',
                        $log->action,
                        $log->ip_address,
                        $log->details,
                        $log->created_at->format('Y-m-d H:i:s')
                    ]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportAsJson(array $data): \Illuminate\Http\JsonResponse
    {
        return response()->json($data)
                        ->header('Content-Disposition', 'attachment; filename="session-report-' . now()->format('Y-m-d-H-i-s') . '.json"');
    }

    /**
     * แสดงรายละเอียด session เฉพาะ
     */
    public function show(UserSession $session)
    {
        $session->load(['user', 'logs' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(50);
        }]);

        // ประวัติ sessions ของ user นี้
        $userSessions = UserSession::where('user_id', $session->user_id)
                                  ->orderBy('login_at', 'desc')
                                  ->limit(10)
                                  ->get();

        // ตรวจสอบ suspicious activities
        $suspiciousActivities = SessionLog::where('session_id', $session->id)
                                         ->where('action', 'like', '%suspicious%')
                                         ->orderBy('created_at', 'desc')
                                         ->get();

        // สถิติการใช้งานของ user
        $userStats = [
            'total_sessions' => UserSession::where('user_id', $session->user_id)->count(),
            'active_sessions' => UserSession::where('user_id', $session->user_id)->active()->count(),
            'unique_devices' => UserSession::where('user_id', $session->user_id)
                                          ->distinct('device_fingerprint')
                                          ->count('device_fingerprint'),
            'unique_ips' => UserSession::where('user_id', $session->user_id)
                                      ->distinct('ip_address')
                                      ->count('ip_address'),
            'last_login' => UserSession::where('user_id', $session->user_id)
                                      ->orderBy('login_at', 'desc')
                                      ->first()?->login_at,
        ];

        return view('super-admin.sessions.show', compact(
            'session', 
            'userSessions', 
            'suspiciousActivities',
            'userStats'
        ));
    }

    /**
     * ยุติ session เฉพาะ
     */
    public function terminate(UserSession $session, Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $reason = $request->reason ?: 'Terminated by Super Admin';
        
        try {
            // บันทึก log ก่อนยุติ session
            SessionLog::create([
                'session_id' => $session->id,
                'user_id' => $session->user_id,
                'action' => 'session_terminated',
                'details' => json_encode([
                    'terminated_by' => Auth::id(),
                    'reason' => $reason,
                    'original_ip' => $session->ip_address,
                    'device_info' => ($session->device_name ?? '') . ' - ' . ($session->platform ?? '') . ' - ' . ($session->browser ?? '')
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // ยุติ session
            $session->terminate($reason, Auth::id());

            Log::info('Super Admin terminated session', [
                'admin_id' => Auth::id(),
                'session_id' => $session->id,
                'user_id' => $session->user_id,
                'reason' => $reason
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Session ถูกยุติเรียบร้อยแล้ว'
                ]);
            }

            return redirect()->route('super-admin.sessions.index')
                           ->with('success', 'Session ถูกยุติเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            Log::error('Failed to terminate session', [
                'admin_id' => Auth::id(),
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการยุติ session'
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'เกิดข้อผิดพลาดในการยุติ session');
        }
    }

    /**
     * Trust/Untrust device
     */
    public function trustDevice(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'trusted' => 'sometimes|boolean'
        ]);

        $sessionId = $request->session_id;
        $trusted = $request->input('trusted', true);

        try {
            $session = UserSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            $session->update(['is_trusted' => $trusted]);

            // บันทึก log
            SessionLog::create([
                'session_id' => $session->id,
                'user_id' => $session->user_id,
                'action' => $trusted ? 'device_trusted' : 'device_untrusted',
                'details' => json_encode([
                    'changed_by' => Auth::id(),
                    'device_info' => ($session->device_name ?? '') . ' - ' . ($session->platform ?? '') . ' - ' . ($session->browser ?? ''),
                    'ip_address' => $session->ip_address
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            Log::info('Super Admin changed device trust status', [
                'admin_id' => Auth::id(),
                'session_id' => $session->id,
                'user_id' => $session->user_id,
                'trusted' => $trusted
            ]);

            return response()->json([
                'success' => true,
                'message' => $trusted ? 'Device marked as trusted' : 'Device trust removed'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to change device trust status', [
                'admin_id' => Auth::id(),
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะความเชื่อถือ'
            ], 500);
        }
    }

    private function exportAsXml(array $data): \Illuminate\Http\Response
    {
        $xml = new \SimpleXMLElement('<session_report/>');
        
        $sessionsNode = $xml->addChild('sessions');
        foreach ($data['sessions'] as $session) {
            $sessionNode = $sessionsNode->addChild('session');
            $sessionNode->addChild('user', htmlspecialchars($session->user->username ?? 'N/A'));
            $sessionNode->addChild('email', htmlspecialchars($session->user->email ?? 'N/A'));
            $sessionNode->addChild('ip_address', htmlspecialchars($session->ip_address));
            $sessionNode->addChild('device_info', htmlspecialchars(($session->device_name ?? '') . ' - ' . ($session->platform ?? '') . ' - ' . ($session->browser ?? '')));
            $sessionNode->addChild('login_at', $session->login_at->toISOString());
            $sessionNode->addChild('last_activity', $session->last_activity->toISOString());
            $sessionNode->addChild('is_active', $session->is_active ? 'true' : 'false');
            $sessionNode->addChild('is_trusted', $session->is_trusted ? 'true' : 'false');
        }

        $filename = 'session-report-' . now()->format('Y-m-d-H-i-s') . '.xml';
        
        return response($xml->asXML())
                ->header('Content-Type', 'application/xml')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get user activity report
     */
    private function getUserActivityReport(int $days): array
    {
        $startDate = now()->subDays($days);

        // Most active users
        $activeUsers = UserSession::with('user')
                                 ->where('login_at', '>=', $startDate)
                                 ->selectRaw('user_id, COUNT(*) as session_count, AVG(TIMESTAMPDIFF(MINUTE, login_at, COALESCE(logout_at, last_activity))) as avg_duration')
                                 ->groupBy('user_id')
                                 ->orderBy('session_count', 'desc')
                                 ->limit(10)
                                 ->get();

        // Login patterns by hour
        $loginPatterns = UserSession::where('login_at', '>=', $startDate)
                                   ->selectRaw('HOUR(login_at) as hour, COUNT(*) as count')
                                   ->groupBy('hour')
                                   ->orderBy('hour')
                                   ->pluck('count', 'hour')
                                   ->toArray();

        // Fill missing hours
        for ($i = 0; $i < 24; $i++) {
            if (!isset($loginPatterns[$i])) {
                $loginPatterns[$i] = 0;
            }
        }
        ksort($loginPatterns);

        // Session duration distribution
        $durationRanges = [
            '0-5m' => UserSession::where('login_at', '>=', $startDate)
                                ->whereRaw('TIMESTAMPDIFF(MINUTE, login_at, COALESCE(logout_at, last_activity)) BETWEEN 0 AND 5')
                                ->count(),
            '5-30m' => UserSession::where('login_at', '>=', $startDate)
                                 ->whereRaw('TIMESTAMPDIFF(MINUTE, login_at, COALESCE(logout_at, last_activity)) BETWEEN 6 AND 30')
                                 ->count(),
            '30m-2h' => UserSession::where('login_at', '>=', $startDate)
                                  ->whereRaw('TIMESTAMPDIFF(MINUTE, login_at, COALESCE(logout_at, last_activity)) BETWEEN 31 AND 120')
                                  ->count(),
            '2h+' => UserSession::where('login_at', '>=', $startDate)
                               ->whereRaw('TIMESTAMPDIFF(MINUTE, login_at, COALESCE(logout_at, last_activity)) > 120')
                               ->count(),
        ];

        return [
            'active_users' => $activeUsers,
            'login_patterns' => $loginPatterns,
            'duration_distribution' => $durationRanges,
            'total_sessions' => UserSession::where('login_at', '>=', $startDate)->count(),
            'unique_users' => UserSession::where('login_at', '>=', $startDate)->distinct('user_id')->count()
        ];
    }

    /**
     * Get security alerts
     */
    private function getSecurityAlerts(int $days): array
    {
        $startDate = now()->subDays($days);

        $suspiciousActivities = SessionLog::where('created_at', '>=', $startDate)
                                         ->where('action', 'like', '%suspicious%')
                                         ->with('user')
                                         ->orderBy('created_at', 'desc')
                                         ->get();

        $failedLogins = SessionLog::where('created_at', '>=', $startDate)
                                 ->where('action', 'login_failed')
                                 ->count();

        $multipleLocations = UserSession::where('login_at', '>=', $startDate)
                                       ->selectRaw('user_id, COUNT(DISTINCT location_country) as country_count')
                                       ->groupBy('user_id')
                                       ->having('country_count', '>', 1)
                                       ->with('user')
                                       ->get();

        return [
            'suspicious_activities' => $suspiciousActivities,
            'failed_login_attempts' => $failedLogins,
            'multiple_location_users' => $multipleLocations,
            'high_risk_sessions' => UserSession::where('is_suspicious', true)
                                              ->where('login_at', '>=', $startDate)
                                              ->count()
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(int $days): array
    {
        $startDate = now()->subDays($days);

        // Average session duration
        $avgDuration = UserSession::where('login_at', '>=', $startDate)
                                 ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, login_at, COALESCE(logout_at, last_activity))'));

        // Peak concurrent sessions (calculate based on active sessions per day)
        $peakSessions = UserSession::where('login_at', '>=', $startDate)
                                  ->selectRaw('DATE(login_at) as date, COUNT(*) as sessions_count')
                                  ->groupBy('date')
                                  ->orderBy('sessions_count', 'desc')
                                  ->first();

        // Session creation rate
        $sessionsByDay = UserSession::where('login_at', '>=', $startDate)
                                   ->selectRaw('DATE(login_at) as date, COUNT(*) as count')
                                   ->groupBy('date')
                                   ->orderBy('date')
                                   ->pluck('count', 'date')
                                   ->toArray();

        return [
            'avg_session_duration' => round($avgDuration ?: 0, 2),
            'peak_concurrent_sessions' => $peakSessions->sessions_count ?? 0,
            'sessions_by_day' => $sessionsByDay,
            'total_data_transferred' => 0, // Could be implemented with actual tracking
            'system_uptime' => '99.9%' // Could be implemented with actual monitoring
        ];
    }

    /**
     * Get geographic distribution report
     */
    private function getGeographicReport(int $days): array
    {
        $startDate = now()->subDays($days);

        $countryStats = UserSession::where('login_at', '>=', $startDate)
                                  ->selectRaw('location_country, COUNT(*) as session_count, COUNT(DISTINCT user_id) as unique_users')
                                  ->whereNotNull('location_country')
                                  ->groupBy('location_country')
                                  ->orderBy('session_count', 'desc')
                                  ->limit(10)
                                  ->get();

        $cityStats = UserSession::where('login_at', '>=', $startDate)
                                ->selectRaw('location_city, location_country, COUNT(*) as session_count')
                                ->whereNotNull('location_city')
                                ->groupBy('location_city', 'location_country')
                                ->orderBy('session_count', 'desc')
                                ->limit(15)
                                ->get();

        return [
            'top_countries' => $countryStats,
            'top_cities' => $cityStats,
            'total_countries' => UserSession::where('login_at', '>=', $startDate)
                                           ->distinct('location_country')
                                           ->whereNotNull('location_country')
                                           ->count(),
            'international_sessions' => UserSession::where('login_at', '>=', $startDate)
                                                  ->where('location_country', '!=', 'Thailand')
                                                  ->count()
        ];
    }

    /**
     * Get device analysis report
     */
    private function getDeviceAnalysis(int $days): array
    {
        $startDate = now()->subDays($days);

        $deviceTypes = UserSession::where('login_at', '>=', $startDate)
                                 ->selectRaw('
                                     CASE 
                                         WHEN device_type = "mobile" THEN "Mobile"
                                         WHEN device_type = "tablet" THEN "Tablet"
                                         ELSE "Desktop"
                                     END as device_type,
                                     COUNT(*) as count
                                 ')
                                 ->groupBy('device_type')
                                 ->pluck('count', 'device_type')
                                 ->toArray();

        $browsers = UserSession::where('login_at', '>=', $startDate)
                              ->selectRaw('browser, COUNT(*) as count')
                              ->whereNotNull('browser')
                              ->groupBy('browser')
                              ->orderBy('count', 'desc')
                              ->limit(10)
                              ->pluck('count', 'browser')
                              ->toArray();

        $platforms = UserSession::where('login_at', '>=', $startDate)
                                ->selectRaw('platform, COUNT(*) as count')
                                ->whereNotNull('platform')
                                ->groupBy('platform')
                                ->orderBy('count', 'desc')
                                ->limit(10)
                                ->pluck('count', 'platform')
                                ->toArray();

        return [
            'device_types' => $deviceTypes,
            'browsers' => $browsers,
            'platforms' => $platforms,
            'trusted_devices' => UserSession::where('login_at', '>=', $startDate)
                                           ->where('is_trusted', true)
                                           ->count(),
            'unique_devices' => UserSession::where('login_at', '>=', $startDate)
                                          ->distinct('session_id')
                                          ->count()
        ];
    }

    /**
     * Get chart data for visualization
     */
    private function getChartData(int $days): array
    {
        $startDate = now()->subDays($days);

        // Sessions by day chart data
        $dailySessions = DB::table('user_sessions')
                          ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                          ->where('created_at', '>=', $startDate)
                          ->groupBy('date')
                          ->orderBy('date')
                          ->get();

        $sessionLabels = [];
        $sessionData = [];
        foreach ($dailySessions as $day) {
            $sessionLabels[] = date('M j', strtotime($day->date));
            $sessionData[] = $day->count;
        }

        // Sessions by role chart data
        $roleData = DB::table('user_sessions')
                     ->join('users', 'user_sessions.user_id', '=', 'users.id')
                     ->selectRaw('users.role, COUNT(*) as count')
                     ->where('user_sessions.created_at', '>=', $startDate)
                     ->groupBy('users.role')
                     ->get();

        $roleLabels = [];
        $roleValues = [];
        foreach ($roleData as $role) {
            $roleLabels[] = ucfirst($role->role);
            $roleValues[] = $role->count;
        }

        return [
            'sessions' => [
                'labels' => $sessionLabels,
                'data' => $sessionData
            ],
            'roles' => [
                'labels' => $roleLabels,
                'data' => $roleValues
            ]
        ];
    }
}
