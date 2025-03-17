<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use App\Logging\CustomLogFormatter;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    //ホーム画面のaタグが押されたとき


    //////////////////////////////////////////////////////////////////
    //AJAX
    //////////////////////////////////////////////////////////////////
    public function atag(Request $request)
    {

       $user = Auth::user();

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");

        // 呼び出し元の情報を取得
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';

        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("{$request->text}ボタンを押しました。", [
            'ボタン' => $request->text, // リクエストの詳細も記録
            'ファイル' => $file,
            '行番号' => $line,
        ]);


        return response()->json(['message' => $request->text]);
    }
    //----------------------------------------------------------------
    //長期情報表示、指示書
    //----------------------------------------------------------------
    public function select(Request $request)
    {
        $user = Auth::user();

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");
        // 呼び出し元の情報を取得
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';

        if($request->select_text == "作業者")
        {
             // ログを日本語で記録
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("作業者「{$request->selected_text}」が選択されました", [
                $request->select_text => $request->selected_text, // リクエストの詳細も記録
                'ファイル' => $file,
                '行番号' => $line,
            ]);
            
        }else
        {   
             // ログを日本語で記録
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("「{$request->selected_text}」{$request->select_text}が選択されました", [
                $request->select_text => $request->selected_text, // リクエストの詳細も記録
                'ファイル' => $file,
                '行番号' => $line,
            ]);

        }
        return response()->json(['message' => $request->selected_text]);
    }
    public function submit(Request $request)
    {
        $user = Auth::user();
         // 呼び出し元の情報を取得
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");
        
        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("{$request->data}ボタンが押されました", [
            // $request->select_text => $request->selected_text, // リクエストの詳細も記録
            'ファイル' => $file,
            '行番号' => $line,
        ]);

        
        return response()->json(['message' => $request->data]);
    }
    //modal
    public function modal(Request $request)
    {
        $user = Auth::user();

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");
        // 呼び出し元の情報を取得
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
        
        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("{$request->modal_location}モーダルが{$request->status}", [
            "モーダル場所" => $request->modal_location, // リクエストの詳細も記録
            "状態" => $request->status, // リクエストの詳細も記録
            'ファイル' => $file,
            '行番号' => $line,
        ]);

        
        return response()->json(['message' => $request]);
    }

    //selectedCellLog
    public function selectedCellLog(Request $request)
    {
        $user = Auth::user();

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
        
        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("選択した内容「品番:{$request->item_code}」「工程:{$request->secondColumnText}」「納期:{$request->delivery_date}」「長期数量:{$request->cellText}」「ロット数:{$request->maxlot}」");
        return response()->json(['message' => $request]);
    }
    //maxボタンを押したときの数量を残す
    public function maxbtn(Request $request)
    {
        $user = Auth::user();

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
        
        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("Maxで「{$request->max_count}」個入力しました。");
        return response()->json(['message' => $request]);
    }
    
    //----------------------------------------------------------------
    //QR、実績入力
    //----------------------------------------------------------------
    public function camera(Request $request)
    {
        $user = Auth::user();

        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$user->name}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
         

        if ($request->location == "カメラ起動")
        {
            //カメラの起動
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("カメラの起動に「{$request->status}」しました。");
            
           return response()->json(['message' => 'カメラのログ送信成功']);
        }elseif($request->location == "QR読み取り"){
            //読み取れたかどうか
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("QRコード読み取りに「{$request->status}」しました。");
             return response()->json(['message' => 'カメラのログ送信成功']);
        }elseif($request->location == "QRコードデータ"){
            //読み取れたデータ
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("QRコードデータ:「ID={$request->status}」");
             return response()->json(['message' => 'カメラのログ送信成功']);
        }elseif($request->location == "QR読み取り結果"){
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("QR読み取り結果画面が表示されました。QRコードデータ:「ID={$request->status}」");
             return response()->json(['message' => 'カメラのログ送信成功']);
        }elseif($request->location == "QR"){
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("QR読取結果の「{$request->status}」ボタンが押されました");
             return response()->json(['message' => 'カメラのログ送信成功']);
        }
        elseif($request->location == "ヘルプ"){
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("QRID入力画面の「{$request->status}」ボタンが押されました");
             return response()->json(['message' => 'カメラのログ送信成功']);
        }
        elseif($request->location == "読み込めない時は"){
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("「QRが読み込めない時は」ボタンが押されました");
             return response()->json(['message' => 'カメラのログ送信成功']);
        }
        elseif($request->location == "実績入力不正"){
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("入力した値が不正です。値");
             return response()->json(['message' => '実績のログ送信成功']);
        }
        elseif($request->location == "実績モーダル"){
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'info',
            ])->info("実績入力確認画面が「{$request->status}」");
             return response()->json(['message' => '実績のログ送信成功']);
        }

        //
    }


    //////////////////////////////////////////////////////////////////
    //AJAXじゃない
    //////////////////////////////////////////////////////////////////
    //// ログファイルを作成してアクションを記録
    public function logAction($userName, $actionDetails)
    {
        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$userName}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';

        // ログファイルが存在しない場合、作成する
        if (!File::exists($logFile)) {
            // ログファイルを作成
            File::put($logFile, ""); // 空のファイルを作成
        }

        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("ユーザー {$userName} がログインしました", [
            '詳細' => $actionDetails, // リクエストの詳細も記録
            'ファイル' => $file,
            '行番号' => $line,
        ]);
    }

    //ページ表示のlog
    public function page_log($userName,$pagename)
    {
        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$userName}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("{$pagename}画面を表示しました。", [
            '画面' => $pagename, // リクエストの詳細も記録
            'ファイル' => $file,
            '行番号' => $line,
        ]);
    }
    //ページデータのlog
    public function page_data($userName,$pagename,$data)
    {
        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$userName}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';

        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("{$pagename}画面のデータ", [
            '表示データ' => $data, // リクエストの詳細も記録
            'ファイル' => $file,
            '行番号' => $line,
        ]);
    }

    public function showing_data($itemname, $process,$userName,$pagename)
    {
        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$userName}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
        $process_str = implode(',', $process);
        // ログを日本語で記録
        Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
        ])->info("{$pagename}画面の表示データ", [
            "品番:{$itemname}" => "工程:{$process_str}",
            'ファイル' => $file,
            '行番号' => $line,
        ]);
    }

    //指示書のデータのlog
    public function directions_data($print_arr,$factory,$department,$userName)
    {
        // ユーザー名を使ってログファイルのパスを指定
        $logFile = storage_path("logs/{$userName}.log");

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $trace['file'] ?? '不明なファイル';
        $line = $trace['line'] ?? '不明な行番号';
        //以下の順番でlogにする　
        //指示書ID、品名、品番、納期、加工数、着手日、工程、設備、作業者
        // ログを日本語で記録
        foreach ($print_arr as $key => $value) {
            // dd($value);
            Log::build([
            'driver' => 'single',
            'path' => $logFile,
            'level' => 'info',
           ])->info("選択した内容「指示書ID:{$print_arr[$key]["characteristic_id"]}」「品名:{$print_arr[$key]["item_name"]}」「加工品番:{$print_arr[$key]["processing_item"]}」「納期:{$print_arr[$key]["delivery_date"]}」「加工数:{$print_arr[$key]["processing_quantity"]}」「着手日:{$print_arr[$key]["start_date"]}」「工程:{$print_arr[$key]["process"]}」「設備:{$print_arr[$key]["workcenter"]}」「作業者:{$print_arr[$key]["woker_id"]}」", [
                'ファイル' => $file,
                '行番号' => $line,
            ]);
        }
    }
}
