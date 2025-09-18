# 📋 คู่มือการใช้งาน Database Seeders

> **วันที่อัปเดต**: 18 กันยายน 2025  
> **เวอร์ชัน**: Laravel 11  
> **ผู้จัดทำ**: AI Assistant

## 📖 ภาพรวม

Seeders คือไฟล์ที่ใช้สร้างข้อมูลเริ่มต้นในฐานข้อมูลเพื่อการทดสอบและการใช้งานจริง ใน folder นี้มีไฟล์ seeders ทั้งหมด 13 ไฟล์ที่แบ่งหน้าที่ตามประเภทข้อมูล

---

## 🗂️ รายการไฟล์ Seeders

### 1. **DatabaseSeeder.php** 🎯
**หน้าที่**: ไฟล์หลักที่ควบคุมการรัน seeders ทั้งหมด  
**วิธีใช้**: `php artisan db:seed` หรือ `php artisan migrate:fresh --seed`  
**ลำดับการรัน**:
1. SystemSettingSeeder
2. RoleSeeder  
3. PermissionSeeder
4. RolePermissionSeeder
5. SuperAdminSeeder
6. UserSeeder
7. SecurityPolicySeeder

**ผลลัพธ์**: ระบบพร้อมใช้งานพร้อมข้อมูลเริ่มต้นครบถ้วน

---

### 2. **SystemSettingSeeder.php** ⚙️
**หน้าที่**: สร้างการตั้งค่าระบบพื้นฐาน  
**วิธีใช้**: `php artisan db:seed --class=SystemSettingSeeder`  
**ข้อมูลที่สร้าง**: 21 รายการการตั้งค่า
- การตั้งค่าความปลอดภัย
- ข้อมูลระบบทั่วไป
- การกำหนดค่าเริ่มต้น

**เมื่อไหร่ควรใช้**: เมื่อต้องการรีเซ็ตการตั้งค่าระบบ

---

### 3. **RoleSeeder.php** 👥
**หน้าที่**: สร้างบทบาทผู้ใช้ในระบบ  
**วิธีใช้**: `php artisan db:seed --class=RoleSeeder`  
**ข้อมูลที่สร้าง**: 3 บทบาท
- `user` - ผู้ใช้ทั่วไป
- `admin` - ผู้ดูแลระบบ
- `super_admin` - ผู้ดูแลระบบสูงสุด

**ความสำคัญ**: 🔥 **บังคับต้องรันก่อน** PermissionSeeder และ UserSeeder

---

### 4. **PermissionSeeder.php** 🔐
**หน้าที่**: สร้างสิทธิ์การเข้าถึงในระบบ  
**วิธีใช้**: `php artisan db:seed --class=PermissionSeeder`  
**ข้อมูลที่สร้าง**: 22 สิทธิ์
- สิทธิ์ดูข้อมูล (view)
- สิทธิ์จัดการ (manage)
- สิทธิ์ดูรายงาน (reports)

**ข้อกำหนด**: ต้องรัน RoleSeeder ก่อน

---

### 5. **RolePermissionSeeder.php** 🔗
**หน้าที่**: เชื่อมโยงบทบาทกับสิทธิ์การเข้าถึง  
**วิธีใช้**: `php artisan db:seed --class=RolePermissionSeeder`  
**การแจกจ่ายสิทธิ์**:
- **User**: 1 สิทธิ์ (view own profile)
- **Admin**: 8 สิทธิ์ (user management + reports)
- **Super Admin**: 22 สิทธิ์ (เข้าถึงได้ทุกอย่าง)

**ข้อกำหนด**: ต้องรัน RoleSeeder และ PermissionSeeder ก่อน

---

### 6. **SuperAdminSeeder.php** 👑
**หน้าที่**: สร้างบัญชีผู้ดูแลระบบหลัก  
**วิธีใช้**: `php artisan db:seed --class=SuperAdminSeeder`  
**บัญชีที่สร้าง**:
- **Super Admin**: username: `superadmin`, password: `SuperAdmin123!`
- **Admin**: username: `admin`, password: `Admin123!`
- **User**: username: `user`, password: `User123!`

**คำเตือน**: ⚠️ เปลี่ยนรหัสผ่านในการใช้งานจริง

---

### 7. **UserSeeder.php** 🧑‍💼
**หน้าที่**: สร้างผู้ใช้ทดสอบเพิ่มเติม  
**วิธีใช้**: `php artisan db:seed --class=UserSeeder`  
**บัญชีที่สร้าง**:
- **Admin Test**: email: `admin@example.com`, password: `Admin123!`
- **User Test**: email: `user@example.com`, password: `User123!`

**วัตถุประสงค์**: สำหรับทดสอบฟีเจอร์ที่ต้องใช้หลายบัญชี

---

### 8. **SecurityPolicySeeder.php** 🛡️
**หน้าที่**: สร้างนโยบายความปลอดภัย  
**วิธีใช้**: `php artisan db:seed --class=SecurityPolicySeeder`  
**นโยบายที่สร้าง**:
- IP Restriction สำหรับ Super Admin
- นโยบายรหัสผ่าน
- การตั้งค่าความปลอดภัยอื่นๆ

**ข้อกำหนด**: ต้องมี User ในระบบก่อน

---

## 🔄 Seeders เสริม (สำหรับทดสอบขั้นสูง)

### 9. **ActivityLogSeeder.php** 📊
**หน้าที่**: สร้างข้อมูลบันทึกกิจกรรมจำลอง  
**เมื่อไหร่ใช้**: ทดสอบระบบ Activity Log และ Audit Trail  
**คำสั่ง**: `php artisan db:seed --class=ActivityLogSeeder`

### 10. **AdminSessionSeeder.php** 👨‍💼
**หน้าที่**: สร้างข้อมูล Session ของ Admin จำลอง  
**เมื่อไหร่ใช้**: ทดสอบระบบ Session Management  
**คำสั่ง**: `php artisan db:seed --class=AdminSessionSeeder`

### 11. **AdminTestUserSeeder.php** 🧪
**หน้าที่**: สร้างบัญชี Admin สำหรับทดสอบเท่านั้น  
**เมื่อไหร่ใช้**: ทดสอบฟีเจอร์ Admin เฉพาะ  
**คำสั่ง**: `php artisan db:seed --class=AdminTestUserSeeder`

### 12. **MessageSeeder.php** 💬
**หน้าที่**: สร้างข้อความในระบบจำลอง  
**เมื่อไหร่ใช้**: ทดสอบระบบ Messaging  
**คำสั่ง**: `php artisan db:seed --class=MessageSeeder`

### 13. **SessionsSeeder.php** 🔗
**หน้าที่**: สร้างข้อมูล User Sessions จำลอง  
**เมื่อไหร่ใช้**: ทดสอบระบบจัดการ Session  
**คำสั่ง**: `php artisan db:seed --class=SessionsSeeder`

---

## 🎯 แนวทางการใช้งานตามสถานการณ์

### 🚀 **การใช้งานครั้งแรก**
```bash
php artisan migrate:fresh --seed
```
**ผลลัพธ์**: ระบบพร้อมใช้งานทันทีพร้อมข้อมูลเริ่มต้นครบถ้วน

### 🔄 **การรีเซ็ตข้อมูลทดสอบ**
```bash
# ลบข้อมูลทั้งหมดและสร้างใหม่
php artisan migrate:fresh --seed

# หรือเพิ่มข้อมูลเท่านั้น (ไม่ลบ migration)
php artisan db:seed
```

### 🧪 **การทดสอบเฉพาะส่วน**
```bash
# ทดสอบระบบสิทธิ์
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder  
php artisan db:seed --class=RolePermissionSeeder

# ทดสอบระบบผู้ใช้
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=UserSeeder

# ทดสอบระบบ Activity Log
php artisan db:seed --class=ActivityLogSeeder
```

### 🔍 **การทดสอบ Feature เฉพาะ**
```bash
# ทดสอบ Message System
php artisan db:seed --class=MessageSeeder

# ทดสอบ Session Management  
php artisan db:seed --class=SessionsSeeder
php artisan db:seed --class=AdminSessionSeeder

# ทดสอบ Security System
php artisan db:seed --class=SecurityPolicySeeder
```

---

## ⚠️ ข้อควรระวัง

### 🔴 **สำคัญมาก**
1. **ไม่ใช้ในระบบจริง**: ข้อมูลทดสอบมีรหัสผ่านง่าย
2. **เปลี่ยนรหัสผ่าน**: เปลี่ยนรหัสผ่าน default ก่อนใช้งานจริง
3. **ลำดับการรัน**: ปฏิบัติตามลำดับ dependency

### 🟡 **ข้อแนะนำ**
1. **Backup ก่อนรัน**: สำรองข้อมูลก่อน migrate:fresh
2. **ตัวแปร Environment**: ตรวจสอบการตั้งค่าใน .env
3. **ความจำ Database**: ข้อมูลทดสอบอาจใช้พื้นที่มาก

---

## 🔧 การแก้ไขปัญหา

### ❌ **ปัญหาที่พบบ่อย**

**1. Foreign Key Constraint Error**
```bash
# แก้ไข: รันตามลำดับที่ถูกต้อง
php artisan migrate:fresh --seed
```

**2. Duplicate Entry Error**
```bash
# แก้ไข: ลบข้อมูลเดิมก่อน
php artisan migrate:fresh
php artisan db:seed
```

**3. Column Not Found Error**
```bash
# แก้ไข: ตรวจสอบ migration ก่อน
php artisan migrate:status
php artisan migrate
```

---

## 📞 การติดต่อและสนับสนุน

หากพบปัญหาในการใช้งาน:
1. ตรวจสอบ log ใน `storage/logs/laravel.log`
2. ตรวจสอบการตั้งค่าฐานข้อมูลใน `.env`
3. อ่านเอกสารเพิ่มเติมใน `docs/`

**เอกสารอ้างอิง**:
- [Laravel Seeding Documentation](https://laravel.com/docs/seeding)
- เอกสารระบบใน `docs/README.md`

---

*📝 เอกสารนี้จัดทำขึ้นเพื่อช่วยให้การทดสอบและการใช้งาน Database Seeders เป็นไปอย่างมีประสิทธิภาพ*