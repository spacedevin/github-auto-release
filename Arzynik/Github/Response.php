<?php namespace Arzynik\Github;
use stdClass;
class Response {
    public $response;
    public $headers;
    public $error;
    /**
     *
     * @param string[] $params
     */
    public function __construct($params = array()) {
        $this->response = $params['response'];
        $this->request = $params['request'];
        $this->url = $params['url'];
        $this->headers = $params['headers'];
        $this->error = $params['error'];
    }
    /**
     *
     * @return stdClass
     */
    public function response() {
        return json_decode($this->response);
    }
}