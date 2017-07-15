<?php

use Elastica\Client;


class ElastcaClient extends Client {
    
    public function __construct() {

        $CI = & get_instance();
        $CI->config->load('elastica');
        $elsconfig = config_item('elastica');

        if (!is_array($elsconfig)) {
            exit('The elasticsearch connection params is not set correctly.');
        }

        parent::__construct($elsconfig);
    }
}
