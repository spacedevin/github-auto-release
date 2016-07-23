<?php namespace Arzynik;
use Arzynik\Config\Github;
use Arzynik\Task\Deploy;
use Arzynik\Task\Download;
use Arzynik\Task\Release;
use Arzynik\Task\Unzip;
use Arzynik\Task\Version;
use Arzynik\Task\Zip;
use Exception;
use stdClass;
class Controller {
    /**
     *
     * @var string[]
     */
    protected $tasks = [];
    /**
     *
     * @return boolean|stdClass
     */
    protected function mayAccess() {
        if(!isset(apache_request_headers()['X-Hub-Signature'])) {
            header('','',401);
            return false;
        }
        list($algo,$hash) = explode('=',apache_request_headers()['X-Hub-Signature']);
        $data = file_get_contents('php://input');
        $jsonData = json_decode($data);
        if(hash_hmac($algo,$data,Github::get()->getKey($jsonData->repository->full_name)) != $hash) {
            header('','',401);
            return false;
        }
        if(!preg_match('#^refs/heads/#',$jsonData->refs)) {
            return false;
        }
        return $jsonData;
    }
    /**
     *
     * @return string
     */
    public function run() {
        $jsonData = $this->mayAccess();
        if(!$jsonData) {
            return 'false';
        }
        try {
            if(!$this->processGeneral($jsonData)) {
                header('','',500);
            }
        } catch(Exception $e) {
            error_log($e->getTraceAsString());
            header('','',500);
        }
        return json_encode($this->tasks);
    }
    /**
     *
     * @param stdClass $jsonData
     * @return boolean
     */
    protected function processGeneral($jsonData) {
        $zipFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . explode('/',$jsonData->ref)[2] . '.zip';
        $zipFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonData->repository->name . '-' . explode('/',$jsonData->ref)[2];
        if(!(new Download())->run($zipFile,$jsonData->repository->full_name,explode('/',$jsonData->ref)[2])) {
            return false;
        }
        $this->tasks[] = 'download';
        if(!(new Unzip())->run($zipFile,$zipFolder)) {
            return false;
        }
        $this->tasks[] = 'unzip';
        $baseFolder = $zipFolder . DIRECTORY_SEPARATOR . Github::get()->getBasePath($jsonData->repository->full_name);
        if(Github::get()->mayDeploy($jsonData->repository->full_name,explode('/',$jsonData->ref)[2])) {
            if(!(new Deploy())->run(
                            $baseFolder,Github::get()->getLocalPath($jsonData->repository->full_name,explode('/',$jsonData->ref)[2])
                    )) {
                return false;
            }
            $this->tasks[] = 'deploy';
        }
        if(explode('/',$jsonData->ref)[2] === 'master') {
            $this->tasks[] = 'master';
            return $this->handleMaster($jsonData,$baseFolder,$zipFile);
        }
        return true;
    }
    /**
     *
     * @param stdClass $jsonData
     * @param string $baseFolder
     * @param string $zipFile
     * @return boolean
     */
    protected function handleMaster($jsonData,$baseFolder,$zipFile) {
        list($version,$tickets) = (new Version())->run($jsonData->commits,$jsonData->repository->full_name);
        $this->tasks[] = 'version';
        $targetFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonData->repository->name . '.' . str_replace('.','-',$version) . '.zip';
        if(!(new Zip($baseFolder))->run($targetFile)) {
            return false;
        }
        $this->tasks[] = 'zip';
        if((new Release($baseFolder))->run($jsonData->repository->full_name,$version,$tickets,$zipFile)) {
            $this->tasks[] = 'release';
            return true;
        }
        return false;
    }
}