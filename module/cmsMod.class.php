<?php
class cmsMod extends commonMod
{
	public function index()
	{
		$this->tpl->display('index');
	}
	
	//�������·��
	//apache ��д�ļ� ���⣬���index.php
	public function getUrl($path = '') { 
		if($this->config['base_url'])
			return $this->config['base_url'].'index.php/'.$path;
		else { 
			$site_path = str_replace("\\","/",getcwd()); 
			$site_path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$site_path); 
			return $site_path.'/index.php/'.$path;
		} 
	}
	
	//�������·��
	public function getAbsUrl($path = '') { 
		if($this->config['base_url'])
			return $this->config['base_url'].''.$path;
		else { 
			$site_path = str_replace("\\","/",getcwd()); 
			$site_path = str_replace($_SERVER['DOCUMENT_ROOT'],"",$site_path); 
			return $site_path.'/'.$path;
		} 
	}
	//������дĿ��·��
	public function getRoute($route = ''){ 
		$route_path = WEB_PATH . 'data/cache/routes.php';
		if($route == '') return '';
		elseif($this->config['HTML_CACHE_ON'] && file_exists($route_path))  {//��Ŀ¼�в���
			require_once($route_path);
			while(isset($_route[$route]) && $_route[$route] != '') {
				$route = $_route[$route];
			}
		}
		elseif((!$this->config['HTML_CACHE_ON']) && $routes = $this->_getRoute($route)) {//�����ݿ��в���,����������д����д�뵽Ŀ¼�ļ���
			if(file_put_contents($route_path,$routes)) { 
				require_once($route_path); 
				while(isset($_route[$route]) && $_route[$route] != '') {
					$route = $_route[$route];
				}
			}
		}
		return $route;//��ʾ�Ҳ���
	}
	//���ݿ��в���url��д
	private function _getRoute($route = ''){ 
		if($route == '') return false;
		$sql = "select * from {$this->model->pre}url_rewrite where `from_url`='$route'";
		$route = $this->model->query($sql);//ִ�в�ѯ,������ڼ�¼��˵���������д�ļ���Ҫ����
		if(!($route && count($route) > 0)) return false;//�����������ֱ�ӷ���
		$sql = "select * from {$this->model->pre}url_rewrite ";
		$route = $this->model->query($sql);//ִ�в�ѯ
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
	
	//��ȡ��������õĿ�����
	public function getBlock($blockid = ''){
		$block_path = WEB_PATH . 'template/blocks/' .$blockid.'.php';
		if($blockid == '') return '';
		elseif(file_exists($block_path))  require_once($block_path);//��Ŀ¼�в���
		elseif($block = $this->_getBlock($blockid)) {//�����ݿ��в���,�����ҵ��Ŀ�д�뵽Ŀ¼�ļ���
			if(file_put_contents($block_path,$block)) require_once($block_path);
			else print_r($block);
		}
		else echo '<br>Block: <b>'.$blockid.'</b> not found~!<br>';//��ʾ�Ҳ���
	}
	//�������ݿ��еĿ�����
	private function _getBlock($blockid = ''){
		if($blockid == '') return false;
		$sql = "select content from {$this->model->pre}cms_block where `key`='$blockid' and `status`!=0";
		$block = $this->model->query($sql);//ִ�в�ѯ  
		if($block && count($block) > 0) return $block[0]['content'];
		return false;
	}
	
	//����layout �ļ�·��
	public function getLayout($filename = ''){
		return WEB_PATH . 'template/layout/' .$filename;
	}
		
	//������ʹ�õ����������
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