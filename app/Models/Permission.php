<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module'
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
                    ->withTimestamps();
    }

    /**
     * Scope to filter permissions by module.
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get all users who have this permission (through their roles).
     */
    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            Role::class,
            'id', // Foreign key on roles table
            'id', // Foreign key on users table
            'id', // Local key on permissions table
            'id'  // Local key on roles table
        )->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
          ->join('user_roles', 'roles.id', '=', 'user_roles.role_id')
          ->where('role_permissions.permission_id', $this->id);
    }
}
