<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'branch';

    protected $primary_key = 'id';

    protected $fillable = [
        'branch_name',
        'branch_address',
        'phone',
        'branch_head',
        'company_id',
    ];

    protected $dates = ['deleted_at'];

    public function branchHead () 
    {
        return $this->belongsTo('App\Models\User', 'id', 'branch_head');
    }

    public function company () 
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id');
    }

    public function transactions () 
    {
        return $this->hasMany('App\Models\Transaction', 'branch_id', 'id');
    }
    
}
