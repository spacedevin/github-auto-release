<?php namespace Arzynik\Task;
use Arzynik\Service\Github;
class Release {
    /**
     *
     * @param string $repository
     * @param string $version
     * @param int[] $tickets
     * @param string $zipFile
     * @return boolean
     */
    public function run($repository,$version,$tickets,$zipFile) {
        $curl = new Github();
        $data = $curl->send('repos/' . $repository . '/releases','{"tag_name": "' . $version . '","name": "' . $version . '","body": "Automatic Release\n Fixes to #' . implode(', #',$tickets) . '"}','post','application/json');
        if(!is_object($data)) {
            $data = json_decode($data);
        }
        if(isset($data->assets_url) && $data->assets_url) {
            $data = $curl->send($data->assets_url . '?name=' . explode('/',$repository)[1] . '.zip',file_get_contents($zipFile),'post','application/zip');
            if(!is_object($data)) {
                $data = json_decode($data);
            }
            if($data->browser_download_url) {
                return true;
            }
        }
        error_log(json_encode($data));
        return false;
    }
}