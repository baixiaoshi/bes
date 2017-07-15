<?php

class Product extends MZ_Model {

    public function fetch_product_by_page_where($last_pid, $page_num) {

        if (!is_numeric($last_pid) || !is_numeric($page_num)) {
            return FALSE;
        }

        // 默认条件
        // 如果product_draft中存在修改记录使用product_draft数据覆盖product数据
        $where[] = "p.id > $last_pid";
        $where[] = "p.status != 99";
        $where_string = empty($where) ? 1 : implode(' AND ', $where);

        $sql = "SELECT p.id, p.uid, p.brand, ifnull(pd.title, p.title) as title, p.cid, p.status, ifnull(pd.price, p.price) as price, ifnull(pd.origin_price, p.origin_price) as origin_price, ifnull(pd.key_props, p.key_props) as key_props, p.gmt_create, p.gmt_modified from product AS p LEFT JOIN product_draft AS pd ON (p.id = pd.pid) WHERE $where_string ORDER BY p.id ASC LIMIT ?";
        $result = $this->execute($sql, array($page_num))->result();
        return $result;
    }
}