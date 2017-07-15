<?php

class Sku extends MZ_Model {

    public function fetch_by_iids($item_id_arr) {

        if (!is_array($item_id_arr) || count($item_id_arr) <= 0) {
            return FALSE;
        }

        $item_iids = implode(',', $item_id_arr);
        $sql = "SELECT * FROM sku WHERE iid IN ($item_iids) ORDER BY iid ASC, price DESC";
        return $this->execute($sql)->result();
    }
}