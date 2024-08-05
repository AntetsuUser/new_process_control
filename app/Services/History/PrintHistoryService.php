<?php 

namespace App\Services\History;
    
use App\Repositories\History\PrintHistoryRepository;
    
class PrintHistoryService 
{
    // リポジトリクラスとの紐付け
    protected $_printhistoryRepository;
    // phpのコンストラクタ
    public function __construct(PrintHistoryRepository $printhistoryRepository)
    {
        $this->_printhistoryRepository = $printhistoryRepository;
    }
    //印刷履歴のすべてのデータを取得
    public function print_history_get()
    {
        // 印刷履歴のデータを取得
        $print_data = $this->_printhistoryRepository->print_history_get();
        // 作業者名を取得
        foreach ($print_data as $key => $value) {
            // 作業者idを変数に格納
            $worker_id = $value["woker_id"];
            // worker_idでデータベースを検索
            $worker_name = $this->_printhistoryRepository->worker_get($worker_id);
            // 配列の作業者IDを作業者名に変更
            $print_data[$key]["woker_id"] = $worker_name;
        }
        return $print_data;
    }
    public function reprint($id) {
        $print_data = $this->_printhistoryRepository->reprint($id);
        // 作業者名を取得
        foreach ($print_data as $key => $value) {
            // 作業者idを変数に格納
            $worker_id = $value["woker_id"];
            // worker_idでデータベースを検索
            $worker_name = $this->_printhistoryRepository->worker_get($worker_id);
            // 配列の作業者IDを作業者名に変更
            $print_data[$key]["woker_id"] = $worker_name;
        }
        return $print_data;
    }

}