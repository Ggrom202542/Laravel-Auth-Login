<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

/**
 * Class User
 * 
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $role
 * @property string $status
 * @property string $approval_status
 * 
 * @method \Illuminate\Database\Eloquent\Collection notifications()
 * @method \Illuminate\Database\Eloquent\Collection unreadNotifications()
 * @method void notify(\Illuminate\Notifications\Notification $notification)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prefix',
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'phone',
        'username',
        'password',
        'profile_image',
        'status',
        'role',
        'approval_status',
        'registered_at',
        'approved_at',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        // Profile fields
        'bio',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'theme',
        'language',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'last_login_ip',
        'login_history',
        'profile_completed',
        'profile_completed_at',
        // Super Admin fields
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'allowed_ip_addresses',
        'admin_session_timeout', // Use correct field name
        'admin_notes', // Add this field
        'created_by_admin',
        // Remove fields that don't exist: session_timeout, allowed_login_methods, updated_by_admin
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'date_of_birth' => 'date',
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean', 
        'push_notifications' => 'boolean',
        'profile_completed' => 'boolean',
        'profile_completed_at' => 'datetime',
        'login_history' => 'array',
        // Super Admin fields
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'allowed_login_methods' => 'array',
    ];

    /**
     * Get the roles that belong to this user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot(['assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    /**
     * Get the registration approval record for this user.
     */
    public function registrationApproval()
    {
        return $this->hasOne(RegistrationApproval::class);
    }

    /**
     * Get approvals reviewed by this user (for admins).
     */
    public function reviewedApprovals()
    {
        return $this->hasMany(RegistrationApproval::class, 'reviewed_by');
    }

    /**
     * Get the activities for this user.
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get the admin sessions for this user.
     */
    public function adminSessions()
    {
        return $this->hasMany(AdminSession::class);
    }

    /**
     * Get the security policies created by this user.
     */
    public function createdSecurityPolicies()
    {
        return $this->hasMany(SecurityPolicy::class, 'created_by');
    }

    /**
     * Get the security policies that apply to this user.
     */
    public function applicableSecurityPolicies()
    {
        return SecurityPolicy::effective()
                            ->forUser($this->id, $this->role)
                            ->orderBy('priority_order');
    }

    /**
     * Get user's full name
     */
    public function getNameAttribute()
    {
        if ($this->attributes['name'] ?? false) {
            return $this->attributes['name'];
        }
        
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        return $this->roles()
                   ->whereHas('permissions', function($query) use ($permission) {
                       $query->where('name', $permission);
                   })->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($roleName, $assignedBy = null)
    {
        $role = Role::where('name', $roleName)->first();
        
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id, [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy
            ]);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute()
    {
        return trim($this->prefix . ' ' . $this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get profile image URL.
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/avatars/' . $this->profile_image);
        }
        
        return asset('images/profile/default-avatar.png');
    }

    /**
     * Check if user account is locked.
     */
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until > now();
    }

    /**
     * Check if user account is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter users by role.
     */
    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Log user activity.
     */
    public function logActivity($action, $description, $properties = null)
    {
        return UserActivity::log($this->id, $action, $description, $properties);
    }

    /**
     * Reset failed login attempts.
     */
    public function resetFailedAttempts()
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedAttempts()
    {
        $attempts = $this->failed_login_attempts + 1;
        $updateData = ['failed_login_attempts' => $attempts];

        // Lock account after 5 failed attempts for 15 minutes
        if ($attempts >= 5) {
            $updateData['locked_until'] = now()->addMinutes(15);
        }

        $this->update($updateData);
    }
}
