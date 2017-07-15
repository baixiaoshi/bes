<?php

namespace beibei\service;


class ProductDetailService extends BaseService {

    public function init() {

        $CI = & get_instance();
        $CI->load->model('product_detail');
        $this->product_detail = $CI->product_detail;
    }

    public function fetch_product_detail_from_db($iid_arr, $condition = 'iid') {

        if (!is_array($iid_arr) || count($iid_arr) <= 0) {
            return FALSE;
        }

        if ($condition == 'iid') {
            return $this->product_detail->fetch_by_iids($iid_arr);
        } else {
            return $this->product_detail->fetch_by_pids($iid_arr);
        }
    }

    public function product_detail_vid_map(array $productDetailInfo, $condition = 'iid') {

        $productDetailMap = array();

        foreach ($productDetailInfo as $productDetail) {

            // 新版本索引，旧结构索引
            // 旧版索引使用item_id 做Map key, 新版使用product_id
            if ($condition == 'iid') {
                $item_id = $productDetail->iid;
            } else {
                $item_id = $productDetail->product_id;
            }

            $vidImgInfo = parseProductMainImg($productDetail->imgs);

            if (!$vidImgInfo) {
                continue;
            }

            $productDetail->imgs = $vidImgInfo;
            $productDetailMap[$item_id] = $productDetail;
        }

        return $productDetailMap;
    }
}