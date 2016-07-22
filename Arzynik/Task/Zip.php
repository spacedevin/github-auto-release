<?php namespace Arzynik;
use Exception;
use ZipArchive;
class Zip {
    protected $basePath = '';
    protected $files = [];
    function __construct($basePath) {
        $this->basePath = $basePath;
    }
    protected function getFiles($path) {
        if(preg_match('#/\.}\.?$#',$path)) {
            return;
        }
        if(is_dir($path)) {
            foreach(scandir($path) as $file) {
                $this->getFiles($path . '/' . $file);
            }
            return;
        }
        $this->files[substr($path,strlen($this->basePath)) + 1] = $path;
    }
    public function run($targetFile) {
        if(!file_exists($targetFile)) {
            throw new Exception('Destination directory "' . $targetFile . '" does not exist.');
        }

        $zip = new ZipArchive();

        if($zip->open($targetFile,ZIPARCHIVE::OVERWRITE) !== true) {
            return false;
        }

        foreach($this->getFiles($this->basePath) as $filename => $path) {
            $zip->addFile($path,$filename);
        }
        $zip->close();
        chmod($targetFile,0777);

        return file_exists($targetFile) && filesize($targetFile);
    }
}