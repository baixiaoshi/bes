<?php
/**
 * Created by PhpStorm.
 * User: tiansheng.deng
 * Date: 2015/5/26
 * Time: 9:54
 */
require_once APPPATH . '/controllers/task/task_base.php';

use beibei\service\elasticsearch\index\ItemRealtimeService;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Itemrealtime_controller extends Task_base {

    public  $item_rt_serv;
    public  $channel;
    public  function __construct(){

        $this->log_file_name = "item_realtime_log";
        parent::__construct();
        $this->item_rt_serv = ItemRealtimeService::get_instance();
        $connection = new AMQPConnection('10.1.3.31','5672','beibei','beibei','beibei');
        $this->channel = $connection->channel();
    }

    public function listen()
    {


        $this->channel->queue_declare(
            'es.item_show_realtime',    #queue
            false,              #passive
            true,               #durable, make sure that RabbitMQ will never lose our queue if a crash occurs
            false,              #exclusive - queues may only be accessed by the current connection
            false               #auto delete - the queue is deleted when all consumers have finished using it
        );

        $this->channel->basic_qos(
            null,   #prefetch size - prefetch window size in octets, null meaning "no specific limit"
            1,      #prefetch count - prefetch window in terms of whole messages
            null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
        );

        $this->channel->basic_consume(
            'es.item_show_realtime',        #queue
            '',                     #consumer tag - Identifier for the consumer, valid within the current channel. just string
            false,                  #no local - TRUE: the server will not send messages to the connection that published them
            false,                  #no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
            false,                  #exclusive - queues may only be accessed by the current connection
            false,                  #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
            array($this, 'process') #callback
        );

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->channel->close();
    }

    public function process(AMQPMessage $msg)
    {
        $this->channel->basic_ack($msg->delivery_info['delivery_tag']);
        $event_id = $msg->body;
        $this->monolog->addInfo('event update : '.$event_id);
        if (is_numeric($event_id)) {
            $res = $this->item_rt_serv->update_martshow_item_index($event_id);
        }
    }
}
