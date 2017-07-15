<?php

namespace beibei\service;


class ProductService extends BaseService {

    public function init() {
        $CI = & get_instance();
        $CI->load->model('product');
        $this->product = $CI->product;
    }

    public function fetch_product_from_db($last_id, $page_num) {

        if (!is_numeric($last_id) || !is_numeric($page_num)) {
            return FALSE;
        }

        $product_array = array();

        $product_result = $this->product->fetch_product_by_page_where($last_id, $page_num);

        if ($product_result) {
            $product_array = $product_result;
        }

        return $product_array;
    }
}