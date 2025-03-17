<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Login\LoginRequest;
use App\Services\Login\LoginService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User_info;
//logcontroller
use App\Http\Controllers\LogController;



class LoginController extends Controller
{
    protected $_loginService;
    public function __construct(LoginService $loginService)
    {
        $this->_loginService = $loginService;
    }

    //ログイン画面
    public function login(Request $request)
    {

        // 全てのクッキーを取得
        $cookies = request()->cookies->all();
        // dd($cookies);
        $username = $cookies["username"];
        $password = $cookies["pa"];
        // dd($cookies);2
        return view('login',compact('username','password'));
    }
    //ログイン認証
    public function login_entry(Request $request)
    {
        // 入力値のバリデーション
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        // dd($request->password);
        // ユーザー名からユーザーを検索
        $user = User_info::where('name', $validated['username'])->first();

        // ユーザーが見つからなかった場合
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            // ログイン失敗時のメッセージ
            return back()->withInput()->withErrors([
                'username' => 'ユーザー名またはパスワードが違います。',
            ]);
        }

        // ユーザー名とパスワードが一致した場合、ログイン
        Auth::login($user);

        //$user->nameでログファイルを作成
        $logController = new LogController();
        $logController->logAction($user->name, 'ログイン成功'); // アクションの詳細は任意で設定

        // 現在日時から1年間の有効期限を計算
        $expirationDate = Carbon::now()->addYear()->timestamp;

        // クッキーにユーザー名と有効期限を保存
        $cookieUsername = cookie('username', $user->name, 525600); // 525600分 = 1年
        $cookieExpiration = cookie('expiration', $expirationDate, 525600); // タイムスタンプ形式
        $pa = cookie('pa', $request->password, 525600); // 10080分 = 7日

        // ログイン後、リダイレクト
        return redirect()->intended('/')->withCookie($cookieUsername)->withCookie($cookieExpiration)->withCookie($pa);
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout(); // ログアウト処理
        $request->session()->invalidate(); // セッションを無効にする
        $request->session()->regenerateToken(); // CSRFトークンを再生成

        return redirect()->route('login');
    }


    //初回起動のページを表示させる
    public function signup(Request $request)
    {
        //所属と役職の情報を取得してくる
        $department = $this->_loginService->get_department();
        $position = $this->_loginService->get_position();

        // dd($department,$position);
        return view('signup',compact('department','position'));
    }
    //サインイン処理
    public function signup_entry(LoginRequest $request)
    {   
        //名前、パスワード、所属、役職をPOSTで受け取る
        $name = $request->name;
        $password = $request->password;
        $all_departments_id = $request->all_departments_id;
        $positions_id = $request->positions_id;
        // dd($all_departments_id);

        //データベースに保存する
        $this->_loginService->handleSignup($name,$password,$all_departments_id,$positions_id);

        return redirect()->route('login');
    }
}
