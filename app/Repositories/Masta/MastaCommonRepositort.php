<?php
// *********************************************************************
//マスタ共通のリポジトリ
//
// *********************************************************************

namespace App\Repositories\Masta;
//factoryDB
use App\Models\Factory;
//workerDB
use App\Models\Worker;
//storeDB
use App\Models\Store;
//EquipmentDB
use App\Models\Equipment;
//numberDB
use App\Models\Number;
//Processing_history
use App\Models\Processing_history;

class MastaCommonRepositort
{
    //工場を取得する
    public function factory_get()
    {
        return Factory::all();
    }

    // 一覧画面で工場と部署と名前を取得する(データベースの名前,モデルの名前)
    public function factoryDepartmentFind($DBname,$DBmodelname)
    {
        //変数にDBのモデル名を入れる
        $model = "App\Models\\" . $DBmodelname;
        //return モデル名 
        return $model::join('factory', 'factory.id', '=', $DBname . '.factory_id')
                 ->join('department', 'department.id', '=', $DBname . '.department_id')
                 ->select($DBname . '.*', 'factory.name as factory_name', 'department.name as department_name')
                 ->orderBy('id', 'asc')
                 ->get();
    }

    // 編集のときに、編集するidの情報のみ取得する
    public function findById($DBname,$DBmodelname,$id)
    {
        //変数にDBのモデル名を入れる
        $model = "App\Models\\" . $DBmodelname;
        return $model::join('factory', 'factory.id', '=', $DBname .'.factory_id')
                    ->join('department', 'department.id', '=', $DBname. '.department_id')
                    ->select($DBname.'.*', 'factory.name as factory_name', 'department.name as department_name')
                    ->find($id);
    }
    //$idのデータを削除する
    public function delete($DBmodelname,$id)
    {
        //変数にDBのモデル名を入れる
        $model = "App\Models\\" . $DBmodelname;
        return $model::find($id)->delete();
    }
}