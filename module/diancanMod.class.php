<?php  //点餐模块的基本操作
class diancanMod extends commonMod
{ 
	public function index()
	{
		$this->display('index');
	}
	
	public function getUserName($temp)
	{
		return $temp['name'];
	}
	public function getUserMoney($uid) //获取用户余额
	{
		$sql="select money from {$this->model->pre}user where id=$uid";
		$result=$this->model->query($sql);
		if(count($result) > 0) return $result[0]['money'];
		else return '无相关数据';
	}
	
	public function getNowRests() //获取当前指定餐厅
	{
		$sql="select id,name from {$this->model->pre}restaurant where `level`=7";
		$result=$this->model->query($sql);
		if(count($result) > 0) {
			$show = '<ul>';
			foreach($result as $restitem) {
				$show .= '
				<li class="now-item rest-item'.$restitem['id'].'">
					<a href="?m_rid='.$restitem['id'].'&m_rname='.$restitem['name'].'">
						<span>'.$restitem['name'].'</span>
					</a>
				</li>
				';
			}
			$show .='</ul>';
			return $show;
		}
		else return '暂无指定餐厅';
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
				<span class="info-action">操作</span></li>';
		$temp_num = 0;
		foreach($list as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-rname">'.$r_name.'</span>';
			$show .= '<span class="info-hits">'.$item['hit'].'</span>';
			$show .= '<span class="info-name">'.$item['name'].'</span>';
			$show .= '<span class="info-price">'.$item['price'].'</span>';
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('diancan/addorder?m_id='.$item['id']).'&r_id='.$r_id.'">吃它</a>
			</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show;
	}
	
	public function addorder() //添加订单
	{
		if(!isset($_GET['m_id']) || $_GET['m_id'] <= 0 ) $this->error('操作异常');
		$data['r_id'] = in($_GET['r_id']);
		$data['m_id'] = in($_GET['m_id']);
		$data['u_id'] = $_SESSION['me_uid'];
		$data['price'] = $this->getMenuPrice($data['m_id']);
		$data['ordertime'] = time(); 
		$o_id = $this->model->table('orders')->data($data)->insert();
		//菜单点击量+1
		$sql = "update {$this->model->pre}menu set hit = hit + 1 where id = ".$data['m_id'];
		$this->model->query($sql);
		$this->error("下单成功，订单id为：".$o_id);
	}
		
	public function delorder() //删除订单
	{
		if(!isset($_GET['o_id']) || $_GET['o_id'] <= 0 ) $this->error('操作异常');
		$data['id'] = $_GET['o_id'];
		//print_r($_SESSION);die();
		if($_SESSION['me_userinfo']['level'] != 9) {
			$data['u_id'] = $_SESSION['me_uid'];
		}
		$o_id = $this->model->table('orders')->where($data)->delete();
		$this->error("删除成功");
	}
	
	
	public function getMenuPrice($m_id = 0) // 根据菜单编号，获取价格
	{
		if($m_id <= 0) $this->error('操作异常');
		$sql = "select price from {$this->model->pre}menu where `id`=$m_id";
		$result = $this->model->query($sql);
		if(!$result) $this->error('价格异常，请报告管理员');
		return $result[0]['price'];
	}
	
	public function getOtherOrders(){ //获取点餐列表（看看别人吃什么）
		$search_time = time() - (3600*4); //检索4小时内的订单,(点餐一般在4小时内完成)
		$listRows=10;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows; 
		$condition['r_id']=$r_id;
		$sql = "select count(*) as counts from `{$this->model->pre}orders` where ordertime > $search_time and status != 5";
		$result = $this->model->query($sql);
		$count = $result[0]['counts'];
		if($count <= 0) return '暂无相关记录';
		$sql = "select a.name as uname,b.id as mid,b.name as mname,b.r_id as rid,b.hit as mhit,c.name as rname,b.price as price,d.ordertime as ordertime from `{$this->model->pre}user` as a,`{$this->model->pre}menu` as b,`{$this->model->pre}restaurant` as c,`{$this->model->pre}orders` as d where d.ordertime > $search_time and d.status != 5 and d.u_id = a.id and d.m_id = b.id and b.r_id = c.id order by d.id desc LIMIT {$limit}";
		$result = $this->model->query($sql);
		if(!$result) return '暂无相关信息';
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-uname">用户</span>
				<span class="info-rname">餐厅</span>
				<span class="info-hits">点击量</span>
				<span class="info-name">套餐</span>
				<span class="info-price">价格(元)</span>
				<span class="info-name">点餐时间</span>
				<span class="info-action">操作</span></li>';
		$temp_num = 0;
		foreach($result as $item) {
			$temp_num++;
			$show .= '<li class="info-item '.($temp_num%2 ==0 ? 'evn':'odd').'">';
			$show .= '<span class="info-uname">'.$item['uname'].'</span>';
			$show .= '<span class="info-rname">'.$item['rname'].'</span>';
			$show .= '<span class="info-hits">'.$item['mhit'].'</span>';
			$show .= '<span class="info-name">'.$item['mname'].'</span>';
			$show .= '<span class="info-price">'.$item['price'].'</span>';
			$show .= '<span class="info-name">'. date('m-d H:i',$item['ordertime']).'</span>';
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('diancan/addorder?m_id='.$item['mid']).'&r_id='.$item['rid'].'">吃它</a>
			</span>';
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show; 
	}
	
	
	public function getMyOrders(){ //查询个人点餐记录
		$temp_uid = $_SESSION['me_uid']; //通过session 获取用户id
		$listRows=10;//每页显示的信息条数
		$cur_page=isset($_GET['page'])?intval($_GET['page']):1;//获取当前分页
		$cur_page=$cur_page<1?1:$cur_page;//当前页小于1，则设当前页为1
		$limit_start=($cur_page-1)*$listRows;
		$limit=$limit_start.','.$listRows; 
		$condition['r_id']=$r_id;
		$sql = "select count(*) as counts from `{$this->model->pre}orders` where `u_id` = $temp_uid";
		$result = $this->model->query($sql);
		$count = $result[0]['counts'];
		if($count <= 0) return '暂无相关记录';
		$sql = "select a.name as uname,d.id as oid,b.name as mname,b.hit as mhit,c.name as rname,b.price as price,d.ordertime as ordertime,d.status as status from `{$this->model->pre}user` as a,`{$this->model->pre}menu` as b,`{$this->model->pre}restaurant` as c,`{$this->model->pre}orders` as d where d.`u_id` = $temp_uid  and d.u_id = a.id and d.m_id = b.id and b.r_id = c.id order by d.id desc LIMIT {$limit}";
		$result = $this->model->query($sql);
		$show = '<ul class="user-info">';
		$show .= '<li class="title">
				<span class="info-uname">用户</span>
				<span class="info-rname">餐厅</span>
				<span class="info-name">套餐</span>
				<span class="info-hits">点击量</span>
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
			$show .= '<span class="info-hits">'.$item['mhit'].'</span>';
			$show .= '<span class="info-price">'.$item['price'].'</span>';
			$show .= '<span class="info-name">'. date('Y-m-d H:i',$item['ordertime']).'</span>';
			$show .= '<span class="info-price">'.($item['status'] == 8 ? '已结账' : ($item['status'] == 5 ? '已失效' :'未结账')).'</span>';
			if($item['status'] == 8){
			$show .= '<span class="info-action">--</span>';
			}
			else {
			$show .= '<span class="info-action">
				<a href="'.module('cms')->getUrl('diancan/delorder?o_id='.$item['oid']).'">删除</a>
			</span>';
			}
			$show .= '</li>';
		}
		$show .= '</ul>';
		$show .= '<div class="page-toolbar">'.$this->page('',$count,$listRows).'</div>';
		return $show; 
	}
	
}
?>