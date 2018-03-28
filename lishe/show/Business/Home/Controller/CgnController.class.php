<?php
/**
  +------------------------------------------------------------------------------
 * CgnController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: CgnController.class.php v001 2016-8-15
 * @description 工程公司
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class CgnController extends CommonController {
	public function __construct(){
		parent::__construct();		
		$this->modelItemConfig=M('company_item_config');
		$this->modelCatConfig=M('company_category_config');
		$this->modelCategory=M('syscategory_cat');
		$this->modelItem=M('sysitem_item');
		$this->modelUserDeposit=M('sysuser_user_deposit');
		$this->modelstatus = M('sysitem_item_status');
		$this->tradeCartModel = M("systrade_cart");
		$this->modelShufflingDetail = M('mall_shuffling_figure_detail');
    	$this->modelShuFigure = M("mall_shuffling_figure");	
    	$this->host=$_SERVER['HTTP_HOST'];
		//todo
		$this->haihetaoComId=array('1466483633689','1472818301299','1472637383793','1474186315741','1469444223094','1481022194594','1482585376631','1482914272399','1482913917347','1486972420273');
		$this->CgnComId='1469444223094';
		if(empty($this->uid) && strtolower(ACTION_NAME)!='login'){
			header("Location:https://".$this->host.__APP__."/Haihetao/login");
			header("Location:".__APP__."/Haihetao/login");
			exit;
		}
		if(strtolower(ACTION_NAME)!='login'){
//			if(!empty($this->comId) && $this->comId!=$this->CgnComId){
			if(!empty($this->comId) && !in_array($this->comId,$this->haihetaoComId)){
				header("Location:http://www.lishe.cn/shop.php");
				exit;
			}
			$menusConfig=$this->modelItemConfig->where('com_id='.$this->CgnComId)->field('profit_rate,item_config_id,recommend,item_ids,cat_content,cat_banner,cat_name')->order('order_sort DESC')->select();	
			if(!empty($menusConfig)){
				$catConfigArr=$this->modelCatConfig->field('cat_config_id,cat_id,cat_name,item_config_id')->where('disabled=0 AND com_id='.$this->CgnComId)->order('order_sort DESC')->select();				
				foreach($menusConfig as $key=>$value){					
					$menus[$value['item_config_id']]=array(
						'cfid'=>$value['item_config_id'],
						'name'=>$value['cat_name'],
						'banner'=>$value['cat_banner'],					
						'content'=>$value['cat_content'],					
						'recommend'=>$value['recommend'],					
						'item_ids'=>$value['item_ids'],
						'profit_rate'=>$value['profit_rate']
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
//			}
		}		
		//显示分类
		$levelList = $this->modelCategory->where('disabled=0 and level=1')->order('order_sort DESC')->select();	
		$this->assign('levelList',$levelList);	
	}
	
	public function getCategory($cfgId=0){
		$condition['disabled']=0;
		$condition['com_id']=$this->CgnComId;
		if($cfgId>0){
			$condition['item_config_id']=$cfgId;
		}
		$catConfig=array();
		$catConfigArr=$this->modelCatConfig->field('item_config_id,cat_config_id,cat_id,cat_name,profit_rate,item_ids,shop_id,recommend')->where($condition)->order('order_sort DESC')->select();
		
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
			$condition='i.profit_rate>='.$value['profit_rate'].' AND i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
			$itemList[$value['cfid']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->where($condition)->order('i.cat_id asc')->limit(10)->select();
			
		}
		    //  头部轮播图
    $shuFigureId = $this->modelShuFigure->where('identify = "cgn"')->getField('shuffling_id');
		if(!empty($shuFigureId)){
	    $shuDetailList = $this->modelShufflingDetail->where('shuffling_id='.$shuFigureId.' and status = 1 and is_delete = 0')->order("order_sort desc")->select();		
		}
		$this->assign('imgList',$shuDetailList);
		$catConfig=$this->getCategory();		
		$this->assign('list',$itemList);
		$this->assign('category',$catConfig);
		$this->display();
	}

	public function category(){
		$cfgId=I('get.cfgId');
		$menusConfig=$this->menus;
		$itemConfig=$menusConfig[$cfgId];
		$categoryConfig=$this->getCategory($cfgId);
		$category=$categoryConfig[$cfgId];
		if(!empty($category)){	
			foreach($category as $key => $value){
				if(!empty($value['recommend'])){
					$condition = 'i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				}else{			
					if($value['cat_id']>0){
						$categorysArr=$this->modelCategory->field('cat_id')->where(array('parent_id'=>$value['cat_id']))->select();
						$categorysId =array();
						foreach ($categorysArr as $keys => $values){
							$categorysId[$key][] = $values['cat_id'];
						}
						if(!empty($categorysId[$key])){
							$condition='i.flag=0 AND i.profit_rate>='.$value['profit_rate'].' AND i.cat_id IN('.implode(',',$categorysId[$key]).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
							if($value['shop_id']>0){
								$condition.=' AND i.shop_id='.$value['shop_id'];
							}
						}else{
							$condition = ' 1 > 1';					
						}
					}else{
						if(empty($value['item_ids'])){
							$condition = ' 1 > 1';
						}else{
							$condition = 'i.item_id IN('.$value['item_ids'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
						}
					}						
				}
				$list[$key] = $value;
				$list[$key]['list'] = M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.cat_id,i.flag')->where($condition)->order('i.flag ASC,i.cat_id ASC')->limit(10)->select();				
			}
		}else{
			
		}
		
		$this->assign('list',$list);
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
			$order = "i.flag ASC,i.cat_id ASC,i.profit_rate DESC";
		}
		$size = 30;
		$cfgId=I('get.cfgId');
		$catId=I('get.catId');
		$this->assign('keyword',trim(I('get.itemName')));
		
		$menusConfig=$this->menus;
		$itemConfig=$menusConfig[$cfgId];
		$categoryConfig=$this->getCategory($cfgId);
		$category=$categoryConfig[$cfgId][$catId];
		
		if(!empty($catId) && intval($catId)>0){
			if(!empty($category['cat_id'])){
				$categorysArr=$this->modelCategory->field('cat_id')->where('parent_id='.$category['cat_id'])->select();
				if(!empty($categorysArr)){
					foreach($categorysArr as $key=>$value){
						$catIds[]=$value['cat_id'];
					}				
					$condition='i.profit_rate>='.$category['profit_rate'].' AND i.cat_id IN ('.implode(',',$catIds).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$where;
					if($category['shop_id']>0){
						$condition='i.shop_id='.$category['shop_id'].' AND '.$condition;
					}
					$count=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
					if(empty($where)){
						$this->modelCatConfig->where('cat_config_id='.$category['cat_config_id'])->save(array('item_count'=>$count));
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
					$style = "badge bg-light-blue";
					$onclass = "badge bg-red";
					$pagestr = $page -> show($style,$onclass);  //组装分页字符串
					$itemList=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->where($condition)->order($order)->limit($limit)->select();
					$this->assign('pagestr',$pagestr);
					$this->assign('empty','<div class="empty">未找到对应的商品！</div>');
					$this->assign('list',$itemList);					
					$this->assign('cfgId',$cfgId);
					$this->assign('catId',$catId);
					$this->assign('comCategory',$category);

					if (I('get.ajaxpost')){
						$this->display('itemListAjax');
					}else{
						$this->display('itemList');
					}
				}
			}else{
				if(empty($category['item_ids'])){
					$category['item_ids']=$category['recommend'];
				}
				$this->showItemList($category['item_ids'],$where,$category,$cfgId,$catId,$order);
			}
		}else{
			if(empty($itemConfig['item_ids'])){
				$itemConfig['item_ids']=$itemConfig['recommend'];
			}
			$this->showItemList($itemConfig['item_ids'],$where,$category,$cfgId,$catId,$order);
		}		
	}
	
	public function showItemList($itemIds,$conditions,$category,$cfgId,$catId,$order){
		$count=0;
		$size = 30;
		$itemList=array();
		$condition='i.item_id IN('.$itemIds.') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$conditions;
		$itemCheck=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->select();
		if(!empty($itemCheck)){
			foreach($itemCheck as $key=>$value){
				$itemId[]=$value['item_id'];
			}
			//实例化分页类
			$count=count($itemId);				
			$page = new \Think\Page($count,$size);
			$rollPage = 5; //分页栏显示的页数个数；
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page->firstRow;  //起始行数
			$pagesize = $page->listRows;   //每页显示的行数
			$style = "badge bg-light-blue";
			$onclass = "badge bg-red";
			$pagestr = $page -> show($style,$onclass);  //组装分页字符串
			$itemId=array_slice($itemId,$start,$pagesize);

			$condition='i.item_id IN ('.implode(',',$itemId).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$conditions;
			$itemList=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->where($condition)->order($order)->select();
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
	//意见反馈
	public function feedBack(){
		$data['prom_type'] = I('post.promType'); 
		$data['user_id'] = $this->uid;
		$data['com_id'] = $this->comId;
		$data['item_name'] = I('post.itemName');
		$data['item_link'] = I('post.itemLink');
		$data['content'] = I('post.feedBack');
		$data['add_time'] = time();
		$data['cat_id'] = I('level1').','.I('level2').','.I('level3');

		//自动回复---20170122
		//$data['reply_content'] = "尊敬的客户您好，您的信息已收到，春节假期期间(1月23日-2月7日)，售后仅支持处理线上申请的7天无理由退换货，其它需求我们将于节后（2月7日后）统一为您处理，请您耐心等待，祝您新年快乐！";
		//$data['reply_time'] = time();
		//$data['reply_admin_name'] = "系统（自动回复）";
		//$data['reply_admin_id'] = 1;

		if (strlen($data['item_name']) > 100) {
			echo json_encode(array(0,'商品名称过长！'));
			exit();
		}
		if (strlen($data['item_link']) > 120) {
			echo json_encode(array(0,'商品链接过长！'));
			exit();
		}
		if (strlen($data['content']) > 255) {
			echo json_encode(array(0,'内容描述过长！'));
			exit();
		}
		$res = M('company_feedback')->add($data);
		if ($res) {
			echo json_encode(array(1,'意见反馈成功！'));
		}else{
			echo json_encode(array(0,'意见反馈失败！'));
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
}