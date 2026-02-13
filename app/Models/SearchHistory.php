<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    protected $table = 'hs_search';
    protected $primaryKey = 'search_id';
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'search',
        'date_from',
        'date_to',
        'executed_date',
        'execution_time',
        'transaction_type',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'executed_date' => 'datetime',
        'execution_time' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getTransactionTypeLabel()
    {
        return $this->transaction_type === 'H' ? 'Header' : 'Body';
    }
}
