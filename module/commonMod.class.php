<?php
//公共模块
class commonMod
{
	public $model;//数据库模型对象
	public $tpl;//模板对象
	public $config;//全局配置
	static $global;//静态变量，用来实现单例模式
	public function __construct()
	{ 
		global $config;
		$this->config=$config;//配置 
		session_start();//开启session
		//数据库模型初始化
		if(!isset(self::$global['model']))
		{
			self::$global['model']=new cpModel($this->config);//实例化数据库模型类 
		}
		$this->model=self::$global['model'];//数据库模型对象

		//模板初始化
		if(!isset(self::$global['tpl']))
		{ 
			self::$global['tpl']=new cpTemplate($this->config);//实例化模板类 
		}
		$this->tpl=self::$global['tpl'];//模板类对象
		$config['AUTH_LOGIN_URL']= $this->getUrl('index/login');//登录地址 
		$config['AUTH_LOGIN_NO']=array('index'=> array('login','verify','register'),'user'=>array('useradd'),'common'=>'*');//不需要认证的模块和操作
		//是否缓存权限信息，设置false,每次从数据库读取，开发时建议设置为false
		$config['AUTH_POWER_CACHE']=false;
		//自动创建数据库表，自动插入模块数据，发布时，可以去掉
		//Auth::getModule($this->model,$config);
		Auth::check($this->model,$config);//检查是否登录
	}
	//模板变量解析
	protected function assign($name, $value)
	{
		return $this->tpl->assign($name, $value);
	}
	//模板输出
	protected function display($tpl='')
	{ 
		return $this->tpl->display($tpl); 	
	}

	//直接跳转
	protected function redirect($url)
	{
		header('location:'.$url,false,301);
		exit;
	}
	//操作成功之后跳转,默认三秒钟跳转
	protected   function success($msg,$url=NULL,$waitSecond=3)
	{
		if($url==NULL)
			$url=__URL__;
		$this->assign('message',$msg);
		$this->assign('url',$url);
		$this->assign('waitSecond',$waitSecond);
		$this->display('success');
		exit;
	}
	//出错之后跳转，后退到前一页
	protected function error($msg)
	{
		header("Content-type: text/html; charset=utf-8"); 
		$msg="alert('$msg');";
		echo "<script>$msg history.go(-1);</script>";
		exit;
	}
	//判断是否是post提交
	protected function isPost()
	{
		return $_SERVER['REQUEST_METHOD']=='POST';
	}
	/*
	功能:分页
	$url，基准网址，若为空，将会自动获取，不建议设置为空 
	$total，信息总条数 
	$perpage，每页显示行数 
	$pagebarnum，分页栏每页显示的页数 
	$mode，显示风格，参数可为整数1，2，3，4任意一个 
	*/
	protected function page($url,$total,$perpage=10,$pagebarnum=5,$mode=1)
	{
		$page=new Page();
		return $page->show($url,$total,$perpage,$pagebarnum,$mode);
	}
	
	/*functions by WJ*/
	
	//获取路径
	public function getUrl($path = ''){
		$_suffix = '';
		if($this->config['URL_REWRITE_ON']) $_suffix = $this->config['URL_HTML_SUFFIX'];
		if($this->config['base_url'])
			return $this->config['base_url'].'index.php/'.$path.$_suffix;
		else { 
			$site_path = str_replace("\\","/",getcwd()); 
			$site_path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$site_path); 
			return $site_path.'/'.'index.php/'.$path.$_suffix;
		}
	}
}
?>