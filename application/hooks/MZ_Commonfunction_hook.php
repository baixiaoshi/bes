<?php
date_default_timezone_set('Asia/Shanghai');

// 覆盖system的redirect，新增检查是否是ajax请求
function redirect($uri = '', $method = 'location', $http_response_code = 302) {
    if (!preg_match('#^https?://#i', $uri)) {
        $uri = site_url($uri);
    }
    $CI = & get_instance();
    $output_format = $CI->navigator->get_output_format();
    if ($output_format == 'json' || $output_format == 'jsonp') {
        $data = array('success' => '302', 'url' => $uri);
        $content_type = 'application/json';
        if ($output_format == 'jsonp') {
            $content_type = 'application/javascript';
        }
        
        $result = $CI->format->factory($data)->{'to_' . $output_format}();
        $CI->output->set_content_type($content_type)->set_output($result);
        $CI->output->_display();
        exit();
    }
    
    switch ($method) {
        case 'refresh':
            header("Refresh:0;url=" . $uri);
            break;
        default:
            header("Location: " . $uri, TRUE, $http_response_code);
            break;
    }
    exit();
}

function get_redirect() {
    $done = base64_url_decode(get_instance()->input->cookie('_done_'));
    return $done;
}

function get_today_gmt_start() {
    $today_year = date('Y', time());
    $today_month = date('m', time());
    $today_day = date('d', time());
    $today_gmt_start = mktime(0, 0, 0, $today_month, $today_day, $today_year);
    return $today_gmt_start;
}

function hide_phone_num($phone_num) {
    return preg_replace('/^(\d{3})\d+(\d{4})$/', '\\1****\\2', $phone_num);
}

function get_today_gmt_end() {
    return get_today_gmt_start() + 86400;
}

function get_day_gmt_start($day) {
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/is', $day, $dayarr) && checkdate($dayarr[2], $dayarr[3], $dayarr[1])) {
        $day_gmt_start = mktime(0, 0, 0, $dayarr[2], $dayarr[3], $dayarr[1]);
    } else {
        $day_gmt_start = 0;
    }
    return $day_gmt_start;
}

function get_day_gmt_end($day) {
    return get_day_gmt_start($day) + 86400;
}

function login_and_return() {
    if (!is_login()) {
        redirect(page_url('member/login.html?done=' . urlencode(get_page_url())));
    }
}

function get_page_url() {
    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'];
    $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' .
                     urlencode($_SERVER['QUERY_STRING']);
    return $url;
}

function de_round($x, $prec = 2) {
    $x = round($x, $prec + 2);
    $pow = pow(10, $prec);
    $num = (float) floor($x * $pow + pow(0.001, $prec)) / $pow;
    if (strpos($num, '.') !== FALSE) {
        $num = substr($num, 0, strpos($num, '.') + $prec + 1);
    }
    return $num;
}

function format_price($cent, $int = FALSE) {
    $price = de_round($cent / 100);
    $price_arr = explode('.', $price);
    
    if ($int === TRUE) {
        return $price;
    }
    
    if (count($price_arr) == 2) {
        $decimal = $price_arr[1];
        if (strlen($decimal) == 1) {
            $price = $price . '0';
        }
    } else {
        $price = $price . '.00';
    }
    return $price;
}

function timestamp2date($timestamp, $pattern = 'Y-m-d H:i') {
    return date($pattern, $timestamp);
}

/**
 * 获取multi_sign二进制特定位的值：0 or 1
 * @param int $dec_value 十进制的值
 * @param int $pos 第几位
 */
function multi_sign_value($dec_value, $pos) {
    $str = strrev(str_pad(decbin($dec_value), $pos, '0', STR_PAD_LEFT));
    return $str[$pos - 1];
}

/**
 * 获取HTML代码的纯文本信息
 */
function pure_text($text) {
    $text = trim($text);
    
    //也可以用strip_tags函数
    $text = preg_replace("/<[^<]*?>/s", "", $text);
    
    //删除空行
    $text = preg_replace('/^\s+\r?\n/m', '', $text);
    
    return $text;
}

function substr_utf8($string, $length, $start = 0, $is_append_points = TRUE) {
    if (empty($string)) {
        return $string;
    }
    $chars = $string;
    $i = 0;
    $m = 0;
    $n = 0;
    do {
        if (!isset($chars[$i])) {
            break;
        }
        if (preg_match("/[0-9a-zA-Z]/", $chars[$i])) {
            $m++;
        } else {
            $n++;
        } //非英文字节,  
        $k = $n / 3 + $m / 2;
        $l = $n / 3 + $m;
        $i++;
    } while ($k < $length);
    $str1 = mb_substr($string, $start, $l, 'utf-8');
    if ($l < mb_strlen($string) && $is_append_points) {
        $str1 .= '..';
    }
    return $str1;
}

function show_common_error() {
    show_error('网络繁忙，请稍后再试');
}

function show_seller_account_freeze_error() {
    show_error('您的账户违反贝贝网合作协议相关规定，账户已冻结！如有疑问请联系贝贝网客服');
}

function curl_redirect_exec($ch, $target_url) {
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $data = curl_exec($ch);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        list($header) = explode("\r\n\r\n", $data, 2);
        $matches = array();
        preg_match('/(Location:|URI:)(.*)/', $header, $matches);
        $url = trim(array_pop($matches));
        $url_parsed = parse_url($url);
        if (isset($url_parsed)) {
            if (strpos($url, 'http://a.m.tmall.com/i') === 0) {
                $url = strtr($url, array('http://a.m.tmall.com/i' => ''));
                $url = strtr($url, array('.htm' => ''));
                $url = 'http://detail.m.tmall.com/item.htm?id=' . $url;
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            $data = curl_exec($ch);
            $matches = array();
            if (preg_match('/Set-Cookie: imewweoriw=(.*);/iU', $data, $matches)) {
                curl_setopt($ch, CURLOPT_URL, $target_url);
                curl_setopt($ch, CURLOPT_COOKIE, 'imewweoriw=' . $matches[1]);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                $data = curl_exec($ch);
            }
        }
    }
    $arr = explode("\r\n\r\n", $data, 2);
    if (count($arr) == 2) {
        list(, $body) = $arr;
        return $body;
    }
    return NULL;
}

function get_url_content($url, $mobile = FALSE) {
    $m_ua_array = array();
    $p_ua_array = array();
    if ($mobile) {
        $m_ua_array[] = 'Mozilla/5.0 (Linux; U; Android 2.2; zh-cn; Desire_A8181 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
        $m_ua_array[] = 'Mozilla/5.0 (Linux; U; Android 2.2; zh-cn; Nexus One Build/FRF91)';
        $m_ua_array[] = 'Mozilla/5.0 (Linux; U; Android 2.2; zh-cn; Droid Build/FRG22D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
        $m_ua_array[] = 'Mozilla/5.0 (Linux; U; Android 2.2; zh-cn; GT-P1000 Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
        $m_ua_array[] = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; zh-cn) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5';
        $m_ua_array[] = 'Mozilla/5.0 (Linux; U; Android 2.3.3; zh-cn; GT-I9000 Build/GINGERBREAD) UC AppleWebKit/530+ (KHTML, like Gecko) Mobile Safari/530';
    } else {
        $p_ua_array[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; InfoPath.1)';
        $p_ua_array[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)';
        $p_ua_array[] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; 360SE)';
        $p_ua_array[] = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; 360SE)';
        $p_ua_array[] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; MASP)';
        $p_ua_array[] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; 360SE)';
    }
    
    $ua = ($mobile) ? $m_ua_array[array_rand($m_ua_array)] : $p_ua_array[array_rand($p_ua_array)];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $r = curl_redirect_exec($ch, $url);
    
    curl_close($ch);
    return $r;
}

function is_spider() {
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return FALSE;
    }
    
    $is_spider = FALSE;
    $spider_kws = array('spider', 'bot');
    
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    foreach ($spider_kws as $kw) {
        if (strpos($user_agent, $kw)) {
            $is_spider = TRUE;
            break;
        }
    }
    return $is_spider;
}

function is_search_refer() {
    $is_se = FALSE;
    $se_kws = array('baidu', 'google', 'sogou', 'soso', 'youdao', 'yahoo');
    
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = strtolower($_SERVER['HTTP_REFERER']);
        foreach ($se_kws as $kw) {
            if (strstr($referer, $kw)) {
                $is_se = TRUE;
                break;
            }
        }
    }
    return $is_se;
}

function encrypt_id($id) {
    $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $codes = array('c', '_', 'x', 'e', 'i', 'v', 'a', 'p', 'l', 'o');
    
    $encrypt = array_combine($numbers, $codes);
    
    foreach ($numbers as $number) {
        $id = strtr($id, $number, $encrypt[$number]);
    }
    return $id;
}

function decrypt_id($id) {
    $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $codes = array('c', '_', 'x', 'e', 'i', 'v', 'a', 'p', 'l', 'o');
    
    $decrypt = array_combine($codes, $numbers);
    foreach ($codes as $code) {
        $id = strtr($id, $code, $decrypt[$code]);
    }
    return $id;
}

function encrypt_uid($uid) {
    $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $codes = array('X', 'c', 'a', 'e', 'I', 'v', 'Z', 'd', 'L', 'o');
    
    $encrypt = array_combine($numbers, $codes);
    
    $neg = FALSE;
    if ($uid < 0) {
        $neg = TRUE;
        $uid = -1 * $uid;
    }
    
    $uid = $encrypt[$uid % 10] . $uid . $encrypt[strrev($uid) % 10];
    foreach ($numbers as $number) {
        $uid = strtr($uid, $number, $encrypt[$number]);
    }
    $uid = ($neg ? 'F' : '') . $uid;
    return $uid;
}

function decrypt_uid($uid) {
    $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $codes = array('X', 'c', 'a', 'e', 'I', 'v', 'Z', 'd', 'L', 'o');
    
    $neg = FALSE;
    if (strpos($uid, 'F') === 0) {
        $neg = TRUE;
        $uid = substr($uid, 1);
    }
    
    $uid = substr($uid, 1, strlen($uid) - 2);
    
    $decrypt = array_combine($codes, $numbers);
    foreach ($codes as $code) {
        $uid = strtr($uid, $code, $decrypt[$code]);
    }
    return $uid * ($neg ? -1 : 1);
}

function get_left_time($moment) {
    $ts = $moment - time();
    return $ts > 0 ? $ts : 0;
}

class MZ_Commonfunction_hook {

    public function hook() {
    }
}
