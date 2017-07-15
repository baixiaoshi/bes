<?php

namespace beibei\service\elasticsearch\wego;

use beibei\service\C2cCircleService;
use beibei\service\C2cTagService;
use beibei\service\elasticsearch\wego\WegoBase;
use beibei\service\C2cMomentService;
use beibei\service\C2cProductService;
use beibei\service\C2cLocationService;


/**
 * Class WegoMomentIndexService
 * @package beibei\service\elasticsearch\wego
 * @method static WegoMomentIndexService get_instance()
 */
class WegoMomentIndexService extends WegoBase {

    public $lastid = 0;

    const WEGO_MOMENT_INDEX = 'quaner_v1';
    const WEGO_MOMENT_INDEX_TYPE = 'moment';
    const WEGO_MOMENT_INDEX_LOG_NAME = 'wego.moment';

    public static $_ES_INDEX_SHARDS = 5;
    public static $_ES_INDEX_REPLICAS = 0;

    public function init() {
        parent::init();
        $CI = &get_instance();
        $CI->load->library('Monolog', array('name' => self::WEGO_MOMENT_INDEX_LOG_NAME));
        $this->logger = $CI->monolog;
        $this->momentService = C2cMomentService::get_instance();
        $this->productService = C2cProductService::get_instance();
        $this->locationService = C2cLocationService::get_instance();
        $this->circleService = C2cCircleService::get_instance();
        $this->tagService = C2cTagService::get_instance();
    }

    public function getLastId() {
        return $this->lastid;
    }

    public function buildDataToEs($last_id = 0, $page_num = 1000) {

        // 检查索引是否存在
        $indexExists = $this->comfirmIndexExists(self::WEGO_MOMENT_INDEX);

        // 不存在创建索引
        if (!$indexExists) {
            $this->createIndex();
        }

        // 获取索引数据
        $moments = $this->momentService->fetch_moment_from_mysql($last_id, $page_num);
        if (!$moments) {
            return FALSE;
        }

        $_moments_bulk = $this->dataSource($moments);

        // 是否结束
        if (!$_moments_bulk) {
            return FALSE;
        }

        // 提交索引
        try {
            $this->elastic->bulk($_moments_bulk);
        } catch (\Exception $e) {
            return FALSE;
        }

        return TRUE;
    }

    // 创建索引
    public function createIndex() {

        $params = array();
        $params['index'] = self::WEGO_MOMENT_INDEX;
        $params['body']['settings']['number_of_shards'] = self::$_ES_INDEX_SHARDS;
        $params['body']['settings']['number_of_replicas'] = self::$_ES_INDEX_REPLICAS;
        $params['body']['mappings'][self::WEGO_MOMENT_INDEX_TYPE] = $this->mappingInfo();

        if (!$this->elastic->indices()->create($params)) {
            $this->logger->addInfo('Create item index fail.');
            return FALSE;
        }

        $this->logger->addInfo('create index success');
        $this->logger->addInfo(self::WEGO_MOMENT_INDEX);
        return TRUE;
    }

    // 设置映射
    public function mappingInfo() {
        $mapping = array();
        $mapping['_source'] = array('enabled' => TRUE);
        $mapping['properties']['review_status'] = array('type' => 'integer');
        $mapping['properties']['is_hot'] = array('type' => 'integer');
        $mapping['properties']['show_location'] = array('type' => 'integer');
        $mapping['properties']['status'] = array('type' => 'integer');
        $mapping['properties']['uid'] = array('type' => 'integer');
        $mapping['properties']['content'] = array('type' => 'string', 'searchAnalyzer' => 'ik', 'indexAnalyzer' => 'ik', 'term_vector' => 'with_positions_offsets', 'index' => 'analyzed');
        $mapping['properties']['id'] = array('type' => 'integer');
        $mapping['properties']['imgs'] = array('type' => 'string');
        $mapping['properties']['like_num'] = array('type' => 'integer');
        $mapping['properties']['comment_num'] = array('type' => 'integer');
        $mapping['properties']['display_region'] = array('type' => 'string');
        $mapping['properties']['type'] = array('type' => 'integer');
        $mapping['properties']['pin']['properties']['location'] = array('type' => 'geo_point');
        $mapping['properties']['gmt_create'] = array('type' => 'integer');
        $mapping['properties']['gmt_modified'] = array('type' => 'integer');
        $mapping['properties']['rank'] = array('type' => 'float');
        $mapping['properties']['special_rank'] = array('type' => 'float');
        $mapping['properties']['related_moments'] = array('type' => 'string');

        $mapping['properties']['tags']['properties']['id'] = array('type' => 'integer');
        $mapping['properties']['tags']['properties']['img_id'] = array('type' => 'integer');
        $mapping['properties']['tags']['properties']['tag_id'] = array('type' => 'integer');
        $mapping['properties']['tags']['properties']['x'] = array('type' => 'integer');
        $mapping['properties']['tags']['properties']['y'] = array('type' => 'integer');
        $mapping['properties']['tags']['properties']['name'] = array('type' => 'string');

        $mapping['properties']['circle'] = array('type' => 'nested');
        $mapping['properties']['circle']['properties']['id'] = array('type' => 'integer');
        $mapping['properties']['circle']['properties']['name'] = array('type' => 'string');
        $mapping['properties']['circle']['properties']['introduction'] = array('type' => 'string');
        $mapping['properties']['circle']['properties']['description'] = array('type' => 'string');
        $mapping['properties']['circle']['properties']['location_id'] = array('type' => 'integer');
        $mapping['properties']['circle']['properties']['circle_display'] = array('type' => 'integer');
        $mapping['properties']['circle']['properties']['is_display'] = array('type' => 'integer');

        $mapping['properties']['product']['properties']['id'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['price_min'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['price_max'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['status'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['sales'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['stock'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['value_of_sales'] = array('type' => 'integer');

        $mapping['properties']['product']['properties']['sku']['properties']['hold_num'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['sku']['properties']['id'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['sku']['properties']['num'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['sku']['properties']['origin_price'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['sku']['properties']['price'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['sku']['properties']['props'] = array('type' => 'string');
        $mapping['properties']['product']['properties']['sku']['properties']['sold_num'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['category']['properties']['cid'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['category']['properties']['cname'] = array('type' => 'string');
        $mapping['properties']['product']['properties']['category']['properties']['is_parent'] = array('type' => 'integer');
        $mapping['properties']['product']['properties']['category']['properties']['parent_id'] = array('type' => 'integer');
        return $mapping;
    }

    public function dataSource($moments) {

        if (!$moments) {
            return FALSE;
        }

        $_moment_ids = array();
        $_product_ids = array();
        foreach ($moments as $moment) {
            $_moment_ids[] = $moment->id;
            if (C2cMomentService::TYPE_ITEM == $moment->type && $moment->product_id) {
                $_product_ids[] = $moment->product_id;
            }
        }

        // 关联moment
        $_related_moment_map = $this->momentService->fetch_product_id_from_mysql($_moment_ids);
        $_product_ids_len = count($_product_ids);

        // 获取商品信息，商品SKU，库存信息
        if ($_product_ids_len) {
            $products_map = $this->productService->fetch_product_from_mysql($_product_ids);
            $products_sku_stock_map = $this->productService->fetch_product_sku_from_mysql($_product_ids);
        }

        // 获取地址信息
        $locations_map = $this->locationService->fetch_location_from_mysql($_moment_ids);
        // 获取话题信息
        $circle_map = $this->circleService->fetch_circle_from_mysql($_moment_ids);
        // 获取标签信息
        $tag_map = $this->tagService->fetch_tag_from_mysql($_moment_ids);
        // 获取评论总数
        $comment_map = $this->momentService->fetch_comment_from_mysql($_moment_ids);
        // 获取喜欢总数
        $like_map = $this->momentService->fetch_like_from_mysql($_moment_ids);

        $bulk = array();
        $bulk['index'] = self::WEGO_MOMENT_INDEX;
        $bulk['type'] = self::WEGO_MOMENT_INDEX_TYPE;

        foreach ($moments as $moment) {

            $this->lastid = $moment->id;

            $bulk['body'][] = array(
                'index' => array(
                    '_id' => $moment->id
                ),
            );

            $_current_moment = array();
            $_current_moment_id = $moment->id;
            $_current_product_id = $moment->product_id;

            if (C2cMomentService::TYPE_ITEM == $moment->type) {

                $_current_product_info = $this->productService->get_default_field();
                $_current_products_sku_stock_info = $this->productService->get_sku_default_field();

                if (isset($products_map[$_current_product_id])) {
                    $_current_product_info = $products_map[$_current_product_id];
                    $moment->content = $_current_product_info->detail;
                    $moment->imgs = $_current_product_info->imgs;
                }

                // 销量累加
                // 销售额计算
                // SKU剩余库存累加
                $_sales_num = 0;
                $_value_of_sales = 0;
                $_sku_price_sum = 0;
                $_sku_stock_sum = 0;

                if (isset($products_sku_stock_map[$_current_product_id])) {
                    $_current_products_sku_stock_info = $products_sku_stock_map[$_current_product_id];
                    foreach ($_current_products_sku_stock_info as $sigle_sku_stock_info) {
                        $_sales_num += $sigle_sku_stock_info->sold_num;
                        $_sku_price_sum += $sigle_sku_stock_info->price;
                        $_sku_stock_sum += $sigle_sku_stock_info->num;
                    }
                    $_sku_count = count($_current_products_sku_stock_info);
                    $_value_of_sales = ($_sku_price_sum / $_sku_count) * $_sales_num;
                }

                $_current_moment['product'] = array(
                    'id' => (int) $_current_product_info->id,
                    'price_min' => (int) $_current_product_info->price_min,
                    'price_max' => (int) $_current_product_info->price_max,
                    'status' => (int) $_current_product_info->status,
                    'sales' => (int) $_sales_num,
                    'value_of_sales' => (int) $_value_of_sales,
                    'stock' => (int) $_sku_stock_sum,
                    'sku' => $_current_products_sku_stock_info
                );

                // 类目信息
                if (isset($this->categoryMap[$_current_product_info->cid])) {
                    $_current_moment['product']['category'] = $this->categoryMap[$_current_product_info->cid];
                }
            }

            // 位置信息
            $_current_moment['display_region'] = '';
            $_current_moment_location_info = new \stdClass();
            $_current_moment_location_info->lon = 0;
            $_current_moment_location_info->lat = 0;
            if (isset($locations_map[$_current_moment_id])) {
                $_current_moment_location_info = $locations_map[$_current_moment_id];
                $_current_moment['display_region'] = $_current_moment_location_info->display_region;
            }

            // 话题信息
            if (isset($circle_map[$_current_moment_id])) {
                $_current_moment_circle_info = $circle_map[$_current_moment_id];
                $_current_moment['circle'] = $_current_moment_circle_info;
            }

            // 标签信息
            if (isset($tag_map[$_current_moment_id])) {
                $_current_moment_tag_info = $tag_map[$_current_moment_id];
                $_current_moment['tags'] = $_current_moment_tag_info;
            }

            // 评论数
            $_current_moment['comment_num'] = 0;
            if (isset($comment_map[$_current_moment_id])) {
                $_current_moment_comment_info = $comment_map[$_current_moment_id];
                $_current_moment['comment_num'] = (int) $_current_moment_comment_info->count;
            }

            // 喜欢数
            $_current_moment['like_num'] = 0;
            if (isset($like_map[$_current_moment_id])) {
                $_current_moment_like_info = $like_map[$_current_moment_id];
                $_current_moment['like_num'] = (int) $_current_moment_like_info->count;
            }

            // 计算内容评分
            $_current_moment_score = 0.00000;
            if (isset($this->talentMap[$moment->uid])) {
                $_moment_sale_num = isset($_sales_num) ? $_sales_num : 0;
                $_current_moment_vote = $_current_moment['like_num'] + $_moment_sale_num * 5;
                $_time = $moment->gmt_create - C2C_RELEASE_TIME;
                $tpfx = $_current_moment_vote <= 0 ? 0 : 1;
                $kdcd = $_current_moment_vote <= 0 ? 1 : $_current_moment_vote;
                $_current_moment_score = sprintf("%.5f", log10($kdcd) + $tpfx * $_time / 450000);
                $_current_moment_special_score = sprintf("%.5f", log10($kdcd) + $tpfx * $_time / 22500);
            }

            $_current_moment['id'] = (int) $moment->id;
            $_current_moment['rank'] = (float) $_current_moment_score;
            $_current_moment['special_rank'] = (float) $_current_moment_special_score;
            $_current_moment['uid'] = (int) $moment->uid;
            $_current_moment['imgs'] = $moment->imgs;
            $_current_moment['content'] = $moment->content;
            $_current_moment['status'] = (int) $moment->status;
            $_current_moment['show_location'] = (int) $moment->show_location;
            $_current_moment['is_hot'] = (int) $moment->is_hot;
            $_current_moment['review_status'] = (int) $moment->review_status;
            $_current_moment['type'] = (int) $moment->type;
            $_current_moment['gmt_create'] = (int) $moment->gmt_create;
            $_current_moment['gmt_modified'] = (int) $moment->gmt_modified;
            $_current_moment['related_moments'] = isset($_related_moment_map[$moment->id]) ? $_related_moment_map[$moment->id] : '';
            $_current_moment['pin'] = array('location' => array('lon' => $_current_moment_location_info->lon, 'lat' => $_current_moment_location_info->lat));
            $bulk['body'][] = $_current_moment;
        }

        return $bulk;
    }
}