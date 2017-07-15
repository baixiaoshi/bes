<?php

class MZ_Security extends CI_Security {

    /**
     * 支持配置跳过CSRF检测的路径
     * 
     * @see CI_Security::csrf_verify()
     */
    public function csrf_verify() {
        // If no POST data exists we will set the CSRF cookie
        if (count($_POST) == 0) {
            return $this->csrf_set_cookie();
        }
        
        // Do the tokens exist in _POST?
        if (!isset($_POST[$this->_csrf_token_name])) {
            // let's see if we should skip CSRF check add by lsave
            $path = $_SERVER['REQUEST_URI'];
            if (strpos($path, '/outer') === 0 || strpos($path, '/tool') === 0 || strpos($path, '/gateway') === 0) {
                // yes we should ship.
                

                // We kill this since we're done and we don't want to
                // polute the _POST array
                unset($_POST[$this->_csrf_token_name]);
                
                // Nothing should last forever
                unset($_COOKIE[$this->_csrf_cookie_name]);
                $this->_csrf_set_hash();
                $this->csrf_set_cookie();
                
                log_message('debug', "CSRF token verified ");
                return $this;
            }
        }
        
        return parent::csrf_verify();
    }
    
    public function csrf_show_error() {
        show_error('请求已过期，请刷新后重试');
    }

    /**
     * 支持跳过编辑器style的过滤
     * 
     * @see CI_Security::_remove_evil_attributes()
     */
    protected function _remove_evil_attributes($str, $is_image) {
        // All javascript event handlers (e.g. onload, onclick, onmouseover), style, and xmlns
        $evil_attributes = array('on\w*', 'style', 'xmlns');
        // add by lsave
        $path = $_SERVER['REQUEST_URI'];
        if (strpos($path, '/item/seller_') === 0) {
            $evil_attributes = array('on\w*', 'xmlns');
        }
        
        if ($is_image === TRUE) {
            /*
			 * Adobe Photoshop puts XML metadata into JFIF images, 
			 * including namespacing, so we have to allow this for images.
			 */
            unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
        }
        
        do {
            $str = preg_replace(
                            "#<(/?[^><]+?)([^A-Za-z\-])(" . implode('|', $evil_attributes) .
                                             ")(\s*=\s*)([\"][^>]*?[\"]|[\'][^>]*?[\']|[^>]*?)([\s><])([><]*)#i", "<$1$6", $str, -1, $count);
        } while ($count);
        
        return $str;
    }
}