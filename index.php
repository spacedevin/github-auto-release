<?php
function arzynikClassLoad($class) {
    if(is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('\\',DIRECTORY_SEPARATOR,$class) . '.php')) {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . str_replace('\\',DIRECTORY_SEPARATOR,$class) . '.php');
    }
}
spl_autoload_register('arzynikClassLoad');
(new Arzynik\Controller())->run();
