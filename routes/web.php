<?php

use Illuminate\Support\Facades\Route;
//uuid設定しようとしたときに設定
use Illuminate\Support\Str;
use App\Models\Tablets; // Tabletモデルをインポート
use Illuminate\Support\Facades\Cookie;
   
//ログインサインインにコントローラー
use App\Http\Controllers\LoginController;

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
//Logコントローラー
use App\Http\Controllers\LogController;
//サイネージコントローラー
use App\Http\Controllers\SignageController;

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
    // クッキーからUUIDを取得
    $uuid = request()->cookie('uuid');
    // dd($uuid);
    if (!$uuid) {
        // dd("uuidが登録されていません。システム担当に連絡してください");
        // UUIDが存在しない場合、新しく生成
        $uuid = (string) Str::uuid(); // UUIDを生成
        
        // クッキーに保存（有効期限は1年間）
        cookie()->queue(cookie('uuid', $uuid, 60 * 24 * 365)); // 1年

        // デバイスを特定してUUIDを更新
        $device = Tablets::where('tablet_number', "ANT-TAB-005")->first();

        if ($device) {
            // デバイスが見つかった場合はUUIDを更新
            $device->update(['uuid' => $uuid]);
            // dd($uuid);
        }
    }else{
        // UUIDが存在する場合、データベースで確認
        $device = Tablets::where('uuid', $uuid)->first();
        if ($device && $device->updated_at->lt(now()->subMonths(11))) {
            // 新しいUUIDを生成
            $newUuid = (string) Str::uuid();

            // データベースのUUIDを更新
            $device->update(['uuid' => $newUuid]);

            // Cookieにも新しいUUIDを保存
            cookie()->queue(cookie('uuid', $newUuid, 60 * 24 * 365)); // 1年
        }
    }
    return view('index');
});

//初回起動時にuser名を登録させる
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login_entry', [LoginController::class, 'login_entry'])->name('login_entry');
Route::get('/signup', [LoginController::class, 'signup'])->name('signup');
Route::post('/signup_entry', [LoginController::class, 'signup_entry'])->name('signup_entry');

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
    //材料入荷情報アップロード
    route::post('/material_upload','material_upload')->name('masta.material_upload');
    route::post('/vba_material_upload','receive_material_from_vba')->name('masta.receive_material_from_vba');
     //材料入荷情報、情報確認画面
    route::post('/material_up_history','material_up_history')->name('masta.material_up_history');




    //追加依頼処理
    route::post('/adding_request','adding_request')->name('masta.adding_request');
    //タブレット一覧POST
    route::post('/tablet_post','tablet_post')->name('masta.tablet_post');
    //タブレット一覧表示
    route::get('/tablet','tablet')->name('masta.tablet');
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
    //指示書を入力済みに
    route::post('/entered','entered')->name('history.entered');


});

//ログ関連
route::prefix('/log')->controller(LogController::class)->group(function()
{
    //logメニュー画面
    route::post('/main','main')->name('log.main');
    
});

//モニター関連

route::prefix('/signage')->controller(SignageController::class)->group(function()
{
    //サイネージメニュー画面
    route::get('/main','main')->name('signage.main');
    //タブレット進捗画面
    route::get('/tablet','tablet')->name('signage.tablet');
    //タブレットajaxのroute
    route::post('/ajax','ajax')->name('signage.ajax');
    route::get('/department','department')->name('signage.department');
});

// csrf確認用
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});