<?php
namespace app\admin\controller;

use app\admin\controller\Init;

class Common extends Init
{
    /**
     * 用户菜单
     *
     * @return json
     */
    public function getUserMenuList()
    {
        return (new \app\admin\logic\Menu)->getUserMenuList();
    }
}