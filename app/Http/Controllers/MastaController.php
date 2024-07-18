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

//ポストリクエストの時の
use App\Http\Requests\Masta\NumberRequest;
use App\Http\Requests\Masta\WorkerEditRequest;
use App\Http\Requests\Masta\StoreRequest;
use App\Http\Requests\Masta\EquipmentRequest;
use App\Http\Requests\Masta\InfoUploadRequest;

//Excelのデータを操作するときに使用
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

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

    public function __construct(WorkerService $workerService, EquipmentService $equipmentService, 
                                CalendarService $calendarService,NumberService $numberService, 
                                StoreService $storeService,MastaCommonService $mastacommonService,
                                UploadService $uploadService)
    {
        //
        $this->_mastacommonService = $mastacommonService;

        $this->_workerService = $workerService;
        $this->_equipmentService = $equipmentService;
        $this->_calendarService = $calendarService;
        $this->_numberService = $numberService;
        $this->_storeService = $storeService;

        $this->_uploadService = $uploadService;

    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    // マスタ管理画面
    ///////////////////////////////////////////////////////////////////////////////////////////
    public function masta()
    {
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
        //品目名称がシャフトアッシーの時に子品番に $request->input('name')[]を
        foreach ($request->print_number as $index => $numbers_value) {
            // $numberを処理する（例：データベースに保存する、表示する、など）
            $numbers_arr = [];
            //データベースに登録する値を連想配列で入れる
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
                }else{
                    continue;
                }
            }
        }

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
        $longinfo_log = $this->_uploadService->get_uplog();
        
        return view('masta.upload',compact('longinfo_log'));
    }
    //長期情報アップロード
    public function longinfo_upload(InfoUploadRequest $request)
    {
        $uploadfile = $request->file->path();
        $filename = $request->file('file');
        //Excelファイルのデータをデータベースに登録する
        $this->_uploadService->create_table($filename,$uploadfile);
        //アップロードされたファイルを履歴DBに入れる
        $this->_uploadService->upload_log($filename);

        return redirect()->route('masta.upload');
    }
}
