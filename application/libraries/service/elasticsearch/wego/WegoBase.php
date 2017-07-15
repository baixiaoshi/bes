<?php

namespace beibei\service\elasticsearch\wego;

use beibei\service\BaseService;
use beibei\service\C2cCategoryService;
use beibei\service\C2cTalentService;


class WegoBase extends BaseService {

    const GET_DATA_FAIL_RETRY = 2;

    public static $_INVALID_PARAMS       = 400;
    public static $_WRONG_ARGUMENT_TYPES = 401;
    public static $_UNKNOW_ERROR         = 404;
    public static $_NORMAL_STATUS        = 200;

    public static $_ES_WRONG_ACTION      = 502;
    public static $_ES_INDEX_NOT_EXISTS  = 500;
    public static $_ES_TYPE_NOT_EXISTS   = 501;
    public static $ES_DOCUMENT_INDEX     = 'index';    // 更新或新增
    public static $ES_DOCUMENT_DELETE    = 'delete';   // 删除

    public static $BB_ES_PRODUCT_TYPE       = 'product';
    public static $BB_ES_PRODUCT_DRAFT_TYPE = 'product_draft';
    public static $BB_ES_SHOW_TYPE          = 'show';
    public static $BB_ES_SHOW_MIZHE_TYPE    = 'show_mizhe';
    public static $BB_ES_TUAN_TYPE          = 'tuan';
    public static $BB_ES_OVERSEA_TYPE       = 'oversea';
    public static $BB_ES_ORDER_TYPE         = 'order';
    public static $BB_ES_MART_SHOW_TYPE     = 'mart_show';
    public static $BB_ES_MART_TUAN_TYPE     = 'mart_tuan';
    public static $BB_ES_MART_OVERSEA_TYPE  = 'mart_oversea';

    public function init() {
        load_lib('elasticsearch');
        $this->elastic = $this->CI->elasticsearch;
        $this->categoryService = C2cCategoryService::get_instance();
        $this->talentService = C2cTalentService::get_instance();
        $this->categoryMap = $this->categoryService->fetch_category_from_mysql();
        $this->talentMap = $this->talentService->fetch_normal_talent_from_mysql();
    }

    public function comfirmIndexExists($index) {

        if (!$index) {
            throw new \Exception('错误的参数类型');
        }

        return $this->elastic->indices()->exists(array('index' => array($index)));
    }
}