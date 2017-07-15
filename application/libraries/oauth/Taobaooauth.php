<?php
require_once APPPATH . '/libraries/oauth/Oauth.php';
require_once APPPATH . '/libraries/topsdk/TopSdk.php';
class TaobaoOauth {
    
    public $appkey;
    public $secretKey;
    
    /** 
     * Contains the last HTTP status code returned.  
     * 
     * @ignore 
     */
    public $http_code;
    /** 
     * Contains the last API call. 
     * 
     * @ignore 
     */
    public $url;
    /** 
     * Set up the API root URL. 
     * 
     * @ignore 
     */
    public $host = "https://oauth.taobao.com/";
    /** 
     * Set timeout default. 
     * 
     * @ignore 
     */
    public $timeout = 30;
    /**  
     * Set connect timeout. 
     * 
     * @ignore 
     */
    public $connecttimeout = 30;
    /** 
     * Verify SSL Cert. 
     * 
     * @ignore 
     */
    public $ssl_verifypeer = FALSE;
    /** 
     * Respons format. 
     * 
     * @ignore 
     */
    public $format = 'json';
    /** 
     * Decode returned json data. 
     * 
     * @ignore 
     */
    public $decode_json = TRUE;
    
    /** 
     * Contains the last HTTP headers returned. 
     * 
     * @ignore 
     */
    public $http_info;
    /** 
     * Set the useragnet. 
     * 
     * @ignore 
     */
    public $useragent = 'MZ OAuth v0.1';
    
    function requestIdentifyURL() {
        return 'http://container.api.taobao.com/container/identify';
    }
    
    function requestCodeURL() {
        return 'https://oauth.taobao.com/authorize';
    }
    
    function requestAccessURL() {
        return 'https://oauth.taobao.com/token';
    }
    
    function __construct($appkey = NULL, $secretKey = NULL) {
        $this->appkey = $appkey;
        $this->secretKey = $secretKey;
    }
    
    function getIdentifyRequest() {
        $params = array("app_key" => $this->appkey, "sign_method" => "md5", "timestamp" => date('Y-m-d H:i:s'));
        
        $sign = $this->generateSign($params);
        $params["sign"] = $sign;
        
        $request = new OAuthRequest("GET", $this->requestIdentifyURL(), $params);
        return $request;
    }
    
    function getTopIdentifyRequest() {
        $params = array("appkey" => $this->appkey, "encode" => "utf-8", "timestamp" => date('Y-m-d H:i:s'));
        
        $request = new OAuthRequest("GET", 'http://container.api.taobao.com/container', $params);
        return $request;
    }
    
    function refreshToken($refresh_token, $session) {
        $params = array("appkey" => $this->appkey, "refresh_token" => $refresh_token, "sessionkey" => $session);
        
        $sign = $this->generateSign($params);
        $params["sign"] = $sign;
        $request = new OAuthRequest("GET", 'http://container.open.taobao.com/container/refresh', $params);
        $resp = $this->http($request->to_url(), 'GET');
        return json_decode($resp);
    }
    
    function getCodeRequest($oauth_callback = NULL) {
        
        $request = new OAuthRequest("GET", $this->requestCodeURL(), array("response_type" => "code", 
                "client_id" => $this->appkey, 
                "redirect_uri" => empty($oauth_callback) ? base_url() . "openid/taobao/callback" : $oauth_callback));
        return $request;
    }
    
    function getAccessRequest($code, $oauth_callback = NULL) {
        $request = new OAuthRequest("POST", $this->requestAccessURL(), array("grant_type" => "authorization_code", 
                "code" => $code, 
                "client_id" => $this->appkey, 
                "client_secret" => $this->secretKey, 
                "redirect_uri" => empty($oauth_callback) ? base_url() . "openid/taobao/callback" : $oauth_callback));
        return $request;
    }
    
    function generateSign($params) {
        ksort($params);
        
        $stringToBeSigned = '';
//        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
//        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;
        
        return strtoupper(md5($stringToBeSigned));
    }
    
    /** 
     * Make an HTTP request 
     * 
     * @return string API results 
     */
    function http($url, $method, $postfields = NULL, $multi = false) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
        
        $header_array = array();
        
        $header_array2 = array();
        if ($multi)
            $header_array2 = array("Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary, "Expect: ");
        foreach ($header_array as $k => $v)
            array_push($header_array2, $k . ': ' . $v);
        
        curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array2);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        
        //echo $url."<hr/>"; 
        

        curl_setopt($ci, CURLOPT_URL, $url);
        
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        
        //echo '=====info====='."\r\n";
        //print_r( curl_getinfo($ci) ); 
        

        //echo '=====$response====='."\r\n";
        //print_r( $response ); 
        

        curl_close($ci);
        return $response;
    }
    
    /** 
     * Get the header info to store. 
     * 
     * @return int 
     */
    function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

}