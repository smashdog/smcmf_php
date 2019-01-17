<?php
namespace app\admin\logic;

use app\admin\model\Menu as menuModel;

class Menu
{
    /**
     * 获取用户的菜单
     *
     * @return json
     */
    public function getUserMenuList()
    {
        $menuModel = new menuModel;
        if(USER['is_system'] == 1){
            $menu = $menuModel->order(['id', 'sort'])->all();
            $menu = $menu->toArray();
        }else{
            $roles = (new \app\admin\model\Role)->where('id', 'in', json_decode(USER['role_ids'], true))->column('permission_ids');
            $temp = [];
            foreach($roles as $v){
                $temp = array_merge($temp, json_decode($v, true));
            }
            if(!$temp){
                return json_return('10000', '', []);
            }
            $menu = $menuModel->where('id', 'in', $temp)->order(['id', 'sort'])->select();
            if(!$menu){
                return json_return('10000', '', []);
            }
        }
        $temp = [];
        foreach($menu as $v){
            if($v['pid'] == 0){
                $temp[$v['id']] = $v;
            }
        }
        foreach($menu as $v){
            if($v['pid'] != 0){
                if(empty($temp[$v['pid']]['sub'])){
                    $temp[$v['pid']]['sub'] = [];
                }
                $temp[$v['pid']]['sub'][$v['id']] = $v;
            }
        }
        return json_return('10000', '', $temp);
    }

    /**
     * 添加/编辑时过滤字段
     *
     * @return array
     */
    public function filterField()
    {
        return ['url'];
    }

    /**
     * 添加/编辑时特殊字段处理
     *
     * @param array $param
     * @param array $save
     * @return array
     */
    public function special($param = [], $save = [])
    {
        $save['url'] = '#';
        if(!empty($param['permission_id'])){
            $permission = \think\Db::name('permission')->where('id', $param['permission_id'])->find();
            $save['url'] = $permission['controller'].'/'.$permission['action'];
        }
        return $save;
    }
}