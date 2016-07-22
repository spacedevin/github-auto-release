<?php namespace Arzynik;
use Arzynik\Github\Download;
use Arzynik\Github\Repository;
class Controller {
    public function run() {
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
        $zip = new \Zip();
        foreach($files as $name => $zipfile) {
            $baseName = substr($name,0,strrpos($name,'/'));
            @mkdir(sys_get_temp_dir() . $baseName);
            file_put_contents($cache . $name,file_get_contents($zipfile));
            $zipedFiles[$name] = $cache . $name;
        }

        $out = $zip->create($zipedFiles,array(
            'name' => $file['name'] . '.zip',
            'destination' => $cache
        ));


        /* delete old downloads */

        $github = new Github();

        $repository = new Repository(array(
            'source' => $source,
            'repo' => $repo
                ),$github);

        foreach($repository->downloads() as $download) {
            if($download->name . '.zip' == $file['name'] . '.zip') {
                $res = $download->delete();
            }
        }

        /* add the new download */

        $download = new Download(array(
            'source' => $source,
            'repo' => $repo
                ),$github);

        $download->create(array(
            'name' => $file['name'] . '.zip',
            'file' => $file['path'],
            'description' => $file['description']
        ));


        /* delete everything */

        foreach($files as $name => $zipfile) {
            unlink($cache . $name);
        }
        unlink($file['path']);
    }
}