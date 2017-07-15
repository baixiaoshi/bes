<?php

use beibei\service\elasticsearch\index\ItemIndexService;


class Welcome_controller extends MZ_Controller {

    public static $PAGE_NUM = 1000;

    private $last_id = 0;

    public function index() {

        // NOTE：调用顺序不能颠倒

        // 实例化索引 Service
        $ItemIndexService = ItemIndexService::get_instance();

        // 创建一个新索引
        $createRes = $ItemIndexService->createIndex();

        if (!$createRes) {
            $this->monolog->addWarning('create new index fail, exit!');
            exit();
        }

        // 索引数据
        $_loop = $ItemIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
        $this->last_id = $ItemIndexService->getLastId();
        var_dump($this->last_id);

        while ($_loop) {
            $this->last_id = $ItemIndexService->getLastId();
            var_dump($this->last_id);
            $_loop = $ItemIndexService->buildDataToEs($this->last_id, self::$PAGE_NUM);
        }

        // 创建别名，让新索引提供服务
        $setRes = $ItemIndexService->setAlias();

        if (!$setRes) {
            $this->monolog->addWarning('set alias from new index fail, exit!');
            exit();
        }

        // 删除旧索引
        $delres = $ItemIndexService->deleteIndex();

        if (!$delres) {
            $this->monolog->addWarning('delete old index fail, exit, exit!');
            exit();
        }

        $this->monolog->addInfo('index done!');

        exit();
    }
}