<?php namespace Arzynik\Github;
class Object {
    private $_github;
    /**
     *
     * @param type $params
     * @param type $github
     */
    public function __construct($params,$github = null) {
        $this->_github = $github;

        $this->load($params);
    }
    /**
     *
     * @param type $object
     */
    public function load($object) {
        if(is_object($object)) {
            foreach(get_object_vars($object) as $key => $value) {
                $this->$key = $value;
            }
        } elseif(is_array($object)) {
            foreach($object as $key => $value) {
                $this->{'_' . $key} = $value;
            }
        }
    }
    /**
     *
     * @return type
     */
    public function github() {
        return $this->_github;
    }
    /**
     *
     * @param string $name
     * @return type
     */
    public function &__get($name) {
        if($name{0} == '_') {
            return $this->{$name};
        } else {
            if(!$this->_properties['id']) {
                $this->info();
            }
            return $this->_properties[$name];
        }
    }
    /**
     *
     * @param string $name
     * @param mixed $value
     * @return type
     */
    public function __set($name,$value) {
        if($name{0} == '_') {
            return $this->{$name} = $value;
        } else {
            return $this->_properties[$name] = $value;
        }
    }
    /**
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name) {
        return $name{0} == '_'?isset($this->{$name}):isset($this->_properties[$name]);
    }
}