<?php

namespace App\Repositories\Login;

use Illuminate\Support\Facades\DB;

// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

use App\Models\All_departments;
use App\Models\Positions;
use App\Models\User_info;

use Illuminate\Support\Facades\Hash;


class LoginRepository
{
    
    public function get_department()
    {
        return All_departments::get()->toArray();
    }

    public function get_position()
    {
        return Positions::get()->toArray();
    }

    public function handleSignup($name,$password,$all_departments_id,$positions_id)
    {
        User_info::create([
            'name' => $name,
            'password' => Hash::make($password),
            'all_departments_id' => $all_departments_id,
            'positions_id' => $positions_id
        ]);
    }


}