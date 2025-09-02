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
            
            // รีเซ็ตการนับความผิดพลาด
            User::where('id', $user->id)->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now()
            ]);
            
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
     * Redirect ผู้ใช้ไปยัง dashboard ที่เหมาะสมตามบทบาท
     */
    protected function redirectToIntended($user)
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->route('super-admin.dashboard')
                           ->with('success', 'ยินดีต้อนรับ Super Admin ' . $user->full_name);
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')
                           ->with('success', 'ยินดีต้อนรับ Admin ' . $user->full_name);
        } else {
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
