<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Permissions
            [
                'name' => 'users.view',
                'display_name' => 'View Users',
                'description' => 'ดูรายการผู้ใช้',
                'module' => 'users'
            ],
            [
                'name' => 'users.create',
                'display_name' => 'Create Users',
                'description' => 'สร้างผู้ใช้ใหม่',
                'module' => 'users'
            ],
            [
                'name' => 'users.edit',
                'display_name' => 'Edit Users',
                'description' => 'แก้ไขข้อมูลผู้ใช้',
                'module' => 'users'
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Delete Users',
                'description' => 'ลบผู้ใช้',
                'module' => 'users'
            ],
            [
                'name' => 'users.assign_roles',
                'display_name' => 'Assign Roles to Users',
                'description' => 'กำหนดบทบาทให้ผู้ใช้',
                'module' => 'users'
            ],

            // Admin Management Permissions
            [
                'name' => 'admins.view',
                'display_name' => 'View Admins',
                'description' => 'ดูรายการแอดมิน',
                'module' => 'admin'
            ],
            [
                'name' => 'admins.create',
                'display_name' => 'Create Admins',
                'description' => 'สร้างแอดมินใหม่',
                'module' => 'admin'
            ],
            [
                'name' => 'admins.edit',
                'display_name' => 'Edit Admins',
                'description' => 'แก้ไขข้อมูลแอดมิน',
                'module' => 'admin'
            ],
            [
                'name' => 'admins.delete',
                'display_name' => 'Delete Admins',
                'description' => 'ลบแอดมิน',
                'module' => 'admin'
            ],

            // System Configuration Permissions
            [
                'name' => 'system.settings',
                'display_name' => 'System Settings',
                'description' => 'ตั้งค่าระบบ',
                'module' => 'system'
            ],
            [
                'name' => 'system.backup',
                'display_name' => 'System Backup',
                'description' => 'สำรองข้อมูลระบบ',
                'module' => 'system'
            ],
            [
                'name' => 'system.maintenance',
                'display_name' => 'System Maintenance',
                'description' => 'บำรุงรักษาระบบ',
                'module' => 'system'
            ],

            // Role & Permission Management
            [
                'name' => 'roles.view',
                'display_name' => 'View Roles',
                'description' => 'ดูบทบาท',
                'module' => 'roles'
            ],
            [
                'name' => 'roles.create',
                'display_name' => 'Create Roles',
                'description' => 'สร้างบทบาทใหม่',
                'module' => 'roles'
            ],
            [
                'name' => 'roles.edit',
                'display_name' => 'Edit Roles',
                'description' => 'แก้ไขบทบาท',
                'module' => 'roles'
            ],
            [
                'name' => 'roles.delete',
                'display_name' => 'Delete Roles',
                'description' => 'ลบบทบาท',
                'module' => 'roles'
            ],
            [
                'name' => 'permissions.manage',
                'display_name' => 'Manage Permissions',
                'description' => 'จัดการสิทธิ์',
                'module' => 'permissions'
            ],

            // Reports & Analytics
            [
                'name' => 'reports.view',
                'display_name' => 'View Reports',
                'description' => 'ดูรายงาน',
                'module' => 'reports'
            ],
            [
                'name' => 'reports.export',
                'display_name' => 'Export Reports',
                'description' => 'ส่งออกรายงาน',
                'module' => 'reports'
            ],
            [
                'name' => 'analytics.view',
                'display_name' => 'View Analytics',
                'description' => 'ดูการวิเคราะห์',
                'module' => 'analytics'
            ],

            // Activity Logs
            [
                'name' => 'activities.view',
                'display_name' => 'View Activities',
                'description' => 'ดูบันทึกกิจกรรม',
                'module' => 'activities'
            ],
            [
                'name' => 'activities.view_all',
                'display_name' => 'View All Activities',
                'description' => 'ดูบันทึกกิจกรรมของทุกคน',
                'module' => 'activities'
            ]
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        $this->command->info('✅ สร้างสิทธิ์การเข้าถึง (Permissions) เรียบร้อย: ' . count($permissions) . ' รายการ');
    }
}
