<?php
namespace app\index\controller;

use think\Controller;

class Upload extends Controller
{
    public function img()
    {
        return (new \app\admin\logic\Upload)->img();
    }

    public function file()
    {
        return (new \app\admin\logic\Upload)->file();
    }
}