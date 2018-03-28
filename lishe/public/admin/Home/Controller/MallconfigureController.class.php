<?php 
namespace Home\Controller;
/*
 * 商城配置管理
 * 2016/10/31
 * zhangrui
 * 
 * */	
class MallconfigureController extends CommonController{
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dCategory=D('Category');
		$this->dGoods=D('Goods');
		$this->dMallConf=D('Mallconfigure');
	}	
/*
 * 商城赠送情景配置
 * */	
	public function giveScene(){
		$info=$this->dMallConf->getIndexInfo(2);
		$this->assign('list',$info);
		$this->display();
	}
/*
 * 添加赠送情景
 * */	
	public function giveSceneEdit(){
		$indexId=I('indexId');
		if($indexId){
			$res=$this->dMallConf->getThisIndexInfo($indexId);
			$this->assign('info',$res);
		}
		$this->display();
	}	
/*
 * 编辑赠送情景保存
 * */	
	public function SaveGiveScene(){
		$data=I('data');
		$indexId=I('indexId');
		$banner=A('Activity')->uploadImg('banner');
		if(!empty($banner)){
			$data['icon']=$banner;
		}	
		$banner=A('Activity')->uploadImg('giftPic');
		if(!empty($banner)){
			$data['gift_pic']=$banner;
		}			
		if($data){
			if($indexId){
				//编辑
				$res=$this->dMallConf->editIndexInfo($indexId,$data);
				$say="编辑";
			}else{
				//保存
				$res=$this->dMallConf->addIndexInfo($data);
				$say="添加";
			}
			if($res){
				$this->success($say."成功！");
			}
		}else{
			$this->error("无数据提交!");
		}	
		
	}
/*
 * 赠送情景删除/禁用启用
 * */	
	public function changeGiveScene(){
		$type=I('type');
		$val=I('val');
		$indexId=I('indexId');
		if($type=="status"){
			//禁用启用
			if($val == 1){
				$data['status']=0;
			}else if($val==0){
				$data['status']=1;
			} 
		}else if($type=="del"){
			//删除情景
			$data['is_delete']=1;
			$this->dMallConf->eqitIndexItemInfo(array('forkey_index_id'=>$indexId),$data);
		}
		$res=$this->dMallConf->editIndexInfo($indexId,$data);
		echo json_encode($res);
	}
/*
 * 商城赠送情景商品配置
 * */	
	public function giveSceneItem(){
		$indexId=I('indexId');
		if($indexId){
			$info=$this->dMallConf->getIndexItemInfo($indexId);
			$this->assign('indexId',$indexId);
			$this->assign('list',$info);
			$this->display();
		}else{
			echo "can't see！";
		}
	}
/*
 * 赠送情景商品配置
 * */	
	public function sceneItemEdit(){
		$itemId=I('itemId');
		if($itemId){
			$res=$this->dMallConf->getThisIndexItemInfo($itemId);
			$this->assign('info',$res);
		}
		$this->assign('indexId',I('indexId'));
		$this->display();
	}
/*
 * 
 * 赠送情景商品保存
 * */
 	public function saveSceneItemEdit(){
 		$itemId=I('itemId');
		$data=I('data');
		$banner=A('Activity')->uploadImg('banner');
		$data['forkey_item_id']=trim($data['forkey_item_id']);
		$res=$this->dMallConf->getThisIndexItemInfo($itemId);
		if(!empty($data['forkey_item_id'])){
			$itemInfo=$this->dGoods->getThisItemInfo($data['forkey_item_id'],'title,price,image_default_id');
			if(empty($itemInfo)){
				$this->error("商品ID填写有错，暂无该商品！");
			}			
		}
		if(!empty($banner)){
			//生成缩略图
			$ext=end(explode('.', $banner));
			$file=end(explode(C('TMPL_PARSE_STRING.__LISHE__'), $banner));
		    $image = new \Think\Image(); 
		    $image->open('.'.$file);
		    $image->thumb(200, 200)->save('.'.$file.'_m.'.$ext);			
			$data['img_default_id']=$banner;
		}
		if($data['forkey_item_id'] != $res['forkey_item_id'] && !empty($itemInfo)){
			if(empty($banner)){
				$data['img_default_id']=$itemInfo['image_default_id'];
			}			
			$data['title']=$itemInfo['title'];
			$data['price']=$itemInfo['price'];
		}
		if($data){
			if($itemId){
				//编辑
				$res=$this->dMallConf->saveIndexItemInfo($itemId,$data);
				$say="编辑";
			}else{
				//添加
				$say="添加";
				$res=$this->dMallConf->addIndexItemInfo($data);
			}	
			if($res){
				$this->success($say."成功！");
			}			
		}else{
			$this->error("无数据提交!");
		}			
		
 	}
/*
 * 赠送情景商品删除/禁用启用
 * */	
	public function changeGiveSceneItem(){
		$type=I('type');
		$val=I('val');
		$itemId=I('itemId');
		if($type=="status"){
			//禁用启用
			if($val == 1){
				$data['status']=0;
			}else if($val==0){
				$data['status']=1;
			} 
		}else if($type=="del"){
			//删除情景
			$data['is_delete']=1;
		}
		$res=$this->dMallConf->saveIndexItemInfo($itemId,$data);
		echo json_encode($res);
	}
/*
 * 商城赠送情景配置
 * */	
	public function wshopIndex(){
		$info=$this->dMallConf->getIndexInfo(0);
		$this->assign('list',$info);
		$this->display();
	}	
/*
 * 添加赠送情景
 * */	
	public function wshopIndexEdit(){
		$indexId=I('indexId');
		if($indexId){
			$res=$this->dMallConf->getThisIndexInfo($indexId);
			$this->assign('info',$res);
		}
		$this->display();
	}	
/*
 * 商城赠送情景商品配置
 * */	
	public function wshopIndexItem(){
		$indexId=I('indexId');
		if($indexId){
			$info=$this->dMallConf->getIndexItemInfo($indexId);
			$this->assign('indexId',$indexId);
			$this->assign('list',$info);
			$this->display();
		}else{
			echo "can't see！";
		}
	}	
/*
 * 赠送情景商品配置
 * */	
	public function wshopItemEdit(){
		$itemId=I('itemId');
		if($itemId){
			$res=$this->dMallConf->getThisIndexItemInfo($itemId);
			$this->assign('info',$res);
		}
		$this->assign('indexId',I('indexId'));
		$this->display();
	}
/*
 * 商城首页抢购页
 * */
	public function mallIndexRush(){
		$res=$this->dMallConf->getRushInfo();
		$this->assign('list',$res);
		$this->display();
	}
/*
 * 编辑抢购
 * */	
	public function editRushActivity(){
//		$activityId=I('activity_id');
		$res=$this->dMallConf->getActivityRush();
		if($res['start_time']){
			$res['start_time']=date('Y-m-d',$res['start_time']);
		}
		if($res['end_time']){
			$res['end_time']=date('Y-m-d',$res['end_time']);
		}
		$this->assign('info',$res);
		$this->display();
	}
/*
 * 保存抢购
 * */	
	public function saveEditRushActivity(){
		$data=I('data');
		if($data['start_time']){
			$data['start_time']=strtotime(trim($data['start_time']));
		}
		if($data['end_time']){
			$data['end_time']=strtotime(trim($data['end_time'])." +24 hours");
		}
		if($data){
			$data['created_time']=time();
			$res=$this->dMallConf->saveActivityRush($data);
		}
		if($res){
			$this->success("修改成功!");
		}else{
			$this->success("修改失败!");
		}
				
	}	
/*
 * 编辑抢购商品
 * */	
	public function editRushActivityItem(){
		$id=I('id');
		$res=$this->dMallConf->getActivityRushItem($id);
		if($res['start_time']){
			$res['start_time']=date('Y-m-d',$res['start_time']);
		}
		if($res['end_time']){
			$res['end_time']=date('Y-m-d',$res['end_time']);
		}
		$this->assign('info',$res);
		$this->display();
	}
/*
 * 保存编辑抢购商品
 * */	
	public function saveRushActivityItem(){
		$id=I('id');
		$data=I('data');
		$data['item_id']=trim($data['item_id']);
		$data['activity_price']=trim($data['activity_price']);
		if(empty($data['item_id']) || empty($data['activity_price'])){
				$this->error("信息请填写完整！");
		}
		if($id){
			$res=$this->dMallConf->getActivityRushItem($id);
		}
		if($data['start_time']){
			$data['start_time']=strtotime(trim($data['start_time']));
		}
		if($data['end_time']){
			$data['end_time']=strtotime(trim($data['end_time'])." +24 hours");
		}		
		if($data['item_id']!=$res['item_id'] || empty($data['title'])){
			$data['sales_count']=0;
			$itemInfo=$this->dGoods->getThisItemInfo($data['item_id'],'title,cat_id,shop_id,price,image_default_id');
			if($data['title']){
				unset($itemInfo['title']);
			}
			if(empty($itemInfo)){
				$this->error("商品ID填写有错，暂无该商品！");
			}
			$data=array_merge($data,$itemInfo);
			$data['item_default_image']=$data['image_default_id'];
		}
		$res=$this->dMallConf->saveActivityRushItem($id,$data);
		if($res){
			$this->success("修改成功!");
		}else{
			$this->success("修改失败!");
		}		
	}
/*
 * 
 * 轮播图配置
 * */
	public function shufflingFigure(){
		$shuffId=I('shuffId');
		$shuffling=$this->dMallConf->getAllshuffling();
		if($shuffId){
			$condition['shuffling_id']=$shuffId;
		}
		$list=$this->dMallConf->getshufflingFigure($condition);
		foreach($list as $key=>$value){
			foreach($shuffling as $keys=>$values){
				if($value['shuffling_id']==$values['shuffling_id']){
					$list[$key]['position']=$values['describe'];
				}
			}			
		}
		$this->assign('shuffling',$shuffling);
		$this->assign('list',$list);
		$this->display();
	}
/*
 * 添加轮播情景
 * */
	public function addShuffling(){
		
		$this->display();
	}
/*
 * 保存轮播情景
 * */
	public function saveShuffling(){
		$data=I('data');
		if(empty($data['describe'])){
			$this->error("请填写轮播图位置！");
		}
		if(empty($data['identify'])){
			$this->error("请填写标识！");
		}		
		$data['created_time']=time();
		$res=$this->dMallConf->addshuffling($data);
		if($res){
			$this->success("添加成功!");
		}else{
			$this->success("添加失败!");
		}			
	}
/*
 * 添加轮播图
 * */
	public function addShufflingFigure(){
		$figureId=I('figureId');
		$condition['figure_id']=$figureId;
		$info=$this->dMallConf->getThisshufflingFigure($condition);
		$shuffling=$this->dMallConf->getAllshuffling();
		$this->assign('shuffling',$shuffling);		
		$this->assign('info',$info);		
		$this->display();
	}	
/*
 * 保存轮播图添加、修改
 * */
	public function saveShufflingFigure(){
		$figureId=I('figureId');
		$data=I('data');
		$data['the_color']=trim($data['the_color']);
		$banner=A('Activity')->uploadImg('banner');
		if(!empty($banner)){
			$data['banner_img']=$banner;
		}
		if($figureId){
			//编辑
			$say="编辑";
			$data['modifyine_time']=time();
			$res=$this->dMallConf->saveshufflingFigure($figureId,$data);
		}else{
			//添加
			$data['created_time']=time();
			$say="添加";
			$res=$this->dMallConf->addshufflingFigure($data);
			
		}
		if($res){
			$this->success($say."成功！");
		}else{
			$this->error($say."失败！");
		}		
	}
/*
 * 轮播图删除/禁用启用
 * */	
	public function changeShuffling(){
		$type=I('type');
		$val=I('val');
		$figureId=I('figureId');
		if($type=="status"){
			//禁用启用
			if($val == 1){
				$data['status']=0;
			}else if($val==0){
				$data['status']=1;
			} 
		}else if($type=="del"){
			//删除情景
			$data['is_delete']=1;
		}
		$data['modifyine_time']=time();
		$res=$this->dMallConf->saveshufflingFigure($figureId,$data,"status,is_delete,modifyine_time");
		echo json_encode($res);
	}
/*
 * 商品活动规则设置：满返，满减，满折
 * */	
 	public function fulldiscountIndex(){
		$size=10;
		$condition=array('is_delete'=>0);
		$number=$this->dMallConf->getCountFulldiscount($condition);
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$res=$this->dMallConf->getAllFulldiscount($condition,$limit);
		foreach($res as $key=>$value){
			$discoiuntIds[]=$value['fulldiscount_id'];
		}
		if(!empty($discoiuntIds)){
			$disCondition=array('fulldiscount_id'=>array('in',$discoiuntIds));
			$discountCom=$this->dMallConf->getSomeFulldiscountCom($disCondition,'fulldiscount_id,com_id');//参加公司
			foreach($discountCom as $key=>$value){
				$comIds[]=$value['com_id'];
			}		
			$companys=D('Order')->getThisCompany($comIds);
			foreach($discountCom as $key=>$value){
				foreach($companys as $comK=>$comV){
					if($value['com_id']==$comV['com_id']){
						$discountCom[$key]['companyName']=$comV['com_name'];
					}
				}
			}
		}
		$discountRule=$this->dMallConf->getFulldiscountRole($disCondition);//规则
		foreach($res as $key=>$value){
			foreach($discountCom as $comK=>$comV){
				if($value['fulldiscount_id']==$comV['fulldiscount_id']){
					$res[$key]['company'][]=$comV['companyName'];
				}
			}
			foreach($discountRule as $comK=>$comV){
				if($value['fulldiscount_id']==$comV['fulldiscount_id']){
					$res[$key]['rule'][]=$comV;
				}
			}			
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$res);	 		
		$this->display();
 	}
//促销商品列表
	public function fulldiscountItems(){
		$fulldiscountId=I('fulldiscountId');
		if($fulldiscountId){
			$items=$this->dMallConf->getSomeFulldiscountItem(array('fulldiscount_id'=>$fulldiscountId));//参加商品
			$this->assign('items',$items);
			$this->display();
		}else{
			echo "Error";
		}
	}
/*
 * 活动规则删除/禁用启用
 * */	
	public function changeFulldiscount(){
		$type=I('type');
		$val=I('val');
		$fulldiscountId=I('fulldiscountId');
		if($type=="status"){
			//禁用启用
			if($val == 1){
				$data['status']=0;
			}else if($val==0){
				$data['status']=1;
			} 
		}else if($type=="del"){
			//删除情景
			$data['is_delete']=1;
		}
		$condition['fulldiscount_id']=$fulldiscountId;
		$res=$this->dMallConf->modifyFulldiscount($condition,$data);
		echo json_encode($res);
	}
/*
 * 添加活动规则
 * */	
	public function fulldiscountAdd(){
		$res=D('Order')->getAllCompany();
		$this->assign('company',$res);
		$fulldiscountId=I('fulldiscountId');
		if($fulldiscountId){
			$condition['fulldiscount_id']=$fulldiscountId;
			$res=$this->dMallConf->getThisFulldiscount($condition,$field);
			$res['start_time']=date("Y-m-d",$res['start_time']);
			$res['end_time']=date("Y-m-d",$res['end_time']);
			$joinCom=$this->dMallConf->getThisFulldiscountCom($condition,'com_id');
			$joinItem=$this->dMallConf->getFieldFulldiscountItem($condition,'item_id');//参加商品
			$discountRule=$this->dMallConf->getFulldiscountRole($condition);//规则
			$res['com']=implode(',', $joinCom);
			$res['items']=implode(',', $joinItem);
			$res['rule']=$discountRule;
			$this->assign('info',$res);
		}
		$this->display();
	}
/*
 * 保存促销规则
 * */
	public function fulldiscountSave(){
		$data=I('data');
		$comIds=I('comIds');
		$itemIds=trim(I('itemIds'));
		$fulldiscountId=I('fulldiscountId');
		$fulledFee=I('fulledFee');
		$promotion=I('promotion');
		if(in_array("", $data)){
			$this->error("促销信息请填写完整!");
		}
		if(!isset($data['fulldiscount_type'])){
			$this->error("请选择促销类型!");
		}
		if($data['startTime']){
			$data['start_time']=strtotime($data['startTime']);
		}
		if($data['endTime']){
			$data['end_time']=strtotime($data['endTime']." +24 hours");
		}		
		if($data['start_time']>$data['end_time']){
			$this->error("促销时间有误!");
		}
		$data['promotion_tag']=$data['fulldiscount_name'];
		$data['shop_id']=0;//店铺暂时为0
		$ItemArr=explode(',', $itemIds);
		//促销规则
		if(!empty($fulledFee) && !empty($promotion)){
			foreach($fulledFee as $k=>$v){
				$ruleInfo[$k]['fulled_fee']=trim($v);
			}
			foreach($promotion as $k=>$v){
				$ruleInfo[$k]['promotion']=trim($v);
			}
			foreach($ruleInfo as $k=>$v){
				if(empty($v['fulled_fee']) || empty($v['promotion'])){
					unset($ruleInfo[$k]);
				}else if(!is_numeric($v['fulled_fee']) || !is_numeric($v['promotion']) || $v['promotion']>$v['fulled_fee']){
					$num=$k+1;
					$this->error("第".$num."条促销规则有误!");
				}
			}
		}	
		if(empty($ruleInfo)){
			$this->error("请添加促销规则!");
		}	
		if($fulldiscountId){
			//修改
			$condition=array('fulldiscount_id'=>$fulldiscountId);
			$this->dMallConf->modifyFulldiscount($condition,$data);
		}else{
			//添加
			$data['created_time']=time();
			$fulldiscountId=$this->dMallConf->addfulldiscount($data);
		}
		//促销规则
		$delRoleRes=$this->dMallConf->delFulldiscountRole(array('fulldiscount_id'=>$fulldiscountId));
		foreach($ruleInfo as $key=>$value){
			$value['promotion_tag']=$data['fulldiscount_name'];
			$value['fulldiscount_id']=$fulldiscountId;
			$roleAdd[]=$this->dMallConf->addfulldiscountRole($value);
		}			
		//促销公司
		if(is_array($comIds)){
			$delComRes=$this->dMallConf->delFulldiscountCom(array('fulldiscount_id'=>$fulldiscountId));
			foreach($comIds as $key=>$value){
				$comAdd[]=$this->dMallConf->addfulldiscountCom(array('com_id'=>$value,'fulldiscount_id'=>$fulldiscountId,'promotion_tag'=>$data['fulldiscount_name']));
			}
		}
		//促销商品
		if(!empty($itemIds)){
			if(is_array($ItemArr)){
				$delItemRes=$this->dMallConf->delFulldiscountItem(array('fulldiscount_id'=>$fulldiscountId));
				$field='item_id,cat_id,shop_id,title,image_default_id,price';
				$itemsInfo=$this->dMallConf->getItemsInfo(array('item_id'=>array('in',$ItemArr)),$field);
				if(!empty($itemsInfo)){
					foreach($itemsInfo as $key=>$value){
						$value['fulldiscount_id']=$fulldiscountId;
						$value['leaf_cat_id']=$value['cat_id'];
						$value['promotion_tag']=$data['fulldiscount_name'];
						$value['start_time']=$data['end_time'];
						$value['end_time']=$data['end_time'];
						$ItemAdd[]=$this->dMallConf->addfulldiscountItem($value);
					}
				}
			}
		}
		if(!empty($comAdd) && !empty($ItemAdd) && !empty($roleAdd)){
			$this->success("促销规则编辑成功！");
		}else{
			$this->error("促销规则编辑失败！");
		}
	}
/*
 * 商品打特价条件
 * */
	public function specialPriceCondition(){
		$data=I('');
		if(!empty($data['comId'])){
			$condition['com_id']=$data['comId'];
		}
		if(!empty($data['skuId'])){
			$condition['sku_id']=trim($data['skuId']);
		}
		if(!empty($data['itemId'])){
			$condition['item_id']=trim($data['itemId']);
		}
		if(!empty($data['goods'])){
			$condition['title']=array('like','%'.trim($data['goods']).'%');
		}
		if(!empty($data['priceStart']) && !empty($data['priceEnd'])){
			$condition['price']=array('between',array(trim($data['priceStart']),trim($data['priceEnd'])));
		}	
		if(!empty($data['oldPriceStart']) && !empty($data['oldPriceEnd'])){
			$condition['shop_price']=array('between',array(trim($data['oldPriceStart']),trim($data['oldPriceEnd'])));
		}			
		return $condition;
	}
/*
 * 商品打特价
 * */
	public function specialPriceIndex(){
		$condition=$this->specialPriceCondition();
		$size=10;
		$number=$this->dMallConf->getComapnyItemsCount($condition);
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$res=$this->dMallConf->getComapnyItems($condition,$limit);
		foreach($res as $key=>$value){
			$comIds[]=$value['com_id'];
		}
		$companys=D('Order')->getThisCompany($comIds);
		foreach($res as $key=>$value){
			foreach($companys as $keys=>$values){
				if($value['com_id']==$values['com_id']){
					$res[$key]['company']=$values['com_name'];	
				}				
			}
		}		
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$res);	 		
		$this->display();		
	}
/*
 * 
 * 新增特价商品
 * */
	public function addSpecialItem(){
		$res=D('Order')->getAllCompany();
		$this->assign('company',$res);		
		$this->display();
	}
/*
 * 编辑特价商品
 * */	
 	public function editSpecialItem(){
		$itemPriceId=I('itemPriceId');
 		$res=$this->dMallConf->getThisComapnyItems($condition);
		$res['start_time']=date("Y-m-d",$res['start_time']);
		$res['end_time']=date("Y-m-d",$res['end_time']);		
		$this->assign('info',$res);
		$this->display();
 	}
	
/*
 * 新增特价商品处理
 * */	
	public function editSpecealItemDeal(){
	 	$data=I('data');
		$itemIds=I('itemId');
		$skuIds=I('skuId');
		$price=I('price');
		$comIds=I('comIds');
		if($data['startTime']){
			$data['start_time']=strtotime(trim($data['startTime']));
		}
		if($data['endTime']){
			$data['end_time']=strtotime(trim($data['endTime'])." +24 hours");
		}			
		if(!empty($itemIds) && !empty($skuIds) && !empty($price)){
			foreach($itemIds as $k=>$v){
				$itemInfo[$k]['item_id']=intval(trim($v));
			}
			foreach($skuIds as $k=>$v){
				$itemInfo[$k]['sku_id']=intval(trim($v));
			}
			foreach($price as $k=>$v){
				$itemInfo[$k]['price']=intval(trim($v));
			}
			foreach($itemInfo as $k=>$v){
				if(empty($v['item_id']) || empty($v['sku_id']) || empty($v['price'])){
					unset($itemInfo[$k]);
				}else{
					$itemId[]=$v['item_id'];
				}
			}
		}
		if(!empty($itemId)){
			$infos=$this->dMallConf->getItemsInfo(array('item_id'=>array('in',$itemId)),'item_id,title,price,image_default_id');
			foreach($itemInfo as $key => $value){
				foreach($infos as $keys => $values){
					if($value['item_id']==$values['item_id']){
						$itemInfo[$key]['start_time']=$data['start_time'];
						$itemInfo[$key]['end_time']=$data['end_time'];
						$itemInfo[$key]['title']=$values['title'];
						$itemInfo[$key]['shop_price']=$values['price'];
						$itemInfo[$key]['image_default_id']=$values['image_default_id'];
					}
				}
			}
		}	
		if(!empty($comIds)){
			//商品对应多公司
			foreach($comIds as $key=>$value){
				foreach($itemInfo as $keys => $values){
					$values['com_id']=$value;
					$values['add_time']=time();
					$addRes[]=$this->dMallConf->addCompanyItem($values);
				}
			}			
		}else{
			//所有公司都符合
			foreach($itemInfo as $keys => $values){
				$values['add_time']=time();
				$addRes[]=$this->dMallConf->addCompanyItem($values);
			}			
		}
		if($addRes){
			$this->success('特价商品添加成功！');
		}else{
			$this->error('特价商品添加失败！');
		}
		
	}
/*
 *编辑特价商品处理 
 * */
 	public function SpecealItemDeal(){
		$itemPriceId=I('itemPriceId');
 		if($itemPriceId){
 			$data=I('data');
			echo '正在开发！';
 		}else{
			echo 'ID为空！'; 			
 		}
 	}
/*
 * 特价商品删除/禁用启用
 * */	
	public function changeCompanyItem(){
		$type=I('type');
		$val=I('val');
		$itemPriceId=I('itemPriceId');
		if($type=="status"){
			//禁用启用
			if($val == 1){
				$data['status']=0;
			}else if($val==0){
				$data['status']=1;
			} 
		}else if($type=="del"){
			//删除情景
			$data['is_delete']=1;
		}
		$condition['item_price_id']=$itemPriceId;
		$res=$this->dMallConf->modifyCompanyItem($condition,$data);
		echo json_encode($res);
	}
/*
 * 广告组管理的搜索条件
 * */
	private function adGroupCondition(){
		if($_GET['gropName']){
			$_GET['gropName']=urldecode($_GET['gropName']);
		}		
		$data=I();
		if($data['gropName']){
			$condition['group_name|group_desc|group_title|group_vice_title']=array('like','%'.trim($data['gropName']).'%');
			$this->assign('gropName',trim($data['gropName']));
		}
		return $condition;
	}	
/**
 *广告组管理 
 **/
	public function adGroupIndex(){
		$condition=$this->adGroupCondition();
		$number=$this->dMallConf->getAdGroupCount($condition);
		$size=20;
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$list=$this->dMallConf->getAllAdGroup($condition,$limit);
		$comGroups=array();
		$areaIds=array();
		$moduleIds=array();
		$tempIds=array();
		foreach($list as $key=>$val){
			$comGroups[]=$val['com_group'];
			$areaIds[]=$val['area_id'];
			$moduleIds[]=$val['module_id'];
			$tempIds[]=$val['template_id'];
		}
		if(!empty($comGroups)){
			//公司组
			$where=array(
				'com_group'=>array('in',$comGroups)
			);
			$companys=$this->dMallConf->getCompanys($where,'com_id,com_name,com_group');
		}
		foreach($list as $key=>$val){
			foreach($companys as $keys=>$vals){
				if($val['com_group']==$vals['com_group']){
					$list[$key]['com'][]=$vals;
				}
				
			}
		}	
		if(!empty($areaIds)){
			//广告区
			unset($where);
			$where=array(
				'area_id'=>array('in',$areaIds)
			);			
			$areas=$this->dMallConf->getAllAdArea($where);
			$newAreas=$this->arrKeyreformArr($areas,'area_id');
		}
		if(!empty($moduleIds)){
			//模块
			unset($where);
			$where=array(
				'module_id'=>array('in',$moduleIds)
			);			
			$modules=$this->dMallConf->getAllmodule($where);
			$newModules=$this->arrKeyreformArr($modules,'module_id');
		}
		if(!empty($tempIds)){
			//模板
			unset($where);
			$where=array(
				'template_id'=>array('in',$tempIds)
			);			
			$temps=$this->dMallConf->getAlltemp($where);
			$newTemps=$this->arrKeyreformArr($temps,'template_id');
		}	
		$pagestr = $page->show("pageos","pageon");  //组装分页字符串
		$this->assign('newAreas',$newAreas);
		$this->assign('newModules',$newModules);
		$this->assign('newTemps',$newTemps);
		$this->assign('pagestr',$pagestr);	
		$this->assign('list',$list);
		$this->assign('number',$number); 		
		$this->display();
	}
/**
 * 数组重组
 *@param $arr需重组的数组
 * @param $key作为新数组的键值
 **/
	private function arrKeyreformArr($arr,$k){
		if(!is_array($arr)){
			return $arr;
		}
		if(!$k){
			return $arr;
		}
		$newArr=array();
		foreach($arr as $key=>$val){
			$newArr[$val[$k]]=$val;
		}
		return $newArr;
	}
/**
 *添加、编辑广告组 
 **/
	public function editAdGroup(){
		$groupId=I('groupId',0,'intval');
		if($groupId){
			$where['group_id']=$groupId;
			$info=$this->dMallConf->getAdGroup($where);
			if($info['template_id']){
				unset($where);
				$where['template_id']=$info['template_id'];
				$thisTemp=$this->dMallConf->getThistemp($where);
				$this->assign('thisTemp',$thisTemp);
			}
			if($info['module_id']){
				unset($where);
				$where['module_id']=$info['module_id'];
				$thisModule=$this->dMallConf->getThismodule($where);
				$this->assign('thisModule',$thisModule);
			}
			$this->assign('info',$info);
		}
		//所有公司
		$allcom=$this->dMallConf->getCompanys(array(),'com_name,com_group');
		//广告区
		$allArea=$this->dMallConf->getAllAdArea();
		$this->assign('allArea',$allArea);
		$this->assign('allcom',$allcom);
		$this->display();
	}
/*
 * 动态获取广告模块模板
 * */
	public function getAdModule(){
		$areaId=I('areaId','0','intval');
		$moduleId=I('moduleId','0','intval');	
		$where=array(
			'area_id'=>$areaId
		);
		$res=$this->dMallConf->getAllmodule($where);
		$options='';
		foreach($res as $key=>$val){
			$options.="<option value=\"{$val['module_id']}\" ";
			if($val['module_id']==$moduleId){
				$options.="selected=\"selected\"";
			}
			$options.=" >{$val['module_name']}</option>";
		}
		$this->ajaxReturn($options);
	}
	public function getAdTemp(){
		$areaId=I('areaId','0','intval');	
		$templateId=I('templateId','0','intval');	
		$where=array(
			'area_id'=>$areaId
		);
		$res=$this->dMallConf->getAlltemp($where);
		$list='';
		foreach($res as $key=>$val){
 		  	$list.= "<div style=\"padding-top:20px\"><div class=\"formControls col-6\" title=\"{$val['template_desc']}\"><label class=\"\"><input name=\"data[template_id]\" value=\"{$val['template_id']}\"  ";
			if($val['module_id']==$moduleId){
				$list.=" checked=\"checked\" ";
			}  			
  			$list.=" type=\"radio\" >";
  			$list.="<img src=\"{$val['template_pic']}\" style=\"max-height: 80px;max-width: 280px;\" /></label></div></div>";			
		}
		$this->ajaxReturn($list);
	}		
/**
 *添加、编辑广告组 处理
 **/
	public function editAdGroupDeal(){
		$groupId=I('groupId',0,'intval');
		$data=I('data');
		$data=$this->trimArrVal($data);   //去空
		if(empty($data)){
			$this->error("数据不能为空!");
		}
		if($groupId){
			//更新
			$where=array(
				'group_id'=>$groupId
			);
			$res=$this->dMallConf->updateAdGroup($where,$data);
		}else{
			//添加
			$res=$this->dMallConf->addAdGroup($data);
		}
		if($res){
			$this->success("SUCCESS");
		}else{
			$this->error("FAIL");
		}
		
	}	
/*
 * 数组字符串首尾去空
 **/	
 	private function trimArrVal($arr){
 		if(!is_array($arr)){
			return $arr;
 		}
 		foreach($arr as &$v){
 			if(is_string($v)){
 				$v=trim($v);
 			}
 		}
		return $arr;
 	}
/**
 *操作成功js样式 (关闭当前刷新之前的)
 **/
	public function jsSayRes(){
		echo <<<Eof
		<script>
  		parent.location.reload();
		</script>
Eof;

		
	}
/*
 * 广告组选择广告位
 * */
	public function choicePosition(){
		//所有广告位
		$posname=I('posname','','trim');
		$groupId=I('groupId','0','intval');
		$isSercah=I('isSercah');
		$type=I('type');
		$where=array(
			'group_id'=>$groupId
		);
		//已关联的广告位
		$relationPos=$this->dMallConf->getGroupFieldPos($where);
		unset($where);
		if($type=="has"){
			//已关联广告位
			if(!empty($relationPos)){
				$where['position_id']=array('in',$relationPos);
			}else{
				$where['position_id']=0;
			}
		}
		if($posname){
			$where['position_name|position_desc|position_title|position_vice_title|item_id|position_id']=array('like','%'.trim($posname).'%');
		}
		$allPostion=$this->dMallConf->getAdCondPostion($where,'position_id,position_name,position_desc');	
		$this->assign('allPostion',$allPostion);
		$this->assign('posname',$posname);
		$this->assign('relationPos',$relationPos);
		$this->assign('groupId',$groupId);
		$this->assign('isSercah',$isSercah);
		$this->display();
	}
/*
 *选择广告位处理 
 * */	
	public function choicePositionDeal(){
		$groupId=I('groupId','0','intval');
		$checkedPostion=I('checkedPostion');
		$isSercah=I('isSercah');
		if(!$groupId){
			$this->error("FAIL");
		}
		if(!is_array($checkedPostion)){
			$this->error("FAIL");
		}
		//原来已选择的广告位
		$where=array(
			'group_id'=>$groupId
		);
		if($isSercah){
			//搜索条件时
			//已关联的广告位
			$relationPos=$this->dMallConf->getGroupFieldPos($where);	
			$samePos=array_intersect($checkedPostion, $relationPos);
			if(!empty($samePos)){
				$where['position_id']=array('in',$samePos);
			}else{
				$where['position_id']=0;
			}
		}
		$alldata=array();
		foreach($checkedPostion as $key=>$val){
			$alldata[$key]['group_id']=$groupId;
			$alldata[$key]['position_id']=$val;
		}
		if(empty($alldata)){
			$this->error("请选择需关联的广告位!");
		}
		//先清除原来广告位关联
		$this->dMallConf->delAdrelation($where);
		$res=$this->dMallConf->addAllAdRelation($alldata);
		if($res){
			$this->success("SUCCESS");
		}else{
			$this->error("FAIL");
		}		
	}
/*
 * 广告位管理的搜索条件
 * */
	private function adPositionCondition(){
		if($_GET['posname']){
			$_GET['posname']=urldecode($_GET['posname']);
		}		
		$data=I();
		if($data['groupId']){
			$postionIds=$this->dMallConf->getGroupFieldPos(array('group_id'=>$data['groupId']));
			if($postionIds){
				$condition['position_id']=array('in',$postionIds);
			}else{
				$condition['position_id']=0;
			}
		}
		if($data['posname']){
			$condition['position_name|position_desc|position_title|position_vice_title|item_id|position_id']=array('like','%'.trim($data['posname']).'%');
			$this->assign('posname',trim($data['posname']));
		}
		return $condition;
	}
/*
 * 广告位管理
 * */
 	public function adPositionIndex(){
 		$type=I('type');
		$condition=$this->adPositionCondition();
		$number=$this->dMallConf->getAdPostionCount($condition);
		$size=30;
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$list=$this->dMallConf->getAllAdPostion($condition,$limit);
		$pagestr = $page->show("pageos","pageon");  //组装分页字符串
		$this->assign('pagestr',$pagestr);	
		$this->assign('list',$list);
		$this->assign('number',$number); 
		$this->assign('type',$type);		
		$this->display();
 	} 
/*
 * 添加/编辑广告位
 * */ 	
	public function editAdPostion(){
		$postionId=I('postionId',0,'intval');
		if($postionId){
			$where=array(
				'position_id'=>$postionId
			);
			$info=$this->dMallConf->getThisAdPostion($where);
			if($info['start_time']){
				$info['startTime']=date('Y-m-d',$info['start_time']);
			}
			if($info['end_time']){
				$info['endTime']=date('Y-m-d',$info['end_time']);
			}			
			$this->assign('info',$info);
		}
		$this->display();
	}	
/*
 * 编辑广告位处理
 * */
	public function editAdPostionDeal(){
		$data=I('data');
		$editPrice=I('price');
		$postionId=I('postionId',0,'intval');
		$data=$this->trimArrVal($data);   //去空
		$data['order_sort']=I('orderSort','0','intval,trim');
		$startTime=I('startTime');
		$endTime=I('endTime');
		//链接类型
		if($data['link_type']==1){
			//商品	
			$data['item_id']=I('itemId');//商品id
			if(empty($data['item_id'])){
				$this->error("请输入商品ID!");
			}
			$itemInfo=$this->dGoods->getThisItemInfo($data['item_id'],'item_id,image_default_id,price');
			if(empty($itemInfo)){
				$this->error("商品不存在!");
			}
			if(empty($editPrice)){
				$data['item_price']=$itemInfo['price'];
			}else{
				$data['item_price']=$editPrice;
			}
			$data['refer_link']="/shop.php/Info/index/itemId/{$data['item_id']}";//跳转链接
			$data['position_pic']=$itemInfo['image_default_id'].'_m.'.end(explode('.',$itemInfo['image_default_id']));
		}else if($data['link_type']==4){
			//网页
			$data['refer_link']=I('referLink');//跳转链接
		}//预留位置其他链接类型时
		$img=A('Activity')->uploadImg('positionPic');
		if(!empty($img)){
			$data['position_pic']=$img;
		}	
		if(!empty($startTime)){
			$data['start_time']=strtotime($startTime);
		}
		if(!empty($endTime)){
			$data['end_time']=strtotime($endTime." +24 hours");
		}
		if(!empty($endTime) && !empty($endTime) && $data['start_time']>$data['end_time']){
			$this->error("起止日期有误!");
		}
		if($postionId){
			//更新
			$where=array(
				'position_id'=>$postionId
			);
			$res=$this->dMallConf->updateAdPostion($where,$data);
		}else{
			//添加
			$res=$this->dMallConf->addAdPostion($data);
		}
		if($res){
			$this->success("SUCCESS");
		}else{
			$this->error("FAIL");
		}
	}
/*
 *删除广告位 
 * */
 	public function delPostion(){
 		$postionId=I('postionId',0,'intval');
		$ret=array(
			'code'=>0,
			'msg' =>'Unkonw'
		);
		//删除图片
		$where=array(
			'position_id'=>$postionId
		);
		$info=$this->dMallConf->getThisAdPostion($where);	
		$lishe=C('TMPL_PARSE_STRING.__LISHE__').'/Upload/';	
		$picHead=explode($lishe, $info['position_pic']);
		if(count($picHead)==2){
			$load='/home/wwwroot/bbc/public/Upload/'.$picHead[1];
			@unlink($load);	
		}
		$res=$this->dMallConf->delADPostion($postionId);
		if($res){
			$ret['code']=1;
			$ret['msg']='删除成功!';
		}
		$this->ajaxReturn($ret);
 	}
/*
 * 取出商品信息
 * */
	public function getThisItemIfo(){
		$itemId=I('get.itemId','0','intval');
		$itemInfo=$this->dGoods->getThisItemInfo($itemId,'item_id,title,image_default_id,price');
		$this->ajaxReturn($itemInfo);
	}
/*
 *取出文件夹下的所有文件 
 * */
 	private function dirFileName($dir){
		$fileArr=array();	 		
		if(!is_dir($dir)){
			return $fileArr;
		}	
		if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false) {
				$fileArr[]=$file;
			} 
			closedir($dh);
		}
		return $fileArr;
 	}
/*
 * 广告位广告组生成模板文件
 * **/
	public function buildTemp(){
		$groupId=I('groupId');
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);
		if(!$groupId){
			$ret['msg']='ID标识不存在!';
			$this->ajaxReturn($ret);
		}
		$where=array(
			'group_id'=>$groupId
		);
		$groupInfo=$this->dMallConf->getAdGroup($where);		
		if(!$groupInfo){
			$ret['msg']='广告组不存在!';
			$this->ajaxReturn($ret);			
		}
		//广告区的细节
		unset($where);		
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}			
		$dir=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/';
		$fileArr=$this->dirFileName($dir);
		foreach($fileArr as $key=>$fielName){
			if(current(explode('_', $fielName))==$moduleInfo['module_id']){
				$delFile[]=$fielName;
			}
		}
		if(!empty($delFile)){
			foreach($delFile as $key=>$del){
  			  @unlink($dir.$del);
			}
		}
		$lishe=C('TMPL_PARSE_STRING.__LISHE__');		
		$url=$lishe."/shop.php/Index/dyTemp/";
        $data = array(
            	'groupId'=>$groupId,
			);
		$return=$this->requestPost($url,$data);
		$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
		$this->ajaxReturn($resu);
//		if($groupInfo['template_id']){
//			unset($where);
//			$where=array(
//				'template_id'=>$groupInfo['template_id']
//			);
//			$tempInfo=$this->dMallConf->getThistemp($where);
//		}
//		if(!$tempInfo){
//			$ret['msg']='模板不存在!';
//			$this->ajaxReturn($ret);			
//		}
//		//当前页的方法
//		$con=A(CONTROLLER_NAME);
//		$functions=get_class_methods($con);
//		$tempMethod=$tempInfo['method'];
//		if(!$tempMethod){
//			$ret['msg']='未定义模板方法!';
//			$this->ajaxReturn($ret);				
//		}
//		if(!in_array($tempMethod, $functions)){
//			$ret['msg']='模板方法不存在!';
//			$this->ajaxReturn($ret);			
//		}
//		$res=$this->$tempMethod($groupInfo);
//		$this->ajaxReturn($res);			
		
	}
/*
 * 取出广告组中所有广告位的数据
 * */
	private function getRelationPostion($groupId){
		if(!$groupId){
			return null;
		}
		$where['group_id']=$groupId;
		$relationPos=$this->dMallConf->getGroupFieldPos($where);
		if(!$relationPos){
			return null;
		}
		unset($where);
		$where=array(
			'position_id'=>array('in',$relationPos)
		);
		$postions=$this->dMallConf->getAdCondPostion($where,'*','order_sort desc');
		return $postions;
	}
	//创建目录
	private function creatDir($fileName){
		if(strpos($fileName,'/')){
			$dirArray = explode('/',$fileName);
			array_pop($dirArray);
			foreach($dirArray as $val){
				$dir .= $val.'/';
				$oldumask = umask(0);
				if(!is_dir($dir)){
					mkdir($dir,0777);
				}
				chmod($dir,0777);
				umask($oldumask);
			}
			return true;
		}
		return false;
	}
/*
 * 模板写入内容
 * */		
 	private function tempPutContents($file,$txt){
		if(!$this->creatDir($file)){
			$ret['msg']='目录创建失败!';
			return $ret;			
		}
		if(@file_put_contents($file, $txt)){
			$ret['code']=1;
			$ret['msg']='模板创建成功!';
			return $ret;
		}else{
			$ret['msg']='模板创建失败!';
			return $ret;			
		} 		
 	}
/*
 * 模板
 * 首页零食宴会，专场，家纺，家旅<!--商品栏1-->
 * */
	private function pcIndexZones($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,4);
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}
		$txt.='<div class="commodity1 mauto">';
		$txt.='	<ul class="comm1_ul">';
		foreach($info as $key=>$val){
			$txt.='		<li ';
			if($key==3){
				$txt.='class="margin0"';
			}
			$txt.=' data_startTime="'.$val['start_time'].'" data_endTime="'.$val['end_time'].'" >';
			$txt.='			<a href="'.$val['refer_link'].'" target="_blank" class="comm1_a'.($key+1).'">';
			$txt.='				<div class="comm1_text">';
			$txt.='					<b class="sptext1">'.$val['position_title'].'</b>';
			$txt.='					<span class="sptext2">'.$val['position_vice_title'].'</span>';
			$txt.='					<span class="sptext3">GO</span>';
			$txt.='				</div>';
			$txt.='				<div class="comm_img">';
			$txt.='					<img src="'.$val['position_pic'].'"/>';
			$txt.='				</div>';
			$txt.='			</a>';
			$txt.='		</li>';
		}
		$txt.='	</ul>';
		$txt.='</div>';
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);

	}
/*
 * 模板
 * 首页-品牌
 * */
	private function pcIndexBrand($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,13);	
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}			
		$txt='<div class="brand mauto">';
		$txt.='<div class="left_item"><a target="_blank"><img src="'.$info[0]['position_pic'].'" alt="" /></a></div>';
		$txt.='<div class="right_item">';
		$txt.='		<ul class="brand_ul">';
		foreach($info as $key=>$val){
			if($key>0){
				$txt.='<li style="background-image: url('.$val['position_pic'].');"><a href="'.$val['refer_link'].'" target="_blank" class="side_a"></a><a href="'.$val['refer_link'].'" target="_blank" class="side_a2">点击进入</a></li>';
			}
		}
		$txt.='		</ul>';
		$txt.='	</div>';
		$txt.='</div>';	
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
	}
/*
 * 模板
 * 首页-<!--限时秒杀-->
 * */
	private function pcIndexSeckill($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,3);
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}			
		$lishe=C('TMPL_PARSE_STRING.__LISHE__');		
		$txt='<div class="seckill mauto">';
		$txt.='	<div class="seckill_tit">';
		$txt.='		<div class="seckill_left_text">'.$groupInfo['group_title'].'</div>';
		$txt.='		<a class="seckill_right_text">'.$groupInfo['group_vice_title'].'</a>';
		$txt.='	</div>';
		$txt.='	<ul class="seckill_con">';
		foreach($info as $key=>$val){
			$txt.='		<li>';
			$txt.='			<div class="seckill_img">';
			$txt.='				<a href="'.$lishe.'/shop.php/Info/index/itemId/'.$val['item_id'].'.html" target="_blank">';
			$txt.='					<img src="'.$val['position_pic'].'" alt="" />';
			$txt.='				</a>';
			$txt.='			</div>';
			$txt.='			<div class="seckill_text">';
			$txt.='				<p class="seckill_text_name">'.$val['position_name'].'</p>';
			$txt.='				<h2>'.$val['position_title'].'</h2>';
			$txt.='				<p class="seckill_text_con">'.$val['position_vice_title'].'</p>';
			$txt.='				<p class="sec_price"><em>'.($val['item_price']*100).'</em>积分</p>';
			$txt.='				<a href="'.$lishe.'/shop.php/Info/index/itemId/'.$val['item_id'].'.html" class="buy" target="_blank">立即抢购</a>';
			$txt.='			</div>';
			$txt.='		</li>';
		}
		$txt.='	</ul>';
		$txt.='</div>';		
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
	}
/*
 * 模板
 * 首页-<!--今日最优-->
 * */
	private function pcIndexAmple($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,6);	
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}		
		$lishe=C('TMPL_PARSE_STRING.__LISHE__');		
		$txt='<div class="best">';
		$txt.='	<h2 class="product_head">';
		$txt.='		<div class="h2_left">'.$groupInfo['group_title'].'</div>';
		$txt.='		<div class="h2_right"><span>'.$groupInfo['group_vice_title'].'</span><img src="__VIEW__Public/images/index/images/icon/sk_arror.png"></div>';
		$txt.='	</h2>';
		$txt.='	<ul class="find_con">';
		foreach($info as $key=>$val){
			$txt.='		<li class="';
			if(in_array($key, array(1,3,5))){
				$txt.='find_item1 ';
			}
			if(in_array($key, array(4,5))){
				$txt.=' find_item2 ';
			}			
			$txt.='" >';
			$txt.='			<a href="'.$lishe.'/shop.php/Info/index/itemId/'.$val['item_id'].'.html" target="_blank">';
			$txt.='				<div class="find_text">';
			$txt.='					<p class="find_p1">'.$val['position_title'].'</p>';
			$txt.='					<p class="find_price">'.($val['item_price']*100).'</p>';
			$txt.='				</div>';
			$txt.='				<div class="find_img">';
			$txt.='					<img src="'.$val['position_pic'].'"/>';
			$txt.='				</div>';
			$txt.='			</a>';
			$txt.='		</li>';
		}
		$txt.='	</ul>';
		$txt.='</div>';	
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
	}
/*
 * 模板
 * 首页-<!--时日推荐-->
 * */
	private function pcIndexTimeRe($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,3);
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}			
		$lishe=C('TMPL_PARSE_STRING.__LISHE__');		
		$txt='<div class="best best2">';
		$txt.='	<h2 class="product_head">';
		$txt.='		<div class="h2_left">'.$groupInfo['group_title'].'</div>';
		$txt.='		<div class="h2_right"><span>'.$groupInfo['group_vice_title'].'</span><img src="__VIEW__Public/images/index/images/icon/sk_arror.png"></div>';
		$txt.='	</h2>';
		$txt.='	<ul class="best_find2">';
		foreach($info as $key=>$val){
			if($key>1){
				break;
			}
			$txt.='		<li ';
			if($key==1){
				$txt.=' style="margin-left: 20px;" ';
			}
			$txt.='  >';
			$txt.='			<a href="'.$lishe.'/shop.php/Info/index/itemId/'.$val['item_id'].'.html" target="_blank">';
			$txt.='				<h2 class="find2_tit">'.$val['position_title'].'</h2>';
			$txt.='				<p>'.$val['position_vice_title'].'</p>';
			$txt.='				<div class="find2_img">';
			$txt.='					<div class="solid"></div>';
			$txt.='					<img src="__VIEW__Public/images/index/images/find2.png" alt="" />';
			$txt.='				</div>';
			$txt.='			</a>';
			$txt.='		</li>';
			$txt.='	</ul>';
		}
		$txt.='	<a href="'.$lishe.'/shop.php/Info/index/itemId/'.$info[2]['item_id'].'.html" target="_blank" class="barbecue mauto" style="background: url('.$info[2]['position_pic'].') no-repeat;" >';
		$txt.='		<h2>'.$info[2]['position_title'].'</h2>';
		$txt.='	</a>';
		$txt.='</div>';
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
	}	
/*
 * 模板
 * 首页-<!--全网热销-->
 * */
	private function pcIndexSelling($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,6);	
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}		
		$lishe=C('TMPL_PARSE_STRING.__LISHE__');	
		$txt='<div class="best best3">';
		$txt.='	<h2 class="product_head">';
		$txt.='		<div class="h2_left">'.$groupInfo['group_title'].'</div>';
		$txt.='		<div class="h2_right"><span>'.$groupInfo['group_vice_title'].'</span><img src="__VIEW__Public/images/index/images/icon/sk_arror.png"></div>';
		$txt.='	</h2>';
		$txt.='	<ul class="best_find3">';
		foreach($info as $key=>$val){
			$txt.='		<li  class="';
			if(in_array($key, array(2,5))){
				$txt.='br_no1 ';
				
			}				
			if($key>2){
				$txt.=' bb_no1';
			}
			$txt.='		">';
			$txt.='			<a href="'.$lishe.'/shop.php/Info/index/itemId/'.$val['item_id'].'.html" target="_blank" class="find_a">';
			$txt.='				<div class="find3_img">';
			$txt.='					<div class="img1 ';
			if($key==1){
				$txt.='tow';
			}
			if($key==2){
				$txt.='three';
			}
			if($key>2){
				$txt.='four';
			}
			$txt.='					 ">'.($key+1).'</div>';
			$txt.='					<img src="'.$val['position_pic'].'"/>';
			$txt.='				</div>';
			$txt.='				<div class="find3_name">';
			$txt.='					'.$val['position_title'].'';
			$txt.='				</div>';
			$txt.='			</a>';
			$txt.='		</li>';
		}
		$txt.='	</ul>';
		$txt.='</div>';	
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
		
	}	
/*
 * 模板
 * 首页楼层banner
 * */
	private function pcIndexFloorBanner($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,1);
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}		
		$txt='	<div class="litter_banner mauto">';
		$txt.='		<a target="_blank" href="'.$info[0]['refer_link'].'"><img src="'.$info[0]['position_pic'].'" alt="" /></a>';
		$txt.='	</div>';	
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
	}
/*
 * 模板
 * 首页楼层banner下的商品
 * */
	private function pcIndexFloorItems($groupInfo){
		if(!$groupInfo){
			return false;
		}
		$ret=array(
			'code'=>0,
			'msg'=>'unkonw'
		);		
		//广告区的细节
		$where=array(
			'area_id'=>$groupInfo['area_id']
		);
		$areaInfo=$this->dMallConf->getThisAdArea($where);
		if(!$areaInfo){
			$ret['msg']='广告区不存在';
			return $ret;
		}
		//模块信息
		unset($where);
		$where=array(
			'module_id'=>$groupInfo['module_id']
		);		
		$moduleInfo=$this->dMallConf->getThismodule($where);
		if(!$moduleInfo){
			$ret['msg']='广告模块不存在';
			return $ret;
		}		
		$postions=$this->getRelationPostion($groupInfo['group_id']); //所有广告位
		$info=array_slice($postions,0,5);
		if(empty($info)){
			$ret['msg']='还未选择广告位!';
			return $ret;			
		}		
		$lishe=C('TMPL_PARSE_STRING.__LISHE__');		
		$txt='<div class="banner_item mauto">';
		$txt.='	<ul class="banner_ul">';
		foreach($info as $key=>$val){
			$txt.='		<li>';
			$txt.='			<div class="bann_img"><a href="'.$lishe.'/shop.php/Info/index/itemId/'.$val['item_id'].'.html" target="_blank"><img src="'.$val['position_pic'].'" alt="" /></a></div>';
			$txt.='			<div class="bann_text">';
			$txt.='				<p class="bp_1">'.$val['item_id'].'</p>';
			$txt.='				<p class="bp_2">'.($val['item_price']*100).'积分</p>';
			$txt.='			</div>';
			$txt.='		</li>';
		}
		$txt.='	</ul>';
		$txt.='</div>';		
		//文件路径及文件名
		$load=C('INDEX_TEMP').$areaInfo['identify'].'/'.$groupInfo['com_group'].'/'.$moduleInfo['module_id'].'_'.$moduleInfo['order_sort'].'.html';
		return $this->tempPutContents($load,$txt);			
	}


		
}
