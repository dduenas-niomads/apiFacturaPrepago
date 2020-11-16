<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePrUser extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'license_pr_user';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    const STATUS_AVAILABLE = 1;
    const STATUS_CANCELLED = 5;
    const STATUS_UNAVAILABLE = 5;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','users_id','licenses_id',
        'date_start','date_end','status',
        //Audit 
        'flag_active','created_at','updated_at','deleted_at',
    ];
    /**
     * Casting of attributes
     *
     * @var array
     */
    protected $casts = [
    ];    
    public function getFillable() {
        # code...
        return $this->fillable;
    }

    public function license()
    {
        return $this->belongsTo('App\Models\License', 'licenses_id')
            ->whereNull('deleted_at');
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = self::TABLE_NAME;
}