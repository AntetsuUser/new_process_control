<?php

use Illuminate\Support\Facades\Route;

//マスタ管理コントローラー
use App\Http\Controllers\MastaController;
//履歴コントローラー
use App\Http\Controllers\HistoryController;
//負荷予測コントローラー
use App\Http\Controllers\LoadPredictionController;
//長期情報コントローラー
use App\Http\Controllers\LongInfoController;
//QR読取コントローラー
use App\Http\Controllers\QrReadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});


//負荷予測関連
route::prefix('/load_prediction')->controller(LoadPredictionController::class)->group(function()
{
    //負荷予測製造課選択画面
    route::get('/department_select','department_select')->name('load_prediction.department_select');
    //負荷予測製造課ごとの値処理
    route::post('/process','process')->name('load_prediction.process');
    //負荷予測設備ごとの負荷率画面
    route::get('/load_prediction_machine','load_prediction_machine')->name('load_prediction.load_prediction_machine');


});

//長期情報関連
route::prefix('/longinfo')->controller(LongInfoController::class)->group(function()
{
    //製造課選択画面
    route::get('/select','select')->name('longinfo.select');
    //長期情報画面POST処理
    route::post('/view_post','view_post')->name('longinfo.view_post');
    //長期画面表示するためのGET
    route::get('/view','view')->name('longinfo.view');

    //指示書印刷画面POST処理
    route::post('/print_post','print_post')->name('longinfo.print_post');
    //指示書印刷画面
    route::get('/print','print')->name('longinfo.print');

});

//QR読み取り関連
route::prefix('/qr')->controller(QrReadController::class)->group(function()
{
    //QRカメラ読取画面
    route::get('/qrcamera','qrcamera')->name('qr.qrcamera');
    //実績入力画面
    route::get('/input_directions','input_directions')->name('qr.input_directions');
    //入力完了画面
    route::post('/input_succes','input_succes')->name('qr.input_succes');;
    
});


//マスタ関連
route::prefix('/masta')->controller(MastaController::class)->group(function()
{
    //マスタ管理画面
    route::get('/', 'masta')->name('masta');

    //品目マスタ画面
    route::get('/number','number')->name('masta.number');
    //品目追加,更新画面
    route::get('/number_insert/{id?}','number_insert')->name('masta.number_insert');
    //品目確認画面
    route::post('/number_confirm','number_confirm')->name('masta.number_confirm');
    //品目登録・更新
    route::post('/number_store','number_store')->name('masta.number_store');
    //品目削除
    route::post('/number_delete','number_delete')->name('masta.number_delete');


    //設備マスタ画面
    route::get('/equipment', 'equipment')->name('masta.equipment');
    //設備編集画面
    route::get('/equipment_insert/{id?}','equipment_insert')->name('masta.equipment_insert');
    //設備編集確認画面
    route::post('/equipment_confirm','equipment_confirm')->name('masta.equipment_confirm');
    //設備登録・更新
    route::post('/equipment_store','equipment_store')->name('masta.equipment_store');
    //設備削除
    route::post('/equipment_delete','equipment_delete')->name('masta.equipment_delete');

    //作業者マスタ画面
    route::get('/worker', 'worker')->name('masta.worker');
    //作業者編集画面
    route::get('/worker_edit/{id?}','worker_edit')->name('masta.worker_edit');
    //作業者編集確認画面
    route::post('/worker_edit_confirm','worker_edit_confirm')->name('masta.worker_edit_confirm');
    //作業者登録・更新
    route::post('/worker_store','worker_store')->name('masta.worker_store');
    //作業者削除
    route::post('/worker_delete','worker_delete')->name('masta.worker_delete');

    //ストアマスタ画面
    route::get('/store','store')->name('masta.store');
    //ストア追加画面
    route::get('/store_edit/{id?}','store_edit')->name('masta.store_edit');
    //ストア確認画面
    route::post('/store_confirm','store_confirm')->name('masta.store_confirm');
    //ストア登録・更新
    route::post('/store_upsert','store_upsert')->name('masta.store_upsert');
    //ストア削除
    route::post('/store_delete','store_delete')->name('masta.store_delete');

    //カレンダー画面
    route::get('/calendar','calendar')->name('masta.calendar');
    //カレンダー登録・削除
    route::post('/calendar_update','calendar_update')->name('masta.calendar_update');

    //アップロード画面
    route::get('/upload','upload')->name('masta.upload');
    //長期情報アップロード
    route::post('/longinfo_upload','longinfo_upload')->name('masta.longinfo_upload');
    //出荷明細アップロード
    route::post('/shipping_upload','shipping_upload')->name('masta.shipping_upload');
    //出荷明細、情報確認画面
    route::get('/clearing_application','clearing_application')->name('masta.clearing_application');
    //出荷データ反映
    route::get('/shipment_application','shipment_application')->name('masta.shipment_application');
    //出荷反映データ履歴処理
    route::post('/application_history','application_history')->name('masta.application_history');
});

//履歴関連
route::prefix('/history')->controller(HistoryController::class)->group(function()
{
    //印刷履歴
    route::get('/print','print')->name('history.print');
    //入力履歴
    route::get('/processing','processing')->name('history.processing');
        //再印刷
    route::post('/reprint','reprint')->name('history.reprint');


});
