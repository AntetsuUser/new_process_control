<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_shipping extends Model
{
    use HasFactory;

    protected $table = 'product_shipping';

    protected $fillable = [
        'product_code',
        'product_text',
        'purchase_order_number',
        'requested_delivery_date',
        'delivered_quantity',
        'material_number_1',
        'material_number_2',
        'material_number_3',
        'material_number_4',
        'material_number_5',
        'status'
    ];
}
