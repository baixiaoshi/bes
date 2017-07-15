<?php

namespace beibei\service;


class SkuService extends BaseService {

    public function init() {
        $CI = & get_instance();
        $CI->load->model('sku');
        $this->sku = $CI->sku;
    }

    public function fetch_sku_from_db($item_id_arr) {
        return $this->sku->fetch_by_iids($item_id_arr);
    }

    public function proccess_sku_info(array $skuResult, $productDetailResult, $logger) {

        $skuPropValue = array();

        list($_sku_left_stock_map, $_sku_left_stock_by_vid_map) = $this->check_stock($skuResult, $productDetailResult);

        foreach ($skuResult as $sku) {

            $stockSum = $sku->num;  // 当前sku总库存
            $stockSoldNum = $sku->sold_num; // 当前sku已付款
            $stockHoldNum = $sku->hold_num; // 当前sku未付款
            $stockSalesNum = $stockSoldNum + $stockHoldNum; // 当前sku总销量
            $stockLeft = $stockSum - $stockSalesNum; // 当前sku剩余库存

            $iid = $sku->iid;
            $mainImgs = array_key_exists($iid, $productDetailResult) ? $productDetailResult[$iid] : FALSE;

            // product_detail 不存在信息直接跳过
            if (!$mainImgs) {
                continue;
            }

            $vids = array_keys($mainImgs->imgs);
            $propsArr = explode(';', $sku->props);

            foreach ($propsArr as $prop) {

                $propArr = explode(':', $prop);
                if (count($propArr) != 4) continue;
                list($id, $vid, $name, $value) = $propArr;

                // 主sku与product_detail匹配不上不复制
                if (!in_array($vid, $vids)) continue;

                // 默认第一个为最小价格(SQL 排序)，取到每个色款的第一个sku信息可以终止循环
                // 同色款库存需要累加，暂时注释掉
                // if (array_key_exists($vid, $skuPropValue[$iid])) continue;
                // 库存判断，该商品所有库存为0，只需要取得最后一个商品价格展示
                if ($iid == 1784565) {
                    $logger->addWarning('1784565', array($vids, $_sku_left_stock_map, $_sku_left_stock_by_vid_map));
                }

                if (isset($_sku_left_stock_map[$iid])) {
                    $left_stock_sum_by_iid = array_sum($_sku_left_stock_map[$iid]);
                    if ($left_stock_sum_by_iid <= 0) {
                        if (isset($skuPropValue[$iid])) {
                            $logger->addWarning($left_stock_sum_by_iid, array($vid, $iid));
                            continue;
                        }
                    } else {
                        if (array_key_exists($vid, $_sku_left_stock_by_vid_map[$iid])) {
                            $left_stock_sum_by_vid = array_sum($_sku_left_stock_by_vid_map[$iid][$vid]);
                            if ($left_stock_sum_by_vid <= 0) {
                                continue;
                            }
                        }
                    }
                }

                // 数据整理
                $skuPropValue[$iid][$vid]['sku_id'] = $sku->id;

                $skuPropValue[$iid][$vid]['price'] = (isset($skuPropValue[$iid][$vid]['price']) && ($skuPropValue[$iid][$vid]['price'] < $sku->price)) ?  $skuPropValue[$iid][$vid]['price'] : $sku->price;
                $skuPropValue[$iid][$vid]['origin_price'] = (isset($skuPropValue[$iid][$vid]['price']) && ($skuPropValue[$iid][$vid]['price'] < $sku->price)) ? $skuPropValue[$iid][$vid]['origin_price'] : $sku->origin_price;
                $skuPropValue[$iid][$vid]['discount'] = $skuPropValue[$iid][$vid]['price'] / $skuPropValue[$iid][$vid]['origin_price'] * 100;

                $skuPropValue[$iid][$vid]['name'] = $value;
                $skuPropValue[$iid][$vid]['stock_sum'] = isset($skuPropValue[$iid][$vid]['stock_sum']) ? $skuPropValue[$iid][$vid]['stock_sum'] + $stockSum : $stockSum;;
                $skuPropValue[$iid][$vid]['stock_sales_num'] = isset($skuPropValue[$iid][$vid]['stock_sales_num']) ? $skuPropValue[$iid][$vid]['stock_sales_num'] + $stockSalesNum : $stockSalesNum;
                $skuPropValue[$iid][$vid]['stock_sold_num'] = isset($skuPropValue[$iid][$vid]['stock_sold_num']) ? $skuPropValue[$iid][$vid]['stock_sold_num'] + $stockSoldNum : $stockSoldNum;
                $skuPropValue[$iid][$vid]['stock_hold_num'] = isset($skuPropValue[$iid][$vid]['stock_hold_num']) ? $skuPropValue[$iid][$vid]['stock_hold_num'] + $stockHoldNum : $stockHoldNum;;
                $skuPropValue[$iid][$vid]['stock_left'] = isset($skuPropValue[$iid][$vid]['stock_left']) ? $skuPropValue[$iid][$vid]['stock_left'] + $stockLeft : $stockLeft;;
                $skuPropValue[$iid][$vid]['sku_props'] = $sku->props;

                if (!array_key_exists('item_size', $skuPropValue[$iid][$vid])) {
                    $skuPropValue[$iid][$vid]['item_size'] = array();
                }

                $prop_list = explode(';', $sku->props);
                $prop_id_arr = [4,7,8,20,46,58,59,64,62,64,75,101,161,163,164,166,168];
                foreach ($prop_list as $prop_single) {
                    $prop_list = explode(':', $prop_single);
                    if (count($prop_list) != 4) continue;
                    if (in_array($prop_list[0] , $prop_id_arr)) {
                        $skuPropValue[$iid][$vid]['item_size'][] = $prop_list[1];
                    }
                }
                $skuPropValue[$iid][$vid]['item_size'] = array_unique($skuPropValue[$iid][$vid]['item_size']);


            }
        }

        return $skuPropValue;
    }

    // 检查色款是否有库存
    // 每个色款取最小的价格
    private function check_stock($skuResult, $productDetailResult) {

        $_sku_left_stock_map = array();

        $_sku_left_stock_by_vid_map = array();

        foreach ($skuResult as $sku) {

            // 当前sku剩余库存
            $stock_left = $sku->num - ($sku->sold_num + $sku->hold_num);

            $iid = $sku->iid;
            $mainImgs = array_key_exists($iid, $productDetailResult) ? $productDetailResult[$iid] : FALSE;

            // product_detail 不存在信息直接跳过
            if (!$mainImgs) {
                continue;
            }

            $vids = array_keys($mainImgs->imgs);
            $propsArr = explode(';', $sku->props);

            foreach ($propsArr as $prop) {

                $propArr = explode(':', $prop);
                if (count($propArr) != 4) continue;
                list($id, $vid, $name, $value) = $propArr;

                // 主SKU与product_detail匹配不上不复制
                if (in_array($vid, $vids)) {
                    $_sku_left_stock_map[$iid][] = $stock_left;
                    $_sku_left_stock_by_vid_map[$iid][$vid][] = $stock_left;
                }
            }
        }

        return array($_sku_left_stock_map, $_sku_left_stock_by_vid_map);
    }
}