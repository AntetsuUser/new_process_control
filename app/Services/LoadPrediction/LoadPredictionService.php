<?php 

namespace App\Services\LoadPrediction;

use App\Repositories\LoadPrediction\LoadPredictionRepository;
use DateTime;
use DateInterval;
use DatePeriod;

class LoadPredictionService 
{
    // リポジトリクラスとの紐付け
    protected $_loadpredictionRepository;

    // phpのコンストラクタ
    public function __construct(LoadPredictionRepository $loadpredictionRepository)
    {
        $this->_loadpredictionRepository = $loadpredictionRepository;
    }

    // 製造課を取得してくる
    public function department_get()
    {
        return $this->_loadpredictionRepository->department_get();
    }

    // 負荷計算
    public function load_calculation($department_id)
    {
        /////////////////////////////////////
        //渡された製造課idの設備を取得してくる
        /////////////////////////////////////
        $machines = $this->_loadpredictionRepository->get_machines($department_id);
        if (empty($machines)) {
            // machinesが空だった場合、コントローラーに値を渡してviewに返す
            return $machines;
        }
        $machines_load =[];

        /////////////////////////////////////
        //各設備に初期値を入れる
        /////////////////////////////////////
        foreach ($machines as $key => $value) {
            //各設備に初期値を入れる
            $machines_load[$value] = [0,0,0];
        }

        // 今の長期の日付を取得する
        $long_term_date = $this->_loadpredictionRepository->get_long_term_date();
        //休みの日を取得
        $holiday = $this->_loadpredictionRepository->get_holiday();
        // 日付リストを取得
        $date_list = $this->getAndSortDateRange($long_term_date);
        // 一週間ごとに分ける
        $weeks = $this->splitIntoWeeks($date_list);
        //休みの日を消した一週間の配列を作り直す
        $new_weeks = [];
        foreach ($weeks as $key => $value) {
           $resultArray = array_diff($value, $holiday);
           $new_weeks[] = $resultArray;
        }
        // 最初と最後の配列を削除しい1週間後から3週間後までの配列を作成
        unset($new_weeks[0]);
        unset($new_weeks[count($new_weeks)]);

        // 添字を再設定する
        $new_weeks = array_values($new_weeks);
        // 空配列を削除
        $new_weeks = array_filter($new_weeks, function($week) {
            return !empty($week);
        });
        /////////////////////////////////////
        //品番ごとに一週間のごとの数量を出す
        /////////////////////////////////////
        //登録されている品番をstockテーブルから取得してくる
        $items = $this->_loadpredictionRepository->get_registration_stock_item();
        // dd($items);
        $items_arr = [];
        foreach ($items as $key => $item_name) {
            foreach ($new_weeks as $key2 => $week) {
                // $items_arr[$value][] = $week;
                // $items_arr[$value] = [0,0,0];
                $week_quantity = 0;
                foreach ($week as $day) {
                    //品番の長期DBに品番と日付けを渡して終了を取得する
                    $quantity =  $this->_loadpredictionRepository->get_long_term_quantity($item_name,$day);
                     if ($quantity === null) {
                            $quantity = 0;
                        }
                    $week_quantity  = $week_quantity +  $quantity;
                }
                if($key2 <= 2)
                {
                    $items_arr[$item_name][$key2] = $week_quantity;
                }
            }
        }
        $processing_seconds = [];
        foreach ($machines_load as $machine_number => $value) {
            //設備番号で品番を取得する
            $return = $this->_loadpredictionRepository->get_product_number($machine_number);

            foreach ($return as $index => $item_name) {
                $count = [];
                if (strpos($item_name['processing_item'], '102') !== false || strpos($item_name['processing_item'], '103') !== false) {
                    // 102103の場合
                    //  ストアの数を追加する
                    $arr = explode(",", $item_name['store']); // コンマで分割して配列にする
                    $length = count($arr); // 配列の長さを取得
                    $item_name['store_length'] = $length;
                    //品番に102,103がついている場合子品番なので親品番をデータベースから取得して来る
                    $return_Parent_Item = $this->_loadpredictionRepository->get_Parent_Item_Number($item_name['processing_item']);
                    foreach ($return_Parent_Item as $name) {
                        if($item_name['process_number'] == "2") {
                            $average = 0.85;
                            //工程番号2（MC加工の場合）
                            foreach ($items_arr[$name] as $key => $quantity) {
                                //一週間の個数の時間をもとめる
                                $count[$key] = $quantity * $item_name["processing_time"] / $item_name["store_length"] * $average;

                                $machines_load[$machine_number][$key] =  $machines_load[$machine_number][$key] + $count[$key];
                            }
                        }elseif($item_name['process_number'] == "1")
                        {
                            $average = 0.75;
                            //工程番号1（NC加工の場合）
                            foreach ($items_arr[$name] as $key => $quantity) {
                                //一週間の個数の時間をもとめる
                                $count[$key] = $quantity * $item_name["processing_time"] / $item_name["store_length"] * $average;

                                $machines_load[$machine_number][$key] =  $machines_load[$machine_number][$key] + $count[$key];
                            }
                        }
                    }
                }else if(strpos($item_name['processing_item'], '704') !== false){
                    //704の場合
                     $average = 0.85;
                    //  ストアの数を追加する
                    $arr = explode(",", $item_name['store']); // コンマで分割して配列にする
                    $length = count($arr); // 配列の長さを取得
                    $item_name['store_length'] = $length;
                    foreach ($items_arr[$item_name['processing_item']] as $key => $quantity) {
                        //一週間の個数の時間をもとめる
                        $count[$key] = $quantity * $item_name["processing_time"] / $item_name["store_length"] * $average;

                        $machines_load[$machine_number][$key] =  $machines_load[$machine_number][$key] + $count[$key];
                    }
                }else
                {
                    //103など増えたとき
                }
            }
        }
        foreach ($machines_load as $machine_number => $value) {

            foreach ($value as $index => $processing_time) {
                $load_rate = 0;
                //一週間実働の稼働時間を求める(一週間の営業×15時間)
                $week_average = count($new_weeks[$index]) * 15 * 60 * 60;
                $load_rate = $processing_time / $week_average * 100;
                $machines_load[$machine_number][$index] = number_format($load_rate, 1) . '%';
            }
        }
        $returning_value = [$machines_load,$new_weeks,$department_id];
        return $returning_value;
    }
    /**
     * 負荷予測機械画面を表示
     * 
     * @param 
     * 
     * $lineNo　設備番号
     * $week_arr　3週間の日にち配列
     */
    public function load_detail_calculation($lineNo,$week_arr){
                
        //設備で加工できる品番を取得してくる
        $return = $this->_loadpredictionRepository->get_product_number($lineNo);
        if($return)
        {
            $items = $this->_loadpredictionRepository->get_registration_item();
            $items_arr = [];
            // dd($items);
            //品番の一日の数量を取得する
            foreach ($items as $key => $item_name) {
                foreach ($week_arr as $key2 => $week) {
                    // $items_arr[$value][] = $week;
                    // $items_arr[$value] = [0,0,0];
                    $week_quantity = 0;
                    foreach ($week as $day) {
                        //品番の長期DBに品番と日付けを渡して終了を取得する
                        $quantity =  $this->_loadpredictionRepository->get_long_term_quantity($item_name,$day);
                        if ($quantity === null) {
                                $quantity = 0;
                            }
                        $items_arr[$item_name][$key2][$day] = $quantity;
                    }
                }
            }
            // dd($items_arr);
            //設備で加工できる品番の加工時間を求める
            foreach ($return as $index => $item_name) {
                $count = [];
                $average = 0;
                if (strpos($item_name['processing_item'], '102') !== false || strpos($item_name['processing_item'], '103') !== false) {
                    // 102103の場合
                    //  ストアの数を追加する
                    $arr = explode(",", $item_name['store']); // コンマで分割して配列にする
                    $length = count($arr); // 配列の長さを取得
                    $item_name['store_length'] = $length;
                    // dd($item_name);
                    //品番に102,103がついている場合子品番なので親品番をデータベースから取得して来る
                    $return_Parent_Item = $this->_loadpredictionRepository->get_Parent_Item_Number($item_name);
                    // dump($item_name);
                    foreach ($return_Parent_Item as $item_index => $parent_name) {
                        $item_name['parent_name'] = $parent_name;
                        // dump($parent_name);
                        $processing_numbers[$parent_name] = [];
                        if (isset($items_arr[$item_name["parent_name"]])) {
                            // キーが存在する場合の処理
                            foreach ($items_arr[$item_name["parent_name"]] as $week_index => $day_value) {
                                if($item_name['process_number'] == "1") {
                                    $average = 0.75;
                                }else{
                                    $average = 0.85;
                                }
                                if (!isset($processing_numbers[$item_name["parent_name"]])) {
                                    $processing_numbers[$item_name["parent_name"]][] = $day_value; // 各週ごとの配列を初期化
                                }else{
                                    $processing_numbers[$item_name["parent_name"]][] = $day_value; // 各週ごとの配列を初期化
                                }
                                foreach ($day_value as $day => $day_quantity) {
                                    // `$day_time[$week_index]` が存在しない場合は初期化
                                    if (!isset($day_time[$week_index])) {
                                        $day_time[$week_index] = []; // 各週ごとの配列を初期化
                                    }

                                    // `$day_time[$week_index][$day]` が存在しない場合は初期化
                                    if (!isset($day_time[$week_index][$day])) {
                                        $day_time[$week_index][$day] = 0; // デフォルト値を 0 に設定
                                    }

                                    if ($day_quantity > 0) {
                                        // 加工時間を計算して追加
                                        $day_processing_time = $day_quantity * $item_name["processing_time"] / $item_name["store_length"] * $average;
                                        $day_time[$week_index][$day] += $day_processing_time;
                                    }
                                }
                            }
                        } else {
                            // 存在しない場合、スキップ
                            continue;
                        }

                    }
                }else if(strpos($item_name['processing_item'], '704') !== false){
                    //704の場合
                    $average = 0.85;
                    //  ストアの数を追加する
                    $arr = explode(",", $item_name['store']); // コンマで分割して配列にする
                    $length = count($arr); // 配列の長さを取得
                    $item_name['store_length'] = $length;
                    if (!isset($processing_numbers[$item_name["processing_item"]])) {
                        $processing_numbers[$item_name["processing_item"]] = []; // 各週ごとの配列を初期化
                    }
                    //日にちごとの加工時間を求め,一日の稼働時間で割り負荷率をパーセントで求める。
                    if (isset($items_arr[$item_name["processing_item"]])) {
                        foreach ($items_arr[$item_name["processing_item"]] as $week_index => $day_value) {
                            if (!isset($processing_numbers[$item_name["processing_item"]])) {
                                $processing_numbers[$item_name["processing_item"]][] = $day_value; // 各週ごとの配列を初期化
                            }else{
                                $processing_numbers[$item_name["processing_item"]][] = $day_value; // 各週ごとの配列を初期化
                            }
                            foreach ($day_value as $day => $day_quantity) {
                                // `$day_time[$week_index]` が存在しない場合は初期化
                                if (!isset($day_time[$week_index])) {
                                    $day_time[$week_index] = []; // 各週ごとの配列を初期化
                                }

                                // `$day_time[$week_index][$day]` が存在しない場合は初期化
                                if (!isset($day_time[$week_index][$day])) {
                                    $day_time[$week_index][$day] = 0; // デフォルト値を 0 に設定
                                }

                                if ($day_quantity > 0) {
                                    // 加工時間を計算して追加
                                    $day_processing_time = $day_quantity * $item_name["processing_time"] / $item_name["store_length"] * $average;
                                    $day_time[$week_index][$day] += $day_processing_time;
                                }
                            }
                        }
                    }else
                    {
                        continue;
                    }
                }else
                {
                    //103など増えたとき
                }
            }
            //加工時間をパーセントに直していく
            // dd("sssss");
            foreach ($day_time as $key => $day) {
                $percentage[$key] =[];
                foreach ($day as $key_name => $value) {
                    // dd($day);
                    $percentage[$key][$key_name] = round(($value / (15 * 60 * 60)) * 100, 1);
                }
            }
            $filtered_arr = array_filter($processing_numbers, function ($item) {
                return !empty($item);
            });
            // dd($processing_numbers,$day_time,$percentage);
            $returning_value = [$filtered_arr,$percentage,$week_arr];
        }else
        {
            // 配列が空の場合
            $returning_value = "null";
        }
        return $returning_value;
    }

    // 日付リストを取得し、範囲を取得してソートする関数
    protected function getAndSortDateRange(array $long_term_date): array
    {
        $date_list = array_column($long_term_date, 'day');

        $start_date = min($date_list);
        $end_date = max($date_list);

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end = (clone $end)->modify('+1 day'); // 元の$endを変更しない
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        $missing_dates = [];
        foreach ($period as $date) {
            $formatted_date = $date->format('Y-m-d');
            if (!in_array($formatted_date, $date_list)) {
                $missing_dates[] = $formatted_date;
            }
        }

        // 日付リストに抜けている日付を追加
        $date_list = array_merge($date_list, $missing_dates);

        // 日付を昇順にソート
        sort($date_list);

        return $date_list;
    }

    // 日付リストを一週間ごとに分ける関数
    protected function splitIntoWeeks(array $date_list): array
    {
        $weeks = [];
        $week = [];
        $week_start_date = null;

        foreach ($date_list as $date) {
            $current_date = new DateTime($date);

            // 最初の週の開始日を設定
            if ($week_start_date === null) {
                $week_start_date = clone $current_date;
                $week_start_date->modify('sunday this week'); // 日曜日始まりに設定
            }

            // 現在の日付が最初の週の範囲に含まれる場合
            if ($current_date <= $week_start_date) {
                $week[] = $date;
            } else {
                // 週の範囲を超えた場合、新しい週を開始
                if (!empty($week)) {
                    $weeks[] = $week;
                }
                $week = [$date];
                $week_start_date = clone $current_date;
                $week_start_date->modify('sunday this week');
            }
        }

        // 最後の週を追加
        if (!empty($week)) {
            $weeks[] = $week;
        }

        return $weeks;
    }


}
