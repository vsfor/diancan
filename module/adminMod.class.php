<?php  //管理模块的基本操作
class adminMod extends commonMod
{ 
	public function __construct()
	{ //管理员相关操作， 验证管理员权限
		parent::__construct();
		if($_SESSION['me_userinfo']['level'] != 9)  { $this->error('权限不足，请联系管理员~！'); }
	}
	
	public function index()
	{
		$this->display('index');
	}
	
	public function getUsers() //用户管理页  用户列表
	{
		$listRows=10;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows;
		$count = $this->model->table('user')->where()->count();
		$sql = "select * from {$this->model->pre}user order by id desc LIMIT {$limit}"; 
		$list=$this->model->query($sql);//执行查询  
		if(!$list) return '无相关记录';
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-name">用户名</span>
				<span class="info-qq">QQ</span>
				<span class="info-money">余额</span>
				<span class="info-lock">状态</span>
				<span class="info-action">操作</span></li>';
		$temp_num = 0;
		foreach($list as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-name">'.$item['name'].'</span>';
			$show .= '<span class="info-qq">'.$item['qq'].'</span>';
			$show .= '<span class="info-money">'.$item['money'].'</span>';
			$show .= '<span class="info-lock">'.($item['lock'] == 1 ? '锁定' : '正常').'</span>';
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('admin/useredit?id='.$item['id']).'">修改</a>
				<a href="'.module('cms')->getUrl('admin/lock?lock='.$item['lock'].'&id='.$item['id']).'&tab=user">'.($item['lock'] == 1 ? '解锁</a>' : '锁定').'</a>
				<a href="'.module('cms')->getUrl('admin/userdel?id='.$item['id']).'">删除</a>
			</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show;
	}
	
	public function getUserList(){//批量充值页  用户列表
		$sql = "select id,name from {$this->model->pre}user";
		$list=$this->model->query($sql);//执行查询  
		if(!$list) return '暂无相关数据';
		$show = '<ul class="add-money">';
		foreach($list as $item){
			$show .= '<li class="user-item"><input type="checkbox" name="users[]" value="'.$item['id'].'">'.$item['name'].'</li>';
		}
		return $show.'</ul>';
	}
	
	
	
	public function lock() //用户锁定 解锁操作
	{
		if(isset($_GET['tab']) && isset($_GET['lock']) && isset($_GET['id']))
		{
			$condition=array();
			$condition['id']=$_GET['id'];
			$data['lock']= abs($_GET['lock'] - 1);
			$this->model->table($_GET['tab'])->data($data)->where($condition)->update();
			//$this->success('操作成功',module('cms')->getUrl('index/useradmin'));
			$this->error('操作成功');
		}
		else
			//$this->success('操作异常',module('cms')->getUrl('index/useradmin'));
			$this->error('操作异常');
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
		//暂不进行详细校验
		$condition=array();
		$condition['name'] = in($_POST['username']);
		$user_info=$this->model->table('user')->where($condition)->find();
		if($user_info) $this->error('用户名已存在~！');
		$data['name'] = in($_POST['username']);
		$data['password'] = in($_POST['password']);
		$data['qq'] = $_POST['userqq'];
		$data['money'] = $_POST['usermoney'];
		$data['lock'] = $_POST['userlock'];
		$data['level'] = $_POST['userlevel'];
		$uid = $this->model->table('user')->data($data)->insert();
		if($uid) $this->error('添加成功，新用户id为：'.$uid);
	}
	
	public function useredit()
	{
		$u_id = in($_GET['id']);
		$data = $this->getUserInfoById($u_id);
		$this->assign('temp_userinfo',$data);
		$this->display('admin/useredit');
	}
	
	public function _useredit() //编辑用户信息
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
		//暂不进行详细校验
		$data['name'] = $_POST['username'];
		$data['password'] = $_POST['password'];
		$data['qq'] = $_POST['userqq'];
		$data['money'] = $_POST['usermoney'];
		$data['lock'] = $_POST['userlock'];
		$data['level'] = $_POST['userlevel'];
		if( $_SESSION['me_userinfo']['id'] == $_POST['userid'] && ($data['name'] != 'admin' || $data['lock'] !=0 || $data['level'] !=9) && $_SESSION['me_userinfo']['name']=='admin')
		//保留根用户基本信息
			$this->error('无法修改admin用户名 状态与权限，如需修改 请联系技术人员 ~！');
		$condition['id'] = $_POST['userid'];
		$this->model->table('user')->data($data)->where($condition)->update();
		$this->error('操作完成，如有异常请联系技术人员');
	}
	
	public function userdel()
	{
		$u_id = in($_GET['id']);
		if( $_SESSION['me_userinfo']['id'] == $u_id && $_SESSION['me_userinfo']['name']=='admin')
		//保留根用户基本信息
			$this->error('无法删除admin用户，如需修改 请联系技术人员 ~！');
		//先清理相关订单信息
		$condition['u_id'] = $u_id;
		$this->model->table('orders')->where($condition)->delete();
		unset($condition);
		//清理用户表中的用户信息
		$condition['id'] = $u_id;
		$this->model->table('user')->where($condition)->delete();
		unset($condition);
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
	
	public function moneyadd() //批量充值
	{
		//数据验证 
		if(!isset($_POST['users'])) { $this->error('请选择用户'); }
		if($_POST['addmoney'] <= 0) { $this->error('请填写充值金额'); }
		$sql = "update {$this->model->pre}user set money = money + ".$_POST['addmoney']." where id in (";
		$temp_count = 0;
		foreach($_POST['users'] as $userid)
		{
			$temp_count++;
			if($temp_count == 1) { $sql .= $userid; }
			else { $sql .= ','.$userid; } 
		}
		$sql .= ")";
		$result = $this->model->query($sql);
		if($result) { $this->error('操作成功'); }
		else { $this->error('操作异常'); }
	}
	
	
	public function getUserMoney($uid) //获取用户余额
	{
		$sql="select money from {$this->model->pre}user where id=$uid";
		$result=$this->model->query($sql);
		if(count($result) > 0) return $result[0]['money'];
		else return '无相关数据';
	}
	
	public function restview() //浏览餐厅  管理餐厅菜单
	{
		$r_id = in($_GET['rid']);
		if($r_id <= 0 ) $this->error('出现异常~！');
		$data = $this->getRestInfoById($r_id);
		$this->assign('temp_restinfo',$data);
		$this->display('admin/restview');
	}
	
	public function getRests() //菜单管理页  餐厅列表
	{
		$listRows=10;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows;
		$count = $this->model->table('restaurant')->where()->count();
		$sql = "select * from {$this->model->pre}restaurant order by id desc LIMIT {$limit}"; 
		$list=$this->model->query($sql);//执行查询  
		if(!$list) return '无相关记录';
		$show = '<ul class="rest-info">';
		$show .= '<li class="title">
				<span class="info-name">餐馆名称</span>
				<span class="info-phone">电话</span>
				<span class="info-addr">地址</span>
				<span class="info-lock">状态</span>
				<span class="info-action">操作</span></li>';
		$temp_num = 0;
		foreach($list as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-name">
			<a href="'.module('cms')->getUrl('admin/restview?rid='.$item['id']).'">
			'.$item['name'].'
			</a>
			</span>';
			$show .= '<span class="info-phone">'.$item['phone'].'</span>';
			$show .= '<span class="info-addr">'.$item['addr'].'</span>';
			$show .= '<span class="info-lock">'.($item['lock'] == 1 ? '锁定' : '正常').'</span>';
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('admin/restedit?rid='.$item['id']).'">修改</a>
				<a href="'.module('cms')->getUrl('admin/lock?lock='.$item['lock'].'&id='.$item['id']).'&tab=restaurant">'.($item['lock'] == 1 ? '解锁</a>' : '锁定').'</a>
				<a href="'.module('cms')->getUrl('admin/restdel?rid='.$item['id']).'">删除</a>
			</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show;
	}
	
	public function getRestList($type = ''){//餐厅指定页及添加菜单页(type = select 区分)  餐厅列表
		$sql = "select id,name from {$this->model->pre}restaurant";
		$list=$this->model->query($sql);//执行查询  
		if(!$list) {	
			if($type == '') 
				return '暂无相关数据'; 
			else 
				return '<option value="0"><span>暂无相关餐厅</span></option>';
		}
		if($type == 'select') { 
			$show = '';
			foreach($list as $item){
				$show .= '<option value="'.$item['id'].'"><span>'.$item['name'].'</span></option>';
			}
			return $show;
		}
		else {
			$show = '<ul class="add-money">';
			foreach($list as $item){
				$show .= '<li class="user-item"><input type="checkbox" name="rests[]" value="'.$item['id'].'">'.$item['name'].'</li>';
			}
			return $show.'</ul>';
		}
		return '';
	}
	
	public function restadd() //添加餐厅
	{
		//数据验证
		 $msg=Check::rule(array(
								array(check::must($_POST['restname']),'请输入餐厅名称'),
								array(check::must($_POST['restphone']),'请输入订餐电话'),
						   )); 
        //如果数据验证通不过，返回错误信息						   
		if($msg!==true)
		{                
			$this->error($msg);
		}
		//暂不进行详细校验
		$data['name'] = $_POST['restname'];
		$data['phone'] = $_POST['restphone'];
		$data['addr'] = $_POST['restaddr']; 
		$data['lock'] = $_POST['restlock']; 
		$uid = $this->model->table('restaurant')->data($data)->insert();
		if($uid) $this->error('添加成功，新餐厅id为：'.$uid);
	}
		
	public function restset() //指定餐厅
	{
		//数据验证 
		if(!isset($_POST['rests'])) { $this->error('请选择餐厅'); } 
		$search_time = time() - (3600*2); // 修改指定餐厅2小时前 新订单的状态，使之失效   防止误点
		$sql = "select count(*) as counts from `{$this->model->pre}orders` where ordertime > $search_time and `status` != 8";
		$result = $this->model->query($sql); 
		if($result[0]['counts'] > 0) { //有人误点 则修改订单状态  设置失效状态标识为 5
			$sql = "update `{$this->model->pre}orders` set `status` = 5 where  ordertime > $search_time and `status` != 8";
			$this->model->query($sql);
		}
		
		$sql = "update {$this->model->pre}restaurant set `level`=0";
		$this->model->query($sql);
		$sql = "update {$this->model->pre}restaurant set `level`=7 where id in (";
		$temp_count = 0;
		foreach($_POST['rests'] as $restid)
		{
			$temp_count++;
			if($temp_count == 1) { $sql .= $restid; }
			else { $sql .= ','.$restid; } 
		}
		$sql .= ")";
		$result = $this->model->query($sql);
		if($result) { $this->error('操作成功'); }
		else { $this->error('操作异常'); }
	}
	
	public function restedit() //编辑餐厅信息
	{
		$r_id = in($_GET['rid']); 
		$data = $this->getRestInfoById($r_id);
		$this->assign('temp_restinfo',$data);
		$this->display('admin/restedit');
	}
	
	public function _restedit() //编辑更新餐厅信息
	{
		//数据验证
		 $msg=Check::rule(array(
								array(check::must($_POST['restname']),'请输入餐厅名称'),
								array(check::must($_POST['restphone']),'请输入订餐电话'),
						   )); 
        //如果数据验证通不过，返回错误信息						   
		if($msg!==true)
		{                
			$this->error($msg);
		}
		//暂不进行详细校验
		$data['name'] = $_POST['restname'];
		$data['phone'] = $_POST['restphone'];
		$data['addr'] = $_POST['restaddr']; 
		$data['lock'] = $_POST['restlock']; 
		$condition['id'] = $_POST['restid'];
		$this->model->table('restaurant')->data($data)->where($condition)->update();
		$this->error('操作完成，如有异常请联系技术人员');
	}
	
	public function getRestInfoById($r_id = 0) //根据餐厅id  获取餐厅信息
	{
		if($r_id == 0) return '';
		$sql = "select * from `{$this->model->pre}restaurant` where `id` = $r_id ";
		$result = $this->model->query($sql);
		if(!$result)  return '';
		return $result[0];
	}
	
	public function restdel() //删除餐厅
	{
		$r_id = in($_GET['rid']);
		if($r_id <= 0 ) $this->error('操作异常~！');
		//删除此餐厅的订单信息
		$condition['r_id'] = $r_id;
		$this->model->table('orders')->where($condition)->delete();
		//删除此餐厅的菜单信息
		$this->model->table('menu')->where($condition)->delete();
		unset($condition);
		//删除餐厅信息
		$condition['id'] = $r_id;
		$this->model->table('restaurant')->where($condition)->delete();
		$this->error('操作完成');
	}
	
	public function getRestMenu($r_id = '', $r_name = '') //获取餐厅菜单列表
	{
		if($r_id <= 0) 	return '请选择餐厅'; 
		$listRows=10;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows; 
		$condition['r_id']=$r_id;
		$count = $this->model->table('menu')->where($condition)->count();
		$sql = "select * from {$this->model->pre}menu where `r_id`=$r_id order by id desc LIMIT {$limit}"; 
		$list=$this->model->query($sql);//执行查询  
		if(!$list) return '无相关记录';
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-rname">所属餐厅</span>
				<span class="info-hits">被点次数</span>
				<span class="info-name">套餐</span>
				<span class="info-price">价格(元)</span>
				<span class="info-lock">状态</span>
				<span class="info-action">操作</span></li>';
		$temp_num = 0;
		foreach($list as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-rname">'.$r_name.'</span>';
			$show .= '<span class="info-hits">'.$item['hit'].'</span>';
			$show .= '<span class="info-name">'.$item['name'].'</span>';
			$show .= '<span class="info-price">'.$item['price'].'</span>';
			$show .= '<span class="info-hits">'.($item['lock'] == 0 ? '正常' : '锁定').'</span>';
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('admin/menuedit?m_id='.$item['id']).'&r_id='.$r_id.'">编辑</a>
				<a href="'.module('cms')->getUrl('admin/lock?lock='.$item['lock'].'&id='.$item['id']).'&tab=menu">'.($item['lock'] == 1 ? '解锁' : '锁定').'</a>
				<a href="'.module('cms')->getUrl('admin/menudel?m_id='.$item['id']).'">删除</a>
			</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show;
	}
	
	public function menuadd() //添加菜单
	{
		//数据验证
		 $msg=Check::rule(array(
								array(check::must($_POST['menurid']),'请选择对应餐厅'),
								array(check::must($_POST['menuname']),'请输入套餐名称'),
								array(check::must($_POST['menuprice']),'请输入套餐价格'),
						   ));  
        //如果数据验证通不过，返回错误信息						   
		if($msg!==true)
		{                
			$this->error($msg);
		}
		//暂不进行详细校验
		$data['name'] = $_POST['menuname'];
		$data['price'] = $_POST['menuprice']; 
		$data['r_id'] = $_POST['menurid'];
		$data['lock'] = $_POST['menulock']; 
		$uid = $this->model->table('menu')->data($data)->insert();
		if($uid) $this->error('添加成功，新套餐id为：'.$uid);
	}
	
	public function menuedit() //编辑菜单
	{
		$m_id = in($_GET['m_id']);
		if($m_id <= 0) $this->error('操作异常~！');
		$data = $this->getMenuInfoById($m_id);
		$r_id = in($_GET['r_id']);
		$this->assign('history_restview',module('cms')->getUrl('admin/restview?rid='.$r_id));
		$this->assign('temp_menuinfo',$data);
		$this->display('admin/menuedit');
	}
	
	public function _menuedit()  //编辑更新菜单
	{
		//数据验证
		 $msg=Check::rule(array( 
								array(check::must($_POST['menuname']),'请输入套餐名称'),
								array(check::must($_POST['menuprice']),'请输入套餐价格'),
						   ));  
        //如果数据验证通不过，返回错误信息						   
		if($msg!==true)
		{                
			$this->error($msg);
		}
		//暂不进行详细校验
		$data['name'] = $_POST['menuname'];
		$data['price'] = $_POST['menuprice'];  
		$data['lock'] = $_POST['menulock']; 
		$condition['id'] = $_POST['menuid'];
		$uid = $this->model->table('menu')->data($data)->where($condition)->update(); 
		$this->error('操作完成');
	}
	
	public function menudel() //删除菜单
	{
		$m_id = in($_GET['m_id']);
		if($m_id <= 0) $this->error('操作异常~！');
		//删除菜单相关的订单
		$condition['m_id'] = $m_id;
		$this->model->table('orders')->where($condition)->delete();
		unset($condition);
		//删除菜单信息
		$condition['id'] = $m_id;
		$this->model->table('menu')->where($condition)->delete();
		$this->error('操作完成');
	}
	
	public function getMenuInfoById($m_id = 0)
	{
		if($m_id == 0) return '';
		$sql = "select * from `{$this->model->pre}menu` where `id` = $m_id ";
		$result = $this->model->query($sql);
		if(!$result)  return '';
		return $result[0];
	}
	
	public function getOrders(){ //查询订单记录
		//$temp_uid = $_SESSION['me_uid']; //通过session 获取用户id
		$listRows=10;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows; 
		$condition['r_id']=$r_id;
		$sql = "select count(*) as counts from `{$this->model->pre}orders`";
		$result = $this->model->query($sql);
		$count = $result[0]['counts'];
		if($count <= 0) return '暂无相关记录';
		$sql = "select a.name as uname,d.id as oid,b.name as mname,c.name as rname,b.price as price,d.ordertime as ordertime,d.status as status from `{$this->model->pre}user` as a,`{$this->model->pre}menu` as b,`{$this->model->pre}restaurant` as c,`{$this->model->pre}orders` as d where d.u_id = a.id and d.m_id = b.id and b.r_id = c.id order by d.id desc LIMIT {$limit}";
		$result = $this->model->query($sql);
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-uname">用户</span>
				<span class="info-rname">餐厅</span>
				<span class="info-name">套餐</span>
				<span class="info-price">价格(元)</span>
				<span class="info-name">点餐时间</span>
				<span class="info-price">状态</span>
				<span class="info-action">操作</span></li>';
		$temp_num = 0;
		foreach($result as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-uname">'.$item['uname'].'</span>';
			$show .= '<span class="info-rname">'.$item['rname'].'</span>';
			$show .= '<span class="info-name">'.$item['mname'].'</span>';
			$show .= '<span class="info-price">'.$item['price'].'</span>';
			$show .= '<span class="info-name">'. date('Y-m-d H:i',$item['ordertime']).'</span>';
			$show .= '<span class="info-price">'.($item['status'] == 8 ? '已结账' : ($item['status'] == 5 ? '已失效' :'未结账')).'</span>';
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('diancan/delorder?o_id='.$item['oid']).'">删除</a>
			</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show; 
	}
	
	
	public function getOrdersNow(){ //查询本次订单
		$search_time = time() - (3600*4); //检索4小时内的订单,(点餐一般在4小时内完成)
		$listRows=100;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows; 
		$condition['r_id']=$r_id;
		$sql = "select count(*) as counts from `{$this->model->pre}orders` where ordertime > $search_time and status != 5";
		$result = $this->model->query($sql);
		$count = $result[0]['counts'];
		if($count <= 0) return '暂无相关记录';
		$sql = "select a.name as uname,b.id as mid,b.name as mname,b.hit as mhit,c.name as rname,b.price as price,d.ordertime as ordertime,d.status as status from `{$this->model->pre}user` as a,`{$this->model->pre}menu` as b,`{$this->model->pre}restaurant` as c,`{$this->model->pre}orders` as d where d.ordertime > $search_time and d.status != 5 and d.u_id = a.id and d.m_id = b.id and b.r_id = c.id order by c.id,b.id desc LIMIT {$limit}";
		$result = $this->model->query($sql);
		if(!$result) return '暂无相关记录';
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-uname">用户</span>
				<span class="info-rname">餐厅</span>
				<span class="info-name">套餐</span>
				<span class="info-hits">点击量</span>
				<span class="info-price">价格(元)</span>
				<span class="info-name">点餐时间</span>
				<span class="info-action">状态</span></li>';
		$temp_num = 0;
		foreach($result as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-uname">'.$item['uname'].'</span>';
			$show .= '<span class="info-rname">'.$item['rname'].'</span>';
			$show .= '<span class="info-name">'.$item['mname'].'</span>';
			$show .= '<span class="info-hits">'.$item['mhit'].'</span>';
			$show .= '<span class="info-price">'.$item['price'].'</span>';
			$show .= '<span class="info-name">'. date('m-d H:i',$item['ordertime']).'</span>';
			$show .= '<span class="info-action">'.($item['status'] == 8 ? '已结账' : ($item['status'] == 5 ? '已失效' :'未结账')).'</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show; 
	}
	
	public function getOrdersNowInfo()
	{ //获取 本次订单信息
		$sql="select id,name,phone from {$this->model->pre}restaurant where `level`=7";
		$result=$this->model->query($sql);
		if(!isset($result)) return '暂无相关信息';
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-rname">餐厅</span> 
				<span class="info-hits">点餐数</span>
				<span class="info-price">合计(元)</span>
				<span class="info-name">联系电话</span>
				<span class="info-action">操作</span></li>';
		$search_time = time() - (3600*4); //检索4小时内的订单,(点餐一般在4小时内完成)
		foreach($result as $r_item)
		{ //按今日指定餐厅  获取统计信息
			$temp_id = $r_item['id'];
			$sql = "select count(a.id) as totalnum,sum(b.price) as totalprice from `{$this->model->pre}orders` as a,`{$this->model->pre}menu` as b where `a`.`status` != 5 and `a`.`m_id` = `b`.`id` and `a`.`ordertime` > $search_time and `b`.`r_id` = $temp_id";		
			$temp_result = $this->model->query($sql);
			if(!isset($temp_result) || $temp_result[0]['totalnum'] == 0) continue;
			//print_r($temp_result);die();
			$show .= '<li class="title">
					<span class="info-rname">'.$r_item['name'].'</span> 
					<span class="info-hits">'.$temp_result[0]['totalnum'].'</span>
					<span class="info-price">'.$temp_result[0]['totalprice'].'</span>
					<span class="info-name">'.$r_item['phone'].'</span>
					<span class="info-action">----</span></li>'; 
		}
		return $show;
	}
	
	public function checkout()
	{ //结账操作
		if($_SESSION['me_userinfo']['level'] != 9)  { $this->error('权限不足，请联系管理员~！'); }
		$search_time = time() - (3600*4); //检索4小时内的订单,(点餐一般在4小时内完成)
		$sql="select id,u_id,price from `{$this->model->pre}orders` where `status` != 5 and `status` != 8 and `ordertime` > $search_time";
		$result=$this->model->query($sql);
		if(!isset($result) ) return true;
		foreach($result as $o_item)
		{	//处理此订单 , 更改订单状态   扣除用户余额
			$temp_sql = "update `{$this->model->pre}orders` set `status` = 8 where id = ".$o_item['id'];
			//print_r($this->model->query($temp_sql)); die();
			if(!$this->model->query($temp_sql)) return false;
			$temp_sql = "update `{$this->model->pre}user` set `money` = `money` - ".$o_item['price']." where `id` = ".$o_item['u_id'];
			//print_r($this->model->query($temp_sql)); die();
			if(!$this->model->query($temp_sql)) return false;
		}
		return true;
	}
	
}
?>
