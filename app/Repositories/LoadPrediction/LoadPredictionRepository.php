<?php
// *********************************************************************
//LoadPrediction
//
// *********************************************************************

namespace App\Repositories\LoadPrediction;
//factoryDB
use App\Models\Factory;

use App\Models\Department;

use App\Models\Equipment;

use App\Models\Long_term_date;

use App\Models\Calendar;

use App\Models\Stock;

// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

use Illuminate\Support\Facades\DB;


class LoadPredictionRepository
{
   //製造課を取得してくる
    public function department_get()
    {
        return Department::all();
    }
    // 渡された製造課idの設備の "line" と "equipment_id" を配列で返す
    public function get_machines($department_id)
    {
        return Equipment::where('department_id', $department_id)
                    ->select('line', 'equipment_id')
                    ->get()
                    ->map(function ($item) {
                        return $item->line . $item->equipment_id;
                    })
                    ->toArray();
    }

    //long_term_dateを取得
    public function get_long_term_date()
    {
        return Long_term_date::select('day')->get()->toArray();
    }
    //登録されている休日を取得
    public function get_holiday()
    {
        return Calendar::select('day')->pluck('day')->toArray();
    }
    //登録されている品番を取得
    public function get_registration_item()
    {
        return Stock::select('processing_item')->pluck('processing_item')->toArray();
    }

    public function get_long_term_quantity($item_name,$day)
    {
        if (Schema::connection('second_mysql')->hasTable($item_name)) 
        {
            // テーブルから指定された日にちのレコードを取得
            $record = DB::connection('second_mysql')
                ->table($item_name)
                ->where('day', $day)
                ->first(); // 複数のレコードを期待する場合は get() を使用

            // レコードが見つかったか確認
            if ($record) {
                return $record->target; // 'quantity' は実際のカラム名に合わせて調整
            } else {
                return null; // レコードが見つからない場合の処理
            }
        } 
        else 
        {
            return null; // レコードが見つからない場合の処理
        }
    }

}   