<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $primary_key = 'id';

    protected $fillable = [
        'product_name',
        'product_code',
        'product_desc',
        'company_id',
        'user_id',
    ];

    protected $dates = ['deleted_at'];

    /* public function unit () 
    {
        return $this->hasMany('App\Models\Unit', 'unit_id', 'id');
    } */

    public function pricelist () 
    {
        return $this->hasMany('App\Models\Pricelist', 'product_id', 'id');
    }

    public function unit () 
    {
        return $this->hasMany('App\Models\Unit', 'product_id', 'id');
    }

    public function transactions () 
    {
        return $this->hasMany('App\Models\TransactionDetail', 'product_id', 'id');
    }

    public function latestPendingTransaction () 
    {
        return $this->hasMany('App\Models\TransactionDetail', 'product_id', 'id');
    }

    public function latestTransaction () 
    {
        return $this->hasMany('App\Models\TransactionDetail', 'product_id', 'id');
    }


    public function user () 
    {
        return $this->hasMany('App\Models\User', 'id', 'user_id');
    }

    public function scopeLatestPendingTransaction ($query) 
    {
        return $query->with('latestPendingTransaction', function ($qTransaction) {
            $qTransaction
                ->orderBy('created_at', 'desc')
                ->whereNotNull('is_pending_transaction')
                ->first();
        });
    }

    public function scopeLatestTransaction ($query) 
    {
        return $query->with('latestTransaction', function ($qTransaction) {
            $qTransaction
                ->orderBy('created_at', 'desc')
                ->whereNull('is_pending_transaction')
                ->first();
        });
    }
}
