<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        $user = Auth::user();
        
        // ตรวจสอบสถานะบัญชี
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกปิดใช้งาน');
        }

        // ตรวจสอบว่าบัญชีถูกล็อกหรือไม่
        if ($user->locked_until && $user->locked_until > now()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'บัญชีของคุณถูกล็อก');
        }

        // ตรวจสอบสิทธิ์
        foreach ($permissions as $permission) {
            // ตรวจสอบ permission ผ่านฐานข้อมูลโดยตรง
            $hasPermission = DB::table('user_roles')
                ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('user_roles.user_id', $user->id)
                ->where('permissions.name', $permission)
                ->exists();

            if ($hasPermission) {
                return $next($request);
            }
        }

        // ไม่มีสิทธิ์เข้าถึง
        abort(403, 'คุณไม่มีสิทธิ์ในการดำเนินการนี้');
    }
}
