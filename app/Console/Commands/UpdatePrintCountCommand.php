<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

class UpdatePrintCountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */

    protected $signature = 'print-count:update';
    protected $description = 'Update print_count if month has changed';

    public function __construct()
    {
        parent::__construct();
    }
    //月が変わった時に印刷回数を更新する処理
    public function handle()
    {
        $today = Carbon::today();
        $currentMonth = $today->month;

        // データベースから前回の月を取得
        $lastRunMonth = Department::value('last_run_month');

        if ($lastRunMonth !== $currentMonth) {
            // 月が替わった場合
            Department::query()->update(['print_count' => 1]);

            Department::update(['last_run_month' => $currentMonth]);

            $this->info('print_count updated and last_run_month updated.');
        } else {
            $this->info('Month has not changed.');
        }
    }
}
