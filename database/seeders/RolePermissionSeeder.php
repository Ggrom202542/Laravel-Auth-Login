<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // กำหนดสิทธิ์ให้กับแต่ละบทบาท
        $rolePermissions = [
            'user' => [
                // User สามารถดูและแก้ไขโปรไฟล์ตัวเองได้เท่านั้น
                'activities.view', // ดูกิจกรรมตัวเอง
            ],
            
            'admin' => [
                // User Management
                'users.view',
                'users.create',
                'users.edit',
                'users.assign_roles',
                
                // Reports
                'reports.view',
                'reports.export',
                
                // Analytics
                'analytics.view',
                
                // Activities
                'activities.view',
            ],
            
            'super_admin' => [
                // All User permissions
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'users.assign_roles',
                
                // Admin Management
                'admins.view',
                'admins.create',
                'admins.edit',
                'admins.delete',
                
                // System Management
                'system.settings',
                'system.backup',
                'system.maintenance',
                
                // Role & Permission Management
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
                'permissions.manage',
                
                // Full Reports & Analytics
                'reports.view',
                'reports.export',
                'analytics.view',
                
                // Full Activity Access
                'activities.view',
                'activities.view_all',
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->command->error("❌ ไม่พบบทบาท: {$roleName}");
                continue;
            }

            $permissionIds = [];
            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                } else {
                    $this->command->warn("⚠️ ไม่พบสิทธิ์: {$permissionName}");
                }
            }

            // Sync permissions (ลบสิทธิ์เก่าและเพิ่มใหม่)
            $role->permissions()->sync($permissionIds);
            
            $this->command->info("✅ กำหนดสิทธิ์ให้ {$role->display_name}: " . count($permissionIds) . " สิทธิ์");
        }

        $this->command->info('✅ กำหนดสิทธิ์ให้บทบาทเรียบร้อยแล้ว');
    }
}
