<?php namespace Arzynik\Task;
class Release {
    public function run($version,$tickets,$zipFile) {
        $curl = new \Arzynik\Service\Github();
        $data = $curl->send($url,'{
  "tag_name": "v' . $version . '",
  "target_commitish": "master",
  "name": "v' . $version . '",
  "body": "Automatic Release\n Fixes to #' . implode(', #',$tickets) . '",
  "draft": false,
  "prerelease": false
}','post','application/json');
        if(!$data->assets_url) {
            $curl->send($data->assets_url . '?name=' . $name . '.zip',$zipFile,'post','application/zip');
            if($data->browser_download_url) {
                return true;
            }
        }
        return false;
    }
}