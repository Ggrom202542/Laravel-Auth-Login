<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorChallenge
{
    /**
     * Handle an incoming request for 2FA challenge
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('TwoFactorChallenge Middleware - Started', [
            'route' => $request->route() ? $request->route()->getName() : 'no_route',
            'url' => $request->url(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId()
        ]);
        
        // ตรวจสอบว่ามี session 2FA challenge หรือไม่
        $userId = $request->session()->get('2fa:user:id');
        
        Log::info('TwoFactorChallenge Middleware - Session Check', [
            'route' => $request->route() ? $request->route()->getName() : 'no_route',
            'has_2fa_session' => !empty($userId),
            'user_id' => $userId,
            'all_session_keys' => array_keys($request->session()->all()),
            'session_data' => $request->session()->all()
        ]);
        
        // ถ้าไม่มี 2FA session ให้ redirect ไป login
        if (empty($userId)) {
            Log::warning('TwoFactorChallenge Middleware - No 2FA session, redirecting to login');
            return redirect()->route('login')
                ->withErrors(['email' => 'กรุณาเข้าสู่ระบบเพื่อใช้งาน 2FA']);
        }
        
        Log::info('TwoFactorChallenge Middleware - Proceeding to controller');
        return $next($request);
    }
}
