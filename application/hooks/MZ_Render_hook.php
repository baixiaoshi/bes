<?php

class MZ_Render_hook {
    protected $_supported_formats = array('xml' => 'application/xml', 'json' => 'application/json', 'jsonp' => 'application/javascript', 
            'csv' => 'application/csv');

    public function render() {
        $CI = & get_instance();
        
        $data = $CI->context->get_all();
        $output_format = $CI->navigator->get_output_format();
        
        if ($CI->navigator->is_breaked()) {
            return;
        }
        
        if (isset($output_format)) {
            $this->_format_output($output_format, $data);
            return;
        }
        
        $target = $CI->navigator->get_target();
        $target = isset($target) ? $target : $CI->router->fetch_directory() . $CI->router->controller_name() . '/' .
                         $CI->router->fetch_method();
        
        $data['__target__'] = $target;
        
        $output = $CI->load->view($target, $data);
        $CI->output->append_output($output);
    }

    private function _format_output($output_format, $data) {
        $CI = & get_instance();
        $result = $CI->format->factory($data)->{'to_' . $output_format}();
        $CI->output->set_content_type($this->_supported_formats[$output_format])->set_output($result);
    }
}