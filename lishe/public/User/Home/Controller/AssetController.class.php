<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 个人资产
 * @author LT
 *
 */
class AssetController extends CommonController {
	
	private $giftGroupOrderRecever;
	private $activityCategory;
	private $companyConfig;
	
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__SHOP__."/Sign/index");
			exit;
		}
		$this->giftGroupOrderRecever = M('gift_group_order_recever');
		$this->activityCategory = M('company_activity_category');
		$this->companyConfig = M('company_config');
	}
	
	//空操作
	public function _empty(){
		echo 'empty request';
	}
	
    //企业权益
    public function entwel(){
    	if(!is_numeric($this->comId) || $this->comId < 1){
    		echo 'error comid';
    		exit();
    	}
    	//查询我的礼包
    	$where = array();
    	$where['user_id'] = $this->uid;
    	//$where['user_id'] = 613;///////////////////////////////////////////////////////测试数据
    	$where['receive_status'] = 0;
    	$activityList = $this->giftGroupOrderRecever->field('recever_id, activity_config_id, delivery_time')->where($where)->select();
    	
    	$activityIdArr = array();
    	$todayDate = date('Y-m-d');
    	foreach ($activityList as &$activity){
    		$activityIdArr[] = $activity['activity_config_id'];
    		$expireTime = date('Y-m-d',strtotime($activity['delivery_time'].' -1 day'));
    		$activity['expire_time'] = $expireTime;
    		if($expireTime < $todayDate){
    			$activity['is_expire'] = 1;
    		}else{
    			$activity['is_expire'] = 0;
    		}
    	}
    	if(empty($activityIdArr)){
    		$this->display('Order/emptyHint');
    		exit();
    	}
    	$where = array();
    	$where['activity_config_id'] = array('in',$activityIdArr); 
    	$assetList = $this->activityCategory->field('activity_config_id,cat_name')->where($where)->select();
    	$assetArr = array();
    	foreach ($assetList as $val){
    		$assetArr[$val['activity_config_id']] = $val['cat_name'];
    	}
    	
    	//查询公司信息
    	$where = array();
    	$where['com_id'] = $this->comId;
    	$comName = $this->companyConfig->where($where)->getField('com_name');
    	
    	$this->assign('comName', $comName);
    	$this->assign('activityList', $activityList);
    	$this->assign('assetArr', $assetArr);
    	$this->display('entwel');
    }
}