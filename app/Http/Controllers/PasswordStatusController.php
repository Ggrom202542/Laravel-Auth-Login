<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PasswordExpirationService;
use Illuminate\Support\Facades\Auth;

class PasswordStatusController extends Controller
{
    protected $passwordService;

    public function __construct(PasswordExpirationService $passwordService)
    {
        $this->middleware('auth');
        $this->passwordService = $passwordService;
    }

    /**
     * Show password status page
     */
    public function show()
    {
        $user = Auth::user();
        
        $data = [
            'user' => $user,
            'isExpired' => $this->passwordService->isPasswordExpired($user),
            'daysLeft' => $this->passwordService->getDaysUntilExpiration($user),
            'shouldShowWarning' => $this->passwordService->shouldShowWarning($user),
            'statistics' => $this->passwordService->getExpirationStatistics(),
        ];

        return view('password.status', $data);
    }

    /**
     * Get password status as JSON
     */
    public function getStatus()
    {
        $user = Auth::user();
        
        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'password_expiration_enabled' => $user->password_expiration_enabled,
            'password_expires_at' => $user->password_expires_at,
            'password_changed_at' => $user->password_changed_at,
            'password_warned_at' => $user->password_warned_at,
            'is_expired' => $this->passwordService->isPasswordExpired($user),
            'days_until_expiration' => $this->passwordService->getDaysUntilExpiration($user),
            'should_show_warning' => $this->passwordService->shouldShowWarning($user),
        ]);
    }
}
