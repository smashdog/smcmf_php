<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证
 */
class Login extends Validate
{
    protected $rule = [
        'password' => 'require',
        'username' => 'require',
        'captcha' => 'require|checkCaptcha'
    ];

    protected $message = [
        'password.require' => '请填写密码',
        'username.require' => '请填写用户名',
        'captcha.require' => '请填写图形验证码'
    ];

    protected $scene = [
        'login' =>  ['password', 'username', 'captcha']
    ];

    protected function checkCaptcha($value)
    {
        return captcha_check($value) ? true : '验证码错误';
    }
}