<?php


class Mart_show extends MZ_Model {

    // 获取在线专场
    public function fetch_mart_show_by_page_where($last_id, $num = 5000, $type = 1) {

        if (!is_numeric($last_id) || !is_numeric($num)) {
            return FALSE;
        }

        // 查询条件
        // 1.mart_show.status = 1    审核通过专场
        // 2.mart_show.type = 1,2     贝贝专场、米折专场
        // 3.mart_show.id > $last_id 偏移量
        // 4.uid > 100000            跳过自动化测试生成的商家
        $where[] = 'ms.status = 1';
        $where[] = 'ms.type = '. $type;
        $where[] = "ms.id > $last_id";

        if (ENVIRONMENT == 'development') {
            $where[] = 'ms.uid >= 0';
        } else {
            $where[] = 'ms.uid >= 100000';
        }

        $where_string = empty($where) ? 1 : implode(' AND ', $where);
        $sql = "SELECT ms.id, ms.uid, ms.title, ms.sbanner, ms.um_promotion, ms.sort, ms.bgcolor, ms.gmt_begin, ms.gmt_end, ms.type AS brand_type, bd.id AS brand_id, bd.name AS brand_name, bd.logo AS brand_logo, bd.desc as brand_desc FROM mart_show AS ms LEFT JOIN brand_detail AS bd ON (ms.brand = bd.id) WHERE $where_string ORDER BY ms.id ASC LIMIT ?";
        $result = $this->execute($sql, array($num))->result();
        return $result;
    }

    // 批量获取专场中商品-最小的价格，最小的折扣
    public function fetch_item_info_by_event_ids($event_id_array) {

        if (!is_array($event_id_array)) {
            return FALSE;
        }

        foreach ($event_id_array as $key => $event_id) {
            if (!is_numeric($event_id)) {
                unset($event_id_array[$key]);
            }
        }

        if (count($event_id_array) <= 0) {
            return FALSE;
        }

        $event_ids = implode(',', $event_id_array);
        $sql = "SELECT COUNT(*) AS count, event_id, floor(min(price / origin_price) * 100) AS min_discount, min(price) AS min_price FROM item WHERE status = 1 AND event_id IN ($event_ids) GROUP BY event_id";
        $result = $this->execute($sql)->result();
        return $result;
    }

    // 批量获取专场中商品-每个类别的商品个数，类别名称
    public function fetch_item_cid_info_by_event_ids($event_id_array) {

        if (!is_array($event_id_array)) {
            return FALSE;
        }

        foreach ($event_id_array as $key => $event_id) {
            if (!is_numeric($event_id)) {
                unset($event_id_array[$key]);
            }
        }

        if (count($event_id_array) <= 0) {
            return FALSE;
        }

        $event_ids = implode(',', $event_id_array);
        $sql = "SELECT i.event_id, i.cid, COUNT(*) AS cid_item_count, c.name FROM item AS i JOIN category AS c ON (i.cid = c.id) WHERE i.event_id IN ($event_ids) AND i.status = 1 GROUP BY i.event_id, i.cid ORDER BY i.event_id DESC, cid_item_count DESC";
        $result = $this->execute($sql)->result();
        return $result;
    }
}