<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit';

    protected $primary_key = 'id';

    protected $fillable = [
        'unit_name',
        'parent_quantity',
        'product_id',
        'price_list_id',
        'supplier_id',
        'branch_id',
        'heirarchy',
        'is_default',
        'price_per_unit',
        'cost_per_unit',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_default' => 'boolean',
    ];


    protected function product () 
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    protected function price () 
    {
        return $this->belongsTo('App\Models\Pricelist', 'price_list_id', 'id');
    }

    protected function supplier () 
    {
        return $this->belongsTo('App\Models\Supplier', 'supplier_id', 'id');
    }
}
