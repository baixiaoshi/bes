<?php

if (function_exists('lcfirst') === false) {

    function lcfirst($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}

class MZ_Loader extends CI_Loader {
    var $_ci_view_screen_path;
    var $_ci_view_layout_path;
    var $_ci_view_control_path;
    var $_ci_controllers_control_path;
    var $_customize_layout;
    var $_inner_cache;

    function __construct() {
        parent::__construct();
        $this->_ci_view_screen_path = APPPATH . 'views/screen/';
        $this->_ci_view_control_path = APPPATH . 'views/control/';
        $this->_ci_view_layout_path = APPPATH . 'views/layout/';
        $this->_ci_controllers_control_path = APPPATH . 'controllers/control/';
    }

    function view($view, $vars = array(), $return = FALSE) {
        $screen = parent::view('screen/' . $view, $vars, TRUE);
        
        if (isset($this->_customize_layout)) {
            if ($this->_customize_layout !== NULL && $this->_customize_layout !== FALSE &&
                             file_exists($this->_ci_view_layout_path . $this->_customize_layout . EXT)) {
                $layout_path = 'layout/' . $this->_customize_layout;
            }
        } else {
            if (file_exists($this->_ci_view_layout_path . $view . EXT)) {
                $layout_path = 'layout/' . $view;
            } else {
                $tokens = explode('/', $view);
                for ($i = count($tokens) - 1; $i >= 0; $i--) {
                    $tokens[$i] = 'default';
                    $tmp_path = implode('/', $tokens);
                    if (file_exists($this->_ci_view_layout_path . $tmp_path . EXT)) {
                        $layout_path = 'layout/' . $tmp_path;
                        break;
                    }
                    array_pop($tokens);
                }
            }
        }
        
        if (isset($layout_path)) {
            $vars['_screen_holder_'] = $screen;
            return parent::view($layout_path, $vars, TRUE);
        } else {
            return $screen;
        }
    }

    function control($control, $vars = array(), $return = FALSE) {
        $_ci_path = $this->_ci_view_control_path . $control . EXT;
        if (!file_exists($_ci_path)) {
            return;
        }
        
        if (file_exists($this->_ci_controllers_control_path . $control . EXT)) {
            require_once ($this->_ci_controllers_control_path . $control . EXT);
            $tmp_array = explode('/', $control);
            $cls = $tmp_array[count($tmp_array) - 1];
            $inst = new $cls();
            try {
                $data = $inst->execute($vars);
            } catch(Exception $e) {
                return;
            }
        }
        
        $data = isset($data) && is_array($data) ? array_merge($vars, $data) : $vars;
        $output = parent::_ci_load(array('_ci_path' => $_ci_path, '_ci_vars' => $data, '_ci_return' => TRUE));
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    function set_layout($layout = NULL) {
        if ($layout === NULL) {
            $this->_customize_layout = FALSE;
        } else {
            $this->_customize_layout = $layout;
        }
    }

    /**
     * 重写lib加载，支持直接加载third_party目录下的package，形式如下：
     * package_name:lib_path
     * 
     * @see CI_Loader::library()
     */
    public function library($library = '', $params = NULL, $object_name = NULL) {
        if (is_array($library)) {
            foreach ($library as $class) {
                $this->library($class, $params);
            }
            return;
        }
        if (strpos($library, ':') === FALSE) {
            return parent::library($library, $params, $object_name);
        }
        list($package, $library) = explode(':', $library);
        $path = '';
        
        if (($last_slash = strrpos($library, '/')) !== FALSE) {
            $path = substr($library, 0, $last_slash + 1);
            $library = substr($library, $last_slash + 1);
        }
        
        if ($object_name == '') {
            $object_name = lcfirst($library);
        }
        
        $mod_path = APPPATH . 'third_party/' . $package . '/libraries/';
        $filepath = $mod_path . $path . $library . '.php';
        
        if (!is_file($filepath)) {
            show_error('Unable to locate the library you have specified: ' . $library);
        }
        
        if (in_array($filepath, $this->_ci_loaded_files)) {
            if (!is_null($object_name)) {
                $CI = & get_instance();
                if (!isset($CI->$object_name)) {
                    return $this->_ci_init_class($library, '', $params, $object_name);
                }
            }
            
            log_message('debug', $library . ' class already loaded. Second attempt ignored.');
            return;
        }
        
        include_once ($filepath);
        $this->_ci_loaded_files[] = $filepath;
        return $this->_ci_init_class($library, '', $params, $object_name);
    }

    /**
     * 重写model加载，支持直接加载third_party目录下的package，形式如下：
     * package_name:model_path
     * 
     * @see CI_Loader::model()
     */
    public function model($model, $name = '', $db_conn = FALSE) {
        if (is_array($model)) {
            foreach ($model as $babe) {
                $this->model($babe);
            }
            return;
        }
        if (strpos($model, ':') === FALSE) {
            return parent::model($model, $name, $db_conn);
        }
        list($package, $model) = explode(':', $model);
        if ($model == '') {
            return;
        }
        
        $path = '';
        
        if (($last_slash = strrpos($model, '/')) !== FALSE) {
            $path = substr($model, 0, $last_slash + 1);
            $model = substr($model, $last_slash + 1);
        }
        
        if ($name == '') {
            $name = lcfirst($model);
        }
        
        if (in_array($name, $this->_ci_models, TRUE)) {
            return;
        }
        
        $CI = & get_instance();
        if (isset($CI->$name)) {
            show_error('The model name you are loading is the name of a resource that is already being used: ' . $name);
        }
        
        $mod_path = APPPATH . 'third_party/' . $package . '/models/';
        if (!is_file($mod_path . $path . $model . '.php')) {
            show_error('Unable to locate the model you have specified: ' . $model);
        }
        
        if ($db_conn !== FALSE and !class_exists('CI_DB')) {
            if ($db_conn === TRUE) {
                $db_conn = '';
            }
            $CI->load->database($db_conn, FALSE, TRUE);
        }
        if (!class_exists('CI_Model')) {
            load_class('Model', 'core');
        }
        
        require_once ($mod_path . $path . $model . '.php');
        $CI->$name = new $model();
        
        $this->_ci_models[] = $name;
        return;
    }

    public function helper($helpers = array()) {
        if (!is_array($helpers) && (strpos($helpers, ':') !== FALSE)) {
            // do nothing
        } else {
            return parent::helper($helpers);
        }
        
        list($package, $helper) = explode(':', $helpers);
        $mod_path = APPPATH . 'third_party/' . $package . '/helpers/';
        if (!is_file($mod_path . $helper . '.php')) {
            show_error('Unable to locate the model you have specified: ' . $mod_path . $helper . '.php');
        }
        
        include_once ($mod_path . $helper . '.php');
        $this->_ci_helpers[$helpers] = TRUE;
        log_message('debug', 'Helper loaded: ' . $helpers);
    }
}