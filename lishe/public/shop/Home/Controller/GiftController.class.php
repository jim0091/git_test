<?php

namespace Home\Controller;

class GiftController extends CommonController {
	
	private $activityCategory;
	private $activityItem;
	private $orderRecever;
	
	public function __construct() {
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__APP__."/Sign/index");
			exit;
		}
		$this->activityCategory = M('company_activity_category');
		$this->activityItem = M('company_activity_item');
		$this->orderRecever = M('gift_group_order_recever');
	}
	
	//
	public function rececer(){
		if(IS_AJAX){
			$ret = array('code'=>-1,'msg'=>'unkown error');
			$orderId = I('post.orderId',-1,'intval');
			$aitemId = I('post.aitemId',-1,'intval');
			
			if(!is_numeric($orderId) || $orderId < 1){
				$ret['msg'] = 'invalid orderId';
				$this->ajaxReturn($ret);
			}
			if(!is_numeric($aitemId) || $aitemId < 1){
				$ret['msg'] = 'invalid aitemId';
				$this->ajaxReturn($ret);
			}
			$param = array();
			$param['orderId'] = $orderId;
			$param['aItemId'] = $aitemId;
			$param['userId'] = $this->uid;
// 			$param['orderId'] = 1001;
// 			$param['aItemId'] = 117;
// 			$param['userId'] = 613;
			$param['sign'] = apiSign($param);
			$url = C('LISHE_URL').'/bag.php/Api/groupOrderRec';
			$result = $this->requestPost($url, $param);
			$resultArr = json_decode($result, true);
			if(isset($resultArr['errcode']) && empty($resultArr['errcode'])){
				$ret['code'] = 1;
				$ret['msg'] = 'success';
			}else{
				$ret['msg'] = '领取失败';
			}
			$this->ajaxReturn($ret);
		}else{
			header("Content-type:text/html;charset=utf-8");
			$recid = I('get.recid',-1,'intval');
			if(!is_numeric($recid) || $recid < 1){
				exit();
			}
			$where = array();
			$where['recever_id'] = $recid;
			$recever = $this->orderRecever->field('order_id,user_id,activity_config_id,delivery_time,receive_status')->where($where)->find();
			if($this->uid != $recever['user_id']){
				echo '非法用户';
				exit();
			}
			if($recever['receive_status'] != 0){
				echo '该礼包已被领取';
				exit();
			}
			$todayDate = date('Y-m-d');
			$expireTime = date('Y-m-d',strtotime($recever['delivery_time'].' -1 day'));
			if($expireTime < $todayDate){
				echo '该礼包已过期';
				exit();
			}
			$orderId = $recever['order_id'];
			$where = array();
			$where['activity_config_id'] = $recever['activity_config_id'];
			$itemList = $this->activityItem = $this->activityItem->where($where)->select();
			
			$catName = $this->activityCategory->where($where)->getField('cat_name');
			
			$this->assign('catName', $catName);
			$this->assign('orderId', $orderId);
			$this->assign('itemList', $itemList);
			$this->display('orderRec');
		}
	}
}