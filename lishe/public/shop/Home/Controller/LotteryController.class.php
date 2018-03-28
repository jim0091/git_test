<?php

namespace Home\Controller;


/**
 * 抽奖
 * @author Gaolong
 *
 */
class LotteryController extends CommonController {
	
	private $isLogin = -1; //是否登陆
	//开始日期
	private $startDate = '2017-04-01 00:00:00';
	private $endDate = '2017-04-07 23:59:59';
	private $startTime = '';
	private $endTime = '';
	
	public function __construct(){
		parent::__construct();
		$this->startTime = strtotime($this->startDate);
		$this->endTime = strtotime($this->endDate);
		$this->isLogin = empty($this->uid) ? -1 : 1;
	}
	
	//首页
	public function index(){
		//检查是否具有抽奖权限
		$isAccess = -1;
		if($this->isLogin == 1){
			$accessArr = $this->access($this->uid, $this->startTime);
			$isAccess = isset($accessArr['payment_id']) ? 1 : -1;
		}
		$lotteryUserList = M('lottery_user')->field('username,prize_name')
								->limit(0,30)
								->order('id DESC')
								->select();
		$this->assign('isLogin', $this->isLogin);
		$this->assign('isAccess', $isAccess);
		$this->assign('userList', $lotteryUserList);
		$this->display('index');
	}
	//开始抽奖
	public function start(){
		$ret = array('code'=>-1, 'msg'=>'unkown error', 'data'=>array());
		
		if($this->isLogin != 1){
			$ret['msg'] = '您还未登录，请登录';
			$this->ajaxReturn($ret);
		}
		
		$uid = $this->uid;
		
		if(NOW_TIME < $this->startTime){
			$ret['msg'] = '活动还没开始';
			$this->ajaxReturn($ret);
		}
		if(NOW_TIME > $this->endTime){
			$ret['msg'] = '活动已经结束了';
			$this->ajaxReturn($ret);
		}
		
		//判断当前用户是否具有抽奖资格
		$accessArr = $this->access($uid, $this->startTime);
		$paymentId = $accessArr['payment_id'];
		$tid = $accessArr['tid'];
		
		if(!is_numeric($paymentId) || $paymentId < 1){
			$ret['msg'] = '你没有抽奖机会哦';
			$this->ajaxReturn($ret);
		}
		//检索奖品
		$LotteryPrize = M('lottery_prize');
		$prizeList = $LotteryPrize->select();
		$prizeNameMap = array();
		$prizeArr = array();
		
		foreach ($prizeList as $prize){
			$remainNum = $prize['remain_num'];
			if($remainNum < 1){
				continue;
			}
			$prizeArr[$prize['prize_id']] = $remainNum;
			$prizeNameMap[$prize['prize_id']] = $prize['prize_name'];
		}
		if(empty($prizeArr)){
			$ret['msg'] = '奖品已全部抽完';
			$this->ajaxReturn($ret);
		}
		
		$winPrizeId = $this->lottery($prizeArr);
		if($winPrizeId < 1){
			$ret['msg'] = '系统错误，重新抽奖';
			$this->ajaxReturn($ret);
		}
		$LotteryPrize->startTrans();
		try {
			$map = array(
				'prize_id' => $winPrizeId
			);
			$result = $LotteryPrize->where($map)->setDec('remain_num');
			if($result == 1){
				$data = array(
					'user_id' => $uid,
					'username' => $this->userName,
					'prize_id' => $winPrizeId,
					'prize_name' => $prizeNameMap[$winPrizeId],
					'payment_id' => $paymentId,
					'tid' => $tid,
					'create_time' => time(),
				);
				$result = M('lottery_user')->add($data);
				if(!$result){
					$LotteryPrize->rollback();
					$ret['msg'] = '系统错误，重新抽奖';
					$this->ajaxReturn($ret);
				}
				//提交事物
				$LotteryPrize->commit();
				
				$tmpSum = array_sum($prizeArr);
				$rate = bcdiv($prizeArr[$winPrizeId], $tmpSum, 4);
				$info = $prizeNameMap[$winPrizeId] . '   概率：' . $rate * 100 .'%';
				
				$prizeNo = $this->prizeNo($winPrizeId);
				
				$ret['code'] = 1;
				$ret['msg'] = 'success';
				$ret['data'] = array('prizeNo'=>$prizeNo,'prizeid'=>$winPrizeId,'info'=>$info);
				$this->ajaxReturn($ret);
			}else{
				$LotteryPrize->rollback();
				$ret['msg'] = '系统错误，重新抽奖';
				$this->ajaxReturn($ret);
			}
		} catch (Exception $e) {
			$LotteryPrize->rollback();
			$ret['msg'] = '系统错误，重新抽奖';
			$this->ajaxReturn($ret);
		}
	}
	
	/**
	 * 抽奖算发
	 * @param unknown $prizeArr 数组（奖品id=>可用奖品数量）
	 * @return number|unknown 奖品id
	 * @author Gaolong
	 */
	private function lottery($prizeArr){
		$sum = array_sum($prizeArr);
		$winPrizeId = -1;
		foreach ($prizeArr as $prizeId => $remainNum){
			$randNum = mt_rand(1, $sum);
			if($randNum <= $remainNum){
				$winPrizeId = $prizeId;
				break;
			}else{
				$sum -= $remainNum;
			}
		}
		return $winPrizeId;
	}
	
	/**
	 * 获取用户抽奖权限
	 * @author Gaolong
	 */
	private function access($uid, $time){
		$map = array(
			'user_id'=>$uid,
			'payed_time' => array('egt', $time),
		);
		//检索支付单
		$EctoolsPayments = M('ectools_payments');
		$payList = $EctoolsPayments->field('payment_id,status')->where($map)->select();
		
		$paymentIdArr = array(); //支付id集合
		foreach ($payList as $pay){
			if($pay['status'] == "succ"){ //支付成功
				$paymentIdArr[] = $pay['payment_id'];
			}
		}
		if (empty($paymentIdArr)){
			return false;
		}
		//检索该支付单中是否有自营商品
		$map = array(
			'payment_id' => array('in', $paymentIdArr),
		);
		//检索支付子表，交易id
		$EctoolsTradePaybill = M('ectools_trade_paybill');
		$paybillList = $EctoolsTradePaybill->field('payment_id,tid')->where($map)->select();
		if(empty($paybillList)){
			return false;
		}
		
		$tidPaymentIdMap = array();
		$tidArr = array();
		foreach ($paybillList as $val){
			$tid = $val['tid'];
			$tidPaymentIdMap[$tid] = $val['payment_id'];
			$tidArr[] = $tid;
		}
		//检索订单是否有自营商品
		$map = array(
			'tid' => array('in', $tidArr),
		);
		$orderList = M('systrade_order')->field('tid,shop_id')->where($map)->select();
		if(empty($orderList)){
			return false;
		}
		$paymentIdArr = array();
		foreach ($orderList as $order){
			if($order['shop_id'] != 10){
				$paymentId = $tidPaymentIdMap[$order['tid']];
				$paymentIdArr[$paymentId] = $order['tid']; //这里已经将paymentid去重了
			}
		}
		if(empty($paymentIdArr)){
			return false;
		}
		$map = array(
			'user_id' => $uid,
		);
		$LotteryUser = M('lottery_user');
		$lotteryUserList = $LotteryUser->field('user_id,payment_id')->where($map)->select();
		//$accessList = array();
		
		foreach ($lotteryUserList as $val){
			$paymentId = $val['payment_id'];
			if(isset($paymentIdArr[$paymentId])){
				unset($paymentIdArr[$paymentId]);
			}
			//$accessList[] = $paymentId;
		}
		if(empty($paymentIdArr) || !is_array($paymentIdArr)){
			return false;
		}
		return array('payment_id'=>key($paymentIdArr),'tid'=>current($paymentIdArr));
	}
	
// 	/**
// 	 * 获奖用户
// 	 * @author Gaolong
// 	 */
// 	public function lotteryUser(){
// 		$LotteryUser = M('lottery_user');
// 		$ret = array('code'=>-1, 'msg'=>'unkown error', 'data'=>array());
// 		$lotteryUserList = $LotteryUser->field('username,prize_name')
// 							->where($map)
// 							->limit(30)
// 							->order('id DESC')
// 							->select();
// 		$ret['code'] = 1;
// 		$ret['msg'] = 'success';
// 		$ret['data'] = $lotteryUserList;
// 		$this->ajaxReturn($ret);
// 	}
	
	/**
	 * 映射前端序号对应关系
	 * @param unknown $prizeId
	 * @return number
	 */
	private function prizeNo($prizeId){
		$prizeNo = -1;
		switch ($prizeId){
			case 1: $prizeNo = 1; break;
			case 2: $prizeNo = 7; break;
			case 3: $prizeNo = 3; break;
			case 4: $prizeNo = 5; break;
			case 5: $prizeNo = 2; break;
			case 6: $prizeNo = 6; break;
			case 7: $prizeNo = 4; break;
			case 8: $prizeNo = 8; break;
		}
		return $prizeNo;
	}
}