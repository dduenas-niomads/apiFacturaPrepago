<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'bs_warehouses';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','bs_companies_id','commercial_name',
        'type_document','document_number','show_address','address',
        'district','city','country','country_name','phone','email', 'series',
        'currency','flag_show_company_info','flag_delivery','flag_principal',
        'type',
        //Audit 
        'flag_active','created_at','updated_at','deleted_at',
    ];

    /**
     * Casting of attributes
     *
     * @var array
     */
    protected $casts = [
        'series' => 'array'
    ];

    public function getFillable() {
        # code...
        return $this->fillable;
    }
	public function getCreatedAtAttribute($value) {
        return date('d/m/Y H:i:s', strtotime($value));
	}
	public function getUpdatedAtAttribute($value) {
        return date('d/m/Y H:i:s', strtotime($value));
	}
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = self::TABLE_NAME;
}