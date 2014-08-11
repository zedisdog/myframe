<?php 
/**
 * 初始化文件
 * 
 * 用于对整个网站的初始化
 * 
 * @version	add by zed 2013-10-21 17:16
 *         	modified by zed 2013-10-28	#添加自动生成默认文件夹及文件特性
 *          modified by zed 2014-1-20   #去掉生成文件最后的换行符
 */
date_default_timezone_set('Asia/Chongqing');
if(!is_dir(APP_ROOT)){//创建app文件夹
	mkdir(APP_ROOT,0775);
}

if(!is_dir(APP_ROOT.DS.'config')){//创建config文件夹
	mkdir(APP_ROOT.DS.'config',0775);
}
if(!is_file(APP_ROOT.DS.'config'.DS.'config.php')){//创建config配置文件
	$file = APP_ROOT.DS.'config'.DS.'config.php';
	touch($file);
	chmod($file,0775);
	$fp = fopen($file,'w+');
	fputs($fp,"<?php\n");
	fputs($fp,"/**\n * 配置文件（test）\n *\n * 以下为测试数据，请自行修改\n */\n");
	fputs($fp,"return ");
	
	$config = array(
		'DB_HOST' => 'localhost',
	    'DB_USER' => 'root',
	    'DB_PWD' => '',
	    'DB_NAME' => '',
	    'DB_LOG_PATH' => '',
	    
		'WB_ID' => '',
	    'WB_APPKEY' => '',
	    'WB_CALLBACK_URL' => '',
	    
		'QQ_APPID'=>'',
	    'QQ_APPKEY'=>'',
	    'QQ_CALLBACK'=>'',
	    'QQ_SCOPE'=>'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr',
	    'QQ_ERROR_REPORT'=>true,
	    'QQ_STORAGE_TYPE'=>'file',
	    'QQ_HOST'=>'localhost',
	    'QQ_USER'=>'',
	    'QQ_PASSWORD'=>'',
	    'QQ_DATABASE'=>'',
	    
	    'MAIL_SERVER' => 'smtp.163.com',
	    'MAIL_SERVER_MAIL'=>'',
	    'MAIL_USER' => '',
	    'MAIL_PASS' => '',
	    'MAIL_PORT' => '25',
	);
	
	$config = var_export($config,true);
	fputs($fp,$config);
	fputs($fp,";");
	/*
	 * TODO:初始化第三方登陆以及邮箱的配置
	 */
	fclose($fp);
}

if(!is_dir(APP_ROOT.DS.'controlers')){//创建controlers文件夹
	mkdir(APP_ROOT.DS.'controlers',0775);
}
if(!is_file(APP_ROOT.DS.'controlers'.DS.'IndexCon.class.php')){//创建controlers默认首页控制器类
	$file = APP_ROOT.DS.'controlers'.DS.'IndexCon.class.php';
	touch($file);
	chmod($file,0775);
	$fp = fopen($file,'w+');
	fputs($fp,"<?php\n");
	fputs($fp,"class IndexCon extends BaseCon {\n");
	fputs($fp,"    function index() {\n");
	fputs($fp,"        \$this->display('index.html');\n");
	fputs($fp,"    }\n");
	fputs($fp,"}\n");
	fputs($fp,"?>");
	fclose($fp);
}

if(!is_dir(APP_ROOT.DS.'model')){//创建model文件夹
	mkdir(APP_ROOT.DS.'model',0775);
}

if(!is_dir(dirname(APP_TMP))){//创建templates文件夹
	mkdir(dirname(APP_TMP),0775);
	mkdir(APP_TMP,0775);
}
if(!is_file(APP_TMP.DS.'index.html')){//创建controlers默认首页控制器类
	$file = APP_TMP.DS.'index.html';
	touch($file);
	chmod($file,0775);
	$fp = fopen($file,'w+');
	fputs($fp,"<!DOCTYPE HTML>\n<html lang=\"zh\">\n<meta charset=\"UTF-8\">\n<title>默认首页模板</title>");
	fputs($fp,"<body style=\"text-align:center;\">\n默认首页模板\n</body>\n</html>");
	fclose($fp);
}


$config = require_once APP_ROOT.'/config/config.php';
//注册自定义自动加载函数
spl_autoload_register('autoload');

?>