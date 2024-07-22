<?php 

namespace App\Services\LongInfo;
    
use App\Repositories\LongInfo\LongInfoRepository;
use DateTime;
    
class LongInfoService 
{
    // リポジトリクラスとの紐付け
    protected $_longinfoRepository;

    // phpのコンストラクタ
    public function __construct(LongInfoRepository $longinfoRepository)
    {
        $this->_longinfoRepository = $longinfoRepository;
    }
    //工場を取得してくる
    public function factory_get()
    {
       return $this->_longinfoRepository->factory_get();
    }
    //
    public function selectable($line_numbers)
    {
        $process =  $this->_longinfoRepository->line_get_process($line_numbers);
        

        $process_list = [];
        foreach ($process as $process_date) {
            $item_name = $process_date["processing_item"];
            $item_process = str_replace('/', '', $process_date["process"]);
            //品番に102（シャフト）、103（ホールド）,704(シャフトアッシー)それぞれの値が含まれていたら
            if (strpos($item_name, '102') !== false) {
                // $item_name に '102' が含まれている場合の処理
                $process_edit = "102" . $item_process;
                if (!in_array($process_edit, $process_list)) {
                    $process_list[] = $process_edit;
                }
            } elseif (strpos($item_name, '103') !== false) {
                // $item_name に '103' が含まれている場合の処理
                $process_edit = "103" . $item_process;
                if (!in_array($process_edit, $process_list)) {
                    $process_list[] = $process_edit;
                }
            } elseif (strpos($item_name, '704') !== false) {
                // $item_name に '704' が含まれている場合の処理
                if ($item_process === "組立") {
                    if (!in_array($item_process, $process_list)) {
                        $process_list[] = $item_process;
                    }
                }else {
                    $process_edit = "704" . $item_process;
                    if (!in_array($process_edit, $process_list)) {
                        $process_list[] = $process_edit;
                    }
                }
            }

        }
        
        // dump($process_list);
        return $process_list;
    }


    //長期情報表示に必要なデータを取得しコントローラーに返す
    public function longinfo_date($line_numbers)
    {
        // line_numbersから品番、工程番号、工程を取得する
        $process = $this->_longinfoRepository->line_get_process($line_numbers);
        $item_arr = array();
        // //品番   から親品番と子品番と結合判定を取得してくる
        foreach ($process as $value) {
            $item_name = $value['processing_item'];
            //親品番と子品番を取得
            $item_data = $this->_longinfoRepository->find_parent($item_name);
            // numberDBの子品番に値があるかないか確認しに行く
            foreach ($item_data as $key => $data) 
            {
                $parent_name = $data['processing_item'];
                if (!isset($item_arr[$parent_name])) {
                    //longinfoDBに品番のテーブルがあるか確認する
                    $table_check = $this->_longinfoRepository->table_check($parent_name);
                    if($table_check)
                    {
                        $item_arr[$parent_name] = [];
                        $day_arr[$parent_name] = [];
                    }
                }
                // $day_arr[$parent_name] = [];
                //選択されたラインで加工するのが親品番かどうか
                $process_count = 0;
                $process_arr =[];
                if(!$data['child_flag'])
                {
                    //子品番フラグがfalseなら
                    $child_part_number1 = $data['child_part_number1'];
                    $child_part_number2 = $data['child_part_number2'];
                    $processing_item = $data['processing_item'];
                    // dump($child_part_number1,$child_part_number2,$processing_item);
                    //子品番1の工程を取得する
                    $child1_item_process = $this->_longinfoRepository->item_get_process($child_part_number1);
                    //工程の長さを取得する
                    $process_count = $this->array_count($child1_item_process,$process_count);
                    //子品番2の工程を取得する
                    $child2_item_process = $this->_longinfoRepository->item_get_process($child_part_number2);
                    $process_count = $this->array_count($child2_item_process,$process_count);
                    //工程を配列に入れる(join_flagが1なら工程を結合してかえす)
                    $final_process =  $this->process_join($data['join_flag'],$child1_item_process,$child_part_number1);
                    //配列に入れる
                    $process_arr[] = $final_process;
                    //工程を配列に入れる(join_flagが1なら工程を結合してかえす)
                    $final_process =  $this->process_join($data['join_flag'],$child2_item_process,$child_part_number2);
                    //配列に入れる
                    $process_arr[] = $final_process;
                    //加工できるのが親品目の2工程目だったら
                    if($value["process_number"] != "1")
                    {
                        //親品番の工程を取得する
                        $parent_item_process = $this->_longinfoRepository->item_get_process($processing_item);
                        $process_count = $this->array_count($parent_item_process,$process_count);
                        $final_process =  $this->process_join($data['join_flag'],$parent_item_process,$processing_item);
                        $process_arr = array_merge($process_arr, $final_process);
                    }
                    else {
                    //親品目の工程が1のとき
                       $process_count + 1;
                       $process_arr[] = $value["process"];
                    }
                    $table_check = $this->_longinfoRepository->table_check($parent_name);
                    if($table_check)
                    {
                        $item_arr[$parent_name] = $process_arr;
                        $day_arr[$parent_name] = $this->_longinfoRepository->get_day($parent_name,$process_count);
                    }
                }
                else {
                    $item_process = $this->_longinfoRepository->item_get_process($item_name);
                    $process_count = $this->array_count($item_process,$process_count);
                    $final_process =  $this->process_join($data['join_flag'],$item_process,$item_name);
                    $table_check = $this->_longinfoRepository->table_check($parent_name);
                    if($table_check)
                    {
                        $item_arr[$parent_name][] = $final_process;
                        $day_arr[$parent_name] = $this->_longinfoRepository->get_day($parent_name,$process_count);
                    }

                }
            }
        }

        // 配列の工程の順番を並び替え
        foreach ($item_arr as $sort_key => $sort_value) {
            if (strpos($sort_value[0],'103') !== false) {
                $item_arr[$sort_key] = array_reverse($sort_value);
            }
        }
        // dd($item_arr);
        // dd($day_arr);
        // 各品番の日付を格納した配列をソート
        uasort($day_arr, function($a, $b) {
            // Define the keyword for "遅延"
            $delay_keyword = "遅延";

            // Check if $a or $b is "遅延"
            if ($a === $delay_keyword) {
                return -1; // $a ("遅延") should come before $b
            } elseif ($b === $delay_keyword) {
                return 1; // $b ("遅延") should come before $a
            }

            // Handle other cases (both are dates or both are strings)
            if (!is_string($a) && !is_string($b)) {
                // Both are dates, compare them
                $dateA = new DateTime($a);
                $dateB = new DateTime($b);
                return $dateA <=> $dateB;
            } elseif (is_string($a) && is_string($b)) {
                // Both are strings, sort alphabetically
                return $a <=> $b;
            } elseif (is_string($a)) {
                // $a is a string, move it to the beginning
                return -1;
            } else {
                // $b is a string, move $a to the end
                return 1;
            }
        });
        foreach ($day_arr as $key => $value) {
            $item_sorted_arr[$key] = $item_arr[$key];
        }
        //在庫を取得してくる
        foreach ($item_sorted_arr as $item_key => $item_value) {
            $item_stock =  $this->_longinfoRepository->get_stock($item_key);
            // 不要キーの削除
            unset($item_stock["id"]);
            unset($item_stock["processing_item"]);
            if (strpos($item_sorted_arr[$item_key][0],'102') !== false and strpos($item_sorted_arr[$item_key][0],'/') !== false ) {
                unset($item_stock["process1_stock"]);
            }else if(strpos($item_sorted_arr[$item_key][0],'103') !== false and strpos($item_sorted_arr[$item_key][0],'/') !== false)
            {
                unset($item_stock["process3_stock"]);
            }
            if(isset($item_sorted_arr[$item_key][1]))
            {
                if (strpos($item_sorted_arr[$item_key][1],'103') !== false and strpos($item_sorted_arr[$item_key][0],'/') !== false ) {
                    unset($item_stock["process3_stock"]);
                }
            }

            $count = 0;
            // 工程数を変数に格納
            $item_arr_count = count($item_sorted_arr[$item_key]);
            // DBのデータを使いやすいように配列に格納
            // $countが2までは素材在庫を配列に、それ以降は各工程の完成品在庫を配列に格納
            foreach ($item_stock as $index => $stock_value) {
                if ($count < 2) {
                    $stock_arr[$item_key]["material"][] = $stock_value;
                }elseif ($count < $item_arr_count + 2) {
                    $stock_arr[$item_key]["process"][] = $stock_value;
                }
                $count++;
            }
        }
        
        // コントローラーに渡すための配列を作成
        $send_arr["item_sorted_arr"] = $item_sorted_arr;
        $send_arr["stock_arr"] = $stock_arr;


        return $send_arr;
    }
    //配列の長さを取得する
    private function array_count($process_arr ,$count )
    {
        $length = $count + count($process_arr);
        return $length;
    }

    //工程の頭に品番をつけたり,工程をくっつけたり
    private function process_join($join_flag,$process_arr,$child_number)
    {
        // 検索する部分文字列
        $shaft_number = "102";
        $hold_number = "103";
        $assy_number = "704";

        if(strpos($child_number, $shaft_number) !== false)
        {
            $process_text = $shaft_number;
        }elseif(strpos($child_number, $hold_number) !== false)
        {
            $process_text = $hold_number;
        }
        elseif(strpos($child_number, $assy_number) !== false)
        {
            $process_text = $assy_number;
        }
        else {
            $process_text = "error";
        }
        // dump($process_text);
        $process_items = [];
        //結合フラグが1で子品番の場合
        if($join_flag == 1 && strpos($child_number, $assy_number) == false)
        {
            foreach ($process_arr as $key => $value) {
               if ($process_text != $shaft_number && $process_text != $hold_number) {
                    $process_text .= "/";
                }
                //工程を結合する
                $process = str_replace('/', '', $value['process']);
                $process_text .= $process;
            }
            // $process_items[] = $process_text;
            // dump($process_text);
            return $process_text;
        }
        //シャフトアッシーの場合
        elseif(strpos($child_number, $assy_number) !== false){
            $process = [] ;
            foreach ($process_arr as $key => $value) {
                if($value["process"] != "組立")
                {
                    $process_text = str_replace('/', '', $value['process']);
                    $process[] = $assy_number . $process_text;
                }
                else {
                    $process[] = $value['process'];
                }
            }
            return $process;
        }
        else {
            # code...
            //結合判定が0で親品番でもない時
        }
    }

    // 日付と曜日の取得
    public function date_get()
    {
        $db_date = $this->_longinfoRepository->date_get();
        // 曜日の配列
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $date_arr = array();
        foreach ($db_date as $key => $value) {
            $date = new DateTime($value->day);
            // 曜日を数値で取得
            $dayOfWeekIndex = $date->format('w');
            // 日本語の曜日を配列から取得
            $dayOfWeek = $weekdays[$dayOfWeekIndex];

            $date_arr[0][] = $value->day;
            $date_arr[1][] = substr($value->day, -2);
            $date_arr[2][] = $dayOfWeek;
        }
        return $date_arr;
    }

    // 長期数量を取得
    public function quantity_get($item_arr)
    {
        // 数量を格納するための配列を宣言
        $quantity_arr[] = array();
        foreach ($item_arr as $key => $process) {
            // 品番テーブルからデータ取得
            $db_quantity = $this->_longinfoRepository->quantity_get($key);
            // dump($db_quantity);
            foreach ($db_quantity as $quant_value) {
                // 長期元数量を格納
                $quantity_arr[$key][0][] = $quant_value->target;

                $count = 0;
                foreach ($process as $process_key => $process_data) {
                    if (strpos($process_data,'NC/MC') !== false) {
                        $count = $count + 2; 
                    }else{
                        $count++;
                    }
                    $table_name = "process" . $count;
                    $quantity_arr[$key][$process_key+1][] = $quant_value->$table_name;
                }
            }
        }
        return $quantity_arr;
    }
    //工程のlotを取得する
    public function lot_get($info_process_arr)
    {
        $lot_arr = [];
        foreach ($info_process_arr as $key => $arr_value) 
        {
            $parent_item = $key;
            $lot_arr[$parent_item] = [];
            foreach ($arr_value as $value) {
                $item_number;
                //102,103は子品番なので検索して取得してくる
                if(strpos($value, '/') !== false)
                {
                    if(strpos($value, '102') !== false)
                    {
                        $item_number = '102';
                        $item = $this->_longinfoRepository->child_number_get($parent_item,$item_number);
                        $process = "M/C";
                    }
                    elseif(strpos($value, '103') !== false)
                    {
                        $item_number = '103';
                        $item = $this->_longinfoRepository->child_number_get($parent_item,$item_number);
                        $process = "M/C";
                    }
                }else {
                    if(strpos($value, 'NC') !== false)
                    {
                        $process = "NC";
                    }else {
                        $process = "MC";
                    }
                    if(strpos($value, '102') !== false)
                    {
                        $item_number = '102';
                        $item = $this->_longinfoRepository->child_number_get($parent_item,$item_number);
                    }
                    elseif(strpos($value, '103') !== false)
                    {
                        $item_number = '103';
                        $item = $this->_longinfoRepository->child_number_get($parent_item,$item_number);
                    } else {
                        if($value == "704MC")
                        {
                            $process = "M/C";
                            $item  = $parent_item;
                        }else {
                            $process = $value;
                            $item  = $parent_item;
                        }
                    }    
                }
                //ロットを拾ってくる
                $lot = $this->_longinfoRepository->lot_get($item,$process);
                $lot_arr[$parent_item][] = $lot;
            }
        }
        return $lot_arr;
    }
    public function print_date_create($data_arr,$arr_count)
    {
        //連想配列を作成する
        $print_arr = [];
        //characteristic_idを作成して配列に入れる
        $digits = sprintf('%04d', $arr_count +1);
        $characteristic_id =  date("ymdHis") . $digits;
        $print_arr['characteristic_id'] = $characteristic_id;

        //親品番から子品番を取得してくる
        $item = $this->_longinfoRepository->child_number_get($data_arr[0],704);
        $child_part_number1 = $item->child_part_number1;
        $child_part_number2 = $item->child_part_number2;
        //工程で品目名称を取得
        if (false !== strpos($data_arr[1], '102')) {

            $item_name = $this->_longinfoRepository->get_item_name($child_part_number1);
            $print_arr['processing_item'] = $child_part_number1;

        }elseif(false !== strpos($data_arr[1], '103')){
            $item_name =$this->_longinfoRepository->get_item_name($child_part_number2);
            $print_arr['processing_item'] = $child_part_number2;

        }elseif (false !== strpos($data_arr[1], '704') || false !== strpos($data_arr[1], '組立'))
        {
            $item_name = $this->_longinfoRepository->get_item_name($data_arr[0]);
            $print_arr['processing_item'] = $data_arr[0];
        }
        else {
            $item_name = "null";
        }
                
        //長期作成日を取得して配列に入れる
        $create_infodate = $this->_longinfoRepository->get_create_infodate();
        $worker_name = $this->_longinfoRepository->worker_name_get($data_arr[8]);

        $print_arr['item_name'] = $item_name;
        $print_arr['parent_name'] = $data_arr[0];
        $print_arr['child_part_number1'] = $child_part_number1;
        $print_arr['child_part_number2'] = $child_part_number2;
        $print_arr['delivery_date'] = $data_arr[2];
        $print_arr['processing_quantity'] = $data_arr[4];
        $print_arr['start_date'] = $data_arr[3];
        //作業者人の名前にした方がいいかも

        $print_arr['woker_id'] = $data_arr[8];
        $print_arr['process'] = $data_arr[1];
        $print_arr['workcenter'] = $data_arr[7];
        $print_arr['capture_date'] = $create_infodate;
        $print_arr['processing_all'] = $data_arr[5];
        $print_arr['long_term_all'] = $data_arr[6];
        $print_arr['input_complete_flag'] = "true";
        //print_arrをDBに登録する
        $this->_longinfoRepository->printing_history_insert($print_arr);
        $print_arr['woker_id'] = $worker_name;
        return $print_arr;
    }   

    public function long_info_quantity($data_arr,$process_number)
    {
        //parent_nameとprocessとprocess_numberとdelivery_dateとprocessing_quantity
        $parent_name = $data_arr['parent_name'];
        $join_flag = $this->_longinfoRepository->get_join_flag($parent_name);
        $process = $data_arr['process'];
        $delivery_date = $data_arr['delivery_date'];
        $processing_quantity = $data_arr['processing_quantity'];

        //////////////////////////////////////////////////////////////////////
        //長期数量から選択した数量分引く
        //////////////////////////////////////////////////////////////////////
        $info_process = "process".$process_number;
        $this->_longinfoRepository->info_calculation($parent_name,$info_process,$delivery_date,$processing_quantity);


        //////////////////////////////////////////////////////////////////////
        //在庫の増減処理
        //////////////////////////////////////////////////////////////////////
        //工程番号から減らす工程在庫のカラム名を入れる変数
        $reduce_stock_name;
        // 材料
        if (false !== strpos($process, '102')) {
            if ($join_flag == 1) {
                // 工程番号から減らす工程在庫のカラム名を作成する
                $reduce_stock_names = ["material_stock_1"]; 
                // 工程番号から増やす工程在庫のカラム名を作成する 
                $increase_stock_names = ["process" . $process_number . "_stock", "process" . ($process_number - 1) . "_stock"];
            } else {
                // 工程番号から増やす工程在庫のカラム名を作成する
                $increase_stock_names = ["process" . $process_number . "_stock"];
                if (false !== strpos($process, 'NC')) {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["material_stock_1"]; 
                } else {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["process" . ($process_number - 1) . "_stock"]; 
                }
            }
        } elseif (false !== strpos($process, '103')) {
            if ($join_flag == 1) {
                // 工程番号から減らす工程在庫のカラム名を作成する
                $reduce_stock_names = ["material_stock_2"];  
                // 工程番号から増やす工程在庫のカラム名を作成する
                $increase_stock_names = ["process" . $process_number . "_stock", "process" . ($process_number - 1) . "_stock"];
            } else {
                // 工程番号から増やす工程在庫のカラム名を作成する
                $increase_stock_names = ["process" . $process_number . "_stock"];
                if (false !== strpos($process, 'NC')) {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["material_stock_2"]; 
                } else {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["process" . ($process_number - 1) . "_stock"]; 
                }
            }
        } else {
            // 工程番号から増やす工程在庫のカラム名を作成する
            $increase_stock_names = ["process" . $process_number . "_stock"];
            // 工程番号から減らす工程在庫のカラム名を作成する
            if (false !== strpos($process, '組立')) {
                if ($join_flag == 1) {
                    $reduce_stock_names = [
                        "process" . ($process_number - 1) . "_stock",
                        "process" . ($process_number - 3) . "_stock",
                        "process" . ($process_number - 2) . "_stock",
                        "process" . ($process_number - 4) . "_stock"
                    ];
                } else {
                    $reduce_stock_names = [
                        "process" . ($process_number - 1) . "_stock",
                        "process" . ($process_number - 3) . "_stock"
                    ];
                }
            } else {
                $reduce_stock_names = [
                    "process" . ($process_number - 1) . "_stock"
                ];
            }
        }
        // DBで在庫の増減をする
        $this->_longinfoRepository->increase_stock($increase_stock_names, $processing_quantity, $parent_name, $reduce_stock_names);
    }

    public function get_in_work()
    {
        return $this->_longinfoRepository->get_in_work();
    }
}