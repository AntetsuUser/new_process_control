<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class CustomLogFormatter extends LineFormatter
{
    public function __construct()
    {
        // タイムスタンプのフォーマットを指定
        $dateFormat = "Y-m-d H:i:s";
        parent::__construct(null, $dateFormat, true, true);
    }

    // フォーマットするメッセージをカスタマイズ
    public function format(LogRecord $record): string
    {
        // ここでカスタマイズを実装することが可能
        return parent::format($record);
    }
}
