<?php

use Elasticsearch\Client;


class ElasticSearch extends Client {

    public function __construct() {

        $CI = & get_instance();
        $CI->config->load('elasticsearch');
        $ElasticSearchconnParams = config_item('elasticsearch');


        if (!is_array($ElasticSearchconnParams)) {
            exit('elasticserch connect params is not set correctly.');
        }

        parent::__construct($ElasticSearchconnParams);
    }
}