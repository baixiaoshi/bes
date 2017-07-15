<?php

$config['elastica'] = array(
    'servers' => array(
        array('host' => '10.2.4.14', 'port' => 9200, 'roundRobin' => TRUE, 'timeout' => 5),
        array('host' => '10.2.4.15', 'port' => 9200, 'roundRobin' => TRUE, 'timeout' => 5),
        array('host' => '10.2.4.16', 'port' => 9200, 'roundRobin' => TRUE, 'timeout' => 5),
    )
);