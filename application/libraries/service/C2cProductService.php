<?php

namespace beibei\service;

/**
 * Class C2cProductService
 * @package beibei\service
 * @method static C2cProductService get_instance()
 */
class C2cProductService extends BaseService {

    public function init() {
        $CI = &get_instance();
        load_model('c2c/product');
        load_model('c2c/product_sku');
        load_model('c2c/product_sku_stock');
        $this->product = $CI->product;
        $this->product_sku = $CI->product_sku;
        $this->product_sku_stock = $CI->product_sku_stock;
    }

    /**
     * @return \stdClass
     */
    public function get_default_field() {
        $product = new \stdClass();
        $product->price_min = 0;
        $product->price_max = 0;
        $product->status = 0;
        $product->cid = 0;
        $product->detail = '';
        $product->imgs = '';
        $product->id = 0;
        return $product;
    }

    public function get_sku_default_field() {
        $product_sku = array();
        return $product_sku;
    }

    /**
     * 获取商品信息
     * @param $product_ids_arr
     *
     * @return array|bool
     */
    public function fetch_product_from_mysql($product_ids_arr) {

        if (!is_array($product_ids_arr) || count($product_ids_arr) <= 0) {
            return FALSE;
        }

        $products = $this->product->get_product($product_ids_arr);

        if (!$products) {
            return FALSE;
        }

        $products = result_to_map($products);

        return $products;
    }

    /**
     * 获取商品SKU信息
     * @param $product_ids_arr
     *
     * @return array|bool
     */
    public function fetch_product_sku_from_mysql($product_ids_arr) {

        if (!is_array($product_ids_arr) || count($product_ids_arr) <= 0) {
            return FALSE;
        }

        $products_sku = $this->product_sku->get_product_sku($product_ids_arr);

        if (!$products_sku) {
            return FALSE;
        }

        $_product_sku_stock_map = array();
        foreach ($products_sku as $product) {
            $_product = clone $product;
            unset($_product->pid);
            $_product->num = (int) $_product->num;
            $_product->sold_num = (int) $_product->sold_num;
            $_product->hold_num = (int) $_product->hold_num;
            $_product_sku_stock_map[$product->pid][] = $_product;
        }

        return $_product_sku_stock_map;
    }

    /**
     * 获取商品SKU库存
     * @param $product_ids_arr
     *
     * @return array|bool
     */
    public function fetch_product_sku_stock_from_mysql($product_ids_arr) {

        if (!is_array($product_ids_arr) || count($product_ids_arr) <= 0) {
            return FALSE;
        }

        $products = $this->product_sku_stock->get_product_sku_stock($product_ids_arr);

        if (!$products) {
            return FALSE;
        }

        $products = result_to_map($products);

        return $products;
    }
}