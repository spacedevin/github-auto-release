<?php namespace Arzynik\Task;
use Exception;
use ZipArchive;
class Zip {
    protected $basePath = '';
    protected $files = [];
    function __construct($basePath) {
        $this->basePath = $basePath;
        $this->getFiles($this->basePath);
    }
    protected function getFiles($path) {
        if(preg_match('#/\.\.?$#',$path)) {
            return;
        }
        if(is_dir($path)) {
            foreach(scandir($path) as $file) {
                $this->getFiles($path . '/' . $file);
            }
            return;
        }
        $this->files[substr($path,strlen($this->basePath) + 1)] = $path;
    }
    public function run($targetFile) {
        @mkdir(dirname($targetFile),0777,true);
        $zip = new ZipArchive();
        if(!$zip->open($targetFile,ZipArchive::CREATE + ZipArchive::OVERWRITE)) {
            throw new Exception('Couldn\'t create zip file');
        }
        foreach($this->files as $filename => $path) {
            $zip->addFile($path,$filename);
        }
        $zip->close();
        chmod($targetFile,0777);

        return file_exists($targetFile) && filesize($targetFile);
    }
}