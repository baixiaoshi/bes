<?php
/*
 * Created on 2011-8-22
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class Context {
    
    private $_conext = array();
    
    public function put($key, $value) {
        $this->_conext[$key] = $value;
    }
    
    public function get_all() {
        return $this->_conext;
    }
    
    public function clear() {
    	$this->_conext = array();
    }

}
