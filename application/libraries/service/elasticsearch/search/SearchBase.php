<?php

namespace beibei\service\elasticsearch\search;

use beibei\service\BaseService;

class SearchBase extends BaseService {

    public $esclient = NULL;

    public static $_INVALID_PARAMS       = 400;
    public static $_WRONG_ARGUMENT_TYPES = 401;
    public static $_UNKNOW_ERROR         = 404;
    public static $_NORMAL_STATUS        = 200;
    public static $_ES_INDEX_NOT_EXISTS  = 500;
    public static $_ES_TYPE_NOT_EXISTS   = 501;

    public static $ES_INDEX_MAP = array(
        'beibei'  => array('category', 'order'),
        'brand'   => array('brand_detail'),
        'item'    => array('show'),
        'product' => array('product'),
    );

    public function init() {
        $CI = & get_instance();
        $CI->load->library('ElastcaClient');
        $this->esclient = $CI->elastcaclient;
    }

    // 确认索引是否存在
    public function indices_exists($index) {

        if (!array_key_exists($index, self::$ES_INDEX_MAP)) {
            return FALSE;
        }

        $indexObject = new \Elastica\Index($this->esclient, $index);
        return $indexObject->exists();
    }

    // 结果集处理
    // 标准处理方法，可通用
    public function convert_data($res_data) {

        $resMap = array();

        foreach ($res_data as $docment) {

            if ($docment instanceof \Elastica\Result) {
                $resMap[$docment->getId()] = $docment->getData();
            } else {
                break;
            }
        }

        return $resMap;
    }
}