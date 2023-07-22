<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Pricelist extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pricelist';

    protected $primary_key = 'id';

    protected $fillable = [
        'pricelist_name',
        'product_id',
        'supplier_id',
        'branch_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function product () 
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function unit () 
    {
        return $this->hasMany('App\Models\Unit', 'price_list_id', 'id');
    }

    public function supplier () 
    {
        return $this->belongsTo('App\Models\Supplier', 'id', 'supplier_id');
    }

}
