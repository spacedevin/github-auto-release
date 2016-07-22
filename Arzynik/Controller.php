<?php namespace Arzynik;
class Controller {
    protected function mayAccess() {
        if(!isset(apache_request_headers()['X-Hub-Signature'])) {
            header('','',401);
            return 'false';
        }
        list($algo,$hash) = explode('=',apache_request_headers()['X-Hub-Signature']);
        $data = file_get_contents('php://input');
        $jsonData = json_decode($data);
        if(hash_hmac($algo,$data,Config::get()->getKey($jsonData->repository->full_name)) != $hash) {
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
        (new Service\Download())->run($zipFile,$jsonData->repository->full_name,explode('/',$jsonData->ref)[2]);
        (new Service\Unzip())->run($zipFile,$zipFolder);
        $baseFolder = $zipFolder . DIRECTORY_SEPARATOR . Config::get()->getBasePath($jsonData->repository->full_name);
        if(Config::get()->mayDeploy($jsonData->repository->full_name,explode('/',$jsonData->ref)[2])) {
            (new Service\Deploy())->run(
                    $baseFolder,Config::get()->getLocalPath($jsonData->repository->full_name,explode('/',$jsonData->ref)[2])
            );
        }
        if(explode('/',$jsonData->ref)[2] !== 'master') {
            return true;
        }
        $targetFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonData->repository->name . '.' . $version . '.zip';
        (new Zip($baseFolder))->run($targetFile);
        (new Task\Version())->run();
        (new Release($baseFolder))->run($targetFile);
    }
    public function __destruct() {
        unlink($this->zipFile);
        $this->delete($this->tmpFolder);
    }
}