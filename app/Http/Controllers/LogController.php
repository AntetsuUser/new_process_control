<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use App\Logging\CustomLogFormatter;
class LogController extends Controller
{
    public function main(Request $request)
    {
        // 日本時間を設定
        date_default_timezone_set('Asia/Tokyo');

        // URLとIPアドレスを取得
        $url = (string)$request->key;
        $local_ip = $request->local_ip;
        $btn_titl = $request->text;
        // ファイル名に使えるようにIPアドレスの`.`を`_`に置換
        $ipAddress = str_replace('.', '_', $local_ip);

        // IPアドレスのログファイルを確認し、なければ作成
        $this->checkAndCreateLogFile($ipAddress, $local_ip);

        // URLに基づいたログメッセージを設定

            // IPアドレスのログファイルにメッセージを書き込み
            $this->writeToLogFile($ipAddress, $btn_titl.'ボタンがクリックされました。');

        // return $local_ip;
    }

    /**
     * IPアドレスのログファイルが存在するか確認し、なければ作成する
     *
     * @param string $ipAddress
     * @param string $ip
     * @return void
     */
    private function checkAndCreateLogFile($ipAddress, $ip)
    {
        // ログファイルのパスを定義
        $logFilePath = storage_path('logs/log_' . $ipAddress . '.log');

        // ログファイルが存在するか確認
        if (!File::exists($logFilePath)) {
            // ログファイルがなければ作成
            $logger = new Logger('ip_logger');
            $logger->pushHandler(new StreamHandler($logFilePath, Logger::INFO));
            // カスタムフォーマッタを適用
            $streamHandler->setFormatter(new \App\Logging\CustomLogFormatter());
            $logger->pushHandler($streamHandler);


            // ログにメッセージを記録
            $logger->info('New log created for IP address: ' . $ip);
        }
    }

    /**
     * 指定されたIPアドレスのログファイルにメッセージを書き込む
     *
     * @param string $ipAddress
     * @param string $message
     * @return void
     */
    private function writeToLogFile($ipAddress, $message)
    {
        $logFilePath = storage_path('logs/log_' . $ipAddress . '.log');

        $logger = new Logger('ip_logger');
        $streamHandler = new StreamHandler($logFilePath, Logger::INFO);
        
        // カスタムフォーマッタを適用
        $streamHandler->setFormatter(new \App\Logging\CustomLogFormatter());
        $logger->pushHandler($streamHandler);

        $logger->info($message);
    }
}
