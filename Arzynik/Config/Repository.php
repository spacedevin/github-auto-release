<?php namespace Arzynik\Config;
class Repository {
    /**
     *
     * @var string
     */
    protected $key = '';
    /**
     *
     * @var string
     */
    protected $repository = '';
    /**
     *
     * @var string
     */
    protected $basePath = '';
    /**
     *
     * @var boolean
     */
    protected $deploy = true;
    /**
     *
     * @var string
     */
    protected $localPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
    /**
     *
     * @var string[]
     */
    protected $allowedBranches = [];
    /**
     *
     * @var string[]
     */
    protected $mainTags = [];
    /**
     *
     * @var string[]
     */
    protected $featureTags = [];
    /**
     *
     * @var string[]
     */
    protected $bugTags = [];
    /**
     *
     * @param string $name
     * @param $data string[]
     */
    public function __construct($name,$data) {
        $this->repository = $name;
        if(isset($data['key'])) {
            $this->key = $data['key'];
        }
        if(isset($data['basePath'])) {
            $this->basePath = $data['basePath'];
        }
        if(isset($data['localPath'])) {
            $this->localPath = $data['localPath'];
        }
        if(isset($data['branches'])) {
            $this->allowedBranches = (array) explode(',',$data['branches']);
        }
        if(isset($data['main'])) {
            $this->mainTags = (array) explode(',',$data['main']);
        }
        if(isset($data['feature'])) {
            $this->featureTags = (array) explode(',',$data['feature']);
        }
        if(isset($data['bug'])) {
            $this->bugTags = (array) explode(',',$data['bug']);
        }
        if(isset($data['deploy'])) {
            $this->deploy = $data['deploy'] !== 'false' && $data['deploy'];
        }
    }
    /**
     *
     * @return string
     */
    public function getKey() {
        return $this->key;
    }
    /**
     *
     * @return string
     */
    public function getRepository() {
        return $this->repository;
    }
    /**
     *
     * @return string
     */
    public function getBasePath() {
        return $this->basePath;
    }
    /**
     *
     * @param string $branch
     * @return boolean
     */
    public function isBranchAllowed($branch) {
        return count($this->allowedBranches) === 0 || in_array($branch,$this->allowedBranches);
    }
    /**
     *
     * @return boolean
     */
    public function mayDeploy() {
        return $this->deploy;
    }
    /**
     *
     * @return string
     */
    public function getLocalPath($branch) {
        return str_replace('[branch]',preg_replace('/[^a-z0-9]/i','',$branch),$this->localPath);
    }
    /**
     *
     * @return string[]
     */
    public function getMainTags() {
        return $this->mainTags;
    }
    /**
     *
     * @return string[]
     */
    public function getFeatureTags() {
        return $this->featureTags;
    }
    /**
     *
     * @return string[]
     */
    public function getBugTags() {
        return $this->bugTags;
    }
}