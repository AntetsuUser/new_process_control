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
        return $this->_uploadRepository->get_uplog();
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

        // ファイル名から日付を抽出
        // preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $originalFilename, $matches);
        // if (count($matches) === 4) {
        //     $year = $matches[1];
        //     $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        //     $day = $matches[3];

        //     $base_date = "{$year}-{$month}-{$day}"; // 日付の文字列を生成
        // } else {
        //     // 日付が見つからなかった場合は、エラー処理などを行う
        //     // 例えば、デフォルトの日付を設定したり、ユーザーに通知したりする
        //     // ここではとりあえず現在日時をデフォルトとして設定する例を示します
        //     $base_date = date('Y-m-d');
        // }
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($uploadfile);
        //シート名を指定する
        $sheet = $spreadsheet->getSheetByName('印刷用');
        //シートの最終行を取得
        $max_row = $sheet -> getHighestRow();
        //シートの最終列まで取得
        $max_col  = $sheet->getHighestColumn();

        // 値を格納する配列
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
                array_push($unregistered,$item_name);
                continue;
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
                    foreach ($columnIterator as $column) 
                    {
                        // 個数を入力
                        $value = $sheet->getCell($column->getColumnIndex() . $row + 1)->getValue();
                        // 日付を入力
                        $day = $sheet->getCell($column->getColumnIndex() . 5)->getValue();
                        $weekday = $sheet->getCell($column->getColumnIndex() . 7)->getValue();
                        $long_DB = 0;
                        //最後の行は入れない
                        if($day == "")
                        {
                            continue;
                        }
                        //長期の最期の週の所
                        if (is_numeric($day) && $day > 59) {
                            $unixDate = ($day - 25569) * 86400; // Excelシリアル値をUnixタイムスタンプに変換
                            $day = gmdate('Y-m-d', $unixDate); // "yyyy-mm-dd"形式にフォーマット (UTCタイムゾーンを使用)
                            $unixdate = ($weekday - 25569) * 86400;
                            $weekday = gmdate('Y-m-d', $unixdate); // 
                        //長期の表示されてる日付
                        } elseif (is_numeric($day) && $day > 0 && $day <= 31) 
                        {
                            //取得した日付がベースの日付より大きかったら
                            if ($day < substr($base_date, 8, 2)) {
                                $new_year = intval(substr($base_date, 0, 4));
                                $new_month = intval(substr($base_date, 5, 2)) + 1;
                                // 月が12を超えた場合は年を加算して月をリセット
                                if ($new_month > 12) {
                                    $new_year++;
                                    $new_month = 1;
                                }
                                $day = sprintf("%04d-%02d-%02d", $new_year, $new_month, $day);
                                $long_DB = $day;
                            } else {
                                
                                $day = substr($base_date, 0, 8) . $day;
                                $long_DB = $day;
                            }
                            $unixDate = strtotime($day); // Unixタイムスタンプに変換
                            if($weekday == NULL)
                            {
                                $weekday = "null";
                            }
                            else {
                                
                                $weekdaydate = date('w', $unixDate); // 曜日を数値で取得 (0: 日曜, 1: 月曜, ... 6: 土曜)
                                // 日本語の曜日を対応する配列を作成
                                $weekdays_arr = ['日', '月', '火', '水', '木', '金', '土'];
                                // 曜日を日本語に変換
                                $weekday = $weekdays_arr[$weekdaydate];
                            }
                        }
                        $days[] = $day;
                        $weekdays[] =  $weekday;
                        $target[] = $value !== null ? $value : 0;
                        $addition[] = 0;
                        $child_material_stock[] = 0;
                        $longinfos_day[] =  $long_DB;
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
        //0のところを削除する
        $filtered_dates = array_filter($longinfos_day, function($value) {
            return $value !== 0;
        });
        //フィルタリングした配列をDBに保存する
        $this->_uploadRepository->create_temp_long_term_date($filtered_dates);
        // 結果を表示        

    }
    //長期アップロードの履歴をDBに入れる
    public function upload_log($filename)
    {
        //アップロードされたファイル
        $originalFilename = $filename->getClientOriginalName();
        date_default_timezone_set('Asia/Tokyo'); // タイムゾーンを日本時間に設定
        $now = date("Y-m-d H:i:s"); // 現在の日付と時刻を取得（YYYY-MM-DD HH:MM:SS形式）
        $category = "長期情報";
        $detail = "アップロード";
        $this->_uploadRepository->upload_log($originalFilename,$category,$detail,$now);

    }
}