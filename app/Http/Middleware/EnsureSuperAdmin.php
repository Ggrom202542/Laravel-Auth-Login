<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาเข้าสู่ระบบ'
                ], 401);
            }
            return redirect()->route('login');
        }

        if (Auth::user()->role !== 'super_admin') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'คุณไม่มีสิทธิ์เข้าถึงฟังก์ชันนี้'
                ], 403);
            }
            
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงฟังก์ชันนี้');
        }

        return $next($request);
    }
}
