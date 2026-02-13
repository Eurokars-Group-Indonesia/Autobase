<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $table = 'hs_import';
    protected $primaryKey = 'import_id';
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'transaction_type',
        'total_row',
        'success_row',
        'error_row',
        'success_rate',
        'executed_date',
        'execution_time',
    ];

    protected $casts = [
        'executed_date' => 'datetime',
        'execution_time' => 'decimal:2',
        'success_rate' => 'decimal:2',
        'total_row' => 'integer',
        'success_row' => 'integer',
        'error_row' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
