<?php

require_once APPPATH . 'models/c2c/base_model.php';

class Category extends Base_Model {

    public static $STATUS_ON = 1;
    
    // 获得一级类目
    public function get_root_cate() {
        return $this->execute('SELECT * FROM `c2c_category` WHERE `status` = 1 AND `parent_id` = 0 AND `is_parent` = 1')->result();
    }
    
    //查询所有类目
    public function get_all() {
        return $this->execute('select * from c2c_category where status = 1')->result();
    }
    
    //根据父类目查询子类目
    public function get_by_parent($parent_id) {
        if (!$this->check_num_ids(array($parent_id))) {
            return FALSE;
        }
        return $this->execute("select * from c2c_category where parent_id = ? and status = 1", array($parent_id))->result();
    }

    //根据父类目数组查询子类目
    public function get_by_parents($parent_ids, $fields = '*') {
        if (!$this->check_num_ids($parent_ids)) {
            return FALSE;
        }
        $ids = implode(',', $parent_ids);
        return $this->execute("select $fields from c2c_category where parent_id IN ($ids) and status = 1")->result();
    }
    
    //根据id查询类目
    public function get_by_id($id) {
        if (!$this->check_num_ids(array($id))) {
            return FALSE;
        }
        return $this->execute("select * from c2c_category where id = ?", array($id))->row();
    }
    
    //根据name查询类目
    public function get_by_name($name) {
        return $this->execute("select * from c2c_category where name = ?", array($name))->result();
    }
    
    //根据name、parent_id查询类目
    public function get_by_parentid_name($name, $parent_id) {
        if (!$this->check_num_ids(array($parent_id))) {
            return FALSE;
        }
        return $this->execute("select * from c2c_category where name = ? and parent_id = ?", array($name, $parent_id))->row();
    }
    
    //添加类目
    public function add_category($name, $parent_id, $is_parent, $gmt_create, $gmt_modified, $sort, $status = 1) {
        $data = array('name' => $name, 
                'parent_id' => $parent_id, 
                'is_parent' => $is_parent, 
                'gmt_create' => $gmt_create, 
                'gmt_modified' => $gmt_modified, 
                'sort' => $sort, 
                'status' => $status);
        $this->insert('c2c_category', $data);
        return $this->insert_id();
    }
    
    //修改类目
    public function update_category($id, $name, $parent_id, $is_parent, $gmt_modified, $sort, $status = 1) {
        if (!$this->check_num_ids(array($id))) {
            return FALSE;
        }
        $field_array = array('name' => $name, 
                'parent_id' => $parent_id, 
                'is_parent' => $is_parent, 
                'gmt_modified' => $gmt_modified, 
                'sort' => $sort, 
                'status' => $status);
        $where_array = array('id' => $id);
        return $this->update('c2c_category', $field_array, $where_array);
    }
    
    //删除类目
    public function del_category($id) {
        if (!$this->check_num_ids(array($id))) {
            return FALSE;
        }
        $data = array('status' => 0);
        $where_array = array('id' => $id);
        return $this->update('c2c_category', $data, $where_array);
    }
    
    //确认是否是父类目
    public function check_parent($id) {
        if (!$this->check_num_ids(array($id))) {
            return FALSE;
        }
        $is_parent = $this->execute('select is_parent from c2c_category where id = ?', array($id))->row();
        $check_children = $this->execute('select id from c2c_category where parent_id = ?', array($id));
        if ($is_parent->is_parent || $check_children) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function get_parent_root() {
        return $this->execute("select id, name from c2c_category where is_parent = 1 and parent_id = 0 and status = " . self::$STATUS_ON)->result();
    }

    public function get_all_children_cate() {
        return $this->execute("select id, name from c2c_category where is_parent = 0 and status = " . self::$STATUS_ON)->result();
    }
    
    public function list_by_ids($ids_arr, $fields = '*') {
        if (!$this->check_num_ids($ids_arr)) {
            return FALSE;
        }
        $ids = implode(',', $ids_arr);
        return $this->execute('SELECT ' . $fields . ' FROM `c2c_category` WHERE id in (' . $ids . ')')->result();
    }

    public function get_children_cate($id) {
        if (!$this->check_num_ids(array($id))) {
            return FALSE;
        }
        return $this->execute("select id, name, parent_id from c2c_category where id = ? and is_parent = 0 and status = " . self::$STATUS_ON, array($id))->row();
    }

    public function get_children_by_ids($ids_arr, $fields = '*') {
        if (!$this->check_num_ids($ids_arr)) {
            return FALSE;
        }
        $ids = implode(',', $ids_arr);
        return $this->execute('SELECT ' . $fields . ' FROM `c2c_category` WHERE is_parent = 0 and status = ' . self::$STATUS_ON . ' and id in (' . $ids . ')')->result();
    }

    public function get_all_parent() {
        return $this->execute("select id, name, parent_id from c2c_category where is_parent = 1")->result();
    }
}