<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Department;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのコマンドスケジュールを定義
     */
    protected function schedule(Schedule $schedule): void
    {
        // バックアップの処理と実行時間を設定
        $schedule->command('backup:run --only-db')->dailyAt('02:00');  // 毎日 02:00 にデータベースバックアップを実行
        $schedule->command('backup:run --only-db')->dailyAt('07:30');  // 毎日 07:30 にデータベースバックアップを実行
        $schedule->command('backup:run --only-db')->dailyAt('12:30');  // 毎日 12:30 にデータベースバックアップを実行
        $schedule->command('backup:run --only-db')->dailyAt('15:30');  // 毎日 15:30 にデータベースバックアップを実行
        $schedule->command('backup:run --only-db')->dailyAt('20:30');  // 毎日 20:30 にデータベースバックアップを実行

        // 月が替わった時に印刷回数を1にリセットするコマンドを設定
        $schedule->command('print-count:update')->dailyAt('04:30');  // 毎日 04:30 に印刷回数をリセット

        // 材料在庫の更新をするコマンドを設定
        $schedule->command('material:update')->dailyAt('04:30');  // 毎日 04:30 に材料在庫の更新を実行

        // 30日以上前の指示書でまだ未処理の物を処理済みにするコマンドを設定
        $schedule->command('data:process-expired')->dailyAt('05:00');  // 毎日 04:30 に材料在庫の更新を実行
    }

    /**
     * アプリケーションのコマンドを登録
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');  // コマンドディレクトリからコマンドをロード

        require base_path('routes/console.php');  // console.php で定義されたルートを読み込む
    }
}
