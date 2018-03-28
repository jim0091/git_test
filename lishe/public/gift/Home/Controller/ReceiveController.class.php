<?php
/**
 +------------------------------------------------------------------------------
 * ReceiveController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: ReceiveController.class.php v001 2016-11-01
 * @description 领取控制器
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class ReceiveController extends Controller {
   
	public function __construct(){
		parent::__construct();
		$this->giftTrade = M('gift_trade');
		$this->giftPost = M('gift_post');
		$this->sysTrade = M('systrade_trade');
		$this->systrade_order = M('systrade_order');
		$this->siteArea = M('site_area');
		$this->giftReceiverAddr = M('gift_receiver_addr');
	}
	
	//打开礼物
	public function open(){
		header("Content-type:text/html;charset=utf-8");
		$flag = I('get.flag','','trim');
		$flagArr = $this->parseFlag($flag);
		if(empty($flagArr)){
			echo "<script type='text/javascript'>alert('错误链接');</script>";
			exit();
		}
		$tid = $flagArr['tid'];
		$postid = $flagArr['postid'];
		$where['post_id'] =  $postid;
		$giftPost = $this->giftPost->where($where)->field('post_id,card_url,post_status')->find();
		if(empty($giftPost)){
			echo "<script type='text/javascript'>alert('错误链接');</script>";
			exit();
		}
		if($giftPost['post_status'] != 0 && $giftPost['post_status'] != 1){
			echo "<script type='text/javascript'>alert('该礼物已被领取！');</script>";
			exit();
		}
		//设置session，有效会话标志
		session('postflagArr', $flagArr);
		$this->assign('giftPicUrl',$giftPost['card_url']);
		$this->display('open');
		//$this->redirect(U(''));
	}
	
	//填写个人信息
	public function contactForm(){
		header("Content-type:text/html;charset=utf-8");
		$flagArr = session('postflagArr');
		$tid = $flagArr['tid'];
		$postid = $flagArr['postid'];
		
		if(!is_numeric($tid) || !is_numeric($postid)){
			echo "<script type='text/javascript'>alert('链接有误！');</script>";
			exit();
		}
		//加载一级地区
		$areaList = $this->siteArea->where('level=1')->select();
		$this->assign('areaList',$areaList);
		$this->display('contactForm');
	}
	
	//收货人信息
	public function saveContact(){
		if(!IS_POST) exit();
		header("Content-type:text/html;charset=utf-8");
		$receiverName = I('post.receiver_name','','trim,strip_tags,stripslashes');
		$receiverPhone = I('post.receiver_phone','','trim,strip_tags,stripslashes');
		$receiverState = I('post.receiver_state',-1,'intval');
		$receiverCity = I('post.receiver_city',-1,'intval');
		$receiverDistrict = I('post.receiver_district',-1,'intval');
		$receiverAddr = I('post.receiver_addr','','trim,strip_tags,stripslashes');
		
		//校验数据
		$flagArr = session('postflagArr');
		$tid = $flagArr['tid'];
		$postid = $flagArr['postid'];
		if(!is_numeric($tid) || !is_numeric($postid)){
			echo "<script type='text/javascript'>alert('非法数据！');</script>";
			exit();
		}
		//校验
		if(mb_strlen($receiverName,'utf8') < 2){
			echo "<script type='text/javascript'>alert('姓名有误！');history.go(-1);</script>";
			exit();
		}
		if(!preg_match('/^1\d{10}$/', $receiverPhone)){
			echo "<script type='text/javascript'>alert('手机号有误！');history.go(-1);</script>";
			exit();
		}
		//验证地区
		//$receiverState = I('post.receiver_state',-1,'intval');
		if(!is_numeric($receiverState) || $receiverState < 1 
				|| !is_numeric($receiverCity) || $receiverCity < 1
				|| !is_numeric($receiverDistrict) || $receiverDistrict < 1){
			echo "<script type='text/javascript'>alert('请选择正确地区！');history.go(-1);</script>";
			exit();
		}
		
		$areaArr = array();
		$areaArr['receiver_state'] = $receiverState;
		$areaArr['receiver_city'] = $receiverCity;
		$areaArr['receiver_district'] = $receiverDistrict;
		$areaArr = $this->receiverAera($areaArr);
		if(!$areaArr){
			echo "<script type='text/javascript'>alert('未获取到地区信息！');history.go(-1);</script>";
			exit();
		}
		$addrlen = mb_strlen($receiverAddr,'utf8');
		if($addrlen < 6 || $addrlen > 32){
			echo "<script type='text/javascript'>alert('收货地址有误！（长度6~32个字符）');history.go(-1);</script>";
			exit();
		}
		
		$where = array();
		$where['post_id'] =  $postid;
		$postStatus = $this->giftPost->where($where)->getField('post_status');
		if($postStatus != 0 && $postStatus != 1){
			echo "<script type='text/javascript'>alert('该礼物已被领取！');history.go(-1);</script>";
			exit();
		}
		$buyerArea = "{$receiverState}/{$receiverCity}/{$receiverDistrict}";
		$receiver = array(
			'receiver_name'=>$receiverName,
			'receiver_phone'=>$receiverPhone,
			'receiver_state'=>$areaArr['receiver_state'],
			'receiver_city'=>$areaArr['receiver_city'],
			'receiver_district'=>$areaArr['receiver_district'],
			'receiver_addr'=>$receiverAddr,
			'buyer_area'=>$buyerArea
		);
		//print_r($receiver);exit();
		//开启事物
		$this->giftPost->startTrans();
		//将数据转移到交易表
		$result = $this->addTrade($tid,$receiver);
		if(!$result){
			$this->giftPost->rollback();
			echo "<script type='text/javascript'>alert('保存收货信息失败');history.go(-1);</script>";
			exit();
		}
		//更新post信息
		$result = $this->updatePost($postid, $receiver);
		if($result){
			$this->giftPost->commit();//提交事物
			$receiver['tid'] = $tid;
			$this->giftReceiverAddr->add($receiver);
			$this->giftTrade->where(array('tid'=>$tid))->setField('post_status',2);//更新交易表的领取状态
			$this->display('wait');
		}else{
			$this->giftPost->rollback();
			echo "<script type='text/javascript'>alert('保存收货信息失败，请重试');history.go(-1);</script>";
		}
	}
	
	public function test(){
		$data = array();
		$data['tid'] = '161109154938726';
		$data['postid'] = 8;
		$dataStr = json_encode($data);
		$flag = authCode($dataStr,'ENCODE',C('KEY'));
		$flag = str_replace('+','%2B',$flag);
		echo 'http://'.$_SERVER['HTTP_HOST'].U('Receive/open').'?flag='.$flag;
	}
	
	//获取地区
	public function areaChild(){
		$jdId = I('area_id','-1','intval');
		$ret = array('code'=>-1,'msg'=>'unkown error');
		if(!is_numeric($jdId) || $jdId < 1){
			$ret['msg'] = 'invalid id';
			$this->ajaxReturn($ret);
		}
		$data = $this->siteArea->where("jd_pid=$jdId")->select();
		if(!empty($data)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
			$ret['data'] = $data;
		}else{
			$ret['msg'] = 'empty';
		}
		$this->ajaxReturn($ret);
	}
	
	//添加到交易表
	private function addTrade($tid,$receiver){
		$where = array();
		$where['tid'] = $tid;
		$trade = $this->giftTrade->where($where)->find();
		
		if(empty($trade)){
			return false;
		}
		
		$data = array();
		$data['tid'] = $tid;
		$data['shop_id'] = $trade['shop_id'];
		$data['user_id'] = $trade['user_id'];
		$data['com_id'] = $trade['com_id'];
		$data['dlytmpl_id'] = 0;
		//$data['ziti_addr'] = '';
		$data['status'] = $trade['status'];
		$data['payment'] = $trade['payment'];
		$data['total_fee'] = $trade['total_fee'];
		$data['post_fee'] = 0;
		$data['receiver_name'] = $receiver['receiver_name'];
		$data['created_time'] = time();
		$data['receiver_state'] = $receiver['receiver_state'];
		$data['receiver_city'] = $receiver['receiver_city'];
		$data['receiver_district'] = $receiver['receiver_district'];
		$data['receiver_address'] = $receiver['receiver_addr'];
		$data['receiver_zip'] = '';
		$data['receiver_mobile'] = $receiver['receiver_phone'];
		$data['title'] = '订单明细介绍';
		$data['buyer_message'] = '';
		$data['receiver_phone'] = '';
		$data['itemnum'] = $trade['num'];
		$data['buyer_area'] = $receiver['buyer_area'];
		$data['total_weight'] = $trade['total_weight'];
		$data['transno'] = $trade['transno'];
		$data['status'] = $trade['status'];
		$data['payed_fee'] = $trade['payed_fee'];
		$data['pay_time'] = $trade['pay_time'];
		$data['trade_from'] = 'gift';
		$result = $this->sysTrade->add($data);
		unset($data);
		if(!$result){
			return false;
		}
		$useid = $trade['user_id'];
		//添加trade_order
		$order = array();
		$order['oid'] = substr(date('YmdHis'),2).rand(10,99).$useid;
		$order['tid'] = $tid;
		$order['shop_id'] = $trade['shop_id'];
		$order['user_id'] = $trade['user_id'];
		$order['item_id'] = $trade['item_id'];
		$order['sku_id'] = $trade['sku_id'];
		$order['cat_id'] = $trade['cat_id'];;//类目id
		$order['bn'] = $trade['bn'];
		$order['title'] = $trade['title'];
		$order['spec_nature_info'] = $trade['spec_nature_info'];
		$order['price'] = $trade['price'];
		$order['cost_price'] = $trade['cost_price'];
		$order['supplier_id'] = $trade['supplier_id'];
		$order['send_type'] = $trade['send_type'];
		$order['num'] = $trade['num'];
		$order['total_fee'] = $trade['total_fee'];
		$order['total_weight'] = $trade['total_weight'];
		//$order['modified_time'] = time();
		$order['status'] = $trade['status'];
		$order['order_from'] = 'gift';
		$order['pic_path'] =  $trade['pic_path'];
		
		return $this->systrade_order->add($order);
	}
	
	//获取地区信息
	private function receiverAera($areaArr){
		if(empty($areaArr)){
			return false;
		}
		//查询地区名称
		$where = array();
		$where['jd_id'] = array('in',$areaArr);
		$areaList = $this->siteArea->field('jd_id,name')->where($where)->select();
		if(empty($areaList)){
			return false;
		}
		foreach ($areaArr as $key=>$jdId){
			foreach ($areaList as $val){
				if($jdId == $val['jd_id']){
					$areaArr[$key] = $val['name'];
				}
			}
		}
		
		return $areaArr;
	}
	
	//更新post
	private function updatePost($postid, $receiver){
		$data = array();
		$data['receiver_name'] = $receiver['receiver_name'];
		$data['receiver_phone'] = $receiver['receiver_phone'];
		$data['receiver_addr'] = $receiver['receiver_addr'];
		$data['post_status'] = 2;
		$where = array();
		$where['post_id'] = $postid;
		return $this->giftPost->where($where)->save($data);
	}
	
	//解析flag参数
	private function parseFlag($flag){
		//判断
		if(empty($flag)){
			return array();
		}
		//解密
		$jsonStr = authCode($flag, 'DECODE', C('KEY'));
		if(empty($jsonStr)){
			return array();
		}
		//json to array
		$data = json_decode($jsonStr, true);
		if(!is_numeric($data['tid']) || !is_numeric($data['postid'])){
			return array();
		}
		return $data;
	}
}