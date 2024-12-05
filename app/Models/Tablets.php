<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tablets extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'tablets';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'tablet_number',
        'tablet_ip',
        'supported_item',
        'factory_id',
        'department_id',
        'uuid'
    ];
}
