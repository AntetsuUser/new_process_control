<?php
// App\Models\User_info.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;  // Authenticatableをインポート
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_info extends Authenticatable  // Authenticatableを継承
{
    use HasFactory;

    // DBとの紐付けを明示的に
    protected $table = 'user_info';

    // createを指定
    protected $fillable = [
        'name',
        'password',
        'all_departments_id',
        'positions_id',
        'ipaddress',
        'user_info',
    ];

    // 追加: パスワードを取得するメソッド（Authenticatableが提供するが、確認のため）
    public function getAuthPassword()
    {
        return $this->password;
    }
}
