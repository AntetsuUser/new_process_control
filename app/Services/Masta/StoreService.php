<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\StoreRepository;
    
class StoreService 
{
    // リポジトリクラスとの紐付け
    protected $_storeRepository;

    // phpのコンストラクタ
    public function __construct(StoreRepository $storeRepository)
    {
        $this->_storeRepository = $storeRepository;
    }
    //登録・更新
    public function upsert($data, $id)
    {
        $this->_storeRepository->upsert($data, $id);
    }

    // 削除
    public function delete($id)
    {
        return $this->_storeRepository->delete($id);
    }
}