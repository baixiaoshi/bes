<?php

abstract class Base_Model extends MZ_Model {

    protected $_db = NULL;

    public function __construct() {
        $CI = &get_instance();
        if (!isset($CI->db_c2c)) {
            $CI->db_c2c = $CI->load->database('beibei_c2c', TRUE);
        }
        $this->_db = $CI->db_c2c;
    }

    final protected function get_complete_table_name() {
        $table_name = $this->get_table_name();
        if (empty($table_name)) {
            throw new \Exception('table name can not be empty');
        }
        return '`' . $table_name . '`';
    }

    public function get_by_id($id) {
        if (empty($id) || !is_numeric($id)) {
            return FALSE;
        }
        $id = intval($id);
        return $this->execute('SELECT * FROM ' . $this->get_complete_table_name() . ' WHERE id = ? limit 1', array($id))->row();
    }

    public function get_by_ids($id_arr, $field = '*') {
        if (empty($id_arr) || !is_array($id_arr) || !$this->check_num_ids($id_arr)) {
            return NULL;
        }

        if (count($id_arr) === 1) {
            $id = intval(current($id_arr));
            $result = $this->execute('SELECT ' . $field . ' FROM ' . $this->get_complete_table_name() . ' WHERE id = ? limit 1', array($id))->result();
        } else {
            $ids = implode(',', $id_arr);
            $result = $this->execute('SELECT ' . $field . ' FROM ' . $this->get_complete_table_name() . ' WHERE id in (' . $ids . ') order by id desc')->result();
        }

        return $result;
    }

    public function update_by_id($id, $data) {
        if (!$data || !is_array($data)) {
            return FALSE;
        }
        $id = intval($id);
        $this->update($this->get_complete_table_name(), $data, array('id' => $id));
        return $this->affected_rows();
    }

    public function delete_by_id($id) {
        if (empty($id) || !is_numeric($id)) {
            return FALSE;
        }
        $id = intval($id);
        $this->delete($this->get_complete_table_name(), array('id' => $id));
        return $this->affected_rows();
    }

    public function count_by_where(MixedQueryCondition $qc) {
        if ($wheres = $qc->get_where()) {
            foreach ($wheres as $where) {
                list($field, $operator, $value) = $where;
                $this->_db->where($field . ' ' . $operator, $value);
            }
        }
        if ($or_wheres = $qc->get_or_where()) {
            foreach ($or_wheres as $where) {
                list($field, $operator, $value) = $where;
                $this->_db->or_where($field . ' ' . $operator, $value);
            }
        }
        if ($where_ins = $qc->get_wherein()) {
            foreach ($where_ins as $where_in) {
                list($field, $values) = $where_in;
                $this->_db->where_in($field, $values);
            }
        }

        return $this->_db->from($this->get_table_name())->count_all_results();
    }

    /**
     * 根据条件进行查询，只返回第一个结果
     *
     * @param MixedQueryCondition $qc
     * @return mixed
     */
    public function get_by_where(MixedQueryCondition $qc) {
        return $this->list_by_page_where($qc, 1, 1);
    }

    public function list_by_page_where(MixedQueryCondition $qc, $page, $page_size) {
        if ($wheres = $qc->get_where()) {
            foreach ($wheres as $where) {
                list($field, $operator, $value) = $where;
                $this->_db->where($field . ' ' . $operator, $value);
            }
        }
        if ($or_wheres = $qc->get_or_where()) {
            foreach ($or_wheres as $where) {
                list($field, $operator, $value) = $where;
                $this->_db->or_where($field . ' ' . $operator, $value);
            }
        }
        if ($where_ins = $qc->get_wherein()) {
            foreach ($where_ins as $where_in) {
                list($field, $values) = $where_in;
                $this->_db->where_in($field, $values);
            }
        }

        if ($order_bys = $qc->get_order_by()) {
            foreach ($order_bys as $order_by) {
                list($field, $sort) = $order_by;
                $this->_db->order_by($field, $sort);
            }
        }

        if ($where_not_in_array = $qc->get_where_notin()) {
            foreach ($where_not_in_array as $where_not_in) {
                list($field, $values) = $where_not_in;
                $this->_db->where_not_in($field, $values);
            }
        }

        $this->_db->limit($page_size, ($page - 1) * $page_size);
        return $this->_db->get($this->get_table_name())->result();
    }
}
