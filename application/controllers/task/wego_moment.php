<?php

use beibei\service\elasticsearch\wego\WegoMomentIndexService;

require_once APPPATH . '/controllers/task/task_base.php';


class Wego_moment_controller extends Task_base {

    public static $PAGE_NUM = 5000;

    private $last_id = 0;

    public function __construct() {
        $this->log_file_name = WegoMomentIndexService::WEGO_MOMENT_INDEX_LOG_NAME;
        parent::__construct();
    }

    public function execute() {

        $indexStartTime = microtime(TRUE);
        $wegoMomentIndexService = WegoMomentIndexService::get_instance();

        // 索引数据
        $_loop = $wegoMomentIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
        $this->last_id = $wegoMomentIndexService->getLastId();

        // 确认是否连上DB，如果第一次连接失败，退出索引
        if (!$_loop && $this->last_id == 0) {
            $this->monolog->addWarning('get data from db fail!');
            exit();
        }

        while ($_loop) {
            $_loop = $wegoMomentIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
            $this->last_id = $wegoMomentIndexService->getLastId();
        }

        $indexEndTime = microtime(TRUE);
        $this->monolog->addInfo('index Using time', array('start' => $indexStartTime, 'endtime' => $indexEndTime, 'using' => $indexEndTime - $indexStartTime));
        $this->monolog->addInfo('index done!');
        exit();
    }
}