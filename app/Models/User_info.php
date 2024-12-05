<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_info extends Model
{
    use HasFactory;

    // protected -> 変数をどこまで公開するか、変数の寿命に近い
    // protected -> このモデルを継承したもののみ扱える

    // DBとの紐付けを明示的に
    protected $table = 'user_info';

    // createを指定
    protected $fillable = [
        // DBに書き込まれるカラムを指定
        'name',
        'password',
        'all_departments_id',
        'positions_id',
        'ipaddress'
    ];
}
