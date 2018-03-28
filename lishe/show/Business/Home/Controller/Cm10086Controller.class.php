<?php
/**
  +------------------------------------------------------------------------------
  +------------------------------------------------------------------------------
 * @version  	$Id: Cm10086Controller.class.php v001 2016-8-24
 * @description 中国移动在线服务公司
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class Cm10086Controller extends CommonController {
	public function __construct(){
		parent::__construct();		
		$this->modelItemConfig=M('company_item_config');
		$this->modelCatConfig=M('company_category_config');
		$this->modelCategory=M('syscategory_cat');
		$this->modelItem=M('sysitem_item');
		$this->modelUserDeposit=M('sysuser_user_deposit');
		$this->modelstatus = M('sysitem_item_status');
		$this->tradeCartModel = M("systrade_cart");		
		$this->modelActivityConfig=M('company_activity_config');
    	$this->modelShufflingDetail = M('mall_shuffling_figure_detail');
    	$this->modelShuFigure = M("mall_shuffling_figure");	
    	$this->host=$_SERVER['HTTP_HOST'];	
		$this->comId='1494386215518';
		if(empty($this->uid) && strtolower(ACTION_NAME)!='login' && strtolower(ACTION_NAME)!='activate'){
			header("Location:https://".$this->host.__APP__."/Cm10086/login");
			exit;
		}
		if(strtolower(ACTION_NAME)!='login' && strtolower(ACTION_NAME)!='activate'){
			if(!empty($this->comId) && $this->comId!=$this->comId){
				header("Location:http://www.lishe.cn/shop.php");
				exit;
			}
			$menusConfig=$this->modelItemConfig->where('disabled=0 AND com_id='.$this->comId)->field('profit_rate,item_config_id,recommend,item_ids,cat_content,cat_icon,cat_banner,cat_name')->order('order_sort DESC')->select();
			
			if(!empty($menusConfig)){
				$catConfigArr=$this->modelCatConfig->field('cat_config_id,cat_id,cat_name,item_config_id')->where('disabled=0 AND com_id='.$this->comId)->order('order_sort DESC')->select();				
				foreach($menusConfig as $key=>$value){
					$menus[$value['item_config_id']]=array(
						'cfid'=>$value['item_config_id'],
						'name'=>$value['cat_name'],
						'banner'=>$value['cat_banner'],					
						'content'=>$value['cat_content'],					
						'recommend'=>$value['recommend'],					
						'item_ids'=>$value['item_ids'],
						'profit_rate'=>$value['profit_rate'],
						'cat_icon'=>$value['cat_icon'],
					);
					foreach($catConfigArr as $keys=>$values){
						if($value['item_config_id']==$values['item_config_id']){
							$menus[$value['item_config_id']]['category'][]=$values;
						}
					}						
				}
			}
			$this->menus=$menus;
			$this->assign('menus',$menus);
		}
		//显示分类
		$levelList = $this->modelCategory->where('disabled=0 and level=1')->order('order_sort DESC')->select();	
		$this->assign('levelList',$levelList);				
	}
	
	public function getCategory($cfgId=0){
		$condition['disabled']=0;
		$condition['com_id']=$this->comId;
		if($cfgId>0){
			$condition['item_config_id']=$cfgId;
		}
		$catConfig=array();
		$catConfigArr=$this->modelCatConfig->field('item_config_id,cat_config_id,cat_id,cat_name,profit_rate,item_ids,shop_id')->where($condition)->order('order_sort DESC')->select();
		if(!empty($catConfigArr)){
			foreach($catConfigArr as $key=>$value){
				$catConfig[$value['item_config_id']][$value['cat_config_id']]=$value;						
			}			
		}
		return $catConfig;
	}
		
	public function index(){
		$menusConfig=$this->menus;
		foreach($menusConfig as $key=>$value){
			//查询上架中的推荐产品信息
			if(!empty($value['recommend'])){
				$condition='i.profit_rate>='.$value['profit_rate'].' AND i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				$itemList[$value['cfid']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag,i.shop_id')->where($condition)->order('i.cat_id ASC')->limit(8)->select();				
			}			
		}
		$catConfig=$this->getCategory();		
    //  头部轮播图
    $shuFigureId = $this->modelShuFigure->where('identify = "CsgIndex"')->getField('shuffling_id');
		if(!empty($shuFigureId)){
	    $shuDetailList = $this->modelShufflingDetail->where('shuffling_id='.$shuFigureId.' and status = 1 and is_delete = 0')->order("order_sort desc")->select();		
		}
		$this->assign('imgList',$shuDetailList);
		$this->assign('list',$itemList);
		$this->assign('category',$catConfig);
		$this->display('Cm10086/V321/index');
	}
//取出一级分类下的二级分类
	public function nextCategory(){
			$cfgId=I('get.cfgId');
			$menusConfig=$this->menus;
			$categoryConfig=$menusConfig[$cfgId]['category'];	
			if($categoryConfig){
				$data=$categoryConfig;
			}else{
				$data=0;
			}
			echo json_encode($data);
	}
	public function category(){
		$cfgId=I('get.cfgId');
		$menusConfig=$this->menus;
		$itemConfig=$menusConfig[$cfgId];
		$categoryConfig=$this->getCategory($cfgId);
		$category=$categoryConfig[$cfgId];
		if(!empty($category)){	
			foreach($category as $key => $value){
				if(empty($value['item_ids'])){											
					$categorysArr=$this->modelCategory->field('cat_id')->where(array('parent_id'=>$value['cat_id']))->select();
					$categorysId =array();
					foreach ($categorysArr as $keys => $values){
						$categorysId[$key][] = $values['cat_id'];
					}
					if(!empty($categorysId[$key])){
						$condition='i.flag=0 AND i.shop_id=10 AND i.profit_rate>='.$value['profit_rate'].' AND i.cat_id IN('.implode(',',$categorysId[$key]).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
					}else{
						$condition = ' 1 > 1';
					}
				}else{
					$condition='i.flag=0 AND i.shop_id=10 AND i.profit_rate>='.$value['profit_rate'].' AND i.item_id IN('.$value['item_ids'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				}
				$list[$key] = $value;
				$list[$key]['list'] = M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.cat_id,i.flag,i.shop_id')->where($condition)->order('i.flag ASC,i.profit_rate DESC')->limit(10)->select();				
			}
		}else{
			
		}
		$this->assign('tempContent',$itemConfig);
		$this->assign('list',$list);
		$this->assign('cfgId',$cfgId);
		$this->display();
	}

	//搜索条件
	public function condition(){
		$condition='';
		if (trim(I('get.itemName'))) {
			$condition = " AND (i.title like '%".$_GET['itemName']."%' or i.keywords like '%".$_GET['itemName']."%')";
		}
		if (trim(I('get.findPrice'))) {
			$priceInter = explode('-',trim(I('get.findPrice')));
			$priceLeft = $priceInter[0];
			$priceRight = $priceInter[1];
			$type = 1;
			if ($priceRight == 0 || $priceLeft > $priceRight) {
				$type = 2;
			}
			
			switch ($type){
				case '1':
					$condition .= " AND i.price between ".$priceLeft." and ".$priceRight;
					break;
				case '2':
					$condition .= " AND i.price > ".$priceLeft;
					break;				
				default:
					$condition = " AND i.price >= 0 ";
					break;
			}
			
		}
		return $condition;
	}
	//搜索排序
	public function sorting(){
		$price = trim(I('get.sortPrice'));
		$profit = trim(I('get.sortProfit'));
		if ($price) {
			$order = "i.flag asc,i.price ".$price;
		}
		if ($profit) {
			$order = "i.flag asc,i.profit_rate ".$profit;
		}

		return $order;
	}

	public function itemList(){
		$where = $this->condition();
		$order = $this->sorting();
		if (empty($order)) {
			$order = "i.flag ASC,i.profit_rate DESC";
		}
		$size = 30;
		$cfgId=I('get.cfgId');
		$catId=I('get.catId');
		$this->assign('keyword',trim(I('get.itemName')));
		
		$menusConfig=$this->menus;
		$itemConfig=$menusConfig[$cfgId];
		$this->assign('tempContent',$itemConfig);
		if(!empty($catId) && intval($catId)>0){
			$categoryConfig=$this->getCategory($cfgId);
			$category=$categoryConfig[$cfgId];
			$category=$category[$catId];
			if(!empty($category['cat_id'])){
				$categorysArr=$this->modelCategory->field('cat_id')->where('parent_id='.$category['cat_id'])->select();
				if(!empty($categorysArr)){
					foreach($categorysArr as $key=>$value){
						$catIds[]=$value['cat_id'];
					}				
					$condition='i.shop_id=10 AND i.profit_rate>='.$category['profit_rate'].' AND i.cat_id IN ('.implode(',',$catIds).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$where;
					$count=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
					if(empty($where)){
						$this->modelCatConfig->where('cat_config_id='.$category['cat_config_id'])->save(array('item_count'=>$count));
					}
					
					if($_GET['t']=='show'){				
						$show=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.jd_sku,i.price')->where($condition)->select();
						$ids='';
						foreach($show as $key=>$value){
							$ids.=$value['jd_sku'].',';
						}
						echo $ids;exit;
					}		
					
					//实例化分页类
					$page = new \Think\Page($count,$size);
					$rollPage = 5; //分页栏显示的页数个数；
					$page -> setConfig('first' ,'首页');
					$page -> setConfig('last' ,'尾页');
					$page -> setConfig('prev' ,'上一页');
					$page -> setConfig('next' ,'下一页');
					$start = $page->firstRow;  //起始行数
					$pagesize = $page->listRows;   //每页显示的行数
					$limit = "$start,$pagesize";
					$style = "badge";
					$onclass = "pageon";
					$pagestr = $page -> show($style,$onclass);  //组装分页字符串
					$itemList=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag,i.shop_id')->where($condition)->order($order)->limit($limit)->select();
					$this->assign('pagestr',$pagestr);
					$this->assign('empty','<div class="empty">未找到对应的商品！</div>');
					$this->assign('list',$itemList);
					$this->assign('comCategory',$category);
					$this->assign('cfgId',$cfgId);
					$this->assign('catId',$catId);
					if (I('get.ajaxpost')){
						$this->display('itemListAjax');
					}else{
						$this->display('itemList');
					}
				}
			}else{
				if(empty($category['item_ids'])){
					$category['item_ids']=$itemConfig['recommend'];
				}
				$this->showItemList($category['item_ids'],$where,$order,$cfgId,$catId);
			}
		}else{
			if(empty($itemConfig['item_ids'])){
				$itemConfig['item_ids']=$itemConfig['recommend'];
			}
			if($_GET['t']=='show'){
				echo $itemConfig['item_ids'];exit;
			}
			$this->showItemList($itemConfig['item_ids'],$where,$order,$cfgId,$catId);
		}		
	}
	
	public function showItemList($itemIds,$conditions,$order,$cfgId,$catId){
		$count=0;
		$itemList=array();
		$condition='i.item_id IN('.$itemIds.') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$conditions;
		$itemCheck=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.price')->where($condition)->order($order)->select();
		if(!empty($itemCheck)){
			
			if($_GET['t']=='show'){
				$ids='';
				foreach($itemCheck as $key=>$value){
					$ids.=$value['item_id'].',';
				}
				echo $ids;exit;
			}
			
			foreach($itemCheck as $key=>$value){
				$itemId[]=$value['item_id'];
			}
			//实例化分页类
			$size=30;
			$count=count($itemId);				
			$page = new \Think\Page($count,$size);
			$rollPage = 5; //分页栏显示的页数个数；
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page->firstRow;  //起始行数
			$pagesize = $page->listRows;   //每页显示的行数
			$style = "badge";
			$onclass = "pageon";
			$pagestr = $page -> show($style,$onclass);  //组装分页字符串
			$itemId=array_slice($itemId,$start,$pagesize);
			$condition='i.item_id IN ('.implode(',',$itemId).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$conditions;
			$itemList=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag,i.shop_id')->where($condition)->order($order)->select();
		}
		$this->assign('pagestr',$pagestr);
		$this->assign('empty','<div class="empty">未找到对应的商品！</div>');
		$this->assign('list',$itemList);
		$this->assign('catinfo',$itemConfig);
		$this->assign('comCategory',$category);
		$this->assign('cfgId',$cfgId);
		$this->assign('catId',$catId);

		if (I('get.ajaxpost')){
			$this->display('itemListAjax');
		}else{
			$this->display('itemList');
		}
	}
	public function topic(){
		$aid=intval($_GET['aid']);
		if($aid){
			$activityConfig=$this->modelActivityConfig->where('activity_config_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->find();
			$activity=array(
				'id'=>$activityConfig['activity_config_id'],
				'name'=>$activityConfig['cat_name'],
				'banner'=>$activityConfig['cat_banner'],					
				'content'=>$activityConfig['cat_content'],
				'item_ids'=>$activityConfig['item_ids'],
				'more_link'=>$activityConfig['more_link']
			);
			if(!empty($activityConfig['item_ids'])){
				$size=30;
				$condition='i.item_id IN('.$activityConfig['item_ids'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				$number=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
				$page = new \Think\Page($number,$size);
				$rollPage = 5; 
				$page -> setConfig('first' ,'首页');
				$page -> setConfig('last' ,'尾页');
				$page -> setConfig('prev' ,'上一页');
				$page -> setConfig('next' ,'下一页');
				$start = $page -> firstRow;  
				$pagesize = $page -> listRows;
				
				$itemIdArr=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->order('i.flag ASC,i.cat_id DESC,i.profit_rate DESC')->select();
				foreach($itemIdArr as $key=>$value){
					$itemId[]=$value['item_id'];
				}
				$itemId=array_slice($itemId,$start,$pagesize);
				$condition='item_id IN('.implode(',',$itemId).')';
				$itemList=$this->modelItem->field('item_id,title,image_default_id,price,mkt_price,flag,shop_id')->where($condition)->select();
				$style = "pageos";
				$onclass = "pageon";
				$pagestr = $page -> show($style,$onclass); 
				$this -> assign('pagestr',$pagestr);					
			}
			$first=array('activity'=>$activity,'list'=>$itemList);		
			$this->assign('first',$first);			
			$this->display('topic-'.$aid);
		}
	}
	
	//用户登录
	public function login(){
		if(!empty($this->uid)){
			header("Location:".__APP__."/Cm10086/index");
		}
		if($_GET['go']=='cart'){
			$this->assign('refer','/cart.html');
		}elseif($_GET['go']=='order'){
			$this->assign('refer','/member-index.html');
		}else{
			$this->assign('refer',__APP__.'/Cm10086');
		}		
		$this->display();
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie('account',null);
		cookie('LSUID',null);
		cookie('UNAME',null);
		header("Location:".__APP__."/Cm10086/login");
	}

	//意见反馈
	public function feedBack(){
		$data['prom_type'] = I('post.promType'); 
		$data['user_id'] = $this->uid;
		$data['com_id'] = $this->comId;
		$data['item_name'] = I('post.itemName');
		$data['item_link'] = I('post.itemLink');
		$data['content'] = I('post.feedBack');
		$data['add_time'] = time();
		$res = M('company_feedback')->add($data);
		if ($res) {
			echo json_encode(array(1,'意见反馈成功！'));
		}else{
			echo json_encode(array(0,'意见反馈失败！'));
		}
	}
	//获取分类
	public function getLevel(){
		$pid = I('pid');
		if (empty($pid)) {
			echo 0;
		}else{
			$res = $this->modelCategory->where('disabled=0 and parent_id='.$pid)->order('order_sort DESC')->select();
			$levelHtml.="<option value='0' selected>--请选择--</option>";
      	if($res){
            foreach($res as $key => $value){
             	$levelHtml.= "<option value='".$value['cat_id']."'>".$value['cat_name']."</option>";
            }
            echo $levelHtml;
       	}else{
            echo -1;
       	}
		}  
	}
	//删除购物车商品
	public function delCart(){
		$cartId = I('post.cartId');
		if ($cartId) {
			$res = $this->tradeCartModel->where('cart_id ='.$cartId)->delete();
			if ($res) {
				echo json_encode(array(1,'删除成功！'));
			}else{
				echo json_encode(array(0,'删除失败！'));
			}
		}else{
			echo json_encode(array(0,'获取购物车id失败！'));
		}
	}	
	//增加授权模拟请求方法
    public function accreditPost($url,$data,$user,$password){
        if(empty($url) || empty($data) || empty($user) || empty($password)){
            return false;
        }
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_USERPWD,''.$user.':'.$password.'');       
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
		/**
		 * 计算购物车总价
		 */
		public function cartTotalPoint(){
			$res = M('systrade_cart')->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and b.user_id='.$this->uid)->field('b.quantity,a.price')->select();
			$totalPrice = 0;
			foreach($res as $key=>$val){
				$totalPrice += $val['quantity']*$val['price']*100;
			}
			$this->ajaxReturn($totalPrice);
		}
		
}