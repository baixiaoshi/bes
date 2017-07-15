<?php

class Product_sku extends MZ_Model {

    public function fetch_by_iids($product_id_arr) {

        if (!is_array($product_id_arr) || count($product_id_arr) <= 0) {
            return FALSE;
        }

        $product_iids = implode(',', $product_id_arr);
        $sql = "SELECT ps.id, ps.pid, ps.props, ps.price, ps.origin_price, ps.outer_id, ps.sort, ss.num, ss.sold_num FROM product_sku AS ps JOIN sku_stock AS ss ON(ps.id = ss.id) WHERE ps.pid IN($product_iids)";
        return $this->execute($sql)->result();
    }
}