# 📸 Two-Factor Authentication (2FA) - Visual Setup Guide

## 🎯 ภาพรวมการใช้งาน

เอกสารนี้แสดงขั้นตอนการใช้งาน 2FA แบบ step-by-step พร้อมภาพประกอบ

---

## 📱 Step 1: ติดตั้งแอป Authenticator

### Android Users:
```
🔗 Google Play Store → Search "Google Authenticator" → Install
📱 Alternative: Microsoft Authenticator, Authy
```

### iOS Users:
```
🔗 App Store → Search "Google Authenticator" → Install  
📱 Alternative: Microsoft Authenticator, Authy
```

---

## 🔐 Step 2: เปิดใช้งาน 2FA ในระบบ

### 2.1 เข้าสู่ระบบ
```
🌐 Browser → https://your-site.com/login
📧 Email: your-email@domain.com  
🔑 Password: your-password
🔘 Click "เข้าสู่ระบบ"
```

### 2.2 ไปที่ Profile Settings
```
👤 Click "Profile" (มุมขวาบน)
⚙️  Click "Settings" 
🔒 หาส่วน "Two-Factor Authentication"
```

### 2.3 เริ่มต้นการตั้งค่า 2FA
```
📋 หน้า 2FA Setup จะแสดง:
   ✅ ขั้นตอนที่ 1: ดาวน์โหลดแอป Authenticator (เสร็จแล้ว)
   🔘 ขั้นตอนที่ 2: คลิก "สร้าง QR Code"
```

### 2.4 สร้าง QR Code
```
🔘 Click "สร้าง QR Code" 
⏳ รอสักครู่...
📱 QR Code จะปรากฏขึ้น (สี่เหลี่ยมดำ-ขาว)
🔤 Secret Key จะแสดงด้านล่าง QR Code
```

---

## 📲 Step 3: สแกน QR Code ด้วยแอป

### 3.1 เปิดแอป Google Authenticator
```
📱 เปิดแอป Google Authenticator
➕ Click "+" หรือ "Add account"
📷 เลือก "Scan QR code"
```

### 3.2 สแกน QR Code
```
📷 Point กล้องไปที่ QR Code บนหน้าจอ
✅ แอปจะสแกนและเพิ่มบัญชีอัตโนมัติ
🏷️  จะเห็น: "Your App Name (your-email@domain.com)"
🔢 รหัส 6 หลักจะเริ่มแสดง (เปลี่ยนทุก 30 วินาที)
```

### 3.3 กรณี QR Code ไม่ทำงาน
```
➕ Click "+" ในแอป Authenticator
⌨️  เลือก "Enter setup key manually"
🏷️  Account Name: Your App Name
👤 Username: your-email@domain.com  
🔑 Key: [ใส่ Secret Key จากหน้าเว็บ]
💾 Click "Add"
```

---

## ✅ Step 4: ยืนยันการตั้งค่า

### 4.1 กรอกรหัสยืนยัน
```
🔢 ดูรหัส 6 หลักในแอป Authenticator
⌨️  กรอกในช่อง "รหัสยืนยัน" บนเว็บ
⏰ ตรวจสอบเวลาที่เหลือ (ไม่ให้หมดเวลา)
🔘 Click "ยืนยันและเปิดใช้งาน 2FA"
```

### 4.2 รับ Recovery Codes
```
✅ ข้อความแสดง "2FA เปิดใช้งานสำเร็จ"
📋 Recovery Codes 8 รหัสจะแสดง:
   ABCD1234
   EFGH5678  
   IJKL9012
   ... (รวม 8 รหัส)

💾 Click "Download Recovery Codes"
🖨️  หรือ Print และเก็บไว้ในที่ปลอดภัย
⚠️  รหัสเหล่านี้ใช้ได้เพียงครั้งเดียว!
```

---

## 🔑 Step 5: ทดสอบการเข้าสู่ระบบ

### 5.1 Logout และ Login ใหม่
```
🚪 Click "Logout"
🔗 ไปที่หน้า Login
📧 กรอก Email + Password
🔘 Click "เข้าสู่ระบบ"
```

### 5.2 ใส่รหัส 2FA
```
🔐 หน้า "Two-Factor Authentication Challenge" จะปรากฏ
📱 เปิดแอป Google Authenticator
🔢 ดูรหัส 6 หลักสำหรับ "Your App Name"
⌨️  กรอกรหัสในช่อง "รหัสยืนยัน"
🔘 Click "ยืนยัน"
✅ เข้าสู่ระบบสำเร็จ!
```

---

## 🆘 Step 6: การใช้ Recovery Codes (กรณีฉุกเฉิน)

### เมื่อไหร่ใช้ Recovery Codes:
- 📱 มือถือหาย/เสีย
- 🔧 แอป Authenticator ขัดข้อง  
- 🔄 Reset มือถือ
- ❌ ลืมสแกน QR Code ใหม่

### วิธีใช้:
```
🔐 หน้า 2FA Challenge
🔗 Click "ใช้ Recovery Code"
🔤 กรอก Recovery Code (8 ตัวอักษร)
🔘 Click "ยืนยัน"
✅ เข้าสู่ระบบได้

⚠️  Recovery Code จะถูกลบหลังใช้แล้ว!
```

### หลังใช้ Recovery Code:
```
👤 ไปที่ Profile → 2FA Settings
🔄 Click "ปิดใช้งาน 2FA"
🔁 ตั้งค่า 2FA ใหม่ทั้งหมด
📋 รับ Recovery Codes ใหม่
```

---

## 🔧 การจัดการ 2FA

### ดู Recovery Codes ที่เหลือ:
```
👤 Profile → Two-Factor Authentication
📋 ส่วน "Recovery Codes Management"
👁️  Click "แสดง Recovery Codes"
🔢 จะเห็นจำนวนที่เหลือใช้งาน
```

### สร้าง Recovery Codes ใหม่:
```
📋 Recovery Codes Management
🔄 Click "สร้าง Recovery Codes ใหม่"
⚠️  Recovery Codes เก่าจะถูกยกเลิก
💾 Download/Print Recovery Codes ใหม่
```

### ปิดใช้งาน 2FA:
```
👤 Profile → Two-Factor Authentication  
🔓 Click "ปิดใช้งาน 2FA"
⚠️  ยืนยันด้วยรหัสผ่าน
✅ 2FA ถูกปิดใช้งาน
```

---

## 🎯 Tips & Tricks

### เพื่อความปลอดภัย:
- 🔒 ตั้งรหัสผ่านในแอป Authenticator
- 🔐 เปิด Screen Lock ในมือถือ
- 💾 เก็บ Recovery Codes ในที่ปลอดภัย (ไม่ใช่รูปถ่าย)
- 🔄 อัพเดตแอป Authenticator เป็นประจำ

### เพื่อความสะดวก:
- 📝 ใช้ชื่อที่จำง่ายในแอป (เช่น "บริษัท ABC")
- ⏰ ตรวจสอบเวลาเซิร์ฟเวอร์และมือถือให้ตรงกัน
- 📱 ติดตั้งแอป Authenticator ในหลายอุปกรณ์ (ถ้าเป็นไปได้)
- 🔄 ทดสอบ 2FA หลังตั้งค่าเสร็จ

---

## 🚨 แก้ไขปัญหาเร่งด่วน

### QR Code ไม่ขึ้น:
```
🔄 Refresh หน้าเว็บ
🧹 Clear Browser Cache
🔄 ลองใหม่อีกครั้ง
📞 ติดต่อ IT Support
```

### รหัส 2FA ไม่ผ่าน:
```
⏰ ตรวจสอบเวลาในมือถือ
🔄 รอรหัสใหม่ (30 วินาที)
📱 ตรวจสอบ Account ที่ถูกต้อง
🔤 ใช้ Recovery Code แทน
```

### หาแอป Authenticator ไม่เจอ:
```
🔍 ค้นหา: "Google Authenticator"  
🔍 ค้นหา: "Microsoft Authenticator"
🔍 ค้นหา: "2FA" หรือ "Authenticator"
```

---

## 📞 ต้องการความช่วยเหลือ?

### ติดต่อ IT Support:
- 📧 **Email**: support@yourcompany.com
- 📱 **Phone**: 02-xxx-xxxx  
- 💬 **Line**: @company_support
- 🎫 **Ticket**: https://support.yourcompany.com

### ข้อมูลที่ควรแจ้ง:
- 👤 ชื่อผู้ใช้งาน
- 📧 Email address
- 📱 ประเภทมือถือ (iPhone/Android)
- 🔧 แอป Authenticator ที่ใช้
- ❌ ข้อผิดพลาดที่เกิดขึ้น

---

**หมายเหตุ:** การใช้งาน 2FA ช่วยเพิ่มความปลอดภัยให้กับบัญชีของคุณอย่างมาก กรุณาเก็บรักษา Recovery Codes ไว้ในที่ปลอดภัยเสมอ!
