<?php 
/**
 * 入口文件
 *
 * 解析url，自动加载模块等
 *
 * @version rewrite by zed 2013-11-8
 * 
 */

error_reporting(E_ALL);
$ds = DIRECTORY_SEPARATOR;
define('DS',$ds);
unset($ds);
defined('ZED_ROOT') or define('ZED_ROOT',dirname(__FILE__));//框架路径
defined('ZED_CORE') or define('ZED_CORE',ZED_ROOT.DS.'lib'.DS.'core');//内核路径
defined('ZED_DRIVER') or define('ZED_DRIVER',ZED_ROOT.DS.'lib'.DS.'driver');//设备路径
defined('ZED_VIEW') or define('ZED_VIEW',ZED_ROOT.DS.'views');//视图路径

if(defined('APP_NAME')) {//根据app名称定义根路径
	define('APP_ROOT',dirname(ZED_ROOT).DS.APP_NAME);
} else {
	define('APP_ROOT',dirname(ZED_ROOT));
}
define('APP_TMP',APP_ROOT.DS.'views'.DS.'templates');//定义模板路径
define('APP_TMP_C',APP_ROOT.DS.'views'.DS.'templates_c');//定义编译文件路径
define('APP_CON',APP_ROOT.DS.'controlers');//定义控制器路径
define('APP_MODEL',APP_ROOT.DS.'model');//定义模型路径
define('APP_CONFIG',APP_ROOT.DS.'config');//定义配置

require_once 'public/comment.function.php';
$sCom = substr(strstr($_SERVER['REQUEST_URI'],'?'),1);//取问号之后的字符串
if(!$sCom) {//默认加载index模块的index方法
	$con = new IndexCon();
	$con->index();
}else{
	$com = get_Array_Com($sCom);
	if(!$com) {
		echo '模块加载失败';
		exit;
	}else if(!$com['ctrl']) {
		$con = new IndexCon();
		$con->$com['act']();
	} else {
		$con = new $com['ctrl']();
		$con->$com['act']();
	}
}
?>