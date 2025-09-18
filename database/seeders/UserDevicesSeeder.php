<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDevice;
use Carbon\Carbon;

class UserDevicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $browsers = [
            'Chrome' => ['120.0.0.0', '119.0.0.0', '118.0.0.0'],
            'Firefox' => ['121.0', '120.0', '119.0'],
            'Safari' => ['17.1', '17.0', '16.6'],
            'Edge' => ['120.0.0.0', '119.0.0.0']
        ];
        $platforms = [
            'desktop' => ['Windows 11', 'Windows 10', 'macOS 14.0', 'macOS 13.6', 'Ubuntu 22.04'],
            'mobile' => ['iOS 17.1', 'iOS 16.7', 'Android 14', 'Android 13', 'Android 12'],
            'tablet' => ['iPadOS 17.1', 'iPadOS 16.7', 'Android 14', 'Android 13']
        ];
        $deviceNames = [
            'desktop' => ['คอมพิวเตอร์ Windows', 'คอมพิวเตอร์ Mac', 'คอมพิวเตอร์ Linux'],
            'mobile' => ['iPhone', 'Samsung Galaxy', 'Google Pixel', 'มือถือ Android'],
            'tablet' => ['iPad', 'Samsung Tab', 'แท็บเล็ต Android']
        ];
        
        foreach ($users as $user) {
            // สร้างอุปกรณ์ 2-5 เครื่องต่อผู้ใช้
            $deviceCount = rand(2, 5);
            
            for ($i = 0; $i < $deviceCount; $i++) {
                $deviceType = $deviceTypes[array_rand($deviceTypes)];
                $browserName = array_rand($browsers);
                $browserVersion = $browsers[$browserName][array_rand($browsers[$browserName])];
                $platform = $platforms[$deviceType][array_rand($platforms[$deviceType])];
                $deviceName = $deviceNames[$deviceType][array_rand($deviceNames[$deviceType])];
                
                // สร้าง User Agent แบบจำลอง
                $userAgent = $this->generateUserAgent($deviceType, $browserName, $browserVersion, $platform);
                
                // สร้างวันที่แบบสุ่ม
                $firstSeen = Carbon::now()->subDays(rand(1, 90));
                $lastSeen = rand(0, 1) ? Carbon::now()->subHours(rand(1, 24)) : Carbon::now()->subDays(rand(1, 7));
                
                UserDevice::create([
                    'user_id' => $user->id,
                    'device_fingerprint' => uniqid('device_', true) . '_' . $user->id . '_' . $i,
                    'device_name' => $deviceName,
                    'device_type' => $deviceType,
                    'browser_name' => $browserName,
                    'browser_version' => $browserVersion,
                    'operating_system' => $platform,
                    'platform' => $platform,
                    'user_agent' => $userAgent,
                    'ip_address' => $this->generateRandomIP(),
                    'location' => $this->generateRandomLocation(),
                    'is_trusted' => rand(0, 1),
                    'is_active' => rand(0, 1),
                    'first_seen_at' => $firstSeen,
                    'last_seen_at' => $lastSeen,
                    'last_login_at' => $lastSeen,
                    'login_count' => rand(1, 50),
                    'requires_verification' => rand(0, 1),
                    'screen_resolution' => $this->generateScreenResolution($deviceType),
                    'timezone' => 'Asia/Bangkok',
                    'language' => 'th-TH',
                    'created_at' => $firstSeen,
                    'updated_at' => $lastSeen,
                ]);
            }
        }
    }
    
    private function generateUserAgent($deviceType, $browser, $version, $platform)
    {
        switch ($browser) {
            case 'Chrome':
                if ($deviceType === 'mobile') {
                    return "Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Mobile Safari/537.36";
                } else {
                    return "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
                }
            case 'Firefox':
                return "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:{$version}) Gecko/20100101 Firefox/{$version}";
            case 'Safari':
                if ($deviceType === 'mobile') {
                    return "Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$version} Mobile/15E148 Safari/604.1";
                } else {
                    return "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{$version} Safari/605.1.15";
                }
            default:
                return "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version} Safari/537.36";
        }
    }
    
    private function generateRandomIP()
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }
    
    private function generateRandomLocation()
    {
        $locations = [
            'กรุงเทพมหานคร, ประเทศไทย',
            'เชียงใหม่, ประเทศไทย', 
            'ภูเก็ต, ประเทศไทย',
            'นครราชสีมา, ประเทศไทย',
            'สงขลา, ประเทศไทย',
            'ขอนแก่น, ประเทศไทย',
            'อุดรธานี, ประเทศไทย',
            null // บางอุปกรณ์ไม่มีข้อมูลตำแหน่ง
        ];
        
        return $locations[array_rand($locations)];
    }
    
    private function generateScreenResolution($deviceType)
    {
        switch ($deviceType) {
            case 'mobile':
                $resolutions = ['375x812', '414x896', '360x800', '412x915', '390x844'];
                break;
            case 'tablet':
                $resolutions = ['768x1024', '820x1180', '1024x1366', '800x1280'];
                break;
            default: // desktop
                $resolutions = ['1920x1080', '1366x768', '2560x1440', '1440x900', '1680x1050'];
                break;
        }
        
        return $resolutions[array_rand($resolutions)];
    }
}
