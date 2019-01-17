<?php
namespace app\admin\controller;

use think\Controller;

class Init extends Controller
{
    protected function initialize()
    {
        $lu = new \app\admin\logic\Login;
        $admin = $lu->isLogin(request()->post(), true);
        if($admin){
            if(is_object($admin)){
                $admin = $admin->toArray();
            }
            if(!defined('USER'))
            define('USER', $admin);
        }
        list($module, $controller, $action) = get_path_info();
        //common这个不允许外部访问
        if($module == 'common'){
            exit(json_return('10001', '别乱访问哦兄弟'));
        }

        //是否需要登录
        if(!$admin && $controller != 'login'){
            exit(json_return('30001', '请先登录', '', ['redirect' => '/login']));
        }
    }
}