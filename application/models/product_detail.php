<?php

class Product_detail extends MZ_Model {

    public function fetch_by_iids($item_id_arr) {

        if (!is_array($item_id_arr) || count($item_id_arr) <= 0) {
            return FALSE;
        }

        $item_iids = implode(',', $item_id_arr);
        $sql = "select i.id as iid, ifnull(d.imgs, pd.imgs) as imgs from item i left join item_detail d on i.id = d.iid left join product_detail pd on i.product_id = pd.product_id where i.id in ($item_iids)";
        $product_detail = $this->execute($sql)->result();
        return $product_detail;
    }

    public function fetch_by_pids($product_id_arr) {

        if (!is_array($product_id_arr) || count($product_id_arr) <= 0) {
            return FALSE;
        }

        $product_iids = implode(',', $product_id_arr);
        $sql = "SELECT product_id, imgs FROM product_detail WHERE product_id IN ($product_iids)";
        $product_detail = $this->execute($sql)->result();
        return $product_detail;
    }

}