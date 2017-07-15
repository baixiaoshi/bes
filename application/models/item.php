<?php

class Item extends MZ_Model {

    public function fetch_item_by_page_where($last_id, $page_num, $type = 1, $index_type = 'old') {

        if (!is_numeric($last_id) || !is_numeric($page_num)) {
            return FALSE;
        }

        // 默认条件
        // 当前日期往前两周内的数据
        // 专场状态为已审核通过
        // 筛选贝贝或者米折专场
        // 商品状态为审核通过
        // uid 大于 100000
        $where[] = 'm.gmt_begin > unix_timestamp(curdate() - interval 2 week)';
        $where[] = 'm.status = 1';
        $where[] = 'm.type = '. $type;
        $where[] = "i.id > $last_id";
        $where[] = 'i.status = 1';

        if (ENVIRONMENT == 'development') {
            $where[] = 'i.uid >= 0';
        } else {
            $where[] = 'i.uid >= 100000';
        }

        $where_string = empty($where) ? 1 : implode(' AND ', $where);

        $_item_sku = "";
        if ($index_type == 'new') {
            $_item_sku = "JOIN item_sku AS isku ON (i.id = isku.iid)";
        }

        $sql = "SELECT i.id, i.uid, i.product_id, i.event_id, i.title, i.desc, i.cid, i.product_props, i.key_props, i.status, i.price, i.origin_price, i.price_max, i.origin_price_max, i.img, i.tags, i.sort, i.clicks,i.sold_num, m.gmt_begin, m.gmt_end, b.name as brand_name, b.id as brand_id, b.logo as brand_logo FROM item as i $_item_sku JOIN mart_show as m ON (i.event_id = m.id) JOIN brand_detail as b ON (i.brand = b.id) WHERE $where_string ORDER BY i.id ASC LIMIT ?";
        $result = $this->execute($sql, array($page_num))->result();
        return $result;
    }

    public function fetch_items_by_event_id($event_id,$index_type) {
        if (!is_numeric($event_id)) {
            return FALSE;
        }
        // 默认条件
        $where[] = 'i.status = 1';
        $where[] = "i.event_id = $event_id";
        if (ENVIRONMENT == 'development') {
            $where[] = 'i.uid >= 0';
        } else {
            $where[] = 'i.uid >= 100000';
        }
        $where_string = empty($where) ? 1 : implode(' AND ', $where);
        $_item_sku = "";
        if ($index_type == 'new') {
            $_item_sku = "JOIN item_sku AS isku ON (i.id = isku.iid)";
        }

        $sql = "SELECT i.id, i.uid, i.product_id, i.event_id, i.title, i.desc, i.cid, i.product_props, i.key_props, i.status, i.price, i.origin_price, i.price_max, i.origin_price_max, i.img, i.tags, i.sort, i.clicks,i.sold_num, m.gmt_begin, m.gmt_end, b.name as brand_name, b.id as brand_id, b.logo as brand_logo FROM item as i $_item_sku JOIN mart_show as m ON (i.event_id = m.id) JOIN brand_detail as b ON (i.brand = b.id) WHERE $where_string ORDER BY i.id ASC";
        $result = $this->execute($sql);
        if($result) {
            $result = $result->result();
        }
        return $result;

    }

    public function fetch_item_by_page_oversea($last_id, $page_num, $type = 1, $index_type = 'old') {

        if (!is_numeric($last_id) || !is_numeric($page_num)) {
            return FALSE;
        }

        // 默认条件
        // 当前日期往前两周内的数据
        // 专场状态为已审核通过
        // 筛选贝贝或者米折专场
        // 商品状态为审核通过
        // uid 大于 100000
        $where[] = 'm.gmt_begin > unix_timestamp(curdate() - interval 2 week)';
        $where[] = 'm.status = 1';
        //$where[] = 'm.type = '. $type;
        $where[] = "i.id > $last_id";
        $where[] = 'i.status = 1';

        if (ENVIRONMENT == 'development') {
            $where[] = 'i.uid >= 0';
        } else {
            $where[] = 'i.uid >= 100000';
        }

        $where_string = empty($where) ? 1 : implode(' AND ', $where);

        $_item_sku = "";
        if ($index_type == 'new') {
            $_item_sku = "JOIN item_sku AS isku ON (i.id = isku.iid)";
        }

        $sql = "SELECT i.id, i.uid, i.product_id, i.event_id, i.title, i.desc, i.cid, i.product_props, i.key_props, i.status, i.price, i.origin_price, i.price_max, i.origin_price_max, i.img, i.tags, i.sort, i.clicks,i.sold_num, m.gmt_begin,m.goods_source, m.gmt_end, b.name as brand_name, b.id as brand_id, b.logo as brand_logo FROM item as i $_item_sku JOIN mart_oversea as m ON (i.event_id = m.id) JOIN brand_detail as b ON (i.brand = b.id) WHERE $where_string ORDER BY i.id ASC LIMIT ?";
        $result = $this->execute($sql, array($page_num));
        if ($result) {
            $result = $result->result();
        }
        return $result;
    }

    public function fetch_items_by_event_id_oversea($event_id,$index_type) {
        if (!is_numeric($event_id)) {
            return FALSE;
        }
        // 默认条件
        $where[] = 'i.status = 1';
        $where[] = "i.event_id = $event_id";
        if (ENVIRONMENT == 'development') {
            $where[] = 'i.uid >= 0';
        } else {
            $where[] = 'i.uid >= 100000';
        }
        $where_string = empty($where) ? 1 : implode(' AND ', $where);
        $_item_sku = "";
        if ($index_type == 'new') {
            $_item_sku = "JOIN item_sku AS isku ON (i.id = isku.iid)";
        }

        $sql = "SELECT i.id, i.uid, i.product_id, i.event_id, i.title, i.desc, i.cid, i.product_props, i.key_props, i.status, i.price, i.origin_price, i.price_max, i.origin_price_max, i.img, i.tags, i.sort, i.clicks,i.sold_num, m.gmt_begin, m.gmt_end, m.goods_source, b.name as brand_name, b.id as brand_id, b.logo as brand_logo FROM item as i $_item_sku JOIN mart_oversea as m ON (i.event_id = m.id) JOIN brand_detail as b ON (i.brand = b.id) WHERE $where_string ORDER BY i.id ASC";
        $result = $this->execute($sql);
        if($result) {
            $result = $result->result();
        }
        return $result;

    }

    public function fetch_item_by_page_tuan($last_id, $page_num, $type = 1, $index_type = 'old') {

        if (!is_numeric($last_id) || !is_numeric($page_num)) {
            return FALSE;
        }

        // 默认条件
        // 当前日期往前两周内的数据
        // 专场状态为已审核通过
        // 筛选贝贝或者米折专场
        // 商品状态为审核通过
        // uid 大于 100000
        $where[] = 'm.gmt_begin > unix_timestamp(curdate() - interval 2 week)';
        $where[] = 'm.status = 1';
        //$where[] = 'm.type = '. $type;
        $where[] = "i.id > $last_id";
        $where[] = 'i.status = 1';

        if (ENVIRONMENT == 'development') {
            $where[] = 'i.uid >= 0';
        } else {
            $where[] = 'i.uid >= 100000';
        }

        $where_string = empty($where) ? 1 : implode(' AND ', $where);

        $_item_sku = "";
        if ($index_type == 'new') {
            $_item_sku = "JOIN item_sku AS isku ON (i.id = isku.iid)";
        }

        $sql = "SELECT i.id, i.uid, i.product_id, i.event_id, i.title, i.desc, i.cid, i.product_props, i.key_props, i.status, i.price, i.origin_price, i.price_max, i.origin_price_max, i.img, i.tags, i.sort, i.clicks,i.sold_num,m.stock,m.tuan_type, m.gmt_begin, m.gmt_end, b.name as brand_name, b.id as brand_id, b.logo as brand_logo FROM item as i $_item_sku JOIN mart_tuan as m ON (i.event_id = m.id) JOIN brand_detail as b ON (i.brand = b.id) WHERE $where_string ORDER BY i.id ASC LIMIT ?";
        $result = $this->execute($sql, array($page_num));
        if ($result) {
            $result = $result->result();
        }
        return $result;
    }
}