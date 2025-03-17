<?php 

namespace App\Services\LongInfo;
    
use App\Repositories\LongInfo\LongInfoRepository;
use DateTime;

use Carbon\Carbon;
    
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

    //負荷予測（平準化予測）に必要な情報を求める
    public function selectitem($line_numbers)
    {
        $process_list = [];
        $process =  $this->_longinfoRepository->line_get_process($line_numbers);
        foreach ($process as $key => $value) {
            $item_name = $value["processing_item"];
            // dd($value/);
            if(strpos($item_name, '704'))
            {   
                if($value["process_number"] == 2)
                {
                    $process_list[$item_name][] = "704MC";
                }else if($value["process_number"]  == 1){
                    $process_list[$item_name][] = "組立";
                }   
            }
            else if (strpos($item_name, '102') !== false) {
                // $item_name に '102' が含まれている場合の処理
                //親品番を検索
                $item_data = $this->_longinfoRepository->find_parent($item_name);
                
                $item_data = $this->_longinfoRepository->find_parent($item_name);
                foreach ($item_data as $item) {
                    if($value["process_number"] == 2)
                    {
                        $process_list[$item["processing_item"]][] = "102MC";
                    }else if($process_number == 1){
                        $process_list[$item["processing_item"]][] = "102NC";
                    }  
                }
            } elseif (strpos($item_name, '103') !== false) {
                // $item_name に '103' が含まれている場合の処理
                $item_data = $this->_longinfoRepository->find_parent($item_name);
                foreach ($item_data as $item) {
                    if($value["process_number"] == 2)
                    {
                        $process_list[$item["processing_item"]][] = "103MC";
                    }else if($process_number == 1){
                        $process_list[$item["processing_item"]][] = "103NC";
                    }  
                }
            } 
            else {
                # code...
            }
        }
    //    dd($process_list);
        // dd($process_list);
        return $process_list;
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
            } else if (strpos($item_name, '103') !== false) {
                // $item_name に '103' が含まれている場合の処理
                $process_edit = "103" . $item_process;
                if (!in_array($process_edit, $process_list)) {
                    $process_list[] = $process_edit;
                }
            } else if (strpos($item_name, '704') !== false) {
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

    public function new_base_ability($info_process_arr,$selectitem)
    {
        //工程を最初抜き取る
        $ability_arr = [];
        $ability = 0.85;
        foreach ($info_process_arr as $item_name => $process) {
            foreach ($process as $key) {
                //工程によって処理を分ける
                if(strpos($key, '102') !== false)
                {
                    //親品番から子品番を求めてくる
                    $child_number = $this->_longinfoRepository->child_number_acquisition($item_name,"child_part_number1");
                    // $ability_arr[$key][$child_number]= [];
                    // 102が含まれていた場合
                    if(strpos($key, '/') !== false){
                        //"/"が含まれていて子品番の工程が結合されている場合
                        $process_number = 2;
                        // 子品番と工程から加工時間とストアを取得してくる
                        $process_information = $this->_longinfoRepository->acquisition_process_information($child_number,$process_number);
                        $processing_time = $process_information['processing_time'];
                        $store_count = count(explode(',',$process_information['store']));
                        $average = floor(54000 / $processing_time * $ability * $store_count);
                        $ability_arr[$key][$child_number]= $average;
                    }else{
                        if(strpos($key, 'NC') !== false){
                            $process_number = 1;
                            $ability = 0.75;
                        }elseif(strpos($key, 'MC') !== false) {
                            $process_number = 2;
                        }
                    }
                }else if(strpos($key, '103') !== false){
                    //親品番から子品番を求めてくる
                    $child_number = $this->_longinfoRepository->child_number_acquisition($item_name,"child_part_number2");
                    // 103が含まれていた場合
                    if(strpos($key, '/') !== false){
                        //"/"が含まれていて子品番の工程が結合されている場合
                        $process_number = 2;
                        // 子品番と工程から加工時間とストアを取得してくる
                        $process_information = $this->_longinfoRepository->acquisition_process_information($child_number,$process_number);
                        $processing_time = $process_information['processing_time'];
                        $store_count = count(explode(',',$process_information['store']));
                        $average = floor(54000 / $processing_time * $ability * $store_count);
                        $ability_arr[$key][$child_number]= $average;
                    }else{
                        
                    }
                }else if($key == "組立"){
                    //組立

                    $ability_arr["組立"][$item_name] = 200;
                }else if(strpos($key, '704') !== false){
                    //704が含まれていた場合
                    $process_number = 2;
                        // 子品番と工程から加工時間とストアを取得してくる
                    $process_information = $this->_longinfoRepository->acquisition_process_information($item_name,$process_number);
                    $processing_time = $process_information['processing_time'];
                    $store_count = count(explode(',',$process_information['store']));
                    $average = floor(54000 / $processing_time * $ability * $store_count);
                    $ability_arr[$key][$item_name]= $average;
                }else{
                    // それ以外
                }
                // $ability_arr[$key] = [];
            }
        }
        // dd($ability_arr);
        return $ability_arr;
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
                    if (!is_array($final_process)) {
                        $process_arr[] = $final_process;
                    }
                    else {
                        foreach ($final_process as $key => $value3) {
                            $process_arr[] = $value3;
                        }
                    }
                    //工程を配列に入れる(join_flagが1なら工程を結合してかえす)
                    $final_process =  $this->process_join($data['join_flag'],$child2_item_process,$child_part_number2);
                    if (!is_array($final_process)) {
                        $process_arr[] = $final_process;
                    }
                    else {
                        foreach ($final_process as $key => $value4) {
                            $process_arr[] = $value4;
                        }
                    }
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
                    //どこの工程なのかを判別する
                
                    //工程の番号を取得すればいいのでは？
                    if($table_check)
                    {
                        $item_arr[$parent_name][] = $final_process;
                        $day_arr[$parent_name] = $this->_longinfoRepository->get_day($parent_name,$process_count);
                    }
                }
            }
        }
        // dd($item_arr);
        // 配列の工程の順番を並び替え
        foreach ($item_arr as $sort_key => $sort_value) {
            if (!empty($sort_value) && strpos($sort_value[0],'103') !== false) {
                $item_arr[$sort_key] = array_reverse($sort_value);
            }
        }
        foreach ($item_arr as $sort_key => $sort_value) {
            $item_arr[$sort_key] = array_unique($item_arr[$sort_key]);
        }
        if (!isset($day_arr) || empty($day_arr)) {
            // $day_arrが存在しない、または空の配列の場合の処理
            return false;
        }
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
            $count = 0;
            if(is_array($item_value) && count($item_value) === 1)
            {
                $where_process = $item_value[0];
                $process_stock = "process1_stock";
                if(strpos($where_process,'102')!== false)
                {
                    if(strpos($item_sorted_arr[$item_key][0],'/') !== false or strpos($where_process,'MC')!== false)
                    {
                        $process_stock = "process2_stock";
                        $item_arr_count = 2;
                    }
                    else {
                        $process_stock = "process1_stock";
                        $item_arr_count = 1;
                    }
                }else if(strpos($where_process,'103')!== false)
                {
                    if(strpos($item_sorted_arr[$item_key][0],'/') !== false or strpos($where_process,'MC')!== false)
                    {
                        $process_stock = "process4_stock";
                        $item_arr_count = 4;
                    }
                    else {
                        $process_stock = "process3_stock";
                        $item_arr_count = 3;
                    }
                }

                //単体の工程の在庫を取得してくる
                $item_stock =  $this->_longinfoRepository->single_get_stock($item_key,$process_stock);

            }
            else {
                $item_stock =  $this->_longinfoRepository->get_stock($item_key);
                // dd($item_stock);
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
                // dump($item_stock);
                // 工程数を変数に格納
                $item_arr_count = count($item_sorted_arr[$item_key]);
                // dump($item_arr_count);
                if (strpos($item_sorted_arr[$item_key][0],'102') !== false and strpos($item_sorted_arr[$item_key][0],'/') !== false ) {
                    $item_arr_count = 2;
                }else if(strpos($item_sorted_arr[$item_key][0],'103') !== false and strpos($item_sorted_arr[$item_key][0],'/') !== false)
                {
                    $item_arr_count = 4;
                }
                if(isset($item_sorted_arr[$item_key][1]))
                {
                    if (strpos($item_sorted_arr[$item_key][1],'103') !== false and strpos($item_sorted_arr[$item_key][0],'/') !== false ) {
                        $item_arr_count = 4;
                    }
                }
            }
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
            $process = [];
            //結合判定が0で親品番でもない時
            foreach ($process_arr as $key => $value) 
            {
                $process_valule = str_replace('/', '', $value['process']);
                $text = $process_text.$process_valule;
                $process[] = $text;
            }
            // dd("aa");
            return $process;
        }
    }

    //品番の単体品番を取得
    public function single_number($info_process_arr)
    {
        $single_item_arr = [];
        foreach ($info_process_arr as $item_name => $process) {
            //単体品番を取得してくる
            $single_items = $this->_longinfoRepository->single_items_get($item_name);
            foreach ($single_items as $key => $value) {
                if(strpos($value, "102") !== false)
                {
                    //102の
                    $single_item_arr[$item_name]["102NC/MC"] = $value;
                }elseif(strpos($value, "103") !== false)
                {
                    //103の
                    $single_item_arr[$item_name]["103NC/MC"] = $value;
                }
                else {
                }
            }
        }
        return $single_item_arr;
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

    // ss
    public function quantity_get($item_arr)
    {
        // 数量を格納するための配列を宣言
        $quantity_arr = array();
        foreach ($item_arr as $key => $process) {
            // 品番テーブルからデータ取得
            $db_quantity = $this->_longinfoRepository->quantity_get($key);
            foreach ($db_quantity as $quant_value) {
                // 長期元数量を格納
                $quantity_arr[$key][0][] = $quant_value->target;

                $count = 0;
                foreach ($process as $process_key => $process_data) {
                    if (strpos($process_data,'102') !== false and strpos($process_data,'NC/MC') === false) {
                        $count = 1; 
                    }elseif (strpos($process_data,'102') !== false and strpos($process_data,'NC/MC') !== false) {
                        $count = 2;
                    }elseif (strpos($process_data,'103') !== false and strpos($process_data,'NC/MC') === false) {
                        $count = 3;
                    }elseif (strpos($process_data,'103') !== false and strpos($process_data,'NC/MC') !== false) {
                        $count = 4;
                    }elseif (strpos($process_data,'組立') !== false) {
                        $count = 5;
                    }else{
                        $count = 6;
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
    public function print_date_create($data_arr,$arr_count,$department,$ip)
    {
        $print_arr = [];
        //////////////////////
        //固有IDを作成
        //////////////////////
        $now = Carbon::now(); // 現在の日時を取得
        $now_id = $now->format('ymdHi'); // 2408281430 のようにフォーマット
        // dd($ip);
        //  ipの下4桁を取得
        // IPアドレスをドットで分割
        $parts = explode('.', $ip);
        // 3番目の部分を取得
        $part3 = $parts[2];
        // 4番目の部分を取得し、3桁に満たない場合は先頭に0を追加
        $part4 = str_pad($parts[3], 3, '0', STR_PAD_LEFT);
        // 結果を結合
        $ip4_digits = $part3 .$part4;

        //製造課ごとのprint回数を取ってくる
        $print_count =$this->_longinfoRepository->get_print_count($department);
        // 4番目の部分を取得し、3桁に満たない場合は先頭に0を追加
        $print_count = str_pad($print_count, 4, '0', STR_PAD_LEFT);
        //characteristic_idを作成して配列に入れる
        $print_arr['characteristic_id'] = $now_id.$print_count.$ip4_digits;
        
        //今まで加工した数を取得する
        $info_process = "process".$data_arr[8];
        //品番と工程で取得
        $processing = $this->_longinfoRepository->processing_all($info_process,$data_arr[0],$data_arr[2]);
        // 文字列を整数に変換
        $long_term_all = intval($data_arr[5]);
        $processing_quantity = intval($data_arr[4]);
        $processing = intval($processing);
        if($long_term_all == 0)
        {
            $processing_all = 0;
        }
        else {
            $processing_all = $long_term_all - $processing  + $processing_quantity;
        }
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
        $worker_name = $this->_longinfoRepository->worker_name_get($data_arr[7]);

        $print_arr['item_name'] = $item_name;
        $print_arr['parent_name'] = $data_arr[0];
        $print_arr['child_part_number1'] = $child_part_number1;
        $print_arr['child_part_number2'] = $child_part_number2;
        $print_arr['delivery_date'] = $data_arr[2];
        $print_arr['processing_quantity'] = $data_arr[4];
        $print_arr['start_date'] = $data_arr[3];
        //作業者人の名前にした方がいいかも

        $print_arr['woker_id'] = $data_arr[7];
        $print_arr['process'] = $data_arr[1];
        $print_arr['workcenter'] = $data_arr[6];
        $print_arr['capture_date'] = $create_infodate;
        $print_arr['processing_all'] = $processing_all;
        $print_arr['long_term_all'] = $data_arr[5];
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
        //NC,MCが結合されていたらどっちも引かなけばならない
        $info_process = "process".$process_number;
        $this->_longinfoRepository->info_calculation($parent_name,$info_process,$delivery_date,$processing_quantity);
        if (strpos($process, 'NC') !== false && strpos($process, 'MC') !== false) 
        {
            // $process に NC と MC の両方が含まれている場合の処理
            $info_process = "process".$process_number-1;
            $this->_longinfoRepository->info_calculation($parent_name,$info_process,$delivery_date,$processing_quantity);
        }

        //////////////////////////////////////////////////////////////////////
        //704なら
        //////////////////////////////////////////////////////////////////////



        //////////////////////////////////////////////////////////////////////
        //在庫の増減処理
        //////////////////////////////////////////////////////////////////////
        //工程番号から減らす工程在庫のカラム名を入れる変数
        $reduce_stock_name;
        // 材料
        if (false !== strpos($process, '102')) {
            $material_index= "child_part_number1";
            if ($join_flag == 1) {
                // 工程番号から減らす工程在庫のカラム名を作成する
                $reduce_stock_names = ["material_stock_1"]; 
                // 工程番号から増やす工程在庫のカラム名を作成する 
                $increase_stock_names = ["process" . $process_number . "_stock"];
                $this->material_stock_subtract($parent_name,$process,$material_index,$processing_quantity);
            } else {
                // 工程番号から増やす工程在庫のカラム名を作成する
                $increase_stock_names = ["process" . $process_number . "_stock"];
                if (false !== strpos($process, 'NC')) {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["material_stock_1"]; 

                    //ここで材料在庫から数量を減らす
                    $this->material_stock_subtract($parent_name,$process,$material_index,$processing_quantity);
                } else {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["process" . ($process_number - 1) . "_stock"]; 
                }
            }
        } elseif (false !== strpos($process, '103')) {
             $material_index= "child_part_number2";
            if ($join_flag == 1) {
                // 工程番号から減らす工程在庫のカラム名を作成する
                
                $reduce_stock_names = ["material_stock_2"];  
                // 工程番号から増やす工程在庫のカラム名を作成する
                $increase_stock_names = ["process" . $process_number . "_stock"];

                //ここで材料在庫から数量を減らす
                    $this->material_stock_subtract($parent_name,$process,$material_index,$processing_quantity);
            } else {
                // 工程番号から増やす工程在庫のカラム名を作成する
                $increase_stock_names = ["process" . $process_number . "_stock"];
                if (false !== strpos($process, 'NC')) {
                    // 工程番号から減らす工程在庫のカラム名を作成する
                    $reduce_stock_names = ["material_stock_2"]; 

                    //ここで材料在庫から数量を減らす
                    $this->material_stock_subtract($parent_name,$process,$material_index,$processing_quantity);
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
        //親品番から
        $this->_longinfoRepository->creation_count($parent_name,$processing_quantity);
        
    }

    public function get_in_work()
    {
        return $this->_longinfoRepository->get_in_work();
    }
    
    public function sin_material_mark($info_process_arr,$quantity_arr,$date_arr)
    {
        
        // dump($info_process_arr);
        // dump($quantity_arr);
        // dump($date_arr);

        $process_stock = [];
        $number_used_material = [];
        $material_stock =[];
        $use_number = [];
        $calculation = [];
        //加工できる品番の工程が入っている
        // 品番の工程在庫を取得してくる
        $item_stock = [];
        // dd($quantity_arr);
        foreach ($quantity_arr as $item_name => $value) {
            //工程結合判定を取得してくるget_join_flag($item_name)

            $process_join_flag = $this->_longinfoRepository->get_join_flag($item_name);
            //　ｃflagが1なら子品番のNCとMC工程結合
            //品番の工程在庫を取得してくる
            $item_process_stock = $this->_longinfoRepository->get_stock($item_name);
            //出荷を加味した在庫を取得してくるshipment_count<-DB
            $complete_stocks = $this->_longinfoRepository->get_complete_stock($item_name);

            //出荷や一週間の作成個数を計算する
            $complete_stock = $stock_count = intval($complete_stocks["before_update_count"]) + intval($complete_stocks["made_count"]);
            $complete_stock2 = $stock_count = intval($complete_stocks["before_update_count"]) + intval($complete_stocks["made_count"] - intval($complete_stocks['shipment_count']));

            if($process_join_flag)
            {
                //7製造の場合
                // 結合判定が1ならMCの工程在庫を一緒にして配列に入れる
                $item_stock[$item_name]["102"] = $item_process_stock["process2_stock"] + $item_process_stock["process5_stock"] + $complete_stock;
                $item_stock[$item_name]["103"] = $item_process_stock["process4_stock"] + $item_process_stock["process5_stock"] + $complete_stock;

                $calculation[$item_name]["102"] = $item_process_stock["process2_stock"] + $item_process_stock["process5_stock"] + $complete_stock2;
                $calculation[$item_name]["103"] = $item_process_stock["process4_stock"] + $item_process_stock["process5_stock"] + $complete_stock2;
            }
            else {
                //それぞれの工程在庫を取得
                $item_stock[$item_name]["102"] = $item_process_stock["process1_stock"] + $item_process_stock["process2_stock"] + $item_process_stock["process5_stock"] + $complete_stock;
                $item_stock[$item_name]["103"] = $item_process_stock["process3_stock"] + $item_process_stock["process4_stock"] + $item_process_stock["process5_stock"] + $complete_stock;

                $calculation[$item_name]["102"] = $item_process_stock["process1_stock"] + $item_process_stock["process2_stock"] + $item_process_stock["process5_stock"] + $complete_stock2;
                $calculation[$item_name]["103"] = $item_process_stock["process3_stock"] + $item_process_stock["process4_stock"] + $item_process_stock["process5_stock"] + $complete_stock2;
            }
            //加工品番から子品番を求めて材料品番を取得してくる

            $child_number = $this->_longinfoRepository->child_number_get($item_name,704);
            $child_number_arr = $child_number->toArray();
            $material_stock_quantity =[];
            foreach ($child_number_arr as $key2 => $value2) {
                //子品番から材料品番を取得してくる
                $material_number = $this->_longinfoRepository->material_number_get($value2);
                $material_stock_count = $this->_longinfoRepository->material_for_mark($material_number);
                $material_stock[$material_number] = (int)$material_stock_count;
                $number_used_material[$material_number][] = $item_name;
            }
        }

        // dump($item_stock);
        // dump($calculation);
        $material_array = [];

        $materialmark_array = [];
        foreach ($quantity_arr as $key => $value) {
            $material_array[$key]["102"] = $value[0];
            $material_array[$key]["103"] = $value[0];
            foreach ($value[0] as $key2 => $value2) {
                $materialmark_array[$key]["102"][] = "　";
                $materialmark_array[$key]["103"][] = "　";
            }
        }
        
        $days = [];
        //材料台帳の支給残数から工程在庫を引いた数を求める
        foreach ($material_array as $name => $quantities) {

            foreach ($quantities as $index => $quantity) {
                $days = $quantity;
                foreach ($quantity as $key => $value) 
                {
                    if($value == 0)
                    {
                        continue;
                    }
                    // 在庫が0以下なら処理を中断
                    if ($item_stock[$name][$index] <= 0) {
                        break;
                    }
                    if ($item_stock[$name][$index] >= $value) {
                        // そのまま引ける場合
                        $item_stock[$name][$index] -= $value;
                        $material_array[$name][$index][$key] = 0; // 実際に引いた数を上書き
                        $materialmark_array[$name][$index][$key] = "●";

                    } else {
                        if($material_array[$name][$index][$key] > 0)
                        {
                            // 在庫が足りない場合、残り全部を引く
                            $material_array[$name][$index][$key] = $material_array[$name][$index][$key] - $item_stock[$name][$index]; // 実際に引いた数を上書き

                            $item_stock[$name][$index] = 0;
                            $materialmark_array[$name][$index][$key] = "○";
                        }else
                        {
                            $item_stock[$name][$index] = 0;
                        }
                    }
                }
            }
        }
        $remaining = [];
        foreach ($number_used_material as $material_name => $use_numbers) {

            $process_sum = 0;
            $process = "";
            foreach ($use_numbers as $index => $process_item_number) {
                if (strpos($material_name, "102") !== false) {
                    // "102" が含まれている場合の処理
                    $process_sum += $calculation[$process_item_number]["102"];
                    $process = "102"; 
                } elseif (strpos($material_name, "103") !== false) {
                    // "103" が含まれている場合の処理
                    $process_sum += $calculation[$process_item_number]["103"];
                    $process = "103"; 
                }
            }
            $remaining_count = $material_stock[$material_name] - $process_sum;
            $remaining[$material_name] = $remaining_count;
            foreach ($use_numbers as $index => $process_item_number) {
                if (strpos($material_name, "102") !== false) {
                    // "102" が含まれている場合の処理
                    $process_sum += $calculation[$process_item_number]["102"];
                    $process = "102"; 
                } elseif (strpos($material_name, "103") !== false) {
                    // "103" が含まれている場合の処理
                    $process_sum += $calculation[$process_item_number]["103"];
                    $process = "103"; 
                }
                $remaining_arr[$process_item_number][$process] = $remaining_count;
            }
        }
        $material_stock = [];
        foreach ($days as $key => $value) {
            $process  = "";
            foreach ($number_used_material as $material_name => $use_numbers) {
                foreach ($use_numbers as $index => $process_item_number) {
                    if (strpos($material_name, "102") !== false) {
                        // "102" が含まれている場合の処理
                        $process = "102";
                    } elseif (strpos($material_name, "103") !== false) {
                        // "103" が含まれている場合の処理
                        $process = "103";
                    }
                    if($material_array[$process_item_number][$process][$key] == 0)
                    {
                        continue;
                    }
                    if ($remaining[$material_name] <= 0) {
                        break;
                    }
                    if ($remaining[$material_name] >= $material_array[$process_item_number][$process][$key]) {
                        // そのまま引ける場合
                        $remaining[$material_name] -= $material_array[$process_item_number][$process][$key];
                        $material_array[$process_item_number][$process][$key] = 0; // 実際に引いた数を上書き
                        $materialmark_array[$process_item_number][$process][$key] = "●";

                    } else {
                        // 在庫が足りない場合、残り全部を引く
                        $material_array[$process_item_number][$process][$key] = $material_array[$process_item_number][$process][$key] - $remaining[$material_name]; // 実際に引いた数を上書き

                        $remaining[$material_name] = 0;
                        $materialmark_array[$process_item_number][$process][$key] = "○";
                    }
                }
            }
        }
        return [$materialmark_array,$remaining_arr];
    }


    //材料の在庫を取得してくる
    public function material_stock($send_arr)
    {
        $material_stock =[];
        foreach ($send_arr as $key => $value) {
            //アッシー品目から子品目を取得してくる
            $child_number = $this->_longinfoRepository->child_number_get($key,704);
            $child_number_arr = $child_number->toArray();
            $material_stock_quantity =[];
            foreach ($child_number_arr as $key2 => $value2) {
                //子品番から材料品番を取得してくる
                $material_number = $this->_longinfoRepository->material_number_get($value2);
                $material_stock_count = $this->_longinfoRepository->material_stock($material_number);
                $material_stock_quantity[] = $material_stock_count;
            }
            $material_stock[$key] = $material_stock_quantity;
        }
        return  $material_stock;
    }
    //材料引き当てのマークを配列で作成して返す
    public function material_mark($quantity_arr)
    {
        //配列から品番を取り出し品目集約を取得してくる
        $collect_name_arr = [];
        foreach ($quantity_arr as $parent_name => $value) {
            $collect_name = $this->_longinfoRepository->get_collect_name($parent_name);
            if (!in_array($collect_name, $collect_name_arr)) {
                $collect_name_arr[] = $collect_name;
            }
        }
        //品目集約で一致する品目をすべて取得してくる
        $parent_names = [];
        foreach ($collect_name_arr as  $collect_name) {
            $parent_name = $this->_longinfoRepository->get_parent_name($collect_name);
            if (is_array($parent_name)) {
                $parent_names = array_merge($parent_names, $parent_name);
            } else {
                $parent_names[] = $parent_name;
            }
        }
        //  長期情報がある品番のDBから数量情報を取得してくる
        $data = [];
        foreach ($parent_names as $name) {
            $DB_data = $this->_longinfoRepository->db_exists($name);
            if($DB_data)
            {
                $data[$name] = $DB_data;
            } 
        }
        //材料の品番のと在庫を取得してくる
        $number_used_material = [];
        $material_stock =[];
        foreach ($data as $name => $data_arr) {
            //アッシー品目から子品目を取得してくる
            $child_number = $this->_longinfoRepository->child_number_get($name,704);
            $child_number_arr = $child_number->toArray();
            foreach ($child_number_arr as $key2 => $value2) {
                //子品番から材料品番を取得してくる
                $material_number = $this->_longinfoRepository->material_number_get($value2);
                $material_stock_count = $this->_longinfoRepository->material_for_mark($material_number);
                $material_stock[$material_number] = (int)$material_stock_count;
                $number_used_material[$material_number][$name] = $data_arr;
            }
        }


        //引き当てマークの配列を作成
        $material_mark_arr = []; 
        foreach ($number_used_material as $material_name => $data_contained) {
            $processing_names = [];
            $data;
            foreach ($data_contained as $processing_name => $quantity_data) {
                $processing_names[] = $processing_name;
                $data = $quantity_data;
            }
            $variable = $material_stock[$material_name];
            $mark_arr = [];
            foreach ($quantity_data as $key => $value) {
                foreach ($processing_names as $index => $processing_name) {
                    if(strpos($material_name, '102') !== false)
                    {
                        if($data_contained[$processing_name][$key]["target"] == 0 || $variable <= 0)
                        {
                            $material_mark_arr["102"][$processing_name][$key] = "";
                            continue;
                        }
                        $variable = $variable - $data_contained[$processing_name][$key]["target"];
                        if($variable > 0)
                        {
                            $material_mark_arr["102"][$processing_name][$key] = "●";
                        }
                        else {
                            $material_mark_arr["102"][$processing_name][$key] = "○";
                        }
                    }elseif (strpos($material_name, '103') !== false) {
                        if($data_contained[$processing_name][$key]["target"] == 0 || $variable <= 0)
                        {
                            $material_mark_arr["103"][$processing_name][$key] = "";
                            continue;
                        }
                        $variable = $variable - $data_contained[$processing_name][$key]["target"];
                        if($variable > 0)
                        {
                            $material_mark_arr["103"][$processing_name][$key] = "●";
                        }
                        else {
                            $material_mark_arr["103"][$processing_name][$key] = "○";
                        }
                    }
                }
            }
        }
        // dd("end");
        return $material_mark_arr;
    }
    //平準化用の配列を作成
    public function base_ability($quantity_arr,$date_arr,$line_numbers)
    {
        $item_average = [];
        //$line_numbersにストアがふくまれていた場合
        if(strpos($line_numbers, "ストア") !== false)
        {
            $process = $this->_longinfoRepository->line_get_process($line_numbers);
            foreach ($process as $index => $value) {
                $processing_item = $value['processing_item'];
                $ability= 0.85;
                $item = $processing_item;
                $item_average["組立"][$item] = 200;
            }
        }else
        {
            //ストア以外
            $process = $this->_longinfoRepository->line_get_process($line_numbers);
            foreach ($process as $key => $value) {
                //加工品目名
                $processing_item_name =  $value['processing_item'];
                //加工工程名
                $process_name =  $value['process'];
                if($process_name == "NC")
                {
                    //NC工程
                    //NCの能力は75％で計算する
                    $ability= 0.75;

                }elseif($process_name == 'M/C')
                {
                    //MC工程
                    //NCの能力は85％で計算する
                    $ability= 0.85;
                    // dump($processing_item_name);
                    //processing_itemの中の102,103,704などで品番を判定する
                    if(strpos($processing_item_name, "102") !== false ){
                        //102が含まれていた場合
                        $item_number = "102MC";
                        $processing_time = $value['processing_time'];
                        $store_count = count(explode(',',$value['store']));
                        $average = floor(54000 / $processing_time * $ability * $store_count);
                        $Parent_Items = $this->_longinfoRepository->get_Parent_Item_Number($processing_item_name);
                        foreach ($Parent_Items as $item_index => $parent_item) {
                            $item_average[$item_number][$processing_item_name] = $average;
                        }

                    }elseif(strpos($processing_item_name, "103") !== false ){
                        //103が含まれていた場合
                        $item_number = "103MC";
                        $processing_time = $value['processing_time'];
                        $store_count = count(explode(',',$value['store']));
                        $average = floor(54000 / $processing_time * $ability * $store_count);
                        $Parent_Items = $this->_longinfoRepository->get_Parent_Item_Number($processing_item_name);
                        foreach ($Parent_Items as $item_index => $parent_item) {
                            $item_average[$item_number][$processing_item_name]= $average;
                        }

                    }elseif(strpos($processing_item_name, "704") !== false ){
                        //704が含まれていた場合
                        //配列初期宣言
                        $item_number = "704MC";
                        $processing_time = $value['processing_time'];
                        $store_count = count(explode(',',$value['store']));
                        //15時間÷加工時間×能力×ストアの数
                        $average = floor(54000 / $processing_time * $ability * $store_count);

                        $item_average[$item_number][$processing_item_name] = $average;

                    }else{
                        //それ以外の追加が来た時
                    }
                }else{
                    //それ以外の追加が来た時
                }

            }
            // dd($item_average);
            
        }
        return $item_average;
    }

    /*
        親品番と加工工程と材料のカラム名をもらって在庫の増減
        もらう値
        $parent_name 
        $process
        $material_index
        
     */
    private function material_stock_subtract($parent_name,$process,$material_index,$processing_quantity)
    {
        $child_number = $this->_longinfoRepository->child_number_get($parent_name,704);
        $child_number_arr = $child_number->toArray();
        $material_stock_quantity =[];

        //$child_number_arr[$material_index]材料を検索してくる
        $item = $this->_longinfoRepository->material_stock_search($child_number_arr[$material_index]);

        //材料を検索して数量を足す
        $this->_longinfoRepository->material_stock_subtract($item,$processing_quantity);
    }
}