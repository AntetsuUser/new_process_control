<?php

namespace App\Repositories\History;

//PrintHistory
use App\Models\PrintHistory;
use App\Models\Worker;


use Illuminate\Support\Facades\DB;

// データベース作成に使う
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log; // これを追加

class PrintHistoryRepository
{
   public function print_history_get()
    {
        //PrintHistoryDBからすべてを取得してくる
        return PrintHistory::all()->toArray();
    }

    public function worker_get($woker_id)
    {
        // テーブルにidがあるか確認
        $worker = Worker::find($woker_id);
        // あれば作業者名を、無ければ未登録を返す
        return $worker ? $worker->name : '未登録';
    }
    public function reprint($id) {
         return PrintHistory::where('characteristic_id', $id)->get()->toArray();
    }
    public function entered($directions_id)
    {
        DB::beginTransaction();
        try {
            PrintHistory::where('characteristic_id', $directions_id)
                ->update(['input_complete_flag' => "false"]);
                // 変更を反映させる
            DB::commit();
        } catch (\Exception $e) {
            // データベースを戻す
            DB::rollBack();
        }
    }
}