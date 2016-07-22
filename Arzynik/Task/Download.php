<?php namespace Arzynik\Task;
class Download {
    public function run($zipFile,$repository,$branch) {
        file_put_contents(
                $zipFile,(new \Arzynik\Service\Github())->send('https://github.com/' . $repository . '/archive/' . $branch . '.zip')
        );
        return is_file($zipFile)?$zipFile:false;
    }
}