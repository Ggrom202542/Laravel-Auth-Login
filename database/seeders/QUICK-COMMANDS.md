# ⚡ คำสั่งใช้งานด่วน (Quick Commands)

> **รวมคำสั่งที่ใช้บ่อยสำหรับการทดสอบระบบ**

---

## 🚀 คำสั่งหลัก (Essential Commands)

### **รีเซ็ตระบบทั้งหมด**
```bash
php artisan migrate:fresh --seed
```
*ลบข้อมูลทั้งหมด → สร้างตารางใหม่ → เพิ่มข้อมูลเริ่มต้น*

### **รัน Seeder ทั้งหมด (เพิ่มข้อมูลเท่านั้น)**
```bash
php artisan db:seed
```
*เพิ่มข้อมูลโดยไม่ลบข้อมูลเดิม*

### **ตรวจสอบสถานะ Migration**
```bash
php artisan migrate:status
```

---

## 🎯 คำสั่งตาม Seeder แต่ละตัว

### **ระบบพื้นฐาน**
```bash
# การตั้งค่าระบบ
php artisan db:seed --class=SystemSettingSeeder

# สิทธิ์และบทบาท (รันเป็นชุด)
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder  
php artisan db:seed --class=RolePermissionSeeder
```

### **ผู้ใช้ระบบ**
```bash
# ผู้ดูแลระบบ
php artisan db:seed --class=SuperAdminSeeder

# ผู้ใช้ทดสอบ
php artisan db:seed --class=UserSeeder

# Admin ทดสอบ
php artisan db:seed --class=AdminTestUserSeeder
```

### **ระบบความปลอดภัย**
```bash
# นโยบายความปลอดภัย
php artisan db:seed --class=SecurityPolicySeeder

# Session และ Activity
php artisan db:seed --class=SessionsSeeder
php artisan db:seed --class=AdminSessionSeeder
php artisan db:seed --class=ActivityLogSeeder
```

### **ระบบเสริม**
```bash
# ระบบข้อความ
php artisan db:seed --class=MessageSeeder
```

---

## 🔧 คำสั่งแก้ไขปัญหา

### **ล้าง Cache**
```bash
# ล้าง cache ทั้งหมด
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# หรือล้างแบบครบชุด
php artisan optimize:clear
```

### **ตรวจสอบข้อมูล**
```bash
# เข้า Tinker เพื่อตรวจสอบข้อมูล
php artisan tinker

# คำสั่งใน Tinker
>>> User::count()                    # จำนวนผู้ใช้
>>> User::all(['username', 'role'])  # รายชื่อผู้ใช้
>>> Role::with('permissions')->get() # บทบาทและสิทธิ์
>>> exit                             # ออกจาก Tinker
```

### **แก้ไขปัญหา Database**
```bash
# สร้าง Database ใหม่
php artisan migrate:install

# รัน Migration อีกครั้ง
php artisan migrate

# Rollback Migration
php artisan migrate:rollback

# ตรวจสอบ Connection
php artisan tinker
>>> DB::connection()->getPdo()
```

---

## 🎲 คำสั่งสำหรับ Development

### **สร้างข้อมูลจำลองเพิ่ม**
```bash
php artisan tinker

# สร้างผู้ใช้สุ่ม 10 คน
>>> User::factory(10)->create()

# สร้างข้อความสุ่ม 50 ข้อความ
>>> Message::factory(50)->create()

# สร้างข้อมูลด้วย relationship
>>> User::factory(5)->create()->each(function($user) {
...     $user->messages()->saveMany(Message::factory(3)->make());
... })
```

### **ตรวจสอบ Performance**
```bash
# วัดเวลา query
php artisan tinker
>>> DB::enableQueryLog()
>>> User::with('role')->get()
>>> DD(DB::getQueryLog())
```

---

## 📊 คำสั่งตรวจสอบระบบ

### **ตรวจสอบ Laravel**
```bash
# ตรวจสอบเวอร์ชัน
php artisan --version

# ตรวจสอบ Environment
php artisan env

# ตรวจสอบ Config
php artisan config:show database
```

### **ตรวจสอบ Database**
```bash
# รายการตาราง
php artisan tinker
>>> DB::select('SHOW TABLES')

# ขนาด Database
>>> DB::select('SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "DB Size in MB" FROM information_schema.tables WHERE table_schema = "laravel_auth"')
```

---

## 🚨 คำสั่งฉุกเฉิน

### **กู้คืนระบบ**
```bash
# Reset ทั้งหมด
php artisan migrate:fresh --seed --force

# หรือถ้าต้องการข้าม confirmation
php artisan migrate:fresh --seed --no-interaction
```

### **สำรองข้อมูล**
```bash
# Export Database (MySQL)
mysqldump -u username -p database_name > backup.sql

# Import Database
mysql -u username -p database_name < backup.sql
```

### **ตรวจสอบ Log**
```bash
# ดู error log ล่าสุด
tail -f storage/logs/laravel.log

# ดู log วันนี้
cat storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

## 🎯 คำสั่งตามสถานการณ์

### **📋 เริ่มทำงานใหม่**
```bash
git pull origin main
composer install
php artisan migrate:fresh --seed
php artisan cache:clear
```

### **🧪 เริ่มทดสอบ**
```bash
php artisan migrate:fresh --seed
php artisan serve
# เปิดเบราว์เซอร์ไปที่ http://localhost:8000
```

### **🔄 หลังแก้ไข Code**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
# ถ้าแก้ migration: php artisan migrate
# ถ้าแก้ seeder: php artisan db:seed --class=ClassName
```

### **🚀 ก่อน Deploy**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

---

## 📱 คำสั่งบนมือถือ/Remote

### **SSH Commands**
```bash
# เชื่อมต่อ server
ssh user@your-server.com

# เข้าไปใน project directory
cd /path/to/your/project

# รัน migration
php artisan migrate:fresh --seed --force

# ตรวจสอบสถานะ
php artisan queue:work --daemon
```

---

## ⚡ Quick Copy Commands

```bash
# Full Reset (คัดลอกได้เลย)
php artisan migrate:fresh --seed && php artisan cache:clear

# Check System Status
php artisan migrate:status && php artisan config:show database

# Emergency Reset  
php artisan migrate:fresh --seed --force --no-interaction

# Development Setup
composer install && php artisan migrate:fresh --seed && php artisan serve

# Production Deploy
php artisan migrate --force && php artisan config:cache && php artisan route:cache
```

---

## 📝 บันทึกคำสั่งที่ใช้

*เก็บประวัติคำสั่งที่ใช้เพื่อการอ้างอิง*

| วันที่ | เวลา | คำสั่ง | ผลลัพธ์ | หมายเหตุ |
|--------|------|--------|---------|----------|
| | | | | |
| | | | | |
| | | | | |

---

*⚡ เอกสารนี้รวบรวมคำสั่งที่ใช้บ่อยเพื่อความสะดวกในการทำงาน*