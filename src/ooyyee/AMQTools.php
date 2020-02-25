<?php

namespace ooyyee;

use think\facade\Log as ThinkLog;
use \AMQPChannel;
use \AMQPExchange;
use \AMQPQueue;
use \AMQPConnection;

class AMQTools
{
	private static $cache_exchange = array ();
	private static $cache_conn = array ();
	private static $slow_queue_route = array ();
    private $mq_queue_name ;
    private $mq_exchange;
    private $mq_channel ;
    private $cache_bind_list = array ();
	const DEFAULT_QUEUE_NAME = 'task_queue';
	const SLOW_QUEUE_NAME = 'slow_task_queue';
	const EXCHANGE_NAME = 'exchange_';

    /**
     * AMQTools constructor.
     * @param AMQPConnection $conn
     * @param string $exchangeName
     * @param string $queueName
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
	private function __construct(AMQPConnection $conn, $exchangeName = '', $queueName = AMQTools::DEFAULT_QUEUE_NAME){
		if (! $conn->isConnected ()) {
			$conn->connect ();
		}
		$this->buildExchange ( $conn, $exchangeName, $queueName );
	}
	
	/* 创建交换机 */
    /**
     * @param $conn
     * @param string $exchangeName
     * @param string $queueName
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
	private function buildExchange($conn, $exchangeName = '', $queueName = AMQTools::DEFAULT_QUEUE_NAME){
		$this->mq_queue_name = $queueName;
		// 创建exchange名称和类型
		$channel = new AMQPChannel ( $conn );
		$this->mq_channel = $channel;
		
		$exchange = new AMQPExchange ( $channel );
		$exchange->setName ( $exchangeName );
		$exchange->setType ( AMQP_EX_TYPE_DIRECT );
		$exchange->setFlags ( AMQP_DURABLE ); // 持久化
		$exchange->declareExchange ();
		$this->mq_exchange = $exchange;
	}
	
	/* 连接队列 */
    /**
     * @return mixed
     * @throws \AMQPConnectionException
     */
	private static function getAMQConn(){
		$vhost = config ( 'amq.vhost' );
		if (isset ( self::$cache_conn[$vhost] ) && self::$cache_conn[$vhost] instanceof \AMQPConnection) {
			return self::$cache_conn[$vhost];
		}
        // 连接RabbitMQ
        $conn = new AMQPConnection ( config ( 'amq.' ) );
        $conn->connect ();
        self::$cache_conn[$vhost] = $conn;
		return self::$cache_conn[$vhost];
	}
	
	/* 实例化 */
    /**
     * @param string $routeKey
     * @param string $queueName
     * @return AMQTools
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
	private static function getInstance($routeKey = '', $queueName = AMQTools::DEFAULT_QUEUE_NAME){
		if (in_array ( $routeKey, self::$slow_queue_route )) {
			$queueName = self::SLOW_QUEUE_NAME;
		} else {
			$queueName = $queueName ? : self::DEFAULT_QUEUE_NAME;
		}
		
		$exchangeName = self::EXCHANGE_NAME . $queueName;
		$cache_key = config ( 'amq.vhost' ) . ':' . $exchangeName;
		if (! isset ( self::$cache_exchange[$cache_key] )) {
			$conn = self::getAMQConn ();
			$obj = new AMQTools ( $conn, $exchangeName, $queueName );
			self::$cache_exchange[$cache_key] = $obj;
		}
		return self::$cache_exchange[$cache_key];
	}
	

    /**
     * 绑定队列
     * @param $routeKey
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
	private function bindRouteKey($routeKey){
		if (! isset ( $this->cache_bind_list[$routeKey] )) {
			// 创建queue名称，使用exchange，绑定routingkey
			$queue = new AMQPQueue ( $this->mq_channel );
			$queue->setName ( $this->mq_queue_name );
			$queue->setFlags ( AMQP_DURABLE ); // 持久化
			$queue->declareQueue ();
			$queue->bind ( $this->mq_exchange->getName (), $routeKey );
		}
	}
	
	/* 发送消息 */
    /**
     * @param $routeKey
     * @param $message
     * @param string $queueName
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
	public static function send($routeKey, $message, $queueName = ''){
		$obj = self::getInstance ( $routeKey, $queueName );
		$obj->bindRouteKey ( $routeKey );
		$obj->_sendMsg ( $routeKey, $message );
	}
	
	/* push msg */
    /**
     * @param $routeKey
     * @param $message
     */
	private function _sendMsg($routeKey, $message){
		// 消息发布
		// $channel->startTransaction();
		// 同一时刻，不要发送超过1条消息给一个工作者
		// $channel->qos(0,1);
		$res = $this->mq_exchange->publish ( serialize ( $message ), $routeKey, AMQP_NOPARAM, array (
			'delivery_mode' => 2,
			'priority' => 9 
		) );
		// $channel->commitTransaction();
	}
	
	/* 关闭连接 */
    /**
     *
     */
	public function __destruct(){
		foreach ( self::$cache_conn as $conn ) {
			$conn->disconnect ();
		}
	}


    /**
     * 添加异步队列
     * @param string $name
     * @param array $msg
     * @param string $queueName
     * @return int|string
     */
	public static function sendMsg($name, $msg, $queueName = ''){
		$data['name'] = $name;
		$data['create_time'] = time ();
		$data['data'] = json_encode ( $msg );
		$data['status'] = 0;
		$id = db ( 'async_queue' )->insertGetId ( $data );
		$data['id'] = $id;
		try {
			if (class_exists ( \AMQPConnection::class )) {
				self::send ( $name, $data, $queueName );
			}
		} catch ( \Exception $e ) {
			ThinkLog::error ( array (
				'message' => $e->getMessage (),
				'file' => $e->getFile (),
				'line' => $e->getLine () 
			) );
		}
		return $id;
	}
}

