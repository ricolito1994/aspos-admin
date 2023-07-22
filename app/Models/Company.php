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
        'company_code',
        'address',
    ];

    protected $dates = ['deleted_at'];

    public function owners () 
    {
        return $this->hasMany('App\Models\User', 'company_id', 'id')
                    ->where('is_owner', true);
    }

    public function employees () 
    {
        return $this->hasMany('App\Models\User', 'company_id', 'id');
    }

    public function branches () 
    {
        return $this->hasMany('App\Models\Branch', 'company_id', 'id');
    }
   
}
