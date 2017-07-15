<?php

require_once APPPATH . '/controllers/task/task_base.php';

use beibei\service\elasticsearch\index\MartshowIndexService;


class Martshow_controller extends Task_base {

    public static $PAGE_NUM = 5000;

    private $last_id = 0;

    public function __construct() {
        // 设置当前索引脚本日志文件名
        $this->log_file_name = MartshowIndexService::$_ES_INDEX_LOG_NAME;
        parent::__construct();
    }

    // 索引入口
    public function execute() {

        $indexStartTime = microtime(TRUE);
        // NOTE：调用顺序不能颠倒
        $MartshowIndexService = MartshowIndexService::get_instance();
        // 创建一个新索引
        $createRes = $MartshowIndexService->createIndex();

        if (!$createRes) {
            $this->monolog->addWarning('create new index fail, exit!');
            exit();
        }

        // 索引数据
        $_loop = $MartshowIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
        $this->last_id = $MartshowIndexService->getLastId();

        // 确认是否连上DB，如果第一次连接失败，退出索引
        if (!$_loop && $this->last_id == 0) {
            $this->monolog->addWarning('get data from db fail!');
            exit();
        }

        while ($_loop) {
            $_loop = $MartshowIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
            $this->last_id = $MartshowIndexService->getLastId();
        }

        // 创建别名，让新索引提供服务
        $setRes = $MartshowIndexService->setAlias();
        if (!$setRes) {
            $this->monolog->addWarning('set alias from new index fail, exit!');
            exit();
        }

        // 删除旧索引
        $delres = $MartshowIndexService->deleteIndex();
        if (!$delres) {
            $this->monolog->addWarning('delete old index fail, exit, exit!');
        }

        $indexEndTime = microtime(TRUE);
        $this->monolog->addInfo('index Using time', array('start' => $indexStartTime, 'endtime' => $indexEndTime, 'using' => $indexEndTime - $indexStartTime));
        $this->monolog->addInfo('index done!');
        exit();
    }
}