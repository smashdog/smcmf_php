<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证
 */
class Role extends Validate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require|checkTitle',
        'permission_ids' => 'require|checkPermission'
    ];

    protected $message = [
        'id.require' => '请选择要编辑的角色',
        'title.require' => '请填写角色名',
        'permission_ids.require' => '请选择权限'
    ];

    protected $scene = [
        'add' =>  ['title', 'permission_ids'],
        'edit' => ['id', 'title', 'permission_ids'],
        'del' => ['id']
    ];

    protected function checkTitle($value)
    {
        return \check_field_repeat('role', 'title', '角色名');
    }

    protected function checkPermission($value)
    {
        if(!is_array($value)){
            $value = [$value];
        }
        $permissionIds = (new \app\admin\model\Permission)->where('id', 'in', $value)->column('id');
        if(count($permissionIds) != count($value)){
            return '权限参数错误';
        }
        return true;
    }
}