<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'mysql';
    const TABLE_NAME = 'bs_companies';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','bs_ms_company_categories_id','warehouses','stores',
        'name','description','address','country_name',
        'district','city','country','postal_code','currency',
        'ecommerce_api_key', 'ecommerce_password', 'ecommerce_shared_secret',
        'ecommerce_store',
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

    public function category()
    {
        return $this->belongsTo('App\Models\MsCompanyCategory', 'bs_ms_company_categories_id');
    }

    public function warehouses()
    {
        return $this->hasMany('App\Models\Warehouse', 'bs_companies_id')
            ->select('id', 'bs_companies_id', 'commercial_name', 'flag_active');
    }

    public static function findCountryName($country)
    {
        $countries = [
            'CL' => 'CHILE',
            'PA' => 'PANAMÁ',
            'PE' => 'PERÚ',
        ];
        if (isset($countries[$country])) {
            return $countries[$country];
        } else {
            return 'OTRO';
        }
    }

    public static function findCurrencyName($currency)
    {
        $currencies = [
            'PEN' => 'SOLES (S/)',
            'USD' => 'DÓLARES ($)',
            'CLP' => 'PESO CHILENO ($)',
        ];
        if (isset($currencies[$currency])) {
            return $currencies[$currency];
        } else {
            return 'OTRO';
        }
    }

    public static function getCurrencies()
    {
        return [
            [ 'code' => 'CLP', 'name' => 'PESO CHILENO ($)' ],
            [ 'code' => 'USD', 'name' => 'DÓLARES ($)' ],
            [ 'code' => 'PEN', 'name' => 'SOLES (S/)' ],
        ];
    }

    public static function getCountries()
    {
        return [
            [ 'code' => 'CL', 'name' => 'CHILE' ],
            [ 'code' => 'PA', 'name' => 'PANAMÁ' ],
            [ 'code' => 'PE', 'name' => 'PERÚ' ],
        ];
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = self::TABLE_NAME;
}