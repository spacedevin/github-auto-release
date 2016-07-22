<?php namespace Arzynik\Task;
class Deploy {
    protected function delete($path) {
        if(preg_match('#/\.}\.?$#',$path)) {
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
    protected function copy($from,$to) {
        if(preg_match('#\.}\.?$#',$from)) {
            return;
        }
        if(is_dir($from)) {
            mkdir($to,0777);
            foreach(scandir($from) as $file) {
                $this->copy($from . DIRECTORY_SEPARATOR . $file,$to . DIRECTORY_SEPARATOR . $file);
            }
            return;
        }
        copy($from,$to);
    }
    public function run($originFolder,$targetFolder) {
        $this->delete($targetFolder);
        $this->copy($originFolder,$targetFolder);
        return is_dir($targetFolder) && filemtime($targetFolder) > time() - 20;
    }
}