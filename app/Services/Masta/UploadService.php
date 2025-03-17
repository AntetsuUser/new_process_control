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
    protected $_uploadRepository;

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
    //出荷明細のアップロード履歴を取得してくる
    public function get_uplog_shipment(){

        $uplogShipment = $this->_uploadRepository->get_uplog_shipment();
        if (count($uplogShipment) > 10) {
            $uplogShipment = array_slice($uplogShipment, 0, 10);
        }
        return $uplogShipment;
    }
    //材料入荷情報のアップロード履歴を取得してくる
    public function get_uplog_material()
    {
        $uplogmaterial = $this->_uploadRepository->get_uplog_material();
        if (count($uplogmaterial) > 10) {
            $uplogmaterial = array_slice($uplogmaterial, 0, 10);
        }
        return $uplogmaterial;
    }
    //追加情報のアップロード履歴を取得してくる
    public function get_Additional_information()
    {
        $uplog = $this->_uploadRepository->get_Additional_information();
         if (count($uplog) > 10) {
            $uplog = array_slice($uplog, 0, 10);
        }
        // dd($uplog);
        return $uplog;
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
        $sozai = [];
        // $長期の順番を入れる配列
        $item_sort_arr = [];
        for ($row = 8; $row <= $max_row; $row += 2) 
        {
            // C列の品番を取得
            $item_name = $sheet->getCell('C' . $row)->getValue();
                        
            // 数字が最初に出現する位置を取得
            $number_position = strcspn($item_name, '0123456789');

            // 数字が出現するまでに "X" が含まれているか確認
            $contains_X_before_number = strpos(substr($item_name, 0, $number_position), 'X') !== false;

            $contains_J_before_number = strpos(substr($item_name, 0, $number_position), 'J') !== false;

            if ($contains_X_before_number) {
                // "X" を残して数字の直前から始める
                $item_name = substr($item_name, strpos($item_name, 'X'));
            }elseif($contains_J_before_number){
                if(strpos($item_name, '77AN') !== false)
                {
                    $item_name = substr($item_name, $number_position);
                }else{
                    $item_name = substr($item_name, strpos($item_name, 'J'));
                }
            } else {
                // 数字から始める
                $item_name = substr($item_name, $number_position);
            }
            $mastadata = $this->_uploadRepository->isInMaster($item_name);
            $item_sort_arr[] = $item_name;

            //素材と品目のjsonを作る
            // A列の品番を取得
            $material = $sheet->getCell('A' . $row)->getValue();
            $material2 = $sheet->getCell('A' . $row +1)->getValue();
            // 空白の場合、-2ずつして値を取得
            $row2 = $row;
            while ((empty($material) || trim($material) === "") && $row2 > 8) {

                $row2 -= 2;  // 2行戻る
                $material = $sheet->getCell('A' . $row2)->getValue();
            }
            $row2 = $row;
            $material_name = substr($material, 1);  // 最初の文字を削除

            while ((empty($material2) || trim($material2) === "") && $row2 > 8) {
                $row2 -= 2;  // 2行戻る
                $material2 = $sheet->getCell('A' . ($row2 + 1))->getValue();
            }
            $material_name2 = substr($material2, 1);  // 最初の文字を削除

            $sozai[$material_name][$material_name2][] = $item_name;

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
                    // else{
                    //     $formattedDate =NULL;
                    //     $dayOfWeek  =NULL;
                    // }

                    if($formattedDate == "1970-01-01")
                    {
                        $formattedDate =NULL;
                        $dayOfWeek  =NULL;
                    }
                    $quantity !== null ? $quantity : 0;
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
                        else {
                            $dayOfWeek = NULL;
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
        $sozai_json = json_encode($sozai, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        // 配列をJSON形式にエンコード
        $sort_jsonData = json_encode($item_sort_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        // 配列をJSON形式にエンコード
        $jsonData = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        // JSONデータをファイルに書き込む
        $sort_file = '/home/pi/Desktop/process_control/public/item_sort_order.json';
        file_put_contents($sort_file, $sort_jsonData);
        $sozai_file = '/home/pi/Desktop/process_control/public/sozai.json';
        file_put_contents($sozai_file, $sozai_json);

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
    //長期情報には数量情報がなく品番マスタには登録がある場合に数量0で登録する
    public function long_term_existence()
    {
        //品番マスタに登録されている品番を取得してくる(stockDBから)
        $items = $this->_uploadRepository->item_code_confirmation();
        $remaking = [];
        $flag = false;
        $item_sort_order_json = '/home/pi/Desktop/process_control/public/item_sort_order.json';
        $sozai_json = '/home/pi/Desktop/process_control/public/sozai.json';
        if (file_exists($sozai_json) && file_exists($item_sort_order_json)) {
            // JSONファイルを読み込む
            $item_sort_order = file_get_contents($item_sort_order_json);
            $sozai_json = file_get_contents($sozai_json);

            // JSONをPHPの配列にデコード
            $item_sort = json_decode($item_sort_order, true); // true を指定すると連想配列に変換
            $sozai = json_decode($sozai_json, true); // true を指定すると連想配列に変換
            $flag = true;
        }
        //temp_longinfoに品番データがあるか確認する
        foreach ($items as $key => $item_name) {
            $result = $this->_uploadRepository->temp_confirmation($item_name["processing_item"]);
            if(!$result && $flag)
            {
                //third_mysqlの一番最初のテーブルをコピーしてテーブル名を$item_nameに変更する
                $result2 = $this->_uploadRepository->copyFirstTable($item_name["processing_item"]);
                $result3 = $this->_uploadRepository->updateColumnsToZero($item_name["processing_item"]);
                //$item_name["processing_item"]の子品番を取得する
                $select_item = $this->_uploadRepository->get_number_info($item_name);
                //シャフトの子品番から材料
                $child_part_number1 = $select_item -> child_part_number1;
                $material1 = $this->_uploadRepository->material_item($child_part_number1);

                //ホールドの子品番
                $ichild_part_number2 = $select_item -> child_part_number2;
                $material2 = $this->_uploadRepository->material_item($ichild_part_number2);
                $sozai[$material1][$material2][] = $item_name["processing_item"];
            
            }
        }
        //item_sortを直す
        $item_arr = [];
        foreach ($sozai as $key => $value) {
            foreach ($value as $key2 => $val) {
                foreach ($val as $item) {
                    $item_arr[] = $item;
                }
            }
        }
        $item_sort_order_json = json_encode($item_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        // JSONデータをファイルに書き込む
        $sort_file = '/home/pi/Desktop/process_control/public/item_sort_order.json';
        file_put_contents($sort_file, $item_sort_order_json);

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
        $id = $this->_uploadRepository->shipping_upload_log($originalFilename,$category,$detail,$now,$start_date,$end_date);
        return $id;
    }

    //出荷明細ファイルのデータを取得してDBにいれる
    public function shipping_data_upload($filename, $uploadfile, $start_date, $end_date, $id)
    {
        // Excelファイルの読み込み
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($uploadfile);
        // 最初のシートを取得
        $firstSheet = $spreadsheet->getSheet(0);
        // シートの最終行を取得
        $max_row = $firstSheet->getHighestRow();
        // シートの最終列まで取得
        $max_col = $firstSheet->getHighestColumn();

        // 開始日と終了日を数値化する
        if (!$this->isValidDate($start_date) || !$this->isValidDate($end_date)) {
            return "invalid_date_format"; // 無効な日付の場合
        }
        $start = $this->dateToExcelSerial($start_date);
        $end = $this->dateToExcelSerial($end_date);

        // 既存の出荷データを取得
        $all_shipping_data = $this->_uploadRepository->get_all_shipping_data();

        // 在庫DBから品番を取得
        $items = $this->_uploadRepository->item_code_confirmation();
        // array_columnで特定のカラムの値を抽出
        $processingItems = array_column($items, "processing_item");

        // 出荷データを格納する配列
        $shipment_data = [];

        // シートの行を処理
        for ($row = 1; $row <= $max_row; $row++) {
            // 要求納期
            $delivery_date = $firstSheet->getCell('E' . $row)->getValue();
            $order_number = $firstSheet->getCell('D' . $row)->getValue();
            if (is_null($delivery_date) || !is_numeric($delivery_date) || is_null($order_number)) {
                continue; // 日付がnullまたは数値でない場合はスキップ
            }

            // 開始数値と終了数値の間に$delivery_dateがあるとき
            if ($start <= $delivery_date && $end >= $delivery_date) {
                // 品目コード、品目名称、要求納期、数量、備考

                /** 要求納期 **/
                $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($delivery_date);
                $formattedDate = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
                /** 品目コード **/
                $code = $firstSheet->getCell('B' . $row)->getValue();
                // 数字が最初に出現する位置を取得
                $number_position = strcspn($code, '0123456789');

                // 数字が出現するまでに "X" や "J" が含まれているかを確認
                $item_code = $this->extractItemCode($code, $number_position);

                /** 品目名称 **/
                $item_name = $firstSheet->getCell('C' . $row)->getValue();

                /** 注文数量 **/
                $ordering_quantity = $firstSheet->getCell('G' . $row)->getValue();

                /** 備考 **/
                $note = $firstSheet->getCell('L' . $row)->getValue();

                /** 注文番号 **/
                $order_number = $firstSheet->getCell('D' . $row)->getValue();
                // dump($row);
                // 注文番号が既存データに重複していないかチェック
                if (!$this->isDuplicateOrder($order_number, $all_shipping_data)) {
                    // 「材不」が備考に含まれていない場合にデータを追加
                    if ($note && strpos($note, '材不') === false) {
                        // 品目コードがprocessingItemsに含まれている場合にデータを追加
                        if (in_array($item_code, $processingItems)) {
                            $shipment_data[] = [
                                'item_code' => $item_code,
                                'item_name' => $item_name,
                                'delivery_date' => $formattedDate,
                                'ordering_quantity' => $ordering_quantity,
                                'note' => $note,
                                'application_flag' => 'false',
                                'history_id' => $id,
                                'order_number' => $order_number
                            ];
                        }
                    }
                }
                else {
                    // dd($row,$code);
                    return $order_number;
                }
            }
        }

        // データベースへの挿入処理
        if (!empty($shipment_data)) {
            foreach ($shipment_data as $data) {
                $this->_uploadRepository->insert_shipment_data($data);
            }
            return "true"; // 正常にアップロードされた場合
        } elseif (empty($shipment_data)) {
            return "no_shipment_data"; // 出荷データがない場合
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

    //反映した履歴を取得する
    public function get_history($history_id)
    {
        $history_arr = $this->_uploadRepository->get_history($history_id);
        return $history_arr;
    }   


    //ファイルの履歴を削除する
    public function shipping_upload_log_delete($id)
    {
        $this->_uploadRepository->shipping_upload_log_delete($id);

    }



    ///////////////////////////////////////////////////////////////////////////////////////////
    // 材料入荷情報
    ///////////////////////////////////////////////////////////////////////////////////////////
    public function material_upload_log($filename,$category,$start_date)
    {
        $end_date= $start_date;
        $originalFilename = $filename->getClientOriginalName();
        date_default_timezone_set('Asia/Tokyo'); // タイムゾーンを日本時間に設定
        $now = date("Y-m-d H:i:s"); // 現在の日付と時刻を取得（YYYY-MM-DD HH:MM:SS形式）
        $detail = "アップロード";
        $id = $this->_uploadRepository->shipping_upload_log($originalFilename,$category,$detail,$now,$start_date,$end_date);
        return $id;
    }

    public function material_data_upload($filename,$uploadfile,$start_date,$id)
    {
        /***********uploadされたファイル名で工場を判断し処理を分岐させる******************/
        //アップロードされたファイル名を取得
        $originalName = $filename->getClientOriginalName();
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($uploadfile);
        $sheetCount = $spreadsheet->getSheetCount();
        $sheet = $spreadsheet->getSheetByName('カガミ');
        $max_row = $sheet->getHighestRow();
        //         // シートの最終列を取得
        $max_col = $sheet->getHighestColumn();

        $material_items = $this->_uploadRepository->get_material_items_name();
        // dd($material_items);

        // Fの値を取得する
        $material_ledger = [];
        for ($i=6; $i <$max_row ; $i++) { 

            $item_name = preg_replace('/^P/', '', $sheet->getCell('C' . $i)->getValue());

            $pay_remaining_count =  $sheet->getCell('F' . $i)->getValue();

            if (in_array($item_name, $material_items, true)) {
                $material_ledger[$item_name] = $pay_remaining_count;
            }

        }
        dd($material_ledger);
    
        //DBと一致したアイテムだけ在庫数をDBに保存する

        // dd($originalName);
        // for ($i = 0; $i < $sheetCount; $i++) {
        //     $sheet = $spreadsheet->getSheet($i);
        //     $sheetName = $sheet->getTitle();
        //     if ($sheetName === '取込用') 
        //     {
        //         //本社第２工場の場合
        //         dump($start_date);
        //         dump($sheetName);
        //         // シートの最終行を取得
        //         $max_row = $sheet->getHighestRow();
        //         // シートの最終列を取得
        //         $max_col = $sheet->getHighestColumn();
        //         if (!$this->isValidDate($start_date)) {
        //             return "invalid_date_format"; // 無効な日付の場合
        //         }
        //         // 開始日と終了日を数値化する
        //         $start = $this->dateToExcelSerial($start_date);
        //         //材料DBに登録されている品目を取得してくる
        //         $material_items = $this->_uploadRepository->get_material_items_name();
        //         // dd($material_items);
        //         //登録用配列
        //         $material_arr =[];
        //         for ($row = 1; $row <= $max_row; $row++) {
        //             // 入荷日
        //             $excel_day = $sheet->getCell('B' . $row)->getValue();
        //             if (is_null($excel_day) || !is_numeric($excel_day)) {
        //                 continue; // 日付がnullまたは数値でない場合はスキップ
        //             }
        //             //チェック
        //             $check = $sheet->getCell('A' . $row)->getValue();
        //             if($excel_day == $start && $check == "〇"){
        //                     /** 入荷日のフォーマットを直す["○○○○-○○-○○に"] **/
        //                 $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excel_day);
        //                 $formattedDate = $dateValue->format('Y-m-d'); // 希望の日付フォーマットに変換 
        //                 //材料品目コード
        //                 $material_code = $sheet->getCell('C' . $row)->getValue();
        //                 // 最初の文字を削除
        //                 $material_code = substr($material_code, 1);
        //                 $is_matched;
        //                 if(in_array($material_code,$material_items))
        //                 {
        //                     $is_matched ="matched";
        //                     //数量
        //                     $quantity = $sheet->getCell('D' . $row)->getValue();
        //                     //備考
        //                     $note = $sheet->getCell('E' . $row)->getValue();
        //                     $material_arr[] = [
        //                         "arrival_date" => $formattedDate,
        //                         "item_code" => $material_code,
        //                         "quantity"=> $quantity,
        //                         "note" => $note,
        //                         "is_matched" =>$is_matched,
        //                         "status" =>"no",
        //                         "factory" =>$sheetName,
        //                         "history_id" => $id
        //                     ];
        //                 }
        //             }
        //         }  
        //         //データベースに登録する
        //         $this->_uploadRepository->arrival_signup($material_arr);
        //     }elseif($sheetName === 'カガミ')
        //     {
        //          // シートの最終行を取得
        //         $max_row = $sheet->getHighestRow();
        //         // シートの最終列を取得
        //         $max_col = $sheet->getHighestColumn();
        //         dd($max_row,$max_col);
        //     }
        // }
        dd("s");
        return "true";
    }

    // VBAから送信されたデータをDBに格納
    public function receive_material_from_vba_insert($uploadfile,$start_date,$id)
    {

    }

    public function get_history_material($history_id)
    {
        $history_arr = $this->_uploadRepository->get_history_material($history_id);
        return $history_arr;
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    //追加依頼
    ///////////////////////////////////////////////////////////////////////////////////////
    //親品番を取得する
    public function get_parent_items()
    {
        $items = $this->_uploadRepository->get_parent_items();

        // テーブル名を配列に格納
        $tableNames = [];
        foreach ($items as $table) {
            $tableNames[] = $table->Tables_in_longinfos;
        }
        return $tableNames;
    }
    //品番で数量を計算する
    public function adding_order_process($item,$delivery_date,$quantity)
    {
        //品番に在庫があるか調べる
        $stork =  $this->_uploadRepository->stock_confirmation($item,$quantity);
        // dd($stork);s
        //在庫が依頼の数量より多い場合
        if ($stork >= $quantity) 
        {
            // 在庫が依頼の数量より多い場合の処理
            //依頼数量分在庫から消す
            $result =  $this->_uploadRepository->erase_quantity_minutes($item,$quantity);
            $this->_uploadRepository->adding_order_history($item,$delivery_date,$quantity);
            return  $result;

        }else{
            // 在庫が依頼の数量よりすくない場合の処理
            //今登録されてる長期の日を取得
            $long_term_date = $this->_uploadRepository->get_long_term_date();
            //$long_term_dateの配列の中に$delivery_dateがあるか
            if (in_array($delivery_date, $long_term_date)) {
                // $delivery_date が $long_term_date 配列に存在する場合の処理
                // 品番の日付の所に数量を増やす
                $this->_uploadRepository->quantity_addition($item,$delivery_date,$quantity);

            } else {
                // $delivery_date が $long_term_date 配列に存在しない場合の処理
                // 遅延に増やす
                $delivery_date = "遅延";
                $this->_uploadRepository->quantity_addition($item,$delivery_date,$quantity);
            }

            // dd($long_term_date,$delivery_date);
        }
        //追加依頼履歴に入れる
        $this->_uploadRepository->adding_order_history($item,$delivery_date,$quantity);

        return true;
        // $this->_uploadRepository->adding_order_process($item,$delivery_date,$quantity)
    }   
    
    /**
     * 品目コードの抽出処理
     * 
     * @param string $code 品目コード
     * @param int $number_position 数字が最初に出現する位置
     * @return string 抽出された品目コード
     */
    private function extractItemCode($code, $number_position)
    {
        $contains_X_before_number = strpos(substr($code, 0, $number_position), 'X') !== false;
        $contains_J_before_number = strpos(substr($code, 0, $number_position), 'J') !== false;

        if ($contains_X_before_number) {
            return substr($code, strpos($code, 'X')); // "X"を残して数字の直前から始める
        } elseif ($contains_J_before_number) {
            if (strpos($code, '77AN') !== false) {
                return substr($code, $number_position); // "77AN"があればそのまま
            } else {
                return substr($code, strpos($code, 'J')); // "J"から始める
            }
        } else {
            return substr($code, $number_position); // 数字から始める
        }
    }

    /**
     * 注文番号が重複しているかをチェック
     * 
     * @param string $order_number 注文番号
     * @param array $all_shipping_data 出荷データ
     * @return bool 重複している場合はtrue
     */
    private function isDuplicateOrder($order_number, $all_shipping_data)
    {
        foreach ($all_shipping_data as $item) {
            if ($item['order_number'] == $order_number) {
                // dd($item,$order_number);

                return true; // 重複が見つかった場合
            }
        }
        return false; // 重複がない場合
    }

    /**
     * 日付の有効性を確認
     * 
     * @param string $date 日付
     * @return bool 日付が有効か
     */
    private function isValidDate($date)
    {
        // 日付の形式を確認するロジック（例：YYYY-MM-DD形式）
        return (bool)strtotime($date); // strtotimeで有効な日付かを判定
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