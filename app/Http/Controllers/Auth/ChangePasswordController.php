<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordExpirationService;
use App\Models\PasswordHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Rules\PasswordPolicy;
use App\Rules\PasswordHistoryRule;

class ChangePasswordController extends Controller
{
    protected $passwordExpirationService;

    public function __construct(PasswordExpirationService $passwordExpirationService)
    {
        $this->middleware('auth');
        $this->passwordExpirationService = $passwordExpirationService;
    }

    /**
     * Show the change password form.
     */
    public function showChangeForm()
    {
        $user = Auth::user();
        
        $data = [
            'passwordExpired' => $this->passwordExpirationService->isPasswordExpired($user),
            'daysLeft' => $this->passwordExpirationService->getDaysUntilExpiration($user),
        ];

        return view('auth.change-password', $data);
    }

    /**
     * Handle password change request.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validation rules
        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('รหัสผ่านปัจจุบันไม่ถูกต้อง');
                }
            }],
            'password' => [
                'required',
                'confirmed',
                new PasswordPolicy(),
                new PasswordHistoryRule($user),
            ],
        ], [
            'current_password.required' => 'กรุณากรอกรหัสผ่านปัจจุบัน',
            'password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'password.confirmed' => 'รหัสผ่านยืนยันไม่ตรงกัน',
        ]);

        try {
            // Store old password in history
            PasswordHistory::storePassword($user, $user->password);

            // Update password with expiration tracking
            $this->passwordExpirationService->updatePassword($user, $request->password);

            return redirect()->route('password.change')
                ->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน กรุณาลองใหม่อีกครั้ง');
        }
    }
}
