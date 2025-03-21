<?php

namespace App\Repositories\Masta;

use App\Models\Process;

use App\Models\Number;

use App\Models\Factory;

use App\Models\Stock;

use App\Models\Material_stock;


// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

use Illuminate\Support\Facades\DB;

class NumberRepository
{
    //選択されたidの情報を取得
    public function number_info($id)
    {
        //渡されたidの品番を取得してくる
        $select_item = Number::join('factory', 'factory.id', '=', 'number' . '.factory_id')
                 ->join('department', 'department.id', '=', 'number' . '.department_id')
                 ->select('number' . '.*', 'factory.name as factory_name', 'department.name as department_name')
                 ->find($id);
        //numberのidを渡して
        $process = $this->process_get($select_item->id);
        return [
            'select_item' => $select_item,
            'process' => $process
        ];
        
    }
    // 子品番の情報を取得
    public function child_info($child_name)
    {
        if(empty($child_name))
        {
            return null;
        }
        $child_item = Number::where('processing_item',$child_name)
                ->join('factory', 'factory.id', '=', 'number' . '.factory_id')
                ->join('department', 'department.id', '=', 'number' . '.department_id')
                ->select('number' . '.*', 'factory.name as factory_name', 'department.name as department_name')
                ->first();
        $process = $this->process_get($child_item->id);
        return [
            'select_item' => $child_item,
            'process' => $process
        ];
    }
    //processの値を取得してくる
    public  function process_get($number_id)
    {
        return Process::where('number_id',$number_id)->get();
    }
    //numberに追加
    public function upsert($data)
    {
        // 条件に一致するレコードを取得
        $existingRecord = Number::where('processing_item', $data['processing_item'])
                                ->first();           
        if ($existingRecord) {
            // 既存レコードが見つかった場合、そのIDを使用して更新する
            $data['id'] = $existingRecord->id;
        }
        DB::beginTransaction();
        try {
            Number::upsert(
            [$data],
            ['id'], 
            ['print_number', 'processing_item', 'item_name','material_item','collect_name','child_part_number1','child_part_number2','factory_id','department_id','line','join_flag']
            );
             if (isset($data['id'])) {
                // 更新された場合のID取得
                $record = Number::where('id', $data['id'])->first();
                $id = $record->id;
            } else {
                // 新規作成された場合のID取得
                $record = Number::where('print_number', $data['print_number'])
                                ->where('processing_item', $data['processing_item'])
                                ->where('item_name', $data['item_name'])
                                ->first();
                $id = $record->id;
            }
            // すべての操作が成功した場合、コミット
            DB::commit();
            return  $id;
        } catch (\Throwable $th) {
           // 何らかのエラーが発生した場合、ロールバック
            DB::rollback();
            // 例外をキャッチしてエラー処理を行う
            // 例外をログに記録したり、ユーザーにエラーメッセージを表示したりできる
            throw $th;
        }
    }
    //processに追加
    public function process_upsert($data)
    {
        // 条件に一致するレコードを取得
        $existingRecord = Process::where('number_id', $data['number_id'])
                                ->where('process_number', $data['process_number'])
                                ->first();

        if ($existingRecord) {
            // 既存レコードが見つかった場合、そのIDを使用して更新する
            $data['id'] = $existingRecord->id;
        }
        DB::beginTransaction();
        try {
                Process::upsert(
                [$data],
                ['id'], 
                ['processing_item', 'process_number','process','store','processing_time','number_id','lot','printing_max']
                );
                DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    //number削除
    public function number_delete($id)
    {
        DB::beginTransaction();
        try {
            Number::where('id', $id)->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    //工程の削除
    public function process_delete($id)
    {
        DB::beginTransaction();
        try {
            Process::where('number_id', $id)->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
    //データベースに登録してある項目を取得する
    public function number_get()
    {
        $numbers = array();
        // 図面番号（親）
        $numbers['print_number_parent'] = Number::select('print_number')->where('child_part_number1', '!=', '')->DISTINCT()->pluck('print_number')->all();  
        // 加工品目（親）
        $numbers['processing_item'] = Number::select('processing_item')->where('child_part_number1', '!=', '')->DISTINCT()->pluck('processing_item')->all();
        // 品目名称（親）
        $numbers['item_name'] = Number::select('item_name')->where('child_part_number1', '!=', '')->DISTINCT()->pluck('item_name')->all();
        // 材料品目（親）
        $numbers['material_item'] = Number::select('material_item')->where('child_part_number1', '!=', '')->DISTINCT()->pluck('material_item')->all();
        // 品目集約（親）
        $numbers['collect_name'] = Number::select('collect_name')->where('collect_name', '!=', '')->where('child_part_number1', '!=', '')->DISTINCT()->pluck('collect_name')->all();

        // 図面番号（子）
        $numbers['print_number_child'] = Number::select('print_number')->where('child_part_number1', '')->DISTINCT()->pluck('print_number')->all();
        // 加工品目（子）
        $numbers['processing_item_child'] = Number::select('processing_item')->where('child_part_number1', '')->DISTINCT()->pluck('processing_item')->all();
        // 加工品目（子）
        $numbers['item_name_child'] = Number::select('item_name')->where('child_part_number1', '')->DISTINCT()->pluck('item_name')->all();
        // 材料品目（子）
        $numbers['material_item_child'] = Number::select('material_item')->where('material_item', '!=', '')->where('child_part_number1', '')->DISTINCT()->pluck('material_item')->all();

        // 品目集約（子）
        $numbers['collect_name_child'] = Number::select('material_item')
            ->where('collect_name', '!=', '')
            ->where(function($query) {
                $query->where('child_part_number1', '')
                    ->orWhereNull('child_part_number1');
            })
            ->pluck('material_item')
            ->unique()
            ->all();//工程
        $numbers['process'] =  Process::select('process')->where('process', '!=', '')->DISTINCT()->pluck('process')->all();
        return $numbers;
    }
    public function stock_insert($stock_arr,)
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
    //second_mysql


    //create_longinfo_tableを作成
   public function create_longinfo_table($name, $item_arr,$DBname)
    {
        if (!Schema::connection($DBname)->hasTable($name)) {
            // トランザクションの開始
            DB::beginTransaction();
            try {
                Schema::connection($DBname)->create($name, function (Blueprint $table) use ($item_arr) {
                    $table->id();
                    // 最初の要素からキーを取得してカラムを定義
                    foreach (array_keys($item_arr[0]) as $value) {
                        if ($value == "day" || $value == "weekday") {
                            $table->string($value)->nullable()->comment($value);
                        } else {
                            $table->integer($value)->nullable()->comment($value);
                        }
                    }
                });

                // テーブルにデータを挿入
                foreach ($item_arr as $item) {
                    DB::connection($DBname)->table($name)->insert($item);
                }

                // コミット
                DB::commit();
            } catch (\Throwable $th) {
                // 何らかのエラーが発生した場合、ロールバック
                DB::rollback();
                // 例外をキャッチしてエラー処理を行う
                // 例外をログに記録したり、ユーザーにエラーメッセージを表示したりできる
                throw $th;
            }
        }
    }

    public function material_entry($data)
    {
        //Material_stock::DBのmaterial_nameからむに$data["material_name"]が存在するか確認
        $existingMaterial = Material_stock::where('material_name', $data['material_name'])->first();
        //なかった場合のみcreate
        if (!$existingMaterial) {
            return Material_stock::create($data);
        }
    }


}   