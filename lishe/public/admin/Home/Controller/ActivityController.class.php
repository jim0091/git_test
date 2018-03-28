<?php  
namespace Home\Controller;
class ActivityController extends CommonController {
/*
 * 平台活动管理
 * 2016/8/23
 * zhangrui
 * 
 * */
 	private $allowType = array(1,4,5);//可以添加商品的活动类型显示	类型 1.活动 2.专题 3.礼包 4.团购 5.预售 6.组合购
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dActivity=D('Activity');
		$this->dOrder=D('Order');
		$this->dGoods=D('Goods');
		
	}
	/*
	 * 
	 * 商城首页的礼舍推荐
	 * */
	public function activityIndex(){
		$size=10;
		$number=$this->dActivity->getACount();
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$activityList=$this->dActivity->getAllActivitys($limit);
		$comInfo=$this->dOrder->getAllCompany();
		foreach($activityList as $key=>$value){
			foreach($comInfo as $keys=>$values){
				if($value['com_id']==$values['com_id']){
					$activityList[$key]['com_name']=$values['com_name'];
				}
			}
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this->assign('pagestr',$pagestr);			
		$this->assign('list',$activityList);
		$this->display();
	}
	/*
	 * 活动编辑
	 * */
	public function activityModify(){
		$aid=I('get.aid');
		if($aid){
			$comId=I('get.comId');
			$comName=$this->dOrder->getUserCompany($comId);
			$res=$this->dActivity->getThisInfo($aid);
			if(!empty($res['start_time'])){
				$res['start_time']=date('Y-m-d',$res['start_time']);
			}
			if(!empty($res['end_time'])){
				$res['end_time']=date('Y-m-d',$res['end_time']);
			}	
			$this->assign('info',$res);
			$this->assign('comName',$comName);
			$this->assign('type',I('type'));
			
		}
		//获取所有专题模板
//		$themes=$this->dActivity->getAllThemes();
//		$this->assign('themes',$themes);
		$this->display();
	}
	/*
	 *首页活动保存/添加 
	 * */
	 public function activityModifySave(){
		$aid=I('aid');
	 	$data=I('data');
		if($data['startTime']){
			$data['start_time']=strtotime($data['startTime']);
		}
		if($data['endTime']){
			$data['end_time']=strtotime($data['endTime']." +24 hours");
		}
		if(empty($data['activity_name'])){
			$this->error("请输入专题/活动名称！");
		}
		if(empty($data['type'])){
			$this->error("请选择类型！");
		}
		M()->startTrans();
		try{
			if($aid){
				//保存
				$data['modifyine_time']=time();
				$res=$this->dActivity->saveThisActivity($aid,$data);
				if($res){
					//修改商品表下信息同步修改
					$type=I('type');
					if($type==1){
						$datas['activity_name']=$data['activity_name'];
						$datas['com_id']=$data['com_id'];
						$result=$this->dActivity->editAidActivityItemInfo($aid,$datas);
					}
					//子表修改活动类型
					$map = array(
						'aid' => $aid
					);
					$typeDa = array(
						'type' => $data['type']
					);
					$this->dActivity->editAcategory($map,$typeDa);
				}			
			}else{
				//添加
				$data['creat_time']=time();
				$res=$this->dActivity->addThisActivity($data);
			}			
		}catch(\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);				
		}
		M()->commit();
		if($res){
			$this->success('SUCCESS...');
		}else{
			$this->error('FAIL...');
		}	
		
					
	 }
	 
	/*
	 * 活动列表
	 * */	
	 public function activityList(){
		$aid=I('get.aid');
		if(!$aid){
			exit('参数有误!');
		}
		$map = array(
			'aid' => $aid
		);
		$type = $this->dActivity->getFieldAInfo($map,'type');
		$List=$this->dActivity->getThisActivitys($aid);
		$this->assign('aid',$aid);
		$this->assign('list',$List);
		$this->assign('type',$type);
		$this->assign('allowType',$this->allowType);
		if($type==2){
			//专题
			$this->display('activityList');	 	
		}else{
			//其他活动
			$this->display('activityFloor');	 	
		}
		
	 }
	 /*
	  * 
	  * */
	public function aitemList(){
		$activityConfId=I('get.activityConfId');
		if($activityConfId){
			$List=$this->dActivity->getSomeActivityItem(array('activity_config_id'=>$activityConfId));
			$this->assign('list',$List);
			$aConfInfo=$this->dActivity->getThisActivity($activityConfId,'activity_config_id,aid,type');
			$this->assign('info',$aConfInfo);
		}else{
			echo "no fund!";
		}	
		$this->assign('allowType',$this->allowType);
		$this->display('ActivityItemList');	 	
		
	}  
	/*
	 * 专题编辑、添加
	 * */
	public function activityEdit(){
		$activityId=I('activityId');
		$aid = I('aid');
		if($aid){
			$map['aid'] = $aid;
			$aType = $this->dActivity->getFieldAInfo($map,'type');
		}
		if($activityId){
			//编辑
			$res=$this->dActivity->getThisActivity($activityId);
			if(!empty($res['start_time'])){
				$res['start_time']=date('Y-m-d H:i:s',$res['start_time']);
			}
			if(!empty($res['end_time'])){
				$res['end_time']=date('Y-m-d H:i:s',$res['end_time']);
			}			
			$aType = $res['type'];
			$this->assign('info',$res);
		}else{
			$this->assign('aId',$aid);
		}
		//可以添加商品的活动类型显示
		$this->assign('allowType',$this->allowType);
		$this->assign('type',$aType);
		$this->display();
	}
	/*
	 * 活动编辑后的保存/添加
	 * */
	 public function acticitySave(){
	 	$data=I('data');
		$activityConfigId=I('activityConfigId');
		if(in_array($data['type'], $this->allowType)){
			//需要填活动规则
			if($data['type'] == 4 && empty($data['group_buy_rule'])){
				$this->error('请选择团购成功标准...');
			}
			if($data['type'] == 4 && empty($data['achieve_num'])){
				$this->error('请填写团购成功标准的数量...');
			}	
		}
		if(empty($data['cat_name'])){
			$this->error("名称不能为空...");
		}
		if(empty($data['cat_content'])){
			$this->error("描述不能为空...");
		}		
		$img=$this->uploadImg('img');
		if(!empty($img)){
			$data['cat_banner']=$img;
		}	
		$imgMin=$this->uploadImg('imgMin'); //移动端banner
		if(!empty($imgMin)){
			$data['cat_banner_mobile']=$imgMin;
		}		
		if($data['startTime']){
			$data['start_time']=strtotime($data['startTime']);
		}
		if($data['endTime']){
			$data['end_time']=strtotime($data['endTime']);
		}	
		if(!empty($data['startTime']) && !empty($data['endTime']) && $data['start_time'] >= $data['end_time']){
			$this->error("活动的开始时间不能大于结束时间..");
		}
		M()->startTrans();
		try{
			if($activityConfigId){
				//编辑
				//查找看时间有没有变化
				$changeType = array(7);
				$changeType = array_merge($this->allowType,$changeType);
				if(in_array($data['type'], $changeType)){
					//可添加活动商品的...
					//更改了活动周期 (需要更新sku表的时间周期)
					$map = array(
						'activity_config_id' => $activityConfigId
					);
					$skuInfo = $this->dActivity->getSomeActivityItem($map,'sku_id,parent_sku_id');
					$skuIds = arrGetField($skuInfo, 'sku_id');
					if(!empty($skuIds)){
						$map = array(
							'sku_id' => array('in', $skuIds)
						);
						$skuDa = array(
							'start_time' => $data['start_time'],
							'end_time'   => $data['end_time']
						);							
						$res = $this->dActivity->updateSku($map,$skuDa);						
					}	
				}	
				$data['modifyine_time']=time();
				$res=$this->dActivity->updateThisActivity($activityConfigId,$data);
				if(!$res){
					$this->error("更新活动失败..");
				}			
			}else{
				//添加
				$data['creat_time']=time();
				$data['activity_id']=$data['aid'];
				$res=$this->dActivity->addActivityInfo($data);
				if(!$res){
					$this->error("添加活动失败..");
				}				
			}		
		}catch(\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);				
		}
		M()->commit();			
		$this->success("SUCCESS...");

	 }	
	
	/*
	 * 活动编辑
	 * */
	 public function ActivityItemEdit(){
		$aitemId=I('aitemId');
		$aid=I('aid');
		if($aid){
			$activityInfo=$this->dActivity->getThisActivitys($aid,'activity_config_id,cat_name');
			if(!$activityInfo){
				exit('请先添加活动专区...');
			}			
		}
	 	if($aitemId){
	 		$res=$this->dActivity->getThisActivityItemInfo($aitemId);
			$res['itemInfos']=json_decode($res['item_info'],TRUE);
			foreach($res['itemInfos'] as $key=>$val){
				$itemIds[] = $val['item_id'];
				$skuIds[] = $val['sku_id'];
			}
			if($itemIds){
				$map = array(
					'item_id' => array('in', $itemIds)
				);
				$field = 'item_id,title,image_default_id';
				$itemInfo = $this->dActivity->getItemInfo($map,$field);
				$this->assign('itemInfo', $itemInfo);
			}
			if($skuIds){
				$map = array(
					'sku_id' => array('in', $skuIds)
				);
				$field = 'sku_id,spec_info,price,cost_price';
				$skuInfo = $this->dActivity->getSkuInfo($map,$field);	
				$this->assign('skuInfo', $skuInfo);
			}
			$this->assign('info',$res);
	 	}else{
			$this->assign('aId',$aid);
	 	}
		$this->assign('activityInfo',$activityInfo);
		$this->display();
		
	 }
	/*
	 * 活动保存、添加
	 * */	 
	 public function ActivityItemSaveDeal(){
	 	$data=I('data');
		$itemIds=I('itemId');
		$skuIds=I('skuId');
		$nums=I('num');
		if(empty($data['price'])){
			$this->error("价格不能为空!");
		}
		if(empty($data['shop_price'])){
			$this->error("商城价格不能为空!");
		}		
		if(!empty($itemIds) && !empty($skuIds) && !empty($nums)){
			foreach($itemIds as $k=>$v){
				$itemInfo[$k]['item_id']=intval($v);
			}
			foreach($skuIds as $k=>$v){
				$itemInfo[$k]['sku_id']=intval($v);
			}
			foreach($nums as $k=>$v){
				$itemInfo[$k]['num']=intval($v);
			}
			foreach($itemInfo as $k=>$v){
				if(empty($v['item_id']) || empty($v['sku_id']) || empty($v['num'])){
					unset($itemInfo[$k]);
				}
			}
			if(!empty($itemInfo)){
				$data['item_info']=json_encode($itemInfo);
			}
		}
		if(!empty($data['item_id'])){
			$itemInfo=$this->dGoods->getThisItemInfo(trim($data['item_id']),'title,cat_id,shop_id,price,image_default_id');
			$data['item_name']=$itemInfo['title'];
			$data['item_img']=$itemInfo['image_default_id'];
			$data['shop_price']=$itemInfo['price'];
			$data['market_price']=$itemInfo['price'];
		}
		if(empty($data['post_fee'])){
			$data['post_fee']=0;
		}
	 	$aitemId=I('aitemId');
		$img=$this->uploadImg('img');
		if(!empty($img)){
			$data['item_img']=$img;
		}
		M()->startTrans();
		try{
		 	if($aitemId){
		 		//修改
		 		$res=$this->dActivity->editThisActivityItemInfo($aitemId,$data);
		 	}else{
		 		//添加
		 		$data['aid']=I('aId');
				$activityInfo=$this->dActivity->getThisInfo($data['aid']);
				$data['activity_name']=$activityInfo['activity_name'];
				$data['com_id']=$activityInfo['com_id'];
				$res=$this->dActivity->addActivityItemInfo($data);
		 	}
		}catch(\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);			
		}	
		M()->commit();
		if($res){
			$this->success('SUCCESS...');
		}else{
			$this->error('FAIL...');
		}		
		
	 }
	/*
	 * 
	 * 企业配置管理首页
	 * */
	public function companySetting(){
		$keyword = I('get.keyword','','trim,urldecode');
		$where = array();
		if (!empty($keyword)) {
			$_GET['keyword'] = $keyword; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['com_name'] = array('like',"%{$keyword}%");
		}
		
		$size=10;
		$number=$this->dActivity->getCompanyCount($where);
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";
		$companyList=$this->dActivity->getCompany($where, $limit);
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('keyword',$keyword);
		$this -> assign('number',$number);
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$companyList);		
		
		$this->display();
	}
/**
 * 企业详情页
 * */	
	public function companyDetail(){
		$confId=I('confid');
		if($confId){
			$companyInfo=$this->dActivity->getThisCompanyInfo($confId);
			$this->assign('info',$companyInfo);
		}
		$this->display();
	}
	
/*
 * 企业专区列表
 * */
 	public function ItemConfig(){
 		$comId=I('comId');
		$this->assign('comId',$comId);
		if($comId){
			$list=$this->dActivity->getThisItemCompany($comId);
			$this->assign('empty','<strong style="color:#5EB95E;font-size:20px;">该公司没有配置活动/商品专区</strong>');	
			$this->assign('list',$list);		
	 		$this->display();
		}
 	}
/*
 * 企业专区设置、编辑
 * */
 	public function ItemConfigEdit(){
		$itemConfId=I('itemConfId');
		if($itemConfId){
			$res=$this->dActivity->getThisItemConf($itemConfId);
			$this->assign('info',$res);	
		}
		$comId=I('comId');
		if($comId){
			$this->assign('comId',$comId);
		}
		$this->display();
 	}
/*
 * 图片上传
 * */	
	public function uploadImg($fileName,$route="banner"){
		if(!empty($_FILES[$fileName]['tmp_name'])){
		    $upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   =     3145728 ;// 设置附件上传大小
		    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath  =      './Upload/'.$route.'/'; // 设置附件上传根目录
		    // 上传单个文件 
		    $info   =   $upload->uploadOne($_FILES[$fileName]);
		    if(!$info) {// 上传错误提示错误信息
		        $this->error($upload->getError());
		    }else{// 上传成功 获取上传文件信息
		        return C('TMPL_PARSE_STRING.__LISHE__').'/Upload/'.$route.'/'.$info['savepath'].$info['savename'];
		    }			
		}else{
			return NULL;
		}		
	}
/*
 * 保存/添加企业专区修改
 * */	
	public function itemConfigSave(){
		$itemConfId=I('itemConfId');
		$data=I('data');
		$banner=$this->uploadImg('banner');
		if(!empty($banner)){
			$data['cat_banner']=$banner;
		}
		$icon=$this->uploadImg('icon');
		if(!empty($icon)){
			$data['cat_icon']=$icon;
		}		
		if($itemConfId){
			//保存
			$data['modifyine_time']=time();
			$res=$this->dActivity->editThisItemConf($itemConfId,$data);
			if($res){
				$this->redirect('Activity/ItemConfigEdit', array('itemConfId' => $itemConfId), 1, '保存成功,页面跳转中...');
			}else{
				$this->redirect('Activity/ItemConfigEdit', array('itemConfId' => $itemConfId), 1, '请修改内容,再保存页面跳转中...');
			}				
		}else{
			//添加
			$data['creat_time']=time();
			$data['com_id']=I('comId');
			if(!empty($data['com_id'])){
				$res=$this->dActivity->addItemConf($data);
				if($res){
					$this->redirect('Activity/ItemConfigEdit', array('itemConfId' => $res), 1, '添加成功,页面跳转中...');
				}else{
					$this->redirect('Activity/ItemConfigEdit', array('comId'=>$data['com_id']), 1, '添加失败请重试,页面跳转中...');
				}						
			}
			
		}			
	}
/*
 *删除专区配置
 * */
 	public function dealitemConfig(){
 		$itemConfId=I('get.itemConfId');
		if($itemConfId){
			$res=$this->dActivity->delItemConf($itemConfId);
			echo json_encode($res);			
		}
 	}
/*
 * 分类配置列表
 * */
	public function categoryConfig(){
		$itemConfId=I('itemConfId');
		$this->assign('itemConfId',$itemConfId);
		$this->assign('comId',I('comId'));
		if($itemConfId){
			$list=$this->dActivity->getThisCategoryInfo($itemConfId);
			$this->assign('list',$list);	
			$this->assign('empty','<strong style="color:#5EB95E;font-size:20px;">该专区下没有分类</strong>');	
			$this->display();
		}
	}
/*
 * 指定分类配置
 * */
	public function categoryConfigEdit(){
		$catConfId=I('catConfId');
		$itemConfId = I('itemConfId');
		$comId = I('comId');
		if($catConfId){
			$res=$this->dActivity->getThisCategoryConf($catConfId);
			$this->assign('info',$res);	
		}
		if($itemConfId && $comId){
			$this->assign('itemConfId',$itemConfId);
			$this->assign('comId',$comId);
		}		
		$this->display();
	}
/*
 * 保存/添加分类配置
 * */	
	
	public function categoryConfigSave(){
		$catConfId=I('catConfId');
		$data=I('data');
		if($catConfId){
			//保存
			$res=$this->dActivity->editThisCatgory($catConfId,$data);
		}else{
			//添加
			if(!empty($data['item_config_id']) && !empty($data['com_id'])){
				$res=$this->dActivity->addCatgory($data);
			}
		}
		if($res){
			$this->success('Success');
		}else{
			$this->error('Fail');
		}					
	}
/*
 *删除分类配置 
 * */
 	public function dealCategoryConfig(){
 		$catConfId=I('get.catConfId');
		if($catConfId){
			$res=$this->dActivity->delCategory($catConfId);
			echo json_encode($res);			
		}
 	}
 	
 	//计算满返积分
 	public function saveActivityReturn(){
 		$comId=I('get.comId');//企业ID
 		$startDate=I('get.startDate');//开始日期
 		$endDate=I('get.endDate');//结束日期
 		$startFee=I('get.startFee');//订单金额
		if(!empty($comId) && !empty($startDate) && !empty($endDate) && !empty($startFee)){
			$res=$this->dActivity->getActivityReturn($comId,$startDate,$endDate,$startFee);
			echo json_encode($res);			
		}
 	}
 	//添加模板
 	public function addThemes(){
 		
		$this->display();
 	}
 	//添加模板保存
 	public function saveAddThemes(){
 		$data=I('data');
		$data['filename']=trim($data['filename']);
		if(empty($data['filename'])){
			$this->error('请输入模板文件名称!');
		}
		$haveFileName=$this->dActivity->getFieldThemes('filename');
		if(in_array($data['filename'], $haveFileName)){
			$this->error('该模板已存在!');
		}
		$img=$this->uploadImg('preview','images');
		if(!empty($img)){
			$data['preview']=$img;
		}		
		$data['theme']='activity';
		$res=$this->dActivity->addThemes($data);
		if($res){
			$this->success("添加成功！");			
		}else{
			$this->error("添加失败！");			
		}
 	} 	
/**
 * sku信息
 */ 
 	public function getSkuInfo(){
 		$skuId = I('skuId');
		if(!$skuId){
			$this->ajaxReturn(0);	
		}
		$map = array(
			'sku_id' => $skuId,
			'parent_sku_id' => 0
		);
		$skuInfo = M('sysitem_sku')->where($map)->field('item_id,sku_id,spec_info,price,cost_price')->find();
		//商品图片
		if(!$skuInfo){
			$this->ajaxReturn(0);	
		}
		$map = array(
			'item_id' => $skuInfo['item_id']
		);
		$itemInfo = M('sysitem_item')->where($map)->field('title,image_default_id')->find();
		$skuInfo['title'] = $itemInfo['title'];
		$skuInfo['spec_info'] = empty($skuInfo['spec_info']) ? '--':$skuInfo['spec_info'];
		$skuInfo['image_default_id'] = $itemInfo['image_default_id'].'_m.'.end(explode('.', $itemInfo['image_default_id']));
		$this->ajaxReturn($skuInfo);	
		
 	}
/**
 *取得sku信息及库存 
 */
 	public function getSkuCon(){
 		$skuId = I('skuId');
		if(!$skuId){
			$this->ajaxReturn(0);	
		}
		$map = array(
			'sku_id' => $skuId,
		);
		$skuInfo = M('sysitem_sku')->where($map)->field('item_id,sku_id,spec_info,price,cost_price')->find();
		//商品图片
		if(!$skuInfo){
			$this->ajaxReturn(0);	
		}
		//库存
		$map = array(
			'sku_id' => $skuId,
		);		
		$store= M('sysitem_sku_store')->where($map)->find();
		$skuInfo = array_merge($skuInfo,$store);
		$map = array(
			'item_id' => $skuInfo['item_id']
		);
		$itemInfo = M('sysitem_item')->where($map)->field('title,image_default_id')->find();
		$skuInfo['title'] = $itemInfo['title'];
		$skuInfo['image_default_id'] = $itemInfo['image_default_id'].'_m.'.end(explode('.', $itemInfo['image_default_id']));
		$this->ajaxReturn($skuInfo); 		
 	}
	/**
	 * 添加/编辑活动商品
	 */
	public function updateAItem(){
		$activityId = I('activityId');
		$aid = I('aid');
		$aitemId = I('aitemId');
		if($aitemId){
			$map = array(
				'aitem_id' => $aitemId
			);
			$info = M('company_activity_item')->where($map)->field('aitem_id,parent_sku_id,price,store')->find();
			if(!$info){
				exit('无法获取活动商品信息!');
			}
			$this->assign('info',$info);
		}
		$this->assign('activityId',$activityId);
		$this->assign('aid',$aid);
		$this->display();
	}
	/**
	 * 添加活动商品
	 */
	public function updateAItemDeal(){
		$data = I('data');
		$aitemId = I('aitemId');  //作更新用
		if(empty($data['aid']) && empty($aitemId)){
			$this->error('无法获取活动类型!');
		}
		if(empty($data['activity_config_id'])  && empty($aitemId)){
			$this->error('无法获取活动场次!');
		}
		if(empty($data['parent_sku_id'])){
			$this->error('请输入skuid校验!');
		}
		if(empty($data['price'])){
			$this->error('请输入该商品参与活动的进货价!');
		}		
		if(empty($data['price'])){
			$this->error('请输入该商品参与活动的售价!');
		}
		if(empty($data['store'])){
			$this->error('请输入该商品参与活动的预备库存!');
		}
		$skuStore = M('sysitem_sku_store');
		M()->startTrans();
		try{
			//sku信息
			$map = array(
				'sku_id' => $data['parent_sku_id'],
				'parent_sku_id' => 0
			);
			$sysitemSku = M('sysitem_sku');
			$skuInfo = $sysitemSku->where($map)->find();	
			$shopPrice = $skuInfo['price'];
			if(empty($skuInfo)){
				$this->error('无法获取该sku信息!');
			}
			if($data['price'] > $skuInfo['price']){
				$this->error('商品参与活动的售价不能大于商品的原价!');
			}	
			//判断库存
			$map = array(
				'sku_id' => $data['parent_sku_id'],
			);		
			$store= $skuStore->where($map)->find();
			if($data['store'] > ($store['store'] - $store['freez'])){
				$this->error('商品参与活动的预备库存不能大于商品的原始库存!');
			}			
			//取出活动类型
			$map = array(
				'aid' => $data['aid']
			);
			$skuInfo['type'] = M('company_activity')->where($map)->getField('type');
			$Period = M('company_activity_category')->where($map)->field('start_time,end_time')->find(); //活动范围
			$skuInfo['start_time'] = $Period['start_time'];
			$skuInfo['end_time'] = $Period['end_time'];
			$skuInfo['parent_sku_id'] = $skuInfo['sku_id'];
			$skuInfo['price'] = $data['price'];
			$skuInfo['point'] = $data['price']*100;
			$skuInfo['cost_price'] = $data['cost_price'];
			$skuInfo['created_time'] = time();
			$skuInfo['sold_quantity'] = 0;
			$skuInfo['cash'] = 0;
			$skuInfo['spec_info'] = trim($data['spec_info']);
			$skuInfo['activity_config_id'] = $data['activity_config_id'];
			//添加sku
			unset($skuInfo['sku_id']);
			$data['sku_id'] = $sysitemSku->data($skuInfo)->add();	
			if(!$data['sku_id']){
				M()->rollback();
				$this->error("添加sku失败..");
			}
			//新增新sku库存信息
			$storeInfo = array(
				'item_id' => $skuInfo['item_id'],
				'sku_id'  => $data['sku_id'],
				'store'   => $data['store']
			);
			$addSkuStore = $skuStore->data($storeInfo)->add();
			if(!$addSkuStore){
				M()->rollback();
				$this->error("添加sku库存失败..");						
			}
			//原sku减相应库存
			$map = array(
				'sku_id' => $data['parent_sku_id'],
			);			
			$decSkuStore = $skuStore->where($map)->setDec('store', $data['store']);
			if(!$decSkuStore){
				M()->rollback();
				$this->error("原sku库存减失败..");				
			}
			//添加活动商品表
			  //商品的基本信息
			$map = array(
			 	'item_id' => $skuInfo['item_id']
			); 
			$itemInfo = M('sysitem_item')->where($map)->field('item_id,title,image_default_id')->find();  
			$data['item_name']    = $itemInfo['title'];
			$data['item_img']     = $itemInfo['image_default_id'].'_m.'.end(explode('.', $itemInfo['image_default_id']));
			$data['item_id']      = $itemInfo['item_id'];
			$data['shop_price']   = $shopPrice;
			$data['market_price'] = $shopPrice;
			$data['cost_price']   = $skuInfo['cost_price'];
			$data['weight']       = $skuInfo['weight'];
			$res = M('company_activity_item')->data($data)->add();
			if(!$res){
				M()->rollback();
				$this->error("添加活动商品失败..");				
			}
			M()->commit();
			$this->success('SUCCESS...');			
		}catch(\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);				
		}	
			
	} 
/**
 * 组合购添加编辑页
 */
	public function combinateEdit(){
		$aitemId=I('aitemId');
		$aid=I('aid');
		if($aid){
			$map = array(
				'aid' => $aid
			);
			$activityInfo = M('company_activity_category')->where($map)->field('activity_config_id,cat_name')->select();
			if(!$activityInfo){
				exit('请先添加活动专区...');
			}
		}
	 	if($aitemId){
			$map = array(
				'aitem_id' => $aitemId
			);
			$res = M('company_activity_item')->where($map)->find();
			$res['itemInfos']=json_decode($res['item_info'],TRUE);
			foreach($res['itemInfos'] as $key=>$val){
				$skuIds[] = $val['sku_id'];
			}
			if($skuIds){
				$map = array(
					'sku_id' => array('in', $skuIds)
				);
				$field = 'sku_id,item_id,spec_info,price,cost_price';
				$skuInfo = M('sysitem_sku')->where($map)->getField($field);
				$this->assign('skuInfo', $skuInfo);
				$itemIds = arrGetField($skuInfo, 'item_id');
				if($itemIds){
					$map = array(
						'item_id' => array('in', $itemIds)
					);
					$field = 'item_id,title,image_default_id';
					$itemInfo = M('sysitem_item')->where($map)->getField($field);
					$this->assign('itemInfo', $itemInfo);
				}
			}
			$this->assign('info',$res);
	 	}else{
			$this->assign('aId',$aid);
	 	}
		$this->assign('activityInfo',$activityInfo);
		$this->display();
	}
/**
 * 组合购保存
 */	
	public function combinateSave(){
	 	$data = I('data');
		$skuIds = I('skuId');
		$costPrice = I('costPrice');
		$prices = I('price');
	 	$aitemId = I('aitemId');
		$aid = I('aId');
		if(empty($data['item_name'])){
			$this->error("请设置组合名称!");
		}
		if(empty($data['activity_config_id'])){
			$this->error("请选择商品活动专区!");
		}
		if(empty($skuIds) || empty($prices)){
			$this->error("请设置组合商品!");
		}	
		if(empty($data['store'])){
			$this->error("请设置组合商品的库存!");
		}	
		//过滤组合购商品信息不完整的
		foreach($skuIds as $key=>$val){
			if(empty($val) || empty($prices[$key]) || empty($costPrice[$key])){
				unset($skuIds[$key]);				
				unset($prices[$key]);				
				unset($costPrice[$key]);				
			}
		}
		$setPrice = array_combine($skuIds, $prices);
		$setCostPrice = array_combine($skuIds, $costPrice);
		//进价判断
		$costArr = array_combine($costPrice, $prices);
		//sku信息
		M()->startTrans();
		try{
			$img = $this->uploadImg('img');
			if(!empty($img)){
				$data['item_img']=$img;
			}	
			$activityItemModel = M('company_activity_item');
			if($aitemId){
				//编辑
				//先不让更改库存和商品
				unset($data['store']);
				$map = array(
					'aitem_id' => $aitemId
				);
				$res = $activityItemModel->where($map)->save($data);
				if(!$res){
					M()->rollback();
					$this->error("更新失败...");						
				}
			}else{
				//添加
				//sku库存
				$modelStore = M('sysitem_sku_store');
				$map = array(
					'sku_id' => array('in', $skuIds),
					'parent_sku_id' => 0
				);			
				$minStore = $modelStore->where($map)->min('store-freez');
				if($data['store'] > $minStore){
					$this->error('库存值不能大于组合商品中最少有效库存的值...');
				}
				$modelSku = M('sysitem_sku');
				$skuInfos = $modelSku->where($map)->select();
				if(!$skuInfos){
					$this->error('无法获取商品信息...');
				}
				//价格判断
				foreach($skuInfos as $key=>$val){
					if($setPrice[$val['sku_id']] > $val){
						$this->error('商品的组合单品价格不能大于商品原价！');
					}
					
				}
				$data['shop_price'] = array_sum(arrGetField($skuInfos, 'price'));
				$data['market_price'] = $data['shop_price'];
				$data['price'] = array_sum($prices);
				$data['cost_price'] = array_sum($costPrice);
				$data['aid'] = $aid;
				$data['sku_id'] = current($skuIds);				
				//添加新sku信息
				//添加sku
				$newSkuInfos = $skuInfos;
				$map = array(
					'activity_config_id' => $data['activity_config_id']
				);
				$Period = M('company_activity_category')->where($map)->field('start_time,end_time,type')->find(); //活动范围
				foreach($newSkuInfos as $key=>$val){
					unset($newSkuInfos[$key]['sku_id']);
					$newSkuInfos[$key]['start_time'] = $Period['start_time'];
					$newSkuInfos[$key]['end_time'] = $Period['end_time'];
					$newSkuInfos[$key]['parent_sku_id'] = $val['sku_id'];
					$newSkuInfos[$key]['price'] = $setPrice[$val['sku_id']];
					$newSkuInfos[$key]['point'] = $setPrice[$val['sku_id']]*100;
					$newSkuInfos[$key]['cost_price'] = $setCostPrice[$val['sku_id']];
					$newSkuInfos[$key]['created_time'] = time();
					$newSkuInfos[$key]['sold_quantity'] = 0;
					$newSkuInfos[$key]['cash'] = 0;
					$newSkuInfos[$key]['type'] = $Period['type'];
					$newSkuInfos[$key]['activity_config_id'] = $data['activity_config_id'];
				}
				$newSkuIds = array();
				foreach($newSkuInfos as $key=>$val){
					$newSkuIds[$val['parent_sku_id']] = $modelSku->data($val)->add();
					if(!$newSkuIds[$val['parent_sku_id']]){
						M()->rollback();
						$this->error("sku:{$val['parent_sku_id']}添加失败...");
					}
					//新增新sku库存信息
					$storeInfo = array(
						'item_id' => $val['item_id'],
						'sku_id'  => $newSkuIds[$val['parent_sku_id']],
						'store'   => $data['store']
					);
					$addSkuStore = $modelStore->data($storeInfo)->add();
					if(!$addSkuStore){
						M()->rollback();
						$this->error("sku:{$val['parent_sku_id']}添加sku库存失败..");						
					}
				}
				//添加活动商品表
				$itemInfo = array();
				foreach($skuInfos as $key=>$val){
					$itemInfo[$key]['sku_id'] = $newSkuIds[$val['sku_id']];
					$itemInfo[$key]['price'] = $setPrice[$val['sku_id']];
					$itemInfo[$key]['cost_price'] = $setCostPrice[$val['sku_id']];
					$itemInfo[$key]['parent_sku_id'] = $val['sku_id'];
				}		
				if(!empty($itemInfo)){
					$data['item_info']=json_encode($itemInfo);
				}
				$aitemId = $activityItemModel->data($data)->add();
				if(!$aitemId){
					M()->rollback();
					$this->error("添加活动商品失败...");			
				}
				//$aitemId写进sku
				$map = array(
					'sku_id' => array('in', $newSkuIds)
				);
				$updSku = $modelSku->where($map)->setField('aitem_id', $aitemId);
				if(!$updSku){
					M()->rollback();
					$this->error("sku更新商品aitem_id失败...");				
				}
				//加减库存
				//原sku减相应库存
				$map = array(
					'sku_id' => array('in', $skuIds),
				);			
				$decSkuStore = $modelStore->where($map)->setDec('store', $data['store']);
				if(!$decSkuStore){
					M()->rollback();
					$this->error("原sku库存减失败..");				
				}				
			}

		}catch(\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);			
		}		
		M()->commit();
		$this->success('SUCCUESS...');
	}
/**
 * 删除活动商品
 */
	public function delaitem(){
		$aitemId= I('aitemId');
		$ret = array('code' => 0, 'msg' => 'Unknow');
		if(!$aitemId || !is_numeric($aitemId)){
			$ret['msg'] = '活动id有误!';
			$this->ajaxReturn($ret);
		}
		$modelAItem = M('company_activity_item');
		//查找该订单sku信息
		$map = array(
			'aitem_id' => $aitemId
		);
		$aItemInfo = $modelAItem->where($map)->field('sku_id,parent_sku_id,item_info')->find();
		//活动商品包里存在的sku
		if(!empty($aItemInfo['item_info'])){
			$skuIds = array();
			$itemInfo = json_decode($aItemInfo['item_info'], TRUE);
			foreach($itemInfo as $key=>$val){
				$skuIds[$val['parent_sku_id']] = $val['sku_id'];
			}
		}else{
			$skuIds = array();
			$skuIds[$aItemInfo['parent_sku_id']] = $aItemInfo['sku_id'];
		}
		if(empty($skuIds)){
			$ret['msg'] = '无法获取skuid!';
			$this->ajaxReturn($ret);	
		}
		//查看sku是否有下订单
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$tid = M('systrade_order')->where($map)->getField('tid');
		M()->startTrans();
		//查看购物车是否存在
		$catId = M('systrade_cart')->where($map)->getField('cart_id');
		if($catId || $tid){
			//该活动商品已有用户加入购物车或已下单
			$res = M('sysitem_sku')->where($map)->setField('disable',1);
		}else{
			//删除sku表对应sku数据
			$res = M('sysitem_sku')->where($map)->delete();
		}
		if(!$res){
			M()->rollback();
			$ret['msg'] = 'sku数据删除失败!';
			$this->ajaxReturn($ret);			
		}
		$map = array(
			'aitem_id' => $aitemId
		);	
		$res = $modelAItem->where($map)->delete();
		if(!$res){
			M()->rollback();
			$ret['msg'] = '活动订单表删除失败!';
			$this->ajaxReturn($ret);			
		}			
		$modelSkuStore = M('sysitem_sku_store');
		//查找sku对应库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);		
		$skuStore = $modelSkuStore->where($map)->getField('sku_id,store');
		if(empty($catId) && empty($tid)){
			//无加购物车和订单时
			$res = $modelSkuStore->where($map)->delete();
		}else{
			$storeInfo = array(
				'store' => 0,
				'freez' => 0
			);
			$res = $modelSkuStore->where($map)->save($storeInfo);
		}	
		//原sku加上相应库存	
		foreach($skuIds as $key=>$val){
			$map = array(
				'sku_id' => $key
			);
			$modelSkuStore->where($map)->setInc('store', $skuStore[$val]);
		}	
		M()->commit();
		$ret['code'] = 1;
		$ret['msg'] = '删除成功!';
		$this->ajaxReturn($ret);			
	}
	
}