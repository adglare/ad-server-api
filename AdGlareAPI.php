<?php

/*******
 * File:         AdGlareAPI.php
 * Version:      2.10
 * Last Update:  2018-07-23
 * API Version:  v2
 * Written by:   adglare.com
 ******/

class AdGlareAPI {
    private $endpoint;
    private $public_key;
    private $response_format = 'json';
    private $response_time = 0;
    private $response_bytes = 0;
    
    function __construct($endpoint, $public_key) {
        $this->endpoint = $endpoint;
        $this->public_key = $public_key;
    }
    
    public function setResponseFormat($f) {
        if(!in_array($f,array('json','raw'))) throw new AdGlareException("This is not a valid response format. Please check the documentation.");
        $this->response_format = $f;
    }
    
    public function getResponse($method,$arr_postdata) {
        $url = "http".((xxlserver())?"s":"")."://".$this->endpoint."/api/v2/".$method;
        $sw = $this->getstopwatch();
        $content = $this->curlRequest($url,$arr_postdata);
        $this->response_time = $this->getstopwatch($sw);
        $this->response_bytes = strlen($content);
        return $this->processResponse($content);
    }
    
    public function getResponseTime() {
        return $this->response_time;
    }
    
    public function getResponseBytes() {
        return $this->response_bytes;
    }
    
    private function processResponse($rawjson) {
        $json = @json_decode($rawjson);
        if($json===false || is_null($json)) {
            if(!xxlserver()) echo "Result: ".$rawjson;
            throw new AdGlareException('Invalid JSON response from API server. Please contact Support.');
        }
        switch($this->response_format) {
            default: case "json": return $json; break;
            case "raw": return $rawjson; break;
        }
    }
    
    private function curlRequest($url,$arr_postdata) {
        if(!function_exists('curl_init')) throw new AdGlareException('Curl library not installed.');
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, false);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array("Authorization: Basic ".$this->public_key));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT,30); 
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($arr_postdata));
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curlHandle, CURLOPT_MAXREDIRS, 5);
        $content = curl_exec($curlHandle); 
        curl_close($curlHandle);         
        return $content;
    }    
    
    private function getstopwatch($t="") {
        return (empty($t)) ? microtime(true) : number_format(microtime(true)-$t, 5);
    }
}

class AdGlareException extends Exception {}

?>
