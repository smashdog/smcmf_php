<?php
namespace app\admin\logic;

use think\facade\Cache;
use app\admin\validate\Login as loginValidate;
use app\admin\model\Admin as adminModel;

class Login
{
    /**
     * 是否登录
     *
     * @return json/boolean
     */
    public function isLogin($param = [], $return = false)
    {
        if(empty($param['token'])){
            $header = request()->header();
            if(isset($header['token'])){
                $param['token'] = $header['token'];
            }else{
                return !$return ? json_return('10001', '未提交token') : false;
            }
        }
        $r = Cache::get($param['token']);
        if(!$r){//未找到对应token
            return !$return ? json_return('10001', '登录过期') : false;
        }
        Cache::set($param['token'], $r, config('session.expire'));
        return !$return ? json_return('10000', '', ['token' => $param['token']]) : $r;
    }
    /**
     * 后台登录
     *
     * @param array $param
     * @return json
     */
    public function login($param = [])
    {
        $validate = new loginValidate;
        if(!$validate->scene('login')->check($param)){
            return json_return('10001', $validate->getError());
        }

        $adminModel = new adminModel;
        $admin = $adminModel->where([
            ['username', '=', $param['username']],
            ['password', '=', md5($param['password'])]
        ])->find();
        if(!$admin){
            return json_return('10002', '用户名或密码错误');
        }
        if($admin['status'] != 1){
            return json_return('10003', '用户状态异常');
        }
        if(is_object($admin)){
            $admin = $admin->toArray();
        }
        $token = md5(dk_get_dt_id());
        //盐度更新
        // $admin['salt'] = md5(dk_get_dt_id());
        // $adminModel->where('id', $admin)->update([
        //     'salt' => $admin['salt']
        // ]);
        if(!empty($admin['role_ids'])){
            $admin['role_ids'] = json_decode($admin['role_ids'], true);
        }
        Cache::set($token, $admin, config('session.expire'));
        (new \app\common\logic\Log)->createLog($admin['id'], 'admin');//登录日志
        return json_return('10000', '登录成功', ['token' => $token, 'username' => $admin['username']]);
    }
}