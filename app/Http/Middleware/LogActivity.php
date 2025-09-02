<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivity;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // บันทึกกิจกรรมสำหรับผู้ใช้ที่ล็อกอินแล้วเท่านั้น
        if (Auth::check() && $this->shouldLog($request)) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * ตรวจสอบว่าควรบันทึกกิจกรรมนี้หรือไม่
     */
    protected function shouldLog(Request $request): bool
    {
        // ไม่บันทึกเส้นทางเหล่านี้
        $excludeRoutes = [
            '_debugbar',
            'telescope', 
            'favicon.ico'
        ];

        $path = $request->path();
        
        foreach ($excludeRoutes as $exclude) {
            if (str_contains($path, $exclude)) {
                return false;
            }
        }

        return true;
    }

    /**
     * บันทึกกิจกรรม
     */
    protected function logActivity(Request $request, Response $response)
    {
        $user = Auth::user();
        
        UserActivity::create([
            'user_id' => $user->id,
            'action' => $this->getActionFromRequest($request),
            'description' => 'เข้าถึง ' . $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?: 'Unknown',
            'properties' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
            ],
            'created_at' => now()
        ]);
    }

    /**
     * กำหนด action จาก request
     */
    protected function getActionFromRequest(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'login')) return 'login';
        if (str_contains($path, 'logout')) return 'logout'; 
        if (str_contains($path, 'admin')) return 'admin_access';

        return match($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view'
        };
    }
}
