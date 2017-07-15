<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Monolog extends Logger {

    public function __construct($params) {

        $CI = & get_instance();
        $this->log_path = $CI->config->item('INDEX_LOG_PATH');

        if (!is_array($params) || !array_key_exists('name', $params)) {
            exit('Invalid argument for init monolog class');
        }

        $name = $params['name'];
        $this->name = $name;
        $this->logFileName = $this->generateFileName($name);
        $_pushHandle = new StreamHandler($this->logFileName, Logger::INFO);
        parent::__construct($this->name, array($_pushHandle));
    }

    public function generateFileName($logName) {
        $params = array();
        $params[] = date('Y-m-d');
        $params[] = $logName;
        $params[] = 'log';
        $logFullPath = implode('.', $params);
        return $this->log_path . $logFullPath;
    }
}