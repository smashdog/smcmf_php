<?php
namespace app\admin\logic;

use think\facade\Request;

class Common
{
    /**
     * 通用列表
     *
     * @param string $table
     * @param integer $per_page
     * @return json
     */
    public function commonList($table = '', $per_page = 10)
    {
        $param = Request::post();
        $where = [];
        if(!empty($param['search'])){
            $search = json_deocde(urldecode($param['search']), true);
            $fields = $this->getTableFields($table);
            foreach($search as $k => $v){
                if(\in_array($k, $fields)){
                    if(preg_match('/\d+/', $v)){
                        $where[] = [$k, '=', $v];
                    }else{
                        $where[] = [$k, 'like', "%{$v}%"];
                    }
                }
            }
        }
        $logicStr = '\\app\\admin\\logic\\'.ucfirst($table);
        $fieldsAll = $this->getTableFields($table, true);
        $filter = ['desc'];
        if($table == 'admin' || $table == 'user'){
            $filter[] = 'password';
        }
        if(file_exists(\think\facade\App::getRootPath().'application/admin/logic/'.ucfirst($table).'.php')){
            try{//尝试过滤字段
                $filter = array_merge($filter, (new $logicStr)->filterField());
            }catch(\Exception $e){

            }
        }
        $fields = [];
        $filedsTemp = [];
        foreach($fieldsAll as $v){
            if($v['Field'] == 'id'){
                $fields[] = [
                    'title' => '序号',
                    'key' => 'id',
                    'type' => 'selection',
                    'maxWidth' => 60
                ];
            }elseif(!in_array($v['Field'], $filter)){
                $fields[] = [
                    'title' => $v['Comment'],
                    'key' => $v['Field']
                ];
            }
            $filedsTemp[$v['Field']] = $v;
        }
        $list = $where
        ? \think\Db::name($table)->where($where)->field($filter, true)->order('id desc')->paginate($per_page)
        : \think\Db::name($table)->field($filter, true)->order('id', 'desc')->paginate($per_page);
        if(is_object($list)){
            $list = $list->toArray();
        }
        foreach($list['data'] as $k => $v){
            foreach($v as $k1 => $v1){
                if($k1 == 'pid'){//父ID
                    if($v1 != 0){
                        $temp = \think\Db::name($table)->where('id', '=', $v1)->find()['title'];
                    }else{
                        $temp = '无';
                    }
                    $list['data'][$k][$k1] = $temp;
                }elseif(preg_match('/^(\w+)_ids$/', $k1, $temp) && $v1){//数据集合
                    $temp1 = '';
                    $temp2 = \think\Db::name($temp[1])->where('id', 'in', json_decode($v1, true))->select();
                    foreach($temp2 as $v2){
                        $temp1 .= ($temp1 ? '、' : '').$v2['title'];
                    }
                    $list['data'][$k][$k1] = $temp1;
                }elseif(preg_match('/^(\w+)_id$/', $k1, $temp) && $v1){//单个关联数据
                    $list['data'][$k][$k1] = \think\Db::name($temp[1])->where('id', '=', $v1)->find()[$temp[1] == 'user' ? 'nickname' : 'title'];
                }elseif(preg_match('/tinyint/', $filedsTemp[$k1]['Type'])){
                    $temp = explode('；', explode('：', $filedsTemp[$k1]['Comment'])[1]);
                    foreach($temp as $v2){
                        $temp1 = explode('、', $v2);
                        if($temp1[0] == $v1){
                            $list['data'][$k][$k1] = $temp1[1];
                            break;
                        }
                    }
                }elseif(in_array($k1, ['create_time', 'update_time'])){
                    $list['data'][$k][$k1] = $v1 ? date('Y-m-d H:i:s', $v1) : '';
                }
            }
        }
        $data = $list['data'];
        unset($list['data']);
        list($module, $controller, $action) = get_path_info();
        $list['button'] = [];
        if(checkPermission($module, $controller, $controller.'Add')){
            $list['button'][] = 'edit';
        }
        if(checkPermission($module, $controller, $controller.'Del')){
            $list['button'][] = 'del';
        }
        $list['formTitle'] = $this->getTableTitle($table);
        $list['fields'] = $fields;
        return json_return('10000', '', $data, $list);
    }

    /**
     * 通用添加/编辑功能
     *
     * @param string $table
     * @return json
     */
    public function commonAddField($table = '')
    {
        $logicStr = '\\app\\admin\\logic\\'.ucfirst($table);
        $result = $this->getTableFields($table, true);
        $filter = ['create_time', 'update_time'];
        $r = '';
        if(input('?get.id')){
            $r = \think\Db::name($table)->where('id', '=', input('get.id'))->find();
        }
        $root = \think\facade\App::getRootPath();
        if(file_exists($root.'application/admin/logic/'.ucfirst($table).'.php')){
            try{
                $filter = (new $logicStr)->filterField();
            }catch(\Exception $e){
    
            }
        }
        $arr = [];
        foreach($result as $v){
            if(!in_array($v['Field'], $filter) && $v['Field'] != 'id'){
                $arr[$v['Field']] = [
                    'title' => $v['Comment']
                ];
                if($v['Field'] == 'pid'){//父ID，目前只支持1级
                    $arr[$v['Field']]['type'] = 'select';
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                    $temp = \think\Db::name($table)->where('pid', '=', 0)->field('id, title')->select();
                    $arr[$v['Field']]['list'] = [
                        0 => [
                            'id' => 0,
                            'title' => '无'
                        ]
                    ];
                    foreach($temp as $v1){
                        $arr[$v['Field']]['list'][] = $v1;
                    }
                }elseif($v['Field'] == 'password'){
                    $arr[$v['Field']]['type'] = 'password';
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                }elseif($v['Field'] == 'icon'){//图标
                    $arr[$v['Field']]['type'] = 'select';
                    $arr[$v['Field']]['value'] = '';
                    $arr[$v['Field']]['list'] = [];
                }elseif(preg_match('/_img$/', $v['Field'])){//单图
                    $arr[$v['Field']]['type'] = 'img';
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                }elseif(preg_match('/tinyint/', $v['Type'])){
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                    $temp = explode('；', explode('：', $v['Comment'])[1]);
                    $arr[$v['Field']]['type'] = 'radio';
                    $arr[$v['Field']]['list'] = [];
                    foreach($temp as $v1){
                        $temp1 = explode('、', $v1);
                        $arr[$v['Field']]['list'][] = [
                            'title' => $temp1[1],
                            'id' => $temp1[0]
                        ];
                    }
                }elseif(preg_match('/varchar\((\d+)\)/', $v['Type'], $temp)){
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                    if(intval($temp[1]) <= 255){
                        $arr[$v['Field']]['type'] = 'text';
                    }else{
                        $arr[$v['Field']]['type'] = 'textarea';
                    }
                }elseif(preg_match('/char\(10\)/', $v['Type']) && preg_match('/日期/', $v['Comment'])){//日期处理
                    $arr[$v['Field']]['type'] = 'date';
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                }elseif(preg_match('/double/', $v['Type']) || preg_match('/float/', $v['Type']) || preg_match('/decimal/', $v['Type'])){
                    $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                    $arr[$v['Field']]['type'] = 'number';
                    $arr[$v['Field']]['step'] = 0.01;
                }elseif(preg_match('/int/', $v['Type'])){
                    if(preg_match('/_id$/', $v['Field'])){//以ID结束的字段表示是关联数据
                        $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                        $arr[$v['Field']]['type'] = 'select';
                        $field = 'id, title';
                        if(str_replace('_id', '', $v['Field']) == 'user'){//用户表特殊处理
                            $field = 'id, nickname as title';
                        }
                        $temp = \think\Db::name(str_replace('_id', '', $v['Field']))->field($field)->select();
                        $arr[$v['Field']]['list'] = [
                            0 => [
                                'id' => 0,
                                'title' => '无'
                            ]
                        ];
                        foreach($temp as $v1){
                            $arr[$v['Field']]['list'][] = $v1;
                        }
                    }else{
                        $arr[$v['Field']]['type'] = 'text';
                        $arr[$v['Field']]['step'] = 1;
                        $arr[$v['Field']]['value'] = $r ? strval($r[$v['Field']]) : '';
                    }
                }elseif(preg_match('/text/', $v['Type']) || preg_match('/longtext/', $v['Type'])){
                    if(preg_match('/_ids/', $v['Field'])){//以IDS结束的字段表示是多项关联
                        $arr[$v['Field']]['value'] = $r ? json_decode($r[$v['Field']], true) : '';
                        $arr[$v['Field']]['type'] = 'checkbox';
                        $arr[$v['Field']]['list'] = \think\Db::name(str_replace('_ids', '', $v['Field']))->field('id, title')->select();
                    }else{
                        $arr[$v['Field']]['value'] = $r ? $r[$v['Field']] : '';
                        $arr[$v['Field']]['type'] = 'editor';
                    }
                }
            }
        }
        $other = ['formTitle' => $this->getTableTitle($table)];
        return json_return('10000', '', $arr, $other);
    }
    /**
     * 添加/编辑
     *
     * @param array $param
     * @param string $table
     * @return json
     */
    public function commonAdd($table = '')
    {
        $param = input('post.');
        $id = input('get.id', null);
        $root = \think\facade\App::getRootPath();
        
        if(file_exists($root.'application/admin/validate/'.ucfirst($table).'.php')){//如果有验证尝试验证
            try{
                $validateStr = '\\app\\admin\\validate\\'.ucfirst($table);
                $validate = new $validateStr;
                if(!empty($id)){
                    if(!$validate->scene('edit')->check($param)){
                        return json_return('10001', $validate->getError());
                    }
                }else{
                    if(!$validate->scene('add')->check($param)){
                        return json_return('10001', $validate->getError());
                    }
                }
            }catch(\Exception $e){

            }
        }
        if($id){
            $data = \think\Db::name($table)->where('id', $id)->find();
            if(!$data){
                return json_return('10002', 'id参数错误');
            }
            $source = $data;
        }else{
            $source = null;
        }
        $fields = $this->getTableFields($table);
        $logicStr = '\\app\\admin\\logic\\'.ucfirst($table);
        if(file_exists($root.'application/admin/logic/'.ucfirst($table).'.php')){
            try{//尝试过滤字段
                $fields = array_diff($fields, (new $logicStr)->filterField());
            }catch(\Exception $e){

            }
        }
        $save = [];
        $time = time();
        foreach($fields as $v){
            if($v == 'create_time' && !$id){
                $save['create_time'] = $time;
            }elseif($v == 'update_time' && $id){
                $save['update_time'] = $time;
            }elseif($v == 'password'){
                if(input('post.password', null)){
                    $save['password'] = md5($param[$v]);
                }
            }elseif($v != 'id'){
                if(isset($param[$v])){
                    $save[$v] = is_array($param[$v]) ? json_encode($param[$v]) : $param[$v];
                }
            }
        }
        if(file_exists($root.'application/admin/logic/'.ucfirst($table).'.php')){
            try{//尝试处理特殊字段
                $save = (new $logicStr)->special($param, $save);
            }catch(\Exception $e){

            }
        }
        if(!$id){
            $param['id'] = \think\Db::name($table)->insertGetId($save);
        }else{
            \think\Db::name($table)->where('id', $id)->update($save);
        }
        (new \app\common\logic\Log)->createLog(USER['id'], 'admin', $source, $save);//日志别忘了
        return json_return();
    }

    /**
     * 删除数据
     *
     * @param string $table
     * @return json
     */
    public function commonDel($table = '')
    {
        if($table == 'permission'){
            return json_return('10001', '权限表数据暂时不允许删除');
        }
        $param = Request::post();
        $validate = new \app\admin\validate\Common;
        if(!$validate->scene('del')->check($param)){
            return json_return('10002', $validate->getError());
        }
        if(!is_array($param['id'])){
            $param['id'] = [$param['id']];
        }
        $modelStr = '\\app\\admin\\model'.ucfirst($table);
        $result = (new $modelStr)->all($param);
        if(!$result){
            return json_return('10002', '没有找到要删除的数据');
        }
        (new \app\common\logic\Log)->createLog(USER['id'], 'admin', $result);//日志别忘了
        $result->delete();
        return json_return();

    }

    /**
     * 获取数据字段
     *
     * @param string $table
     * @param boolean $full
     * @return array
     */
    private function getTableFields($table = '', $full = false)
    {
        $fields = \think\Db::query('show full fields from '.config('database.prefix').$table);
        if($full){
            return $fields;
        }
        $data = [];
        foreach($fields as $v){
            $data[] = $v['Field'];
        }
        return $data;
    }

    /**
     * 获取表的COMMENT
     *
     * @param string $table
     * @return string
     */
    private function getTableTitle($table = '')
    {
        $r = \think\Db::query('show create table '.config('database.prefix').$table);
        if(\preg_match('/utf8 COMMENT=\'(.*?)\'/', $r[0]['Create Table'], $temp)){
            return $temp[1];
        }
        return '';
    }
}