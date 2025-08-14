<?php

namespace App\Http\Controllers\Super_Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('super_admin.dashboard');
    }

    public function information()
    {
        return view('super_admin.profile.information');
    }

    public function updateInformation(Request $request)
    {
        $request->validate(
            [
                'prefix' => 'required|string|max:10',
                'name' => 'required|string|max:100',
                'email' => 'required|max:50',
                'phone' => 'required|string|max:10|min:10|regex:/^[0-9]+$/',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ],
            [
                'prefix.required' => 'กรุณากรอกคำนำหน้า',
                'name.required' => 'กรุณากรอกชื่อ',
                'email.required' => 'กรุณากรอกอีเมล',
                'phone.required' => 'กรุณากรอกเบอร์โทรศัพท์',
                'phone.regex' => 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง',
                'phone.min' => 'กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก',
                'phone.max' => 'กรุณากรอกเบอร์โทรศัพท์ไม่เกิน 10 หลัก',
                'image.image' => 'รูปภาพไม่ถูกต้อง',
                'image.mimes' => 'รูปภาพต้องเป็นไฟล์ประเภท: jpeg, png, jpg, gif',
                'image.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB',
            ]
        );

        // อัปโหลดไฟล์ภาพถ้ามี
        $file_name = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $file_name = time() . '.' . $extension;
            $path = 'images/profile/' . auth()->user()->id . '/';

            // ลบไฟล์เดิมถ้ามี
            $old_avatar = auth()->user()->avatar;
            if ($old_avatar) {
                $old_path = public_path($path . $old_avatar);
                if (file_exists($old_path)) {
                    @unlink($old_path);
                }
            }

            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!is_dir(public_path($path))) {
                mkdir(public_path($path), 0777, true);
            }

            $file->move(public_path($path), $file_name);
        } else {
            $path = '';
            $file_name = null;
        }

        $data = [
            'prefix' => $request->input('prefix'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'avatar' => $file_name,
            'updated_at' => now(),
        ];

        User::where('id', auth()->user()->id)->update($data);
        return redirect()->route('super_admin.information')->with('success', 'ข้อมูลส่วนตัวของคุณถูกอัปเดตแล้ว');
    }

    public function accountSettings()
    {
        return view('super_admin.profile.account');
    }

    public function updateAccountSettings(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|max:50',
                'username' => 'required|string|max:50|unique:users,username,' . auth()->user()->id,
                'password' => 'nullable|string|min:8|confirmed',
            ],
            [
                'email.required' => 'กรุณากรอกอีเมล',
                'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
                'email.max' => 'อีเมลต้องไม่เกิน 50 ตัวอักษร',
                'username.required' => 'กรุณากรอกบัญชีผู้ใช้',
                'username.max' => 'บัญชีผู้ใช้ต้องไม่เกิน 50 ตัวอักษร',
                'username.unique' => 'บัญชีผู้ใช้มีอยู่แล้ว',
                'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
                'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
            ]
        );

        $data = [
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
            'updated_at' => now(),
        ];

        // อัปเดตข้อมูลผู้ใช้
        User::where('id', auth()->user()->id)->update($data);
        // logout หลังอัปเดต
        auth()->logout();
        return redirect()->route('login')->with('success', 'การตั้งค่าบัญชีของคุณถูกอัปเดตแล้ว กรุณาเข้าสู่ระบบใหม่');
    }
}
