<?php namespace Arzynik\Task;
class Deploy {
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
    protected function copy($origin,$target) {
        if(preg_match('#\.\.?$#',$origin)) {
            return;
        }
        if(is_dir($origin)) {
            mkdir($target,0777);
            foreach(scandir($origin) as $file) {
                $this->copy($origin . DIRECTORY_SEPARATOR . $file,$target . DIRECTORY_SEPARATOR . $file);
            }
            return;
        }
        copy($origin,$target);
    }
    public function run($originFolder,$targetFolder) {
        $this->delete($targetFolder);
        $this->copy($originFolder,$targetFolder);
        return is_dir($targetFolder) && filemtime($targetFolder) > time() - 20;
    }
}