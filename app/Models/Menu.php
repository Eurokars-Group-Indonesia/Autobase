<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    protected $table = 'ms_menus';
    protected $primaryKey = 'menu_id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'menu_id',
        'menu_code',
        'menu_name',
        'menu_url',
        'menu_icon',
        'parent_id',
        'menu_order',
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
            if (empty($model->menu_id)) {
                $lastMenu = static::orderBy('menu_id', 'desc')->first();
                $nextNumber = $lastMenu ? (int)substr($lastMenu->menu_id, 3) + 1 : 1;
                $model->setAttribute('menu_id', 'MNU' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT));
            }
            if (empty($model->unique_id)) {
                $model->unique_id = (string) Str::uuid();
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id', 'menu_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'menu_id')
            ->where('is_active', '1')
            ->orderBy('menu_order');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ms_role_menus', 'menu_id', 'role_id')
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
