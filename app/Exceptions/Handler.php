<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception): void
    {
        if ($this->shouldReport($exception)) {
            $this->logException($exception); // ログ処理を分離
        }

        parent::report($exception);
    }

    private function logException(Throwable $exception): void
    {
        $user = Auth::user();

        // ユーザーごとのログファイル設定
        if ($user) {
            $logFile = storage_path("logs/{$user->name}.log");
            // スタックトレースを取得
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $file = $backtrace[0]['file'];
            $line = $backtrace[0]['line'];
            Log::build([
                'driver' => 'single',
                'path' => $logFile,
                'level' => 'error',
            ])->error('エラーが発生しました: ' . $exception->getMessage(), [
                'exception' => get_class($exception),
                'user_name' => $user->name,
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'file' => $file,  // 発生元のファイル名
                'line' => $line,  // 発生元の行番号
            ]);
        } else {
            Log::error('エラーが発生しました: ' . $exception->getMessage(), [
                'exception' => get_class($exception),
                'user_name' => 'ログインしていないユーザー',
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
            ]);
        }
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'message' => '指定されたデータが見つかりませんでした。',
            ], 404);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => '認証に失敗しました。ログインしてください。',
            ], 401);
        }

        return parent::render($request, $exception);
    }
}
