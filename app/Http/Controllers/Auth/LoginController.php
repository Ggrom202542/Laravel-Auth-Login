<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log, Http};

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

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
                'username.exists' => 'ชื่อผู้ใช้ไม่ถูกต้อง',
                'password' => [
                    'required' => 'กรุณากรอกรหัสผ่าน',
                    'string' => 'รหัสผ่านต้องเป็นข้อความ',
                ]
            ]
        );

        if (Auth::attempt($input)) {
            return redirect()->intended($this->redirectTo)->with('success', 'ลงชื่อเข้าใช้งานสำเร็จ');
        }

        return back()->withErrors([
            'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง',
        ]);
    }
}
