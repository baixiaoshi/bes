<?php
/**
 * @property Httpsqs $httpsqs
 */

class Async_email {
    private $EMAIL_QUEUE_NAME = 'email';
    private $httpsqs = NULL;
    
    public function __construct() {
        $CI = & get_instance();
        $CI->load->library('httpsqs');
        $this->httpsqs = & $CI->httpsqs;
    }
    
    public function push($uid, $email, $type) {
        $msg = $uid . '$$' . $email . '$$' . $type;
        return $this->httpsqs->put($this->EMAIL_QUEUE_NAME, $msg);
    }
    
    public function pop() {
        $result = $this->httpsqs->get($this->EMAIL_QUEUE_NAME);
        if ($result != NULL) {
            return explode('$$', $result);
        } else {
            return NULL;
        }
    }
}