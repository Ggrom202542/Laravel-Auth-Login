<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\IpHelper;
use App\Models\IpRestriction;
use Illuminate\Http\Request;

class IpInformationController extends Controller
{
    /**
     * แสดงข้อมูล IP และการจัดการ
     */
    public function index()
    {
        // ดึงข้อมูล IP ปัจจุบัน
        $ipInfo = IpHelper::getIpInfo();
        
        // ดึงรายการ IP ที่ถูกบล็อค
        $blockedIps = IpRestriction::where('type', 'blacklist')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.ip-info', compact('ipInfo', 'blockedIps'));
    }

    /**
     * ทดสอบการบล็อค IP
     */
    public function testBlock(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            // ตรวจสอบว่าเป็น Private IP หรือไม่
            if (IpHelper::isPrivateIp($request->ip_address)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบล็อค Private/Local IP ได้ เนื่องจากอาจส่งผลกระทบต่อระบบ'
                ]);
            }

            // ตรวจสอบว่าเป็น IP ของผู้ใช้ปัจจุบันหรือไม่
            $currentRealIp = IpHelper::getRealIpAddress();
            if ($request->ip_address === $currentRealIp) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบล็อค IP ของตัวคุณเองได้'
                ]);
            }

            $result = IpRestriction::blockIp(
                $request->ip_address,
                $request->reason ?: 'การทดสอบระบบ',
                $request->description
            );

            return response()->json([
                'success' => true,
                'message' => 'บล็อค IP สำเร็จ',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ทดสอบการปลดบล็อค IP
     */
    public function testUnblock(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip'
        ]);

        try {
            $result = IpRestriction::unblockIp($request->ip_address);

            return response()->json([
                'success' => true,
                'message' => 'ปลดบล็อค IP สำเร็จ',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ตรวจสอบสถานะ IP
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        try {
            $ip = $request->ip;
            
            $data = [
                'ip_address' => $ip,
                'is_blocked' => IpRestriction::isBlocked($ip),
                'is_whitelisted' => IpRestriction::isWhitelisted($ip),
                'is_private' => IpHelper::isPrivateIp($ip),
                'is_valid' => IpHelper::isValidIp($ip),
                'restriction_info' => null,
                'additional_info' => null
            ];

            // ดึงข้อมูลการจำกัดถ้ามี
            $restriction = IpRestriction::where('ip_address', $ip)
                ->where('status', 'active')
                ->first();

            if ($restriction) {
                $data['restriction_info'] = [
                    'type' => $restriction->type,
                    'reason' => $restriction->reason,
                    'created_at' => $restriction->created_at->format('d/m/Y H:i:s'),
                    'description' => $restriction->description
                ];
            }

            // เพิ่มข้อมูลเพิ่มเติม
            if (IpHelper::isPrivateIp($ip)) {
                $data['additional_info'] = 'IP นี้เป็น Private/Local IP ซึ่งอาจเป็น IP ภายในเครือข่าย';
            } elseif (!IpHelper::isValidIp($ip)) {
                $data['additional_info'] = 'IP นี้อาจไม่ใช่ Public IP ที่ถูกต้อง';
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ลบการจำกัด IP
     */
    public function destroy($id)
    {
        try {
            $restriction = IpRestriction::findOrFail($id);
            $restriction->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบการจำกัด IP สำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ดูข้อมูล Real IP สำหรับ Debug
     */
    public function debug()
    {
        $data = [
            'ip_info' => IpHelper::getIpInfo(),
            'server_variables' => [
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
                'HTTP_X_REAL_IP' => $_SERVER['HTTP_X_REAL_IP'] ?? null,
                'HTTP_CF_CONNECTING_IP' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
                'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? null,
            ],
            'request_data' => [
                'request_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'headers' => request()->headers->all()
            ],
            'environment' => [
                'app_env' => app()->environment(),
                'app_debug' => config('app.debug'),
                'server_name' => $_SERVER['SERVER_NAME'] ?? null,
                'server_port' => $_SERVER['SERVER_PORT'] ?? null,
            ]
        ];

        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    }
}