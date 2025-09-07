# Laravel Date Formatting Issues - Solution Guide

## 🔥 ปัญหาที่พบบ่อย

### Error: "Call to a member function format() on string"

```php
// ❌ ปัญหา - อาจเกิด error
{{ $user->last_login_at->format('d/m/Y H:i') }}
```

## 🔍 สาเหตุหลัก

1. **Mixed Data Types**: ข้อมูลบางตัวเป็น string, บางตัวเป็น Carbon object
2. **Missing Model Casting**: Model ไม่ได้ cast datetime fields
3. **Manual Data Assignment**: การ assign ข้อมูลโดยตรงที่ไม่ผ่าน Eloquent
4. **Database Migration Issues**: Column type ไม่ถูกต้อง
5. **API/Import Data**: ข้อมูลจาก external source เป็น string

## ✅ วิธีแก้ไขที่ถูกต้อง

### 1. ใช้ Helper Function (แนะนำ)

```php
// ✅ ปลอดภัย - ใช้ helper function
{{ safe_date_format($user->last_login_at, 'd/m/Y H:i', 'ยังไม่เคยล็อกอิน') }}
{{ safe_date_diff($user->created_at, 'ไม่ทราบ') }}
```

### 2. Manual Type Checking

```php
// ✅ ตรวจสอบ type ก่อนใช้
@if($user->last_login_at)
    @php
        $lastLogin = is_string($user->last_login_at) 
            ? \Carbon\Carbon::parse($user->last_login_at) 
            : $user->last_login_at;
    @endphp
    {{ $lastLogin->format('d/m/Y H:i') }}
@else
    ยังไม่เคยล็อกอิน
@endif
```

### 3. Model Casting (ป้องกันต้นทาง)

```php
// app/Models/User.php
protected $casts = [
    'email_verified_at' => 'datetime',
    'last_login_at' => 'datetime',  // เพิ่มบรรทัดนี้
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];
```

## 🛠️ Helper Functions ที่สร้างไว้

### safe_date_format()
```php
safe_date_format($date, $format = 'd/m/Y H:i:s', $default = 'ไม่ได้ระบุ')
```

**Parameters:**
- `$date`: ค่าวันที่ (string, Carbon, DateTime, timestamp)
- `$format`: รูปแบบการแสดงผล (default: 'd/m/Y H:i:s')
- `$default`: ค่าเริ่มต้นเมื่อไม่มีข้อมูล

**Examples:**
```php
{{ safe_date_format($user->last_login_at) }}
{{ safe_date_format($user->created_at, 'j M Y') }}
{{ safe_date_format($user->last_login_at, 'd/m/Y', 'ยังไม่เคยล็อกอิน') }}
```

### safe_date_diff()
```php
safe_date_diff($date, $default = 'ไม่ทราบ')
```

**Examples:**
```php
{{ safe_date_diff($user->last_login_at) }}  // "2 hours ago"
{{ safe_date_diff($user->created_at) }}     // "3 days ago"
```

## 📋 Best Practices

1. **ใช้ Helper Functions เสมอ** สำหรับ user input date
2. **Cast ใน Model** สำหรับ database datetime columns
3. **Validate ก่อนใช้** เมื่อรับข้อมูลจาก external source
4. **Log Errors** เพื่อ debug ปัญหา
5. **Consistent Format** ใช้รูปแบบเดียวกันทั้งแอป

## 🔧 การแก้ไขข้อมูลเก่า

```php
// Migration เพื่อ convert string เป็น datetime
Schema::table('users', function (Blueprint $table) {
    $table->datetime('last_login_at')->nullable()->change();
});

// หรือ Manual update
DB::table('users')
    ->whereNotNull('last_login_at')
    ->where('last_login_at', 'like', '%-%')
    ->chunk(100, function ($users) {
        foreach ($users as $user) {
            try {
                $carbonDate = \Carbon\Carbon::parse($user->last_login_at);
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_login_at' => $carbonDate]);
            } catch (\Exception $e) {
                Log::warning("Cannot parse date for user {$user->id}: {$user->last_login_at}");
            }
        }
    });
```

## 🎯 สรุป

ปัญหา "Call to a member function format() on string" เกิดจาก:
- **Type Confusion**: Laravel expect Carbon object แต่ได้ string
- **Missing Casting**: Model ไม่ได้ cast datetime
- **Mixed Data Sources**: ข้อมูลมาจากหลายแหล่ง

**วิธีแก้ที่ดีที่สุด:**
1. ใช้ `safe_date_format()` helper
2. เพิ่ม casting ใน Model
3. Validate ข้อมูลก่อนใช้
