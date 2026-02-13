<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RolePermission extends Model
{
    protected $table = 'ms_role_permissions';
    protected $primaryKey = 'role_permission_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'permission_id',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
        'unique_id',
        'is_active',
    ];

    protected $casts = [
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->unique_id)) {
                $model->unique_id = (string) Str::uuid();
            }
            if (empty($model->created_date)) {
                $model->created_date = now();
            }
            if (empty($model->is_active)) {
                $model->is_active = '1';
            }
        });

        static::updating(function ($model) {
            $model->updated_date = now();
        });
    }

    /**
     * Get the role that owns the role permission.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Get the permission that owns the role permission.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'permission_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Scope a query to only include active role permissions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', '1');
    }
}
