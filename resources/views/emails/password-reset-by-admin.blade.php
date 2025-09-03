<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รหัสผ่านถูกรีเซ็ต</title>
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
            border-bottom: 2px solid #ffc107;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #ffc107;
            margin: 0;
            font-size: 24px;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials h3 {
            margin: 0 0 10px 0;
            color: #28a745;
        }
        .credentials p {
            margin: 5px 0;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .security-notice {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .admin-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #0c5460;
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
            <h1>🔐 รหัสผ่านถูกรีเซ็ต</h1>
            <p>รหัสผ่านของคุณถูกเปลี่ยนโดยผู้ดูแลระบบ</p>
        </div>

        <div class="alert">
            <p><strong>⚠️ แจ้งเตือน:</strong></p>
            <p>รหัสผ่านของบัญชี <strong>{{ $user->email }}</strong> ถูกรีเซ็ตโดยผู้ดูแลระบบเมื่อ {{ now()->format('d/m/Y H:i:s') }} น.</p>
        </div>

        <div class="admin-info">
            <p><strong>ดำเนินการโดย:</strong> {{ $adminName }}</p>
            <p><strong>วันที่และเวลา:</strong> {{ now()->format('d/m/Y H:i:s') }} น.</p>
        </div>

        <div class="credentials">
            <h3>ข้อมูลการเข้าสู่ระบบใหม่</h3>
            <p><strong>อีเมล/ชื่อผู้ใช้:</strong> {{ $user->email }}</p>
            <p><strong>รหัสผ่านใหม่:</strong> {{ $newPassword }}</p>
        </div>

        <div class="security-notice">
            <p><strong>🔒 คำแนะนำด้านความปลอดภัย:</strong></p>
            <ul>
                <li>กรุณาเปลี่ยนรหัสผ่านใหม่หลังจากเข้าสู่ระบบ</li>
                <li>ใช้รหัสผ่านที่แข็งแรงและไม่ซ้ำกับที่อื่น</li>
                <li>อย่าแชร์ข้อมูลการเข้าสู่ระบบกับผู้อื่น</li>
                <li>หากคุณไม่ได้ขอให้รีเซ็ตรหัสผ่าน กรุณาติดต่อผู้ดูแลระบบทันที</li>
            </ul>
        </div>

        @if($user->role !== 'user')
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin: 20px 0; color: #856404;">
            <p><strong>สำหรับผู้ดูแลระบบ:</strong></p>
            <p>เนื่องจากคุณมีสิทธิ์พิเศษในระบบ กรุณาตั้งรหัสผ่านที่แข็งแรงและพิจารณาเปิดใช้งาน Two-Factor Authentication</p>
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="btn">เข้าสู่ระบบ</a>
        </div>

        <div class="footer">
            <p>หากคุณมีคำถามหรือข้อสงสัย กรุณาติดต่อผู้ดูแลระบบ</p>
            <p>อีเมลนี้ถูกส่งอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            <p>&copy; {{ date('Y') }} ระบบจัดการผู้ใช้. สงวนลิขสิทธิ์.</p>
        </div>
    </div>
</body>
</html>
