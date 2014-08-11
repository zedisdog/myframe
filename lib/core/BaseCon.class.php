<?php 
/**
 * 控制器基础类
 * 
 * 初始化模板引擎等
 * 
 * @version	add by zed 2013-10-21
 * 			modified by zed 2013-11-14 #24,25
 *
 */
class BaseCon {
	protected $smarty;
	/**
	 * 构造函数
	 */
	function BaseCon() {
		//创建smarty模板引擎对象
		$this->smarty = new Smarty();
		$this->smarty -> template_dir = APP_TMP; //模板存放目录
		$this->smarty -> compile_dir = APP_TMP_C; //编译目录
		$this->smarty -> left_delimiter = '<{'; //左定界符
		$this->smarty -> right_delimiter = '}>'; //右定界符
		
		//根据APP_ROOT构造css,js路径
		if(defined('APP_NAME')){
			$this->assign('TmpPath',str_replace('\\','/','http://'.$_SERVER['HTTP_HOST'].DS.APP_NAME.DS.'views'.DS.'templates'));
		}else{
			$this->assign('TmpPath',str_replace('\\','/','http://'.$_SERVER['HTTP_HOST'].DS.'views'.DS.'templates'));
		}
	}
	
	/**
	 * 设置模板变量
	 * 
	 * @param string $tag
	 * @param string $value
	 */
	function assign($tag,$value) {
		$this->smarty->assign($tag,$value);
	}
	
	/**
	 * 显示模板
	 * 
	 * @param string $tmpFile
	 * @return BaseCon
	 */
	function display($tmpFile){
		$this->smarty->display($tmpFile);
	}
	/**
	 * json格式返回数据
	 * 
	 * @param array $data 数据
	 * @param bool $status 状态
	 * @param string $info 附加信息
	 */
	function jsonReturn($status='',$data='',$info='') {
		$result['status'] = $status;
		$result['info'] = $info;
		$result['data'] = $data;
		exit(json_encode($result));
	}

	/**
	 * 输出信息
	 *
	 * @param string $msg 消息
	 * @param string $link 要跳转的链接
	 */
	function msg($msg,$link){
		echo "<meta http-equiv=\"refresh\" content=\"3;url={$link}\">";
		echo "<div style=\"width:100%;height:675px;font-size:16px; font-family:微软雅黑\">
		<div style=\"width:500px; height:300px; margin-left:auto; margin-right:auto; margin-top:200px; border:1px solid gray; text-align:center\">
			<div style=\"margin-top:135px;\">
				{$msg}
			</div>
			<div style=\"margin-top:100px;font-size:13px;\">
				3秒后跳转...
			</div>
		</div>
	</div>";
	}
}
?>