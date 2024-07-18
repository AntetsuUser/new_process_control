<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedHistory extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'processed_data';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'characteristic_id',
        'item_name',
        'processing_item',
        'parent_name',
        'process',
        'workcenter',
        'processing_plan_quantity',
        'good_item',
        'processing_defect_item',
        'material_defect_item',
        'delivery_date',
        'capture_date',
        'input_datetime',
    ];
}
