<?php
namespace app\admin\controller;

use app\admin\controller\Init;

class Error extends Init
{
    public function _empty()
    {
        list($module, $controller, $action) = get_path_info(false);
        if(count(\think\Db::query('desc '.config('database.prefix').$controller)) == 0){
            return \json_return('40004', '404 not found');
        }
        if(\preg_match('/^(\w+)List$/', $action)){//列表
            return (new \app\admin\logic\Common)->commonList( $controller);
        }
        if(\preg_match('/^(\w+)Add$/', $action)){//添加/编辑
            if($this->request->isPost()){
                return (new \app\admin\logic\Common)->commonAdd($controller);
            }else{
                return (new \app\admin\logic\Common)->commonAddField($controller);
            }
        }
        if(\preg_match('/^(\w+)Del$/', $action)){//删除
            return (new \app\admin\logic\Common)->commonDel($controller);
        }
        if(\preg_match('/^(\w+)Edit$/', $action)){//编辑页获取数据
            return get_admin_database_info($controller, $this->request->post('id'), ['id', 'password']);
        }
        return \json_return('40004', '404 not found');
    }
}