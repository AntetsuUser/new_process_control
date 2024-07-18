<?php

namespace App\Repositories\History;

//PrintHistory
use App\Models\PrintHistory;
use App\Models\Worker;

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
}