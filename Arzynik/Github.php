<?php namespace Arzynik;
use Arzynik\Github\Response;
class Github {
    /**
     *
     * @param array $params
     * @return Response
     */
    public function request($params) {
        $params['headers'] = $params['headers']?true:false;

        $ch = $this->getCurl($params['url'],$params['data'],$params['method']);
        $output = curl_exec($ch);

        if($params['headers']) {
            $sep = strpos($output,"\r\n\r\n") === false?"\n\n":"\r\n\r\n";
            list($headersRaw,$output) = explode($sep,$output,2);
        }

        $response = new Response(array(
            'url' => $params['url'],
            'request' => isset($params['method']) && $params['method'] == 'get'?http_build_query($params['data']):json_encode($params['data']),
            'response' => $output,
            'headers' => $params['headers']?$this->headers($headersRaw):null,
            'error' => curl_error($ch)
        ));

        curl_close($ch);

        return $response;
    }
    protected function getCurl($url,$data = array(),$method = 'get') {

        $ch = curl_init();

        switch($method) {
            case 'delete':
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');
            case 'post':
                curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
                curl_setopt($ch,CURLOPT_URL,$url);

                break;
            default:
            case 'get':

                if(is_array($data) && count($data)) {
                    curl_setopt($ch,CURLOPT_URL,$url . '?' . http_build_query($data));
                } else {
                    curl_setopt($ch,CURLOPT_URL,$url);
                }
                curl_setopt($ch,CURLOPT_HTTPGET,true);
        }


        curl_setopt($ch,CURLOPT_USERPWD,Config::get()->getUserName() . ':' . Config::get()->getPassword());
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_HEADER,true);
        return $ch;
    }
    protected function headers($headersRaw) {
        foreach(explode("\n",$headersRaw) as $header) {
            if(preg_match('/HTTP\//i',$header)) {
                $header = explode(' ',$header);
                $headers[$header[0]] = $header[1];
            } else {
                $header = explode(':',$header,2);
                $headers[$header[0]] = $header[1];
            }
        }
        return $headers;
    }
}