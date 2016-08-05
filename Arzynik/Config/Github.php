<?php namespace Arzynik\Config;
use InvalidArgumentException;
class Github {
    /**
     *
     * @var string
     */
    protected $password = '';
    /**
     *
     * @var string
     */
    protected $userName = '';
    /**
     *
     * @var Repository[]
     */
    protected $repositories = [];
    /**
     *
     * @var Github
     */
    protected static $instance = null;
    /**
     *
     * @param string $iniPath
     */
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
    /**
     *
     * @param string $iniPath
     * @return array
     */
    protected function getDataList($iniPath) {
        if($iniPath && is_file($iniPath) && !is_dir($iniPath)) {
            return parse_ini_file($iniPath,true,INI_SCANNER_RAW);
        }
        return parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.ini',true,INI_SCANNER_RAW);
    }
    /**
     *
     * @param string $repository
     * @param string $branch
     * @return boolean
     */
    public function isBranchAllowed($repository,$branch) {
        if(!$this->exists($repository)) {
            return false;
        }
        return $this->repositories[$repository]->isBranchAllowed($branch);
    }
    /**
     *
     * @return string
     */
    public function getUserName() {
        return $this->username;
    }
    /**
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }
    /**
     *
     * @param string $repository
     * @return string
     * @throws InvalidArgumentException
     */
    public function getKey($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getKey();
    }
    /**
     *
     * @param string $repository
     * @return boolean
     */
    public function exists($repository) {
        return isset($this->repositories[$repository]);
    }
    /**
     *
     * @param type $repository
     * @return type
     * @throws InvalidArgumentException
     */
    public function getBasePath($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getBasePath();
    }
    /**
     *
     * @param string $repository
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function getMainTags($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getMainTags();
    }
    /**
     *
     * @param string $repository
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function getFeatureTags($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getFeatureTags();
    }
    /**
     *
     * @param string $repository
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function getBugTags($repository) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        return $this->repositories[$repository]->getBugTags();
    }
    /**
     *
     * @param string $repository
     * @param string $branch
     * @return string
     * @throws InvalidArgumentException
     */
    public function getLocalPath($repository,$branch) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        if(!$this->isBranchAllowed($repository,$branch)) {
            throw new InvalidArgumentException('This branch may not be deployed: ' . $branch);
        }
        return $this->repositories[$repository]->getLocalPath($branch);
    }
    /**
     *
     * @param string $repository
     * @param string $branch
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function mayDeploy($repository,$branch) {
        if(!$this->exists($repository)) {
            throw new InvalidArgumentException('This repository is not configured: ' . $repository);
        }
        if(!$this->isBranchAllowed($repository,$branch)) {
            throw new InvalidArgumentException('This branch may not be deployed: ' . $branch);
        }
        return $this->repositories[$repository]->mayDeploy();
    }
    /**
     *
     * @return Github
     */
    public static function get() {
        if(!self::$instance) {
            self::$instance = new Github();
        }
        return self::$instance;
    }
}