<?php

require_once APPPATH . 'models/c2c/base_model.php';

class Talent extends Base_Model {

    const TALENT_STATUS_NORMAL = 0;

    public function get_all_normal_talent() {
        $sql = 'select uid from c2c_talent where status = ?';
        return $this->execute($sql, array(self::TALENT_STATUS_NORMAL))->result();
    }
}