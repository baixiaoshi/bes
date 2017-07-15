<?php

namespace beibei\service;


class MartshowService extends BaseService {

    protected $martshows = NULL;

    public function init() {
        $CI = & get_instance();
        $CI->load->model('mart_show');
        $this->martshow = $CI->mart_show;
    }

    public function fetch_data($last_id, $page_num, $type = 1) {

        $martshows = $this->fetch_martshow_from_mysql($last_id, $page_num, $type);

        if (!$martshows) {
            return FALSE;
        }

        return $martshows;
    }

    public function fetch_martshow_from_mysql($last_id, $page_num, $type = 1) {

        $martshows = $this->martshow->fetch_mart_show_by_page_where($last_id, $page_num, $type);
        $this->martshows = $martshows;
        return $martshows;
    }

    // 获取专场中所有商品类别
    public function fetch_item_cid_info_by_event_ids($category) {

        if (!$category) {
            return FALSE;
        }

        $event_id_arr = $this->parse_martshow_event_ids();

        if (!$event_id_arr || count($event_id_arr) <= 0) {
            return FALSE;
        }

        $result = $this->martshow->fetch_item_cid_info_by_event_ids($event_id_arr);

        if (!$result) {
            return FALSE;
        }

        $shows_item_cid_map = array();

        // cid relation
        foreach ($result as $show_item_cid_info) {

            $item_cid = $show_item_cid_info->cid;
            $event_id = $show_item_cid_info->event_id;

            if (array_key_exists($item_cid, $category)) {

                $_item_cid_array = $category[$item_cid];
                if (count($_item_cid_array) == 6) {
                    list($c1_id, $c2_id, $c3_id, $c1_name, $c2_name, $c3_name) = array_values($_item_cid_array);
                    if (!is_numeric($c3_id)) $c3_id = 0;
                    $_item_cids = array($c1_id, $c2_id, $c3_id);

                    if (array_key_exists($event_id, $shows_item_cid_map)) {
                        $shows_item_cid_map[$event_id] = array_unique(array_merge($shows_item_cid_map[$event_id], $_item_cids));
                    } else {
                        $shows_item_cid_map[$event_id] = $_item_cids;
                    }
                }
            }
        }

        return $shows_item_cid_map;
    }

    // 取出专场商品的聚合信息
    // 最小价格，最小折扣，商品总数
    public function fetch_item_aggr_by_event_id() {

        $event_id_arr = $this->parse_martshow_event_ids();

        if (!$event_id_arr || count($event_id_arr) <= 0) {
            return FALSE;
        }

        $result = $this->martshow->fetch_item_info_by_event_ids($event_id_arr);

        if (!$result) {
            return FALSE;
        }

        $result = result_to_map($result, 'event_id');

        return $result;
    }

    // 解析出所有专场的id数据
    private function parse_martshow_event_ids() {

        if (!$this->martshows) {
            return FALSE;
        }

        $event_ids = array();
        foreach ($this->martshows as $martshow) {
            $event_ids[] = $martshow->id;
        }

        return $event_ids;
    }
}