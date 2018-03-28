<?php
namespace Home\Controller;
class ActivityController extends CommonController {
	public function __construct() {
		parent::__construct();
		$this->modelActivityConfig=M('company_activity_config');
		$this->modelConfig=M('company_gaway_config');
		$this->modelItem=M('sysitem_item');
		
		$this->assign('public',C('ROOT').'Show/Home/View/Activity/');
		$this->assign('index',__APP__.'/Activity/moon');
	}
	public function activity($aid){
		if(empty($aid)){
			$aid=1;
		}
		$activityConfig=$this->modelActivityConfig->where('activity_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->select();
		foreach($activityConfig as $key=>$value){
			$activity[$value['activity_config_id']]=array(
				'id'=>$value['activity_config_id'],
				'name'=>$value['cat_name'],
				'banner'=>$value['cat_banner'],					
				'content'=>$value['cat_content'],
				'item_ids'=>$value['item_ids'],
				'more_link'=>$value['more_link']
			);
			if(!empty($value['recommend'])){
				$condition='i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';	
				$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price')->where($condition)->limit(10)->select();
			}
		}		
		return array('activity'=>$activity,'list'=>$itemList);
	}
		
	//专题活动页面
	public function index(){
		$aid=intval($_GET['aid']);
		$activity=$this->activity($aid);
		
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->display();
	}
	
	public function fifth(){
		$res = C('ACTIVITY');
		foreach($res as $k=>$v){
			foreach($v['item'] as $key => $value){
				$itemId[$key] = $key;
			}
		}
		$status = M('sysitem_item_status');
		$condition['item_id'] = array('in', $itemId);
		$result = $status->where($condition)->field('item_id,approve_status')->select();
		foreach($result as $key => $value){
			if($value['approve_status'] != "onsale"){
				$resId[] = $value['item_id'];
			}
		}
		foreach($res as $key => $value){
			foreach($value['item'] as $keys => $values){
				if(in_array($keys, $resId)){
					unset($res[$key]['item'][$keys]);
				}
			}
		}

		foreach($res as $key => $value){
				if(count($value['item']) > 10){
					$res[$key]['item']=array_slice($value['item'],0,10,true);
					
				}
			
		}

		// 20160712  start
	   $configDis = $this->modelConfig->field('id,send_type,flag')->select();
	   $this-> assign('configDis',$configDis);
	   // 20160712 end
		$this->assign('item',$res);
		$this->display('Activity/fifth/index');
	}


 	public function moon(){
		$activity=$this->activity(3);	
		$first=array(
			'activity' => current($activity['activity']),
			'list'     => current($activity['list'])
		);
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->assign('first',$first);
		$this->display('Activity/moon/index');
	}
	//更多
	public function itemList(){
		$activityId=I('activityId');
		if($activityId){
			$activityConfig=$this->modelActivityConfig->where('activity_config_id='.$activityId)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->find();
			$activity=array(
				'id'=>$activityConfig['activity_config_id'],
				'name'=>$activityConfig['cat_name'],
				'banner'=>$activityConfig['cat_banner'],					
				'content'=>$activityConfig['cat_content'],
				'item_ids'=>$activityConfig['item_ids'],
				'more_link'=>$activityConfig['more_link']
			);
			if(!empty($activityConfig['item_ids'])){
				$size=50;
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
				
				$itemIdArr=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->order('i.cat_id DESC,i.profit_rate DESC')->select();
				foreach($itemIdArr as $key=>$value){
					$itemId[]=$value['item_id'];
				}
				$itemId=array_slice($itemId,$start,$pagesize);
				$condition='item_id IN('.implode(',',$itemId).')';
				$itemList=$this->modelItem->field('item_id,title,image_default_id,price,mkt_price')->where($condition)->select();
				$style = "pageos";
				$onclass = "pageon";
				$pagestr = $page -> show($style,$onclass); 
				$this -> assign('pagestr',$pagestr);					
			}
			$first=array('activity'=>$activity,'list'=>$itemList);		
			$this->assign('first',$first);
			$this->display('Activity/moon/itemList');
		}
	}
	
	
}