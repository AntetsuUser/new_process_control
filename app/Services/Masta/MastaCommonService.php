<?php 

// *********************************************************************
//マスタ共通のサービス
//
// *********************************************************************

namespace App\Services\Masta;
    
use App\Repositories\Masta\MastaCommonRepositort;
    
class MastaCommonService 
{
    // リポジトリクラスとの紐付け
    protected $_mastacommonRepository;

    // phpのコンストラクタ
    public function __construct(MastaCommonRepositort $mastacommonRepository)
    {
        $this->_mastacommonRepository = $mastacommonRepository;
    }

    // 工場を取得する
    public function factory_get()
    {
        return $this->_mastacommonRepository->factory_get();
    }

    // idから工場・部署の名前をjoinして取得する(データベースの名前,モデルの名前)
    public function factoryDepartmentFind($DBname,$DBmodelname)
    {
        return $this->_mastacommonRepository->factoryDepartmentFind($DBname,$DBmodelname);
    }

    // $idに対応した各データベースの値との工場・部署をjoinして取得する
    public function findById($DBname,$DBmodelname,$id)
    {
        return $this->_mastacommonRepository->findById($DBname,$DBmodelname,$id);
    }

    // $idのデータを削除する
     public function delete($DBmodelname,$id)
    {
        return $this->_mastacommonRepository->delete($DBmodelname,$id);
    }
}