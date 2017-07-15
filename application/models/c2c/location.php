<?php

require_once APPPATH . 'models/c2c/base_model.php';

class Location extends Base_Model {

    public function get_location($moment_ids_arr) {

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

        $sql = "SELECT object_id, lon, lat, display_region from c2c_location WHERE object_id IN ({$_moment_ids_string})";
        return $this->execute($sql)->result();
    }
}