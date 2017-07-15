<?php

require_once APPPATH . 'models/c2c/base_model.php';

class Product_sku_stock extends Base_Model {

    public function get_product_sku_stock($product_ids_arr) {

        if (!is_array($product_ids_arr) || count($product_ids_arr) <= 0) {
            return FALSE;
        }

        foreach ($product_ids_arr as $key => $pid) {
            if (!is_numeric($pid) || $pid <= 0) {
                throw new Exception('错误的参数类型');
            }
        }

        $product_ids_len = count($product_ids_arr);
        if ($product_ids_len <= 0 || $product_ids_len > 5000) {
            throw new Exception('商品ID数量必须在1至5000之间');
        }

        $_product_ids_string = implode(',', $product_ids_arr);

        $sql = "SELECT sku_id, pid, num, sold_num, hold_num FROM c2c_product_sku_stock WHERE pid IN ({$_product_ids_string})";
        return $this->execute($sql)->result();
    }
}