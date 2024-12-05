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

    public function get_department()
    {
        return $this->_loginRepository->get_department();
    }

    public function get_position()
    {
        return $this->_loginRepository->get_position();
    }
    
 

}