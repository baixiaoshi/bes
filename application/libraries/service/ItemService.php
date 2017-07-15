<?php

namespace beibei\service;


class ItemService extends BaseService {

    public function init() {

        $CI = & get_instance();

        $CI->load->model('item');
        $this->item = $CI->item;
    }

    public function fetch_items_from_db($last_id, $page_num, $show = 'mart_show', $type = 1, $index_type = 'old')
    {

        $items = array();
        if ($show == 'mart_oversea') {
            $itemResult = $this->item->fetch_item_by_page_oversea($last_id, $page_num, $type, $index_type);
        } else if ($show == 'mart_tuan') {
            $itemResult = $this->item->fetch_item_by_page_tuan($last_id, $page_num, $type, $index_type);
        } else {
            $itemResult = $this->item->fetch_item_by_page_where($last_id, $page_num, $type, $index_type);
        }

        if ($itemResult) {
            return $itemResult;
        }

        return $items;
    }

    public function parse_product_id_iid($items) {

        $product_id_arr = array();
        $item_id_arr = array();

        foreach ($items as $item) {
            $product_id_arr[] = $item->product_id;
            $item_id_arr[] = $item->id;
        }

        return array($product_id_arr, $item_id_arr);
    }


}