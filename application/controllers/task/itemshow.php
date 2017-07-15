<?php

require_once APPPATH . '/controllers/task/task_base.php';

use beibei\service\elasticsearch\index\ShowItemIndexService;


class Itemshow_controller extends Task_base {

    public static $PAGE_NUM = 5000;

    private $last_id = 0;

    public function __construct() {
        // 设置当前索引脚本日志文件名
        $this->log_file_name = ShowItemIndexService::$_ES_INDEX_LOG_NAME;
        parent::__construct();
    }

    // 索引入口
    public function execute() {

        $indexStartTime = microtime(TRUE);

        // NOTE：调用顺序不能颠倒
        // 实例化索引 Service
        $ShowItemIndexService = ShowItemIndexService::get_instance();
        try {
            // 创建一个新索引
            $createRes = $ShowItemIndexService->createIndex();

            if (!$createRes) {
                $this->monolog->addWarning('create new index fail, exit!');
                exit();
            }

            // 索引数据
            $_loop = $ShowItemIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
            $this->last_id = $ShowItemIndexService->getLastId();

            // 确认是否连上DB，如果第一次连接失败，退出索引
            if (!$_loop && $this->last_id == 0) {
                $this->monolog->addWarning('get data from db fail!');
            }

            while ($_loop) {
                $_loop = $ShowItemIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
                $this->last_id = $ShowItemIndexService->getLastId();
            }

            // 创建别名，让新索引提供服务
            $setRes = $ShowItemIndexService->setAlias();

            if (!$setRes) {
                $this->monolog->addWarning('set alias from new index fail, exit!');
                exit();
            }

            // 删除旧索引
            $delres = $ShowItemIndexService->deleteIndex();

            if (!$delres) {
                $this->monolog->addWarning('delete old index fail, exit, exit!');
            }

            $indexEndTime = microtime(TRUE);

            $this->monolog->addInfo('index Using time', array('start' => $indexStartTime, 'endtime' => $indexEndTime, 'using' => $indexEndTime - $indexStartTime));
            $this->monolog->addInfo('index done!');
        }
        catch(Exception $e) {
            $res = $ShowItemIndexService->deleteNewIndex();
            if ($res) {
                $this->monolog->addWarning('delete old index fail, exit, exit!');
            }
            $this->monolog->addWarning($e->getMessage());
        }

        exit();

    }
}