<?php namespace Arzynik\Service;
use Arzynik\Config\Github as Github2;
class Github {
    /**
     *
     * @return resource
     */
    protected function getCurl() {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_USERPWD,Github2::get()->getUserName() . ':' . Github2::get()->getPassword());
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($curl,CURLOPT_AUTOREFERER,true);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_USERAGENT,'Idrinth/github-auto-release');
        return $curl;
    }
    /**
     *
     * @param string $url
     * @param string $data
     * @param string $contentType
     * @return string
     */
    public function send($url,$data = '',$contentType = 'application/json') {
        if(substr($url,0,4) !== 'http') {
            $url = 'https://api.github.com' . ($url[0] === '/'?'':'/') . $url;
        }
        $curl = $this->getCurl();
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: ' . $contentType,'Accept: application/vnd.github.v3+json'));
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl,CURLOPT_URL,$url);
        return curl_exec($curl);
    }
    /**
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function get($url,$data = []) {
        if(substr($url,0,4) !== 'http') {
            $url = 'https://api.github.com' . ($url[0] === '/'?'':'/') . $url;
        }
        $curl = $this->getCurl();
        curl_setopt($curl,CURLOPT_URL,$url . (is_array($data) && count($data)?'?' . http_build_query($data):''));
        curl_setopt($curl,CURLOPT_HTTPGET,true);
        return curl_exec($curl);
    }
}