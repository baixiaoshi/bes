<?php

class MZ_Autologin_hook {
    public function login() {
        $CI = & get_instance();
        $path = $_SERVER['REQUEST_URI'];
    }

    private function isNotException($uri) {
        $exceptions = $this->getExceptionURI();
        $isFound = FALSE;
        foreach ($exceptions as $e) {
            if (strpos($uri, $e) !== FALSE) {
                $isFound = TRUE;
                break;    
            }
        }
        if ($isFound) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    private function getExceptionURI() {
        return array(
            '/deployment/mergecallback',
            '/deployment/packagetagcallback',
            '/deployment/predeploycallback',
            '/deployment/deployonlinecallback',
            '/deployment/autouitestcallback',
            '/deployment/developing_projects'
        );
    }
}
