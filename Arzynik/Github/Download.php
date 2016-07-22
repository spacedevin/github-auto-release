<?php namespace Arzynik\Github;
class Download extends Object {
    public $_source;
    public $_repo;
    /**
     *
     * @return type
     */
    public function delete() {
        $request = $this->github()->request(array(
            'url' => $this->github()->base . '/repos/' . $this->_source . '/' . $this->_repo . '/downloads/' . $this->id,
            'method' => 'delete'
        ));
        return $request->reponse;
    }
    /**
     *
     * @param string[] $params
     */
    public function upload($params = array()) {
        $request = $this->github()->request(array(
            'url' => 'https://github.s3.amazonaws.com/',
            'method' => 'post',
            'headers' => true,
            'format' => 'form',
            'data' => array(
                'key' => $this->path,
                'acl' => $this->acl,
                'success_action_status' => '201',
                'Filename' => $this->name,
                'AWSAccessKeyId' => $this->accesskeyid,
                'Policy' => $this->policy,
                'Signature' => $this->signature,
                'Content-Type' => $this->mime_type,
                'file' => '@' . $params['file']
            )
        ));
    }
    /**
     *
     * @param string[] $params
     */
    public function create($params = array()) {
        $request = $this->github()->request(array(
            'url' => $this->github()->base . '/repos/' . $this->_source . '/' . $this->_repo . '/downloads',
            'method' => 'post',
            'headers' => true,
            'format' => 'json',
            'data' => array(
                'name' => $params['name'],
                'size' => filesize($params['file']),
                'description' => $params['description']
            )
        ));

        $this->load($request->response());
        $this->upload($params);
    }
    /**
     *
     * @return array()
     */
    public function info() {
        if(!isset($this->_properties)) {
            $request = $this->github()->request(array(
                'url' => $this->github()->base . '/repos/' . $this->_source . '/' . $this->_repo . '/downloads/' . $this->id,
                'method' => 'get'
            ));
            $this->load($request->response());
        }
        return $this->_properties;
    }
}