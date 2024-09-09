<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\History\PrintHistoryService;
use App\Services\History\ProcessedHistoryService;
//Logを残すのに必要
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    // サービスクラスとの紐付け
    protected $_printHistoyService;
    protected $_processedHistoryService;

    public function __construct(PrintHistoryService $printHistoyService, ProcessedHistoryService $processedHistoryService)
    {
        $this->_printHistoyService = $printHistoyService;
        $this->_processedHistoryService = $processedHistoryService;
    }
    //印刷履歴画面
    public function print()
    {
        $print_history = $this->_printHistoyService->print_history_get();
        Log::channel('process_log')->info('印刷画面表示');
        return view('history.print_history',compact('print_history'));
    }

    //入力履歴画面
    public function processing()
    {
        $processed_history = $this->_processedHistoryService->processed_history_get();  
        Log::channel('process_log')->info('入力画面表示');
        return view('history.processing_history',compact('processed_history'));
    }
    public function reprint(Request $request)
    {
        $id = $request->id;

        $print_history = $this->_printHistoyService->reprint($id);
        // dd($print_history);
        Log::channel('process_log')->info('指示書再表示');
        return view('history.reprint',compact('print_history'));
    }
}
