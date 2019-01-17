<?php
namespace app\common\logic;

class Upload
{
    private function index($inputname = '')
    {
        $file = request()->file($inputname);
        $info = $file->getInfo();
        $newname = preg_replace('/\/tmp\/\w+/', '/tmp/'.$info['name'], $info['tmp_name']);
        rename($info['tmp_name'], $newname);
        $conf = config('cos.');
        $cosClient = new \Qcloud\Cos\Client([
            'region' => $conf['COS_REGION'],
            'credentials'=> [
                'appId' => $conf['COS_APPID'],
                'secretId'    => $conf['COS_KEY'],
                'secretKey' => $conf['COS_SECRET']
            ]
        ]);
        try {
            $result = $cosClient->putObject([
                'Bucket' => $conf['COS_BUCKET'],
                'Key' => $info['name'],
                'Body' => fopen($newname, 'rb')
            ]);
            return json_return('10000', '上传成功', [
                'k' => input('param.k'),
                'url' => $cosClient->getObjectUrl($conf['COS_BUCKET'], $info['name'])
            ]);
        } catch (\Exception $e) {
            return json_return('10001', '', ['error' => $e]);
        }
    }

    public function img()
    {
        $file = $this->index('img');
        return $file;
    }

    public function file()
    {
        $file = $this->index('file');
        return $file;
    }
}