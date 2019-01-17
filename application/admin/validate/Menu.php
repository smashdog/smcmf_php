<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 用户验证
 */
class Menu extends Validate
{
    protected $rule = [
        'title' => 'require',
        'pid' => 'checkPid'
    ];

    protected $message = [
        'title.require' => '请填写菜单名'
    ];

    protected $scene = [
        'add' =>  ['title', 'pid'],
        'edit' => ['title', 'pid'],
        'del' => ['id']
    ];
    
    protected function checkPid($value, $rule, $data){
        if($value){
            if(!(new \app\admin\model\Menu)->get($value)){
                return '父ID错误';
            }
            if(empty($data['permission_id'])){
                return '子菜单必须选择一个权限';
            }
            if(!$permission = (new \app\admin\model\Permission)->get($data['permission_id'])){
                return '权限参数错误';
            }
        }
        return true;
    }
}