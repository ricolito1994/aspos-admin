<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaction_details';

    protected $primary_key = 'id';

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'item_transaction_type',
        'unit',
        'quantity',
        'price_per_unit',
        'cost_per_unit',
        'total_cost',
        'total_price',
        'remaining_balance',
        'product_id',
        'unit_id',
        'branch_id',
        'company_id',
        'supplier',
        'stock', // 1 for in, 0 for out
        'is_pending_transaction',
        'is_done_pending_transaction'
    ];

    protected $dates = ['deleted_at'];


    public function unit () 
    {
        return $this->hasMany('App\Models\Unit', 'id', 'unit_id');
    }

    public function product () 
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function transaction () 
    {
        return $this->belongsTo('App\Models\Transaction', 'transaction_id', 'id');
    }
}
