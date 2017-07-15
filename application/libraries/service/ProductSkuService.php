<?php

namespace beibei\service;


class ProductSkuService extends BaseService {

    public function init() {
        $CI = & get_instance();
        $CI->load->model('product_sku');
        $this->product_sku = $CI->product_sku;
    }

    public function fetch_sku_from_db($product_id_arr) {
        return $this->product_sku->fetch_by_iids($product_id_arr);
    }

    public function proccess_sku_info(array $skuResult, $productDetailResult, $logger) {

        $skuPropValue = array();
        list($_sku_left_stock_map, $_sku_left_stock_by_vid_map) = $this->check_stock($skuResult, $productDetailResult);

        foreach ($skuResult as $sku) {

            // 商品中心三期中总库存, 没有实际存储
            // 当前sku总销量
            $stockSalesNum = $sku->sold_num;
            // 当前sku剩余库存
            $stockLeft = $sku->num;

            $product_id = $sku->pid;
            $mainImgs = array_key_exists($product_id, $productDetailResult) ? $productDetailResult[$product_id] : FALSE;

            // product_detail 不存在信息直接跳过
            if (!$mainImgs) {
                continue;
            }

            $vids = array_keys($mainImgs->imgs);
            $propsArr = explode(';', $sku->props);



            foreach ($propsArr as $prop) {

                $propArr = explode(':', $prop);
                // 确保数据是正确的，不正确直接跳过
                if (count($propArr) != 4) continue;
                list($id, $vid, $name, $value) = $propArr;

                // 主sku与product_detail匹配不上不复制
                if (!in_array($vid, $vids)) continue;

                // 同色款库存需要累加
                // 库存判断，该商品所有库存为0，只需要取得最后一个商品价格展示
                if (isset($_sku_left_stock_map[$product_id])) {
                    $left_stock_sum_by_iid = array_sum($_sku_left_stock_map[$product_id]);
                    if ($left_stock_sum_by_iid <= 0) {
                        if (isset($skuPropValue[$product_id])) {
                            $logger->addWarning($left_stock_sum_by_iid, array($vid));
                            continue;
                        }
                    } else {
                        if (array_key_exists($vid, $_sku_left_stock_by_vid_map[$product_id])) {
                            $left_stock_sum_by_vid = array_sum($_sku_left_stock_by_vid_map[$product_id][$vid]);
                            if ($left_stock_sum_by_vid <= 0) {
                                continue;
                            }
                        }
                    }
                }

                // SKU 数据整理
                $skuPropValue[$product_id][$vid]['sku_id'] = $sku->id;


                $skuPropValue[$product_id][$vid]['price'] = (isset($skuPropValue[$product_id][$vid]['price']) && ($skuPropValue[$product_id][$vid]['price'] < $sku->price)) ?  $skuPropValue[$product_id][$vid]['price'] : $sku->price ;
                $skuPropValue[$product_id][$vid]['origin_price'] = (isset($skuPropValue[$product_id][$vid]['price']) && ($skuPropValue[$product_id][$vid]['price'] > $sku->price)) ? $skuPropValue[$product_id][$vid]['origin_price'] : $sku->origin_price ;
                $skuPropValue[$product_id][$vid]['discount'] = $skuPropValue[$product_id][$vid]['price'] / $skuPropValue[$product_id][$vid]['origin_price'] * 100;


                $skuPropValue[$product_id][$vid]['name'] = $value;
                $skuPropValue[$product_id][$vid]['stock_sold_num'] = isset($skuPropValue[$product_id][$vid]['stock_sold_num']) ? $skuPropValue[$product_id][$vid]['stock_sold_num'] + $stockSalesNum : $stockSalesNum;
                $skuPropValue[$product_id][$vid]['stock_left_num'] = isset($skuPropValue[$product_id][$vid]['stock_left_num']) ? $skuPropValue[$product_id][$vid]['stock_left_num'] + $stockLeft : $stockLeft;;

                $skuPropValue[$product_id][$vid]['sku_props'] = $sku->props;

                if (!array_key_exists('item_size', $skuPropValue[$product_id][$vid])) {
                    $skuPropValue[$product_id][$vid]['item_size'] = array();
                }


                $prop_list = explode(';', $sku->props);
                $prop_id_arr = [4,7,8,20,46,58,59,64,62,64,75,101,161,163,164,166,168];
                foreach ($prop_list as $prop_single) {
                    $prop_list = explode(':', $prop_single);
                    if (count($prop_list) != 4) continue;
                    if (in_array($prop_list[0] , $prop_id_arr)) {
                        $skuPropValue[$product_id][$vid]['item_size'][] = $prop_list[1];
                    }
                }
                $skuPropValue[$product_id][$vid]['item_size'] = array_unique($skuPropValue[$product_id][$vid]['item_size']);

                $skuPropValue[$product_id][$vid]['sku_id'] = $sku->id;

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
            $stock_left = $sku->num;
            $product_id = $sku->pid;
            $mainImgs = array_key_exists($product_id, $productDetailResult) ? $productDetailResult[$product_id] : FALSE;

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
                    $_sku_left_stock_map[$product_id][] = $stock_left;
                    $_sku_left_stock_by_vid_map[$product_id][$vid][] = $stock_left;
                }
            }
        }

        return array($_sku_left_stock_map, $_sku_left_stock_by_vid_map);
    }
}