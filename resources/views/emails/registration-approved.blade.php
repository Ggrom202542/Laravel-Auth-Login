<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การสมัครสมาชิกได้รับการอนุมัติ</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #28a745;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 15px;
        }
        .content {
            margin-bottom: 30px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .info-item {
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #28a745;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #218838;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Laravel Auth System</div>
            <div class="success-icon">✅</div>
            <h1 style="color: #28a745; margin: 0;">ยินดีด้วย!</h1>
        </div>

        <div class="content">
            <p>เรียน คุณ<strong>{{ $user->name }}</strong>,</p>
            
            <p>เรามีความยินดีที่จะแจ้งให้ทราบว่า <strong>การสมัครสมาชิกของคุณได้รับการอนุมัติแล้ว</strong></p>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #28a745;">ข้อมูลการอนุมัติ</h3>
                <div class="info-item">
                    <span class="info-label">อีเมล:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">วันที่อนุมัติ:</span>
                    <span class="info-value">{{ $approvedDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">สถานะ:</span>
                    <span class="info-value" style="color: #28a745; font-weight: bold;">อนุมัติแล้ว</span>
                </div>
            </div>

            <p>ตอนนี้คุณสามารถเข้าสู่ระบบและใช้งานบริการต่างๆ ของเราได้แล้ว</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="btn">เข้าสู่ระบบ</a>
            </div>

            <div class="warning">
                <strong>หมายเหตุ:</strong> หากคุณไม่ได้สมัครสมาชิกกับเรา กรุณาติดต่อเราทันทีผ่านอีเมลนี้
            </div>
        </div>

        <div class="footer">
            <p>อีเมลนี้ส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            <p>&copy; {{ date('Y') }} Laravel Auth System. สงวนลิขสิทธิ์.</p>
            <p style="margin-top: 15px;">
                หากคุณมีปัญหาในการเข้าสู่ระบบ กรุณาติดต่อทีมสนับสนุน
            </p>
        </div>
    </div>
</body>
</html>
