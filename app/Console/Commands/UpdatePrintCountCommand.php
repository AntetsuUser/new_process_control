<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

class UpdatePrintCountCommand extends Command
{
    /**
     * コンソールコマンドの名前とシグネチャー
     *
     * @var string
     */
    protected $signature = 'print-count:update';  // コマンド名とシグネチャー

    /**
     * コンソールコマンドの説明
     *
     * @var string
     */
    protected $description = '月が変わった場合に印刷回数を更新';  // コマンドの説明

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
     * 月が変わった時に印刷回数を更新する処理
     *
     * @return void
     */
    public function handle()
    {
        $today = Carbon::today();  // 今日の日付を取得
        $currentMonth = $today->month;  // 現在の月を取得

        // データベースから前回の月を取得
        $lastRunMonth = Department::value('last_run_month');

        if ($lastRunMonth !== $currentMonth) {
            // 月が替わった場合
            Department::query()->update(['print_count' => 1]);  // 印刷回数を1にリセット

            // 最後に実行した月を更新
            Department::query()->update(['last_run_month' => $currentMonth]);

            // 成功メッセージを表示
            $this->info('印刷回数が更新され、最後に実行した月が更新されました。');
        } else {
            // 月が変更されていない場合のメッセージ
            $this->info('月は変更されていません。');
        }
    }
}
