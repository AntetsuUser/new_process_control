<?php 

namespace App\Services\Masta;
    
use App\Repositories\Masta\NumberRepository;
    
class NumberService 
{
    // リポジトリクラスとの紐付け
    protected $_numberRepository;

    // phpのコンストラクタ
    public function __construct(NumberRepository $numberRepository)
    {
        $this->_numberRepository = $numberRepository;
    }
    //
    public function  number_info($id)
    {
        //親品番の情報取得
        $item =  $this->_numberRepository->number_info($id);
        //子品番を変数に入れる
        $child_part_number1 = $item['select_item']->child_part_number1;
        $child_part_number2 = $item['select_item']->child_part_number2;;
        // 子品番が登録されていたら
        if(!empty($child_part_number1) && !empty($child_part_number2))
        {
            //子品番の情報を取得してくる
            $child1 = $this->_numberRepository->child_info($child_part_number1);
            $child2 = $this->_numberRepository->child_info($child_part_number2);
            if( empty($child1) || empty($child2)){
                return [$item];
            }
            return [$item,$child1,$child2];
        }
        else {
            return [$item];
        }

    }
    //numberにアップサートする
    public function upsert($data)
    {
        // ここで$numbers_arrをnumbers_DBに登録し、生成されたモデルを取得
        return $this->_numberRepository->upsert($data);
    }
    //processにアップサート
    public function process_upsert($data)
    {
        // ここで$numbers_arrをprocessDBに登録する
        return $this->_numberRepository->process_upsert($data);
    }
    //削除
    public function number_delete($id)
    {
        $this->_numberRepository->number_delete($id);
        $this->_numberRepository->process_delete($id);
    }
    // データベースに登録されてるデータを取得
    public function number_get()
    {
        return $this->_numberRepository->number_get();
    }
    //stockテーブルに登録する
    public function stock_insert($item_name)
    {
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
            $this->_numberRepository->stock_insert($stock_arr);
    }

    public function longinfos_create($process_json)
    {
        // /home/pi/Desktop/process_control/public/data.json
        // JSONファイルのパス
        $jsonFilePath = '/home/pi/Desktop/process_control/public/data.json';

        // ファイルが存在するか確認
        if (file_exists($jsonFilePath)) {
            // ファイルの内容を読み込む
            $jsonContent = file_get_contents($jsonFilePath);

            // JSONデータをデコードして配列に変換
            $data = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // JSONデータのデコードに成功
                foreach ($process_json as $key => $value) {
                   foreach ($data[$key] as $index => $item) {
                        $data[$key][$index]["addition"]= $data[$key][$index]["target"];
                        foreach ($value as $val) {
                            $data[$key][$index][$val]  = $data[$key][$index]["target"];
                        }
                    }
                    //longinfo DBに登録する
                    $this->_numberRepository->create_longinfo_table($key,$data[$key],"second_mysql");
                }
            } 
        } 
        $new_jsonFilePath = '/home/pi/Desktop/process_control/public/new_data.json';
        // ファイルが存在するか確認
        if (file_exists($new_jsonFilePath)) {
            // ファイルの内容を読み込む
            $jsonContent = file_get_contents($new_jsonFilePath);

            // JSONデータをデコードして配列に変換
            $data = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                // JSONデータのデコードに成功
                foreach ($process_json as $key => $value) {
                   foreach ($data[$key] as $index => $item) {
                        $data[$key][$index]["addition"]= $data[$key][$index]["target"];
                        foreach ($value as $val) {
                            $data[$key][$index][$val]  = $data[$key][$index]["target"];
                        }
                    }
                    //longinfo DBに登録する
                    $this->_numberRepository->create_longinfo_table($key,$data[$key],"third_mysql");
                }
            } 
        } 
    }
}