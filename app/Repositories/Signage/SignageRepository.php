<?php

namespace App\Repositories\Signage;
//タブレットマスタDB
use App\Models\Tablets;
//DepartmentDB
use App\Models\Department;
//品目マスタ
use App\Models\Number;
//長期表示日
use App\Models\Long_term_date;

use App\Models\Stock;

use Illuminate\Support\Facades\DB;

// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

class SignageRepository
{
    //IPaddressで製造課を取得してくる
    public function get_department($uuid)
    {
        // dd($ip_address);
        //idのやつだけ返す
        return Tablets::where('uuid',$uuid)->value('department_id');
        
    }
    //製造課に登録されている親品番を取得
    public function get_items($department)
    {
        //7製造なら
        if($department)
        {
            //加工品目に704が含まれていたら
            $processingItems = Number::where('processing_item', 'like', '%704%')
                          ->where('department_id', $department)
                          ->pluck('processing_item')
                          ->toArray();  // 配列に変換

            return $processingItems;
        }
        //ほかの製造課でやるときに増やす

    }
    //長期の表示日付を取得する
    public function get_long_term_date()
    {
        return Long_term_date::pluck('day')->toArray();
    }
    //データベースにテーブルがあるかかくにん
    public function confirmation_exists_in_db($item_name)
    {
        if (Schema::connection('second_mysql')->hasTable($item_name)) {
            $record = DB::connection('second_mysql')->table($item_name)->get()->toArray();
            if ($record) {
                return $record;
            } else {
                return [];
            }
        }else {
            return [];
        }
    }
    //工程の在庫を取得してくる
    public function get_stock($item_name,$stock_key_name)
    {
        return Stock::where('processing_item', $item_name)->pluck($stock_key_name)->first();
    }
    //品番の結合フラグを取得してくる
    public function join_flag_confirmation($item_name)
    {
        return Number::where('processing_item', $item_name)->pluck('join_flag')->first();
    }
    //品目名称を取得
    public function get_item_names($production)
    {
        //collect_nameのNULL以外の値のかぶりなしで取得する
        $uniqueNamesCount = Number::whereNotNull('collect_name')
            ->distinct()
            ->pluck('collect_name')->toArray();

        return $uniqueNamesCount;
    }
    //品目名称から品番をすべて取得
    public function acquisition_from_item_aggregation($item_name)
    {
        return Number::whereNotNull('collect_name')
        ->where('collect_name', $item_name)
        ->pluck('processing_item')->toArray();
    }
    //テーブルに存在するか
    public function table_exists($table_name)
    {
        // テーブルが存在するか確認
        if (Schema::connection('second_mysql')->hasTable($table_name)) {
            // レコードの存在を確認
            return DB::connection('second_mysql')->table($table_name)->exists();
        }
        
        // テーブルが存在しない場合はfalseを返す
        return false;
    }

    public function info_data($item_name,$process)
    {
        if (Schema::connection('second_mysql')->hasTable($item_name)) {
            // レコードの存在を確認
            $record = DB::connection('second_mysql')->table($item_name)->pluck($process)->toArray();
            return $record;
        }
    }

    public function target_data($item_name)
    {
        if (Schema::connection('second_mysql')->hasTable($item_name)) {
            // レコードの存在を確認
            $record = DB::connection('second_mysql')->table($item_name)->pluck('target')->toArray();
            return $record;
        }
    }

    public function get_date()
    {
        
    }

}