<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrintHistory;
use Carbon\Carbon;

class ProcessExpiredData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:process-expired';
    protected $description = '期限切れの未処理データを処理済みにする';



    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 今日の日付を取得
        $today = Carbon::today();
         // 今日から40日前の日付を取得
        $fortyDaysAgo = $today->subDays(30);

        //発行されたデータを全て取得する
        $completedPrints = PrintHistory::where('input_complete_flag', "true")->get()->toArray();
        foreach ($completedPrints as $print) {
            // 期限切れか判定
            $startDate = Carbon::parse($print["start_date"]);
            if ($startDate->lt($fortyDaysAgo)) {
                $this->info("30日以上前だよ: " . $print["start_date"]);
                // 処理済みにする
                PrintHistory::where('id', $print["id"])->update(['input_complete_flag' => "false"]);
            } else {
                $this->info("30日以内だよ: " . $print["start_date"]);
            }
        }
    }
}
