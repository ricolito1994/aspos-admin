<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $primary_key = 'id';

    protected $fillable = [
        'company_name',
        'address',
        'owner_id',
    ];

    protected $dates = ['deleted_at'];

    public function owner () 
    {
        return $this->belongsTo('App\Models\User', 'owner_id');
    }
}
