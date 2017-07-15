<?php
class MZ_exception extends Exception {
    
    public static $C_APP_CALL_LIMIT = 1000;
    
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}