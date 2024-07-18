<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'number';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'print_number',
        'processing_item',
        'item_name',
        'material_item',
        'collect_name',
        'child_part_number1',
        'child_part_number2',
        'factory_id',
        'department_id',
        'line',
        'join_flag',

    ];
}
