<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'processing_item',
        'material_stock_1',
        'material_stock_2',
        'process1_stock',
        'process2_stock',
        'process3_stock',
        'process4_stock',
        'process5_stock',
        'process6_stock',
        'process7_stock',
        'process8_stock',
        'process9_stock',
        'process10_stock'
    ];
}
