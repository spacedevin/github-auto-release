<?php namespace Arzynik\Task;
use Arzynik\Service\Github;
class Release {
    public function run($repository,$version,$tickets,$zipFile) {
        $curl = new Github();
        $data = $curl->send('repos/' . $repository . '/releases','{
  "tag_name": "v' . $version . '",
  "target_commitish": "master",
  "name": "v' . $version . '",
  "body": "Automatic Release\n Fixes to #' . implode(', #',$tickets) . '",
  "draft": false,
  "prerelease": false
}','post','application/json');
        if(!$data->assets_url) {
            $curl->send($data->assets_url . '?name=' . explode('/',$repository)[1] . '.zip',file_get_contents($zipFile),'post','application/zip');
            if($data->browser_download_url) {
                return true;
            }
        }
        return false;
    }
}