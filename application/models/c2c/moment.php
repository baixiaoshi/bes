<?php

require_once APPPATH . 'models/c2c/base_model.php';


class Moment extends Base_Model {

    public function get_moment($last_id, $page_num = 1000) {

        if (!is_numeric($last_id) || !is_numeric($page_num) || $page_num <= 0) {
            return FALSE;
        }

        $last_id = (int) $last_id;
        $page_num = (int) $page_num;

        $sql = "SELECT * FROM c2c_moment WHERE id > ? ORDER BY id ASC LIMIT ?";
        return $this->execute($sql, array($last_id, $page_num))->result();
    }

    public function get_comment_count($moment_ids_arr) {

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
            throw new Exception('MID数量必须在1至5000之间');
        }

        $_moment_ids_string = implode(',', $moment_ids_arr);
        $sql = "SELECT count(*) AS count, correlation_id FROM c2c_comment WHERE `status` = 0 AND `review_status` > -1 AND `correlation_id` IN ({$_moment_ids_string}) GROUP BY correlation_id";
        return $this->execute($sql)->result();
    }

    public function get_like_count($moment_ids_arr) {

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
            throw new Exception('MID数量必须在1至5000之间');
        }

        $_moment_ids_string = implode(',', $moment_ids_arr);
        $sql = "SELECT count(*) AS count, `moment_id` FROM `c2c_like` WHERE `status` = 0 AND `moment_id` IN ({$_moment_ids_string}) GROUP BY `moment_id`";
        return $this->execute($sql)->result();
    }

    public function get_related_moment_ids($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        foreach ($moment_ids_arr as $key => $mid) {
            if (!is_numeric($mid) || $mid <= 0) {
                throw new Exception('错误的参数类型');
            }
        }

        $_moment_ids_string = implode(',', $moment_ids_arr);
        $sql = "SELECT `moment_id`, `related_ids` FROM c2c_related_moments WHERE moment_id IN({$_moment_ids_string})";
        return $this->execute($sql)->result();
    }
}