<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\History\PrintHistoryService;
use App\Services\History\ProcessedHistoryService;
// Logを残すのに必要
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    // サービスクラスとの紐付け
    protected $_printHistoyService;
    protected $_processedHistoryService;

    /**
     * コンストラクタ
     * 
     * @param PrintHistoryService $printHistoyService
     * @param ProcessedHistoryService $processedHistoryService
     */
    public function __construct(PrintHistoryService $printHistoyService, ProcessedHistoryService $processedHistoryService)
    {
        $this->_printHistoyService = $printHistoyService;  // 印刷履歴サービスのインスタンスを設定
        $this->_processedHistoryService = $processedHistoryService;  // 処理済み履歴サービスのインスタンスを設定
    }

    /**
     * 印刷履歴画面を表示
     */
    public function print()
    {
        $print_history = $this->_printHistoyService->print_history_get();  // 印刷履歴を取得
        Log::channel('process_log')->info('印刷画面表示');  // ログに情報を記録
        return view('history.print_history',compact('print_history'));  // 印刷履歴画面を表示
    }

    /**
     * 入力履歴画面を表示
     */
    public function processing()
    {
        $processed_history = $this->_processedHistoryService->processed_history_get();  // 入力履歴を取得
        Log::channel('process_log')->info('入力画面表示');  // ログに情報を記録
        return view('history.processing_history',compact('processed_history'));  // 入力履歴画面を表示
    }

    /**
     * 指示書の再印刷画面を表示
     * 
     * @param Request $request
     */
    public function reprint(Request $request)
    {
        $id = $request->id;  // リクエストから指示書IDを取得

        $print_history = $this->_printHistoyService->reprint($id);  // 指示書IDに基づいて再印刷情報を取得
        Log::channel('process_log')->info('指示書再表示');  // ログに情報を記録
        return view('history.reprint',compact('print_history'));  // 再印刷画面を表示
    }

    /**
     * 指示書を入力済みに変更
     * 
     * @param Request $request
     */
    public function entered(Request $request)
    {
        // 指示書IDを取得
        $directions_id = $request->directions_id;

        // 指示書IDに基づいて作業フラグを「入力済み」に更新
        $this->_printHistoyService->entered($directions_id);
        
        // 成功メッセージと共に印刷履歴画面にリダイレクト
        return redirect()->route('history.print')->with('status', '指示書が反映されました');
    }
}
