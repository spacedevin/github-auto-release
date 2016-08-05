<?php namespace Arzynik\Task;
use Arzynik\Service\Github;
class Download {
    /**
     *
     * @param string $zipFile
     * @param string $repository
     * @param string $branch
     * @return string
     */
    public function run($zipFile,$repository,$branch) {
        file_put_contents(
                $zipFile,(new Github())->get('https://github.com/' . $repository . '/archive/' . $branch . '.zip')
        );
        return is_file($zipFile)?$zipFile:false;
    }
}