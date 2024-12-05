<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\TabletRepository;
use App\Repositories\Masta\MastaCommonRepositort;

    
class TabletService 
{
    // リポジトリクラスとの紐付け
    protected $_tabletRepository;
    protected $_mastacommonRepository;

    // phpのコンストラクタ
    public function __construct(TabletRepository $tabletRepository,MastaCommonRepositort $mastacommonRepository)
    {
        $this->_tabletRepository = $tabletRepository;
        $this->_mastacommonRepository = $mastacommonRepository;
    }

//     // データを渡す
//     public function insert($data)
//     {
//         // $dataに対する処理はここに書く
//         $this->_tabletRepository->insert($data);
//     }

//     public function upsert($data, $worker_id)
//     {
//         // // ログインしているユーザのID
//         // $user_id = auth()->id();
//         $this->_tabletRepository->upsert($data, $worker_id);
//     }

    // $idに対応した人の 工場・部署・名前を取得する
    public function findById()
    {
        return $this->_mastacommonRepository->factoryDepartmentFind("tablets","Tablets");
    }

//     // 作業者の削除
//     public function delete($id)
//     {
//         return $this->_tabletRepository->delete($id);
//     }
}