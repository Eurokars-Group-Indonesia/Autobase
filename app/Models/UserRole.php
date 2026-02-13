<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'ms_user_roles';
    protected $primaryKey = 'user_role_id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'user_id',
        'role_id',
        'brand_id',
        'assigned_date',
        'created_by',
        'updated_by',
        'unique_id',
        'is_active',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'is_active' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }
}
