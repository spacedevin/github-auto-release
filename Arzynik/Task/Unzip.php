<?php namespace Arzynik\Task;
use Arzynik\Service\FileSystem;
use ZipArchive;
class Unzip {
    public function run($zipFile,$zipFolder) {
        $zip = new ZipArchive();
        if(!$zip->open($zipFile)) {
            return false;
        }
        if(is_dir($zipFolder)) {
            (new FileSystem())->delete($zipFolder);
        }
        $zip->extractTo(sys_get_temp_dir());
        $zip->close();
        return is_dir($zipFolder);
    }
}