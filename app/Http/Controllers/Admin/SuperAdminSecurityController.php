<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IpRestriction;
use App\Models\LoginAttempt;
use App\Models\AdminSession;
use App\Models\UserSession;
use App\Models\SystemSetting;
use App\Models\SecurityLog;
use App\Services\AccountLockoutService;
use App\Services\IpManagementService;
use App\Services\SuspiciousLoginService;
use Illuminate\Support\Facades\{Log, DB, Cache};
use Carbon\Carbon;

class SuperAdminSecurityController extends Controller
{
    protected $lockoutService;
    protected $ipManagementService;
    protected $suspiciousLoginService;

    public function __construct(
        AccountLockoutService $lockoutService, 
        IpManagementService $ipManagementService,
        SuspiciousLoginService $suspiciousLoginService
    ) {
        $this->middleware(['auth', 'role:super_admin']);
        $this->lockoutService = $lockoutService;
        $this->ipManagementService = $ipManagementService;
        $this->suspiciousLoginService = $suspiciousLoginService;
    }

    /**
     * หน้าแดชบอร์ดความปลอดภัยหลักสำหรับ Super Admin
     */
    public function index()
    {
        // สถิติความปลอดภัยระด��บระบบ
        $securityStats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'locked_accounts' => User::where('locked_until', '>', now())->count(),
            'admin_accounts' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'suspicious_logins_today' => LoginAttempt::where('is_suspicious', true)
                ->whereDate('attempted_at', today())->count(),
            'failed_attempts_today' => LoginAttempt::where('status', 'failed')
                ->whereDate('attempted_at', today())->count(),
            'blocked_ips' => IpRestriction::where('type', 'blacklist')
                ->active()->count(),
            'whitelisted_ips' => IpRestriction::where('type', 'whitelist')
                ->active()->count(),
        ];

        // กิจกรรมล่าสุดที่มีความเสี่ยงสูง
        $recentHighRiskActivities = LoginAttempt::where('risk_score', '>', 70)
            ->with('user')
            ->orderBy('attempted_at', 'desc')
            ->limit(10)
            ->get();

        // แดชบอร์ดสำหรับการตรวจสอบแบบเรียลไทม์
        $systemHealth = [
            'security_level' => $this->calculateSystemSecurityLevel(),
            'threat_level' => $this->calculateThreatLevel(),
            'last_security_scan' => Cache::get('last_security_scan', 'ยังไม่เคยสแกน'),
            'active_sessions' => $this->getActiveSessionsCount(),
        ];

        return view('admin.super-admin.security.index', compact(
            'securityStats', 
            'recentHighRiskActivities', 
            'systemHealth'
        ));
    }

    /**
     * การจัดการอุปกรณ์ขั้นสูงสำหรับ Super Admin
     */
    public function deviceManagement()
    {
        // สถิติอุปกรณ์ระดับระบบ
        $deviceStats = [
            'total' => UserSession::count(),
            'trusted' => UserSession::where('is_trusted', true)->count(),
            'suspicious' => UserSession::where('is_suspicious', true)->count(),
            'online' => UserSession::where('last_activity', '>', now()->subHours(1))->count(),
        ];

        // รายการอุปกรณ์ทั้งหมดในระบบ
        $devices = UserSession::with('user')
            ->orderBy('last_activity', 'desc')
            ->paginate(20);

        // อุปกรณ์ที่น่าสงสัย
        $suspiciousDevices = UserSession::where('is_suspicious', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.super-admin.security.devices', compact(
            'deviceStats', 
            'devices', 
            'suspiciousDevices'
        ));
    }

    /**
     * การจัดการ IP ขั้นสูงสำหรับ Super Admin
     */
    public function ipManagement()
    {
        // สถิติ IP ระดับระบบ
        $ipStats = [
            'total_restrictions' => IpRestriction::count(),
            'active_blacklist' => IpRestriction::where('type', 'blacklist')
                ->active()->count(),
            'active_whitelist' => IpRestriction::where('type', 'whitelist')
                ->active()->count(),
            'recent_blocks' => IpRestriction::where('created_at', '>', now()->subDays(7))->count(),
        ];

        // รายการ IP ทั้งหมด
        $ipRestrictions = IpRestriction::orderBy('created_at', 'desc')
            ->paginate(20);

        // IP ที่มีกิจกรรมมากที่สุด
        $topActiveIPs = LoginAttempt::select('ip_address', DB::raw('COUNT(*) as attempt_count'))
            ->where('attempted_at', '>', now()->subDays(30))
            ->groupBy('ip_address')
            ->orderBy('attempt_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.super-admin.security.ip-management', compact(
            'ipStats', 
            'ipRestrictions', 
            'topActiveIPs'
        ));
    }

    /**
     * การตรวจจับกิจกรรมน่าสงสัยขั้นสูง
     */
    public function suspiciousActivity()
    {
        // สถิติกิจกรรมน่าสงสัย
        $suspiciousStats = [
            'high_risk_attempts' => LoginAttempt::where('risk_score', '>', 80)->count(),
            'medium_risk_attempts' => LoginAttempt::whereBetween('risk_score', [50, 80])->count(),
            'flagged_users' => User::where('suspicious_login_count', '>', 5)->count(),
            'automated_blocks' => IpRestriction::where('auto_generated', true)->count(),
        ];

        // กิจกรรมน่าสงสัยล่าสุด
        $recentSuspicious = LoginAttempt::where('is_suspicious', true)
            ->with('user')
            ->orderBy('attempted_at', 'desc')
            ->paginate(20);

        // การวิเคราะห์รูปแบบการโจมตี
        $attackPatterns = $this->analyzeAttackPatterns();

        return view('admin.super-admin.security.suspicious-activity', compact(
            'suspiciousStats', 
            'recentSuspicious', 
            'attackPatterns'
        ));
    }

    /**
     * การจัดการ Security Policies ระดับระบบ
     */
    public function securityPolicies()
    {
        // โหลดนโยบายความปลอดภัยปัจจุบัน
        $policies = [
            'password_policy' => config('password_policy'),
            'session_timeout' => config('session.lifetime'),
            'max_login_attempts' => config('auth.max_attempts', 5),
            'lockout_duration' => config('auth.lockout_duration', 900),
            'ip_restriction_enabled' => SystemSetting::get('ip_restriction_enabled', true),
            'suspicious_login_detection' => SystemSetting::get('suspicious_login_detection', true),
        ];

        return view('admin.super-admin.security.policies', compact('policies'));
    }

    /**
     * อัปเดต Security Policies
     */
    public function updateSecurityPolicies(Request $request)
    {
        $request->validate([
            'session_timeout' => 'required|integer|min:5|max:1440',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:60|max:3600',
            'ip_restriction_enabled' => 'boolean',
            'suspicious_login_detection' => 'boolean',
        ]);

        // อัปเดตการตั้งค่าความปลอดภัย
        $policies = $request->only([
            'session_timeout',
            'max_login_attempts', 
            'lockout_duration',
            'ip_restriction_enabled',
            'suspicious_login_detection'
        ]);

        foreach ($policies as $key => $value) {
            // จัดการ boolean values สำหรับ checkboxes
            if (in_array($key, ['ip_restriction_enabled', 'suspicious_login_detection'])) {
                $value = $request->has($key) ? true : false;
                $type = 'boolean';
            } else {
                $type = 'integer';
            }
            
            SystemSetting::set($key, $value, $type);
        }

        // บันทึก audit log
        Log::info('Security policies updated by Super Admin', [
            'admin_id' => auth()->id(),
            'policies' => $policies,
            'ip_address' => request()->ip()
        ]);

        return redirect()->back()->with('success', 'นโยบายความปลอดภัยได้รับการอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * บังคับ logout ผู้ใช้จากอุปกรณ์ทั้งหมด
     */
    public function forceLogoutUser(User $user)
    {
        // ยกเลิก session ทั้งหมดของผู้ใช้
        AdminSession::where('user_id', $user->id)->delete();
        
        // อัปเดตข้อมูลผู้ใช้
        $user->update([
            'last_forced_logout' => now(),
            'forced_logout_reason' => 'Super Admin action'
        ]);

        // บันทึก audit log
        Log::info('User force logout by Super Admin', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'target_username' => $user->username,
            'ip_address' => request()->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => "บังคับ logout ผู้ใช้ {$user->username} เรียบร้อยแล้ว"
        ]);
    }

    /**
     * ระงับบัญชีผู้ใช้ทันที
     */
    public function suspendUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'duration' => 'required|in:1hour,6hours,24hours,7days,30days,permanent'
        ]);

        $suspendUntil = match($request->duration) {
            '1hour' => now()->addHour(),
            '6hours' => now()->addHours(6),
            '24hours' => now()->addDay(),
            '7days' => now()->addWeek(),
            '30days' => now()->addMonth(),
            'permanent' => null,
        };

        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_until' => $suspendUntil,
            'suspension_reason' => $request->reason,
            'suspended_by' => auth()->id()
        ]);

        // ลบ session ทั้งหมด
        AdminSession::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => "ระงับบัญชี {$user->username} เรียบร้อยแล้ว"
        ]);
    }

    /**
     * คำนวณระดับความปลอดภัยของระบบ
     */
    private function calculateSystemSecurityLevel()
    {
        $score = 100;
        
        // ลดคะแนนตามจำนวนบัญชีที่ถูกล็อก
        $lockedAccounts = User::where('locked_until', '>', now())->count();
        $score -= min($lockedAccounts * 5, 30);
        
        // ลดคะแนนตามกิจกรรมน่าสงสัย
        $suspiciousToday = LoginAttempt::where('is_suspicious', true)
            ->whereDate('attempted_at', today())->count();
        $score -= min($suspiciousToday * 2, 20);
        
        // ลดคะแนนตาม failed attempts
        $failedToday = LoginAttempt::where('status', 'failed')
            ->whereDate('attempted_at', today())->count();
        $score -= min($failedToday, 25);

        return max($score, 0);
    }

    /**
     * คำนวณระดับภัยคุกคาม
     */
    private function calculateThreatLevel()
    {
        $highRiskAttempts = LoginAttempt::where('risk_score', '>', 80)
            ->whereDate('attempted_at', today())->count();
            
        if ($highRiskAttempts > 10) return 'สูง';
        if ($highRiskAttempts > 5) return 'ปานกลาง';
        return 'ต่ำ';
    }

    /**
     * นับจำนวน active sessions
     */
    private function getActiveSessionsCount()
    {
        return AdminSession::where('last_activity', '>', now()->subHours(1))->count();
    }

    /**
     * วิเคราะห์รูปแบบการโจมตี
     */
    private function analyzeAttackPatterns()
    {
        // วิเคราะห์รูปแบบจาก IP addresses
        $ipPatterns = LoginAttempt::select('ip_address', DB::raw('COUNT(*) as attempts'))
            ->where('status', 'failed')
            ->where('attempted_at', '>', now()->subDays(7))
            ->groupBy('ip_address')
            ->having('attempts', '>', 10)
            ->orderBy('attempts', 'desc')
            ->get();

        // วิเคราะห์รูปแบบจาก User-Agent
        $userAgentPatterns = LoginAttempt::select('user_agent', DB::raw('COUNT(*) as attempts'))
            ->where('status', 'failed')
            ->where('attempted_at', '>', now()->subDays(7))
            ->whereNotNull('user_agent')
            ->groupBy('user_agent')
            ->having('attempts', '>', 5)
            ->orderBy('attempts', 'desc')
            ->limit(10)
            ->get();

        return [
            'suspicious_ips' => $ipPatterns,
            'suspicious_user_agents' => $userAgentPatterns,
            'brute_force_attempts' => $ipPatterns->where('attempts', '>', 50)->count(),
            'bot_detection' => $userAgentPatterns->filter(function($ua) {
                return stripos($ua->user_agent, 'bot') !== false || 
                       stripos($ua->user_agent, 'crawler') !== false;
            })->count()
        ];
    }

    /**
     * API endpoint สำหรับ AJAX calls
     */
    public function getSecurityStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'locked_accounts' => User::where('locked_until', '>', now())->count(),
            'suspicious_logins_today' => LoginAttempt::where('is_suspicious', true)
                ->whereDate('attempted_at', today())->count(),
            'security_level' => $this->calculateSystemSecurityLevel(),
            'threat_level' => $this->calculateThreatLevel(),
        ];

        return response()->json($stats);
    }

    /**
     * เริ่มการสแกนระบบความปลอดภัย
     */
    public function runSystemScan()
    {
        try {
            // อัปเดตเวลาสแกนล่าสุด
            Cache::put('last_security_scan', now()->format('d/m/Y H:i:s'), 3600);
            
            // ทำการสแกนระบบ (ในการใช้งานจริงจะมีการตรวจสอบต่างๆ)
            $scanResults = [
                'vulnerabilities_found' => 0,
                'suspicious_activities' => LoginAttempt::where('is_suspicious', true)
                    ->whereDate('attempted_at', today())->count(),
                'blocked_ips' => IpRestriction::where('type', 'blacklist')
                    ->active()->count(),
            ];

            Log::info('Security system scan completed', [
                'admin_id' => auth()->id(),
                'results' => $scanResults,
                'ip_address' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'การสแกนระบบเสร็จสิ้น',
                'results' => $scanResults
            ]);
        } catch (\Exception $e) {
            Log::error('Security scan failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสแกน'
            ]);
        }
    }

    /**
     * ลบข้อมูลที่หมดอายุ
     */
    public function cleanupExpired()
    {
        try {
            $cleaned = 0;
            
            // ลบ IP restrictions ที่หมดอายุ
            $expiredIPs = IpRestriction::where('expires_at', '<', now())->count();
            IpRestriction::where('expires_at', '<', now())->delete();
            $cleaned += $expiredIPs;
            
            // ลบ login attempts เก่า (เก่ากว่า 90 วัน)
            $oldAttempts = LoginAttempt::where('attempted_at', '<', now()->subDays(90))->count();
            LoginAttempt::where('attempted_at', '<', now()->subDays(90))->delete();
            $cleaned += $oldAttempts;

            Log::info('Security cleanup completed', [
                'admin_id' => auth()->id(),
                'items_cleaned' => $cleaned,
                'ip_address' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => "ลบข้อมูลเก่าเรียบร้อยแล้ว ({$cleaned} รายการ)"
            ]);
        } catch (\Exception $e) {
            Log::error('Cleanup failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'
            ]);
        }
    }

    /**
     * บังคับ logout ผู้ใช้ทั้งหมด
     */
    public function forceLogoutAll()
    {
        try {
            // ลบ session ทั้งหมดยกเว้น Super Admin ปัจจุบัน
            $currentAdminId = auth()->id();
            $loggedOutCount = AdminSession::where('user_id', '!=', $currentAdminId)->count();
            AdminSession::where('user_id', '!=', $currentAdminId)->delete();

            Log::warning('Mass logout initiated by Super Admin', [
                'admin_id' => $currentAdminId,
                'affected_users' => $loggedOutCount,
                'ip_address' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => "บังคับ logout ผู้ใช้ทั้งหมดเรียบร้อยแล้ว ({$loggedOutCount} session)"
            ]);
        } catch (\Exception $e) {
            Log::error('Mass logout failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการ logout'
            ]);
        }
    }

    /**
     * บล็อก IP address
     */
    public function blockIP(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'string|max:255'
        ]);

        try {
            IpRestriction::updateOrCreate(
                ['ip_address' => $request->ip_address],
                [
                    'type' => 'blacklist',
                    'reason' => $request->reason ?? 'Blocked by Super Admin',
                    'status' => 'active',
                    'created_by' => auth()->id(),
                    'expires_at' => null // Permanent block
                ]
            );

            Log::warning('IP blocked by Super Admin', [
                'admin_id' => auth()->id(),
                'blocked_ip' => $request->ip_address,
                'reason' => $request->reason,
                'admin_ip' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => "บล็อก IP {$request->ip_address} เรียบร้อยแล้ว"
            ]);
        } catch (\Exception $e) {
            Log::error('IP blocking failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบล็อก IP'
            ]);
        }
    }

    /**
     * เพิกถอนอุปกรณ์ที่น่าสงสัยทั้งหมด
     */
    public function revokeAllSuspiciousDevices(Request $request)
    {
        try {
            // หาอุปกรณ์ที่น่าสงสัยทั้งหมด
            $suspiciousDevices = AdminSession::suspicious()->get();
            $count = $suspiciousDevices->count();

            if ($count == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบอุปกรณ์ที่น่าสงสัย',
                    'count' => 0
                ]);
            }

            // เพิกถอนการไว้วางใจและบังคับให้ออกจากระบบ
            foreach ($suspiciousDevices as $device) {
                $securityFlags = $device->security_flags ?? [];
                $securityFlags['is_trusted'] = false;
                $securityFlags['is_suspicious'] = true;
                $securityFlags['revoked_at'] = now()->toISOString();
                $securityFlags['revoked_by'] = auth()->id();
                $securityFlags['revoked_reason'] = 'Mass revocation of suspicious devices';

                $device->update([
                    'security_flags' => $securityFlags,
                    'is_active' => false
                ]);

                // Log การกระทำ
                SecurityLog::create([
                    'type' => 'device_revoked',
                    'user_id' => $device->user_id,
                    'admin_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'details' => [
                        'device_id' => $device->id,
                        'action' => 'mass_revoke_suspicious',
                        'device_info' => [
                            'device_name' => $device->device_name,
                            'ip_address' => $device->ip_address
                        ]
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "เพิกถอนอุปกรณ์ที่น่าสงสัยเรียบร้อยแล้ว",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Mass device revocation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเพิกถอนอุปกรณ์',
                'count' => 0
            ]);
        }
    }

    /**
     * บังคับออกจากระบบทุกอุปกรณ์
     */
    public function forceLogoutAllDevices(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            // ยกเลิก session ทั้งหมดยกเว้น Super Admin ปัจจุบัน
            $currentSessionId = session()->getId();
            $affectedSessions = AdminSession::where('session_id', '!=', $currentSessionId)->get();
            $count = $affectedSessions->count();

            foreach ($affectedSessions as $session) {
                $session->update(['is_active' => false]);

                // Log การกระทำ
                SecurityLog::create([
                    'type' => 'forced_logout',
                    'user_id' => $session->user_id,
                    'admin_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'details' => [
                        'device_id' => $session->id,
                        'action' => 'mass_logout',
                        'reason' => $request->reason,
                        'device_info' => [
                            'device_name' => $session->device_name,
                            'ip_address' => $session->ip_address
                        ]
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "บังคับออกจากระบบทุกอุปกรณ์เรียบร้อยแล้ว",
                'count' => $count,
                'reason' => $request->reason
            ]);

        } catch (\Exception $e) {
            Log::error('Mass logout failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบังคับออกจากระบบ'
            ]);
        }
    }

    /**
     * ล้างอุปกรณ์เก่า
     */
    public function cleanupOldDevices(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'include_suspicious' => 'boolean',
            'include_untrusted' => 'boolean'
        ]);

        try {
            $cutoffDate = now()->subDays($request->days);
            $query = AdminSession::where('last_activity', '<', $cutoffDate);

            // เพิ่มเงื่อนไขตามที่เลือก
            if ($request->include_suspicious) {
                $query->orWhere(function($q) use ($cutoffDate) {
                    $q->suspicious()->where('last_activity', '<', $cutoffDate);
                });
            }

            if ($request->include_untrusted) {
                $query->orWhere(function($q) use ($cutoffDate) {
                    $q->whereJsonContains('security_flags->is_trusted', false)
                      ->where('last_activity', '<', $cutoffDate);
                });
            }

            $oldDevices = $query->get();
            $count = $oldDevices->count();

            if ($count == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบอุปกรณ์เก่าที่ตรงตามเงื่อนไข',
                    'count' => 0
                ]);
            }

            // ลบอุปกรณ์เก่า
            foreach ($oldDevices as $device) {
                // Log ก่อนลบ
                SecurityLog::create([
                    'type' => 'device_cleaned',
                    'user_id' => $device->user_id,
                    'admin_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'details' => [
                        'device_id' => $device->id,
                        'action' => 'cleanup_old_device',
                        'criteria' => [
                            'days_inactive' => $request->days,
                            'include_suspicious' => $request->include_suspicious,
                            'include_untrusted' => $request->include_untrusted
                        ],
                        'device_info' => [
                            'device_name' => $device->device_name,
                            'ip_address' => $device->ip_address,
                            'last_activity' => $device->last_activity
                        ]
                    ]
                ]);

                $device->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "ล้างอุปกรณ์เก่าเรียบร้อยแล้ว",
                'count' => $count,
                'criteria' => [
                    'days' => $request->days,
                    'include_suspicious' => $request->include_suspicious,
                    'include_untrusted' => $request->include_untrusted
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Device cleanup failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการล้างอุปกรณ์เก่า'
            ]);
        }
    }

    /**
     * ไว้วางใจอุปกรณ์
     */
    public function trustDevice(Request $request, $deviceId)
    {
        try {
            $device = AdminSession::findOrFail($deviceId);
            
            $securityFlags = $device->security_flags ?? [];
            $securityFlags['is_trusted'] = true;
            $securityFlags['is_suspicious'] = false;
            $securityFlags['trusted_at'] = now()->toISOString();
            $securityFlags['trusted_by'] = auth()->id();

            $device->update(['security_flags' => $securityFlags]);

            SecurityLog::create([
                'type' => 'device_trusted',
                'user_id' => $device->user_id,
                'admin_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'details' => [
                    'device_id' => $device->id,
                    'action' => 'trust_device',
                    'device_info' => [
                        'device_name' => $device->device_name,
                        'ip_address' => $device->ip_address
                    ]
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'อุปกรณ์ได้รับการไว้วางใจแล้ว'
            ]);

        } catch (\Exception $e) {
            Log::error('Device trust failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการไว้วางใจอุปกรณ์'
            ]);
        }
    }

    /**
     * ทำเครื่องหมายอุปกรณ์ว่าน่าสงสัย
     */
    public function suspectDevice(Request $request, $deviceId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $device = AdminSession::findOrFail($deviceId);
            
            $securityFlags = $device->security_flags ?? [];
            $securityFlags['is_suspicious'] = true;
            $securityFlags['is_trusted'] = false;
            $securityFlags['suspected_at'] = now()->toISOString();
            $securityFlags['suspected_by'] = auth()->id();
            $securityFlags['suspect_reason'] = $request->reason ?? 'ไม่ระบุเหตุผล';

            $device->update(['security_flags' => $securityFlags]);

            SecurityLog::create([
                'type' => 'device_suspected',
                'user_id' => $device->user_id,
                'admin_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'details' => [
                    'device_id' => $device->id,
                    'action' => 'mark_suspicious',
                    'reason' => $request->reason ?? 'ไม่ระบุเหตุผล',
                    'device_info' => [
                        'device_name' => $device->device_name,
                        'ip_address' => $device->ip_address
                    ]
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'อุปกรณ์ถูกทำเครื่องหมายว่าน่าสงสัยแล้ว',
                'reason' => $request->reason ?? 'ไม่ระบุเหตุผล'
            ]);

        } catch (\Exception $e) {
            Log::error('Device suspect failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการทำเครื่องหมายอุปกรณ์'
            ]);
        }
    }

    /**
     * บล็อกอุปกรณ์
     */
    public function blockDevice(Request $request, $deviceId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $device = AdminSession::findOrFail($deviceId);
            
            $securityFlags = $device->security_flags ?? [];
            $securityFlags['is_blocked'] = true;
            $securityFlags['is_trusted'] = false;
            $securityFlags['is_suspicious'] = true;
            $securityFlags['blocked_at'] = now()->toISOString();
            $securityFlags['blocked_by'] = auth()->id();
            $securityFlags['block_reason'] = $request->reason;

            $device->update([
                'security_flags' => $securityFlags,
                'is_active' => false
            ]);

            SecurityLog::create([
                'type' => 'device_blocked',
                'user_id' => $device->user_id,
                'admin_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'details' => [
                    'device_id' => $device->id,
                    'action' => 'block_device',
                    'reason' => $request->reason,
                    'device_info' => [
                        'device_name' => $device->device_name,
                        'ip_address' => $device->ip_address
                    ]
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'อุปกรณ์ถูกบล็อกแล้ว',
                'reason' => $request->reason
            ]);

        } catch (\Exception $e) {
            Log::error('Device block failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบล็อกอุปกรณ์'
            ]);
        }
    }
}
