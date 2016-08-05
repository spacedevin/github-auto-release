<?php namespace Arzynik\Task;
class Deploy {
    /**
     *
     * @param string $originFolder
     * @param string $targetFolder
     * @return boolean
     */
    public function run($originFolder,$targetFolder) {
        $fileSystem = new \Arzynik\Service\FileSystem();
        $fileSystem->delete($targetFolder);
        $fileSystem->copy($originFolder,$targetFolder);
        return is_dir($targetFolder) && filemtime($targetFolder) > time() - 20;
    }
}