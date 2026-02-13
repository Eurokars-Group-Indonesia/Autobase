<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $table = 'ms_role';
    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'role_id',
        'role_code',
        'role_name',
        'role_description',
        'created_by',
        'updated_by',
        'unique_id',
        'is_active',
    ];

    protected $casts = [
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'is_active' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->role_id)) {
                $lastRole = static::orderBy('role_id', 'desc')->first();
                $nextNumber = $lastRole ? (int)substr($lastRole->role_id, 3) + 1 : 1;
                $model->setAttribute('role_id', 'ROL' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT));
            }
            if (empty($model->unique_id)) {
                $model->unique_id = (string) Str::uuid();
            }
        });
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'ms_role_permissions', 'role_id', 'permission_id')
            ->wherePivot('is_active', '1')
            ->where('ms_permissions.is_active', '1');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'ms_user_roles', 'role_id', 'user_id')
            ->wherePivot('is_active', '1')
            ->where('ms_users.is_active', '1');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'ms_role_menus', 'role_id', 'menu_id')
            ->wherePivot('is_active', '1')
            ->where('ms_menus.is_active', '1')
            ->orderBy('menu_order');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'unique_id';
    }
}
