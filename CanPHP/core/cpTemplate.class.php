<?php
class cpTemplate {
	private $config =array(); //配置
	private $vars = array();//存放变量信息
	private $_replace = array();
	
	public function __construct($config = array()) {
		$this->config = array_merge(cpConfig::get('TPL'), $config);//参数配置	
		$this->_replace = array('reg' => array( 'search' => array("/__[A-Z]+__/",	//替换常量
																"/{(\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*)}/i",	//替换变量
																"/{include\s*file=\"(.*)\"}/ie",	//递归解析模板包含
												 				),
											  'replace' => array("<?php if (defined('$0')) echo $0; else echo '$0'; ?>",
																 "<?php echo $1; ?>",
																 "\$this->_compile('$1')",
																)					   
					   						)
								);
	}
	
	//模板赋值
	public function assign($name, $value = '') {
		$this->vars[$name] = $value;
	}

	//执行模板解析输出
	public function display($tpl = '') {
		//如果没有设置模板，则调用当前模块的当前操作模板
		if ( ($tpl == "") && (!empty($_GET['_module'])) && (!empty($_GET['_action'])) ) {
			$tpl = $_GET['_module'] . "/" . $_GET['_action'];
		}
		extract($this->vars, EXTR_OVERWRITE);
		if ($this->config['TPL_CACHE_ON']) {
			define('CANPHP', true);
			$tplFile = $this->config['TPL_TEMPLATE_PATH'] . $tpl . $this->config['TPL_TEMPLATE_SUFFIX'];
			$cacheFile = $this->config['TPL_CACHE_PATH'] . str_replace('/','-',$tpl) . $this->config['TPL_CACHE_SUFFIX'];
			
			if (!file_exists($tplFile)) {
				cpError::show($tplFile . "模板文件不存在");
			}
			//普通的文件缓存
			if ( empty($this->config['TPL_CACHE_TYPE']) ) {
				if (!is_dir($this->config['TPL_CACHE_PATH'])) {
					@mkdir($this->config['TPL_CACHE_PATH'],0777,true);	
				}
				if ( (!file_exists($cacheFile)) || (filemtime($tplFile) > filemtime($cacheFile)) ) {
					file_put_contents($cacheFile, "<?php if (!defined('CANPHP')) exit;?>" . $this->_compile($tpl));//写入缓存
				}
				include($cacheFile);//加载编译后的模板缓存
				
			} else {
				//支持memcache等缓存，主要用于sae平台
				$tpl_key = md5( realpath($tplFile) );
				$tpl_time_key = $tpl_key.'_time';
				require_once( dirname(__FILE__) . '/cpCache.class.php' );
				
				$cache = new cpCache($this->config, $this->config['TPL_CACHE_TYPE']);
				$compile_content = $cache->get($tpl_key);
				if ( empty($compile_content) || (filemtime($tplFile) > $cache->get($tpl_time_key)) ) {
					$compile_content = $this->_compile($tpl);
					$cache->set($tpl_key, $compile_content, 3600*24*365);	//缓存编译内容
					$cache->set($tpl_time_key, time(), 3600*24*365);	//缓存编译内容
				}
				eval('?>' . $compile_content);
			}
		} else {
			eval('?>' . $this->_compile($tpl));//直接执行编译后的模板
		}		
	}	
	
	//自定义标签
	public function replace($tags = array(),$reg=false) {
		$flag=$reg ? 'reg' : 'str';
		foreach($tags as $k => $v) {
			$this->_replace[$flag]['search'][] = $k;
			$this->_replace[$flag]['replace'][] = $v;
		}
	}
	
	//提供外部内容编译
	public function compile($template) {
		extract($this->vars, EXTR_OVERWRITE);
		if (function_exists('tpl_parse_ext')) {
			$template = tpl_parse_ext($template);
		}
		$template = str_replace($this->_replace['str']['search'], $this->_replace['str']['replace'], $template);
		$template = preg_replace($this->_replace['reg']['search'], $this->_replace['reg']['replace'], $template);
		$contents = ob_get_contents();
		if($contents === false) {
			$contents = "";
			ob_start();
		} else {
			ob_clean();
		}
		eval('?>' . $template);
		$contents2 = ob_get_contents();
		ob_clean();
		echo $contents; //输出编译之前要输出的内容
		return $contents2;
	}
	
	//模板编译核心
	private  function _compile($tpl) {
		$tpl = $this->config['TPL_TEMPLATE_PATH'] . $tpl . $this->config['TPL_TEMPLATE_SUFFIX'];
		if (!file_exists($tpl)) {
			cpError::show($tpl . "模板文件不存在");
		}
		$template = file_get_contents($tpl);
		//如果自定义模板标签解析函数tpl_parse_ext($template)存在，则执行
		if (function_exists('tpl_parse_ext')) {
			$template = tpl_parse_ext($template);
		}
		$template = str_replace($this->_replace['str']['search'], $this->_replace['str']['replace'], $template);
		return preg_replace($this->_replace['reg']['search'], $this->_replace['reg']['replace'], $template);
	}
}
?>