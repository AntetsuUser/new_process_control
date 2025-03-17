<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Material_stock;
use App\Models\Material_arrival;

use App\Models\Product_shipping;
//Shipment_count
use App\Models\Shipment_count;

class UpdateMaterialCommand extends Command
{
    /**
     * コンソールコマンドの名前とシグネチャー
     *
     * @var string
     */
    protected $signature = 'material:update';  // コマンド名とシグネチャー
    protected $description = '材料の在庫を更新';  // コマンドの説明

    /**
     * 新しいコマンドインスタンスを作成
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();  // 親クラスのコンストラクタを呼び出し
    }

    /**
     * コンソールコマンドを実行
     *
     * @return void
     */
    public function handle()
    {
        // アップロードされた情報で反映していないものだけを取得
        $items = Material_arrival::where('status','no')->get()->toArray();




        // 各アイテムについてループ処理
        foreach ($items as $key => $value) {
            $item_code = $value["item_code"];  // アイテムコードを取得
            $quantity = (int) $value['quantity'];  // 数字型に変換
            $status = $value["status"];  // ステータスを取得
            
            // 対応する在庫情報を取得
            $stock = Material_stock::where('material_name', $item_code)->first();
            
            if ($stock) {
                // 在庫が見つかった場合、現在の在庫数を取得し、数量を加算
                $current_stock = (int) $stock->material_stock;  // 現在の在庫数
                $current_for_mark = (int) $stock->material_for_mark;  // 現在のマーキング用在庫数

                // 新しい在庫数とマーキング用在庫数を計算
                $updated_stock = $current_stock + $quantity;
                $updated_for_mark = $current_for_mark + $quantity;

                // 在庫情報を更新
                $stock->update([
                    'material_stock' => $updated_stock,
                    'material_for_mark' => $updated_for_mark,
                ]);
                
                // Material_arrival のステータスを 'yes' に更新
                Material_arrival::where('id', $value['id'])->update(['status' => 'yes']);
                
                // 成功メッセージを表示
                $this->info("アイテム '{$item_code}' は正常に更新されました。");
            } else {
                // 在庫が見つからなかった場合の警告メッセージ
                $this->warn("アイテム '{$item_code}' の出荷が見つかりませんでした。");
            }
        }

        // アップロードされた情報で反映していないものだけを取得
        $shipping_items = Product_shipping::where('status','no')->get()->toArray();


        // 各アイテムについてループ処理
        foreach ($shipping_items as $key => $value) {
            $item_code = $value["product_code"];  // アイテムコードを取得
            $quantity = (int) $value['delivered_quantity'];  // 数字型に変換
            $status = $value["status"];  // ステータスを取得

            //表示用の在庫の数量を減らす


            //在庫マーク用の数量の調整
            // 対応する在庫情報を取得
            $stock = Shipment_count::where('item_name', $item_code)->first();
            
            if ($stock) {
                // 在庫が見つかった場合、現在の在庫数を取得し、数量を加算
                $current_stock = (int) $stock->shipment_count;  // 現在の在庫数
                 $this->info("在庫数'{$current_stock}' ");

                // 新しい在庫数とマーキング用在庫数を計算
                $updated_stock = $current_stock + $quantity;

                // 在庫情報を更新
                $stock->update([
                    'shipment_count' => $updated_stock,

                ]);
                
                // Material_arrival のステータスを 'yes' に更新
                Product_shipping::where('id', $value['id'])->update(['status' => 'yes']);
                
                // 成功メッセージを表示
                $this->info("アイテム '{$item_code}' は正常に更新されました。");
            } else {
                // 在庫が見つからなかった場合の警告メッセージ
                $this->warn("アイテム '{$item_code}' の在庫が見つかりませんでした。");
            }
        }
    }
}
