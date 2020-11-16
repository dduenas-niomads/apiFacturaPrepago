<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'bs_sales';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    const STATUS_FINISHED = 3;
    const STATUS_NULLED = 4;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // Table rows
        'id','bs_companies_id','bs_warehouses_id',
        'bs_sales_id','users_id','type_document',
        'serie','number','ticket','currency','total','subtotal',
        'discounts','taxes','taxes_info','customer_info',
        'payments_info','description','bs_ms_sales_status_id','status_info',
        'sunat_send_info','sunat_nulled_info','flag_sunat',
        // Audit
        'flag_active','created_at','updated_at','deleted_at',
    ];
    /**
     * Casting of attributes
     *
     * @var array
     */
    protected $casts = [
        'taxes_info' => 'array',
        'customer_info' => 'array',
        'payments_info' => 'array',
        'status_info' => 'array',
        'sunat_send_info' => 'array',
        'sunat_nulled_info' => 'array'
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