<?php

function parseProductMainImg($imgs) {

    if (empty($imgs)) {
        return FALSE;
    }

    $mainPicMap = array();
    $mainPicArr = explode(';', $imgs);
    foreach ($mainPicArr as $propIdImg) {
        $vidAndString = explode(':', $propIdImg);
        if (count($vidAndString) != 2) {
            continue;
        }

        list($vid, $img) = $vidAndString;
        $mainPicMap[$vid] = $img;
    }

    return $mainPicMap;
}


function parseSkuProps($skuProps) {

    if (empty($skuProps)) {
        return FALSE;
    }

    $propsArr = explode(';', $skuProps);

    foreach ($propsArr as $prop) {
        list($id, $vid, $name, $value) = explode(':', $prop);
    }
}