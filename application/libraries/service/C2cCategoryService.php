<?php

namespace beibei\service;

/**
 * Class C2cCategoryService
 * @package beibei\service
 * @method static C2cCategoryService get_instance()
 */
class C2cCategoryService extends BaseService {

    const IS_PARENT = 1;
    const IS_NOT_PARENT = 0;

    public function init() {
        $CI = &get_instance();
        load_model('c2c/category');
        $this->category = $CI->category;
    }

    public function fetch_category_from_mysql() {

        $category_array = $this->category->get_all();

        if (!$category_array) {
            return FALSE;
        }

        $category_map = array();
        $last_cate = array();
        foreach ($category_array as $category) {
            $category_map[$category->id] = $category;
            if ($category->is_parent == self::IS_NOT_PARENT) {
                $last_cate[] = $category;
            }
        }

        $_catemap = array();
        foreach ($last_cate as $cate) {
            $_cate_temp = array();
            $_cate_temp['cid'] = (int) $cate->id;
            $_cate_temp['cname'] = $cate->name;
            $_cate_temp['parent_id'] = (int) $cate->parent_id;
            $_cate_temp['is_parent'] = (int) $cate->is_parent;
            $_catemap[$cate->id][] = $_cate_temp;

            if ($cate->parent_id) {
                if (isset($category_map[$cate->parent_id])) {
                    $_second_cate = $category_map[$cate->parent_id];
                    $_cate_temp = array();
                    $_cate_temp['cid'] =  (int) $_second_cate->id;
                    $_cate_temp['cname'] = $_second_cate->name;
                    $_cate_temp['parent_id'] = (int) $_second_cate->parent_id;
                    $_cate_temp['is_parent'] = (int) $_second_cate->is_parent;
                    $_catemap[$cate->id][] = $_cate_temp;
                    if (isset($category_map[$_second_cate->parent_id])) {
                        $_last_cate = $category_map[$_second_cate->parent_id];
                        $_cate_temp = array();
                        $_cate_temp['cid'] = (int) $_last_cate->id;
                        $_cate_temp['cname'] = $_last_cate->name;
                        $_cate_temp['parent_id'] = (int) $_last_cate->parent_id;
                        $_cate_temp['is_parent'] = (int) $_last_cate->is_parent;
                        $_catemap[$cate->id][] = $_cate_temp;
                    }
                }
            }
        }

        return $_catemap;
    }
}