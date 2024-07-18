<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\CalendarRepository;
    
class CalendarService 
{
    // リポジトリクラスとの紐付け
    protected $_equipmentRepository;

    // phpのコンストラクタ
    public function __construct(CalendarRepository $calendarRepository)
    {
        $this->_calendarRepository = $calendarRepository;
    }

    public function calendar_get()
    {
        return $this->_calendarRepository->calendar_get();
    }
    
    public function insertOrdelete($data)
    {
        return $this->_calendarRepository->insertOrdelete($data);
    }
}