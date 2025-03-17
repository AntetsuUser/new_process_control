<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // DBクエリビルダを使用
use Illuminate\Support\Facades\Hash; // パスワードのハッシュ化
use App\Models\User_info;

class AddUserInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-user-info-command
                            {name : 名前}
                            {password : パスワード}
                            {department_id : 部署ID}
                            {position_id : 役職ID}
                            {ip : 端末IPアドレス}
                            {user_name : 使用者名}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ユーザー情報をuser_infoテーブルに追加するコマンド';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $password = Hash::make($this->argument('password')); // パスワードをハッシュ化
        $departmentId = $this->argument('department_id');
        $positionId = $this->argument('position_id');
        $ipAddress = $this->argument('ip');
        $user_name = $this->argument('user_name');

        // データをuser_infoテーブルに挿入
        DB::table('user_info')->insert([
            'name' => $name,
            'password' => $password,
            'all_departments_id' => $departmentId,
            'positions_id' => $positionId,
            'ipaddress' => $ipAddress,
            'user_name' => $user_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("ユーザー「{$name}」が正常に追加されました。");
    }
}
