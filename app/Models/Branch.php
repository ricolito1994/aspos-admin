<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branch';

    protected $primary_key = 'id';

    protected $fillable = [
        'branch_name',
        'branch_address',
        'phone',
        'branch_head',
    ];

    protected $dates = ['deleted_at'];

    public function owner () 
    {
        return $this->belongsTo('App\User', 'owner_id');
    }

    public function branchHead () 
    {
        return $this->belongsTo('App\User', 'branch_head');
    }
}
