<?php

namespace beibei\service;

/**
 * Class C2cTalentService
 * @package beibei\service
 * @method static C2cTalentService get_instance()
 */
class C2cTalentService extends BaseService {

    public function init() {
        $CI = &get_instance();
        load_model('c2c/talent');
        $this->talent = $CI->talent;
    }

    public function fetch_normal_talent_from_mysql() {

        $talents = $this->talent->get_all_normal_talent();

        if (!$talents) {
            return FALSE;
        }

        $talent_map = array();
        foreach ($talents as $talent) {
            $talent_map[$talent->uid] = $talent->uid;
        }

        return $talent_map;
    }
}