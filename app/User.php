<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    const TABLE_NAME = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'bs_ms_roles_id', 'bs_companies_id', 'bs_warehouses_id',
        'name', 'lastname', 'email', 'password','country_name',
        'active', 'activation_token', 'forgot_password_token',
        'country', 'type_document', 'document_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'activation_token', 'forgot_password_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function activeLicense()
    {
        return $this->hasOne('App\Models\LicensePrUser', 'users_id')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->with('license');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'bs_companies_id')
            ->with('warehouses');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'bs_warehouses_id');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\MsRole', 'bs_ms_roles_id');
    }
}
