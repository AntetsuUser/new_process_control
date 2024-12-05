<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Login\LoginService;

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

        return view('login');
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
    public function signup_entry(Request $request)
    {
        
    }
}
