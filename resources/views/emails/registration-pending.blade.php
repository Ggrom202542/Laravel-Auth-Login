<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการสมัครสมาชิก - รอการอนุมัติ</title>
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
            border-bottom: 2px solid #ffc107;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #ffc107;
            margin-bottom: 10px;
        }
        .pending-icon {
            font-size: 48px;
            color: #ffc107;
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
            border-left: 4px solid #ffc107;
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
        .status-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .status-title {
            font-weight: bold;
            color: #856404;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .status-text {
            color: #856404;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #17a2b8;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #138496;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 14px;
        }
        .timeline {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .timeline-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        .timeline-icon {
            margin-right: 15px;
            font-size: 20px;
        }
        .timeline-text {
            flex: 1;
        }
        .current {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .completed {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Laravel Auth System</div>
            <div class="pending-icon">⏳</div>
            <h1 style="color: #ffc107; margin: 0;">ยืนยันการสมัครสมาชิก</h1>
        </div>

        <div class="content">
            <p>เรียน คุณ<strong>{{ $user->name }}</strong>,</p>
            
            <p><strong>ขอขอบคุณที่สมัครสมาชิกกับเรา!</strong> เราได้รับข้อมูลการสมัครของคุณเรียบร้อยแล้ว</p>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #ffc107;">ข้อมูลการสมัคร</h3>
                <div class="info-item">
                    <span class="info-label">อีเมล:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">วันที่สมัคร:</span>
                    <span class="info-value">{{ $submittedDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">สถานะปัจจุบัน:</span>
                    <span class="info-value" style="color: #ffc107; font-weight: bold;">รอการอนุมัติ</span>
                </div>
            </div>

            <div class="status-box">
                <div class="status-title">🔄 กำลังดำเนินการพิจารณา</div>
                <div class="status-text">
                    ทีมงานของเรากำลังตรวจสอบข้อมูลการสมัครของคุณ<br>
                    โดยปกติใช้เวลา 1-3 วันทำการ
                </div>
            </div>

            <div class="timeline">
                <h4 style="margin-top: 0; color: #495057;">ขั้นตอนการอนุมัติ</h4>
                <div class="timeline-item completed">
                    <span class="timeline-icon">✅</span>
                    <div class="timeline-text">
                        <strong>ส่งข้อมูลการสมัคร</strong><br>
                        <small>{{ $submittedDate }}</small>
                    </div>
                </div>
                <div class="timeline-item current">
                    <span class="timeline-icon">⏳</span>
                    <div class="timeline-text">
                        <strong>ตรวจสอบข้อมูล</strong><br>
                        <small>กำลังดำเนินการ</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <span class="timeline-icon">📧</span>
                    <div class="timeline-text">
                        <strong>แจ้งผลการพิจารณา</strong><br>
                        <small>ภายใน 1-3 วันทำการ</small>
                    </div>
                </div>
            </div>

            <p>คุณสามารถตรวจสอบสถานะการสมัครได้ตลอดเวลาผ่านลิงก์ด้านล่าง</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $statusUrl }}" class="btn">ตรวจสอบสถานะ</a>
            </div>

            <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; color: #0c5460;">
                <strong>หมายเหตุ:</strong> 
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>กรุณาเก็บอีเมลนี้ไว้สำหรับอ้างอิง</li>
                    <li>เราจะส่งอีเมลแจ้งผลการพิจารณาให้คุณทราบ</li>
                    <li>หากไม่ได้รับอีเมลตอบกลับภายใน 5 วันทำการ กรุณาติดต่อเรา</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>อีเมลนี้ส่งโดยอัตโนมัติ กรุณาอย่าตอบกลับ</p>
            <p>&copy; {{ date('Y') }} Laravel Auth System. สงวนลิขสิทธิ์.</p>
            <p style="margin-top: 15px;">
                หากคุณมีข้อสงสัย กรุณาติดต่อทีมสนับสนุน
            </p>
        </div>
    </div>
</body>
</html>
