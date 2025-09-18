<?php

namespace App\Helpers;

class IpHelper
{
    /**
     * ดึง Real IP Address ของผู้ใช้งานจริง
     * รองรับ Proxy, Load Balancer, และ CDN
     */
    public static function getRealIpAddress(): string
    {
        // ลำดับความสำคัญของ Headers ที่จะตรวจสอบ
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load Balancer/Proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'HTTP_X_REAL_IP',            // Nginx
            'REMOTE_ADDR'                // Direct connection
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]); // ใช้ IP แรก (Real Client IP)
                
                // ตรวจสอบว่าเป็น IP ที่ถูกต้อง
                if (self::isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        // Fallback: ใช้ Laravel's request()->ip()
        return request()->ip() ?: '127.0.0.1';
    }

    /**
     * ตรวจสอบว่าเป็น IP Address ที่ถูกต้องหรือไม่
     */
    public static function isValidIp(string $ip): bool
    {
        // ตรวจสอบ format ของ IP
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        // ตรวจสอบว่าไม่ใช่ Local/Private IP
        if (self::isPrivateIp($ip)) {
            return false;
        }

        return true;
    }

    /**
     * ตรวจสอบว่าเป็น Private/Local IP หรือไม่
     */
    public static function isPrivateIp(string $ip): bool
    {
        $privateRanges = [
            '127.0.0.0/8',    // Loopback
            '10.0.0.0/8',     // Private Class A
            '172.16.0.0/12',  // Private Class B
            '192.168.0.0/16', // Private Class C
            '169.254.0.0/16', // Link-local
            '::1/128',        // IPv6 loopback
            'fc00::/7',       // IPv6 private
        ];

        foreach ($privateRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ตรวจสอบว่า IP อยู่ในช่วงที่กำหนดหรือไม่
     */
    public static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $mask) = explode('/', $range);
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return self::ipv4InRange($ip, $subnet, $mask);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return self::ipv6InRange($ip, $subnet, $mask);
        }

        return false;
    }

    /**
     * ตรวจสอบ IPv4 ในช่วง
     */
    private static function ipv4InRange(string $ip, string $subnet, int $mask): bool
    {
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);
        
        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * ตรวจสอบ IPv6 ในช่วง
     */
    private static function ipv6InRange(string $ip, string $subnet, int $mask): bool
    {
        $ipBinary = inet_pton($ip);
        $subnetBinary = inet_pton($subnet);
        
        if ($ipBinary === false || $subnetBinary === false) {
            return false;
        }

        $bytesToCheck = intval($mask / 8);
        $bitsToCheck = $mask % 8;

        // ตรวจสอบ bytes ที่สมบูรณ์
        for ($i = 0; $i < $bytesToCheck; $i++) {
            if ($ipBinary[$i] !== $subnetBinary[$i]) {
                return false;
            }
        }

        // ตรวจสอบ bits ที่เหลือ
        if ($bitsToCheck > 0 && $bytesToCheck < 16) {
            $mask = 0xFF << (8 - $bitsToCheck);
            return (ord($ipBinary[$bytesToCheck]) & $mask) === (ord($subnetBinary[$bytesToCheck]) & $mask);
        }

        return true;
    }

    /**
     * รับข้อมูล IP พร้อมทั้งข้อมูลเพิ่มเติม
     */
    public static function getIpInfo(): array
    {
        $realIp = self::getRealIpAddress();
        $requestIp = request()->ip();
        
        return [
            'real_ip' => $realIp,
            'request_ip' => $requestIp,
            'is_development' => app()->environment('local'),
            'is_private_ip' => self::isPrivateIp($realIp),
            'user_agent' => request()->userAgent(),
            'headers' => [
                'x_forwarded_for' => request()->header('X-Forwarded-For'),
                'x_real_ip' => request()->header('X-Real-IP'),
                'cf_connecting_ip' => request()->header('CF-Connecting-IP'),
                'client_ip' => request()->header('Client-IP'),
            ]
        ];
    }

    /**
     * สำหรับสภาพแวดล้อมการพัฒนา: จำลอง Real IP
     */
    public static function getDevMockIp(): string
    {
        // ในโหมดพัฒนา ให้จำลอง IP จากข้อมูลที่มี
        if (app()->environment('local')) {
            // สามารถใช้ IP จาก session หรือ cookie เพื่อจำลอง
            $mockIps = [
                '203.144.144.144', // True Internet Thailand
                '1.1.1.1',         // Cloudflare DNS
                '8.8.8.8',         // Google DNS
                '180.180.180.180', // AIS Thailand
                '202.44.32.1',     // NECTEC Thailand
            ];
            
            // สุ่มเลือก IP สำหรับการทดสอบ
            $userId = auth()->id() ?? 1;
            return $mockIps[$userId % count($mockIps)];
        }
        
        return self::getRealIpAddress();
    }
}