<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash};
use App\Models\{User, UserDevice, LoginAttempt, IpRestriction, SecurityPolicy};
use App\Services\{SuspiciousLoginService, IpManagementService, AccountLockoutService};
use Carbon\Carbon;

class SecurityController extends Controller
{
    protected $suspiciousLoginService;
    protected $ipManagementService;
    protected $lockoutService;

    public function __construct(
        SuspiciousLoginService $suspiciousLoginService,
        IpManagementService $ipManagementService,
        AccountLockoutService $lockoutService
    ) {
        $this->middleware(['auth', 'role:user,admin,super_admin']);
        $this->suspiciousLoginService = $suspiciousLoginService;
        $this->ipManagementService = $ipManagementService;
        $this->lockoutService = $lockoutService;
    }

    /**
     * แสดง Security Dashboard สำหรับผู้ใช้
     */
    public function index()
    {
        $user = Auth::user();
        
        // ข้อมูลสถิติความปลอดภัย
        $securityStats = $this->getSecurityStatistics($user);
        
        // ข้อมูลอุปกรณ์
        $userDevices = UserDevice::where('user_id', $user->id)
                                ->orderBy('last_seen_at', 'desc')
                                ->take(5)
                                ->get();
        
        // ล็อกอินล่าสุด
        $recentLogins = LoginAttempt::where('user_id', $user->id)
                                  ->where('status', 'success')
                                  ->orderBy('attempted_at', 'desc')
                                  ->take(10)
                                  ->get();
        
        // การพยายามเข้าสู่ระบบที่ผิดปกติ
        $suspiciousAttempts = LoginAttempt::where('user_id', $user->id)
                                        ->where('is_suspicious', true)
                                        ->where('attempted_at', '>=', now()->subDays(30))
                                        ->orderBy('attempted_at', 'desc')
                                        ->take(5)
                                        ->get();

        // Security Alerts ล่าสุด
        $securityAlerts = $this->getSecurityAlerts($user);

        return view('user.security.index', compact(
            'securityStats',
            'userDevices', 
            'recentLogins',
            'suspiciousAttempts',
            'securityAlerts'
        ));
    }

    /**
     * แสดงรายละเอียดอุปกรณ์ของผู้ใช้
     */
    public function devices()
    {
        $user = Auth::user();
        
        $userDevices = UserDevice::where('user_id', $user->id)
                                ->orderBy('last_seen_at', 'desc')
                                ->paginate(10);
        
        $deviceStats = [
            'total_devices' => UserDevice::where('user_id', $user->id)->count(),
            'trusted_devices' => UserDevice::where('user_id', $user->id)->where('is_trusted', true)->count(),
            'active_devices' => UserDevice::where('user_id', $user->id)->where('is_active', true)->count()
        ];

        return view('user.security.devices', compact('userDevices', 'deviceStats'));
    }

    /**
     * แสดงประวัติการเข้าสู่ระบบ
     */
    public function loginHistory()
    {
        $user = Auth::user();
        
        $loginAttempts = LoginAttempt::where('user_id', $user->id)
                                   ->orderBy('attempted_at', 'desc')
                                   ->paginate(20);
        
        $loginStats = [
            'total_attempts' => LoginAttempt::where('user_id', $user->id)->count(),
            'successful_logins' => LoginAttempt::where('user_id', $user->id)->where('status', 'success')->count(),
            'failed_attempts' => LoginAttempt::where('user_id', $user->id)->where('status', 'failed')->count(),
            'suspicious_attempts' => LoginAttempt::where('user_id', $user->id)->where('is_suspicious', true)->count()
        ];

        return view('user.security.login-history', compact('loginAttempts', 'loginStats'));
    }

    /**
     * จัดการอุปกรณ์ที่เชื่อถือได้
     */
    public function trustDevice(Request $request, $deviceId)
    {
        $user = Auth::user();
        
        $device = UserDevice::where('user_id', $user->id)
                           ->where('id', $deviceId)
                           ->firstOrFail();
        
        $device->update([
            'is_trusted' => !$device->is_trusted,
            'trusted_at' => $device->is_trusted ? null : now()
        ]);

        $action = $device->is_trusted ? 'trusted' : 'untrusted';
        
        return response()->json([
            'success' => true,
            'message' => "Device has been {$action} successfully.",
            'is_trusted' => $device->is_trusted
        ]);
    }

    /**
     * ลบอุปกรณ์
     */
    public function removeDevice(Request $request, $deviceId)
    {
        $user = Auth::user();
        
        $device = UserDevice::where('user_id', $user->id)
                           ->where('id', $deviceId)
                           ->firstOrFail();
        
        // ไม่ให้ลบอุปกรณ์ปัจจุบัน
        if ($device->device_fingerprint === $request->fingerprint()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove current device.'
            ], 400);
        }

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device removed successfully.'
        ]);
    }

    /**
     * แสดงการแจ้งเตือนความปลอดภัย
     */
    public function securityAlerts()
    {
        $user = Auth::user();
        
        $alerts = $this->getSecurityAlerts($user, 50);
        
        return view('user.security.alerts', compact('alerts'));
    }

    /**
     * อัปเดตการตั้งค่าความปลอดภัย
     */
    public function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'login_notifications' => 'boolean',
            'suspicious_activity_alerts' => 'boolean',
            'device_management_alerts' => 'boolean'
        ]);

        $user = Auth::user();
        
        // อัปเดตการตั้งค่าใน user preferences
        $preferences = $user->preferences ?? [];
        $preferences['security'] = [
            'login_notifications' => $request->boolean('login_notifications'),
            'suspicious_activity_alerts' => $request->boolean('suspicious_activity_alerts'),
            'device_management_alerts' => $request->boolean('device_management_alerts')
        ];
        
        $user->update(['preferences' => $preferences]);

        return response()->json([
            'success' => true,
            'message' => 'Security settings updated successfully.'
        ]);
    }

    /**
     * ดาวน์โหลดข้อมูลความปลอดภัยส่วนบุคคล
     */
    public function exportSecurityData()
    {
        $user = Auth::user();
        
        $securityData = [
            'user_info' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'export_date' => now()->toISOString()
            ],
            'devices' => UserDevice::where('user_id', $user->id)->get()->toArray(),
            'login_history' => LoginAttempt::where('user_id', $user->id)
                                         ->orderBy('attempted_at', 'desc')
                                         ->take(100)
                                         ->get()
                                         ->toArray(),
            'security_stats' => $this->getSecurityStatistics($user)
        ];

        $filename = "security_data_{$user->username}_" . now()->format('Y-m-d') . ".json";
        
        return response()->json($securityData)
                        ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /**
     * รับสถิติความปลอดภัยของผู้ใช้
     */
    private function getSecurityStatistics($user)
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        return [
            'account_age_days' => $user->created_at->diffInDays(now()),
            'total_logins' => LoginAttempt::where('user_id', $user->id)
                                        ->where('status', 'success')
                                        ->count(),
            'failed_attempts' => LoginAttempt::where('user_id', $user->id)
                                           ->where('status', 'failed')
                                           ->count(),
            'suspicious_logins' => LoginAttempt::where('user_id', $user->id)
                                             ->where('is_suspicious', true)
                                             ->count(),
            'last_login' => LoginAttempt::where('user_id', $user->id)
                                      ->where('status', 'success')
                                      ->latest('attempted_at')
                                      ->first()?->attempted_at,
            'unique_ips_30days' => LoginAttempt::where('user_id', $user->id)
                                             ->where('attempted_at', '>=', $thirtyDaysAgo)
                                             ->distinct('ip_address')
                                             ->count(),
            'devices_count' => UserDevice::where('user_id', $user->id)->count(),
            'trusted_devices_count' => UserDevice::where('user_id', $user->id)
                                                ->where('is_trusted', true)
                                                ->count(),
            'security_score' => $this->calculateSecurityScore($user)
        ];
    }

    /**
     * รับการแจ้งเตือนความปลอดภัย
     */
    private function getSecurityAlerts($user, $limit = 10)
    {
        return LoginAttempt::where('user_id', $user->id)
                          ->where(function($query) {
                              $query->where('is_suspicious', true)
                                    ->orWhere('alert_level', '>=', 'medium');
                          })
                          ->orderBy('attempted_at', 'desc')
                          ->take($limit)
                          ->get()
                          ->map(function($attempt) {
                              return [
                                  'id' => $attempt->id,
                                  'type' => $attempt->is_suspicious ? 'suspicious_login' : 'security_alert',
                                  'message' => $this->generateAlertMessage($attempt),
                                  'severity' => $attempt->alert_level ?? 'low',
                                  'date' => $attempt->attempted_at,
                                  'ip_address' => $attempt->ip_address,
                                  'location' => $attempt->city ? "{$attempt->city}, {$attempt->country_name}" : 'Unknown'
                              ];
                          });
    }

    /**
     * คำนวณคะแนนความปลอดภัย
     */
    private function calculateSecurityScore($user)
    {
        $score = 50; // คะแนนเริ่มต้น
        
        // มี 2FA (+30 คะแนน)
        if ($user->two_factor_enabled) {
            $score += 30;
        }
        
        // จำนวนอุปกรณ์ที่เชื่อถือได้ (+10 คะแนน ต่อเครื่อง, สูงสุด 20)
        $trustedDevices = UserDevice::where('user_id', $user->id)
                                  ->where('is_trusted', true)
                                  ->count();
        $score += min($trustedDevices * 10, 20);
        
        // ไม่มี suspicious logins ใน 30 วันล่าสุด (+20 คะแนน)
        $suspiciousLogins = LoginAttempt::where('user_id', $user->id)
                                      ->where('is_suspicious', true)
                                      ->where('attempted_at', '>=', now()->subDays(30))
                                      ->count();
        if ($suspiciousLogins === 0) {
            $score += 20;
        }
        
        // รหัสผ่านที่แข็งแรง (ถ้ามีการตรวจสอบ) (+10 คะแนน)
        // สามารถเพิ่มการตรวจสอบได้ตามต้องการ
        
        return min($score, 100); // สูงสุด 100 คะแนน
    }

    /**
     * สร้างข้อความแจ้งเตือน
     */
    private function generateAlertMessage($attempt)
    {
        if ($attempt->is_suspicious) {
            $reasons = [];
            
            if ($attempt->risk_factors) {
                foreach ($attempt->risk_factors as $factor) {
                    switch ($factor) {
                        case 'new_ip':
                            $reasons[] = 'login from new IP address';
                            break;
                        case 'unusual_time':
                            $reasons[] = 'login at unusual time';
                            break;
                        case 'new_device':
                            $reasons[] = 'login from new device';
                            break;
                        case 'geographic_anomaly':
                            $reasons[] = 'login from unusual location';
                            break;
                    }
                }
            }
            
            $reason = implode(', ', $reasons) ?: 'suspicious activity detected';
            return "Suspicious login attempt detected: {$reason}";
        }
        
        return "Security alert: {$attempt->alert_level} risk login attempt";
    }

    /**
     * API endpoint to get current security statistics
     */
    public function getSecurityStats()
    {
        $user = Auth::user();
        $stats = $this->getSecurityStatistics($user);
        
        return response()->json([
            'security_score' => $stats['security_score'],
            'total_logins' => $stats['total_logins'],
            'failed_attempts' => $stats['failed_attempts'],
            'devices_count' => $stats['devices_count'],
            'account_age_days' => $stats['account_age_days'],
            'last_updated' => now()->toISOString()
        ]);
    }
}
