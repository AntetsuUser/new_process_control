<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Additional_information extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'additional_information';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'id',
        'item_name',
        'request_date',
        'quantity',
    ];
}
