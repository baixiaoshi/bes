<?php

namespace beibei\service\elasticsearch\search;

use beibei\service\elasticsearch\search\SearchBase;


class CategorySearchService extends SearchBase {

    public static $ES_INDEX = 'beibei';
    public static $ES_TYPE  = 'category';

    // 功能：取出所有类目
    // NOTE:和db中的结构不同，使用场景不同于CategoryService中get_all
    // 返回的结果是一个以叶子类目为key的数组
    // 使用此方法请先经过场景review，勿随意使用
    public function fetch_all_record() {

        $retobj = new \stdClass();
        $retobj->status = self::$_UNKNOW_ERROR;
        $retobj->total_found = 0;
        $retobj->message = '';
        $retobj->data = array();

        if (!$this->indices_exists(self::$ES_INDEX)) {
            $retobj->status = self::$_ES_INDEX_NOT_EXISTS;
            $retobj->message = '索引不存在';
            return $retobj;
        }

        $query_array = array();
        $query_array['size'] = 5000;

        $search = new \Elastica\Search($this->esclient);
        $result_set = $search->addIndex(self::$ES_INDEX)->addType(self::$ES_TYPE)->search($query_array);
        $total_hits = $result_set->getTotalHits();
        $total_data = $result_set->getResults();

        $retobj->status = self::$_NORMAL_STATUS;

        if ($total_hits <= 0) {
            return $retobj;
        }

        $retobj->total_found = $total_hits;
        $retobj->data = $this->convert_data($total_data);

        return $retobj;
    }

    // 根据给定cid获取顶级类目信息
    // 支持批量
    public function fetch_root_cate_by_cid($cid) {

        $retobj = new \stdClass();
        $retobj->status = self::$_UNKNOW_ERROR;
        $retobj->total_found = 0;
        $retobj->message = '';
        $retobj->data = array();

        if (!is_numeric($cid)) {
            $retobj->message = '错误的参数类型';
            $retobj->status = self::$_WRONG_ARGUMENT_TYPES;
            return $retobj;
        }

        $query_array = array();
        $query_array['query']['filtered']['filter']['or'][]['term']['c2_id'] = $cid;
        $query_array['query']['filtered']['filter']['or'][]['term']['c3_id'] = $cid;
        $query_array['size'] = 1000;
        $query_array['sort'] = array('_id' => 'asc');

        $search = new \Elastica\Search($this->esclient);
        $result_set = $search->addIndex(self::$ES_INDEX)->addType(self::$ES_TYPE)->search($query_array);

        $total_hits = $result_set->getTotalHits();
        $total_data = $result_set->getResults();

        if ($total_hits <= 0) {
            return $retobj;
        }

        $_pong_data = array();
        foreach ($total_data as $docment) {
            if ($docment instanceof \Elastica\Result) {
                $_cat_data = array_values($docment->getData());
                $_cat_id = $docment->getId();
                if ($total_hits == 1) {
                    $_pong_data = $this->formate_data_source($_cat_data, 'c1');
                } else {
                    $_pong_data[] = $this->formate_data_source($_cat_data, 'c1');
                }
            } else {
                break;
            }
        }

        $retobj->status = self::$_NORMAL_STATUS;
        $retobj->total_found = $total_hits;
        $retobj->data = $_pong_data;
        return $retobj;
    }

    // 可以是任意一级的cid，返回所有层级类目信息
    public function fetch_by_cid($cid) {

        $retobj = new \stdClass();
        $retobj->status = self::$_UNKNOW_ERROR;
        $retobj->total_found = 0;
        $retobj->message = '';
        $retobj->data = array();

        if (!is_numeric($cid)) {
            $retobj->message = '错误的参数类型';
            $retobj->status = self::$_WRONG_ARGUMENT_TYPES;
            return $retobj;
        }

        $query_array = array();
        $query_array['query']['filtered']['filter']['or'][]['term']['c1_id'] = $cid;
        $query_array['query']['filtered']['filter']['or'][]['term']['c2_id'] = $cid;
        $query_array['query']['filtered']['filter']['or'][]['term']['c3_id'] = $cid;
        $query_array['size'] = 1000;
        $query_array['sort'] = array('_id' => 'asc');

        $search = new \Elastica\Search($this->esclient);
        $result_set = $search->addIndex(self::$ES_INDEX)->addType(self::$ES_TYPE)->search($query_array);

        $total_hits = $result_set->getTotalHits();
        $total_data = $result_set->getResults();

        if ($total_hits <= 0) {
            return $retobj;
        }

        $_pong_data = array();
        foreach ($total_data as $docment) {
            if ($docment instanceof \Elastica\Result) {
                $_cat_data = array_values($docment->getData());
                $_cat_id = $docment->getId();
                if ($total_hits == 1) {
                    $_pong_data = $this->formate_data_source($_cat_data);
                } else {
                    $_pong_data[] = $this->formate_data_source($_cat_data);
                }
            } else {
                break;
            }
        }

        $retobj->status = self::$_NORMAL_STATUS;
        $retobj->total_found = $total_hits;
        $retobj->data = $_pong_data;
        return $retobj;
    }

    // $field = 'all', 'c1', 'c2', 'c3'
    public function formate_data_source($_cat_data, $field = 'all') {

        if (!is_array($_cat_data) || count($_cat_data) != 6) {
            return FALSE;
        }

        list($c1_id, $c2_id, $c3_id, $c1_name, $c2_name, $c3_name) = $_cat_data;

        if ($field == 'all') {
            $retdata = array(
                'c1' => array(
                    'cid' => $c1_id,
                    'cname' => $c1_name
                ),
                'c2' => array(
                    'cid' => $c2_id,
                    'cname' => $c2_name
                ),
                'c3' => array(
                    'cid' => $c3_id,
                    'cname' => $c3_name
                )
            );
        } elseif ($field == 'c1') {
            $retdata = array(
                'cid' => $c1_id,
                'cname' => $c1_name
            );
        } elseif ($field == 'c2') {
            $retdata = array(
                'cid' => $c1_id,
                'cname' => $c1_name
            );
        } elseif ($field == 'c3') {
            $retdata = array(
                'cid' => $c1_id,
                'cname' => $c1_name
            );
        }

        return $retdata;
    }
}