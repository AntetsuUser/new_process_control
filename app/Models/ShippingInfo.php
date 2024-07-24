<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingInfo extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'shipping_info';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'item_code',
        'item_name',
        'delivery_date',
        'ordering_quantity',
        'note',
    ];
}
