<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsCompanyCategory extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'bs_ms_company_categories';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','name','description',
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
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = self::TABLE_NAME;
}