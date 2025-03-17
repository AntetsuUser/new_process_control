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

use App\Models\Number;

use App\Models\Process;

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
    public function get_registration_stock_item()
    {
       // Stockテーブルからprocessing_itemカラムを取得し配列に格納
        return Stock::select('processing_item')->pluck('processing_item')->toArray();

    }
    public function get_registration_item()
    {
       // Stockテーブルからprocessing_itemカラムを取得し配列に格納
        $names = Stock::select('processing_item')->pluck('processing_item')->toArray();

        $existingTables = [];

        foreach ($names as $item_name) {
            // テーブルが存在するか確認
            if (Schema::connection('second_mysql')->hasTable($item_name)) {
                // テーブルが存在する場合、配列に追加
                $existingTables[] = $item_name;
            }
        }

        // 存在するテーブル名の配列を返す
        return $existingTables;
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
    //設備番号から品番を取得してくる
    public function get_product_number($machine_number)
    {
       return Process::select('processing_item','processing_time','store','process_number')->where('store', 'LIKE', $machine_number . ',%')  // 先頭
            ->orWhere('store', 'LIKE', '%,' . $machine_number . ',%') // 中間
            ->orWhere('store', 'LIKE', '%,' . $machine_number)       // 最後
            ->orWhere('store', '=', $machine_number)                // 完全一致
            ->get()->toArray();  // 値だけを取り出す

    }
    //子品番から親品番を取得してくる
    public function get_Parent_Item_Number($item_name)
    {
        $flag = true; // 初期値として0を設定
        //親品番、子品番1,2、品目名称、結合flagを取得する
        $processingItem = Number::where('child_part_number1', $item_name)
                        ->orWhere('child_part_number2', $item_name)
                        ->select('processing_item')
                        ->get()
                        ->pluck('processing_item')->toArray(); 
        if (empty($processingItem)) {
           $processingItem = Number::where('processing_item', $item_name)->select('processing_item','child_part_number1','child_part_number2','item_name','join_flag')->get()->toArray();
           $flag = false;
        }
        return $processingItem;
    }

}   