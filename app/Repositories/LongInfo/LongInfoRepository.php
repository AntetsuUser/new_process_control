<?php
// *********************************************************************
//longinfo共通のリポジトリ
//
// *********************************************************************

namespace App\Repositories\LongInfo;
//factoryDB
use App\Models\Factory;

use App\Models\Process;

use App\Models\Number;

use App\Models\Worker;

use App\Models\Stock;

use App\Models\Long_term_date;

use App\Models\PrintHistory;


// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

use Illuminate\Support\Facades\DB;


class LongInfoRepository
{
    //工場を取得する
    public function factory_get()
    {
        return Factory::all();
    }
    //processDBから工程、工程番号、加工品目を取得
    public function line_get_process($line_numbers)
    {
        //配列で取得して返す
        return Process::where('store','REGEXP', "(^|,)$line_numbers(,|$)")->select('processing_item','process','process_number','processing_time','lot')
                    ->get()
                    ->toArray();
    }   
    public function item_get_process($item)
    {
        return Process::where('processing_item',$item)->select('process','process_number')->get()
                    ->toArray();
    }
    //作業者の名前を取得する
    public function worker_name_get($id)
    {
        return Worker::where('id',$id)->value('name');
    }


    //品番で親品番を探してくる
    public function find_parent($item_name)
    {
        $flag = true; // 初期値として0を設定
        //親品番、子品番1,2、品目名称、結合flagを取得する
        $processingItem = Number::where('child_part_number1', $item_name)
                        ->orWhere('child_part_number2', $item_name)
                        ->select('processing_item','child_part_number1','child_part_number2','item_name','join_flag')
                        ->get()->toArray();
        if (empty($processingItem)) {
           $processingItem = Number::where('processing_item', $item_name)->select('processing_item','child_part_number1','child_part_number2','item_name','join_flag')->get()->toArray();
           $flag = false;
        }
        // 各アイテムにフラグを追加
        foreach ($processingItem as &$item) {
            $item['child_flag'] = $flag;
            // dd($item);
        }
        unset($item); // 参照を解除
        return $processingItem;
    }
    // longinfoDBに品番があるか確認する
    public function table_check($parent_name)
    {
        $result = Schema::connection('second_mysql')->hasTable($parent_name);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }


    //在庫データベースに品番で検索して値を取得してくる
    public function get_stock($item_name)
    {
        $stock = Stock::where('processing_item', $item_name)->first();

        if ($stock) {
            return $stock->toArray();
        } else {
            return [];
        }
    }


    //longinfoDBの$item_nameテーブルから数量を取得する
    public function get_info($name)
    {
        // テーブルが存在するかどうかをチェック
        if (Schema::connection('second_mysql')->hasTable($name)) {
            try {
                // テーブルが存在する場合、データを取得
                $quantity = DB::connection('second_mysql')
                    ->table($name)
                    ->get();
                return $quantity;
            } catch (\Exception $e) {
                // エラーをキャッチしてログに出力
                \Log::error('Error in get_info while fetching data: ' . $e->getMessage());
                return []; // エラー時に空の配列を返す
            }
        } else {
            // テーブルが存在しない場合の処理
            \Log::warning('Table does not exist: ' . $name);
            return []; // 例：空の配列を返す
        }
    }

    // 各品番の加工数量が0以上の最初の日付取得
    public function get_day($item_name,$process_count)
    {
        // テーブルが存在するかどうかをチェック
        if (Schema::connection('second_mysql')->hasTable($item_name)) {
            $process_column = "process" . $process_count;
            $record = DB::connection('second_mysql')
                ->table($item_name)
                ->where($process_column, '>', 0)
                ->orderBy('id')
                ->first(['day']);

            if ($record) {
                $day = $record->day;
                return $day;
            } else {
                return "2500-01-01";
            }
        } else {
            return "2500-01-01";
        }
    }
    //品番の結合判定を取得してくる
    public function get_join_flag($item_name)
    {
       return Number::where('processing_item',$item_name)->value('join_flag');
    }

    // 長期情報の日付取得
    public function date_get()
    {
        $day = DB::connection('mysql')->table('long_term_date')->select('day')->get();
        return $day;
    }

    // 品番テーブルからデータ取得
    public function quantity_get($item_name)
    {
        if (Schema::connection('second_mysql')->hasTable($item_name)) {
            $record = DB::connection('second_mysql')->table($item_name)->get();
            if ($record) {
                return $record;
            } else {
                return [];
            }
        }else {
            return [];
        }
        // return $record;
    }
    //子品番取得
    public function child_number_get($item_name,$number)
    {
        if($number == 102)
        {
           $item = Number::where('processing_item', $item_name)->value('child_part_number1');
        }else if($number == 103)
        {
           $item = Number::where('processing_item', $item_name)->value('child_part_number2');
        }
        else if($number == 704)
        {
           $item = Number::where('processing_item', $item_name)
              ->select('child_part_number1', 'child_part_number2')
              ->first();
        }
        return $item;
    }
    //lotを取得してくる
    public function lot_get($item_name,$process)
    {
        $lot = Process::where('processing_item', $item_name)->where('process', $process)->value('lot');
        return $lot;
    }
    //長期作成日を取得してくる
    public function get_create_infodate()
    {
        $firstRecord = Long_term_date::orderBy('id', 'asc')->first();
        $dayValue = $firstRecord->day;
        return $dayValue;
    }
    //品目名称を取得する
    public function get_item_name($processing_item)
    {
        $item_name = Number::where('processing_item', $processing_item)->value('item_name');
        return $item_name;
    }

    //printing_historyDBに値を登録する
    public function printing_history_insert($print_arr)
    {
        // 悲観的ロックを使用してトランザクションを実行
        DB::transaction(function () use ($print_arr) {
            // レコードに対して悲観的ロックをかける
            $record = PrintHistory::where('id', 1)->lockForUpdate()->first();

            // 登録処理を実行
            PrintHistory::create($print_arr);
        });
    }
    //在庫の増減処理
    public function increase_stock($increase_stock_names, $processing_quantity, $parent_name, $reduce_stock_names = [])
    {
        DB::transaction(function () use ($increase_stock_names, $processing_quantity, $parent_name, $reduce_stock_names) {
            // 悲観的ロックを使用して在庫を取得し、増やす処理を行う
            foreach ($increase_stock_names as $column_name) {
                $stock = Stock::where('processing_item', $parent_name)
                    ->whereRaw("`{$column_name}` IS NOT NULL")
                    ->lockForUpdate()
                    ->first();
                
                if ($stock) {
                    $stock->increment($column_name, $processing_quantity);
                }
            }

            // 減らす処理
            foreach ($reduce_stock_names as $column_name) {
                $stock = Stock::where('processing_item', $parent_name)
                    ->whereRaw("`{$column_name}` IS NOT NULL")
                    ->lockForUpdate()
                    ->first();
                
                if ($stock) {
                    $stock->decrement($column_name, $processing_quantity);
                }
            }
        });
    }
    //longinfoDBの数量を消す
    //選択した品番の日付から数量を消す
    public function info_calculation($table_name, $info_process, $delivery_date, $processing_quantity)
    {
        // Check if the table exists
        if (Schema::connection('second_mysql')->hasTable($table_name)) 
        {
            DB::connection('second_mysql')->transaction(function () use ($table_name, $info_process, $delivery_date, $processing_quantity) {
                // Lock the row for update
                $record = DB::connection('second_mysql')
                    ->table($table_name)
                    ->where('day', $delivery_date)
                    ->whereRaw("`{$info_process}` IS NOT NULL")
                    ->lockForUpdate()
                    ->first();
                
                if ($record) {
                    // Decrement the quantity
                    DB::connection('second_mysql')
                        ->table($table_name)
                        ->where('day', $delivery_date)
                        ->whereRaw("`{$info_process}` IS NOT NULL")
                        ->decrement($info_process, $processing_quantity);
                }
            });
        }
    }
    //作業中を取得してくる
    public function get_in_work()
    {
        return PrintHistory::where('input_complete_flag',"true")->select('parent_name','delivery_date','process','capture_date','processing_quantity')
                        ->get()->toArray();
    }

    
}