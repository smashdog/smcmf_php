<?php
namespace app\common\logic;

class Log
{
    /**
     * 写日志
     *
     * @param int $action_id
     * @param string $type
     * @param array/object $source_data
     * @param array/object $new_data
     * @return boolean
     */
    public function createLog($action_id, $type, $source_data = null, $new_data = null)
    {
        if($type == 'user'){
            $logModel = new \app\common\model\UserLog;
        }elseif($type == 'admin'){
            $logModel = new \app\common\model\AdminLog;
        }else{
            return false;
        }
        list($module, $controller, $action) = get_path_info();
        $logModel->action = "{$module}/{$controller}/{$action}";
        if($source_data){
            if(is_object($source_data)){
                $source_data = $source_data->toArray();
            }
            $logModel->source_data = json_encode($source_data);
        }
        if($new_data){
            if(is_object($new_data)){
                $new_data = $new_data->toArray();
            }
            $logModel->new_data = json_encode($new_data);
        }
        $logModel->create_year = date('Y');
        $logModel->create_month = date('m');
        $logModel->create_day = date('d');
        $logModel->create_his = date('H:i:s');
        $logModel->action_id = $action_id;
        $logModel->id = dk_get_dt_id();
        $logModel->ip = request()->ip();
        $logModel->save();
        return true;
    }

    /**
     * 日志列表
     *
     * @param string $type
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $date
     * @return json
     */
    public function getLogList($type, $module, $controller, $action, $date = null)
    {
        if($type == 'user'){
            $logModel = new \app\common\model\userLog;
        }elseif($type == 'admin'){
            $logModel = new \app\common\model\adminLog;
        }else{
            return json_return('10001', '日志类别错误');
        }
        $where = [
            ['module', '=', $module],
            ['controller', '=', $controller],
            ['action', '=', $action]
        ];
        if(preg_match('/^\d{4}-\d{2}-\d(2)$/')){
            $date = explode('-', $date);
            $where[] = ['create_year', '=', $date[0]];
            $where[] = ['create_month', '=', $date[1]];
            $where[] = ['create_day', '=', $date[2]];
        }
        $list = $logModel->where($where)->paginate(10);
        if(is_object($list)){
            $list = $list->toArray();
        }
        return json_return('10000', '', $list);
    }
}