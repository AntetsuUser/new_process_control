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
        // dump($new_weeks);
        // $machines_load
        /////////////////////////////////////
        //品番ごとに一週間のごとの数量を出す
        /////////////////////////////////////
        //登録されている品番をstockテーブルから取得してくる
        $items = $this->_loadpredictionRepository->get_registration_item();
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
                $items_arr[$item_name][$key2] = $week_quantity;
            }
        }
        dd($items_arr);
        dd("sssss");
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
