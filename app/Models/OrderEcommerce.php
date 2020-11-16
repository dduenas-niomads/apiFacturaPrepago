<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class OrderEcommerce extends Model
{
    use Notifiable;

    protected $connection = 'mysql';
    const TABLE_NAME = 'bs_orders_ecommerce';
    const STATE_ACTIVE = true;
    const STATE_INACTIVE = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //Table Rows
        'id','bs_companies_id','bs_documents_id','email','total_price',
        'subtotal_price','total_discounts','total_line_items_price',
        'currency','gateway','order_number','confirmed',
        'financial_status','line_items','shipping_lines',
        'billing_address','flag_ei_send','email_sended_at',
        // Auditory
        'flag_active','created_at','updated_at','deleted_at'
    ];
    /**
     * Casting of attributes
     *
     * @var array
     */
    protected $casts = [
        'line_items' => 'array',
        'shipping_lines' => 'array',
        'billing_address' => 'array',
    ];    
    public function getFillable() {
        # code...
        return $this->fillable;
    }
	public function getCreatedAtAttribute($value) {
        return date('d/m/Y H:i:s', strtotime($value));
	}
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = self::TABLE_NAME;
}