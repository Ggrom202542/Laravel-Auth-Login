<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ตรวจสอบว่ามีผู้ใช้อยู่หรือไม่
        $users = User::where('status', 'active')->get();
        
        if ($users->count() < 1) {
            $this->command->info('ต้องมีผู้ใช้อย่างน้อย 1 คนเพื่อสร้างประวัติกิจกรรมตัวอย่าง');
            return;
        }

        $this->command->info('กำลังสร้างประวัติกิจกรรมตัวอย่าง...');

        // สร้างกิจกรรมตัวอย่างสำหรับ 30 วันที่ผ่านมา
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // กิจกรรมพื้นฐาน
        $activityTypes = [
            'login' => [
                'description' => 'เข้าสู่ระบบสำเร็จ',
                'weight' => 15, // ความถี่ในการเกิด
                'suspicious_chance' => 5 // % ที่จะเป็นกิจกรรมน่าสงสัย
            ],
            'logout' => [
                'description' => 'ออกจากระบบ',
                'weight' => 10,
                'suspicious_chance' => 0
            ],
            'dashboard_view' => [
                'description' => 'เข้าดูหน้า Dashboard',
                'weight' => 20,
                'suspicious_chance' => 2
            ],
            'profile_update' => [
                'description' => 'อัปเดตข้อมูลโปรไฟล์',
                'weight' => 8,
                'suspicious_chance' => 0
            ],
            'password_change' => [
                'description' => 'เปลี่ยนรหัสผ่าน',
                'weight' => 3,
                'suspicious_chance' => 0
            ],
            'message_sent' => [
                'description' => 'ส่งข้อความ',
                'weight' => 12,
                'suspicious_chance' => 1
            ],
            'message_read' => [
                'description' => 'อ่านข้อความ',
                'weight' => 15,
                'suspicious_chance' => 0
            ],
            'page_view' => [
                'description' => 'เข้าดูหน้าเว็บ',
                'weight' => 25,
                'suspicious_chance' => 3
            ],
            'data_export' => [
                'description' => 'ส่งออกข้อมูล',
                'weight' => 2,
                'suspicious_chance' => 15
            ],
            'failed_login' => [
                'description' => 'พยายามเข้าสู่ระบบแต่ไม่สำเร็จ',
                'weight' => 5,
                'suspicious_chance' => 80
            ],
            'failed_access' => [
                'description' => 'พยายามเข้าถึงหน้าที่ไม่มีสิทธิ์',
                'weight' => 3,
                'suspicious_chance' => 90
            ],
            'file_upload' => [
                'description' => 'อัปโหลดไฟล์',
                'weight' => 6,
                'suspicious_chance' => 5
            ],
            'settings_change' => [
                'description' => 'เปลี่ยนการตั้งค่า',
                'weight' => 4,
                'suspicious_chance' => 10
            ]
        ];

        // IP addresses ตัวอย่าง
        $ipAddresses = [
            '192.168.1.100',
            '192.168.1.101', 
            '192.168.1.102',
            '10.0.0.50',
            '10.0.0.51',
            '203.154.63.45', // IP ภายนอก
            '210.86.173.25', // IP ภายนอก
            '123.456.789.1', // IP ต้องสงสัย
        ];

        // User agents ตัวอย่าง
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
            // Suspicious user agents
            'curl/7.68.0',
            'python-requests/2.25.1',
            ''
        ];

        // สร้างกิจกรรมสำหรับแต่ละวัน
        $totalActivities = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // จำนวนกิจกรรมต่อวัน (สุ่มระหว่าง 10-50)
            $dailyActivities = rand(10, 50);
            
            // ถ้าเป็นวันหยุด ลดกิจกรรมลง
            if ($currentDate->isWeekend()) {
                $dailyActivities = intval($dailyActivities * 0.3);
            }

            for ($i = 0; $i < $dailyActivities; $i++) {
                $user = $users->random();
                $activityType = $this->getRandomActivityType($activityTypes);
                $activityData = $activityTypes[$activityType];
                
                // สุ่มเวลาในวัน
                $activityTime = $currentDate->copy()->addMinutes(rand(0, 1439));
                
                // สุ่ม IP และ User Agent
                $ipAddress = $ipAddresses[array_rand($ipAddresses)];
                $userAgent = $userAgents[array_rand($userAgents)];
                
                // ตรวจสอบว่าเป็นกิจกรรมที่น่าสงสัยหรือไม่
                $isSuspicious = rand(1, 100) <= $activityData['suspicious_chance'];
                
                // URL ตัวอย่าง
                $urls = [
                    '/dashboard',
                    '/profile',
                    '/profile/edit',
                    '/messages',
                    '/activities',
                    '/admin/dashboard',
                    '/admin/users',
                    '/notifications',
                    '/settings'
                ];
                
                $url = $urls[array_rand($urls)];
                
                // HTTP methods
                $methods = ['GET', 'POST', 'PUT', 'DELETE'];
                $method = $methods[array_rand($methods)];
                
                // Response status codes
                $statusCodes = $isSuspicious ? [401, 403, 404, 500] : [200, 201, 302];
                $responseStatus = $statusCodes[array_rand($statusCodes)];
                
                // Response time (milliseconds)
                $responseTime = $isSuspicious ? rand(1000, 5000) : rand(100, 800);
                
                // Device type
                $deviceTypes = ['desktop', 'mobile', 'tablet'];
                $deviceType = $deviceTypes[array_rand($deviceTypes)];
                
                // Browser
                $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
                $browser = $browsers[array_rand($browsers)];
                
                // Platform
                $platforms = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];
                $platform = $platforms[array_rand($platforms)];
                
                // Payload ตัวอย่าง
                $payload = null;
                if (in_array($activityType, ['message_sent', 'profile_update', 'settings_change'])) {
                    $payload = [
                        'form_data' => [
                            'field1' => 'value1',
                            'field2' => 'value2'
                        ]
                    ];
                }
                
                // Properties ตัวอย่าง
                $properties = [
                    'route_name' => $activityType . '.index',
                    'request_id' => Str::uuid(),
                    'session_duration' => rand(300, 7200), // วินาที
                ];
                
                if ($isSuspicious) {
                    $properties['suspicious_reason'] = $this->getSuspiciousReason($activityType);
                }

                ActivityLog::create([
                    'user_id' => $user->id,
                    'activity_type' => $activityType,
                    'description' => $activityData['description'] . ($isSuspicious ? ' (น่าสงสัย)' : ''),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'url' => 'https://example.com' . $url,
                    'method' => $method,
                    'payload' => $payload,
                    'response_status' => $responseStatus,
                    'response_time' => $responseTime / 1000, // แปลงเป็นวินาที
                    'location' => $this->getLocationFromIP($ipAddress),
                    'device_type' => $deviceType,
                    'browser' => $browser,
                    'platform' => $platform,
                    'is_suspicious' => $isSuspicious,
                    'session_id' => Str::random(40),
                    'causer_type' => User::class,
                    'causer_id' => $user->id,
                    'properties' => $properties,
                    'created_at' => $activityTime,
                    'updated_at' => $activityTime
                ]);

                $totalActivities++;
            }

            $currentDate->addDay();
        }

        $this->command->info("สร้างประวัติกิจกรรมตัวอย่างเรียบร้อยแล้ว จำนวน {$totalActivities} รายการ");
    }

    /**
     * สุ่มเลือกประเภทกิจกรรมตาม weight
     */
    private function getRandomActivityType(array $activityTypes): string
    {
        $weightedArray = [];
        
        foreach ($activityTypes as $type => $data) {
            for ($i = 0; $i < $data['weight']; $i++) {
                $weightedArray[] = $type;
            }
        }
        
        return $weightedArray[array_rand($weightedArray)];
    }

    /**
     * รับเหตุผลความน่าสงสัย
     */
    private function getSuspiciousReason(string $activityType): string
    {
        $reasons = [
            'failed_login' => 'พยายามเข้าสู่ระบบหลายครั้ง',
            'failed_access' => 'พยายามเข้าถึงหน้าที่ไม่มีสิทธิ์',
            'data_export' => 'ส่งออกข้อมูลจำนวนมาก',
            'login' => 'เข้าสู่ระบบจาก IP ที่ไม่คุ้นเคย',
            'page_view' => 'เข้าดูหน้าเว็บผิดปกติ',
            'message_sent' => 'ส่งข้อความจำนวนมาก',
            'settings_change' => 'เปลี่ยนการตั้งค่าที่สำคัญ',
            'file_upload' => 'อัปโหลดไฟล์ประเภทที่ไม่อนุญาต'
        ];

        return $reasons[$activityType] ?? 'กิจกรรมผิดปกติ';
    }

    /**
     * รับตำแหน่งจาก IP
     */
    private function getLocationFromIP(string $ip): ?string
    {
        $locations = [
            '192.168.1.100' => 'กรุงเทพฯ, ประเทศไทย',
            '192.168.1.101' => 'กรุงเทพฯ, ประเทศไทย', 
            '192.168.1.102' => 'กรุงเทพฯ, ประเทศไทย',
            '10.0.0.50' => 'เชียงใหม่, ประเทศไทย',
            '10.0.0.51' => 'เชียงใหม่, ประเทศไทย',
            '203.154.63.45' => 'สิงคโปร์',
            '210.86.173.25' => 'ญี่ปุ่น',
            '123.456.789.1' => 'ไม่ทราบ (IP น่าสงสัย)',
        ];

        return $locations[$ip] ?? null;
    }
}
