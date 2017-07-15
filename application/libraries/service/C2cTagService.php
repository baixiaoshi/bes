<?php

namespace beibei\service;

/**
 * Class C2cTagService
 * @package beibei\service
 * @method static C2cTagService get_instance()
 */
class C2cTagService extends BaseService {

    public function init() {
        $CI = &get_instance();
        load_model('c2c/tag');
        $this->tag = $CI->tag;
    }

    public function fetch_tag_from_mysql($moment_ids_arr) {

        if (!is_array($moment_ids_arr) || count($moment_ids_arr) <= 0) {
            return FALSE;
        }

        $tags = $this->tag->get_tag($moment_ids_arr);

        if (!$tags) {
            return FALSE;
        }

        $tags_map = array();
        foreach ($tags as $tag) {
            $_clone_tag = clone $tag;
            unset($_clone_tag->moment_id);
            $_clone_tag->id = (int) $_clone_tag->id;
            $_clone_tag->img_id = (int) $_clone_tag->img_id;
            $_clone_tag->tag_id = (int) $_clone_tag->tag_id;
            $_clone_tag->x = (int) $_clone_tag->x;
            $_clone_tag->y = (int) $_clone_tag->y;
            $tags_map[$tag->moment_id][] = $_clone_tag;
        }

        return $tags_map;
    }
}