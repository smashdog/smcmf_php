<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证
 */
class Menu extends Validate
{
    protected $rule = [
        'id' => 'require',
    ];

    protected $message = [
        'id.require' => 'ID为空',
    ];

    protected $scene = [
        'del' => ['id'],
    ];
}