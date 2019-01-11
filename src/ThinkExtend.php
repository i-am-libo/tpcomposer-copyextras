<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class ThinkExtend extends LibraryInstaller
{

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->copyExtraFiles($package);
    }

    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->copyExtraFiles($target);

    }
    /**
    * 复制文件夹
    * @param $source
    * @param $dest
    */
    protected function copydir($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0777, true);
        } else {
            deldir($desc);
        }
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false) {
            if ($item == '.' || $item == '..') continue;
            $_source = $source . '/' . $item;
            $_dest = $dest . '/' . $item;
            if (is_file($_source)) copy($_source, $_dest);
            if (is_dir($_source)) $this->copydir($_source, $_dest);
        }
        closedir($handle);
    }

    /**
     * 删除文件夹
     * @param $path
     */
    protected function deldir($path){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        deldir($path.$val.'/');
                        //目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
    }

    protected function copyExtraFiles(PackageInterface $package)
    {
        if ($this->composer->getPackage()->getType() == 'project') {

            $extra = $package->getExtra();

            if(!empty($extra['think-copy-files'])) {
                $source_dir = $extra['think-copy-dir']["source_dir"];
                $desc_dir = $extra['think-copy-dir']["desc_dir"];
                $this->copydir($source_dir, $desc_dir);
            }

            if (!empty($extra['think-config'])) {

                $composerExtra = $this->composer->getPackage()->getExtra();

                $appDir = !empty($composerExtra['app-path']) ? $composerExtra['app-path'] : 'application';

                if (is_dir($appDir)) {

                    $extraDir = $appDir . DIRECTORY_SEPARATOR . 'extra';
                    $this->filesystem->ensureDirectoryExists($extraDir);

                    //配置文件
                    foreach ((array) $extra['think-config'] as $name => $config) {
                        $target = $extraDir . DIRECTORY_SEPARATOR . $name . '.php';
                        $source = $this->getInstallPath($package) . DIRECTORY_SEPARATOR . $config;
                        /*
                        if (is_file($target)) {
                            $this->io->write("<info>File {$target} exist!</info>");
                            continue;
                        }

                        if (!is_file($source)) {
                            $this->io->write("<info>File {$target} not exist!</info>");
                            continue;
                        }
                        */
                        copy($source, $target);
                    }
                }
            }
        }
    }

    public function supports($packageType)
    {
        return 'think-extend' === $packageType;
    }
}