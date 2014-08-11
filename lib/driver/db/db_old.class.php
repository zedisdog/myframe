<?php
/**
 * 数据库操作类类文件（单件模式）
 * 
 * @version	create by zed 2011-08-31 17:00
 * 			add by zed 2011-10-22 09:13
 * 			modified by zed 2011-11-15 #function select
 */
class db {
    private $SqlLink;
    private $pResourceId;
    private $bIsLog;
    private $pLogHandle;
    static $pInstance;
    
    /**
     * 构造函数
     * 
     * 链接数据库等初始化操作，其他所需参数由配置文件带入。生成数据库链接后整个对象存入$pInstance
     * 
     * @param bool $islog 日志生成开关
     * 
     */
    private function __construct($islog) {
        global $db_config;
        $this->bIsLog = $islog;
        //echo 123;
	    $this->SqlLink = mysql_connect ( $db_config ['dbhost']/*配置项：主机名*/, $db_config ['dbuser']/*配置项：用户名*/, 
	    		$db_config ['dbpwd']/*配置项：密码*/); // 连接数据库
        if (! $this->SqlLink) {
			echo '连接数据库失败：' . mysql_error ();
			$this->write_log ( '连接数据库失败：' . mysql_error () );
			exit ();
		}
		
		if (! mysql_select_db ( $db_config ['dbname']/*配置项：库名*/, $this->SqlLink )) {
			echo '选择数据库失败' . mysql_error ();
			$this->write_log ( '选择数据库失败：' . mysql_error () );
			exit ();
		}
		mysql_query ( 'set names utf8' );//设置编码
		
		// 获取日志文件句柄
		if ($this->bIsLog) {
			$this->pLogHandle = fopen ( $db_config ["logfilepath"] . "dblog.txt", "a+" );
		}
		
		$this->write_log ( '实例化数据库类' );
	}
	
	/**
	 * 写日志函数
	 * 
	 * @param string $msg //报错说明
	 */
	private function write_log($msg = '') {
		if ($this->bIsLog) {
			$text = date ( "Y-m-d H:i:s" ) . " " . $msg . "\r\n";
			fwrite ( $this->pLogHandle, $text );
		}
	}
	
	/**
	 * 取数据库操作对象（单件）
	 * 
	 * @param bool $islog 写日志开关
	 * @return db 返回数据库操作对象
	 */
	static function getDb($islog = false) {
		if (! self::$pInstance) {
			self::$pInstance = new self ( $islog );
		}
		return self::$pInstance;
	}
	
	/**
	 * 执行语句
	 * 
	 * 资源ID存入$pResourceId
	 * 
	 * @param string $sql sql语句
	 * @return bool
	 */
	public function query($sql) {
		$this->pResourceId = mysql_query ( $sql, $this->SqlLink );
		if (! $this->pResourceId) {
			echo '执行sql失败：' . mysql_error ().'<br />'.$sql;
			$this->write_log ( '执行sql失败：' . mysql_error () .'<br />'.$sql);
			exit ();
		}
		$this->write_log ( '执行sql：' . $sql );
		return true;
	}
	
	/**
	 * 拼接select语句
	 * 
	 * @param string $table 表名
	 * @param array/string $data 列名 ==>'colName' or array('colName1','colName2')
	 * @param string $order 排序  ==> 'colName-orderType'
	 * @param string $where where语句==>'id=1'
	 * @return bool
	 */
	public function select($table, $data=NULL, $where=NULL, $order=NULL) {
		$cols = false;
		if($order){
			if(strpos($order,'-')==false){
				return false;
			}else{
				$order = explode('-',$order);
			}
		}
		if (is_array ( $data ) && count ( $data ) <= 0) {
			$cols = '*';
		} else if(!is_array($data)) {
			$cols = $data;
		}
		if (! $cols) {
			$cols = implode ( $data, '`,`' );
			$cols = '`' . $cols . '`';
		}
		if(strpos($table,'`')){
			$table = '`'.$table.'`';
		}
		if($where){
			if(!$order) {
				$sql = "select $cols from $table where $where";
			}else {
				$sql = "select $cols from $table where $where order by $order[0] $order[1]";
			}
		}else{
			if(!$order) {
				$sql = "select $cols from $table";
			}else {
				$sql = "select $cols from $table order by $order[0] $order[1]";
			}
		}
		//exit($sql);
		//echo $sql.'<br />';
		if (! $this->query ( $sql )) {
			$this->write_log ( '查询失败：' . mysql_error () . '--语句：' . $sql );
			return false;
		}
		$this->write_log ( '查询：' . $sql );
		return true;
	}
	
	/**
	 * 拼接insert语句
	 * 
	 * @param string $table 表名
	 * @param array $dataArray 要设置的字段名以及值=>array('name'=>'123')
	 * @return bool
	 */
	public function insert($table, $dataArray) {
		$field = '';
		$value = '';
		if (! is_array ( $dataArray ) || count ( $dataArray ) <= 0) {
			exit ( '没有要插入的数据' );
		}
		while ( list ( $key, $val ) = each ( $dataArray ) ) {
			$field .= "`$key`,";
			$value .= "'$val',";
		}
		$field = substr ( $field, 0, - 1 );
		$value = substr ( $value, 0, - 1 );
		$sql = "insert into `$table`($field) values($value)";
		if (! $this->query ( $sql )) {
			$this->write_log ( '插入失败：' . mysql_error () . '--语句：' . $sql );
			return false;
		}
		$this->write_log ( '插入：' . $sql );
		return true;
	}
	
	/**
	 * 拼接delete语句
	 * 
	 * @param string $table 表名
	 * @param string $condition 条件语句
	 * @return bool
	 */
	public function delete($table, $condition = "") {
		if (empty ( $condition )) {
			exit ( '没有设置删除的条件' );
		}
		$sql = "delete from `$table` where 1=1 and $condition";
		if (! $this->query ( $sql )) {
			$this->write_log ( '删除失败：' . mysql_error () . '--语句：' . $sql );
			return false;
		}
		$this->write_log ( '删除：' . $sql );
		return true;
	}
	
	/**
	 * 拼接update语句
	 * 
	 * @param string $table 表名
	 * @param array $dataArray	要更新的字段名以及值=>array('name'=>'123')
	 * @param string $condition 条件
	 * @return bool
	 */
	public function update($table, $dataArray, $condition = "") {
		if (! is_array ( $dataArray ) || count ( $dataArray ) <= 0) {
			var_dump($dataArray);
			exit ( '没有要更新的数据' );
		}
		$value = '';
		foreach ($dataArray as $key => $val) {
			$value .= "`$key` = '$val',";
		}
		$value = substr ( $value, 0, - 1 );
		$sql = "update `$table` set $value where 1=1 and $condition";
		if (! $this->query ( $sql )) {
			$this->write_log ( '更新失败：' . mysql_error () . '--语句：' . $sql );
			return false;
		}
		$this->write_log ( '更新：' . $sql );
		return true;
	}
	
	/**
	 *  获取所有条目
	 *  
	 *  @param string $method mysql_fetch_array函数获取方式
	 *  @return array
	 */
	public function fetch($method = MYSQL_ASSOC) {
		$rows = array (); // 所有行
		$row; // 单行;
		if ($this->pResourceId) {
			while ( $row = mysql_fetch_array ( $this->pResourceId, $method ) ) {
				$rows [] = $row;
			}
			$this->write_log ( '获取所有条目' );
			return $rows;
		}
		$this->write_log ( '获取所有条目失败' );
		return false;
	}
	
	/**
	 * 获取单条数据
	 * 
	 * @param string $method mysql_fetch_array函数获取方式
	 * @return array|boolean
	 */
	public function fetch_one($method = MYSQL_ASSOC) {
		$row;
		if ($this->pResourceId) {
			$row = mysql_fetch_array ( $this->pResourceId, $method );
			$this->write_log ( '获取单个条目' );
			return $row;
		}
		$this->write_log ( '获取单个条目失败' );
		return false;
	}
	
	/**
	 * 获取结果记录数
	 * 
	 * @return int
	 */
	public function getNumOfResult() {
		$rows =  mysql_num_rows($this->pResourceId);
		return $rows;
	}
	
	/**
	 * 获取上一步插入的id值
	 * 
	 * @return int
	 */
	public function getInsertId(){
		return mysql_insert_id();
	}
}
?>