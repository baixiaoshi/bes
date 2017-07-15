<?php

class Task_base extends MZ_Controller {

    public $log_file_name = NULL;

    public function __construct() {

        parent::__construct();
        if (!$this->input->is_cli_request()) {
            exit('Permission denied!');
        }

        if (is_null($this->log_file_name)) {
            exit('请设置日志文件名');
        }

        // 初始化日志类
        $log_init_params = array('name' => $this->log_file_name);
        $this->load->library('Monolog', $log_init_params);
    }
}