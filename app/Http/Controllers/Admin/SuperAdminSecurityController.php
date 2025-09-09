<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\IpRestriction;
use App\Models\LoginAttempt;
use App\Models\AdminSession;
use App\Models\SystemSetting;
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
        // สถิติความปลอดภัยระดับระบบ
        $securityStats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'locked_accounts' => User::where('account_locked', true)->count(),
            'admin_accounts' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'suspicious_logins_today' => LoginAttempt::where('is_suspicious', true)
                ->whereDate('attempted_at', today())->count(),
            'failed_attempts_today' => LoginAttempt::where('status', 'failed')
                ->whereDate('attempted_at', today())->count(),
            'blocked_ips' => IpRestriction::where('type', 'blacklist')
                ->where('is_active', true)->count(),
            'whitelisted_ips' => IpRestriction::where('type', 'whitelist')
                ->where('is_active', true)->count(),
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
            'total_devices' => AdminSession::count(),
            'trusted_devices' => AdminSession::where('is_trusted', true)->count(),
            'suspicious_devices' => AdminSession::where('is_suspicious', true)->count(),
            'active_devices' => AdminSession::where('last_activity', '>', now()->subHours(24))->count(),
        ];

        // รายการอุปกรณ์ทั้งหมดในระบบ
        $allDevices = AdminSession::with('user')
            ->orderBy('last_activity', 'desc')
            ->paginate(20);

        // อุปกรณ์ที่น่าสงสัย
        $suspiciousDevices = AdminSession::where('is_suspicious', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.super-admin.security.devices', compact(
            'deviceStats', 
            'allDevices', 
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
                ->where('is_active', true)->count(),
            'active_whitelist' => IpRestriction::where('type', 'whitelist')
                ->where('is_active', true)->count(),
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
            'flagged_users' => User::where('security_alerts', '>', 0)->count(),
            'automated_blocks' => IpRestriction::where('is_automatic', true)->count(),
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
            'ip_restriction_enabled' => SystemSetting::getValue('ip_restriction_enabled', true),
            'suspicious_login_detection' => SystemSetting::getValue('suspicious_login_detection', true),
        ];

        return view('admin.super-admin.security.policies', compact('policies'));
    }

    /**
     * อัปเดต Security Policies
     */
    public function updateSecurityPolicies(Request $request)
    {
        $request->validate([
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:300|max:3600',
            'session_timeout' => 'required|integer|min:15|max:480',
            'password_min_length' => 'required|integer|min:6|max:32',
            'password_require_uppercase' => 'boolean',
            'password_require_lowercase' => 'boolean',
            'password_require_numbers' => 'boolean',
            'password_require_symbols' => 'boolean',
        ]);

        // อัปเดตการตั้งค่าความปลอดภัย
        $policies = $request->only([
            'max_login_attempts', 'lockout_duration', 'session_timeout',
            'password_min_length', 'password_require_uppercase',
            'password_require_lowercase', 'password_require_numbers',
            'password_require_symbols', 'ip_restriction_enabled',
            'suspicious_login_detection'
        ]);

        foreach ($policies as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => is_bool($value) ? 'boolean' : 'string']
            );
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
        $lockedAccounts = User::where('account_locked', true)->count();
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
            'locked_accounts' => User::where('account_locked', true)->count(),
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
                    ->where('is_active', true)->count(),
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
                    'is_active' => true,
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
}
