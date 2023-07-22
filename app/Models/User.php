<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use App\Models\Company;
use App\Models\Branch;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'designation',
        'phone',
        'branch_id',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $dates = ['deleted_at'];

    public function company () 
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id');
    }

    public function branch () 
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }

    public function selectedBranch () 
    {
        return $this->hasOne('App\Models\Branch', 'id', 'selected_branch');
    }

    public function isBranchHead () 
    {
        return $this->hasOne('App\Models\Branch', 'branch_head', 'id');
    }

    public function transactions () 
    {
        return $this->hasMany('App\Models\Transaction', 'user_id', 'id');
    }

    public function productEntry () 
    {
        return $this->hasMany('App\Models\Product', 'user_id', 'id');
    }

    public function supplierEntry () 
    {
        return $this->hasMany('App\Models\Supploer', 'user_id', 'id');
    }

}
