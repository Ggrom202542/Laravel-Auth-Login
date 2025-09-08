<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\AccountLockoutService;
use App\Services\IpManagementService;
use App\Services\SuspiciousLoginService;
use App\Models\IpRestriction;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\{Log, DB};

class SecurityController extends Controller
{
    protected $lockoutService;
    protected $ipManagementService;
    protected $suspiciousLoginService;

    public function __construct(
        AccountLockoutService $lockoutService, 
        IpManagementService $ipManagementService,
        SuspiciousLoginService $suspiciousLoginService
    ) {
        $this->middleware(['auth', 'check.role:admin,super_admin']);
        $this->lockoutService = $lockoutService;
        $this->ipManagementService = $ipManagementService;
        $this->suspiciousLoginService = $suspiciousLoginService;
    }

    /**
     * แสดงหน้าจัดการความปลอดภัย
     */
    public function index()
    {
        $lockedAccounts = $this->lockoutService->getLockedAccounts();
        
        $statistics = [
            'total_locked' => $lockedAccounts->count(),
            'locked_today' => $lockedAccounts->where('locked_at', '>=', now()->startOfDay())->count(),
            'high_failed_attempts' => User::where('failed_login_attempts', '>=', 3)->count()
        ];

        return view('admin.security.index', compact('lockedAccounts', 'statistics'));
    }

    /**
     * ปลดล็อกบัญชีผู้ใช้
     */
    public function unlockAccount(Request $request, User $user)
    {
        if (!$this->lockoutService->isAccountLocked($user)) {
            return back()->with('error', 'บัญชีนี้ไม่ได้ถูกล็อก');
        }

        $reason = $request->input('reason', 'Manual unlock by admin');
        $this->lockoutService->adminUnlockAccount($user, $reason);

        return back()->with('success', "ปลดล็อกบัญชี {$user->username} เรียบร้อยแล้ว");
    }

    /**
     * ล็อกบัญชีผู้ใช้ด้วยตนเอง
     */
    public function lockAccount(Request $request, User $user)
    {
        if ($this->lockoutService->isAccountLocked($user)) {
            return back()->with('error', 'บัญชีนี้ถูกล็อกอยู่แล้ว');
        }

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $this->lockoutService->adminLockAccount($user, $request->reason);

        return back()->with('success', "ล็อกบัญชี {$user->username} เรียบร้อยแล้ว");
    }

    /**
     * รีเซ็ตความพยายาม login ที่ล้มเหลว
     */
    public function resetFailedAttempts(User $user)
    {
        $this->lockoutService->resetFailedAttempts($user);

        return back()->with('success', "รีเซ็ตความพยายาม login ของ {$user->username} เรียบร้อยแล้ว");
    }

    /**
     * ดูรายละเอียดความปลอดภัยของผู้ใช้
     */
    public function userSecurityDetails(User $user)
    {
        $lockoutStatus = $this->lockoutService->getLockoutStatus($user);
        
        $securityData = [
            'lockout_status' => $lockoutStatus,
            'last_login_at' => $user->last_login_at,
            'last_login_ip' => $user->last_login_ip,
            'last_failed_login_at' => $user->last_failed_login_at,
            'failed_attempts' => $user->failed_login_attempts,
            'trusted_ips' => json_decode($user->trusted_ips, true) ?? [],
            'security_settings' => [
                'ip_restriction' => $user->enable_ip_restriction,
                'device_verification' => $user->require_device_verification
            ]
        ];

        return view('admin.security.user-details', compact('user', 'securityData'));
    }

    /**
     * อัปเดตการตั้งค่าความปลอดภัยของผู้ใช้
     */
    public function updateUserSecurity(Request $request, User $user)
    {
        $request->validate([
            'enable_ip_restriction' => 'boolean',
            'require_device_verification' => 'boolean',
            'trusted_ips' => 'nullable|string'
        ]);

        $trustedIps = [];
        if ($request->trusted_ips) {
            $ips = array_map('trim', explode(',', $request->trusted_ips));
            $trustedIps = array_filter($ips, function($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP);
            });
        }

        $user->update([
            'enable_ip_restriction' => $request->boolean('enable_ip_restriction'),
            'require_device_verification' => $request->boolean('require_device_verification'),
            'trusted_ips' => json_encode($trustedIps)
        ]);

        Log::info('User security settings updated', [
            'user_id' => $user->id,
            'updated_by' => auth()->user()->email,
            'changes' => $request->only(['enable_ip_restriction', 'require_device_verification', 'trusted_ips'])
        ]);

        return back()->with('success', 'อัปเดตการตั้งค่าความปลอดภัยเรียบร้อยแล้ว');
    }

    /**
     * รายงานความปลอดภัย
     */
    public function securityReport()
    {
        $dateRange = request()->get('days', 7);
        $startDate = now()->subDays($dateRange);

        $report = [
            'failed_logins' => User::where('last_failed_login_at', '>=', $startDate)
                ->selectRaw('DATE(last_failed_login_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'locked_accounts' => User::where('locked_at', '>=', $startDate)
                ->selectRaw('DATE(locked_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'top_failed_ips' => DB::table('users')
                ->where('last_failed_login_at', '>=', $startDate)
                ->whereNotNull('last_login_ip')
                ->selectRaw('last_login_ip as ip, COUNT(*) as count')
                ->groupBy('last_login_ip')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
                
            'summary' => [
                'total_failed_attempts' => User::where('last_failed_login_at', '>=', $startDate)->sum('failed_login_attempts'),
                'accounts_locked' => User::where('locked_at', '>=', $startDate)->count(),
                'unique_failed_ips' => User::where('last_failed_login_at', '>=', $startDate)
                    ->whereNotNull('last_login_ip')
                    ->distinct('last_login_ip')
                    ->count()
            ]
        ];

        return view('admin.security.report', compact('report', 'dateRange'));
    }

    /**
     * ล้างล็อกที่หมดอายุแล้ว
     */
    public function cleanupExpiredLocks()
    {
        $count = $this->lockoutService->cleanupExpiredLocks();
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "ล้างล็อกหมดอายุ {$count} บัญชีเรียบร้อยแล้ว"
        ]);
    }

    // IP Management Methods

    /**
     * แสดงหน้า IP Management
     */
    public function ipManagement()
    {
        $ipRestrictions = $this->ipManagementService->getRestrictions();
        $statistics = $this->ipManagementService->getStatistics();

        return view('admin.security.ip-management', compact('ipRestrictions', 'statistics'));
    }

    /**
     * เพิ่ม IP ลง blacklist
     */
    public function addToBlacklist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now'
        ]);

        $ipRecord = $this->ipManagementService->addToBlacklist(
            $request->ip_address,
            $request->reason,
            $request->description,
            $request->expires_at ? \Carbon\Carbon::parse($request->expires_at) : null
        );

        return back()->with('success', "เพิ่ม IP {$request->ip_address} ลง blacklist เรียบร้อยแล้ว");
    }

    /**
     * เพิ่ม IP ลง whitelist
     */
    public function addToWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $ipRecord = $this->ipManagementService->addToWhitelist(
            $request->ip_address,
            $request->reason,
            $request->description
        );

        return back()->with('success', "เพิ่ม IP {$request->ip_address} ลง whitelist เรียบร้อยแล้ว");
    }

    /**
     * ลบ IP restriction
     */
    public function removeIpRestriction(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip'
        ]);

        $removed = $this->ipManagementService->removeRestriction($request->ip_address);

        if ($removed) {
            return back()->with('success', "ลบ restriction สำหรับ IP {$request->ip_address} เรียบร้อยแล้ว");
        }

        return back()->with('error', "ไม่พบ restriction สำหรับ IP {$request->ip_address}");
    }

    /**
     * ดูรายละเอียด IP
     */
    public function ipDetails(string $ip)
    {
        $ipRecord = IpRestriction::where('ip_address', $ip)->first();
        
        if (!$ipRecord) {
            return back()->with('error', 'ไม่พบข้อมูล IP นี้');
        }

        // อัปเดตข้อมูลทางภูมิศาสตร์
        $this->ipManagementService->updateGeographicInfo($ipRecord);

        return view('admin.security.ip-details', compact('ipRecord'));
    }

    /**
     * รายงาน IP Activity
     */
    public function ipReport(Request $request)
    {
        $days = $request->input('days', 7);
        $report = $this->ipManagementService->getActivityReport($days);

        return view('admin.security.ip-report', compact('report', 'days'));
    }

    /**
     * ล้าง IP restrictions ที่หมดอายุ
     */
    public function cleanupExpiredIpRestrictions()
    {
        $count = $this->ipManagementService->cleanupExpiredRestrictions();
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "ล้าง IP restrictions หมดอายุ {$count} รายการเรียบร้อยแล้ว"
        ]);
    }

    // Suspicious Login Detection Methods

    /**
     * แสดงหน้า Suspicious Login Detection
     */
    public function suspiciousLogins()
    {
        $suspiciousLogins = LoginAttempt::suspicious()
                                       ->with(['user', 'investigator'])
                                       ->orderBy('attempted_at', 'desc')
                                       ->paginate(20);

        $statistics = $this->suspiciousLoginService->getDetectionStatistics();

        return view('admin.security.suspicious-logins', compact('suspiciousLogins', 'statistics'));
    }

    /**
     * ดูรายละเอียด Login Attempt
     */
    public function loginAttemptDetails(LoginAttempt $loginAttempt)
    {
        $loginAttempt->load(['user', 'investigator']);
        
        // ดึงข้อมูลที่เกี่ยวข้อง
        $relatedAttempts = LoginAttempt::where('ip_address', $loginAttempt->ip_address)
                                     ->orWhere('device_fingerprint', $loginAttempt->device_fingerprint)
                                     ->where('id', '!=', $loginAttempt->id)
                                     ->orderBy('attempted_at', 'desc')
                                     ->limit(10)
                                     ->get();

        return view('admin.security.login-attempt-details', compact('loginAttempt', 'relatedAttempts'));
    }

    /**
     * ทำเครื่องหมายว่าตรวจสอบแล้ว
     */
    public function markAsInvestigated(LoginAttempt $loginAttempt)
    {
        $loginAttempt->markAsInvestigated(auth()->id());

        return back()->with('success', 'ทำเครื่องหมายการตรวจสอบเรียบร้อยแล้ว');
    }

    /**
     * รายงาน Suspicious Login Detection
     */
    public function suspiciousLoginReport(Request $request)
    {
        $days = $request->input('days', 7);
        $statistics = $this->suspiciousLoginService->getDetectionStatistics();

        return view('admin.security.suspicious-login-report', compact('statistics', 'days'));
    }

    /**
     * ดูประวัติการเข้าสู่ระบบของผู้ใช้
     */
    public function userLoginHistory(User $user)
    {
        $days = request()->input('days', 30);
        $report = $this->suspiciousLoginService->getUserSecurityReport($user->id, $days);

        $loginAttempts = LoginAttempt::where('user_id', $user->id)
                                   ->orderBy('attempted_at', 'desc')
                                   ->paginate(20);

        return view('admin.security.user-login-history', compact('user', 'report', 'loginAttempts', 'days'));
    }

    /**
     * บล็อกกิจกรรมที่น่าสงสัย
     */
    public function blockSuspiciousActivity(Request $request)
    {
        $request->validate([
            'login_attempt_id' => 'required|exists:login_attempts,id',
            'action' => 'required|in:block_ip,lock_account,revoke_device'
        ]);

        $loginAttempt = LoginAttempt::findOrFail($request->login_attempt_id);
        $actions = [];

        switch ($request->action) {
            case 'block_ip':
                $this->ipManagementService->addToBlacklist(
                    $loginAttempt->ip_address,
                    'Blocked due to suspicious login activity',
                    "Login attempt ID: {$loginAttempt->id}"
                );
                $actions[] = 'IP blocked';
                break;

            case 'lock_account':
                if ($loginAttempt->user) {
                    $this->lockoutService->adminLockAccount(
                        $loginAttempt->user,
                        'Account locked due to suspicious activity'
                    );
                    $actions[] = 'Account locked';
                }
                break;

            case 'revoke_device':
                if ($loginAttempt->device_fingerprint && $loginAttempt->user) {
                    \App\Models\UserDevice::revokeDeviceTrust(
                        $loginAttempt->device_fingerprint,
                        $loginAttempt->user_id
                    );
                    $actions[] = 'Device trust revoked';
                }
                break;
        }

        // บันทึกการดำเนินการ
        $loginAttempt->addSecurityAction($request->action, [
            'performed_by' => auth()->id(),
            'reason' => 'Manual intervention by admin'
        ]);

        return back()->with('success', 'ดำเนินการเรียบร้อยแล้ว: ' . implode(', ', $actions));
    }
}
