<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\EquipmentRepository;
    
class EquipmentService 
{
    // リポジトリクラスとの紐付け
    protected $_equipmentRepository;

    // phpのコンストラクタ
    public function __construct(EquipmentRepository $equipmentRepository)
    {
        $this->_equipmentRepository = $equipmentRepository;
    }

    public function upsert($data,$id)
    {
        $this->_equipmentRepository->upsert($data, $id);
    }


    public function delete($id)
    {
        return $this->_equipmentRepository->delete($id);
    }
}