<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product_shipping;
use App\Models\Shipment_count;


class update_stock_Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update_stock_-command';

    protected $description = '在庫を更新';  // コマンドの説明


    /**
     * The console command description.
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

        
    }
}
