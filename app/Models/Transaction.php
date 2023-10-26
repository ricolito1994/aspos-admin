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
    ];

    protected $dates = ['deleted_at'];

    public function branch () 
    {
        return $this->belongsTo(Branch::class, 'id', 'branch_id');
    }

    public function company () 
    {
        return $this->belongsTo(Company::class, 'id', 'company_id');
    }

    public function itemDetails () 
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }

    public function customer () 
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
