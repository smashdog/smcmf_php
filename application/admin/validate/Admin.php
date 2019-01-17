<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证
 */
class Admin extends Validate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'role_ids' => 'require|checkRoleIds',
        'status' => 'require',
    ];

    protected $message = [
        'username.require' => '请填写用户名',
        'password.require' => '请填写密码',
        'password.role_ids' => '请选择角色',
        'password.status' => '请选择用户状态',
    ];

    protected $scene = [
        'add' =>  ['password', 'username', 'status'],
        'edit' =>  ['username', 'status'],
    ];

    protected function checkRoleIds($value)
    {
        if(!is_array($value)){
            $value = [$value];
        }
        if(count($value) != count(\think\Db::name('admin')->where('id', 'in', $value)->column('id'))){
            return '角色选择错误';
        }
        return true;
    }
}