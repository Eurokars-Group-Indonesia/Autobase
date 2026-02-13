<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransactionBody extends Model
{
    protected $table = 'tx_body';
    protected $primaryKey = 'body_id';
    public $incrementing = true;
    protected $keyType = 'integer';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        'part_no',
        'invoice_no',
        'pos_code',
        'description',
        'qty',
        'selling_price',
        'discount',
        'extended_price',
        'menu_price',
        'vat',
        'menu_vat',
        'cost_price',
        'analysis_code',
        'invoice_status',
        'unit',
        'mins_per_unit',
        'wip_no',
        'line',
        'account_code',
        'department',
        'franchise_code',
        'sales_type',
        'warranty_code',
        'menu_flag',
        'contribution',
        'date_decard',
        'magic_1',
        'magic_2',
        'po_no',
        'grn_no',
        'menu_code',
        'labour_rates',
        'supplier_code',
        'menu_link',
        'currency_price',
        'part_or_labour',
        'operator_code',
        'operator_name',
        'pos_code',
        'created_by',
        'updated_by',
        'unique_id',
        'is_active',
    ];

    protected $casts = [
        'date_decard' => 'date',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'is_active' => 'string',
        'qty' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'extended_price' => 'decimal:2',
        'menu_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'contribution' => 'decimal:2',
        'currency_price' => 'decimal:2',
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

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'unique_id';
    }

    /**
     * Relationships
     */
    public function brand()
    {
        return $this->belongsTo(\App\Models\Brand::class, 'pos_code', 'brand_code');
    }

    /**
     * Get invoice status label
     */
    public function getInvoiceStatusLabel()
    {
        return $this->invoice_status === 'X' ? 'Closed' : 'Completed';
    }

    /**
     * Get part or labour label
     */
    public function getPartOrLabourLabel()
    {
        return $this->part_or_labour === 'P' ? 'Part' : 'Labour';
    }
}
