<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Services\LongInfo\LongInfoService;

//ポストリクエストの時の
use App\Http\Requests\Longinfo\SelectRequest;

class LongInfoController extends Controller
{
    //共通
    protected $_longinfoService;

    public function __construct(LongInfoService $longinfoService)
    {
        //
        $this->_longinfoService = $longinfoService;
        
        
    }
    public function someMethod(Request $request)
    {
        // 何らかの処理を行った後に前のページに戻る
        return redirect()->back();
    }


    //工場、製造課選択画面遷移
    public function select()
    {
        $factory = $this->_longinfoService->factory_get();

        return view('longinfo.select', compact('factory'));
    }

    //長期情報表示
    public function view(Request $request)
    {
        //印刷履歴から作業中のデータを取得してくる
        $work_arr =  $this->_longinfoService->get_in_work();
        // dd($work_arr);
        //ラインと番号で設備番号
        $line = $request->line;
        if( $line == "store")
        {
            $line = "ストア";
        }
        $numbers = $request->numbers;
        $line_numbers = $line . $numbers;
        

        //設備番号で加工可能な品番を取得してくる
        // dd($line_numbers);
        $send_arr = $this->_longinfoService->longinfo_date($line_numbers);
        $info_process_arr = $send_arr["item_sorted_arr"];
        $stock_arr = $send_arr["stock_arr"];
        //表示する配列をもらう
        // 日付データ取得
        $date_arr = $this->_longinfoService->date_get();    
                //選択可能な工程を取得する
        $selectable = $this->_longinfoService->selectable($line_numbers);
        $selectable_json = json_encode($selectable, JSON_PRETTY_PRINT);
        // 数量情報取得
        $quantity_arr = $this->_longinfoService->quantity_get($info_process_arr);
        //工場id、製造課id、作業者idを変数に格納
        $factory = $request->factory;
        $department = $request->department;
        $workers = $request->workers;
        //ロット数を取得してくる
        $lot_arr = $this->_longinfoService->lot_get($info_process_arr);
        // dd($quantity_arr,$date_arr);

        return view('longinfo.view', compact('info_process_arr', 'stock_arr', 'date_arr', 'quantity_arr',
                                            'selectable_json','lot_arr','line_numbers','workers','work_arr'));
    }
    public function print(Request $request)
    {
                
        $post_data = $request->data;
        // ↓この配列が渡される
        //[[品目コード、工程、納期、着手日、加工数、今まで何個加工したか、長期数量、設備番号、作業者id]]
        //ここでcharacteristic_id,item_name,parent_name,child_part_number1,child_part_number2,input_complete_flagを配列にいれて配列をviewに渡す
        //品番の在庫と長期数を計算してデータベースに反映させる
        $print_arr =[];
        foreach ($post_data as $arr_count => $value) 
        {
            //指示書に必要な情報えお配列に入れてprint_historyDBにデータを入れる
            //ここで固有ID、品目名称、親品番、子品番１、子品番２、作業フラグを配列にいれて配列をviewに渡す
            // カンマで分割して配列に変換
            $Array = explode(',', $value);
            // dd($Array);
            // 工程の番号
            $process_number = $Array[9];
            $data_arr = $this->_longinfoService->print_date_create($Array,$arr_count);
            //選択した数量ぶん長期情報DBから減らす、在庫を増やす
            //parent_nameとprocessとprocess_numberとdelivery_dateとprocessing_quantity
            $this->_longinfoService->long_info_quantity($data_arr,$process_number);
            $print_arr[] = $data_arr;
        }
        return view('longinfo.print',compact('print_arr'));
    }
    
}
