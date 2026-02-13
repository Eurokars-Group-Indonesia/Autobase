<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'ms_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'dealer_id',
        'name',
        'email',
        'full_name',
        'phone',
        'created_by',
        'updated_by',
        'last_login',
        'unique_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
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
            'created_date' => 'datetime',
            'updated_date' => 'datetime',
            'last_login' => 'datetime',
            'is_active' => 'string',
        ];
    }

    /**
     * Relationships
     */
    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id', 'dealer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ms_user_roles', 'user_id', 'role_id')
            ->wherePivot('is_active', '1')
            ->where('ms_role.is_active', '1')
            ->withPivot('assigned_date', 'created_by', 'created_date', 'updated_by', 'updated_date', 'unique_id');
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'ms_user_brand', 'user_id', 'brand_id')
            ->wherePivot('is_active', '1')
            ->where('ms_brand.is_active', '1')
            ->withPivot('created_by', 'created_date', 'updated_by', 'updated_date', 'unique_id');
    }

    public function userBrands()
    {
        return $this->hasMany(UserBrand::class, 'user_id', 'user_id');
    }

    /**
     * Get user's brand IDs (realtime query)
     * 
     * @return array
     */
    public function getBrandIds()
    {
        return $this->brands()->pluck('ms_brand.brand_id')->toArray();
    }

    public function hasPermission($permissionCode)
    {
        // Cek apakah user memiliki role yang memiliki permission ini
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('ms_permissions.permission_code', $permissionCode)
                    ->where('ms_permissions.is_active', '1')
                    ->where('ms_role_permissions.is_active', '1'); // Gunakan nama tabel pivot langsung
            })
            ->exists();
    }

    public function hasRole($roleCode)
    {
        return $this->roles()
            ->where('ms_role.role_code', $roleCode)
            ->where('ms_role.is_active', '1')
            ->exists();
    }

    public function getMenus()
    {
        $roleIds = $this->roles()->pluck('ms_role.role_id');
        
        return Menu::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('ms_role.role_id', $roleIds);
        })
        ->where('is_active', '1')
        ->whereNull('parent_id')
        ->with(['children' => function ($query) use ($roleIds) {
            $query->whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('ms_role.role_id', $roleIds);
            });
        }])
        ->orderBy('menu_order')
        ->get();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->unique_id)) {
                $model->unique_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'unique_id';
    }
}
