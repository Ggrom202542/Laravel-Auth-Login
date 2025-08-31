# 🎨 Migration Templates

## 📋 Overview
เทมเพลต Migration สำหรับการสร้างฐานข้อมูลของระบบ Authentication แบบ Role-Based

## 🗂️ Required Migrations

### 1. Roles Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_create_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Role name (user, admin, super_admin)');
            $table->string('display_name')->comment('Display name for UI');
            $table->text('description')->nullable()->comment('Role description');
            $table->boolean('is_active')->default(true)->comment('Role status');
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
```

### 2. Permissions Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_create_permissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Permission name');
            $table->string('display_name')->comment('Display name for UI');
            $table->text('description')->nullable()->comment('Permission description');
            $table->string('module')->comment('Module/Feature group');
            $table->boolean('is_active')->default(true)->comment('Permission status');
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('module');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
```

### 3. Role Permissions Pivot Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_create_role_permissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['role_id', 'permission_id']);
            
            // Indexes
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
```

### 4. User Roles Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_create_user_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable()->comment('Assignment notes');
            
            // Unique constraint - user can have multiple roles but not duplicate
            $table->unique(['user_id', 'role_id']);
            
            // Indexes
            $table->index('user_id');
            $table->index('role_id');
            $table->index('assigned_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
};
```

### 5. User Activities Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_create_user_activities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action')->comment('Action performed (login, logout, update_profile, etc.)');
            $table->text('description')->comment('Detailed description of action');
            $table->ipAddress('ip_address')->comment('User IP address');
            $table->text('user_agent')->comment('User browser/device info');
            $table->json('properties')->nullable()->comment('Additional action properties');
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_activities');
    }
};
```

### 6. System Settings Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_create_system_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Setting key');
            $table->text('value')->comment('Setting value');
            $table->string('type')->default('string')->comment('Value type (string, integer, boolean, json)');
            $table->text('description')->nullable()->comment('Setting description');
            $table->string('group')->default('general')->comment('Setting group');
            $table->boolean('is_public')->default(false)->comment('Can be accessed by non-admin users');
            $table->timestamps();
            
            // Indexes
            $table->index('key');
            $table->index('group');
            $table->index('is_public');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};
```

### 7. Enhanced Users Table
```php
<?php
// File: database/migrations/xxxx_xx_xx_xxxxxx_update_users_table_add_new_fields.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // เพิ่มฟิลด์ใหม่
            $table->string('first_name')->after('prefix')->comment('ชื่อ');
            $table->string('last_name')->after('first_name')->comment('นามสกุล');
            $table->string('profile_image')->nullable()->after('last_name')->comment('รูปโปรไฟล์');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('profile_image');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('last_login_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->softDeletes();
            
            // ลบฟิลด์เก่า
            $table->dropColumn(['name', 'user_type']);
            
            // เพิ่ม indexes
            $table->index('status');
            $table->index('last_login_at');
            $table->index('locked_until');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // เพิ่มฟิลด์เก่ากลับมา
            $table->string('name')->after('email_verified_at');
            $table->enum('user_type', ['user', 'admin', 'super_admin'])->default('user')->after('password');
            
            // ลบฟิลด์ใหม่
            $table->dropColumn([
                'first_name', 'last_name', 'profile_image', 'status',
                'last_login_at', 'failed_login_attempts', 'locked_until'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
```

## 🎯 Migration Best Practices

### 1. Naming Convention
```bash
# Format: YYYY_MM_DD_HHMMSS_action_table_name.php
2025_08_31_100000_create_roles_table.php
2025_08_31_100100_create_permissions_table.php
2025_08_31_100200_create_role_permissions_table.php
2025_08_31_100300_create_user_roles_table.php
2025_08_31_100400_create_user_activities_table.php
2025_08_31_100500_create_system_settings_table.php
2025_08_31_100600_update_users_table_add_new_fields.php
```

### 2. Migration Commands
```bash
# สร้าง migrations
php artisan make:migration create_roles_table
php artisan make:migration create_permissions_table
php artisan make:migration create_role_permissions_table
php artisan make:migration create_user_roles_table
php artisan make:migration create_user_activities_table
php artisan make:migration create_system_settings_table
php artisan make:migration update_users_table_add_new_fields

# รัน migrations
php artisan migrate

# ย้อนกลับ migration ล่าสุด
php artisan migrate:rollback

# ย้อนกลับ migration ทั้งหมด
php artisan migrate:reset

# รีเฟรช migrations (rollback + migrate)
php artisan migrate:refresh

# ดู status ของ migrations
php artisan migrate:status
```

### 3. Migration Guidelines

#### Do's:
- ✅ ใช้ `foreignId()` และ `constrained()` สำหรับ foreign keys
- ✅ เพิ่ม `comment()` สำหรับทุกฟิลด์
- ✅ สร้าง indexes สำหรับฟิลด์ที่ใช้ใน WHERE, ORDER BY
- ✅ ใช้ `nullable()` เฉพาะเมื่อจำเป็น
- ✅ กำหนด `default()` value ที่เหมาะสม
- ✅ ใช้ `enum()` สำหรับฟิลด์ที่มีค่าจำกัด

#### Don'ts:
- ❌ อย่าลบ migration ที่รันแล้วใน production
- ❌ อย่าแก้ไข migration ที่รันแล้ว
- ❌ อย่าละ down() method
- ❌ อย่าใช้ raw SQL เว้นแต่จำเป็น

## 📝 Migration Checklist

### Pre-Migration
- [ ] Backup database
- [ ] Check current migration status
- [ ] Review migration files
- [ ] Test in development environment

### During Migration
- [ ] Run migrations step by step
- [ ] Check for errors
- [ ] Verify data integrity
- [ ] Test rollback functionality

### Post-Migration
- [ ] Verify all tables created correctly
- [ ] Check foreign key constraints
- [ ] Test application functionality
- [ ] Clear application caches

## 🚨 Migration Troubleshooting

### Common Issues:
1. **Foreign Key Constraint Error**
   - ตรวจสอบลำดับการสร้างตาราง
   - ตรวจสอบว่าตาราง parent มีอยู่แล้ว

2. **Column Already Exists Error**
   - ตรวจสอบ migration ที่รันไปแล้ว
   - ใช้ `Schema::hasColumn()` เพื่อตรวจสอบ

3. **Index Name Too Long Error**
   - กำหนดชื่อ index เอง
   - ใช้ชื่อสั้น ๆ

### Recovery Commands:
```bash
# กู้คืนจาก backup
mysql -u username -p database_name < backup.sql

# รัน migration ใหม่
php artisan migrate:fresh --seed

# ตรวจสอบ connection
php artisan migrate:status
```

---

**Template Version:** 1.0  
**Created:** August 31, 2025  
**Compatible:** Laravel 10.x+  
**Database:** MySQL 8.0+, PostgreSQL 13+
