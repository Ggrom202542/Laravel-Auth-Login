<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * แสดงโปรไฟล์ผู้ใช้
     */
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        return view('profile.show', compact('user'));
    }

    /**
     * แสดงหน้าแก้ไขโปรไฟล์
     */
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        return view('profile.edit', compact('user'));
    }

    /**
     * อัพเดทโปรไฟล์ผู้ใช้
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validatedData = $request->validate([
            'prefix' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $user->fill($validatedData);
        $user->save();

        // ตรวจสอบว่าโปรไฟล์ครบถ้วนหรือไม่
        $this->checkProfileCompletion($user);

        return redirect()->route('profile.show')
                        ->with('success', 'โปรไฟล์ได้รับการอัพเดทเรียบร้อยแล้ว');
    }

    /**
     * อัพโหลดรูปโปรไฟล์
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            // ลบรูปเก่าถ้ามี
            if ($user->profile_image && Storage::exists('public/avatars/' . $user->profile_image)) {
                Storage::delete('public/avatars/' . $user->profile_image);
            }

            $file = $request->file('avatar');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            // สร้าง directory ถ้าไม่มี
            if (!Storage::exists('public/avatars')) {
                Storage::makeDirectory('public/avatars');
            }

            // อัพโหลดไฟล์
            $path = $file->storeAs('public/avatars', $filename);

            // อัพเดท database
            $user->profile_image = $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'อัพโหลดรูปโปรไฟล์สำเร็จ',
                'avatar_url' => Storage::url('avatars/' . $filename)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการอัพโหลดรูป'
        ]);
    }

    /**
     * แสดงหน้าการตั้งค่า
     */
    public function settings(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        return view('profile.settings', compact('user'));
    }

    /**
     * อัพเดทการตั้งค่าผู้ใช้
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validatedData = $request->validate([
            'theme' => 'required|in:light,dark',
            'language' => 'required|in:th,en',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
        ]);

        // กำหนดค่าเป็น false ถ้าไม่ได้เลือก checkbox
        $validatedData['email_notifications'] = $request->has('email_notifications');
        $validatedData['sms_notifications'] = $request->has('sms_notifications');
        $validatedData['push_notifications'] = $request->has('push_notifications');

        $user->fill($validatedData);
        $user->save();

        return redirect()->route('profile.settings')
                        ->with('success', 'การตั้งค่าได้รับการอัพเดทเรียบร้อยแล้ว');
    }

    /**
     * เปลี่ยนรหัสผ่าน
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'กรุณาใส่รหัสผ่านปัจจุบัน',
            'new_password.required' => 'กรุณาใส่รหัสผ่านใหม่',
            'new_password.min' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'new_password.confirmed' => 'การยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // ตรวจสอบรหัสผ่านปัจจุบัน
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
        }

        // อัพเดทรหัสผ่านใหม่
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile.settings')
                        ->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }

    /**
     * ตรวจสอบความครบถ้วนของโปรไฟล์
     */
    private function checkProfileCompletion(User $user): void
    {
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone'
        ];

        $isComplete = true;
        foreach ($requiredFields as $field) {
            if (empty($user->{$field})) {
                $isComplete = false;
                break;
            }
        }

        if ($isComplete && !$user->profile_completed) {
            $user->profile_completed = true;
            $user->profile_completed_at = now();
            $user->save();
        } elseif (!$isComplete && $user->profile_completed) {
            $user->profile_completed = false;
            $user->save();
        }
    }

    /**
     * ลบรูปโปรไฟล์
     */
    public function deleteAvatar(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->profile_image) {
            // ลบไฟล์รูป
            if (Storage::exists('public/avatars/' . $user->profile_image)) {
                Storage::delete('public/avatars/' . $user->profile_image);
            }

            // อัพเดท database
            $user->profile_image = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'ลบรูปโปรไฟล์เรียบร้อยแล้ว'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ไม่พบรูปโปรไฟล์'
        ]);
    }
}
