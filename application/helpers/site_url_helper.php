<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function request_url() {
    $url = 'http://' . $_SERVER['HTTP_HOST'];
    if (!empty($_SERVER['REQUEST_URI'])) {
        $url .= $_SERVER['REQUEST_URI'];
    }
    return $url;
}

function get_url($name, $params = array()) {
    return get_uri_placeholder(get_instance()->config->item($name), $params);
}

/**
 * 获取页面地址
 * @param $path
 */
function page_url($path = '') {
    return base_url() . $path;
}

function redmine_url($path = '') {
    return config_item('redmine_url') . $path;
}

function image_url($path = '') {
    return config_item('image_url') . 'app/common/image/' . $path;
}

function style_url($path = '') {
    return config_item('style_url') . 'app/common/css/' . $path;
}

function script_url($path = '') {
    return config_item('script_url') . 'app/common/js/' . $path;
}

function base64_url_encode($data) {
    return strtr(rtrim(base64_encode($data), '='), '+/', '-_');
}

function base64_url_decode($base64) {
    return base64_decode(strtr($base64, '-_', '+/'));
}

function get_uri_placeholder($fullpath, $init_query_data) {
    $uri = new Uri_with_placeholder($fullpath, $init_query_data);
    return $uri;
}

class Uri_with_placeholder {
    
    private $ori_path;
    private $init_query_data;
    private $query_data = array();
    
    function __construct($fullpath = '', $init_query_data = array()) {
        $this->ori_path = $fullpath;
        $this->init_query_data = $init_query_data;
    }
    
    public function add_query($key, $value) {
        $this->query_data[$key] = $value;
        return $this;
    }
    
    public function render($reset = FALSE) {
        $final_query_data = array_merge($this->init_query_data, $this->query_data);
        $keys = array_keys($final_query_data);
        $patterns = array();
        foreach ($keys as $key) {
            $patterns[] = '/\(' . $key . '\)/';
        }
        $values = array_values($final_query_data);
        $dest = preg_replace($patterns, $values, $this->ori_path);
        $dest = preg_replace('/\((\w+)\)/', '', $dest);
        
        if ($reset) {
            unset($this->query_data);
            $this->query_data = array();
        }
        
        return $dest;
    }

}