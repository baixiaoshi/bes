<?php

require_once APPPATH . 'models/c2c/base_model.php';

class Circle extends Base_Model {

    public function get_circle($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        foreach ($moment_ids_arr as $key => $mid) {
            if (!is_numeric($mid) || $mid <= 0) {
                throw new Exception('错误的参数类型');
            }
        }

        $moment_ids_len = count($moment_ids_arr);
        if ($moment_ids_len <= 0 || $moment_ids_len > 5000) {
            throw new Exception('商品ID数量必须在1至5000之间');
        }

        $_moment_ids_string = implode(',', $moment_ids_arr);
        $sql = "SELECT cc.`id`, cc.`name`, cc.`location_id`, cc.`introduction`, cc.`description`, ccm.`moment_id`, ccm.`is_display`, ccm.`circle_display` FROM `c2c_circle_moment` AS ccm LEFT JOIN `c2c_circle` AS cc ON (ccm.`circle_id` = cc.`id`) WHERE ccm.moment_id IN ({$_moment_ids_string})";
        return $this->execute($sql)->result();
    }
}