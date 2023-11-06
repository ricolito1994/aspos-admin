<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $primary_key = 'id';

    protected $fillable = [
        'customer_code',
        'customer_name',
        'pwd_no',
        'senior_citizen_no',
        'address',
        'customer_type',
        'created_by',
        'company_id',
    ];

    protected $dates = ['deleted_at'];
}
