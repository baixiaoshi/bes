<?php
/**
 * Created by PhpStorm.
 * User: tiansheng.deng
 * Date: 2015/5/26
 * Time: 15:16
 */
namespace beibei\service\elasticsearch\search;

use beibei\service\elasticsearch\search\SearchBase;
class ItemSearchService extends SearchBase{

    //根据event_id获取es中已经保存的所有item indexid 即 hit的_id
    public  function get_item_id($event_id){
        $queryArray = array();
        $queryArray['query']['filtered']['filter']['term']["event_id"] = $event_id;

        $search = new \Elastica\Search($this->esclient);
        $resSet = $search->addIndex('item')->addType('show')->search($queryArray);
        $_ids_new = array();
        $_ids_old = array();
        foreach($resSet as $doc) {
            if (strstr($doc->getIndex(),'item_new_')) {
               $_ids_new[] = $doc->getId();
            }
            else if(strstr($doc->getIndex(),'item_old_')){
                $_ids_old[] = $doc->getId();
            }
        }
        return array($_ids_new,$_ids_old);
    }

    //根据event_id获取es中已经保存的所有item indexid 即 hit的_id
    public  function get_item_id_oversea($event_id){
        $queryArray = array();
        $queryArray['query']['filtered']['filter']['term']["event_id"] = $event_id;

        $search = new \Elastica\Search($this->esclient);
        $resSet = $search->addIndex('oversea_item')->addType('show')->search($queryArray);
        $_ids = array();
        foreach($resSet as $doc) {
            $_ids[] = $doc->getId();
        }
        return $_ids;
    }

}