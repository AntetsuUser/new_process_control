<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\WorkerRepository;
    
class WorkerService 
{
    // リポジトリクラスとの紐付け
    protected $_workerRepository;

    // phpのコンストラクタ
    public function __construct(WorkerRepository $workerRepository)
    {
        $this->_workerRepository = $workerRepository;
    }

    // データを渡す
    public function insert($data)
    {
        // $dataに対する処理はここに書く
        $this->_workerRepository->insert($data);
    }

    public function upsert($data, $worker_id)
    {
        // // ログインしているユーザのID
        // $user_id = auth()->id();
        $this->_workerRepository->upsert($data, $worker_id);
    }

    // $idに対応した人の 工場・部署・名前を取得する
    public function findById($id)
    {
        return $this->_workerRepository->findById($id);
    }

    // 作業者の削除
    public function delete($id)
    {
        return $this->_workerRepository->delete($id);
    }
}