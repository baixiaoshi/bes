<?php

namespace beibei\service;

/**
 * Class C2cMomentService
 * @package beibei\service
 * @method static C2cMomentService get_instance()
 */
class C2cMomentService extends BaseService {

    const TYPE_STATUS = 0; // 照片
    const TYPE_ITEM = 1;   // 商品

    public function init() {
        $CI = &get_instance();
        load_model('c2c/moment');
        $this->moment = $CI->moment;
    }

    public function fetch_moment_from_mysql($last_id, $page_num) {

        if (!is_numeric($last_id) || !is_numeric($page_num) || $page_num <= 0) {
            return FALSE;
        }

        $moments = $this->moment->get_moment((int) $last_id, (int) $page_num);

        if (!$moments) {
            return FALSE;
        }

        return $moments;
    }

    public function fetch_product_id_from_mysql($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        $moment_product_ids = $this->moment->get_related_moment_ids($moment_ids_arr);

        if (!$moment_product_ids) {
            return FALSE;
        }

        $_related_moment_map = array();
        foreach ($moment_product_ids as $_key => $val) {
            if (!empty($val->related_ids)) {
                $_related_moment_map[$val->moment_id] = $val->related_ids;
            }
        }

        return $_related_moment_map;
    }

    public function fetch_comment_from_mysql($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        $comments = $this->moment->get_comment_count($moment_ids_arr);

        if (!$comments) {
            return FALSE;
        }

        $comments = result_to_map($comments, 'correlation_id');

        return $comments;
    }

    public function fetch_like_from_mysql($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        $likes = $this->moment->get_like_count($moment_ids_arr);

        if (!$likes) {
            return FALSE;
        }

        $likes = result_to_map($likes, 'moment_id');

        return $likes;
    }
}