<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'purchases';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    const START_NUMBER = 1;
    const STATUS_FINISHED = 3;
    const STATUS_CANCELLED = 4;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','license_pr_user_id','number',
        'invoice_details','receptor_details','sended_at','status',
        //Audit 
        'flag_active','created_at','updated_at','deleted_at',
    ];
    /**
     * Casting of attributes
     *
     * @var array
     */
    protected $casts = [
        'invoice_details' => 'array',
        'receptor_details' => 'array',
    ];    
    public function getFillable() {
        # code...
        return $this->fillable;
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = self::TABLE_NAME;
}