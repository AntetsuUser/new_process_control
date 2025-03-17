<?php 

namespace App\Services\Login;
    
use App\Repositories\Login\LoginRepository;

    
class LoginService 
{
    // リポジトリクラスとの紐付け
    protected $_loginRepository;

    // phpのコンストラクタ
    public function __construct(LoginRepository $loginRepository)
    {
        $this->_loginRepository = $loginRepository;
    }
    //登録されている所属部署を取得する
    public function get_department()
    {
        return $this->_loginRepository->get_department();
    }
    //登録されている役職を取得する
    public function get_position()
    {
        return $this->_loginRepository->get_position();
    }
    //サインイン情報をデータベースに保存する
    public function handleSignup($name,$password,$all_departments_id,$positions_id)
    {
        $this->_loginRepository->handleSignup($name,$password,$all_departments_id,$positions_id);
    }
    
 

}