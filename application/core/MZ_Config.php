<?php

class MZ_Config extends CI_Config {

    /**
     * 重写config加载，支持直接加载third_party目录下的package，形式如下：
     * package_name:config_path
     * 
     * @see CI_Config::load()
     */
    public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE) {
        $CI = & get_instance();
        if (strpos($file, ':') === FALSE) {
            return parent::load($file, $use_sections, $fail_gracefully);
        }
        
        list($package, $file) = explode(':', $file);
        $path = '';
        
        if (($last_slash = strrpos($file, '/')) !== FALSE) {
            $path = substr($file, 0, $last_slash + 1);
            $file = substr($file, $last_slash + 1);
        }
        
        $mod_path = APPPATH . 'third_party/' . $package . '/config/';
        
        $check_locations = defined('ENVIRONMENT') ? array(ENVIRONMENT . '/' . $path . $file, $path . $file) : array($path . $file);
        
        $found = FALSE;
        foreach ($check_locations as $location) {
            $filepath = $mod_path . $location . '.php';
            if (in_array($filepath, $this->is_loaded, TRUE)) {
                return;
            }
            if (is_file($filepath)) {
                $found = TRUE;
                break;
            }
        }
        
        if ($found === FALSE) {
            show_error('Unable to locate the config file you have specified: ' . $file);
        }
        
        include ($filepath);
        if (!isset($config) or !is_array($config)) {
            if ($fail_gracefully === TRUE) {
                return FALSE;
            }
            show_error('Your ' . $filepath . ' file does not appear to contain a valid configuration array.');
        }
        
        if ($use_sections === TRUE) {
            if (isset($this->config[$file])) {
                $this->config[$file] = array_merge($this->config[$file], $config);
            } else {
                $this->config[$file] = $config;
            }
        } else {
            $this->config = array_merge($this->config, $config);
        }
        
        $this->is_loaded[] = $filepath;
        unset($config);
    }
}