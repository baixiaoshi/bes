<?php

/**
 * @property CI_Loader $load
 * @property CI_DB_active_record $_db
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
 */
class MZ_Model extends CI_Model {
    protected $_db;

    public function __construct() {
        parent::__construct();
        $CI = & get_instance();
        if (isset($CI->db)) {
            $this->_db = $CI->db;
        } else {
            $CI->load->database();
            $this->_db = $CI->db;
        }
    }

    public function execute($statement, $binds = NULL) {
        if (empty($binds)) {
            return $this->_db->query($statement);
        } else {
            return $this->_db->query($statement, $binds);
        }
    }

    public function query($table_name, $query_array, $where_array) {
        return $this->_db->select($query_array)->from($table_name)->where($where_array)->get();
    }

    public function update($table_name, $field_array, $where_array) {
        return $this->_db->update($table_name, $field_array, $where_array);
    }
    
    public function update_batch($table_name, $field_array, $index) {
        return $this->_db->update_batch($table_name, $field_array, $index);
    }
    
    public function insert_batch($table_name, $field_array) {
        return $this->_db->insert_batch($table_name, $field_array);
    }

    public function insert($table_name, $data_array) {
        return $this->_db->insert($table_name, $data_array);
    }

    public function delete($table_name, $where_array) {
        return $this->_db->delete($table_name, $where_array);
    }

    public function insert_id() {
        return $this->_db->insert_id();
    }

    public function affected_rows() {
        return $this->_db->affected_rows();
    }

    public function pagination_query($table_name, $columns, $wheres, $page_num, $page_size, $order_by = 'id desc') {
        $subquery = "select id from `$table_name` " . (empty($wheres) ? '' : "where $wheres ") . " order by $order_by limit " .
                         ($page_num - 1) * $page_size . ', 1';

        list(, $desc) = explode(' ', strtolower($order_by));
        if ($desc == 'asc') {
            $compair = '>=';
        } else {
            $compair = '<=';
        }
        return $this->_db->query(
                        "select $columns from `$table_name` where id $compair (" . $subquery . ") " . (empty($wheres) ? '' : " and $wheres ") .
                                         " order by $order_by limit $page_size");
    }
    
    public function large_pagination_query($table_name, $columns, $wheres, $page_num, $page_size, $index_desc = '') {
        $subquery = "select id from `$table_name` " . (empty($wheres) ? '' : "where $wheres ") . " order by id desc limit " .
        ($page_num - 1) * $page_size . ', 1';
        
        $result = $this->_db->query($subquery)->row();
        if (!$result) {
            return;
        }
        
        return $this->_db->query(
                        "select $columns from `$table_name` $index_desc where id <= $result->id" . (empty($wheres) ? '' : " and $wheres ") .
                        " order by id desc limit $page_size");
    }

    public function count_all_results($table_name, $wheres_array) {
        return $this->_db->from($table_name)->where($wheres_array)->count_all_results();
    }
}