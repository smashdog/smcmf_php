<?php
namespace app\index\controller;

use think\Controller;

class Login extends Controller
{
    /**
     * 验证码
     *
     * @return html
     */
    public function captcha()
    {
        $config = [
            'codeSet' => '0123456789',
            'length' => 6
        ];
        $captcha = new \think\captcha\Captcha($config);
        return $captcha->entry(); 
    }
}