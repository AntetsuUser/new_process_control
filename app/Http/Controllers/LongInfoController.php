<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\LongInfo\LongInfoService;
// Logを残すために必要なクラス
use Illuminate\Support\Facades\Log;
// ポストリクエストの時のリクエストバリデーション
use App\Http\Requests\Longinfo\SelectRequest;

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

        $line = $request->line;
        $numbers = $request->numbers;
        $factory = $request->factory;
        $department = $request->department;
        Log::channel('process_log')->info("工場表示, 工場: {$factory}　製造課: {$department}　設備番号: {$line_numbers}");

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

        // 材料の在庫を取得
        $material_arr = $this->_longinfoService->material_stock($stock_arr);

        // 日付情報を取得
        $date_arr = $this->_longinfoService->date_get();    

        // 選択可能な工程を取得
        $selectable = $this->_longinfoService->selectable($line_numbers);

        // JSON形式に変換
        $selectable_json = json_encode($selectable, JSON_PRETTY_PRINT);

        // 数量情報を取得
        $quantity_arr = $this->_longinfoService->quantity_get($info_process_arr);

        // 材料の引き当てマーク配列を取得
        $material_mark_arr = $this->_longinfoService->material_mark($quantity_arr);

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
        
        Log::channel('process_log')->info('値受け取り　viewを表示');

        // ビューにデータを渡す
        return view('longinfo.view', compact('info_process_arr', 'stock_arr', 'date_arr', 'quantity_arr',
                                            'selectable_json', 'lot_arr', 'line_numbers', 'workers', 'work_arr', 
                                            'line', 'numbers', 'factory', 'department','material_arr','material_mark_arr'));
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

        // ビューにデータを渡す
        return view('longinfo.print', compact('print_arr', 'line', 'numbers', 'factory', 'department', 'workers'));
    }
}
