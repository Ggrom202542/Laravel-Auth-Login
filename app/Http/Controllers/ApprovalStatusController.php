<?php

namespace App\Http\Controllers;

use App\Models\RegistrationApproval;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalStatusController extends Controller
{
    /**
     * Show registration approval status using token
     *
     * @param string $token
     * @return View
     */
    public function show(string $token): View
    {
        $approval = RegistrationApproval::with('user', 'reviewer')
                                      ->where('approval_token', $token)
                                      ->first();

        if (!$approval) {
            abort(404, 'ไม่พบข้อมูลการสมัครสมาชิกที่ร้องขอ');
        }

        // Check if token is expired for pending approvals
        if ($approval->status === 'pending' && $approval->isTokenExpired()) {
            $approval->update([
                'status' => 'expired',
                'rejection_reason' => 'Token หมดอายุ - กรุณาสมัครสมาชิกใหม่อีกครั้ง'
            ]);
        }

        return view('auth.approval-status', compact('approval'));
    }

    /**
     * Show general pending registration message
     *
     * @return View
     */
    public function pending(): View
    {
        return view('auth.registration-pending');
    }
}
