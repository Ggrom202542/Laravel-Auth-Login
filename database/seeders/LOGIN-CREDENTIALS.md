# 🔑 ข้อมูลการเข้าสู่ระบบสำหรับทดสอบ

> **ข้อมูลนี้ใช้สำหรับการทดสอบเท่านั้น**  
> **⚠️ ห้ามใช้ในระบบจริง - เปลี่ยนรหัสผ่านก่อนใช้งานจริง**

---

## 👑 Super Admin Accounts

### Account 1: Super Admin หลัก
- **Username**: `superadmin`
- **Password**: `SuperAdmin123!`
- **Role**: `super_admin`
- **สิทธิ์**: เข้าถึงได้ทุกอย่างในระบบ
- **หมายเหตุ**: บัญชีหลักสำหรับการจัดการระบบ

---

## 🛡️ Admin Accounts  

### Account 2: Admin หลัก
- **Username**: `admin`
- **Password**: `Admin123!`
- **Role**: `admin`
- **สิทธิ์**: จัดการผู้ใช้, ดูรายงาน, จัดการระบบส่วนใหญ่
- **หมายเหตุ**: สำหรับทดสอบฟีเจอร์ Admin

### Account 3: Admin ทดสอบ (Email)
- **Email**: `admin@example.com`
- **Username**: `admin-test`
- **Password**: `Admin123!`
- **Role**: `admin`
- **สิทธิ์**: เช่นเดียวกับ Admin หลัก
- **หมายเหตุ**: สำหรับทดสอบ login ด้วย email

---

## 👤 User Accounts

### Account 4: User หลัก  
- **Username**: `user`
- **Password**: `User123!`
- **Role**: `user`
- **สิทธิ์**: ดูข้อมูลส่วนตัว, ใช้ฟีเจอร์พื้นฐาน
- **หมายเหตุ**: สำหรับทดสอบฟีเจอร์ User ทั่วไป

### Account 5: User ทดสอบ (Email)
- **Email**: `user@example.com`
- **Username**: `user-test`  
- **Password**: `User123!`
- **Role**: `user`
- **สิทธิ์**: เช่นเดียวกับ User หลัก
- **หมายเหตุ**: สำหรับทดสอบ login ด้วย email

---

## 🎯 การใช้งานตามสถานการณ์

### 🔍 **ทดสอบระบบสิทธิ์**
```
Super Admin → ทดสอบฟีเจอร์ทั้งหมด
Admin → ทดสอบจัดการผู้ใช้, รายงาน  
User → ทดสอบฟีเจอร์พื้นฐาน
```

### 👥 **ทดสอบ Multi-User**
```
ใช้ account หลัก + account ทดสอบ
เพื่อจำลองสถานการณ์หลายผู้ใช้
```

### 📧 **ทดสอบ Login Methods**
```
Username Login → ใช้ account หลัก (1,2,4)
Email Login → ใช้ account ทดสอบ (3,5)
```

---

## 🚨 ข้อควรระวังด้านความปลอดภัย

### ⛔ **ห้ามทำ**
- ใช้รหัสผ่านนี้ในระบบจริง
- แชร์ข้อมูลนี้กับบุคคลภายนอก
- เก็บไฟล์นี้ใน public repository

### ✅ **ต้องทำ**
- เปลี่ยนรหัสผ่านทั้งหมดก่อนใช้งานจริง
- ลบบัญชีทดสอบในระบบจริง
- ใช้ strong password ในการใช้งานจริง

---

## 🔄 การรีเซ็ตข้อมูล

### **วิธีการรีเซ็ตบัญชีทั้งหมด**
```bash
php artisan migrate:fresh --seed
```

### **วิธีการเพิ่มบัญชีทดสอบใหม่**
```bash
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=UserSeeder
```

---

## 📋 ตารางสรุป

| ลำดับ | Username | Email | Password | Role | จุดประสงค์ |
|-------|----------|-------|----------|------|------------|
| 1 | `superadmin` | - | `SuperAdmin123!` | super_admin | จัดการระบบทั้งหมด |
| 2 | `admin` | - | `Admin123!` | admin | จัดการผู้ใช้และรายงาน |
| 3 | `admin-test` | admin@example.com | `Admin123!` | admin | ทดสอบ email login |
| 4 | `user` | - | `User123!` | user | ใช้งานฟีเจอร์พื้นฐาน |
| 5 | `user-test` | user@example.com | `User123!` | user | ทดสอบ email login |

---

*🔐 ข้อมูลนี้อัปเดตล่าสุด: 18 กันยายน 2025*  
*📝 สร้างโดย: Database Seeders*