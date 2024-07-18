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

}