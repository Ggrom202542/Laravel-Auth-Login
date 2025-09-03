<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รหัสผ่านใหม่ - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .password-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
        }
        .password-box h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .password {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            background: white;
            padding: 15px;
            border-radius: 5px;
            border: 2px dashed #dc3545;
            text-align: center;
            letter-spacing: 2px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 20px;
            margin: 30px 0;
        }
        .warning-box h4 {
            color: #856404;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
        }
        .warning-box ul {
            color: #856404;
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table th, .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .info-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            width: 30%;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        .support-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .support-info h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .password {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 รหัสผ่านใหม่</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                เรียน คุณ{{ $user->first_name }} {{ $user->last_name }}
            </div>

            <p>ระบบได้ทำการรีเซ็ตรหัสผ่านของคุณแล้ว โดย{{ $admin ? ' ' . $admin->first_name . ' ' . $admin->last_name . ' (ผู้ดูแลระบบ)' : 'ระบบอัตโนมัติ' }}</p>

            <!-- Password Box -->
            <div class="password-box">
                <h3>รหัสผ่านชั่วคราวของคุณคือ:</h3>
                <div class="password">{{ $password }}</div>
            </div>

            <!-- Warning -->
            <div class="warning-box">
                <h4>⚠️ ข้อควรระวัง</h4>
                <ul>
                    <li><strong>กรุณาเปลี่ยนรหัสผ่านใหม่ทันที</strong> หลังจากเข้าสู่ระบบ</li>
                    <li>รหัสผ่านนี้เป็นรหัสชั่วคราว ไม่ควรใช้เป็นรหัสถาวร</li>
                    <li>ห้ามแชร์รหัสผ่านนี้กับผู้อื่น</li>
                    <li>หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาติดต่อผู้ดูแลระบบทันที</li>
                </ul>
            </div>

            <!-- Reset Information -->
            <table class="info-table">
                <tr>
                    <th>วันที่รีเซ็ต:</th>
                    <td>{{ now()->format('d/m/Y H:i:s') }} น.</td>
                </tr>
                <tr>
                    <th>ผู้ดำเนินการ:</th>
                    <td>{{ $admin ? $admin->first_name . ' ' . $admin->last_name . ' (ผู้ดูแลระบบ)' : 'ระบบอัตโนมัติ' }}</td>
                </tr>
                <tr>
                    <th>ชื่อผู้ใช้:</th>
                    <td>{{ $user->username }}</td>
                </tr>
                <tr>
                    <th>เบอร์โทรศัพท์:</th>
                    <td>{{ $user->phone }}</td>
                </tr>
            </table>

            <!-- Instructions -->
            <h3>วิธีการเข้าสู่ระบบ:</h3>
            <ol>
                <li>ไปที่หน้าเข้าสู่ระบบ</li>
                <li>ใส่ชื่อผู้ใช้: <strong>{{ $user->username }}</strong></li>
                <li>ใส่รหัสผ่านใหม่ที่ระบุข้างต้น</li>
                <li>ระบบจะขอให้เปลี่ยนรหัสผ่านใหม่</li>
                <li>ตั้งรหัสผ่านใหม่ที่ปลอดภัย</li>
            </ol>

            <!-- Support Information -->
            <div class="support-info">
                <h4>📞 ต้องการความช่วยเหลือ?</h4>
                <p>หากมีปัญหาในการเข้าสู่ระบบหรือมีคำถาม กรุณาติดต่อทีมสนับสนุน:</p>
                <p>📧 อีเมล: support@yourdomain.com</p>
                <p>📱 โทรศัพท์: 02-xxx-xxxx</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>อีเมลนี้ส่งโดยระบบอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. สงวนลิขสิทธิ์.</p>
            <p style="margin-top: 10px; font-size: 12px;">
                หากคุณได้รับอีเมลนี้โดยไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาละเว้นอีเมลนี้
            </p>
        </div>
    </div>
</body>
</html>
