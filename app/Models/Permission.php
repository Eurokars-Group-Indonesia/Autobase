<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $table = 'ms_permissions';
    protected $primaryKey = 'permission_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'permission_code',
        'permission_name',
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
            if (empty($model->unique_id)) {
                $model->unique_id = (string) Str::uuid();
            }
        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ms_role_permissions', 'permission_id', 'role_id')
            ->wherePivot('is_active', '1')
            ->where('ms_role.is_active', '1');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'unique_id';
    }
}
