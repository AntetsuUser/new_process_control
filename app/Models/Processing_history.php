<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Processing_history extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'processing_history';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'id',
        'category',
        'detail',
        'file_name',
        'upload_day',
        'start_date',
        'end_date',

    ];
}
