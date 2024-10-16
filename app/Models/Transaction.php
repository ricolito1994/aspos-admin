<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';

    protected $primary_key = 'id';

    protected $fillable = [
        'transaction_code',
        'transaction_type',
        'stock',
        'transaction_desc',
        'transaction_date',
        'user_id',
        'supplier_id',
        'branch_id',
        'company_id',
        'total_cost',
        'total_price',
        'customer_id',
        'change',
        'amt_released',
        'amt_received',
        'final_amt_received',
        'discount_percent',
        'discount_type',
        'item_transaction_type',
        'vat',
        'amt_released',
        'ref_transaction_id',
        'remaining_balance',
        'is_expense',
        'is_pending_transaction',
        'is_done_pending_transaction',
        'requested_by',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_expense' => 'boolean',
    ];

    public function branch () 
    {
        return $this->belongsTo('App\Models\Branch', 'id', 'branch_id');
    }

    public function company () 
    {
        return $this->belongsTo('App\Models\Company', 'id', 'company_id');
    }

    public function itemDetails () 
    {
        return $this->hasMany('App\Models\TransactionDetail', 'transaction_id', 'id');
    }

    public function refTransaction () 
    {
        return $this->belongsTo('App\Models\Transaction', 'ref_transaction_id', 'id');
    }

    public function customer () 
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id', 'id');
    }

    public function createdBy () 
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function requestedBy () 
    {
        return $this->belongsTo('App\Models\User', 'requested_by', 'id');
    }
}
