<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\LoadPrediction\LoadPredictionService;


class LoadPredictionController extends Controller
{
    //共通
    protected $_loadpredictionService;

    public function __construct(LoadPredictionService $loadpredictionService)
    {
        //
        $this->_loadpredictionService = $loadpredictionService;
        
    }
    //負荷予測製造課選択
    public function department_select(Request $request)
    {
        $department = $this->_loadpredictionService->department_get();
        return view('load_prediction.department_select',compact('department'));
    }

    public function process(Request $request)
    {
        //postで受け取った製造課id
        $department_id =  $request ->division;

        //製造課idを渡して加工設備を取得し負荷率を計算する
        $result = $this->_loadpredictionService->load_calculation($department_id);

        // 負荷率の計算結果が空だった場合
        if (empty($result)) {
            return redirect()->route('load_prediction.department_select')
                            ->with('message', 'データが登録されていないか、存在しません');
        }

        // データが存在する場合、結果をビューに渡して表示する
        return redirect()->route('load_prediction.load_prediction_machine')
                        ->with('data', $result);

    }
    //viewを表示
    public function load_prediction_machine(Request $request)
    {
        return view('load_prediction.load_prediction_machine');
    }
}
