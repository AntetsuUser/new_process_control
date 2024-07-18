<?php 

namespace App\Services\Qr;
    
use App\Repositories\Qr\QrReadRepository;
    
class QrReadService 
{
    // リポジトリクラスとの紐付け
    protected $_qrreadRepository;

    // phpのコンストラクタ
    public function __construct(QrReadRepository $qrreadRepository)
    {
        $this->_qrreadRepository = $qrreadRepository;
    }
    public function getdirection_date($characteristic_id)
    {
       return $this->_qrreadRepository->getdirection_date($characteristic_id);
    }

}