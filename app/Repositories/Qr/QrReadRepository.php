<?php

namespace App\Repositories\Qr;

use App\Models\PrintHistory;

class QrReadRepository
{
    //指示書固有IDから指示書の情報を取得する
    public function getdirection_date($characteristic_id)
    {
        return PrintHistory::where('characteristic_id', $characteristic_id) ->get()->toArray();
    }
}