<?php
class emptyMod extends commonMod {
	function index(){ 
		$mod_name=$_GET['_module'];         
		$act_name=$_GET['_action'];                 
		if(Plugin::run($mod_name,$act_name)==false)        
		{    
			//$to_url = module('cms')->getRoute($mod_name);
			//if( $to_url == '404') {
				cpError::show($_GET['mod_name'].'模块或插件不存在 index'); 
			//}
			//else {
				//$_result = file_get_contents('http://'.$_SERVER["HTTP_HOST"].'/'.$to_url);
				//$_result = Http::doGet('http://www.baidu.com'); 
			//	$_result = Http::doGet('http://'.$_SERVER["HTTP_HOST"].'/'.$to_url);
			//	print_r($_result);
			//}
        }  
	}
	
	function _empty(){
		$mod_name=$_GET['_module'];         
		$act_name=$_GET['_action'];                 
		if(Plugin::run($mod_name,$act_name)==false)        
		{        
			cpError::show($_GET['mod_name'].'模块或插件不存在 _empty'); 
        }   
	}
}
?>