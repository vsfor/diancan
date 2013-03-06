<?php
class cmsMod extends commonMod
{
	public function index()
	{
		$this->tpl->display('index');
	}
	
	//返回相对路径
	//apache 重写文件 问题，添加index.php
	public function getUrl($path = '') { 
		if($this->config['base_url'])
			return $this->config['base_url'].'index.php/'.$path;
		else { 
			$site_path = str_replace("\\","/",getcwd()); 
			$site_path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$site_path); 
			return $site_path.'/index.php/'.$path;
		} 
	}
	
	//返回相对路径
	public function getAbsUrl($path = '') { 
		if($this->config['base_url'])
			return $this->config['base_url'].''.$path;
		else { 
			$site_path = str_replace("\\","/",getcwd()); 
			$site_path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$site_path); 
			return $site_path.'/'.$path;
		} 
	}
	//返回重写目标路径
	public function getRoute($route = ''){ 
		$route_path = WEB_PATH . 'data/cache/routes.php';
		if($route == '') return '';
		elseif($this->config['HTML_CACHE_ON'] && file_exists($route_path))  {//在目录中查找
			require_once($route_path);
			while(isset($_route[$route]) && $_route[$route] != '') {
				$route = $_route[$route];
			}
		}
		elseif((!$this->config['HTML_CACHE_ON']) && $routes = $this->_getRoute($route)) {//在数据库中查找,并将所有重写规则写入到目录文件中
			if(file_put_contents($route_path,$routes)) { 
				require_once($route_path); 
				while(isset($_route[$route]) && $_route[$route] != '') {
					$route = $_route[$route];
				}
			}
		}
		return $route;//提示找不到
	}
	//数据库中查找url重写
	private function _getRoute($route = ''){ 
		if($route == '') return false;
		$sql = "select * from {$this->model->pre}url_rewrite where `from_url`='$route'";
		$route = $this->model->query($sql);//执行查询,如果存在记录则说明缓存的重写文件需要更新
		if(!($route && count($route) > 0)) return false;//如果不存在则直接返回
		$sql = "select * from {$this->model->pre}url_rewrite ";
		$route = $this->model->query($sql);//执行查询
		//print_r($route);
		//die();
		if($route && count($route) > 0) {
			$routes = '<?php ///total rewrite num: ' . count($route);
			foreach($route as $item) {
				$routes .= "
\$_route['".$item['from_url']."']= '".$item['to_url']."';";
			}
			return $routes;
		}
		return false;
	}
	
	//获取输出所调用的块内容
	public function getBlock($blockid = ''){
		$block_path = WEB_PATH . 'template/blocks/' .$blockid.'.php';
		if($blockid == '') return '';
		elseif(file_exists($block_path))  require_once($block_path);//在目录中查找
		elseif($block = $this->_getBlock($blockid)) {//在数据库中查找,并将找到的块写入到目录文件中
			if(file_put_contents($block_path,$block)) require_once($block_path);
			else print_r($block);
		}
		else echo '<br>Block: <b>'.$blockid.'</b> not found~!<br>';//提示找不到
	}
	//查找数据库中的块内容
	private function _getBlock($blockid = ''){
		if($blockid == '') return false;
		$sql = "select content from {$this->model->pre}cms_block where `key`='$blockid' and `status`!=0";
		$block = $this->model->query($sql);//执行查询  
		if($block && count($block) > 0) return $block[0]['content'];
		return false;
	}
	
	//返回layout 文件路径
	public function getLayout($filename = ''){
		return WEB_PATH . 'template/layout/' .$filename;
	}
		
	//返回所使用的浏览器名称
	public function getBrowser($AGENT=''){
		if(empty($AGENT)){
			$AGENT=$_SERVER["HTTP_USER_AGENT"];
		}
		if(strpos($AGENT,"Opera"))	$browser="Opera";
		elseif(strpos($AGENT,"Firefox"))	$browser="Firefox";
		elseif(strpos($AGENT,"Chrome"))		$browser="Chrome";
		elseif(strpos($AGENT,"MSIE 6"))		$browser="IE6";
		elseif(strpos($AGENT,"MSIE 7"))		$browser="IE7";
		elseif(strpos($AGENT,"MSIE 8"))		$browser="IE8";
		elseif(strpos($AGENT,"MSIE 9"))		$browser="IE9";
		elseif(strpos($AGENT,"MSIE 10"))	$browser="IE10";
		else $browser="Other";
		return $browser;
	}
	
}
?>