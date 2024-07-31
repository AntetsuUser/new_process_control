<?php

namespace App\Repositories\Qr;

use App\Models\PrintHistory;

use App\Models\Stock;

use App\Models\ProcessedHistory;

use Illuminate\Support\Facades\DB;

// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

class QrReadRepository
{
    //指示書固有IDから指示書の情報を取得する
    public function getdirection_date($characteristic_id)
    {
        return PrintHistory::where('characteristic_id', $characteristic_id)->first()->toArray();
    }

    public function working_end($characteristic_id)
    {
        DB::beginTransaction();
        try {
            PrintHistory::where('characteristic_id', $characteristic_id)
                ->update(['input_complete_flag' => "false"]);
                // 変更を反映させる
            DB::commit();
        } catch (\Exception $e) {
            // データベースを戻す
            DB::rollBack();
        }
    }

    public function subtract_from_stock($parent_name, $reduce_stock_names, $defect_quantity)
    {
        DB::beginTransaction();
        try {
            // 行をロックして同時更新を防ぐ
            $stock = Stock::where('processing_item', $parent_name)
                        ->whereRaw("`{$reduce_stock_names}` IS NOT NULL")
                        ->lockForUpdate()
                        ->first();
                if ($stock) {
                    // 在庫から不良の数だけ引く
                    $stock->decrement($reduce_stock_names, $defect_quantity);
                }

            // トランザクションをコミット
            DB::commit();
        } catch (\Throwable $th) {
            // エラーが発生した場合はトランザクションをロールバック
            DB::rollback();
            
            // エラーを処理するか、例外を投げる
            throw $th;
        }
    }

    public function increase_long_term_information($parent_name, $reduce_stock_names, $defect_quantity, $delivery_date)
    {
        if (Schema::connection('second_mysql')->hasTable($parent_name)) 
        {
            DB::connection('second_mysql')->beginTransaction();
            try {
                foreach ($reduce_stock_names as $column_name) 
                {
                    DB::connection('second_mysql')
                        ->table($parent_name)
                        ->where('day', $delivery_date)
                        ->whereNotNull($column_name)
                        ->lockForUpdate()
                        ->increment($column_name, $defect_quantity);
                }

                // トランザクションをコミット
                DB::connection('second_mysql')->commit();
            } catch (\Throwable $th) {
                // エラーが発生した場合はトランザクションをロールバック
                DB::connection('second_mysql')->rollBack();
                
                // エラーを処理するか、例外を投げる
                throw $th;
            }
        }
    }
    public function input_history_create($directions_data)
    {
        ProcessedHistory::create($directions_data);
    }


}