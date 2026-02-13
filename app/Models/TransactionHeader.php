<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransactionHeader extends Model
{
    protected $table = 'tx_header';
    protected $primaryKey = 'header_id';
    public $incrementing = true;
    protected $keyType = 'integer';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'pos_code',
        'invoice_no',
        'wip_no',
        'account_code',
        'customer_name',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'address_5',
        'department',
        'invoice_date',
        'magic_id',
        'document_type',
        'exchange_rate',
        'registration_no',
        'chassis',
        'mileage',
        'currency_code',
        'gross_value',
        'net_value',
        'customer_discount',
        'service_code',
        'registration_date',
        'description',
        'engine_no',
        'phone_number_1',
        'phone_number_2',
        'phone_number_3',
        'phone_number_4',
        'operator_code',
        'operator_name',
        'account_company',
        'created_by',
        'updated_by',
        'unique_id',
        'is_active',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'registration_date' => 'date',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'is_active' => 'string',
        'exchange_rate' => 'decimal:2',
        'gross_value' => 'decimal:2',
        'net_value' => 'decimal:2',
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

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'pos_code', 'brand_code');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'unique_id';
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabel()
    {
        return $this->document_type === 'I' ? 'Invoice' : 'Credit Note';
    }
}
