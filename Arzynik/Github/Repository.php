<?php namespace Arzynik\Github;
class Repository extends Object {
    public $_source;
    public $_repo;
    /**
     *
     * @return array
     */
    public function info() {
        if(!isset($this->id)) {
            $request = $this->github()->request(array(
                'url' => $this->github()->base . '/repos/' . $this->_source . '/' . $this->_repo,
                'method' => 'get'
            ));

            $this->load($request->response());
            print_r($request->response);
        }
        return $this->_properties;
    }
    /**
     *
     * @return Download[]
     */
    public function downloads() {
        if(!isset($this->_downloads)) {
            $request = $this->github()->request(array(
                'url' => $this->github()->base . '/repos/' . $this->_source . '/' . $this->_repo . '/downloads',
                'method' => 'get'
            ));

            $this->_downloads = array();

            foreach($request->response() as $download) {
                $download->_source = $this->_source;
                $download->_repo = $this->_repo;
                $this->_downloads[$download->id] = new Download($download,$this->github());
            }
        }
        return $this->_downloads;
    }
}