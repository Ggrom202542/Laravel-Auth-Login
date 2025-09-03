<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การสมัครสมาชิกไม่ได้รับการอนุมัติ</title>
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
            border-bottom: 2px solid #dc3545;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 10px;
        }
        .warning-icon {
            font-size: 48px;
            color: #dc3545;
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
            border-left: 4px solid #dc3545;
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
        .reason-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .reason-title {
            font-weight: bold;
            color: #721c24;
            margin-bottom: 10px;
        }
        .reason-text {
            color: #721c24;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 14px;
        }
        .support-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Laravel Auth System</div>
            <div class="warning-icon">❌</div>
            <h1 style="color: #dc3545; margin: 0;">แจ้งผลการพิจารณา</h1>
        </div>

        <div class="content">
            <p>เรียน คุณ<strong>{{ $user->name }}</strong>,</p>
            
            <p>เราขอแจ้งให้ทราบว่า <strong>การสมัครสมาชิกของคุณไม่ได้รับการอนุมัติ</strong> ในขณะนี้</p>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #dc3545;">ข้อมูลการพิจารณา</h3>
                <div class="info-item">
                    <span class="info-label">อีเมล:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">วันที่พิจารณา:</span>
                    <span class="info-value">{{ $rejectedDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">สถานะ:</span>
                    <span class="info-value" style="color: #dc3545; font-weight: bold;">ไม่อนุมัติ</span>
                </div>
            </div>

            @if($rejectionReason)
            <div class="reason-box">
                <div class="reason-title">เหตุผลที่ไม่อนุมัติ:</div>
                <div class="reason-text">{{ $rejectionReason }}</div>
            </div>
            @endif

            <p>หากคุณต้องการสมัครสมาชิกอีกครั้ง กรุณาตรวจสอบข้อมูลให้ครบถ้วนและถูกต้อง</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $registerUrl }}" class="btn">สมัครสมาชิกอีกครั้ง</a>
            </div>

            <div class="support-box">
                <strong>ต้องการความช่วยเหลือ?</strong><br>
                หากคุณมีข้อสงสัยเกี่ยวกับการไม่อนุมัติ หรือต้องการคำแนะนำเพิ่มเติม 
                กรุณาติดต่อทีมสนับสนุนของเรา
            </div>
        </div>

        <div class="footer">
            <p>อีเมลนี้ส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            <p>&copy; {{ date('Y') }} Laravel Auth System. สงวนลิขสิทธิ์.</p>
            <p style="margin-top: 15px;">
                หากคุณต้องการความช่วยเหลือ กรุณาติดต่อทีมสนับสนุน
            </p>
        </div>
    </div>
</body>
</html>
