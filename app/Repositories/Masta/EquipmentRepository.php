<?php

namespace App\Repositories\Masta;
use Illuminate\Support\Facades\DB;

use App\Models\Equipment;

class EquipmentRepository
{
    public function upsert($data, $id)
    {
        // トランザクションの開始
        DB::beginTransaction();
        try {
            $data['id'] = $id;
            Equipment::upsert(
                // 追加もしくは更新するデータ（idがnullの場合は追加）
                // 複数行追加できるため、一つの場合でも、配列に入れる
                [$data],
                // 存在するかどうかを確認するためのカラム
                ['id'], 
                // 更新したいカラム
                ['factory_id', 'department_id', 'line','equipment_id','category','model']
            );
            DB::commit();
        } catch (\Throwable $th) {
           // 何らかのエラーが発生した場合、ロールバック
            DB::rollback();
            // 例外をキャッチしてエラー処理を行う
            // 例外をログに記録したり、ユーザーにエラーメッセージを表示したりできる
            throw $e;
        }
    }


    public function delete($id)
    {
        Equipment::find($id)->delete();
    }
}