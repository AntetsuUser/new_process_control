<?php

namespace App\Http\Controllers;

use App\Services\LongInfo\LongInfoService;

use Illuminate\Http\Request;
use App\Services\Signage\SignageService;
use App\Services\LoadPrediction\LoadPredictionService;
use Illuminate\Support\Facades\Auth;
//Logコントローラー
use App\Http\Controllers\LogController;

class SignageController extends Controller
{
    //共通
    protected $_signageService;
    protected $_longinfoService;
    protected $_loadpredictionService;

    public function __construct(SignageService $signageService,LongInfoService $longinfoService,LoadPredictionService $loadpredictionService)
    {
        //
        $this->_signageService = $signageService;
        $this->_longinfoService = $longinfoService;
        $this->_loadpredictionService = $loadpredictionService;
    }

    //モニター表示
    public function main(Request $request)
    {
        // dd($request);
        $ip_address = $request->local_ip;
        $uuid = request()->cookie('uuid');
        // dd($ip_address);
        $division = $request->query('division');
        //ipで製造課を取得してくる
        $production = $this->_signageService->getproduction("s");
        if(is_null($production) && is_null($division))
        {
            $scene ="main";
            $department = $this->_loadpredictionService->department_get();
            //製造課選択のviewに返す//signage.department
            $user = Auth::user();
            $logController = new LogController();
            $logController->page_log($user->name, 'モニター製造課選択'); 
            return view('signage.department',compact('scene','department'));
        }
        //ipを投げて表示する品目を取得してくる
        if(!is_null($division))
        {
            $production = $division;
        }
        $direction_date = $this->_signageService->getitem($production);
        // dd($direction_date );
        $item_Array = $direction_date[0];
        $info_day= $direction_date[1];
        $process_names= $direction_date[2];
        $display_arr= $direction_date[3];
        $items= $direction_date[4];
        $dateArray= $direction_date[5];
        $weekdayArray= $direction_date[6];
        $display_stock_arr = $direction_date[7];
        $material_mark = $direction_date[8];
        $material_stock = $direction_date[9];
        // dd($items,$material_mark);
        // dd($items,$process_names,$info_day);
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'モニター'); 
        return view('signage.main', compact('item_Array','info_day','process_names','items','display_arr','ip_address','dateArray','weekdayArray','production','display_stock_arr','material_mark','material_stock'));
    }

    //タブレット
    public function tablet(Request $request)
    {
        //ipで製造課を取得してくる
        $ip_address = $request->local_ip;
        $uuid = request()->cookie('uuid');
        $division = $request->query('division');
        //ipで製造課を取得してくる
        $production = $this->_signageService->getproduction("s");
        if(is_null($production) && is_null($division))
        {
            $scene ="tablet";
            $department = $this->_loadpredictionService->department_get();
            //製造課選択のviewに返す//signage.department
            $user = Auth::user();
            $logController = new LogController();
            $logController->page_log($user->name, 'タブレット製造課選択'); 
            return view('signage.department',compact('scene','department'));
        }
        //ipを投げて表示する品目を取得してくる
        if(!is_null($division))
        {
            $production = $division;
        }
        //uuidが登録されていなかった場合
        // $production = 8;
        //製造課で登録されている品目名称を被りなしで取得する
        $item_names = $this->_signageService->get_item_names($production);
        // dd($item_names);
        //品目の工程を取得
        $process_arr = $this->_signageService->get_process($production);

        $direction_date = $this->_signageService->getitem($production);

        $work_arr =  $this->_longinfoService->get_in_work();
         
        //日付けを取得してくる
        $date = $this->_signageService->get_date();
        $dateArray= $direction_date[5];
        $weekdayArray= $direction_date[6];
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'タブレット進捗確認'); 
        return view('signage.tablet',compact('item_names','process_arr','dateArray','weekdayArray','production','date' ,'work_arr'));
    }

    public function ajax(Request $request)
    {
        //製造課
        $production = $request->production;
        //品目
        $item_names = $request->item_names;
        //工程
        $process = $request->process;

        //選択された品目集約の品目を取得し、テーブルが存在する品番を取得する
        $items = $this->_signageService->acquisition_from_item_aggregation($item_names);

        
        //製造課と選択された工程で工程を取得してくる
        $Processes = $this->_signageService->process_sorting($production,$process);

        $display_array = $Processes[0];
        $process_array = $Processes[1];
        $display_stock_array = $Processes[2];

        //品番と工程で長期情報を取得してくる
        $target_data = $this->_signageService->target_data($items);
        
        //品番と工程で長期情報を取得してくる
        $info_data = $this->_signageService->info_data($process_array,$items);
        uasort($info_data, function($a, $b) {
            $lastProcessA = end($a);
            $lastProcessB = end($b);
            
            // 最後の配列の値を取得
           // 各プロセスの配列を1つずつ比較
            foreach ($lastProcessA as $index => $valueA) {
                $valueB = $lastProcessB[$index] ?? 0; // $lastProcessBに同じインデックスがない場合は0とする
                
                if ($valueA !== $valueB) {
                    return $valueB - $valueA; // 降順にソート
                }
            }
            
            // 全ての要素が等しい場合は0を返して順序を維持
            return 0;
        });
        // return $info_data;
        //並び変えたキーだけ取得
        $sorted_keys = array_keys($info_data);

        //在庫の引き当てマーク配列を作成する材料の在庫数
        $material_mark = $this->_signageService->tablet_material_mar($sorted_keys,$production);
        $material_mark_arr = $material_mark[0];
        $material_stock = $material_mark[1];
        //品番と工程で在庫を取得してくる
        $stock_data = $this->_signageService->stock_data($process_array,$items);

        $return_arr = [$target_data,$info_data,$stock_data,$display_array,$display_stock_array,$material_mark_arr,$material_stock];

        return $return_arr;
    }

}
