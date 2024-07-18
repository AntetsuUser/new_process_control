<?php 

namespace App\Services\History;
    
use App\Repositories\History\ProcessedHistoryRepository;
    
class ProcessedHistoryService 
{
    // リポジトリクラスとの紐付け
    protected $_processedHistoryRepository;
    // phpのコンストラクタ
    public function __construct(ProcessedHistoryRepository $processedhistoryRepository)
    {
        $this->_processedHistoryRepository = $processedhistoryRepository;
    }
    //入力履歴のすべてのデータを取得
    public function processed_history_get()
    {
        return $this->_processedHistoryRepository->processed_history_get();
    }

}