<?php
class Httpsqs {
    private $host;
    private $port;
    private $auth;
    private $charset;
    private static $sockets = array();
    public function __construct($charset = 'utf-8') {
        $CI = & get_instance();
        $this->host = $CI->config->item('HTTPSQS_HOST');
        $this->port = $CI->config->item('HTTPSQS_PORT');
        $this->auth = $CI->config->item('HTTPSQS_AUTH');
        $this->charset = $charset;
    }
    
    public function get($name, $kt = 0) {
        $query = $this->build_query($name, 'get');
        $r = $this->execute($query, 'GET', '', $kt);
        return $r && $r['data'] === 'HTTPSQS_GET_END' ? NULL : $r['data'];
    }
    
    public function put($name, $body, $kt = 0) {
        $query = $this->build_query($name, 'put');
        $r = $this->execute($query, 'POST', $body, $kt);
        return $r && $r['data'] === 'HTTPSQS_PUT_OK' ? TRUE : FALSE;
    }
    
    public function reset($name) {
        $query = $this->build_query($name, 'reset');
        $r = $this->execute($query);
        if ($r && $r['data'] == "HTTPSQS_RESET_OK") {
            return true;
        } else
            return false;
    }
    
    public function status($name, $type = 'text') {
        if ($type == 'json') {
            $cmd = 'status_json';
        } else {
            $cmd = 'status';
        }
        $query = $this->build_query($name, $cmd);
        $r = $this->execute($query);
        if ($r == false || $r['data'] == false || $r['data'] == 'HTTPSQS_ERROR') {
            return false;
        }
        return $r['data'];
    }
    
    public function view($name, $pos) {
        $query = $this->build_query($name, 'view', array("pos=" . $pos));
        $r = $this->execute($query);
        if ($r == false || $r['data'] == false || $r['data'] == 'HTTPSQS_ERROR') {
            return false;
        } else
            return $r['data'];
    }
    
    private function build_query($name, $cmd, $extra = array()) {
        $tmp = array("name=" . $name, "opt=" . $cmd);
        if ($this->auth) {
            $tmp[] = "auth=" . $this->auth;
        }
        $tmp[] = "charset=" . $this->charset;
        $tmp = array_merge($tmp, $extra);
        return '/?' . implode('&', $tmp);
    }
    
    private function get_content($query) {
        return get_url_content('http://' . $this->host . ':' . $this->port . $query);
    }
    
    private function execute($query, $type = 'GET', $body = '', $kt = 0) {
        if ($kt == 1) {
            $key = md5($this->host . ':' . $this->port);
            if (!isset(self::$sockets[$key])) {
                self::$sockets[$key] = fsockopen($this->host, $this->port, $error, $errstr, 5);
            }
            $socket = self::$sockets[$key];
        } else {
            $socket = fsockopen($this->host, $this->port, $error, $errstr, 5);
        }
        if (!$socket) {
            return false;
        }
        $out = "{$type} {$query} HTTP/1.1\r\n";
        $out .= "Host: {$this->host}\r\n";
        if ($type == 'POST') {
            $out .= "Content-Length: " . strlen($body) . "\r\n";
        }
        if ($kt == 0) {
            $out .= "Connection: close\r\n";
        } else {
            $out .= "Connection: Keep-Alive\r\n";
        }
        $out .= "\r\n";
        if ($type == 'POST' && $body) {
            $out .= $body;
        }
        fwrite($socket, $out);
        $line = trim(fgets($socket));
        if (!empty ($line)) {
            list($proto, $rcode, $result) = explode(" ", $line);
        }
        $len = -1;
        $pos_value = 0;
        while (($line = trim(fgets($socket))) != "") {
            if (strstr($line, 'Content-Length:')) {
                list($cl, $len) = explode(" ", $line);
            } elseif (strstr($line, "Pos:")) {
                list($pos_key, $pos_value) = explode(" ", $line);
            }
        }
        if ($len < 0) {
            $chunk_size = (integer) hexdec(fgets($socket, 4096));
            $bodyContent = '';
            while (!feof($socket) && $chunk_size > 0) {
                $bodyContent .= fread($socket, $chunk_size);
                fread($socket, 2); // skip /r/n
                $chunk_size = (integer) hexdec(fgets($socket, 4096));
            }
            return array('pos' => intval($pos_value), 'data' => $bodyContent);
        }
        $body = @fread($socket, $len);
        if ($kt == 0) {
            fclose($socket);
        }
        return array('pos' => intval($pos_value), 'data' => $body);
    }
}