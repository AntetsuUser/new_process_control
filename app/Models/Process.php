<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'process';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'processing_item',
        'process_number',
        'process',
        'store',
        'processing_time',
        'number_id',
        'lot',
        'printing_max',
    ];
}
