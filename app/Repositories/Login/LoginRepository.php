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


}