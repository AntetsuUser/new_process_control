<?php 

namespace App\Services\Signage;
    
use App\Repositories\Signage\SignageRepository;
use App\Repositories\LongInfo\LongInfoRepository;

    
class SignageService 
{
    // リポジトリクラスとの紐付け
    protected $_signageRepository;
    protected $_longinfoRepository;

    // phpのコンストラクタ
    public function __construct(SignageRepository $signageRepository, LongInfoRepository $longinfoRepository)
    {
        $this->_signageRepository = $signageRepository;
        $this->_longinfoRepository = $longinfoRepository;
    }
    
    public function getitem($department)
    {
        /********************日付け**************************/
        //表示する日付けを取得する
        $info_day = $this->_signageRepository->get_long_term_date();
        // 配列の最初に "遅延" を追加
        array_unshift($info_day, "遅延");
                
                // 日にちの配列
        $dateArray = [];

        // 曜日の配列
        $weekdayArray = [];
        $weekdayMap = [
            'Sunday'    => '日',
            'Monday'    => '月',
            'Tuesday'   => '火',
            'Wednesday' => '水',
            'Thursday'  => '木',
            'Friday'    => '金',
            'Saturday'  => '土'
        ];

        // 配列をループして、日付と曜日を取得
        foreach ($info_day as $key => $value) {
            if ($key > 0) { // 最初の要素（"遅延"）をスキップ
                // DateTimeオブジェクトを作成
                $date = new \DateTime($value);
                
                // 日にちを取得して配列に追加
                $dateArray[] = $date->format('d'); // 日にちだけ
                
                // 英語の曜日を取得し、日本語に変換して配列に追加
                $englishWeekday = $date->format('l'); // 英語の曜日
                $weekdayArray[] = $weekdayMap[$englishWeekday]; // 日本語の曜日に変換
            }
        }

        array_unshift($dateArray, "遅延");
        array_unshift($weekdayArray, "");
        /*****************品番を長期順に並び変える**************************/
        //製造課に登録されている品目をすべて取得してくる
        $item_arr = $this->_signageRepository->get_items($department);
        //
        //jsonの配列を取得してくる
        $file_path = '/home/pi/Desktop/process_control/public/item_sort_order.json';
        $json_data = file_get_contents($file_path);
        // JSONデータを配列にデコード
        $data = json_decode($json_data, true);
        $sort_data = [];
        //長期の順番にする
        foreach ($data as $key => $value) {
            if (in_array($value, $item_arr)) {
                $sort_data[] = $value;
            } 
        }

        //材料の情報を取得する
        $material_mark = $this->material_mark($sort_data);
        //素材の数量を取得
        $material_stock = $this->material_stock($sort_data);
        // dd($material_stock);
        //品番の工程が入る配列
        /*** 先に宣言しておく********************************/
        $process_names = [];
        $name_array = [
            "process1" => "102NC",
            "process2" => "102MC",
            "process3" => "103NC",
            "process4" => "103MC",
            "process5" => "組立",
            "process6" => "704MC",
        ];
        $name_join_array = [
            "process2" => "102NC/MC",
            "process4" => "103NC/MC",
            "process5" => "組立",
            "process6" => "704MC",
        ];
        $stock_process_names =[
            "process1" => "完NC在庫",
            "process2" => "完MC在庫",
            "process3" => "完NC在庫",
            "process4" => "完MC在庫",
            "process5" => "完組立在庫",
            "process6" => "完MC在庫",
        ];
        $join_stock_process_names =[
            "process2" => "完NC/MC在庫",
            "process4" => "完NC/MC在庫",
            "process5" => "完組立在庫",
            "process6" => "完MC在庫",
        ];

        $stock_names = ["material_stock_1","material_stock_2"];
        $display_arr = [];
        $display_stock_arr = [];
        $items = [];
        /*** 先に宣言しておく********************************/
        foreach ($sort_data as $item_name) 
        {
            $process_arr = [];
            //品番で検索して、結合フラグを確認する結合フラグが「1」だった場合process1とprocess3を配列から削除する
            $join_flag = $this->_signageRepository->join_flag_confirmation($item_name);
            //品番を渡してデータベースに存在するか確認
            $item_record = $this->_signageRepository->confirmation_exists_in_db($item_name);
            if($join_flag == 1)
            {
                $display_arr[$item_name] =  $name_join_array;
                $display_stock_arr[$item_name] = $join_stock_process_names;
            }else{
                $display_arr[$item_name] =  $name_array;
                $display_stock_arr[$item_name] = $stock_process_names;
            }
            if($item_record)
            {   
                //材料の在庫をそれぞれ配列に入れる
                $material_stock_1 = $this->_signageRepository->get_stock($item_name, "material_stock_1");
                $process_names[$item_name]['material_stock_1'] = $material_stock_1; 
                $material_stock_2 = $this->_signageRepository->get_stock($item_name, "material_stock_2");
                $process_names[$item_name]['material_stock_2'] = $material_stock_2; 
                foreach ($item_record[0] as $key => $value) {
                    if (strpos($key, 'process') !== false) 
                    { 
                        // 'process' が含まれている場合のチェック
                        $stock_key_name = $key."_stock";
                        //工程の在庫を取得する
                        $stock_quantity = $this->_signageRepository->get_stock($item_name,$stock_key_name);

                        if($join_flag == 1 && ($key == "process1" || $key == "process3"))
                        {
                            continue;
                        }
                        else {
                            $stock_quantity = $this->_signageRepository->get_stock($item_name,$stock_key_name);
                        }
                        $process_names[$item_name][$key] = $stock_quantity;
                        
                    }
                }
                foreach ($item_record as $key => $value) 
                {
                    foreach ($value as $key2 => $value2) 
                    {
                        
                        if (strpos($key2, 'process') !== false || strpos($key2, 'target') !== false) 
                        { 
                            if($join_flag == 1)
                            {
                                if($key2 == "process1" || $key2 == "process3")
                                {

                                    continue;
                                }
                                
                                $items[$item_name][$key2][] = $value2;

                            }
                            else {
                                
                                $items[$item_name][$key2][] = $value2;
                            }
                            // $items[$item_name][$key][] = $value2;
                        }
                        
                    }
                }
            }

        }
        $return_arr = [$sort_data,$info_day,$process_names,$display_arr,$items,$dateArray,$weekdayArray,$display_stock_arr,$material_mark,$material_stock];
        // dd($return_arr);
        return  $return_arr ;
    }

    //ipで製造課取得
    public function getproduction($uuid)
    {
         return  $this->_signageRepository->get_department($uuid);
    }

    //品目名称を取得してくる
    public function get_item_names($production)
    {
        return  $this->_signageRepository->get_item_names($production);
    }
    //工程を取得してくる
    public function get_process($production)
    {
        //7製造なら
        if($production == 8)
        {
            //工程の配列
            $name_array = [
            "102NC",
            "102MC",
            "103NC",
            "103MC",
            "組立",
            "704MC",
            ];
            return $name_array;
        }   
    }
    //品目集約から品番を取得してくる
    public function acquisition_from_item_aggregation($item_names)
    {
        $results = []; // 結果を格納するための配列を定義
        foreach ($item_names as $item) {
            $result = $this->_signageRepository->acquisition_from_item_aggregation($item);
            if (is_array($result)) {
                $results = array_merge($results, $result); // 結果をフラットな配列に追加
            } else {
                $results[] = $result; // 結果が配列でない場合、そのまま追加
            }
        }
        //配列の品番のテーブルが存在するか
        foreach ($results as $key => $table_name) {
            $exists = $this->_signageRepository->table_exists($table_name);
            if (!$exists) {
                unset($results[$key]); // 存在しない場合、その要素を削除
            }
        }
        // インデックスを再構築して綺麗な配列にする
        $results = array_values($results);

        return $results;
    }
    //工程を整理する
    public function process_sorting($production,$process)
    {
        //作業用と表示用の配列を作る
        $display_array = [];
        $process_array = [];
        $display_stock_array = [];
        //返す用の配列  
        $return_arr = [];
        // 7製造なら
        if($production == 8){
            foreach ($process as $value) {
                if(strpos($value, '102') !== false)
                {
                    $display_array[] = "102NC/MC";
                    $process_array[] = "process2";
                    $display_stock_array[] = "完NC/MC在庫 ";
                }else if(strpos($value, '103') !== false){
                    $display_array[] = "103NC/MC";
                    $process_array[] = "process4";
                    $display_stock_array[] = "完NC/MC在庫";
                }
                else if(strpos($value, '組立') !== false){
                    $display_array[] = "組立";
                    $process_array[] = "process5";
                    $display_stock_array[] = "完組立在庫";
                }
                else if(strpos($value, '704MC') !== false){
                    $display_array[] = "704MC";
                    $process_array[] = "process6";
                    $display_stock_array[] = "完MC在庫";
                }
            }
            //配列の重複をなくす
            $display_array = array_values(array_unique($display_array));
            $process_array = array_values(array_unique($process_array));
            $display_stock_array = array_values(array_unique($display_stock_array));
            
        }
        $return_arr = [$display_array,$process_array,$display_stock_array];


        return $return_arr;
    }

    //工程と品番で長期の情報を取得してくる
    public function info_data($process_array,$items)
    {
        //長期情報のデータを取得してくる
        $info_datas = [];
        foreach ($items as $item_name) 
        {
            foreach ($process_array as $process) {
                $info_data = $this->_signageRepository->info_data($item_name,$process);
                $info_datas[$item_name][$process] =  $info_data;
            }
        }
        return $info_datas;
    }
    public function stock_data($process_array,$items)
    {
        //在庫のデータを取得してくる
        $stock_datas = [];
        foreach ($items as $item_name) 
        {
            $stock_data = $this->_signageRepository->get_stock($item_name,"material_stock_1");
            $stock_datas[$item_name]["material_stock_1"] =  $stock_data;
            $stock_data = $this->_signageRepository->get_stock($item_name,"material_stock_2");
            $stock_datas[$item_name]["material_stock_2"] =  $stock_data;
            foreach ($process_array as $process) {
                $stock_key_name = $process.'_stock';
                $stock_data = $this->_signageRepository->get_stock($item_name,$stock_key_name);
                $stock_datas[$item_name][$process] =  $stock_data;
            }
        }
        return $stock_datas;
    }
    public function target_data($items)
    {
        $target_datas = [];
        foreach ($items as $value) {
            $target_data = $this->_signageRepository->target_data($value);
            $target_datas[$value] = $target_data;
        }
        return $target_datas;
    }

    public function get_date()
    {
        return $this->_signageRepository->get_long_term_date();
    }


    public function tablet_material_mar($sorted_keys) 
    {
        $result = [];
        // Get material marking information
        $material_mark = $this->material_mark($sorted_keys);
        // Get material stock quantities
        $material_stock = $this->material_stock($sorted_keys);

        $result = [$material_mark, $material_stock];
        return $result; // Ensure the space is a regular ASCII space
    }


    //親品番を渡して材料マーク配列を作成する
    private function material_mark($item_arr)
    {

        $data = [];
        foreach ($item_arr as $name) {
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
                            $material_mark_arr["102"][$processing_name][$key] = "　";
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
                            $material_mark_arr["103"][$processing_name][$key] = "　";
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
        return $material_mark_arr;
    }

    private function material_stock($send_arr)
    {
        $material_stock =[];
        foreach ($send_arr as $key => $value) {
            //アッシー品目から子品目を取得してくる
            $child_number = $this->_longinfoRepository->child_number_get($value,704);
            $child_number_arr = $child_number->toArray();
            $material_stock_quantity =[];
            foreach ($child_number_arr as $key2 => $value2) {
                //子品番から材料品番を取得してくる
                $material_number = $this->_longinfoRepository->material_number_get($value2);
                $material_stock_count = $this->_longinfoRepository->material_stock($material_number);
                $material_stock_quantity[] = $material_stock_count;
            }
            $material_stock[$value] = $material_stock_quantity;
        }
        return  $material_stock;
    }


}