<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        $user = Auth::user();
        
        // ตรวจสอบสถานะบัญชี
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
        }

        // ตรวจสอบว่าบัญชีถูกล็อกหรือไม่
        if ($user->locked_until && $user->locked_until > now()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกล็อก กรุณาลองใหม่อีกครั้งภายหลัง');
        }

        // ตรวจสอบบทบาท
        foreach ($roles as $role) {
            // ตรวจสอบผ่าน role column ในตาราง users
            // รองรับทั้ง super-admin และ super_admin
            if ($user->role === $role || 
                ($role === 'super-admin' && $user->role === 'super_admin') ||
                ($role === 'super_admin' && $user->role === 'super-admin')) {
                return $next($request);
            }
        }

        // ไม่มีสิทธิ์เข้าถึง
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์เข้าถึง บทบาทของคุณ: ' . $user->role
            ], 403);
        }
        
        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้ บทบาทของคุณ: ' . $user->role);
    }
}
