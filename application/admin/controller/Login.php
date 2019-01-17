<?php
namespace app\admin\controller;

use think\facade\Cache;
use think\Controller;

class Login extends Controller
{
    private $loginLogic;

    protected function initialize()
    {
        $this->loginLogic = new \app\admin\logic\Login;
    }
    /**
     * 登录
     *
     * @return json
     */
    public function index()
    {
        return $this->loginLogic->login($this->request->post());
    }

    /**
     * 判断是否登录
     *
     * @return json
     */
    public function isLogin()
    {
        return $this->loginLogic->isLogin($this->request->post());
    }
}