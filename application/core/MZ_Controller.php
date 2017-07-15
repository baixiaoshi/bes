<?php

/**
 * @property CI_Loader $load
 * @property CI_DB_active_record $db
 * @property CI_Calendar $calendar
 * @property Email $email
 * @property CI_Encrypt $encrypt
 * @property CI_Ftp $ftp
 * @property CI_Hooks $hooks
 * @property CI_Image_lib $image_lib
 * @property CI_Language $language
 * @property CI_Log $log
 * @property CI_Output $output
 * @property CI_Pagination $pagination
 * @property CI_Parser $parser
 * @property CI_Session $session
 * @property CI_Sha1 $sha1
 * @property CI_Table $table
 * @property CI_Trackback $trackback
 * @property CI_Unit_test $unit
 * @property CI_Upload $upload
 * @property CI_URI $uri
 * @property CI_User_agent $agent
 * @property CI_Validation $validation
 * @property CI_Xmlrpc $xmlrpc
 * @property CI_Zip $zip
 * @property MZ_common $mz_common
 * @property Context $context
 */
abstract class MZ_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function forward_to($target) {
        $this->navigator->set_target($target);
    }

    public function _set_output_format($format) {
        $this->navigator->set_output_format($format);
    }
}

// 工具方法及事件

global $_STYLES;
global $_SCRIPTS;
global $_HEAD_TITLE;
global $_META;
global $_CANONICAL;

$_STYLES = array();
$_SCRIPTS = array();
$_HEAD_TITLE = 'Husor DevOps自助平台';
$_META = array();

function is_get() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function is_ajax() {
    return get_instance()->input->is_ajax_request();
}

function add_style($path, $top = FALSE) {
    global $_STYLES;
    if ($top === FALSE) {
        $_STYLES[] = $path;
    } else {
        array_unshift($_STYLES, $path);
    }
}

function get_styles() {
    global $_STYLES;
    return $_STYLES;
}

function add_script($path, $top = FALSE) {
    global $_SCRIPTS;
    if ($top === FALSE) {
        $_SCRIPTS[] = $path;
    } else {
        array_unshift($_SCRIPTS, $path);
    }
}

function get_scripts() {
    global $_SCRIPTS;
    return $_SCRIPTS;
}

function set_head_title($title, $tail = TRUE) {
    global $_HEAD_TITLE;
    $_HEAD_TITLE = $title;
    if ($tail) {
        $_HEAD_TITLE .= '-贝贝网';
    }
    $_HEAD_TITLE = htmlspecialchars($_HEAD_TITLE);
}

function get_head_title() {
    global $_HEAD_TITLE;
    return $_HEAD_TITLE;
}

function set_canonical($href) {
    global $_CANONICAL;
    $_CANONICAL = htmlspecialchars($href);
}

function get_canonical() {
    global $_CANONICAL;
    return $_CANONICAL;
}

function set_meta($key, $value) {
    global $_META;
    $_META[$key] = htmlspecialchars($value);
}

function get_meta() {
    global $_META;
    return $_META;
}

function set_meta_description($value) {
    set_meta('description', $value);
}

function _get($name) {
    return trim(get_instance()->input->get($name));
}

function _post($name) {
    return trim(get_instance()->input->post($name));
}

function is_login() {
    return get_instance()->session->userdata('_is_login_') == TRUE;
}

function current_user_name() {
    $username = get_instance()->session->userdata('_username_');
    return $username;
}

function current_uid() {
    return get_instance()->session->userdata('_uid_');
}

function log_error($message) {
    log_message('error', $message);
}

function load_lib($lib, $param_name = NULL) {
    return get_instance()->load->library($lib, NULL, $param_name);
}

function load_model($model, $param_name = NULL) {
    return get_instance()->load->model($model, $param_name);
}

function get_client_ip() {
    static $china_cache_ips = array('183.129.130', '218.59.215', '119.134.254', '221.204.214');
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $remote_addr = $_SERVER['REMOTE_ADDR'];
        $remote_addr = substr($remote_addr, 0, strrpos($remote_addr, '.'));
        if (in_array($remote_addr, $china_cache_ips)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = array_shift(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
                return $ip;
            }
        }
    }

    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else {
        $ip = get_instance()->input->ip_address();
    }
    return $ip;
}

function get_referer() {
    if (isset($_SERVER['HTTP_REFERER'])) {
        return $_SERVER['HTTP_REFERER'];
    }
    return '';
}

function result_to_map(array $result, $field = 'id') {
    $map = array();
    foreach ($result as $entry) {
        $map[$entry->$field] = $entry;
    }
    return $map;
}

function bb_service_autoloader($class) {

    if (strpos($class, 'beibei\\service\\') !== 0) {
        return;
    }

    $class_part = explode('\\', $class);
    unset($class_part[0]);
    unset($class_part[1]);

    $className = implode('/', $class_part);

    include APPPATH . 'libraries/service/' . $className . EXT;
}

function _mz_error_handler($severity, $message, $filepath, $line) {
    if ($severity == E_STRICT) {
        return;
    }
    $_error = &load_class('Exceptions', 'core');
    if (($severity & error_reporting()) == $severity) {
        for ($i = ob_get_level(); $i > 0; $i--) {
            ob_end_clean();
        }
        if (config_item('log_threshold') != 0) {
            $_error->log_exception($severity, $message, $filepath, $line);
        }
        show_error($message);
        exit();
    }
}

function _mz_exception_handler($e) {
    echo $e->__toString();
    exit();
}

function mt_shutdown_function() {
    if (!is_null($e = error_get_last())) {
        header('content-type: text/plain');
        print "this is not html:\n\n" . print_r($e, TRUE);
    }
}

if (ENVIRONMENT == 'development') {
    register_shutdown_function('mt_shutdown_function');
}

set_error_handler('_mz_error_handler');
set_exception_handler('_mz_exception_handler');
spl_autoload_register('bb_service_autoloader');