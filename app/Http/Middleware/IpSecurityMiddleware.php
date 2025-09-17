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
        $userIp = $this->getRealIpAddress($request);

        // ข้าม localhost และ private IPs ใน development
        if ($this->isLocalOrPrivateIp($userIp)) {
            return $next($request);
        }

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

    /**
     * ดึง Real IP Address จาก headers ต่างๆ
     */
    private function getRealIpAddress(Request $request): string
    {
        // ตรวจสอบ headers ที่ proxy/load balancer อาจใช้
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_X_FORWARDED_FOR',      // Standard
            'HTTP_X_FORWARDED',          // Standard
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_CLIENT_IP',            // Proxy
            'REMOTE_ADDR'                // Default
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // หาก header มี multiple IPs (comma-separated)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    /**
     * ตรวจสอบว่าเป็น localhost หรือ private IP
     */
    private function isLocalOrPrivateIp(string $ip): bool
    {
        // Localhost IPs
        $localIps = ['127.0.0.1', '::1', 'localhost'];
        
        if (in_array($ip, $localIps)) {
            return true;
        }

        // Private IP ranges
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
