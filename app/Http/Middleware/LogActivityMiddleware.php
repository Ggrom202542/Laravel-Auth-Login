<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // แปลงเป็น milliseconds
        
        // บันทึกกิจกรรมหลังจากได้ response แล้ว
        $this->logActivity($request, $response, $responseTime);
        
        return $response;
    }
    
    /**
     * บันทึกกิจกรรมของผู้ใช้
     */
    private function logActivity(Request $request, Response $response, float $responseTime)
    {
        try {
            // ข้ามการบันทึกสำหรับ route บางอย่าง
            if ($this->shouldSkipLogging($request)) {
                return;
            }
            
            $user = Auth::user();
            $activityType = $this->determineActivityType($request, $response);
            $description = $this->generateDescription($request, $response, $activityType);
            
            // ตรวจสอบว่าเป็นกิจกรรมที่น่าสงสัยหรือไม่
            $isSuspicious = $this->isSuspiciousActivity($request, $response);
            
            // เตรียมข้อมูล payload
            $payload = $this->preparePayload($request);
            
            // ข้อมูลเพิ่มเติม
            $properties = [
                'route_name' => $request->route() ? $request->route()->getName() : null,
                'request_id' => $request->header('X-Request-ID', uniqid()),
                'referer' => $request->header('referer'),
                'accept_language' => $request->header('accept-language'),
            ];
            
            // บันทึกกิจกรรม
            ActivityLog::create([
                'user_id' => $user ? $user->id : null,
                'activity_type' => $activityType,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'payload' => $payload,
                'response_status' => $response->getStatusCode(),
                'response_time' => round($responseTime, 3),
                'location' => $this->getLocationFromIP($request->ip()),
                'device_type' => $this->getDeviceType($request->userAgent()),
                'browser' => $this->getBrowser($request->userAgent()),
                'platform' => $this->getPlatform($request->userAgent()),
                'is_suspicious' => $isSuspicious,
                'session_id' => $request->session()->getId(),
                'causer_type' => $user ? get_class($user) : null,
                'causer_id' => $user ? $user->id : null,
                'properties' => $properties
            ]);
            
        } catch (\Exception $e) {
            // หากเกิดข้อผิดพลาดในการบันทึก ให้ log แต่ไม่หยุดการทำงาน
            \Log::error('Failed to log activity: ' . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * ตรวจสอบว่าควรข้ามการบันทึกหรือไม่
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipRoutes = [
            'api/activities/recent',
            'activities/chart-data',
            'messages/recent',
            '_debugbar',
            'telescope',
            'horizon',
            'health-check',
            'ping'
        ];
        
        $path = $request->path();
        
        foreach ($skipRoutes as $skipRoute) {
            if (str_contains($path, $skipRoute)) {
                return true;
            }
        }
        
        // ข้าม static files
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * กำหนดประเภทกิจกรรม
     */
    private function determineActivityType(Request $request, Response $response): string
    {
        $method = $request->method();
        $path = $request->path();
        $routeName = $request->route() ? $request->route()->getName() : null;
        $statusCode = $response->getStatusCode();
        
        // กิจกรรมพิเศษ
        if ($routeName) {
            if (str_contains($routeName, 'login')) {
                return $statusCode >= 200 && $statusCode < 300 ? 'login' : 'failed_login';
            }
            if (str_contains($routeName, 'logout')) {
                return 'logout';
            }
            if (str_contains($routeName, 'register')) {
                return 'register';
            }
            if (str_contains($routeName, 'password')) {
                return 'password_change';
            }
            if (str_contains($routeName, 'profile')) {
                return 'profile_update';
            }
            if (str_contains($routeName, '2fa')) {
                return str_contains($path, 'enable') ? '2fa_enabled' : '2fa_disabled';
            }
            if (str_contains($routeName, 'message')) {
                return $method === 'POST' ? 'message_sent' : 'message_read';
            }
            if (str_contains($routeName, 'notification')) {
                return 'notification_sent';
            }
            if (str_contains($routeName, 'approval')) {
                if (str_contains($path, 'approve')) return 'approval_granted';
                if (str_contains($path, 'reject')) return 'approval_rejected';
                return 'approval_request';
            }
            if (str_contains($routeName, 'export')) {
                return 'data_export';
            }
            if (str_contains($routeName, 'upload')) {
                return 'file_upload';
            }
            if (str_contains($routeName, 'download')) {
                return 'file_download';
            }
        }
        
        // กิจกรรมตาม HTTP method
        switch ($method) {
            case 'GET':
                if ($statusCode >= 400) {
                    return 'failed_access';
                }
                return str_contains($path, 'dashboard') ? 'dashboard_view' : 'page_view';
            case 'POST':
                return 'data_create';
            case 'PUT':
            case 'PATCH':
                return 'data_update';
            case 'DELETE':
                return 'data_delete';
            default:
                return 'unknown_activity';
        }
    }
    
    /**
     * สร้างคำอธิบายกิจกรรม
     */
    private function generateDescription(Request $request, Response $response, string $activityType): string
    {
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();
        $user = Auth::user();
        
        $descriptions = [
            'login' => 'เข้าสู่ระบบสำเร็จ',
            'failed_login' => 'พยายามเข้าสู่ระบบแต่ไม่สำเร็จ',
            'logout' => 'ออกจากระบบ',
            'register' => 'สมัครสมาชิกใหม่',
            'password_change' => 'เปลี่ยนรหัสผ่าน',
            'profile_update' => 'อัปเดตข้อมูลโปรไฟล์',
            '2fa_enabled' => 'เปิดใช้งาน Two-Factor Authentication',
            '2fa_disabled' => 'ปิดใช้งาน Two-Factor Authentication',
            'message_sent' => 'ส่งข้อความ',
            'message_read' => 'อ่านข้อความ',
            'notification_sent' => 'ส่งการแจ้งเตือน',
            'approval_granted' => 'อนุมัติคำขอ',
            'approval_rejected' => 'ปฏิเสธคำขอ',
            'approval_request' => 'ส่งคำขออนุมัติ',
            'data_export' => 'ส่งออกข้อมูล',
            'file_upload' => 'อัปโหลดไฟล์',
            'file_download' => 'ดาวน์โหลดไฟล์',
            'dashboard_view' => 'เข้าดูหน้า Dashboard',
            'page_view' => 'เข้าดูหน้าเว็บ',
            'data_create' => 'สร้างข้อมูลใหม่',
            'data_update' => 'แก้ไขข้อมูล',
            'data_delete' => 'ลบข้อมูล',
            'failed_access' => 'พยายามเข้าถึงหน้าที่ไม่มีสิทธิ์'
        ];
        
        $baseDescription = $descriptions[$activityType] ?? 'ทำกิจกรรมไม่ทราบประเภท';
        
        // เพิ่มรายละเอียดเพิ่มเติม
        $details = [];
        
        if ($statusCode >= 400) {
            $details[] = "HTTP {$statusCode}";
        }
        
        if ($user && in_array($activityType, ['approval_granted', 'approval_rejected', 'data_delete'])) {
            $details[] = "โดย {$user->name}";
        }
        
        $details[] = "{$method} {$path}";
        
        return $baseDescription . (!empty($details) ? ' (' . implode(', ', $details) . ')' : '');
    }
    
    /**
     * ตรวจสอบกิจกรรมที่น่าสงสัย
     */
    private function isSuspiciousActivity(Request $request, Response $response): bool
    {
        $statusCode = $response->getStatusCode();
        $path = $request->path();
        $method = $request->method();
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        
        // กรณีที่น่าสงสัย
        $suspiciousConditions = [
            // HTTP status codes ที่น่าสงสัย
            $statusCode === 401 || $statusCode === 403 || $statusCode === 404,
            
            // พยายามเข้าถึง admin routes โดยไม่มีสิทธิ์
            str_contains($path, 'admin') && $statusCode >= 400,
            
            // พยายามเข้าถึง super-admin routes โดยไม่มีสิทธิ์
            str_contains($path, 'super-admin') && $statusCode >= 400,
            
            // User agent ที่น่าสงสัย
            empty($userAgent) || str_contains(strtolower($userAgent), 'bot'),
            
            // Method ที่ไม่ปกติ
            in_array($method, ['TRACE', 'OPTIONS', 'HEAD']) && $statusCode >= 400,
            
            // พยายามเข้าถึงไฟล์ระบบ
            str_contains($path, '.env') || str_contains($path, 'config') || str_contains($path, '.git'),
            
            // SQL injection attempts
            preg_match('/(\bselect\b|\bunion\b|\binsert\b|\bdelete\b|\bdrop\b)/i', $request->getQueryString() ?? ''),
            
            // XSS attempts
            preg_match('/<script|javascript:|onload=|onerror=/i', $request->getQueryString() ?? ''),
        ];
        
        // ตรวจสอบความถี่การเข้าถึงจาก IP เดียวกัน
        if (Auth::check()) {
            $recentActivities = ActivityLog::where('ip_address', $ip)
                ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                ->count();
                
            if ($recentActivities > 20) { // มากกว่า 20 requests ใน 5 นาที
                $suspiciousConditions[] = true;
            }
        }
        
        return in_array(true, $suspiciousConditions);
    }
    
    /**
     * เตรียมข้อมูล payload
     */
    private function preparePayload(Request $request): ?array
    {
        $payload = [];
        
        // เพิ่มข้อมูล query parameters
        if ($request->query()) {
            $payload['query'] = $request->query();
        }
        
        // เพิ่มข้อมูล form data (ยกเว้นรหัสผ่าน)
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $formData = $request->except(['password', 'password_confirmation', '_token', '_method']);
            if (!empty($formData)) {
                $payload['form_data'] = $formData;
            }
        }
        
        // เพิ่มข้อมูล headers ที่สำคัญ
        $importantHeaders = [
            'accept',
            'accept-encoding',
            'accept-language',
            'x-requested-with',
            'content-type'
        ];
        
        foreach ($importantHeaders as $header) {
            if ($request->hasHeader($header)) {
                $payload['headers'][$header] = $request->header($header);
            }
        }
        
        return !empty($payload) ? $payload : null;
    }
    
    /**
     * รับตำแหน่งจาก IP Address
     */
    private function getLocationFromIP(string $ip): ?string
    {
        // ในการใช้งานจริงอาจใช้ service อย่าง GeoIP
        // สำหรับตอนนี้ให้คืนค่า null
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'localhost';
        }
        
        return null;
    }
    
    /**
     * ตรวจสอบประเภทอุปกรณ์
     */
    private function getDeviceType(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        
        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'iphone') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }
        
        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }
        
        return 'desktop';
    }
    
    /**
     * ตรวจสอบเบราว์เซอร์
     */
    private function getBrowser(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        
        $browsers = [
            'Chrome' => '/Chrome\/([\d\.]+)/',
            'Firefox' => '/Firefox\/([\d\.]+)/',
            'Safari' => '/Safari\/([\d\.]+)/',
            'Edge' => '/Edge\/([\d\.]+)/',
            'Opera' => '/Opera\/([\d\.]+)/',
            'Internet Explorer' => '/MSIE ([\d\.]+)/'
        ];
        
        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * ตรวจสอบแพลตฟอร์ม
     */
    private function getPlatform(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        
        $platforms = [
            'Windows' => '/Windows NT/',
            'macOS' => '/Mac OS X/',
            'Linux' => '/Linux/',
            'iOS' => '/iPhone|iPad/',
            'Android' => '/Android/'
        ];
        
        foreach ($platforms as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }
        
        return 'Unknown';
    }
}
