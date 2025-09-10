<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ตรวจสอบว่ามีผู้ใช้อยู่หรือไม่
        $users = User::where('status', 'active')->get();
        
        if ($users->count() < 2) {
            $this->command->info('ต้องมีผู้ใช้อย่างน้อย 2 คนเพื่อสร้างข้อความตัวอย่าง');
            return;
        }

        $adminUsers = $users->whereIn('role', ['admin', 'super_admin']);
        $regularUsers = $users->where('role', 'user');

        // ข้อความตัวอย่างสำหรับทดสอบ
        $sampleMessages = [
            [
                'subject' => 'ยินดีต้อนรับสู่ระบบ',
                'body' => 'ยินดีต้อนรับเข้าสู่ระบบการจัดการผู้ใช้ของเรา ขอให้คุณใช้งานอย่างมีความสุข และหากมีข้อสงสัยสามารถติดต่อทีมงานได้ตลอดเวลา',
                'priority' => 'normal',
                'message_type' => 'system'
            ],
            [
                'subject' => 'การอัปเดตนโยบายความปลอดภัย',
                'body' => 'เรียนผู้ใช้งานทุกท่าน เราได้ทำการอัปเดตนโยบายความปลอดภัยใหม่ กรุณาตรวจสอบและอ่านรายละเอียดในเมนูการตั้งค่าความปลอดภัย',
                'priority' => 'high',
                'message_type' => 'system'
            ],
            [
                'subject' => 'การบำรุงรักษาระบบ',
                'body' => 'ระบบจะมีการบำรุงรักษาในวันเสาร์ที่ 15 กันยายน 2567 ตั้งแต่เวลา 02:00 - 06:00 น. ขออภัยในความไม่สะดวก',
                'priority' => 'urgent',
                'message_type' => 'system'
            ],
            [
                'subject' => 'ขอความช่วยเหลือการใช้งาน',
                'body' => 'สวัสดีครับ ผมมีปัญหาในการใช้งานเมนูจัดการอุปกรณ์ ไม่สามารถเพิ่มอุปกรณ์ใหม่ได้ ขอความช่วยเหลือด้วยครับ',
                'priority' => 'normal',
                'message_type' => 'user'
            ],
            [
                'subject' => 'รายงานกิจกรรมประจำสัปดาห์',
                'body' => 'เรียนผู้ดูแลระบบ ขอรายงานกิจกรรมของผู้ใช้ในสัปดาห์ที่ผ่านมา มีผู้ใช้เข้าสู่ระบบรวม 150 ครั้ง ไม่พบกิจกรรมที่น่าสงสัย',
                'priority' => 'low',
                'message_type' => 'user'
            ]
        ];

        // สร้างข้อความระบบจาก Admin ไปยังผู้ใช้ทั่วไป
        if ($adminUsers->count() > 0 && $regularUsers->count() > 0) {
            $admin = $adminUsers->first();
            
            foreach ($sampleMessages as $index => $messageData) {
                if ($messageData['message_type'] === 'system') {
                    foreach ($regularUsers->take(3) as $user) {
                        Message::create([
                            'sender_id' => $admin->id,
                            'recipient_id' => $user->id,
                            'subject' => $messageData['subject'],
                            'body' => $messageData['body'],
                            'priority' => $messageData['priority'],
                            'message_type' => $messageData['message_type'],
                            'read_at' => $index < 2 ? null : Carbon::now()->subHours(rand(1, 48)), // บางข้อความยังไม่ได้อ่าน
                            'created_at' => Carbon::now()->subDays(rand(0, 7)),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }
        }

        // สร้างข้อความระหว่างผู้ใช้
        if ($users->count() >= 2) {
            foreach ($sampleMessages as $index => $messageData) {
                if ($messageData['message_type'] === 'user') {
                    $sender = $users->random();
                    $recipient = $users->where('id', '!=', $sender->id)->random();
                    
                    $message = Message::create([
                        'sender_id' => $sender->id,
                        'recipient_id' => $recipient->id,
                        'subject' => $messageData['subject'],
                        'body' => $messageData['body'],
                        'priority' => $messageData['priority'],
                        'message_type' => $messageData['message_type'],
                        'read_at' => rand(0, 1) ? Carbon::now()->subHours(rand(1, 24)) : null,
                        'created_at' => Carbon::now()->subDays(rand(0, 5)),
                        'updated_at' => Carbon::now()
                    ]);

                    // สร้างการตอบกลับสำหรับบางข้อความ
                    if (rand(0, 1)) {
                        Message::create([
                            'sender_id' => $recipient->id,
                            'recipient_id' => $sender->id,
                            'subject' => 'Re: ' . $messageData['subject'],
                            'body' => 'ขอบคุณสำหรับข้อความของคุณ ผมจะตรวจสอบและตอบกลับให้อีกครั้ง',
                            'priority' => 'normal',
                            'message_type' => 'user',
                            'parent_id' => $message->id,
                            'read_at' => rand(0, 1) ? Carbon::now()->subHours(rand(1, 12)) : null,
                            'created_at' => Carbon::now()->subDays(rand(0, 3)),
                            'updated_at' => Carbon::now()
                        ]);

                        // อัปเดตเวลาตอบกลับของข้อความต้นฉบับ
                        $message->update(['replied_at' => Carbon::now()->subDays(rand(0, 3))]);
                    }
                }
            }
        }

        // สร้างข้อความเพิ่มเติมเพื่อการทดสอบ
        for ($i = 0; $i < 10; $i++) {
            $sender = $users->random();
            $recipient = $users->where('id', '!=', $sender->id)->random();
            
            $subjects = [
                'ขอความช่วยเหลือด่วน',
                'รายงานปัญหาระบบ',
                'ขอข้อมูลเพิ่มเติม',
                'แจ้งข้อมูลการเปลี่ยนแปลง',
                'ขอการอนุมัติ',
                'ยืนยันข้อมูล',
                'ขอบคุณสำหรับความช่วยเหลือ',
                'ติดตามสถานะ',
                'รายงานผลการทำงาน',
                'ข้อเสนอแนะ'
            ];
            
            $bodies = [
                'ขอความช่วยเหลือในการแก้ไขปัญหาที่เกิดขึ้นในระบบ',
                'พบปัญหาการใช้งานที่ต้องการการแก้ไขอย่างด่วน',
                'ต้องการข้อมูลเพิ่มเติมเพื่อดำเนินการต่อไป',
                'แจ้งให้ทราบถึงการเปลี่ยนแปลงที่เกิดขึ้น',
                'ขอการอนุมัติสำหรับการดำเนินการในเรื่องนี้',
                'ขอยืนยันข้อมูลที่ได้รับไว้',
                'ขอบคุณมากสำหรับความช่วยเหลือที่ดี',
                'ติดตามสถานะการดำเนินการล่าสุด',
                'รายงานผลการทำงานในรอบนี้',
                'มีข้อเสนอแนะเพื่อปรับปรุงระบบ'
            ];
            
            $priorities = ['low', 'normal', 'high', 'urgent'];
            
            Message::create([
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'subject' => $subjects[array_rand($subjects)],
                'body' => $bodies[array_rand($bodies)],
                'priority' => $priorities[array_rand($priorities)],
                'message_type' => 'user',
                'read_at' => rand(0, 3) ? Carbon::now()->subHours(rand(1, 72)) : null,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()
            ]);
        }

        $this->command->info('สร้างข้อความตัวอย่างเรียบร้อยแล้ว');
    }
}
