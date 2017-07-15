<?php
class MZ_Form_validation extends CI_Form_validation {
    
    function unique($value, $params) {
        
        $CI = & get_instance();
        $CI->load->database();
        
        $CI->form_validation->set_message('unique', '该%s已经被使用了');
        
        list($table, $field) = explode(".", $params, 2);
        
        $query = $CI->db->select($field)->from($table)->where($field, $value)->limit(1)->get();
        
        if ($query->row()) {
            return false;
        } else {
            return true;
        }
    }
    
    function pregmatch($value, $params) {
        $CI = & get_instance();
        $CI->form_validation->set_message('pregmatch', '%s 不符合规则');
        
        return preg_match($params, $value) == 1;
    }
}