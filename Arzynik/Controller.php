<?php namespace Arzynik;
use Arzynik\Config\Github;
use Arzynik\Task\Deploy;
use Arzynik\Task\Download;
use Arzynik\Task\Release;
use Arzynik\Task\Unzip;
use Arzynik\Task\Version;
class Controller {
    protected function mayAccess() {
        if(!isset(apache_request_headers()['X-Hub-Signature'])) {
            header('','',401);
            return 'false';
        }
        list($algo,$hash) = explode('=',apache_request_headers()['X-Hub-Signature']);
        $data = file_get_contents('php://input');
        $jsonData = json_decode($data);
        if(hash_hmac($algo,$data,Github::get()->getKey($jsonData->repository->full_name)) != $hash) {
            header('','',401);
            return 'false';
        }
        return $jsonData;
    }
    public function run() {
        $jsonData = $this->mayAccess();
        if(!$jsonData) {
            return 'false';
        }
        $zipFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . explode('/',$jsonData->ref)[2] . '.zip';
        $zipFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonData->repository->name . '-' . explode('/',$jsonData->ref)[2];
        if(!(new Download())->run($zipFile,$jsonData->repository->full_name,explode('/',$jsonData->ref)[2])) {
            return 'false';
        }
        if(!(new Unzip())->run($zipFile,$zipFolder)) {
            return false;
        }
        $baseFolder = $zipFolder . DIRECTORY_SEPARATOR . Github::get()->getBasePath($jsonData->repository->full_name);
        if(Github::get()->mayDeploy($jsonData->repository->full_name,explode('/',$jsonData->ref)[2])) {
            if(!(new Deploy())->run(
                            $baseFolder,Github::get()->getLocalPath($jsonData->repository->full_name,explode('/',$jsonData->ref)[2])
                    )) {
                return 'false';
            }
        }
        if(explode('/',$jsonData->ref)[2] !== 'master') {
            return 'true';
        }
        list($version,$tickets) = (new Version())->run();
        $targetFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonData->repository->name . '.' . $version . '.zip';
        if(!(new Zip($baseFolder))->run($targetFile)) {
            return 'false';
        }
        if((new Release($baseFolder))->run($jsonData->repository->full_name,$version,$tickets,$zipFile)) {
            return 'true';
        }
        return 'false';
    }
}