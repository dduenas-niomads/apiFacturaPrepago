<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'bs_products';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','bs_companies_id','bs_warehouses_id','bs_brands_id',
        'bs_ms_product_categories_id','name','description',
        'code','url_friendly','price','price_config',
        //Audit 
        'flag_active','created_at','updated_at','deleted_at',
    ];
    /**
     * Casting of attributes
     *
     * @var array
     */
    protected $casts = [
        'price_config' => 'array'
    ];    
    public function getFillable() {
        # code...
        return $this->fillable;
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand', 'bs_brands_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\MsProductCategory', 'bs_ms_product_categories_id');
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