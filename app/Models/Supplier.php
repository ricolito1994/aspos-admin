<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// act as price list
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';

    protected $primary_key = 'id';

    protected $fillable = [
        'supplier_name',
        'address',
        'contact_number',
        'email',
        'contact_person',
    ];

    protected $dates = ['deleted_at'];

    public function priceList () 
    {
        return $this->hasMany('App\Models\Unit', 'supplier_id', 'id');
    }
}
