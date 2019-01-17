<?php
namespace app\admin\logic;

use Qcloud\Cos\Client;

class Upload
{
    public function img()
    {
        return (new \app\common\logic\Upload)->img();
    }

    public function file()
    {
        return (new \app\common\logic\Upload)->file();
    }
}