<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Register;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Hash,
    DB,
    Http,
    Validator
};

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
    }

    function register(Request $request)
    {
        $request->validate(
            [
                'prefix' => ['required', 'string', 'max:10'],
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:15'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            [
                'prefix.required' => 'กรุณากรอกคำนำหน้า',
                'name.required' => 'กรุณากรอกชื่อ - นามสกุล',
                'phone.required' => 'กรุณากรอกหมายเลขโทรศัพท์',
                'username.required' => 'กรุณากรอกชื่อผู้ใช้',
                'username.unique' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว',
                'password.required' => 'กรุณากรอกรหัสผ่าน',
                'password.confirmed' => 'รหัสผ่านไม่ตรงกัน',
            ]
        );

        Register::create([
            'prefix' => $request->prefix,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'user_type' => 'user', // Default user type
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('login');
    }
}
