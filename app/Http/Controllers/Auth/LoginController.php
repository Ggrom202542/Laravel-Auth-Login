<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log, Http};
use App\Models\User;
use App\Models\UserActivity;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username()
    {
        return 'username';
    }

    public function login(Request $request)
    {
        Log::info('Login request received', [
            'method' => $request->method(),
            'url' => $request->url(),
            'input' => $request->only(['username']),
            'headers' => $request->headers->all()
        ]);
        
        $input = $request->only('username', 'password');

        $this->validate(
            $request,
            [
                'username' => 'required|string',
                'password' => 'required|string',
            ],
            [
                'username.required' => 'กรุณากรอกชื่อผู้ใช้',
                'username.string' => 'ชื่อผู้ใช้ต้องเป็นข้อความ',
                'password' => [
                    'required' => 'กรุณากรอกรหัสผ่าน',
                    'string' => 'รหัสผ่านต้องเป็นข้อความ',
                ]
            ]
        );

        // หาผู้ใช้ตาม username
        $user = User::where('username', $input['username'])->first();

        // ตรวจสอบว่าผู้ใช้มีอยู่หรือไม่
        if (!$user) {
            return back()->withErrors([
                'username' => 'ไม่พบชื่อผู้ใช้นี้ในระบบ',
            ]);
        }

        // ตรวจสอบสถานะบัญชี
        if ($user->status !== 'active') {
            return back()->withErrors([
                'username' => 'บัญชีของคุณถูกปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ]);
        }

        // ตรวจสอบว่าบัญชีถูกล็อกหรือไม่
        if ($user->locked_until && $user->locked_until > now()) {
            $remainingTime = $user->locked_until->diffInMinutes(now());
            return back()->withErrors([
                'username' => "บัญชีของคุณถูกล็อก กรุณาลองใหม่ใน {$remainingTime} นาที",
            ]);
        }

        // ลองเข้าสู่ระบบ
        if (Auth::attempt($input)) {
            $user = Auth::user();
            
            Log::info('User login successful', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role
            ]);
            
            // รีเซ็ตการนับความผิดพลาด
            User::where('id', $user->id)->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now()
            ]);
            
            // ตรวจสอบ Two-Factor Authentication
            $requires2FA = $this->requiresTwoFactorAuth($user);
            
            // ตรวจสอบสถานะ 2FA ของผู้ใช้
            $userHas2FA = ($user->two_factor_enabled || $user->google2fa_enabled) 
                         && !empty($user->google2fa_secret) 
                         && !is_null($user->google2fa_confirmed_at);
            
            Log::info('2FA Check Result', [
                'user_id' => $user->id,
                'requires_2fa' => $requires2FA,
                'config_enabled' => config('auth.two_factor.enabled'),
                'user_has_2fa' => $userHas2FA,
                'two_factor_enabled' => $user->two_factor_enabled,
                'google2fa_enabled' => $user->google2fa_enabled,
                'google2fa_secret_exists' => !empty($user->google2fa_secret),
                'google2fa_confirmed_at' => $user->google2fa_confirmed_at
            ]);
            
            if ($requires2FA) {
                // เก็บ user ID และ credentials ใน session สำหรับ 2FA challenge
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:login:timestamp', now()->timestamp);
                $request->session()->put('2fa:user:remember', $request->filled('remember'));
                
                // ออกจากระบบชั่วคราว แต่ regenerate session เพื่อป้องกันปัญหา
                Auth::logout();
                $request->session()->regenerate();
                
                Log::info('Two-Factor Authentication required - user logged out temporarily', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'session_id' => $request->session()->getId(),
                    'session_2fa_id' => $request->session()->get('2fa:user:id'),
                    'auth_check_after_logout' => Auth::check() ? 'true' : 'false'
                ]);
                
                // Direct redirect แทน meta refresh
                return redirect()->route('2fa.challenge')
                    ->with('message', 'กรุณายืนยันตัวตนด้วยรหัส 2FA');
            }
            
            // บันทึกกิจกรรม
            UserActivity::create([
                'user_id' => $user->id,
                'action' => 'login',
                'description' => 'เข้าสู่ระบบสำเร็จ',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?: 'Unknown',
                'created_at' => now()
            ]);

            // Redirect ตามบทบาท
            return $this->redirectToIntended($user);
        }

        // การเข้าสู่ระบบล้มเหลว - เพิ่มจำนวนครั้งที่ผิด
        if ($user) {
            $attempts = $user->failed_login_attempts + 1;
            $updateData = ['failed_login_attempts' => $attempts];

            // Lock account after 5 failed attempts for 15 minutes
            if ($attempts >= 5) {
                $updateData['locked_until'] = now()->addMinutes(15);
            }

            User::where('id', $user->id)->update($updateData);
            
            // บันทึกกิจกรรมการเข้าสู่ระบบล้มเหลว
            UserActivity::create([
                'user_id' => $user->id,
                'action' => 'login_failed',
                'description' => 'พยายามเข้าสู่ระบบด้วยรหัสผ่านที่ไม่ถูกต้อง',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?: 'Unknown',
                'created_at' => now()
            ]);
        }

        return back()->withErrors([
            'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง',
        ]);
    }

    /**
     * ตรวจสอบว่าผู้ใช้ต้องใช้ Two-Factor Authentication หรือไม่
     */
    private function requiresTwoFactorAuth($user)
    {
        // ตรวจสอบว่าระบบเปิดใช้งาน 2FA หรือไม่
        $configEnabled = config('auth.two_factor.enabled', false);
        Log::info('requiresTwoFactorAuth - Config check', [
            'config_enabled' => $configEnabled,
            'env_value' => env('TWO_FACTOR_ENABLED'),
            'user_id' => $user->id
        ]);
        
        if (!$configEnabled) {
            Log::info('requiresTwoFactorAuth - 2FA disabled in config', ['user_id' => $user->id]);
            return false;
        }

        // ตรวจสอบว่าผู้ใช้เปิดใช้งาน 2FA หรือไม่
        $userHas2FA = ($user->two_factor_enabled || $user->google2fa_enabled) 
                     && !empty($user->google2fa_secret) 
                     && !is_null($user->google2fa_confirmed_at);
                     
        Log::info('requiresTwoFactorAuth - User 2FA check', [
            'user_id' => $user->id,
            'user_has_2fa' => $userHas2FA,
            'two_factor_enabled' => $user->two_factor_enabled,
            'google2fa_enabled' => $user->google2fa_enabled,
            'google2fa_secret' => !empty($user->google2fa_secret),
            'google2fa_confirmed_at' => $user->google2fa_confirmed_at
        ]);
        
        if (!$userHas2FA) {
            // หาก enforce_for_all_users = true, บังคับให้ตั้ง 2FA
            $enforceAll = config('auth.two_factor.enforce_for_all_users', false);
            Log::info('requiresTwoFactorAuth - User has no 2FA', [
                'user_id' => $user->id,
                'enforce_for_all' => $enforceAll
            ]);
            
            if ($enforceAll) {
                // TODO: Redirect to 2FA setup page
                return true;
            }
            return false;
        }

        Log::info('requiresTwoFactorAuth - 2FA required', ['user_id' => $user->id]);
        return true;
    }

    /**
     * Redirect ผู้ใช้ไปยัง dashboard ที่เหมาะสมตามบทบาท
     */
    protected function redirectToIntended($user)
    {
        Log::info('LoginController redirectToIntended - User role check', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'role_type' => gettype($user->role)
        ]);

        switch ($user->role) {
            case 'super_admin':
                Log::info('Login redirect - Super Admin dashboard');
                return redirect()->route('super-admin.dashboard')
                               ->with('success', 'ยินดีต้อนรับ Super Admin ' . $user->full_name);
            
            case 'admin':
                Log::info('Login redirect - Admin dashboard');
                return redirect()->route('admin.dashboard')
                               ->with('success', 'ยินดีต้อนรับ Admin ' . $user->full_name);
            
            case 'user':
            default:
                Log::info('Login redirect - User dashboard');
                return redirect()->route('user.dashboard')
                               ->with('success', 'ยินดีต้อนรับ ' . $user->full_name);
        }
    }

    /**
     * ปรับแต่ง logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            // บันทึกกิจกรรม logout
            UserActivity::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'ออกจากระบบ',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?: 'Unknown',
                'created_at' => now()
            ]);
        }

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }
}
