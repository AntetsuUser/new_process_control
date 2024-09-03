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
    public function view_post(Request $request)
    {
        //ip取得
        $ip =  $request-> ip();
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

        $line = $request->line;
        $numbers = $request->numbers;
        $factory = $request->factory;
        $department = $request->department;

        //設備番号で加工可能な品番を取得してくる
        // dd($line_numbers);
        $send_arr = $this->_longinfoService->longinfo_date($line_numbers);
        if ($send_arr == false) {
            $factory = $this->_longinfoService->factory_get();
            $message = '選択したW/Cには登録されている品番がありません。選択内容をご確認ください。';
            return view('longinfo.select', compact('factory', 'message'));
        }
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

            // セッションにデータを保存
        $request->session()->put('info_process_arr', $info_process_arr);
        $request->session()->put('stock_arr', $stock_arr);
        $request->session()->put('date_arr', $date_arr);
        $request->session()->put('quantity_arr', $quantity_arr);
        $request->session()->put('selectable_json', $selectable_json);
        $request->session()->put('lot_arr', $lot_arr);
        $request->session()->put('line_numbers', $line_numbers);
        $request->session()->put('workers', $workers);
        $request->session()->put('work_arr', $work_arr);
        $request->session()->put('line', $line);
        $request->session()->put('numbers', $numbers);
        $request->session()->put('factory', $factory);
        $request->session()->put('department', $department);

        // GETリクエストにリダイレクト
        return redirect()->route('longinfo.view');
    }

    public function view(Request $request)
    {
        // セッションからデータを取得
        $info_process_arr = $request->session()->get('info_process_arr', []);
        $stock_arr = $request->session()->get('stock_arr', []);
        $date_arr = $request->session()->get('date_arr', []);
        $quantity_arr = $request->session()->get('quantity_arr', []);
        $selectable_json = $request->session()->get('selectable_json', '');
        $lot_arr = $request->session()->get('lot_arr', []);
        $line_numbers = $request->session()->get('line_numbers', '');
        $workers = $request->session()->get('workers', '');
        $work_arr = $request->session()->get('work_arr', []);
        $line = $request->session()->get('line', '');
        $numbers = $request->session()->get('numbers', '');
        $factory = $request->session()->get('factory', '');
        $department = $request->session()->get('department', '');
        // ビューにデータを渡す
        return view('longinfo.view', compact('info_process_arr', 'stock_arr', 'date_arr', 'quantity_arr',
                                            'selectable_json', 'lot_arr', 'line_numbers', 'workers', 'work_arr', 
                                            'line', 'numbers', 'factory', 'department'));
    }
    //指示書印刷画面の値処理
    public function print_post(Request $request)
    {
        $line = $request->line;
        $numbers = $request->numbers;
        $factory = $request->factory;
        $department = $request->department;
        //IPアドレス
        $ip = $request-> ip();

        // POSTデータを取得
        $post_data = $request->data;

        // 作業者IDを抜き出す
        $arr = explode(',', $post_data[0]);
        $workers = $arr[7];
        $print_arr = [];
        
        foreach ($post_data as $arr_count => $value) 
        {
            // 指示書に必要な情報を配列に入れて処理
            $Array = explode(',', $value);
            $process_number = $Array[8];
            $data_arr = $this->_longinfoService->print_date_create($Array, $arr_count,$department,$ip);
            $this->_longinfoService->long_info_quantity($data_arr, $process_number);
            $print_arr[] = $data_arr;
        }

        // セッションにデータを保存
        $request->session()->put('print_arr', $print_arr);
        $request->session()->put('line', $line);
        $request->session()->put('numbers', $numbers);
        $request->session()->put('factory', $factory);
        $request->session()->put('department', $department);
        $request->session()->put('workers', $workers);

        // GETリクエストにリダイレクト
        return redirect()->route('longinfo.print');
    }

    //指示書印刷画面に遷移
    public function print(Request $request)
    {
        // セッションからデータを取得
        $print_arr = $request->session()->get('print_arr', []);
        $line = $request->session()->get('line', '');
        $numbers = $request->session()->get('numbers', '');
        $factory = $request->session()->get('factory', '');
        $department = $request->session()->get('department', '');
        $workers = $request->session()->get('workers', '');

        // ビューにデータを渡す
        return view('longinfo.print', compact('print_arr', 'line', 'numbers', 'factory', 'department', 'workers'));
    }
    
}
