<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckPasswordExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Skip check if password expiration is disabled for user
        if (!$user->password_expiration_enabled) {
            return $next($request);
        }

        // Skip check for certain routes
        $excludedRoutes = [
            'password.change',
            'password.update',
            'logout',
            'api.*'
        ];

        foreach ($excludedRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        $now = Carbon::now();
        $passwordConfig = config('password_policy.expiration');
        
        // Check if password is expired
        if ($user->password_expires_at && $now->greaterThan($user->password_expires_at)) {
            return redirect()->route('password.change')
                ->with('error', 'รหัสผ่านของคุณหมดอายุแล้ว กรุณาเปลี่ยนรหัสผ่านใหม่');
        }

        // Check if password will expire soon and send warning
        $warningDays = $passwordConfig['warning_days'] ?? 7;
        $warningDate = $now->copy()->addDays($warningDays);
        
        if ($user->password_expires_at && $warningDate->greaterThanOrEqualTo($user->password_expires_at)) {
            $daysLeft = $now->diffInDays($user->password_expires_at, false);
            
            // Only show warning once per day
            $lastWarned = $user->password_warned_at ? Carbon::parse($user->password_warned_at) : null;
            $shouldWarn = !$lastWarned || $lastWarned->diffInDays($now) >= 1;
            
            if ($shouldWarn && $daysLeft >= 0) {
                // Update last warned timestamp
                $user->password_warned_at = $now;
                $user->save();
                
                $message = $daysLeft > 0 
                    ? "รหัสผ่านของคุณจะหมดอายุใน {$daysLeft} วัน กรุณาเปลี่ยนรหัสผ่านใหม่"
                    : "รหัสผ่านของคุณจะหมดอายุวันนี้ กรุณาเปลี่ยนรหัสผ่านทันที";
                    
                session()->flash('password_warning', $message);
            }
        }

        return $next($request);
    }
}
