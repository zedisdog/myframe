<?php
class db{
    private $sql_op = array();
    private $sql = '';
    private $SqlLink;
    private $pResourceId;
    private $bIsLog;
    private $pLogHandle;
    private $result;
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
        if(isset($db_config['dbport'])){
        	$host = $db_config ['dbhost'].':'.$db_config['dbport'];
        }else{
        	$host = $db_config ['dbhost'];
        }
        $this->SqlLink = mysql_connect ( $host/*配置项：主机名*/, $db_config ['dbuser']/*配置项：用户名*/, 
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
     * 魔法call
     *
     * 收集数据库操作参数
     * 
     * 
     */
    function __call($func,$param){
        $exist_op = array('select','from','where','order_by','update','delete','insert','set','select_one','limit','data');

        if(in_array($func,$exist_op)){
            $this->sql_op[$func] = $param;
            return $this;
        }
    }

    /**
     * 结束方法
     * 
     * 在所有连贯操作之后调用，必须,不然无法执行
     *
     *
     */
    function end(){
        if (array_key_exists('select',$this->sql_op)||array_key_exists('select_one', $this->sql_op)){//取一条记录或者去多条记录都在select方法里面
            $this->select();
            $this->sql_op = array();
            return $this->result;
        }else if (array_key_exists('update', $this->sql_op)){
            $this->update();
            $this->sql_op = array();
            return $this->result;
        }else if (array_key_exists('delete', $this->sql_op)){
            $this->delete();
            $this->sql_op = array();
            return $this->result;
        }else if (array_key_exists('insert', $this->sql_op)){
            $this->insert();
            $this->sql_op = array();
            return $this->result;
        }
    }

    /**
     * select语句构造方法
     *
     * 调用例子：$db->select/select_one('*')->from('admin')->where(array('id'=>1))->order_by('id')->limit(0,10)->end();
     *
     */
    private function select(){
        //将列名加(`)符号,避免与关键字冲突
        $col = isset($this->sql_op['select'][0])?$this->sql_op['select'][0]:$this->sql_op['select_one'][0];
        if(is_array($col)){
            $col = implode('`,`', $col);
            $col = '`'.$col.'`';
        }

        $this->sql = 'SELECT '.$col.' FROM '.$this->sql_op['from'][0];

        //如果有where参数，则构造where子句
        if(isset($this->sql_op['where'][0])){
            
            $this->sql .= ' WHERE '.$this->where();
            $this->sql = trim($this->sql);
        }

        //如果有order by 参数，则构造order by子句
        if(isset($this->sql_op['order_by'])){
            $tmp = $this->sql_op['order_by'];
            $tmp[0] = '`'.$tmp[0].'`';
            if(!isset($tmp[1])){
                $tmp[1] = 'DESC';
            }
            $tmp = implode(' ', $tmp);
            $this->sql .= ' ORDER BY '.$tmp;
        }

        //如果有limit参数，则构造limit子句
        if(isset($this->sql_op['limit'])){
            $tmp = implode(',', $this->sql_op['limit']);
            $this->sql .= ' LIMIT '.$tmp;
        }
//         echo $this->sql;
//         exit;
        //执行语句
        $this->query();
    }

    /**
     * update语句构造方法
     *
     * 调用例子：$db->update('user')->set(array('id'=>1,'pwd'=>123))->where(array('id'=>2,'pwd'=>321),'and')->end();
     *
     */
    private function update(){
        //将列名加(`)符号,避免与关键字冲突
        if(count($this->sql_op['update'])>1){
            $tmp = implode('`,`', $this->sql_op['update']);
            $tmp = '`'.$tmp.'`';
        }else{
            $tmp = '`'.$this->sql_op['update'][0].'`';
        }

        $this->sql = 'UPDATE '.$tmp.' SET ';

        //构造数据
        $tmp = '';
        foreach ($this->sql_op['set'][0] as $key => $value) {
            $tmp .= '`'.$key.'`="'.$value.'",';
        }
        $tmp = substr($tmp, 0,-1);
        
        //如果有where参数，则构造where子句
        if(isset($this->sql_op['where'][0])){
          $this->sql .= $tmp.' WHERE '.$this->where();
        }else{
          $this->sql .= $tmp;
        }
        $this->sql = trim($this->sql);
        //echo $this->sql;
        //exit;
        //执行语句
        $this->query();
    }

    /**
     * insert语句构造方法
     *
     * 调用例子：$db->insert('user')->data(array('id'=>1,'pwd'=>234))->end();
     *
     */
    private function insert(){
        //将列名加(`)符号,避免与关键字冲突
        if(count($this->sql_op['insert'])>1){
            $tmp = implode('`,`', $this->sql_op['insert']);
            $tmp = '`'.$tmp.'`';
        }else{
            $tmp = '`'.$this->sql_op['insert'][0].'`';
        }

        $this->sql = 'INSERT INTO '.$tmp;

        //构造数据
        $tmp = $this->sql_op['data'][0];
        $tmp_key = array_keys($tmp);
        $tmp_key = implode('`,`', $tmp_key);
        $tmp_key = '`'.$tmp_key.'`';
        $this->sql .= '('.$tmp_key.') VALUES';

        $tmp = implode('","', $tmp);
        $tmp = '"'.$tmp.'"';
        $this->sql .= '('.$tmp.')';

        //执行语句
        $this->query();
    }

    /**
     * delete语句构造方法
     *
     * 调用例子：$db->delete('user')->where(array('id'=>1,'pwd'=>123,'name'=>321),'or')->end();
     *
     */
    private function delete(){
        //将列名加(`)符号,避免与关键字冲突
        if(count($this->sql_op['delete'])>1){
            $tmp = implode('`,`', $this->sql_op['delete']);
            $tmp = '`'.$tmp.'`';
        }else{
            $tmp = '`'.$this->sql_op['delete'][0].'`';
        }

        $this->sql = 'DELETE FROM '.$tmp.' WHERE '.$this->where();

        //执行语句
        $this->query();
    }

    /**
     * where语句构造方法
     *
     * 一定是数组形式，调用例子：where(array('id'=>2,'pwd'=>321),'and')
     *
     */
    private function where(){
        //判断逻辑连接词，默认用and
        if(isset($this->sql_op['where'][1])){
            $logic = strtoupper($this->sql_op['where'][1]);
        }else{
            $logic = 'AND';
        }

        //构造where子句
        if(is_array($this->sql_op['where'][0])){
            $tmp = '';
            if(count($this->sql_op['where'][0])>1){
                foreach ($this->sql_op['where'][0] as $key => $value) {
                    if(isset($this->sql_op['where'][2]) && $this->sql_op['where'][2]=='like'){
                      $tmp .= '`'.$key.'` LIKE "'.$value.'" '.$logic.' ';
                    }elseif(isset($this->sql_op['where'][2]) && $this->sql_op['where'][2]=='neq'){
                    	$tmp .= '`'.$key.'` != "'.$value.'" '.$logic.' ';
                    }else{
                      $tmp .= '`'.$key.'`="'.$value.'" '.$logic.' ';
                    }
                }
                $tmp = substr($tmp,0,-4);
            }else{
                foreach ($this->sql_op['where'][0] as $key => $value) {
                  if(isset($this->sql_op['where'][2]) && $this->sql_op['where'][2]=='like'){
                    $tmp .= '`'.$key.'` LIKE "'.$value.'"';
                  }else{
                    $tmp .= '`'.$key.'`="'.$value.'"';
                  }
                }
            }
        }else{
            exit('请使用数组形式');
        }
        return $tmp;
    }

    function query($is_assoc=true){
        if (isset($this->sql_op['select'])){
            $this->pResourceId = mysql_query($this->sql, $this->SqlLink);
            if($this->pResourceId){
              while ($r = mysql_fetch_array($this->pResourceId,($is_assoc?MYSQL_ASSOC:MYSQL_NUM))){
                  $result[] = $r;
              }
            }else{
              $result = NULL;
            }
        }else if (isset($this->sql_op['select_one'])){
            $this->pResourceId = mysql_query($this->sql, $this->SqlLink);
            if(isset($this->pResourceId) && $this->pResourceId){
            	$result = mysql_fetch_array($this->pResourceId,($is_assoc?MYSQL_ASSOC:MYSQL_NUM));
            }else{
            	$result = NULL;
            }
        }else if (isset($this->sql_op['update'])){
            mysql_query($this->sql, $this->SqlLink);
            $result = mysql_affected_rows();
        }else if (isset($this->sql_op['insert'])){
            mysql_query($this->sql, $this->SqlLink);
            $result = mysql_insert_id();
        }else if (isset($this->sql_op['delete'])){
            mysql_query($this->sql, $this->SqlLink);
            $result = mysql_affected_rows();
        }
        $this->result = isset($result)?$result:NULL;
    }
    
    function query_sql($sql){
    	$this->pResourceId = mysql_query($sql,$this->SqlLink);
     	if($this->pResourceId){
              while ($r = mysql_fetch_array($this->pResourceId,MYSQL_ASSOC)){
                  $result[] = $r;
              }
       }else{
              $result = NULL;
       }
       if(count($result)<1){
       		$result = NULL;
       }
       return $result;
    }
}
?>