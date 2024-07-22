<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Qr\QrReadService;

class QrReadController extends Controller
{

    //共通
    protected $_qrreadService;

    public function __construct(QrReadService $qrreadService)
    {
        //
        $this->_qrreadService = $qrreadService;
    }

    //QRカメラ画面
    public function qrcamera()
    {
        return view('qr.qrcamera');
    }
    //指示書IDの指示書の入力画面遷移
    public function input_directions(Request $request)
    {
        $characteristic_id = $request->query('characteristic_id'); // クエリパラメータを取得
        $direction_date = $this->_qrreadService->getdirection_date($characteristic_id);
        // dd($direction_date);
        return view('qr.input_directions',compact('direction_date'));
    }

    //入力完了画面
    public function input_succes(Request $request)
    {  
        $data = $request->all();
        $this->_qrreadService->achievement_application($data);
        return view('qr.input_succes');
    }
}
