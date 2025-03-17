<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Qr\QrReadService;
use App\Services\LongInfo\LongInfoService;


use Illuminate\Support\Facades\Auth;
//Logコントローラー
use App\Http\Controllers\LogController;


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
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'QRカメラ'); 
        return view('qr.qrcamera');
    }
    //指示書IDの指示書の入力画面遷移
    public function input_directions(Request $request)
    {
        $characteristic_id = $request->query('characteristic_id'); // クエリパラメータを取得
        $direction_date = $this->_qrreadService->getdirection_date($characteristic_id);
        
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, "指示書ID「{$characteristic_id}」の指示書の入力"); 
        return view('qr.input_directions',compact('direction_date'));
    }

    //入力完了画面
    public function input_succes(Request $request)
    {  
        $data = $request->all();
        //ここの値を使ってやる
        $this->_qrreadService->achievement_application($data);
        $this->_qrreadService->input_history_create($data);
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '入力完了'); 
        return view('qr.input_succes');
    }
}
