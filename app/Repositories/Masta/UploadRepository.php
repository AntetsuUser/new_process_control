<?php

namespace App\Repositories\Masta;

// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

use Illuminate\Support\Facades\DB;

//numberDB
use App\Models\Number;

use App\Models\Process;

use App\Models\Stock;

use App\Models\Processing_history;

use App\Models\Temp_long_term_date;



class UploadRepository
{
    //アップロード表示の時にcategoryが長期情報のやつだけ取得する
    public function get_uplog()
    {
        //最新のファイル情報から取得する
        return Processing_history::where('category', '長期情報') ->orderBy('id', 'desc')->get();
    }

    //親品番から子品番の情報を取得する
    public function get_number_info($item_name)
    {
        //渡されたitem_nameでDBのprocessing_itemカラムと一致した値を取ってくる
        $select_item = Number::select('child_part_number1', 'child_part_number2', 'join_flag')
                        ->where('processing_item', $item_name)
                        ->first();

        return $select_item;
    }
    //加工品番が渡されるので工程を取得する
    public function get_process($processing_item) 
    {
        $process = Process::where('processing_item', $processing_item)
                            ->pluck('process')
                            ->toArray();
        return $process;
    }
    //マスタに登録されているか確認する
    public function isInMaster($item_name)
    {
        $result = Number::where('processing_item', $item_name)
                    ->exists();
        return $result;
    }

    //アップロードの履歴を残す
    public function upload_log($filename,$category,$detail,$upload_day)
    {
        Processing_history::create([
        'file_name' => $filename,
        'category' => $category,
        'detail' => $detail,
        'upload_day' => $upload_day,
        // You may add more fields here if needed
        ]);
        
    }

    //temp_longinfoに品番ごとのテーブルを作成する
    public function create_new_longinfo_table($name,$item_arr)
    {        
        if (!Schema::connection('third_mysql')->hasTable($name)) {
            // トランザクションの開始
            DB::beginTransaction();
            try {
                Schema::connection('third_mysql')->create($name, function (Blueprint $table) use ($item_arr)
                {

                    $table->id();
                    foreach ($item_arr as  $value) 
                    {
                        if($value == "day" || $value == "weekdays" )
                        {   
                            $table->string($value)->nullable()->comment($value);
                        }
                        else {
                            $table->integer($value)->nullable()->comment($value);
                        }
                    }
                });
                // コミット
                DB::commit();
            }
            catch (\Throwable $th) {
            // 何らかのエラーが発生した場合、ロールバック
                DB::rollback();
                // 例外をキャッチしてエラー処理を行う
                // 例外をログに記録したり、ユーザーにエラーメッセージを表示したりできる
                throw $e;
            }
        }
    }
    //temp_longinfoの作られたテーブルに値を入れる
    public function insert_item_data($name,$item_arr)
    {
        // 配列のサイズを確認
        $count = count($item_arr['day']);

        // 各要素をデータベースに挿入
        for ($i = 0; $i < $count; $i++) {
            $insertData = [];
            foreach ($item_arr as $key => $value) {
                $insertData[$key] = $value[$i];
            }

            DB::connection('third_mysql')->table($name)->insert($insertData);
        }
    }
    //temp_longinfoに在庫のテーブルを作成する初期値は0
    public function create_stock_table($item_arr, $connection = 'third_mysql')
    {
        try {
            Schema::connection($connection)->create('stock', function (Blueprint $table) use ($item_arr) {
                $table->id();

                $uniqueColumns = []; // ユニークなカラム名を格納する配列

                foreach ($item_arr as $index => $values) 
                {
                    // 配列の最初の要素をテーブルのカラム名として使用する
                    $columnName = $values[0];

                    // 重複したカラム名があればカラム名を変更する
                    $uniqueColumnName = $columnName;
                    $suffix = 1;
                    while (in_array($uniqueColumnName, $uniqueColumns)) {
                        $uniqueColumnName = $columnName . '_' . $suffix;
                        $suffix++;
                    }

                    // ユニークなカラム名を記録
                    $uniqueColumns[] = $uniqueColumnName;

                    if ($index == 0)
                    {   
                        $table->string($uniqueColumnName)->nullable()->comment("name");
                    }
                    else {
                        // テーブルのカラム名は最初の要素以外の値を使用する
                        foreach ($values as $key => $value) {
                            if ($key != 0) {
                                $table->integer($value)->nullable()->comment($value);
                            }
                        }
                    }
                }
            });
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            // throw $th; // コメントアウトを外すと、呼び出し元で例外をキャッチできます
        }
    }
    //在庫のデータベースに登録する
    public function create_stock($stock_arr)
    {
        $exists = Stock::where('processing_item', $stock_arr['processing_item'])->exists();

        if (!$exists) {
            Stock::create([
                'processing_item' => $stock_arr['processing_item'],
                'material_stock_1' => $stock_arr['material_stock_1'],
                'material_stock_2' => $stock_arr['material_stock_2'],
                'process1_stock' => $stock_arr['process1_stock'],
                'process2_stock' => $stock_arr['process2_stock'],
                'process3_stock' => $stock_arr['process3_stock'],
                'process4_stock' => $stock_arr['process4_stock'],
                'process5_stock' => $stock_arr['process5_stock'],
                'process6_stock' => $stock_arr['process6_stock'],
                'process7_stock' => $stock_arr['process7_stock'],
                'process8_stock' => $stock_arr['process8_stock'],
                'process9_stock' => $stock_arr['process9_stock'],
                'process10_stock' => $stock_arr['process10_stock'],
            ]);
        }
    }
    public function create_temp_long_term_date($filtered_dates) 
    {
        foreach ($filtered_dates as $date) {
            Temp_long_term_date::create(['day' => $date]);
        }
    }
    //temp_longinfoに入れる際いちどテーブルを削除する
    public function drop_all_tables($connection = 'third_mysql')
    {

        $tables = DB::connection($connection)->select('SHOW TABLES');

        // データベース名を取得
        $databaseName = DB::connection($connection)->getDatabaseName();
        $key = "Tables_in_$databaseName";
        // dd($tables);
        // テーブルが存在するか確認
        if (!empty($tables)) {
            // 各テーブルを削除
            foreach ($tables as $table) {
                $tableName = $table->$key;
                Schema::connection($connection)->drop($tableName);
            }
        }
    }
    public function temp_day_delete(){
        Temp_long_term_date::truncate();
    }   
}