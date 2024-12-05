<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\LoadPrediction\LoadPredictionService;

class LoadPredictionController extends Controller
{
    // 共通サービスクラスとの紐付け
    protected $_loadpredictionService;

    /**
     * コンストラクタ
     * 
     * @param LoadPredictionService $loadpredictionService
     */
    public function __construct(LoadPredictionService $loadpredictionService)
    {
        $this->_loadpredictionService = $loadpredictionService;  // 負荷予測サービスをインスタンス化
    }

    /**
     * 負荷予測製造課選択画面を表示
     * 
     * @param Request $request
     */
    public function department_select(Request $request)
    {
        $department = $this->_loadpredictionService->department_get();  // 製造課のデータを取得
        return view('load_prediction.department_select', compact('department'));  // 製造課選択画面を表示
    }

    /**
     * 負荷率計算の処理を実行
     * 
     * @param Request $request
     */
    public function process(Request $request)
    {
        // POSTで受け取った製造課IDを取得
        $department_id = $request->division;

        // 製造課IDを渡して加工設備を取得し、負荷率を計算
        $result = $this->_loadpredictionService->load_calculation($department_id);

        // 負荷率の計算結果が空だった場合
        if (empty($result)) {
            // データが登録されていない、または存在しない場合
            return redirect()->route('load_prediction.department_select')
                             ->with('message', 'データが登録されていないか、存在しません');
        }

        // データが存在する場合、計算結果をビューに渡して表示
        return redirect()->route('load_prediction.load_prediction_machine')
                         ->with('data', $result);
    }

    /**
     * 負荷予測機械画面を表示
     * 
     * @param Request $request
     */
    public function load_prediction_machine(Request $request)
    {
        return view('load_prediction.load_prediction_machine');  // 負荷予測機械画面を表示
    }
}
