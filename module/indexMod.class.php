<?php //链接 指定 操作，登录处理
class indexMod extends commonMod
{ 
	public function index()
	{
		$this->display('index');
	}
	
	public function test()
	{
		//print_r(module('admin')->getUserInfoById(14));
		print_r(module('admin')->getRestInfoById(4));
		echo "<hr>yes";
	}
	
//点餐相关操作
	public function nowmenu(){ //查看今日菜单
		$this->display('diancan/nowmenu');
	}
	public function otherorders(){ //查看其他订单
		$this->display('diancan/otherorders');
	}
	public function myorders(){ //查看订单记录
		$this->display('diancan/myorders');
	}
//用户相关操作
	//用户统一管理，暂只提供  密码及QQ信息 自定义，其他由管理员修改
	//暂开放注册用于测试
	public function register() //用户注册
	{
		//print_r($this->config);die();
		if($this->config['openregister'] == 1)
			$this->display('user/useradd');
		else
			$this->error('注册功能已关闭，请联系管理员~！');
	}
//管理相关操作	
	public function useradmin(){ //用户管理
		$this->display('admin/useradmin');
	}	
	public function useradd(){ //添加用户
		$this->display('admin/useradd');
	}	
	public function addmoney(){ // 批量充值
		$this->display('admin/addmoney');
	}	
	public function menuadmin(){ //菜单管理
		$this->display('admin/menuadmin');
	}	
	public function restadd(){ //添加餐厅
		$this->display('admin/restadd');
	}	
	public function restset(){ //指定今日餐厅
		$this->display('admin/restset');
	}	
	public function menuadd(){ //添加菜单
		$this->display('admin/menuadd');
	}
	public function orderadmin(){ //订单管理
		$this->display('admin/orderadmin');
	}
	public function ordersnow(){ //本次订单
		$this->display('admin/ordersnow');
	}
	public function checkout(){ //结账
		if(module('admin')->checkout()) $this->error('已成功结账');
		$this->error('操作异常，请联系技术人员');
	}

	//用户登录	
	public function login(){
		//echo "login";
		if(!$this->isPost())
		{
			$this->display('login');
			return;
		} 
		//数据验证
		 $msg=Check::rule(array(
								array(check::must($_POST['username']),'请输入用户名'),
								array(check::must($_POST['password']),'请输入密码'),
						   )); 
        //如果数据验证通不过，返回错误信息						   
		if($msg!==true)
		{                
			$this->error($msg);
		}
		$username = in($_POST['username']);
		$password = in($_POST['password']);
		$remember = in($_POST['remember']);
		//数据库操作
		if($this->_login($username,$password))
		{	
			if($remember == 1) {
				setcookie("diancan[username]",$username);
				setcookie("diancan[password]",$password);
				setcookie("diancan[remember]",$remember);
			//	print_r($_COOKIE); //die();
			}
			$this->redirect(__APP__);
		}
		else
		{
			$this->error('用户名或密码错误，请重新输入');
		}	
	}
	
	//用户登录 验证
	private function _login($username,$password)
	{
		$condition=array();
		$condition['name']=$username;
		$user_info=$this->model->table('user')->where($condition)->find();
		//用户名密码正确
		if($user_info && ($user_info['password']==$password))
		{	//没有锁定
			if($user_info['lock']==1)
			{
				$this->error('账号已被锁定，请联系管理员');
				return false;
			}
			/*
			if($user_info['lock']==2)
			{//控制账号单一登录
			//待解决
				$this->error('账号已被登录，如有异常，请及时联系管理员');
				return false;
			}
			*/
			//设置登录信息
			$_SESSION['me_uid']=$user_info['id'];
			$_SESSION['me_userinfo']=$user_info;
			Auth::set(-1);
			$_SESSION['__ROOT__']=__ROOT__;		
			//更新帐号信息
			$data=array();			
			$data['lasttime']=time();
			$data['ip']=get_client_ip();
			$data['logincounts']=$user_info['logincounts'] + 1;
			//$data['lock'] = 2;
			$this->model->table('user')->data($data)->where($condition)->update();
			return true;
		}	
		return false;
	}
	//用户退出
	public function logout()
	{
		//用户直接关闭浏览器，非正常退出  会导致下次登录异常
		//$condition['id'] = $_SESSION['me_uid'];
		//$data['lock'] = 0;
		//$this->model->table('user')->data($data)->where($condition)->update();
		unset($_SESSION['me_uid']); 
		unset($_SESSION['me_username']);
		unset($_SESSION['me_userinfo']); 
		unset($_SESSION['auth_groupid']);
		Auth::clear();//清除权限验证
		unset($_SESSION['__ROOT__']);
		$this->success('您已成功退出系统',__APP__);
	}
}
?>