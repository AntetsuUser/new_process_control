<?php

namespace App\Repositories\History;

//ProcessedHistory
use App\Models\ProcessedHistory;

class ProcessedHistoryRepository
{
   public function processed_history_get()
    {
        //ProcessedHistoryDBからすべてを取得してくる
        return ProcessedHistory::orderBy('id', 'desc')->paginate(50);
    }

    
}