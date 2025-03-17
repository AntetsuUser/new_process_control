<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\LongInfo\LongInfoService;
// Logを残すために必要なクラス
use Illuminate\Support\Facades\Log;
// ポストリクエストの時のリクエストバリデーション
use App\Http\Requests\Longinfo\SelectRequest;

use Illuminate\Support\Facades\Auth;
//Logコントローラー
use App\Http\Controllers\LogController;

class LongInfoController extends Controller
{
    // 共通のサービスクラス
    protected $_longinfoService;

    /**
     * コンストラクタ
     * 
     * @param LongInfoService $longinfoService
     */
    public function __construct(LongInfoService $longinfoService)
    {
        $this->_longinfoService = $longinfoService;  // LongInfoサービスをインスタンス化
    }

    /**
     * 前のページに戻る処理
     * 
     * @param Request $request
     */
    public function someMethod(Request $request)
    {
        // 何らかの処理を行った後に前のページに戻る
        return redirect()->back();
    }

    /**
     * 工場、製造課選択画面に遷移
     */
    public function select()
    {
        $factory = $this->_longinfoService->factory_get();  // 工場情報を取得
        Log::channel('process_log')->info('工場、製造課選択画面遷移');
         //userが画面を表示させたときのログ
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '長期情報製造課選択'); 
        return view('longinfo.select', compact('factory'));  // 工場選択画面を表示
    }

    /**
     * 長期情報表示の処理
     * 
     * @param Request $request
     */
    public function view_post(Request $request)
    {
        // IPアドレスを取得
        $ip = $request->ip();

        // 印刷履歴から作業中のデータを取得
        $work_arr = $this->_longinfoService->get_in_work();

        // ラインと番号を組み合わせて設備番号を作成
        $line = $request->line;
        if ($line == "store") {
            $line = "ストア";  // "store"を日本語に変換
        }
        $numbers = $request->numbers;
        $line_numbers = $line . $numbers;

        $factory = $request->factory;
        $department = $request->department;
        
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '長期情報表示'); 
        Log::channel('process_log')->info("工場表示, 工場: {$factory}　製造課: {$department}　設備番号: {$line_numbers}");

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");
        $factorys = ["本社","本社第2","宮地","池田"];
        $departments = ["1製造課","2製造課（本社）","2製造課（宮地）","3製造課","4製造課","5製造課","6製造課","7製造課",];
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
         ])->info("選択された情報「{$factorys[intval($factory)-1]}工場、{$departments[intval($department)-1]}、設備番号: {$line_numbers}」", [
        '選択された情報' => "{$factorys[intval($factory)-1]}工場、{$departments[intval($department)-1]}、設備番号: {$line_numbers}",
    ]);

        // 設備番号で加工可能な品番を取得
        if ($line == "U-N" && $department == "8") {
            $factory = $this->_longinfoService->factory_get();
            $message = '選択された「7製造課」、「U-N」で表示できる品番がありません。選択内容をご確認ください。';

            return view('longinfo.select', compact('factory', 'message'));
        }

        // 指定された設備番号に関連する長期情報を取得
        $send_arr = $this->_longinfoService->longinfo_date($line_numbers);
        if ($send_arr == false) {
            $factory = $this->_longinfoService->factory_get();
            $message = '選択したW/Cには登録されている品番がありません。選択内容をご確認ください。';
            return view('longinfo.select', compact('factory', 'message'));
        }

        // 品番に関連する情報を取得
        $info_process_arr = $send_arr["item_sorted_arr"];
        $stock_arr = $send_arr["stock_arr"];
        //単体品番を取得してくる
        $single_number = $this->_longinfoService->single_number($info_process_arr);
        // 材料の在庫を取得
        $material_arr = $this->_longinfoService->material_stock($stock_arr);
        // dump($material_arr);

        // 日付情報を取得
        $date_arr = $this->_longinfoService->date_get();    

        // 選択可能な工程を取得
        $selectable = $this->_longinfoService->selectable($line_numbers);
        // dd($selectable);
        // 選択可能な品番を取得
        $selectitem = $this->_longinfoService->selectitem($line_numbers);
        // JSON形式に変換
        $selectable_json = json_encode($selectable, JSON_PRETTY_PRINT);
        // 数量情報を取得
        $quantity_arr = $this->_longinfoService->quantity_get($info_process_arr);
        // dd($quantity_arr);
        // 材料の引き当てマーク配列を取得
        // $material_mark_arr = $this->_longinfoService->material_mark($quantity_arr);

         // 材料の引き当てマーク配列を取得
        $material_mark_arr_count = $this->_longinfoService->sin_material_mark($info_process_arr,$quantity_arr,$date_arr);
        $material_arr = $material_mark_arr_count[1];
        $material_mark_arr = $material_mark_arr_count[0];
        // dd($material_arr);
        //平準化用の配列を作成
        // $base_ability = $this->_longinfoService->base_ability($quantity_arr,$date_arr,$line_numbers);
        // dd($base_ability);

        //new平準化用の配列を作成
        $base_ability = $this->_longinfoService->new_base_ability($info_process_arr,$selectitem);


        

        // 工場、製造課、作業者IDを取得
        $factory = $request->factory;
        $department = $request->department;
        $workers = $request->workers;

        // ロット数を取得
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
        $request->session()->put('material_arr', $material_arr);
        $request->session()->put('material_mark_arr', $material_mark_arr);
        $request->session()->put('base_ability', $base_ability);
        $request->session()->put('single_number', $single_number);
        //selectitem
        $request->session()->put('selectitem', $selectitem);
        //$single_number
        Log::channel('process_log')->info('処理終了　GETに値を渡す');

        // GETリクエストにリダイレクト
        return redirect()->route('longinfo.view');
    }

    /**
     * 長期情報表示画面を表示
     * 
     * @param Request $request
     */
    public function view(Request $request)
    {
        $user = Auth::user();
        $logController = new LogController();
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
        $material_arr = $request->session()->get('material_arr', []);
        $material_mark_arr = $request->session()->get('material_mark_arr', []);
        $base_ability = $request->session()->get('base_ability', []);
        $single_number = $request->session()->get('single_number', []);
        //selectitem
        $selectitem = $request->session()->get('selectitem', []);

        $logController->page_log($user->name, '長期情報表示'); 
        //logに表示データを残す
        foreach ($info_process_arr as $itemname => $process) {
            $logController->showing_data($itemname, $process,$user->name,'長期情報表示'); 
        }
        //logに残すデータの配列
        // $log_arr = [$info_process_arr, $stock_arr,$date_arr,$quantity_arr,$selectable_json,$lot_arr,$line_numbers,$workers,$work_arr,$line, $numbers,$factory,$department, $material_arr,$material_mark_arr];

        // ビューにデータを渡す
        return view('longinfo.view', compact('info_process_arr', 'stock_arr', 'date_arr', 'quantity_arr',
                                            'selectable_json', 'lot_arr', 'line_numbers', 'workers', 'work_arr', 
                                            'line', 'numbers', 'factory', 'department','material_arr','material_mark_arr','base_ability','single_number','selectitem'));
    }

    /**
     * 指示書印刷処理
     * 
     * @param Request $request
     */
    public function print_post(Request $request)
    {
        // POSTデータを取得
        $post_data = $request->data;

        // 作業者IDを抜き出し
        $arr = explode(',', $post_data[0]);
        $workers = $arr[7];
        $print_arr = [];

        if (count($arr) == 10) {
            $ip = $arr[9];  // IPアドレスをPOSTデータから取得
        } else {
            $ip = $request->ip();  // IPアドレスをリクエストから取得
        }

        foreach ($post_data as $arr_count => $value) {
            // 指示書に必要な情報を処理
            $Array = explode(',', $value);
            $process_number = $Array[8];
            $data_arr = $this->_longinfoService->print_date_create($Array, $arr_count, $request->department, $ip);
            $this->_longinfoService->long_info_quantity($data_arr, $process_number);
            $print_arr[] = $data_arr;
        }

        // セッションにデータを保存
        $request->session()->put('print_arr', $print_arr);
        $request->session()->put('line', $request->line);
        $request->session()->put('numbers', $request->numbers);
        $request->session()->put('factory', $request->factory);
        $request->session()->put('department', $request->department);
        $request->session()->put('workers', $workers);
        
        // $logController->page_log($user->name, '指示書印刷画面'); 

        // 指示書印刷画面にリダイレクト
        return redirect()->route('longinfo.print');
    }

    /**
     * 指示書印刷画面を表示
     * 
     * @param Request $request
     */
    public function print(Request $request)
    {
        
        // セッションからデータを取得
        $print_arr = $request->session()->get('print_arr', []);
        $line = $request->session()->get('line', '');
        $numbers = $request->session()->get('numbers', '');
        $factory = $request->session()->get('factory', '');
        $department = $request->session()->get('department', '');
        $workers = $request->session()->get('workers', '');
        
        $user = Auth::user();
        $logController = new LogController();
        //指示書の情報をlogに残す
        $logController->directions_data($print_arr,$factory,$department,$user->name);

        //指示書印刷画面表示のlog
        $logController->page_log($user->name, '指示書印刷画面'); 
        
        // ビューにデータを渡す
        return view('longinfo.print', compact('print_arr', 'line', 'numbers', 'factory', 'department', 'workers'));
    }
}
