<?php 

namespace App\Services\Qr;
    
use App\Repositories\Qr\QrReadRepository;
use Carbon\Carbon;

    
class QrReadService 
{
    // リポジトリクラスとの紐付け
    protected $_qrreadRepository;

    // phpのコンストラクタ
    public function __construct(QrReadRepository $qrreadRepository)
    {
        $this->_qrreadRepository = $qrreadRepository;
    }
    public function getdirection_date($characteristic_id)
    {
       return $this->_qrreadRepository->getdirection_date($characteristic_id);
    }

    //  実績入力を反映データベースに反映させる
    public function achievement_application($directions_data)
    {
        $characteristic_id = $directions_data["unique_id"];
        //poor_processingかpoor_materialがNULLじゃない時
        if (!is_null($directions_data["poor_processing"]) || !is_null($directions_data["poor_material"])) {
            // 加工不良、材料不良があるとき
            // 'poor_processing' が NULL なら 0 に置き換え
            if (is_null($directions_data["poor_processing"])) {
                $directions_data["poor_processing"] = 0;
            }
            else {
                $directions_data["poor_processing"] = intval($directions_data["poor_processing"]);
            }
            // 'poor_material' が NULL なら 0 に置き換え
            if (is_null($directions_data["poor_material"])) {
                $directions_data["poor_material"] = 0;
            }
            else {
                $directions_data["poor_material"] = intval($directions_data["poor_material"]);
            }
            //不良の数を足す
            $defect_quantity = $directions_data["poor_material"] + $directions_data["poor_processing"];
            //品番の長期情報データベースと在庫データベースに数を反映させる

            $process = $directions_data["process"];
            $process_number = $directions_data["process_number"];
            //////////////////////////////////////////////////////////////////////
            //在庫を減らす
            /////////////////////////////////////////////////////////////////////
            //工程番号から減らす工程在庫のカラム名を入れる変数
            $reduce_stock_name = "process" . $process_number . "_stock";

            // 在庫から不良の数だけマイナスする
            $this->_qrreadRepository->subtract_from_stock($directions_data["parent_name"],$reduce_stock_name,$defect_quantity);
            //////////////////////////////////////////////////////////////////////
            //長期情報の数量を増やす
            //////////////////////////////////////////////////////////////////////
            //工程番号から減らす工程在庫のカラム名を入れる変数
            $process_number_int =intval($process_number);
            for ($i=$process_number_int; $i >= 1 ; $i--) { 
                $reduce_stock_names[] = "process" . $i;
            }
            $this->_qrreadRepository->increase_long_term_information($directions_data["parent_name"],$reduce_stock_names,$defect_quantity,$directions_data["delivery_date"]);

        }
        // 指示書の作業フラグをfalseにする
        $this->_qrreadRepository->working_end($characteristic_id);

    }
    public function input_history_create($directions_data)
    {
        //入力履歴に値を入れるために配列をきれいにする
        $directions_data["characteristic_id"] = $directions_data["unique_id"];
        unset($directions_data['unique_id']);
        // processing_quantity の null チェックと変換
        $directions_data['processing_plan_quantity'] = isset($directions_data['processing_quantity']) ? $directions_data['processing_quantity'] : 0;
        unset($directions_data['processing_quantity']);

        // good_product の null チェックと変換
        $directions_data['good_item'] = isset($directions_data['good_product']) ? $directions_data['good_product'] : 0;
        unset($directions_data['good_product']);

        // poor_processing の null チェックと変換
        $directions_data['processing_defect_item'] = isset($directions_data['poor_processing']) ? $directions_data['poor_processing'] : 0;
        unset($directions_data['poor_processing']);

        // poor_material の null チェックと変換
        $directions_data['material_defect_item'] = isset($directions_data['poor_material']) ? $directions_data['poor_material'] : 0;
        unset($directions_data['poor_material']);
        $directions_data["capture_date"] = $directions_data["create_day"];
        unset($directions_data['create_day']);
        $directions_data['input_datetime'] = Carbon::now('Asia/Tokyo');
        $this->_qrreadRepository->input_history_create($directions_data);
    }

}