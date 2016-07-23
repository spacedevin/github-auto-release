<?php namespace Arzynik\Service;
class FileSystem {
    /**
     *
     * @param string $path
     * @return void
     */
    public function delete($path) {
        if(preg_match('#\.\.?$#',$path)) {
            return;
        }
        if(is_dir($path)) {
            foreach(scandir($path) as $file) {
                $this->delete($path . DIRECTORY_SEPARATOR . $file);
            }
            rmdir($path);
            return;
        }
        unlink($path);
    }
    /**
     *
     * @param string $path
     * @return void
     */
    public function getFiles($path,&$list) {
        if(preg_match('#/\.\.?$#',$path)) {
            return;
        }
        if(is_dir($path)) {
            foreach(scandir($path) as $file) {
                $this->getFiles($path . DIRECTORY_SEPARATOR . $file);
            }
            return;
        }
        $list[substr($path,strlen($this->basePath) + 1)] = $path;
    }
    /**
     *
     * @param string $origin
     * @param string $target
     * @return void
     */
    public function copy($origin,$target) {
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
}