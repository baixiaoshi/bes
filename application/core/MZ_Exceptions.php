<?php

class MZ_Exceptions extends CI_Exceptions {
    
    // 覆盖system的show_error，新增检查是否是ajax请求
    function show_error($heading, $message, $template = 'error_general', $status_code = 500) {
        $ajax = FALSE;
        $output_format = 'json';
        $content_type = 'application/json';
        if (class_exists('CI_Controller') && class_exists('Navigator')) {
            $CI = & get_instance();
            $output_format = $CI->navigator->get_output_format();
            if ($output_format == 'json' || $output_format == 'jsonp') {
                $ajax = TRUE;
                if($output_format == 'jsonp') {
                    $content_type = 'application/javascript';
                }
            }
        } else if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $ajax = TRUE;
        }
        
        if ($ajax) {
            if (isset($CI)) {
                $data = array('success' => '500', 'message' => $message);
                $result = $CI->format->factory($data)->{'to_' . $output_format}();
            } else {
                $result = json_encode(array('success' => 500, 'message' => $message));
            }
            
            header('Content-Type: ' . $content_type);
            echo $result;
            exit;
        }
        
        return parent::show_error($heading, $message, $template, $status_code);
    }
    
}