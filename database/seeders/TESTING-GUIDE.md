# 🧪 แนวทางการทดสอบระบบ

> **คู่มือการทดสอบฟีเจอร์ต่างๆ ด้วย Database Seeders**

---

## 🎯 แผนการทดสอบตามฟีเจอร์

### 1. **ทดสอบระบบ Authentication & Authorization** 🔐

#### **Setup**
```bash
php artisan migrate:fresh --seed
```

#### **Test Cases**
- **Login Testing**
  - Login ด้วย username: `superadmin`
  - Login ด้วย email: `admin@example.com`
  - ทดสอบ wrong password
  - ทดสอบ account lockout

- **Role-Based Access Testing**
  - Super Admin → เข้าถึงทุกหน้า
  - Admin → เข้าถึงหน้าจัดการผู้ใช้
  - User → เข้าถึงเฉพาะโปรไฟล์

---

### 2. **ทดสอบระบบจัดการผู้ใช้** 👥

#### **Setup**
```bash
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=UserSeeder
```

#### **Test Cases**
- Login เป็น Admin (`admin` / `Admin123!`)
- ดูรายการผู้ใช้ทั้งหมด (ต้องเห็น 5 accounts)
- แก้ไขข้อมูลผู้ใช้
- เปลี่ยนสถานะผู้ใช้ (active/inactive)
- ลบผู้ใช้ (ยกเว้น Super Admin)

---

### 3. **ทดสอบระบบสิทธิ์การเข้าถึง** 🔑

#### **Setup**
```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
```

#### **Test Cases**
- ทดสอบการเข้าถึงหน้าต่างๆ ตาม role
- ทดสอบ middleware protection
- ทดสอบการแสดง/ซ่อน menu ตาม permission

---

### 4. **ทดสอบระบบความปลอดภัย** 🛡️

#### **Setup**
```bash
php artisan db:seed --class=SecurityPolicySeeder
```

#### **Test Cases**
- ทดสอบ IP restriction (ถ้ามี)
- ทดสอบ password policy
- ทดสอบ session timeout
- ทดสอบ suspicious activity detection

---

### 5. **ทดสอบระบบ Activity Log** 📊

#### **Setup**
```bash
php artisan db:seed --class=ActivityLogSeeder
```

#### **Test Cases**
- ดู activity logs ของผู้ใช้
- ตรวจสอบการบันทึกกิจกรรม login/logout
- ทดสอบ filtering logs
- ทดสอบ export logs

---

### 6. **ทดสอบระบบ Messaging** 💬

#### **Setup**
```bash
php artisan db:seed --class=MessageSeeder
```

#### **Test Cases**
- ส่งข้อความระหว่างผู้ใช้
- ตรวจสอบการแจ้งเตือน
- ทดสอบการลบข้อความ
- ทดสอบ message history

---

## 🔄 การทดสอบแบบขั้นตอน

### **Level 1: พื้นฐาน** (15 นาที)
```bash
# 1. Setup ระบบ
php artisan migrate:fresh --seed

# 2. ทดสอบ Login
- Super Admin: superadmin / SuperAdmin123!
- Admin: admin / Admin123!
- User: user / User123!

# 3. ตรวจสอบการแสดงผลตาม role
```

### **Level 2: ปานกลาง** (30 นาที)
```bash
# 1. ทดสอบการจัดการผู้ใช้
php artisan db:seed --class=UserSeeder

# 2. ทดสอบ CRUD operations
# 3. ทดสอบ role assignment
# 4. ทดสอบ permission checking
```

### **Level 3: ขั้นสูง** (60 นาที)
```bash
# 1. ทดสอบระบบความปลอดภัย
php artisan db:seed --class=SecurityPolicySeeder

# 2. ทดสอบ activity logging
php artisan db:seed --class=ActivityLogSeeder

# 3. ทดสอบ session management
php artisan db:seed --class=SessionsSeeder

# 4. ทดสอบ integration testing
```

---

## 🎲 การทดสอบแบบสุ่ม (Random Testing)

### **สร้างข้อมูลสุ่มเพิ่มเติม**
```bash
# ใช้ Factory สร้างผู้ใช้สุ่ม
php artisan tinker
>>> User::factory(10)->create()

# ใช้ Factory สร้างข้อความสุ่ม  
>>> Message::factory(50)->create()
```

### **Stress Testing**
```bash
# สร้างผู้ใช้จำนวนมาก
>>> User::factory(1000)->create()

# ทดสอบ pagination
# ทดสอบ search functionality
# ทดสอบ performance
```

---

## 📋 Checklist การทดสอบ

### **ก่อนเริ่มทดสอบ** ✅
- [ ] ตรวจสอบ `.env` configuration
- [ ] ตรวจสอบ database connection
- [ ] Backup ข้อมูลเดิม (ถ้ามี)
- [ ] Clear cache: `php artisan cache:clear`

### **ระหว่างการทดสอบ** ✅
- [ ] ตรวจสอบ error logs
- [ ] บันทึกผลการทดสอบ
- [ ] ทดสอบ cross-browser (ถ้าจำเป็น)
- [ ] ทดสอบ responsive design

### **หลังการทดสอบ** ✅
- [ ] รีเซ็ตข้อมูลทดสอบ
- [ ] อัปเดต documentation
- [ ] รายงานปัญหาที่พบ
- [ ] วางแผนการแก้ไข

---

## 🐛 การจัดการปัญหาที่อาจพบ

### **ปัญหา Database**
```bash
# Reset ข้อมูล
php artisan migrate:fresh --seed

# ตรวจสอบ migration status
php artisan migrate:status

# ตรวจสอบ seeder class
php artisan db:seed --class=ClassName --verbose
```

### **ปัญหา Permission**
```bash
# ตรวจสอบ file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

### **ปัญหา Login**
```bash
# ตรวจสอบ user table
php artisan tinker
>>> User::all(['username', 'email', 'role', 'status'])

# Reset password
>>> User::where('username', 'admin')->first()->update(['password' => Hash::make('newpassword')])
```

---

## 📊 ตัวอย่างการทดสอบแบบ Manual

### **Test Case 1: Login Flow**
1. ไปที่หน้า login
2. กรอก username: `admin`
3. กรอก password: `Admin123!`
4. คลิก Login
5. **คาดหวัง**: เข้าสู่ dashboard สำเร็จ

### **Test Case 2: Access Control**
1. Login เป็น User (`user` / `User123!`)
2. พยายามเข้าถึงหน้า admin panel
3. **คาดหวัง**: ถูก redirect หรือแสดง error 403

### **Test Case 3: CRUD Operations**
1. Login เป็น Admin
2. สร้างผู้ใช้ใหม่
3. แก้ไขข้อมูลผู้ใช้
4. ลบผู้ใช้
5. **คาดหวัง**: operations ทั้งหมดทำงานถูกต้อง

---

## 📝 การบันทึกผลการทดสอบ

### **Template การรายงาน**
```
วันที่ทดสอบ: ________________
ระยะเวลา: __________________
ผู้ทดสอบ: __________________

✅ ผ่าน / ❌ ไม่ผ่าน

[ ] Authentication System
[ ] Authorization System  
[ ] User Management
[ ] Activity Logging
[ ] Security Features
[ ] Message System

ปัญหาที่พบ:
1. ________________________
2. ________________________  
3. ________________________

แนวทางแก้ไข:
1. ________________________
2. ________________________
3. ________________________
```

---

*🎯 เอกสารนี้จัดทำขึ้นเพื่อช่วยให้การทดสอบระบบเป็นไปอย่างเป็นระบบและครอบคลุม*