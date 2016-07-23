<?php namespace Arzynik\Task;
use Arzynik\Service\FileSystem;
use Exception;
use ZipArchive;
class Zip {
    /**
     *
     * @var string
     */
    protected $basePath = '';
    /**
     *
     * @var string[]
     */
    protected $files = [];
    /**
     *
     * @param string $basePath
     */
    function __construct($basePath) {
        $this->basePath = $basePath;
        (new FileSystem())->getFiles($this->basePath,$this->files);
    }
    /**
     *
     * @param string $targetFile
     * @return boolean
     * @throws Exception
     */
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