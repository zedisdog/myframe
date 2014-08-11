<?php 
/**
 * 公共函数
 * 
 * @version	add by zed 2013-10-21
 * 			add function getCom
 * 			modified by zed 2013-10-25 15:57 -> modified function autoload & add function require_array
 * 			modified by zed 2013-10-25 16:29
 *         	modified by zed 2013-10-25 #DS
 *			modified by zed 2013-12-4
 *          modified by zed 2014-02-23 18:11 #getImgPath
 */
require_once 'init.php';//初始化文件


/**
 * 自动加载函数
 * 
 * 自定义自动加载函数，避免与其他代码的自动加载冲突
 * 
 * @param string $class 类名
 */
function autoload($class) {
	$filename = $class.'.class.php';
	if($class=='Smarty') {//包含smarty主文件
		require_once ZED_ROOT.DS.'views'.DS.'libs'.DS.$filename;
	} else if(strpos($class,'Smarty')!==false){
		require_once ZED_ROOT.DS.'views'.DS.'libs'.DS.'sysplugins'.DS.strtolower($class).'.php';		
	} else if($class=='db'){//数据库操作类文件
		require_once ZED_DRIVER.DS.'db'.DS.$filename;
	} else if(substr($class,-3)=='Con'){//包含模块类文件
		require_array(array(APP_CON.DS.$filename, ZED_CORE.DS.$filename));
	} else if(substr($class,-5)=='Model') {//包含模型类文件
		require_array(array(APP_MODEL.DS.$filename,ZED_CORE.DS.$filename));
	}else if($class=='SaeTOAuthV2' or $class=='SaeTClientV2'){//包含微博开放平台api类
		require_array(array(ZED_DRIVER.DS.'plus'.DS.'saetv2.ex.class.php'));
	}else if($class=='QC'){//包含qq互联开放平台api类
		require_once(ZED_DRIVER.DS.'plus'.DS.'qqapi'.DS.'qqConnectAPI.php');
	}else if($class=='smtp'){//包含邮件发送类
		require_once(ZED_DRIVER.DS.'plus'.DS.'email.class.php');
	}else{
	  require_once (ZED_DRIVER.DS.'plus'.DS.$filename);
	}
}

/**
 * 解析url
 * 
 * @param	string	$sCom	格式化的命令字符串=> mod[-action/id-3-type-3]
 * @return	array	$com	解析后的以数组形式组织的命令
 */
function get_Array_Com($sCom) {
	$sCom = str_replace('&','-',$sCom);
	$sCom = str_replace('=','-',$sCom);
	if(!strpos($sCom,'/') && strpos($sCom,'-')){
		$sCom = explode('-', $sCom);
		$tmp = array();
		$tmp[0] = $sCom[0];
		$tmp[1] = $sCom[1];
		unset($sCom[0]);
		unset($sCom[1]);
		$r[] = implode('-', $tmp);
		$r[] = implode('-', $sCom);
		$sCom = $r;
	}else{
		$sCom = explode('/',$sCom);
		if(count($sCom)>2){//参数里面有斜杠的解决方案
			$sCom = implode('/',$sCom);
			$tmp = substr($sCom,0,strpos($sCom,'/'));
			$p = substr($sCom,strpos($sCom,'/')+1);
			$sCom = array();
			$sCom[0] = $tmp;
			$sCom[1] = $p;
		}
	}
	$tmp = explode('-',$sCom[0]);
	if(count($tmp)==2){
		$com['ctrl'] = $tmp[0]?$tmp[0].'Con':'';
		$com['act'] = isset($tmp[1])?$tmp[1]:'index';
	}else{
		$com['ctrl'] = $tmp[0]?$tmp[0].'Con':'';
		$com['act'] = 'index';
	}
	if(count($sCom)>1){
		$paras = array_pop($sCom);
		$paras = array_values(array_filter(explode('-',$paras)));
	}
	if(isset($paras)) {
		if(count($paras)%2!=0) {
			var_dump($paras);
			echo '参数不匹配';
			return false;
		}
		for($i=0;$i<count($paras);$i=$i+2) {
			$para[$paras[$i]] = $paras[$i+1];
		}
		$com['paras'] = isset($para)?$para:'';
	}
	return $com;
}

/**
 * 获取get方式参数
 * 
 * @param string $paraName
 * @return bool/mixed
 */
function get($paraName) {
	$com = get_Array_Com(filtrate($_SERVER['QUERY_STRING']));
	return isset($com['paras'][$paraName])?$com['paras'][$paraName]:false;
}

/**
 * 获取post方式参数
 * 
 * @param str $paraName
 * @return str
 */
function post($paraName){
	if(isset($_POST[$paraName])){
		return filtrate($_POST[$paraName]);
	}else{
		return false;
	}
}
/**
 * 以数组形式包含文件
 * 
 * 奖文件可能所在所有目录组成数组传入，函数判断文件是否存在，存在即包含
 * 
 * @param array $arr
 * @return boolean
 */
function require_array($arr) {
	if(!is_array($arr)) {
		return false;
	}
	foreach($arr as $path) {
		if(is_file($path)){
			require_once $path;
			return true;
		}
	}
}

/**
 * 过滤危险字符
 * 
 * @param str $str
 * @return str
 */
function filtrate($str){
	$str = trim($str);
	$str = str_replace('select','',$str);
	$str = str_replace('/*','',$str);
	$str = str_replace('*/','',$str);
	return $str;
}

/**
 * 获取ip地址
 * 
 * @return str
 */
function getIP(){
  global $ip;
  if (getenv("HTTP_CLIENT_IP"))
    $ip = getenv("HTTP_CLIENT_IP");
  else if(getenv("HTTP_X_FORWARDED_FOR"))
    $ip = getenv("HTTP_X_FORWARDED_FOR");
  else if(getenv("REMOTE_ADDR"))
    $ip = getenv("REMOTE_ADDR");
  else $ip = "Unknow";
  return $ip;
}

/**
 *破例获取数据库类
 *
 * @return object
 */
function getdb($islog){
	return db::getDb($islog);
}

/**
 * 字符串转16进制
 *
 * @return str
 */
function   str2Hex($string)   
{   
      $hex="";   
      for   ($i=0;$i<strlen($string);$i++)   
      $hex.=dechex(ord($string[$i]));   
      $hex=strtoupper($hex);   
      return   $hex;   
}   

/**
 * 16进制转字符串
 * 
 * @return str
 */
function   hex2Str($hex)   
{   
      $string="";   
      for   ($i=0;$i<strlen($hex)-1;$i+=2)   
      $string.=chr(hexdec($hex[$i].$hex[$i+1]));   
      return   $string;   
}

/**
 * 获取图片路径
 * @param str $strArr
 * @return array
 * 
 * 未完成，路径已固定，事后做修改
 */
function getImgPath($strArr){
  $strArr = explode(',',$strArr);
  foreach ($strArr as $pic){
    $t[] = 'http://'.$_SERVER['HTTP_HOST'].'/img/'.$pic;
  }
  return $t;
}
?>