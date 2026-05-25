<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'hourly_rate',
        'device_id',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * مجموعات الصلاحيات (بالإضافة للصلاحيات المباشرة).
     */
    public function permissionGroups()
    {
        return $this->belongsToMany(PermissionGroup::class, 'permission_group_user');
    }

    /**
     * Cached permission names — يتم تحميلها مرة واحدة فقط خلال الـ Request.
     */
    protected ?array $cachedPermissionNames = null;

    /**
     * تحميل جميع أسماء الصلاحيات (direct + groups) مرة واحدة.
     */
    protected function resolvedPermissionNames(): array
    {
        if ($this->cachedPermissionNames === null) {
            $direct = $this->permissions()->pluck('name')->all();

            $fromGroups = $this->permissionGroups()
                ->with('permissions:id,name')
                ->get()
                ->flatMap(fn ($group) => $group->permissions->pluck('name'))
                ->all();

            $this->cachedPermissionNames = array_unique(array_merge($direct, $fromGroups));
        }

        return $this->cachedPermissionNames;
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($permissionName, $this->resolvedPermissionNames(), true);
    }
}
