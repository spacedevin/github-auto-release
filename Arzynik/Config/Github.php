<?php namespace Arzynik\Config;
use InvalidArgumentException;
class Github {
    protected $password = '';
    protected $userName = '';
    protected $repositories = [];
    public function __construct($iniPath = null) {
        $data = $this->getDataList($iniPath);
        if(isset($data['Github'])) {
            if(isset($data['Github']['username'])) {
                $this->username = $data['Github']['username'];
            }
            if(isset($data['Github']['password'])) {
                $this->password = $data['Github']['password'];
            }
            unset($data['Github']);
        }
        foreach($data as $repository => $set) {
            $this->repositories[$repository] = new Repository($repository,$set);
        }
    }
    protected function getDataList($iniPath) {
        if($iniPath && is_file($iniPath) && !is_dir($iniPath)) {
            return parse_ini_file($iniPath,true,INI_SCANNER_RAW);
        }
        return parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.ini',true,INI_SCANNER_RAW);
    }
    public function isBranchAllowed($repository,$branch) {
        if(!$this->exists($repository)) {
            return false;
        }
        return $this->repositories[$repository]->isBranchAllowed($branch);
    }
    public function getUserName() {
        return $this->username;
    }
    public function getPassword() {
        return $this->password;
    }
    public function getKey($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getKey();
    }
    public function exists($repository) {
        return isset($this->repositories[$repository]);
    }
    public function getBasePath($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getBasePath();
    }
    public function getLocalPath($repository,$branch) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        if(!$this->isBranchAllowed($repository,$branch)) {
            throw new InvalidArgumentException('This branch may not be deployed: ' . $branch);
        }
        return $this->repositories[$repository]->getLocalPath($branch);
    }
    public function mayDeploy($repository,$branch) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        if(!$this->isBranchAllowed($repository,$branch)) {
            throw new InvalidArgumentException('This branch may not be deployed: ' . $branch);
        }
        return $this->repositories[$repository]->mayDeploy();
    }
    public static function get() {
        if(!isset($GLOBALS[self::class])) {
            $GLOBALS[self::class] = new Github();
        }
        return $GLOBALS[self::class];
    }
}