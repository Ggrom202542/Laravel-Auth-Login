<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RegistrationApproval;
use App\Mail\RegistrationPending;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash,
    DB,
    Validator,
    Mail,
    Log
};
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        $request->validate(
            [
                'prefix' => ['required', 'string', 'max:10'],
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'phone' => ['required', 'string', 'max:15', 'unique:users'],
                'email' => ['nullable', 'email', 'max:255', 'unique:users'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            [
                'prefix.required' => 'กรุณากรอกคำนำหน้า',
                'first_name.required' => 'กรุณากรอกชื่อ',
                'last_name.required' => 'กรุณากรอกนามสกุล',
                'phone.required' => 'กรุณากรอกหมายเลขโทรศัพท์',
                'phone.unique' => 'หมายเลขโทรศัพท์นี้ถูกใช้งานแล้ว',
                'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
                'username.required' => 'กรุณากรอกชื่อผู้ใช้',
                'username.unique' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว',
                'password.required' => 'กรุณากรอกรหัสผ่าน',
                'password.confirmed' => 'รหัสผ่านไม่ตรงกัน',
                'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 หลัก',
            ]
        );

        DB::beginTransaction();

        try {
            // สร้าง User ด้วยสถานะ pending approval
            $user = User::create([
                'prefix' => $request->prefix,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'username' => $request->username,
                'password' => $request->password, // จะถูก hash อัตโนมัติ
                'role' => 'user',
                'status' => 'inactive', // ยังไม่สามารถใช้งานได้
                'approval_status' => 'pending',
                'registered_at' => now(),
            ]);

            // สร้าง approval record
            $approvalToken = Str::random(64);
            
            $approval = RegistrationApproval::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'approval_token' => $approvalToken,
                'token_expires_at' => now()->addDays(7), // หมดอายุใน 7 วัน
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'additional_data' => json_encode([
                    'registration_method' => 'web_form',
                    'browser' => $request->header('User-Agent'),
                    'referer' => $request->header('referer'),
                ])
            ]);

            DB::commit();

            // ส่งอีเมลยืนยันการสมัครให้ผู้สมัคร
            try {
                Mail::to($user->email)->send(new RegistrationPending($user, $approval));
            } catch (\Exception $e) {
                // Log email error but don't fail the registration
                Log::error('Failed to send registration pending email: ' . $e->getMessage());
            }

            return redirect()->route('register.pending')
                           ->with('success', 'ส่งคำขอสมัครสมาชิกเรียบร้อยแล้ว กรุณาตรวจสอบอีเมลและรอการอนุมัติจากผู้ดูแลระบบ');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Show pending approval page.
     */
    public function showPendingApproval()
    {
        return view('auth.pending');
    }
}
