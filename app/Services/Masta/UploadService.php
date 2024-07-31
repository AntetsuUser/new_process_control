<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\UploadRepository;


//Excelのデータを操作するときに使用
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

    
class UploadService 
{
    // リポジトリクラスとの紐付け
    protected $uploadepository;

    // phpのコンストラクタ
    public function __construct(UploadRepository $uploadRepository)
    {
        $this->_uploadRepository = $uploadRepository;
    }

    //アップロードの履歴を全部取ってくる
    public function get_uplog(){

        $uplogShipment = $this->_uploadRepository->get_uplog();
        if (count($uplogShipment) > 10) {
            $uplogShipment = array_slice($uplogShipment, 0, 10);
        }
        return $uplogShipment;
    }
    public function get_uplog_shipment(){

        $uplogShipment = $this->_uploadRepository->get_uplog_shipment();
        if (count($uplogShipment) > 10) {
            $uplogShipment = array_slice($uplogShipment, 0, 10);
        }
        return $uplogShipment;
    }

    //長期情報Excelファイルからデータベースのテーブルを作成する
    public function create_table($filename,$uploadfile)
    {
        //データベースのテーブルを削除する
        $this->_uploadRepository->drop_all_tables();
        //アップロードされたファイル
        $originalFilename = $filename->getClientOriginalName();
        date_default_timezone_set('Asia/Tokyo'); // タイムゾーンを日本時間に設定
        $now = date("Y-m-d H:i:s"); // 現在の日付と時刻を取得（YYYY-MM-DD HH:MM:SS形式）

        $reader = new XlsxReader();
        $spreadsheet = $reader->load($uploadfile);
        //シート名を指定する
        $sheet = $spreadsheet->getSheetByName('印刷用');
        //シートの最終行を取得
        $max_row = $sheet -> getHighestRow();
        //シートの最終列まで取得
        $max_col  = $sheet->getHighestColumn();

        $column = 'C';
        //取得したデータが入る
        $data = [];
        // 行目から最終行までループして値を取得
        $columnIterator = $sheet->getColumnIterator('F');
        // 未登録品番を格納する配列　いらなかったら消しといて
        $unregistered = array();
        $serialdate = $sheet->getCell('G' . 7)->getValue();
        $base_date = new \DateTime('1899-12-30'); // Excelの日付シリアル値の基準日を設定
        $base_date->add(new \DateInterval('P' . $serialdate . 'D')); // シリアル値の日数を追加

        $base_date =  $base_date->format('Y-m-d'); // 変換された日付を表示
        //8行目から最終行までループ　2個ずつ
        $longinfos_day = [];
        $json = [];
        for ($row = 8; $row <= $max_row; $row += 2) 
        {
            // C列の品番を取得
            $item_name = $sheet->getCell('C' . $row)->getValue();
            
            // 数字が最初に出現する位置を取得
            $number_position = strcspn($item_name, '0123456789');
            // 文字の除去
            $item_name = substr($item_name, $number_position);
            //マスタに登録されているチェック
            $mastadata = $this->_uploadRepository->isInMaster($item_name);

            //マスタに登録されていなかったら
            if ($mastadata == false) 
            {
                //登録されていなかったらjsonに記入する
                array_push($unregistered,$item_name);
                $json[$item_name] = [];
                foreach ($columnIterator as $column) 
                {
                    // 個数を入力
                    $quantity = $sheet->getCell($column->getColumnIndex() . $row + 1)->getValue();
                    // 日付を入力
                    $day = $sheet->getCell($column->getColumnIndex() . 5)->getValue();
                    $weekday = $sheet->getCell($column->getColumnIndex() . 7)->getValue();

                    $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($weekday);
                    $formattedDate = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
                    if($day == "")
                    {
                        continue;
                    } else if (is_numeric($day) && (int)$day < 1000) {
                        $day = $formattedDate;
                        $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
                        $dayOfWeek = $daysOfWeek[(int)$dateValue->format('w')];
                    } else if (is_numeric($day) && (int)$day >= 1000) {
                        $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$day);
                        $formattedday = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
                        $day = $formattedday;
                        // 曜日の短縮形を取得する
                        $dayOfWeek = $formattedDate;
                    }

                    if($formattedDate == "1970-01-01")
                    {
                        $formattedDate =NULL;
                        $dayOfWeek  =NULL;
                    }
                    // dump($day,$formattedDate,$quantity);
                    $json[$item_name][] =["day"=>$day,"weekday"=>$dayOfWeek,"target"=>$quantity];
                }
            }
            else {
                if ($item_name) 
                {
                    //在庫の配列がはいる
                    $stock_arr = [];
                    /////////////////////////////////////////////////////////////    
                    //データベースに品番ごとのテーブルを作成する
                    /////////////////////////////////////////////////////////////
                    //データベース作成用
                    $DB_name[$item_name] = [];
                    //値が入るよう
                    $data[$item_name] = [];
                    $item_arr = [] ;

                    //品番でnumberDBから子品番を取得する
                    $select_item = $this->_uploadRepository->get_number_info($item_name);
                    // dd($select_item);
                    //配列に親品番の名前と子品番の名前を入れる
                    $item_arr[] = $select_item -> child_part_number1;
                    $item_arr[] = $select_item -> child_part_number2;
                    $item_arr[] = $item_name;
                    //品番の子品番を結合するかのflagが時はいってる
                    $join = $select_item -> join_flag; 
                    //工程の配列を作成
                    $item_process = [];
                    foreach ($item_arr as $name) {
                        $process = $this->_uploadRepository->get_process($name);
                        foreach ($process as $value) {
                            $item_process[] = $value;
                        }
                    }
                    //データベース作成用
                    $DB_col_name = ["day","weekdays","target","addition"];
                    //データベース作成用に工程
                    foreach ($item_process as $index => $pro_value) 
                    {
                        $DB_col_name[] = "process".$index+1;
                    }
                    $DB_name[$item_name] = $DB_col_name;
                    // temp_longinfoに品番ごとのテーブルを作成する
                    foreach ($DB_name as $key => $value) {
                        $this->_uploadRepository->create_new_longinfo_table($key,$value);
                    }
                    /////////////////////////////////////////////////////////////    
                    //作成したテーブルに値を入れる
                    /////////////////////////////////////////////////////////////
                    //配列初期化
                    $col_value = [];
                    $days = [];
                    $weekdays = [];
                    $target =[];
                    $addition = [];
                    $longinfos_day = [];
                    $long_DB = 0;
                    foreach ($columnIterator as $column) 
                    {
                        $long_DB = NULL;
                        // 個数を入力
                        $value = $sheet->getCell($column->getColumnIndex() . $row + 1)->getValue();
                        // 日付を入力
                        $day = $sheet->getCell($column->getColumnIndex() . 5)->getValue();
                        $weekday = $sheet->getCell($column->getColumnIndex() . 7)->getValue();
                        $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($weekday);
                        $formattedDate = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
                        //最後の行は入れない
                        if($day == "")
                        {
                            continue;
                        }else if (is_numeric($day) && (int)$day < 1000) {
                            $day = $formattedDate;
                            $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
                            $dayOfWeek = $daysOfWeek[(int)$dateValue->format('w')];
                            $long_DB  = $day;
                        } else if (is_numeric($day) && (int)$day >= 1000) {
                            $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$day);
                            $formattedday = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
                            $day = $formattedday;
                            // 曜日の短縮形を取得する
                            $dayOfWeek = $formattedDate;
                        }
                        $days[] = $day;
                        $weekdays[] =  $dayOfWeek;
                        $target[] = $value !== null ? $value : 0;
                        $addition[] = 0;
                        $child_material_stock[] = 0;
                       if (!is_null($long_DB)) {
                            $longinfos_day[] = $long_DB;
                        }
                    }
                    //配列に日付、曜日、数量、追加数量、材料在庫、材料在庫を入れる
                    $col_value["day"] = $days;
                    $col_value["weekdays"] = $weekdays;
                    $col_value["target"] = $target;
                    $col_value["addition"] = $addition;

                    //工程ごとに数量をいれる
                    foreach ($item_process as $index => $pro_value) 
                    {
                        $col_value["process".$index+1] = $target;
                    }    

                    $data[$item_name] = $col_value;
                    //あたいを品番のテーブルに入れる
                    $this->_uploadRepository->insert_item_data($item_name,$data[$item_name]);
                    //在庫のデータベースを作る（すでに登録されていたら何もしない、登録されていなかったら登録）
                    $stock_arr = [
                        "processing_item" => $item_name,
                        "material_stock_1" => 0,
                        "material_stock_2" => 0,
                        "process1_stock" => 0,
                        "process2_stock" => 0,
                        "process3_stock" => 0,
                        "process4_stock" => 0,
                        "process5_stock" => 0,
                        "process6_stock" => 0,
                        "process7_stock" => 0,
                        "process8_stock" => 0,
                        "process9_stock" => 0,
                        "process10_stock" => 0,];
                    $this->_uploadRepository->create_stock($stock_arr);
                    
                }

            }
        }
        // 配列をJSON形式にエンコード
        $jsonData = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // JSONデータをファイルに書き込む
        $file = '/home/pi/Desktop/process_control/public/new_data.json';
        file_put_contents($file, $jsonData);
        //0のところを削除する
        $filtered_dates = array_filter($longinfos_day, function($value) {
            return $value !== 0;
        });
        //フィルタリングした配列をDBに保存する
        $this->_uploadRepository->create_temp_long_term_date($filtered_dates);
        // 結果を表示        

    }
    //長期アップロードの履歴をDBに入れる
    public function upload_log($filename,$category)
    {
        //アップロードされたファイル
        $originalFilename = $filename->getClientOriginalName();
        date_default_timezone_set('Asia/Tokyo'); // タイムゾーンを日本時間に設定
        $now = date("Y-m-d H:i:s"); // 現在の日付と時刻を取得（YYYY-MM-DD HH:MM:SS形式）
        $detail = "アップロード";
        $this->_uploadRepository->upload_log($originalFilename,$category,$detail,$now);

    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    // 出荷明細
    ///////////////////////////////////////////////////////////////////////////////////////////

    //出荷明細アップロードの情報をDBに入れる
    public function shipping_upload_log($filename,$category,$start_date,$end_date)
    {
        $originalFilename = $filename->getClientOriginalName();
        date_default_timezone_set('Asia/Tokyo'); // タイムゾーンを日本時間に設定
        $now = date("Y-m-d H:i:s"); // 現在の日付と時刻を取得（YYYY-MM-DD HH:MM:SS形式）
        $detail = "アップロード";
        $this->_uploadRepository->shipping_upload_log($originalFilename,$category,$detail,$now,$start_date,$end_date);
    }


    //出荷明細のExcelファイルの値を抜き出す
    public function shipping_data_upload($filename,$uploadfile,$start_date,$end_date)
    {
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($uploadfile);
        // 最初のシートを取得
        //変更予定
        $firstSheet = $spreadsheet->getSheet(0);
        //シートの最終行を取得
        $max_row = $firstSheet -> getHighestRow();
        //シートの最終列まで取得
        $max_col  = $firstSheet->getHighestColumn();
        //開始と終了を数値化する
        $start = $this->dateToExcelSerial($start_date);
        $end = $this->dateToExcelSerial($end_date);

        //在庫DBから品番を取得する
        $items =  $this->_uploadRepository->item_code_confirmation();
        // array_columnで特定のカラムの値を抽出
        $processingItems = array_column($items, "processing_item");

        //シートの処理
        for ($row = 1; $row <= $max_row; $row++) {
            //要求納期
            $delivery_date = $firstSheet->getCell('E' . $row)->getValue();
            if (is_null($delivery_date) || !is_numeric($delivery_date)) {
                    continue; // 日付がnullまたは数値でない場合はスキップ
            }
            //開始数値と終了数値の間に$delivery_dateがあるとき
            if($start <= $delivery_date && $end >= $delivery_date)
            {
                //品目コード、品目名称、要求納期、数量、備考

                /** 要求納期 **/
                //日付け数値から文字列に変換
                $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($delivery_date);
                $formattedDate = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
                
                /** /品目コード **/
                $code = $firstSheet->getCell('B' . $row)->getValue();
                // 数字が最初に出現する位置を取得
                $number_position = strcspn($code, '0123456789');
                // 文字の除去
                $item_code = substr($code, $number_position);

                /** /品目名称 **/
                $item_name = $firstSheet->getCell('C' . $row)->getValue();
                /** /数量 **/
                $ordering_quantity = $firstSheet->getCell('G' . $row)->getValue();
                /** /備考 **/
                $note = $firstSheet->getCell('L' . $row)->getValue();

                // in_arrayで値が存在するかを確認
                if (in_array($item_code, $processingItems)) {
                    $shipment_data[] =  ['item_code' => $item_code, 'item_name' => $item_name,'delivery_date' => $formattedDate, 
                                'ordering_quantity' => $ordering_quantity, 'note' => $note];
                } 
            }
        }
        if (!empty($shipment_data)) 
        {
            foreach ($shipment_data as $data) 
            {
                $this->_uploadRepository->insert_shipment_data($data);
            }
        }

    }

    //処理対象の出荷情報を取得する
    public function get_shipping_data()
    {
        return $this->_uploadRepository->get_shipping_data();
    }

    // 処理対象の出荷情報を在庫に反映させる
    public function shipment_info_application($id,$item_code,$ordering_quantity)
    {

        $return = $this->_uploadRepository->shipment_info_application($item_code,$ordering_quantity);
        if($return)
        {       
            //数量変更出来たら削除
            $this->_uploadRepository->shipment_info_delete($id);
            return true;
        }
        else
        {   
            return false;
        }

    }

    //日付文字列を数値に変換する関数
    function dateToExcelSerial($date) 
    {
        $start = new \DateTime("1899-12-30"); // Excelの日付シリアル値の開始日
        $target = new \DateTime($date);
        // 日数の差を計算
        $interval = $start->diff($target);
        return $interval->days;
    }
    // 値が存在するか確認する関数
    function isValueInArray($array, $value, $key) {
        foreach ($array as $item) {
            if (isset($item[$key]) && $item[$key] === $value) {
                return true;
            }
        }
        return false;
    }
}