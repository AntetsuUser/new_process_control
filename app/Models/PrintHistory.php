<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintHistory extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'print_history';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'characteristic_id',
        'item_name',
        'processing_item',
        'parent_name',
        'child_part_number1',
        'child_part_number2',
        'delivery_date',
        'processing_quantity',
        'start_date',
        'woker_id',
        'process',
        'workcenter',
        'capture_date',
        'processing_all',
        'long_term_all',
        'input_complete_flag'
    ];
}
