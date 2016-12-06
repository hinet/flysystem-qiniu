<?php
use Storage;
use Qiniu\Auth;
use Hinet\Flysystem\Qiniu\QiniuAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
class QiniuServiceProvider extends ServiceProvider {
    public function boot()
    {
        Storage::extend('qiniu', function($app, $config)
        {
            $auth = new Auth($config['accessKey'],$config['secretKey']);
            $filesystem = new Filesystem(new QiniuAdapter($auth, $config['bucket']));
            
            return $filesystem;
        });
    }
    public function register()
    {
        //
    }
}