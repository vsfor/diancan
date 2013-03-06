<?php  //用户基本操作
class userMod extends commonMod
{ 
	public function index()
	{
		$this->display('index');
	}
	
	
	public function useradd() //添加新用户
	{
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
		$condition=array();
		$condition['name']=in($_POST['username']);
		$user_info=$this->model->table('user')->where($condition)->find();
		if($user_info) $this->error('用户名已存在~！');
		//暂不进行详细校验
		$data['name'] = in($_POST['username']);
		$data['password'] = in($_POST['password']);
		$data['qq'] = $_POST['userqq'];
		$data['money'] = 0; //初始金额
		$uid = $this->model->table('user')->data($data)->insert();
		if(!$uid)  $this->error('操作异常，请联系管理员~！');//$this->error('添加成功，新用户id为：'.$uid);
		$this->success('注册成功','index');
	}
	
	public function useredit()
	{
		$u_id = in($_SESSION['me_uid']);
		$data = $this->getUserInfoById($u_id);
		$this->assign('temp_userinfo',$data);
		$this->display('user/useredit');
	}
	
	public function _useredit() //编辑用户信息
	{
		//数据验证
		 $msg=Check::rule(array(
								array(check::must($_POST['password']),'请输入密码'),
						   )); 
        //如果数据验证通不过，返回错误信息						   
		if($msg!==true)
		{                
			$this->error($msg);
		}
		//暂不进行详细校验
		$data['password'] = $_POST['password'];
		$data['qq'] = $_POST['userqq']; 
		$condition['id'] = $_POST['userid'];
		$this->model->table('user')->data($data)->where($condition)->update();
		$this->error('操作完成，如有异常请联系技术人员');
	}
	 
	public function getUserInfoById($u_id = 0) //通过用户id 获取用户信息
	{
		if($u_id == 0) return '';
		$sql = "select * from `{$this->model->pre}user` where `id` = $u_id ";
		$result = $this->model->query($sql);
		if(!$result)  return '';
		return $result[0];
	}
	
}
?>