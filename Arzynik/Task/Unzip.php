<?php namespace Arzynik\Task;
use ZipArchive;
class Unzip {
    protected function delete($path) {
        if(preg_match('#/\.\.?$#',$path)) {
            return;
        }
        if(is_dir($path)) {
            foreach(scandir($path) as $file) {
                $this->delete($path . '/' . $file);
            }
            rmdir($path);
            return;
        }
        unlink($path);
    }
    public function run($zipFile,$zipFolder) {
        $zip = new ZipArchive();
        if(!$zip->open($zipFile)) {
            return false;
        }
        if(is_dir($zipFolder)) {
            $this->delete($zipFolder);
        }
        $zip->extractTo(sys_get_temp_dir());
        $zip->close();
        return is_dir($zipFolder);
    }
}