<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证
 */
class Permission extends Validate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require',
        'module' => 'require',
        'controller' => 'require',
        'action' => 'require'
    ];

    protected $message = [
        'id.require' => '请选择要编辑的权限',
        'title.require' => '请填写权限标题',
        'module.require' => '请填写模型',
        'controller.require' => '请填控制器',
        'action.require' => '请填方法',
    ];

    protected $scene = [
        'add' =>  ['title', 'module', 'controller', 'action'],
        'edit' => ['id', 'title', 'module', 'controller', 'action'],
        'del' => ['id']
    ];
}