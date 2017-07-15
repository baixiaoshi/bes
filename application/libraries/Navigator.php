<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
    /*
 * Created on 2011-8-23
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class Navigator {
    
    private $_target;
    private $_output_format;
    private $_content_type;
    private $_break;
    protected $_supported_formats = array('xml' => 'application/xml', 
            'rawxml' => 'application/xml', 
            'json' => 'application/json', 
            'jsonp' => 'application/javascript', 
            'serialize' => 'application/vnd.php.serialized', 
            'php' => 'text/plain', 
            'html' => 'text/html', 
            'csv' => 'application/csv');
    
    public function get_target() {
        return $this->_target;
    }
    
    public function set_target($target) {
        $this->_target = $target;
    }
    
    public function get_output_format() {
        return $this->_output_format;
    }
    
    public function set_output_format($format) {
        $this->_output_format = $format;
        $this->_content_type = $this->_supported_formats[$format];
    }
    
    public function get_content_type() {
        return $this->_content_type;
    }
    
    public function break_render() {
        $this->_break = TRUE;
    }
    
    public function is_breaked() {
        return isset($this->_break) && $this->_break == TRUE;
    }

}
