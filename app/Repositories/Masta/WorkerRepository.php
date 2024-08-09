<?php

namespace App\Repositories\Masta;

use Illuminate\Support\Facades\DB;

//workerDB
use App\Models\Worker;
//factoryDB
use App\Models\Factory;

class WorkerRepository
{
    public function upsert($data, $user_id)
    {
        // トランザクションの開始
        DB::beginTransaction();

        try {
            $data['id'] = $user_id;
            // dd($data);

            Worker::upsert(
                // 追加もしくは更新するデータ（idがnullの場合は追加）
                // 複数行追加できるため、一つの場合でも、配列に入れる
                [$data],
                // 存在するかどうかを確認するためのカラム
                ['id'], 
                // 更新したいカラム
                ['factory_id', 'department_id', 'name']
            );
            // すべての操作が成功した場合、コミット
            DB::commit();
        } catch (\Throwable $th) {
            // 何らかのエラーが発生した場合、ロールバック
            DB::rollback();
            // 例外をキャッチしてエラー処理を行う
            // 例外をログに記録したり、ユーザーにエラーメッセージを表示したりできる
            throw $th;
        }
    
    }

    // 作業者編集のときに、編集する人の情報のみ取得する
    public function findById($id)
    {
        //idのやつだけ返す
        return Worker::join('factory', 'factory.id', '=', 'worker.factory_id')
                     ->join('department', 'department.id', '=', 'worker.department_id')
                     ->select('worker.*', 'factory.name as factory_name', 'department.name as department_name')
                     ->find($id);
    }

    // 作業者削除
    public function delete($id)
    {
        Worker::find($id)->delete();
    }
}