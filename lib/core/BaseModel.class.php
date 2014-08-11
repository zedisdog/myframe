<?php 
/**
 * 模型基础类
 *
 * 初始化数据库等
 *
 * @version	add by zed 2013-11-15
 *
 */
class BaseModel {
	protected $db;
	protected $data;
	protected $id;
	/**
	 * 构造函数
	 */
	function BaseModel(){
		$this->db = db::getDb();
	}
	
	function __set($name,$value){
		switch ($name){
			case 'id':
				$this->id=$value;
				break;
			default:
				$this->data[$name] = $value;
		}
	}

	function getSMTP(){
		global $email_config;
		$smtp = new smtp($email_config['server'],$email_config['port'],true,$email_config['user'],$email_config['pass']);
		$smtp->debug = false;
		return $smtp;
	}
}
?>