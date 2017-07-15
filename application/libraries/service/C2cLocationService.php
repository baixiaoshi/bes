<?php

namespace beibei\service;

/**
 * Class C2cLocationService
 * @package beibei\service
 * @method static C2cLocationService get_instance()
 */
class C2cLocationService extends BaseService {

    public function init() {
        $CI = &get_instance();
        load_model('c2c/location');
        $this->location = $CI->location;
    }

    public function fetch_location_from_mysql($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        $locations = $this->location->get_location($moment_ids_arr);

        if (!$locations) {
            return FALSE;
        }

        $locations = result_to_map($locations, 'object_id');

        return $locations;
    }
}