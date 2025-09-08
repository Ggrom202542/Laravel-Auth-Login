<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\IpManagementService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class IpSecurityMiddleware
{
    protected $ipManagementService;

    public function __construct(IpManagementService $ipManagementService)
    {
        $this->ipManagementService = $ipManagementService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userIp = $request->ip();

        // ตรวจสอบว่า IP ถูกบล็อกหรือไม่
        if ($this->ipManagementService->isIpBlocked($userIp)) {
            // Log การพยายามเข้าถึงจาก IP ที่ถูกบล็อก
            Log::warning('Blocked IP attempted access', [
                'ip' => $userIp,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method()
            ]);

            // Return 403 Forbidden response
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'Your IP address has been blocked due to security reasons.'
                ], 403);
            }

            return response()->view('errors.403-ip-blocked', [
                'ip' => $userIp
            ], 403);
        }

        return $next($request);
    }
}
