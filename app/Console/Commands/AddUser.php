<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add {name} {password} {permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //ユーザー登録のコマンドの処理
        $name = $this->argument('name');
        $password = bcrypt($this->argument('password'));
        $permission = $this->argument('permission');


        User::create([
            'name' => $name,
            'password' => $password,
            'permission' => $permission,
        ]);

        $this->info('success');
    }
}
