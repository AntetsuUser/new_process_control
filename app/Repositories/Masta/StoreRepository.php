<?php

namespace App\Repositories\Masta;
use Illuminate\Support\Facades\DB;

//storeDB
use App\Models\Store;

class StoreRepository
{
    //追加or更新
    public function upsert($data, $id)
    {
        // トランザクションの開始
        DB::beginTransaction();

        try {

            $data['id'] = $id;
            Store::upsert(
                // 追加もしくは更新するデータ（idがnullの場合は追加）
                // 複数行追加できるため、一つの場合でも、配列に入れる
                [$data],
                // 存在するかどうかを確認するためのカラム
                ['id'], 
                // 更新したいカラム
                ['factory_id', 'department_id', 'store']
            );
            // すべての操作が成功した場合、コミット
            DB::commit();
        } catch (\Throwable $th) {
             // 何らかのエラーが発生した場合、ロールバック
            DB::rollback();
            // 例外をキャッチしてエラー処理を行う
            // 例外をログに記録したり、ユーザーにエラーメッセージを表示したりできる
            throw $e;
        }
    }
    //削除
    public function delete($id)
    {
        Store::find($id)->delete();
    }

}