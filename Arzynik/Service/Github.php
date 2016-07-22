<?php namespace Arzynik\Service;
use InvalidArgumentException;
class Github {
    protected function setMethodData($url,$data,$method,$contentType,$curl) {
        if($method === 'post') {
            curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: ' . $contentType));
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
            curl_setopt($curl,CURLOPT_URL,$url);
            return $curl;
        }
        if($method === 'get') {
            if(is_array($data) && count($data)) {
                curl_setopt($curl,CURLOPT_URL,$url . '?' . http_build_query($data));
            } else {
                curl_setopt($curl,CURLOPT_URL,$url);
            }
            curl_setopt($curl,CURLOPT_HTTPGET,true);
            return $curl;
        }
        throw new InvalidArgumentException();
    }
    protected function getCurl() {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_USERPWD,Config::get()->getUserName() . ':' . Config::get()->getPassword());
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($curl,CURLOPT_AUTOREFERER,true);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_HEADER,true);
        return $curl;
    }
    public function send($url,$data = '',$method = 'get',$contentType = 'application/json') {
        if(substr($url,0,4) !== 'http') {
            $url = 'https://github.com/' . $url;
        }
        $curl = $this->getCurl();
        $this->setMethodData($url,$data,$method,$contentType,$curl);
        return curl_exec($curl);
    }
}