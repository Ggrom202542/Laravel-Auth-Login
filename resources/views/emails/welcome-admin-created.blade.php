<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยินดีต้อนรับสู่ระบบ</title>
    <style>
        body {
            font-family: 'Sarabun', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .welcome-section {
            margin-bottom: 25px;
        }
        .credentials {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .credentials p {
            margin: 5px 0;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .security-tips {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-tips h4 {
            color: #0c5460;
            margin: 0 0 10px 0;
        }
        .security-tips ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .security-tips li {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ยินดีต้อนรับสู่ระบบ</h1>
            <p>บัญชีของคุณถูกสร้างโดยผู้ดูแลระบบ</p>
        </div>

        <div class="welcome-section">
            <h2>สวัสดี {{ $user->name }}</h2>
            <p>ผู้ดูแลระบบได้สร้างบัญชีใหม่สำหรับคุณในระบบจัดการผู้ใช้ คุณสามารถเข้าสู่ระบบได้โดยใช้ข้อมูลด้านล่าง:</p>
        </div>

        <div class="credentials">
            <h3>ข้อมูลการเข้าสู่ระบบ</h3>
            <p><strong>อีเมล:</strong> {{ $user->email }}</p>
            <p><strong>ชื่อผู้ใช้:</strong> {{ $user->username }}</p>
            <p><strong>รหัสผ่านเริ่มต้น:</strong> {{ $password }}</p>
            <p><strong>สถานะบัญชี:</strong> {{ ucfirst($user->status) }}</p>
            <p><strong>บทบาท:</strong> {{ ucfirst($user->role) }}</p>
        </div>

        <div class="warning">
            <p><strong>⚠️ คำเตือนด้านความปลอดภัย:</strong></p>
            <p>กรุณาเปลี่ยนรหัสผ่านหลังจากเข้าสู่ระบบครั้งแรก เพื่อความปลอดภัยของบัญชีของคุณ</p>
        </div>

        @if($user->role !== 'user')
        <div class="security-tips">
            <h4>คำแนะนำด้านความปลอดภัยสำหรับผู้ดูแลระบบ:</h4>
            <ul>
                <li>ใช้รหัสผ่านที่แข็งแรง (ความยาวอย่างน้อย 8 ตัวอักษร)</li>
                <li>เปิดใช้งาน Two-Factor Authentication (2FA) หากมี</li>
                <li>อย่าแชร์ข้อมูลการเข้าสู่ระบบกับผู้อื่น</li>
                <li>ออกจากระบบเมื่อใช้งานเสร็จ โดยเฉพาะบนคอมพิวเตอร์สาธารณะ</li>
                <li>ตรวจสอบกิจกรรมการเข้าสู่ระบบเป็นประจำ</li>
            </ul>
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="btn">เข้าสู่ระบบ</a>
        </div>

        <div class="footer">
            <p>หากคุณมีคำถามหรือต้องการความช่วยเหลือ กรุณาติดต่อผู้ดูแลระบบ</p>
            <p>&copy; {{ date('Y') }} ระบบจัดการผู้ใช้. สงวนลิขสิทธิ์.</p>
        </div>
    </div>
</body>
</html>
