<?php
namespace app\admin\controller;

use app\admin\controller\Init;

class Upload extends Init
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