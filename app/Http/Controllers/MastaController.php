<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//  マスタ共通
use App\Services\Masta\MastaCommonService;
//　各サービス
use App\Services\Masta\NumberService;
use App\Services\Masta\EquipmentService;
use App\Services\Masta\WorkerService;
use App\Services\Masta\StoreService;
use App\Services\Masta\CalendarService;
use App\Services\Masta\UploadService;
use App\Services\Masta\TabletService;

//ポストリクエストの時の
use App\Http\Requests\Masta\NumberRequest;
use App\Http\Requests\Masta\WorkerEditRequest;
use App\Http\Requests\Masta\StoreRequest;
use App\Http\Requests\Masta\EquipmentRequest;

//アップロードのrequest
use App\Http\Requests\Masta\InfoUploadRequest;
use App\Http\Requests\Masta\ShippingUploadRequest;
use App\Http\Requests\Masta\MaterialUploadRequest;

//Excelのデータを操作するときに使用
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;



use Illuminate\Support\Facades\Auth;
//Logコントローラー
use App\Http\Controllers\LogController;

class MastaController extends Controller
{
    // サービスクラスとの紐付け
    //共通
    protected $_mastacommonService;
    //各サービス
    protected $_workerService;
    protected $_equipmentService;
    protected $_calendarService;
    protected $_numberService;
    protected $_storeService;
    protected $_uploadService;
    protected $_tabletService;
    

    public function __construct(WorkerService $workerService, EquipmentService $equipmentService, 
                                CalendarService $calendarService,NumberService $numberService, 
                                StoreService $storeService,MastaCommonService $mastacommonService,
                                UploadService $uploadService,TabletService $tabletService)
    {
        //
        $this->_mastacommonService = $mastacommonService;

        $this->_workerService = $workerService;
        $this->_equipmentService = $equipmentService;
        $this->_calendarService = $calendarService;
        $this->_numberService = $numberService;
        $this->_storeService = $storeService;

        $this->_uploadService = $uploadService;
        $this->_tabletService = $tabletService;

    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    // マスタ管理画面
    ///////////////////////////////////////////////////////////////////////////////////////////
    public function masta(Request $request)
    {
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'マスタ管理'); 
        return view('masta/masta');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////
    // 品目マスタ
    ///////////////////////////////////////////////////////////////////////////////////////////
    // 品目マスタ画面遷移
    public function  number()
    {
        //numberDBから値を持ってくる
        //DBの名前とmodelの名前を渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'number';
        $DBmodelname = 'Number';
        $number = $this->_mastacommonService->factoryDepartmentFind($DBname,$DBmodelname);  
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '品目マスタ'); 
        return view('masta.number',compact('number'));
    }

    // 品目追加、更新画面遷移
    public function number_insert(Request $request, $id = null)
    {
        $data = null;
        if (isset($id))
        {
            // idの品目の情報を取得してくる
            $data = $this->_numberService->number_info($id);
        }
        //工場を取得する
        $factory = $this->_mastacommonService->factory_get();
        //DBに登録してあるデータを取得してくる
        $numbers = $this->_numberService->number_get();
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '品目追加、更新'); 
        return view('masta.number_insert',compact('factory','data','numbers','id'));
    }
    //品目確認画面
    public function number_confirm(NumberRequest $request)
    {
        $number_params = $request->all();
        $id = null;
        if ($request->has('id'))
        {
            // 編集する人のid取得して確認画面に渡す
            $id = $request->input('id');
            $id = json_decode($id, true);
        }
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '品目確認'); 
        return view('masta.number_confirm',compact('number_params','id'));
    }
    //masta.number_store
    //品目登録・更新処理
    public function number_store(Request $request)
    {
        $ids=null;
        if ($request->has('id'))
        {
            // 編集する人のid取得して確認画面に渡す
            $json_ids = $request->input('id');
            $ids = json_decode($json_ids, true);
        }
        //numbers_DBに入れるデータ
        $numbers_data = $request->only([
            //図面番号 ,加工品目,品目名称,材料品目,品目集約,工場id,製造課id,ラインNo,結合判定
            'print_number', 'name', 'item_name', 'resource_item', 'collect_name', 'factories_id', 'departments_id', 'line_number', 'join_flag',  
        ]);
        $process_json =[];
        $process_count=1;
        //品目名称がシャフトアッシーの時に子品番に $request->input('name')[]を
        foreach ($request->print_number as $index => $numbers_value) {
            // $numberを処理する（例：データベースに保存する、表示する、など）
            $numbers_arr = [];
            //データベースに登録する値を連想配列で入れる
            if($index == 0)
            {
                $parent_number = $numbers_data['name'][$index];
            }
            //図面番号
            $numbers_arr['print_number'] = $numbers_data['print_number'][$index];
            //加工品目
            $numbers_arr['processing_item'] = $numbers_data['name'][$index];
            //品目名称
            $numbers_arr['item_name'] = $numbers_data['item_name'][$index];
            // 材料品目
            $numbers_arr['material_item'] = $numbers_data['resource_item'][$index];
            //品目集約
            $numbers_arr['collect_name'] = $numbers_data['collect_name'][$index];
            //工場
            $numbers_arr['factory_id'] = $numbers_data['factories_id'][$index];
            //製造課
            $numbers_arr['department_id'] = $numbers_data['departments_id'][$index];
            //ライン
            $numbers_arr['line'] = $numbers_data['line_number'][$index];
            //結合判定
            $numbers_arr['join_flag'] = $numbers_data['join_flag'];

            //材料在庫DBに材料品目の登録確認をする
            if (!is_null($numbers_data['resource_item'][$index])) {
                // nullじゃない時に実行
                $material_item = $numbers_data['resource_item'][$index];
                //材料登録
                $this->_numberService->material_entry($material_item);
            }

            //子品番の登録
            if ($index == 0 && $numbers_data['name'][1] != '' && $numbers_data['name'][2]!= '') {
                $numbers_arr['child_part_number1'] = $numbers_data['name'][1];
                $numbers_arr['child_part_number2'] = $numbers_data['name'][2];
            } else {
                $numbers_arr['child_part_number1'] = '';
                $numbers_arr['child_part_number2'] = '';
            }
            if(isset($ids[$index]))
            {
                $numbers_arr['id'] = $ids[$index];
            }
            else {
                $numbers_arr['id']= null;
            }
            //データが存在する場合のみデータベースに登録
            if(!empty($numbers_arr['print_number']))
            {
                if($index == 0)
                {
                    //stockテーブルに追加する
                    $this->_numberService->stock_insert($numbers_arr['processing_item']);

                }
                $numbers_id = $this->_numberService->upsert($numbers_arr);
            }
            // ここで$numbers_arrをnumbers_DBに登録してデータを処理し、登録したIDを取得
            for ($i=01; $i <= 4; $i++) 
            { 
                //工程を入れていく
                $process_arr = [];
                $processKey = "process_$i";
                

                if($request->has($processKey) )
                {
                    if (!isset($request[$processKey][$index])) {
                        continue;
                    }
                    $process_text = "process".$process_count;
                    //加工品目
                    $process_arr["processing_item"] = $numbers_data['name'][$index];
                    // 加工工程番号
                    $process_arr["process_number"] = $i;
                    //工程
                    $process_arr["process"] = $request->input("process_$i")[$index];
                    // ストア・w/c
                    $process_arr["store"] = $request->input("process_store_$i")[$index];
                    //加工時間
                    $process_arr["processing_time"] = $request->input("process_sec_$i")[$index];
                    // number_id
                    $process_arr["number_id"] = $numbers_id;
                    // ロット
                    $process_arr["lot"] = $request->input("process_lot_$i")[$index];
                    //上限
                    $process_arr["printing_max"] = $request->input("process_lot_$i")[$index];
                    // ここで$numbers_arrをnumbers_DBに登録してデータを処理し、登録したIDを取得

                    $this->_numberService->process_upsert($process_arr);
                    $process_json[$parent_number][] = $process_text;
                    $process_count++;
                }else{
                    continue;
                }
            }
        }
        // //data_jsonとlonginfosに品番を追加
        $this->_numberService->longinfos_create($process_json);
        return redirect()->route('masta.number');
    }

    // number_delete
    //品目削除
    public function number_delete(Request $request)
    {
        //idを取り出す
        $ids=null;
        if ($request->has('delete_id'))
        {
            // 編集する人のid取得して確認画面に渡す
            $id = $request->input('delete_id');
        }
        $this->_numberService->number_delete($id);

        return redirect()->route('masta.number');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////
    // 設備マスタ
    ///////////////////////////////////////////////////////////////////////////////////////////

    // 設備マスタ画面
    public function equipment()
    {
        //DBの名前とmodelの名前を渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'equipment';
        $DBmodelname = 'Equipment';
        $equipment = $this->_mastacommonService->factoryDepartmentFind($DBname,$DBmodelname);  
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '設備マスタ'); 
        return view('masta.equipment', compact('equipment'));
    }

    // 設備追加・編集画面遷移
    public function equipment_insert(Request $request, $id = null)
    {
        //DBの名前とmodelの名前を渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'equipment';
        $DBmodelname = 'Equipment';
        $data = null;   
        if (isset($id))
        {
            // idの工場・部署・名前をとってくる
            $data = $this->_mastacommonService->findById($DBname,$DBmodelname,$id);
        }

        $factory = $this->_mastacommonService->factory_get();

        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '設備追加・編集'); 
        return view('masta.equipment_insert', compact('data','factory'));
    }
    //設備確認画面
    public function equipment_confirm(EquipmentRequest $request)
    {
         $data = $request->only([
            'factory_id', 
            'department_id', 
            'factory_name',
            'department_name',
            'line', 
            'equipment_id', 
            'category', 
            'model', 
            'submit',
        ]);
        $id = null;
        if ($request->has('id'))
        {
            // 編集する人のid取得して確認画面に渡す
            $id = $request->input('id');
        }
        // dd($data);
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '設備確認'); 
        return view('masta.equipment_confirm', compact('data','id'));
    }
    //設備登録・追加
    public function equipment_store(Request $request)
    {
        $data = $request->only([
            'factory_id', 
            'department_id', 
            'line', 
            'equipment_id', 
            'category', 
            'model', 
        ]);
        $id = null;
        if ($request->has('id'))
        {
            // idがあれば更新、なければ追加
            $id = $request->input('id');
        }
        $this->_equipmentService->upsert($data, $id);

        return redirect()->route('masta.equipment');
    }
    // 設備削除
    public function equipment_delete(Request $request)
    {
        $DBmodelname = 'Equipment';
        $id = $request->input('id');
        $this->_mastacommonService->delete($DBmodelname,$id);

        return redirect()->route('masta.equipment');
    }  


    ///////////////////////////////////////////////////////////////////////////////////////////
    // 作業者マスタ
    ///////////////////////////////////////////////////////////////////////////////////////////

    // 作業者マスタ画面遷移
    public function worker()
    {
        //DBの名前とmodelの名前を渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'worker';
        $DBmodelname = 'Worker';
        $worker = $this->_mastacommonService->factoryDepartmentFind($DBname,$DBmodelname);  
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '作業者マスタ'); 
        return view('masta.worker', compact('worker'));
    }

    // 作業者追加・編集画面遷移
    public function worker_edit(Request $request, $id = null)
    {
        //DBの名前とmodelの名前を渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'worker';
        $DBmodelname = 'Worker';
        $data = null;
        if (isset($id))
        {
            // idの工場・部署・名前をとってくる
            $data = $this->_mastacommonService->findById($DBname,$DBmodelname,$id);
        }
        $factory = $this->_mastacommonService->factory_get();
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '作業者追加・編集'); 
        return view('masta.worker_edit', compact('data','factory'));
    }

    // 作業者追加・更新確認画面
    public function worker_edit_confirm(WorkerEditRequest $request)
    {        
        $data = $request->only([
            'factory_id', 
            'department_id', 
            'family_name', 
            'personal_name', 
            'factory_name',
            'department_name',
        ]);

        $id = null;
        if ($request->has('id'))
        {
            // 編集する人のid取得して確認画面に渡す
            $id = $request->input('id');
        }
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, '作業者追加・更新確認'); 
        return view('masta.worker_edit_confirm', compact('data', 'id'));
    }

    // 作業者追加・更新
    public function worker_store(Request $request)
    {
        $data = $request->only([
            'factory_id', 
            'department_id', 
            'name', 
        ]);

        $id = null;
        if ($request->has('id'))
        {
            // idがあれば更新、なければ追加
            $id = $request->input('id');
        }
        $this->_workerService->upsert($data, $id);

        return redirect()->route('masta.worker');
    }

    // 作業者削除
    public function worker_delete(Request $request)
    {
        $DBmodelname = 'Worker';
        $id = $request->input('id');

        $this->_mastacommonService->delete($DBmodelname,$id);

        return redirect()->route('masta.worker');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////
    // ストアマスタ
    ///////////////////////////////////////////////////////////////////////////////////////////
    //ストアマスタ画面遷移
    public function store()
    {
        //DBネームを渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'store';
        $DBmodelname = 'Store';
        $store = $this->_mastacommonService->factoryDepartmentFind($DBname,$DBmodelname);
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'ストアマスタ'); 
        return view('masta.store',compact('store'));
    }
    //ストア追加・編集画面
    public function store_edit($id = null)
    {
        //DBネームを渡して工場名を製造課名をjoinして取得してくる
        $DBname = 'store';
        $DBmodelname = 'Store';
        //編集でidが送られてくる
        //$idがNULLではない場合に送られてきたidの情報をDBから取得する
        $data =null;
        if (isset($id))
        {            
            // idの工場・部署・名前をとってくる
            $data = $this->_mastacommonService->findById($DBname,$DBmodelname,$id);
        }
        $factory = $this->_mastacommonService->factory_get();
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'ストア追加・編集'); 
        return view('masta.store_edit',compact('factory','data'));
    }   
    //ストア登録確認画面
    public function store_confirm(StoreRequest $request)
    {
        $data = $request->only([
            'factory_id', 
            'department_id', 
            'store', 
            'factory_name',
            'department_name',
            'submit'
        ]);

        $id = null;
        if ($request->has('id'))
        {
            // 編集するid取得して確認画面に渡す
            $id = $request->input('id');
        }
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'ストア登録、編集確認'); 
        return view('masta.store_confirm',compact('data','id'));
    }
    //ストア登録・更新
    public function store_upsert(Request $request)
    {
        $data = $request->only([
            'factory_id', 
            'department_id', 
            'store', 
        ]);
        $id = null;
        if ($request->has('id'))
        {
            // idがあれば更新、なければ追加
            $id = $request->input('id');
        }
        $this->_storeService->upsert($data, $id);
        //リダイレクトでstoreマスタ画面へ
        return redirect()->route('masta.store');
    }
    //ストア削除
    public function store_delete(Request $request)
    {
        $DBmodelname = 'Store';

        $id = $request->input('id');

        $this->_mastacommonService->delete($DBmodelname,$id);

        return redirect()->route('masta.store');
    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    // カレンダマスタ
    ///////////////////////////////////////////////////////////////////////////////////////////

    // カレンダー画面遷移
    public function calendar()
    {
        // 今の年と前後の年を取得し配列に入れる
        $year = [];
        // 現在の日付の1日から3か月戻す
        $previousMonthFirstDay = date("Y-m-01", strtotime("-3 month"));
        // 前の年を取得
        $previousYear = date("Y", strtotime("-1 year", strtotime($previousMonthFirstDay)));
        $year[] = $previousYear;
        // 今年の西暦だけを取得
        $currentYear = date("Y", strtotime($previousMonthFirstDay));
        $year[] = $currentYear;
        // 次の年を取得
        $nextYear = date("Y", strtotime("+1 year", strtotime($previousMonthFirstDay)));
        $year[] = $nextYear;
        //DBからのデータを取得する
        $holidayData =  $this->_calendarService->calendar_get();

        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'カレンダマスタ'); 
        return view('masta.calendar',compact('year','holidayData'));
    }
    //calendar休日登録or削除
    public function calendar_update(Request $request)
    {
        //calendarで選択された日付を変数に
        $holiday = $request->input('holiday');
        //json形式を配列に直す
        $holidayArray = json_decode($holiday);
        //データベースに登録or削除
        $this->_calendarService->insertOrdelete($holidayArray);

        return redirect()->route('masta.calendar');
    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    // アップロード
    ///////////////////////////////////////////////////////////////////////////////////////////

    // アップロード画面
    public function upload()
    {
        $tab = "all";
        //長期情報アップロードのログを取得
        $longinfo_log = $this->_uploadService->get_uplog();
        //出荷明細のログを取得
        $shipment_log = $this->_uploadService->get_uplog_shipment();
        //材料入荷情報のログを取得
        $material_log = $this->_uploadService->get_uplog_material();
        //アップロードのログ
        $up_log = $this->_uploadService->get_Additional_information();
        //親品番取得
        $parent_items = $this->_uploadService->get_parent_items();
        // dd($parent_items);
        // `with` メソッドを使って追加のデータをビューに渡す
        
        $user = Auth::user();
        $logController = new LogController();
        $logController->page_log($user->name, 'アップロード'); 
        return view('masta.upload', compact('longinfo_log','shipment_log','parent_items','up_log','material_log'));
    }
    //長期情報アップロード
    public function longinfo_upload(InfoUploadRequest $request)
    {
        $tab = "all";
        $uploadfile = $request->file->path();
        $filename = $request->file('file');
        $category = "長期情報";
        //アップロードされたファイルを履歴DBに入れる
        $this->_uploadService->upload_log($filename,$category);

        //Excelファイルのデータをデータベースに登録する
        $this->_uploadService->create_table($filename,$uploadfile);

        // dd("a");
        //長期情報には数量情報がなく品番マスタには登録がある場合に数量0で登録する
        $this->_uploadService->long_term_existence();

        return redirect()->route('masta.upload')->with([
            'tab' => $tab,
            'message_all' => 'アップロードが完了しました。',
            'message_type' => 'success' // メッセージの種類を指定（成功、エラーなど）
        ]);
    }
    //出荷明細アップロード
    public function shipping_upload(ShippingUploadRequest $request)
    {
        $tab = "shipping";
        $category = "出荷明細";
        //file情報
        $uploadfile = $request->shipment_file->path();
        $filename = $request->file('shipment_file');
        //開始日
        $start_date = $request->input('delivery_day');
        //終了日
        $end_date = $request->input('delivery_day_end');
        // idを取得する
        //アップロードされたファイルを履歴DBに入れる
        $id = $this->_uploadService->shipping_upload_log($filename,$category,$start_date,$end_date);
        //Excelファイルのデータをデータベースに登録する
        $upload_flag = $this->_uploadService->shipping_data_upload($filename,$uploadfile,$start_date,$end_date,$id);
        $a_order_number = '';
        if($upload_flag == "true")
        {
            //アップロード成功
            $message = "アップロードが完了しました。出荷確認ボタンを押して反映してください";
            $type  = 'success';
            $a_order_number = '';
        }
        else if($upload_flag == "no_shipment_data")
        {
            //注文データに被りがあるとき
            $message = "アップロードするデータがありません。ファイル、日付けを確認してください";
            $type  = 'danger';
            $a_order_number = '';
            //ファイル履歴を削除する
            $this->_uploadService->shipping_upload_log_delete($id);
        }
        else
        {
            //注文データに被りがあるとき
            $message = "アップロードしたデータに、購買発注番号が重複している部分があります。アップロードファイルをご確認ください。";
            $type  = 'danger';
            $a_order_number = $upload_flag;
            $this->_uploadService->shipping_upload_log_delete($id);
        }

        return redirect()->route('masta.upload')->with([
            'tab' => $tab,
            'message_shipment' => $message,
            'message_type' => $type, // メッセージの種類を指定（成功、エラーなど）
            'order_number' => $a_order_number
        ]);
    }

    //出荷情報確認画面
    public function clearing_application()
    {
        //出荷情報のDBから値を取得してくる
        $shipping_data = $this->_uploadService->get_shipping_data();
        
        // dd($shipping_data);
        return view('masta.clearing_application',compact('shipping_data'));
    }
    //出荷情報を在庫に反映させる
    public function shipment_application()
    {
        $tab = "shipping";
        //出荷情報のDBから値を取得してくる
        $shipping_data = $this->_uploadService->get_shipping_data();
        $application_flag = true;
        foreach ($shipping_data as $data) 
        {
            $id = $data["id"];
            $item_code = $data["item_code"];
            $ordering_quantity = $data["ordering_quantity"];
            //削除してる
            $return = $this->_uploadService->shipment_info_application($id,$item_code,$ordering_quantity);
            if(!$return)
            {
                $application_flag = false;
            }
        }
        if($application_flag)
        {
            $message  = '出荷情報が反映されました。';
            $message_type = 'success';
        }
        else {
            $message  = '出荷情報が反映出来なかった項目があります確認してください。';
            $message_type = 'warning';
        }
        return redirect()->route('masta.upload')->with([
            'tab' => $tab,
            'message_shipment' => $message,
            'message_type' => $message_type  // メッセージの種類を指定（成功、エラーなど）
        ]);
    }
    ///////////////////////////////
    //材料台帳
    ///////////////////////////////
    public function material_upload(MaterialUploadRequest $request)
    {
        $tab = "material";
        $category = "材料入荷情報";

        $uploadfile = $request->material_file->path();
        $filename = $request->file('material_file');
        // //開始日
        $start_date = $request->input('arrival_date');

        // 材料台帳の支給残数をアップロードで取得してくる


        $id = $this->_uploadService->material_upload_log($filename,$category,$start_date);
        // //Excelファイルのデータをデータベースに登録する
        $upload_flag = $this->_uploadService->material_data_upload($filename,$uploadfile,$start_date,$id);

        if($upload_flag == "true")
        {
            //アップロード成功
            $message = "アップロードが完了しました。詳細ボタンで情報を確認できます。";
            $type  = 'success';
            $a_order_number = '';
        }
        return redirect()->route('masta.upload')->with([
            'tab' => $tab,
            'message_shipment' => $message,
            'message_type' => $type, // メッセージの種類を指定（成功、エラーなど）
            'order_number' => $a_order_number
        ]);
    }

    // ExcelVBAからデータを受け取ってDBに保存する場合
    public function receive_material_from_vba(Request $request)
    {
        $tab = "material";
        $category = "材料入荷情報";

        // VBAからPOSTされたJSONデータを取得
        $data = $request->json()->all();
        if (empty($data)) {
            return response()->json(['error' => '工程管理システムにデータが送信できませんでした'], 400);
        }

        //開始日
        $start_date = $request->input('arrival_date');
        // アップロード履歴をログDBに保存
        $id = $this->_uploadService->material_upload_log($filename,$category,$start_date);
        // VBAからPOSTされた値でデータベース登録
        $upload_flag = $this->_uploadService->receive_material_from_vba_insert($data,$start_date,$id);

        return response()->json(['message' => 'Data processed successfully']);
        
    }


    //履歴
    public function material_up_history(Request $request)
    {
        $history_id = $request->material_log_id;
        $history_arr = $this->_uploadService->get_history_material($history_id);
        return view('masta.material_up_history', compact('history_arr'));
    }
    ///////////////////////////////
    //追加依頼
    ///////////////////////////////
    public function adding_request(Request $request)
    {
        $tab = "adding_request";
        $item = $request->item;
        $delivery_date = $request->delivery_date;
        $quantity = $request->quantity;

        //処理
        $result = $this->_uploadService->adding_order_process($item,$delivery_date,$quantity);
        if($result)
        {
            $message  = '反映されました';
            $message_type = 'success';
        }
        else {
            $message  = '反映されませんでした';
            $message_type = 'warning';
        }
        return redirect()->route('masta.upload')->with([
            'tab' => $tab,
            'message_adding' => $message,
            'message_type' => $message_type  // メッセージの種類を指定（成功、エラーなど）
        ]);
    }

    public function application_history(Request $request)
    {
        //出荷情報のDBから値を取得してくる
        $shipping_data = $this->_uploadService->get_shipping_data();
        
        // dd($shipping_data);
        return view('masta.clearing_application',compact('shipping_data'));
    }


    ///////////////////////////////////////////////////////////////////////////////////////////
    // タブレット一覧
    ///////////////////////////////////////////////////////////////////////////////////////////
    public function tablet_post(Request $request)
    {
        dd($request);
        $ip= $request->local_ip;


    }

    public function tablet(Request $request)
    {

        $masta_data = $this->_tabletService->findById();
        $factory = $this->_mastacommonService->factory_get();
        return view('masta.tablet', compact('masta_data','factory'));
    }
}

