<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DeviceManagementService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\{Auth, Log, Session};

class DeviceVerificationMiddleware
{
    protected $deviceService;

    public function __construct(DeviceManagementService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $level = 'basic'): Response
    {
        // ข้าม guest users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $deviceFingerprint = $this->deviceService->generateDeviceFingerprint($request);

        // บันทึกหรืออัปเดตอุปกรณ์
        $device = $this->deviceService->registerDevice($user, $request);

        // ตรวจสอบตามระดับความปลอดภัย
        switch ($level) {
            case 'trusted':
                if (!$this->deviceService->isDeviceTrusted($deviceFingerprint, $user->id)) {
                    return $this->redirectToVerification($request, $device, 'Device must be trusted for this action');
                }
                break;

            case 'verified':
                if ($this->deviceService->deviceNeedsVerification($deviceFingerprint, $user->id)) {
                    return $this->redirectToVerification($request, $device, 'Device verification required');
                }
                break;

            case 'basic':
            default:
                // บันทึกการใช้งานเบื้องต้น
                $device->updateActivity();
                break;
        }

        // เพิ่มข้อมูลอุปกรณ์ใน request สำหรับใช้ในการ log หรือวิเคราะห์
        $request->attributes->set('device', $device);
        $request->attributes->set('device_fingerprint', $deviceFingerprint);

        return $next($request);
    }

    /**
     * Redirect ไปยังหน้าการยืนยันอุปกรณ์
     */
    protected function redirectToVerification(Request $request, $device, string $reason): Response
    {
        Log::info('Device verification required', [
            'user_id' => Auth::id(),
            'device_fingerprint' => $device->device_fingerprint,
            'reason' => $reason,
            'url' => $request->fullUrl()
        ]);

        // เก็บ URL ปลายทางไว้หลังจากยืนยันเสร็จ
        Session::put('device_verification.intended_url', $request->fullUrl());
        Session::put('device_verification.device_id', $device->id);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Device verification required',
                'message' => $reason,
                'verification_url' => route('device.verification')
            ], 403);
        }

        return redirect()->route('device.verification')
                        ->with('warning', $reason);
    }
}
