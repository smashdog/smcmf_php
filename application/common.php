<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('json_return')){
    /**
     * 通用json返回
     *
     * @param string $status
     * @param string $msg
     * @param array $data
     * @param array $other
     * @return json
     */
    function json_return($status = '10000', $msg = '操作成功', $data = [], $other = [])
    {
        $arr = [
            'status' => $status,
            'data' => $data,
            'msg' => $msg
        ];
        if($other){
            $arr = array_merge($arr, $other);
        }
        return json_encode($arr);
    }
}
if(!function_exists('json_redirect')){
    /**
     * 带跳转地址的json返回
     *
     * @param string $url
     * @return json
     */
    function json_redirect($url = '')
    {
        return json_return('10000', '', '', [
            'redirect' => $url ? $url : (!empty(request()->param['jumpUrl']) ? urldecode($request()->param['jumpUrl']) : '/')
        ]);
    }
}

if(!function_exists('get_path_info')){
    /**
     * 只有通过pathinfo才能获取到模型、控制器、方法
     *
     * @return array
     */
    function get_path_info($lower = true)
    {
        $temp = explode('/', request()->pathinfo());
        if(count($temp) == 1){
            $module = 'index';
            $controller = 'index';
            $action = 'index';
        }elseif(count($temp) == 2){
            $module = $lower ? strtolower($temp[0]) : $temp[0];
            $controller = $lower ? strtolower($temp[1]) : $temp[1];
            $action = 'index';
        }else{
            $module = $lower ? strtolower($temp[0]) : $temp[0];
            $controller = $lower ? strtolower($temp[1]) : $temp[1];
            $action = $lower ? strtolower($temp[2]) : $temp[2];
        }
        return [$module, $controller, $action];
    }
}

if(!function_exists('get_database_info')){
    /**
     * 根据模型和ID，过滤隐藏字段后返回
     * 
     * @param str $modelUrl
     * @param int $id
     * @param array $hidden
     * 
     * @return json
     */
    function get_admin_database_info($table, $id, $hidden = [])
    {
        if(!$id){
            return json_return('10001', '参数为空');
        }
        if(file_exists(\think\facade\App::getRootPath().'application/admin/logic/'.ucfirst($table).'.php')){
            try{
                $logicStr = '\\app\\admin\\logic\\'.ucfirst($table);
                $hidden = array_merge($hidden, (new $logicStr)->filterField());
            }catch(\Exception $e){

            }
        }
        $result = \think\Db::name($table)->where('id', $id)->field($hidden, true)->find();
        if(!$result){
            return json_return('10002', '参数错误');
        }
        if(is_object($result)){
            $result = $result->toArray();
        }
        return json_return('10000', '', $result);
    }
}

if(!function_exists('check_field_repeat')){
    /**
     * 判断对应字段的数据是否存在
     *
     * @param string $table
     * @param string $field
     * @param string $fieldName
     * @return string/boolean
     */
    function check_field_repeat($table = '', $field = '', $fieldName = '')
    {
        $where = [[$field, '=', request()->post($field)]];
        if(request()->post('id', null)){
            $where[] = ['id', '<>', request()->post('id')];
        }
        $r = \think\Db::name($table)->where($where)->find();
        return $r ? $fieldName.'已经存在' : true;
    }
}

if(!function_exists('check_permission'))
{
    /**
     * 权限验证
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    function checkPermission($module = '', $controller = '', $action = '')
    {
        if(USER['is_system']){
            return true;
        }
        if(!USER['role_ids']){
            return false;
        }
        $roles = (new \app\admin\model\Role)->where('id', 'in', json_decode(USER['role_ids'], true))->column('permission_ids');
        $temp = [];
        foreach($roles as $v){
            $temp = array_merge($temp, json_decode($v, true));
        }
        $permissions = (new \app\admin\model\Permission)->where('id', 'in', $temp)->select();
        foreach($permissions as $v){
            if($v['module'] == $module && $v['controller'] == $controller && $v['action'] == $action){
                return true;
            }
        }
        return false;
    }
}