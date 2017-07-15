<?php

namespace beibei\service;

/**
 * Class C2cCircleService
 * @package beibei\service
 * @method static C2cCircleService get_instance()
 */
class C2cCircleService extends BaseService {

    public function init() {
        $CI = &get_instance();
        load_model('c2c/circle');
        $this->circle = $CI->circle;
    }

    public function fetch_circle_from_mysql($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        $circles = $this->circle->get_circle($moment_ids_arr);

        if (!$circles) {
            return FALSE;
        }

        $circles_map = array();
        foreach ($circles as $circle) {
            $_clone_clircle = clone $circle;
            unset($_clone_clircle->moment_id);
            $_clone_clircle->id = (int) $_clone_clircle->id;
            $_clone_clircle->location_id = (int) $_clone_clircle->location_id;
            $_clone_clircle->is_display = (int) $_clone_clircle->is_display;
            $_clone_clircle->circle_display = (int) $_clone_clircle->circle_display;
            $circles_map[$circle->moment_id][] = $_clone_clircle;
        }

        return $circles_map;
    }
}