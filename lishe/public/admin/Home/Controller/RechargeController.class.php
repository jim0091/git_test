<?php  
namespace Home\Controller;
class RechargeController extends CommonController{
/**
 * 充值管理zhngrui
 * 20161230creat
 * 
 **/	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dRecharge=D('Recharge');
		$this->dReturn = D('Return');
	}
/*
 * 正章干洗搜索条件
 * */	
	public function serachCondition(){
		$data['startTime'] = I('startTime','','trim,urldecode');
		$data['endTime'] = I('endTime','','trim,urldecode');		
		$data['mobile'] = I('mobile','','trim');	
		$data['applyMobile'] = I('applyMobile','','trim');	
		$data['type']= I('type',-99,'intval');	
		$data['status']= I('status',-99,'intval');	
		$this->assign('searchData',$data);
		//时间条件
		if(!empty($data['endTime'])){
			$data['endTime']=date('Y-m-d',strtotime("{$data['endTime']} +1 day"));
		}
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$condition['create_time']=array('between',array($data['startTime'],$data['endTime']));
		}
		if(!empty($data['startTime']) && empty($data['endTime'])){
			$condition['create_time']=array('gt',$data['startTime']);
		}
		if(empty($data['startTime']) && !empty($data['endTime'])){
			$condition['create_time']=array('lt',$data['endTime']);
		}		
		//充值手机号
		if(!empty($data['mobile'])){
			$condition['mobile']=$data['mobile'];
		}
		//申请人手机号
		if(!empty($data['applyMobile'])){
			$condition['apply_username']=$data['applyMobile'];
		}		
		//充值类型
		if($data['type']!=-99){
			$condition['coupon_type']=$data['type'];
		}
		if($data['status']!=-99){
			if($data['status'] == -1){
				$condition['apply_status']=3;
			}else{
				$condition['apply_status']=$data['status'];
			}			
		}		
		return $condition;
	}	
/**
 * 正章干洗洗衣券兑换
 **/		
	public function zzgxIndex(){
		$condition=$this->serachCondition();
		$number=$this->dRecharge->getZZExchApplyCount($condition);
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
		$list=$this->dRecharge->getAllZZExchApply($condition,$limit);
		foreach($list as $key=>$value){
			$applyIds[]=$value['apply_id'];
		}
		if(!empty($applyIds)){
			$where=array(
				'apply_id'=>array('in',$applyIds),
			);
			$details=$this->dRecharge->getAllZZExchApplyCoupon($where);
			foreach($details as $key=>$val){
				$coupon[$val['apply_id']][]=$val;
			}
		}
		$pagestr = $page->show("pageos","pageon");  //组装分页字符串
		$this->assign('pagestr',$pagestr);	
		$this->assign('list',$list);
		$this->assign('coupon',$coupon);
		$this->assign('number',$number);		
		$this->display();
	}	
/*
 * 正章干洗充值页
 * */
	public function zzgxRecharge(){
		$applyId=I('applyId');
		if(!$applyId){
			exit('ID NO FOUND!');	
		}
		$where=array(
			'apply_id'=>$applyId,
		);
		$info=$this->dRecharge->getThisZZExInfo($where);
		$this->assign('info',$info);
		$this->display();
	}
/*
 * 正章干洗充值详情
 * */	
 	public function zzgxRechargeDeatils(){
		$applyId=I('applyId');
		if(!$applyId){
			exit('ID NO FOUND!');	
		}
		$where=array(
			'apply_id'=>$applyId,
		);
		$info=$this->dRecharge->getThisZZExInfo($where);
		$this->assign('info',$info);
		$this->display(); 		
 	}
/*
 * 正章干洗拒绝充值
 * */	
 	public function zzgxRefuseRecharge(){
	  	$applyId=I('applyId','0','intval');
	  	$data['is_match']=I('match','1','intval');
	  	$data['is_receive']=I('receive','1','intval');
	  	$data['not_match_op']=I('matchOp','','intval');
	  	$data['note']=I('mark','','trim');
	  	$data['operator']=$this->adminId;
		$ret=array(
			'code' => 0,
			'msg'  => 'Error Unkonw',
		);
		if(!$applyId){
			$ret['msg']='ID标识获取失败!';
			$this->ajaxReturn($ret);
		} 	
		$where=array(
			'apply_id'=>$applyId,
		);	
	  	$data['apply_status']=2;
	  	$data['finish_time']=date('Y-m-d H:i:s');					
		$res=$this->dRecharge->updateZZExInfo($where,$data);
		if(!$res){
			$ret['msg']='操作失败!';
			$this->ajaxReturn($ret);				
		}
		$ret['code']=1;
		$ret['msg']='操作成功!';
		$this->makeLog('zzgxRecharge',"applyId:{$applyId}-------start");
		$this->makeLog('zzgxRecharge',"applyId:{$applyId}拒绝充值--{$this->realName}");
		$this->makeLog('zzgxRecharge',"applyId:{$applyId}--------end");
		$this->ajaxReturn($ret);		
 	}
/*
 * 正章干洗充值操作
 * */
	public function zzgxToRecharge(){
	  	$applyId=I('applyId','0','intval');
	  	$data['is_match']=I('match','1','intval');
	  	$data['is_receive']=I('receive','1','intval');
	  	$data['not_match_op']=I('matchOp');
	  	$canAmount=I('canAmount','','trim');
	  	$data['note']=I('mark','','trim');
	  	$data['operator']=$this->adminId;
	  	$data['avail_coupon_amount']=$canAmount;
		$ret=array(
			'code' => 0,
			'msg'  => 'Error Unkonw',
		);
		if(!$applyId){
			$ret['msg']='ID标识获取失败!';
			$this->ajaxReturn($ret);
		}
		$where=array(
			'apply_id'=>$applyId,
		);
		$info=$this->dRecharge->getThisZZExInfo($where);
		if($info['apply_status']==1){
			$ret['msg']='该充值卡、券已充值过,无需重新充值!';
			$this->ajaxReturn($ret);			
		}
		if(empty($info)){
			$ret['msg']='ID参数有误!';
			$this->ajaxReturn($ret);			
		}
		if($canAmount>$info['coupon_amount']){
			$ret['msg']='有效总面值不能大于总面值!';
			$this->ajaxReturn($ret);			
		}
		//计算最后兑换金额
		$toMoney=$canAmount*$info['exch_rate'];
	  	$data['avail_amount']=$toMoney;
		$this->makeLog('zzgxRecharge',"applyId:{$applyId}-------start");
		//生成支付单
		$paymentId=$this->creatPaymentInfo($info['user_id'],$info['mobile'],$toMoney);
		if(!$paymentId){
			$ret['msg']='生成支付单失败!';
			$this->makeLog('zzgxRecharge',"applyId:{$applyId}生成支付单失败!");
			$this->ajaxReturn($ret);				
		}
		//充值积分
		$rechargees=$this->rechargePoint($paymentId,'zzgx','正章干洗充值','正章干洗洗衣卡券充值','正章干洗洗衣卡券兑换充值!');
		if(!$rechargees){
			$ret['msg']='积分充值失败!';
			$this->makeLog('zzgxRecharge',"applyId:{$applyId}积分充值失败!");
			$this->ajaxReturn($ret);			
		}
		//更新正章干洗数据
	  	$data['apply_status']=1;
	  	$data['finish_time']=date('Y-m-d H:i:s');		
		$res=$this->dRecharge->updateZZExInfo($where,$data);
		if(!$res){
			$ret['msg']='积分充值成功，本地更新失败!';
			$this->makeLog('zzgxRecharge',"applyId:{$applyId}积分充值成功，本地更新失败!");
			$this->ajaxReturn($ret);				
		}
		//短信通知用户
		$Repoint=$toMoney*100;
		$content="尊敬的用户，您的正章干洗券充值积分（{$Repoint}）已经到账，请及时登录商城进行购物，祝您购物愉快，礼舍科技竭诚为您服务4001188234    【礼舍科技】";
		$this->sendSms($info['mobile'],$content);
		$ret['code']=1;
		$ret['msg']='积分充值成功!';
		$this->makeLog('zzgxRecharge',"applyId:{$applyId}积分充值成功,支付单号：{$paymentId}!");
		$this->makeLog('zzgxRecharge',"applyId:{$applyId}--------end");
		$this->ajaxReturn($ret);	
				
	}
/**
 * 添加支付表数据
 * @param $userId 用户Id
 * @param $mobile 用户名
 * @param $money 交易金额
 **/	
	private function creatPaymentInfo($userId,$mobile,$money){
		if(empty($userId) || empty($mobile) || empty($money)){
			return FALSE;
		}
        $data['payment_id'] = date(YmdHis).$userId.'1';//支付单号
        $data['money'] = floatval($money);//需要支付的金额
        $data['cur_money'] = 0;//支付货币金额
        $data['user_id'] = $userId;
        $data['user_name'] = $mobile;
        $data['pay_type'] = 'recharge';
        $data['op_name'] = $this->realName; //操作员
        $data['bank'] = '预存款';//收款银行
        $data['created_time'] = time();
		$result=$this->dRecharge->addPaymentInfo($data);	
		if($result){
			return $data['payment_id'];
		}else{
			return FALSE;
		}
	}
/**
 *充值积分 
 * @param $paymentId支付单号
 * @param $payType支付类型
 * @param $payName支付方式
 * @param $memo支付表备注
 * @param $msg积分详情表备注
 **/
 	private function rechargePoint($paymentId,$payType,$payName,$memo,$msg){
 		if(!$paymentId){
 			return FALSE;
 		}
		$paymentInfo=$this->dRecharge->getPaymentInfo($paymentId,$field);
		$balance=$paymentInfo['money']*100;
        $sign=md5('activeType=1&orderno='.$paymentId.'&phoneNum='.$paymentInfo['user_name'].'&pointsAmount='.$balance.'&pointsType=1'.C('API_KEY'));
        $url=C('API').'mallPoints/exchange';
	    $data=array(
	        'phoneNum'=>$paymentInfo['user_name'],
	        'pointsAmount'=>$balance,
	        'orderno'=>$paymentId,
	        'pointsType'=>1,
	        'activeType'=>1,
	        'sign'=>$sign
	    );      
	    $return=$this->requestPost($url,$data);	
		$this->makeLog('zzgxRecharge',"充值返回:{$return}");
        $retArr=json_decode($return,TRUE);
        if($retArr['result']==100 && $retArr['errcode']==0){
        	//充值成功
        	//更新本地积分
        	$this->dRecharge->updateLocalPoint($paymentInfo['user_id'],$paymentInfo['money'],$balance);
            $data['cur_money'] = $paymentInfo['money'];
	        $data['pay_app_id'] = $payType; 
	        $data['pay_name'] = $payName; 			
            $data['memo'] = $memo;
            $data['payed_time'] = time();
            $data['status'] = 'succ';
            $data['ls_trade_no'] = $retArr['data']['info']['transno'];
			//添加至积分详情表
			$pointDeatils['type']='add';
			$pointDeatils['user_id']=$paymentInfo['user_id'];
			$pointDeatils['operator']='管理员';
			$pointDeatils['fee']=$paymentInfo['money'];
			$pointDeatils['message']=$msg;
			$pointDeatils['logtime']=time();
			$this->dRecharge->addPointDeatil($pointDeatils);			
			//更新支付表数据
			$this->dRecharge->updatePaymentInfo($paymentInfo['payment_id'],$data);
			return TRUE;
        }else{
        	//充值失败
        	return FALSE;
        }        
		
 	}
/**
 * 发送短信通知用户
 * @param $phone发送手机号
 * @param $content内容
 **/
	public function sendSms($phone,$content){
        vendor('SendPhoneCode.SendCode','','.php');
        $sendCode = new \SendCode();
        $codeResult = $sendCode->sendPhoneCode($phone,$content);
        if ($codeResult['message'] == "成功") {
			$this->makeLog('zzgxRecharge',"{$phone}短信发送成功");
            return TRUE;
        }else{
			$this->makeLog('zzgxRecharge',"{$phone}短信发送失败");
            return FALSE;
        }		
	}


/****************************************************积分返现********************************************************/
	//
	public function cond(){
		$data['startTime'] = I('startTime','','trim,urldecode');
		$data['endTime'] = I('endTime','','trim,urldecode');		
		$data['aid'] = I('aid','','intval');	
		$data['paymentId'] = I('paymentId','','trim');	
		$data['return_status']= I('return_status','');	
		$data['pay_status']= I('pay_status','');	
		$this->assign('searchData',$data);
		//时间条件
		if(!empty($data['endTime'])){
			$data['endTime']=date('Y-m-d',strtotime("{$data['endTime']} +1 day"));
		}
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$condition['return_time']=array('between',array($data['startTime'],$data['endTime']));
		}
		if(!empty($data['startTime']) && empty($data['endTime'])){
			$condition['return_time']=array('gt',$data['startTime']);
		}
		if(empty($data['startTime']) && !empty($data['endTime'])){
			$condition['return_time']=array('lt',$data['endTime']);
		}		
		//充值手机号
		if(!empty($data['aid'])){
			$condition['aid']=$data['aid'];
		}
		//支付单号
		if(!empty($data['paymentId'])){
			$condition['payment_id']=$data['paymentId'];
		}		
		//满返状态
		if(!empty($data['return_status'])){
			$condition['return_status']=$data['return_status'];
		}
		//支付状态
		if(!empty($data['pay_status'])){			
			$condition['pay_status']=$data['pay_status'];		
		}		
		return $condition;
	}
	//积分返现
	public function pointreg(){
		$condition=$this->cond();
		$number=$this->dReturn->getReturnCount($condition);
		$size=20;
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；nfig('last' ,'尾页');onfig('prev' ,'上一页');age -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$field = '*';
		$list=$this->dReturn->getReturnList($condition,$field,$limit);	
		foreach($list as $key=>$value){
			$aIds[]=$value['aid'];
			$uIds[]=$value['user_id'];
		}
		if(!empty($aIds)){
			$where=array(
				'aid'=>array('in',$aIds),
			);
			$field = 'aid,activity_name';
			$activity=$this->dReturn->getActivity($where,$field);
			foreach($activity as $key=>$val){
				$aid[$val['aid']]=$val['activity_name'];
			}
		}
		if (!empty($aIds)) {
			unset($condition);
			unset($field);
			$condition=array(
				'user_id'=>array('in',$uIds),
			);
			$field = 'user_id,username';
			$userName=$this->dReturn->getUser($where,$field);
			foreach($userName as $key=>$val){
				$user[$val['user_id']]=$val['username'];
			}
		}
		unset($condition);
		unset($field);
		$condition = array();
		$field = 'aid,activity_name';
		$activityList = $this->dReturn->getActivity($condition,$field);
		$pagestr = $page->show("pageos","pageon");  //组装分页字符串
		$this->assign('pagestr',$pagestr);	
		$this->assign('list',$list);
		$this->assign('activityList',$activityList);
		$this->assign('aid',$aid);	
		$this->assign('user',$user);		
		$this->assign('number',$number);		
		$this->display();
	}	
	//积分返现
	public function pointRecharge(){
		$returnId = I('returnId');
		if (empty($returnId)) {
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}
		//获取返现记录信息
		$condition = array('return_id'=>$returnId);
		$field = array('payment_id','return_id','return_fee','returned_fee','pay_status','return_status','user_id','return_time');
		$returnInfo = $this->dReturn->getReturnInfo($condition,$field);
		if (!$returnInfo) {
			echo json_encode(array(0,'未查询到相关退款记录！'));
			exit();
		}
		if (empty($returnInfo['return_fee'])) {
			echo json_encode(array(0,'该订单无需返现！'));
			exit();
		}

		if ($returnInfo['returned_fee'] != '0' || $returnInfo['return_status'] == 'TRADE_FINISHED' || !empty($returnInfo['return_time'])) {
			echo json_encode(array(0,'已返现的订单无法重复返现！'));
			exit();
		}
		if ($returnInfo['pay_status'] != 'succ') {
			echo json_encode(array(0,'无法返现未支付的订单！'));
			exit();
		}
		if (empty($returnInfo['user_id'])) {
			echo json_encode(array(0,'无法查询该用户'));
			exit();			
		}
		//获取用户信息
		unset($condition);
		unset($field);
		$condition = array('user_id'=>$returnInfo['user_id']);
		$field = array('user_id','mobile');
		$userInfo = $this->dReturn->getUserInfo($condition,$field);
		if (!$userInfo) {
			echo json_encode(array(0,'用户不存在'));
			exit();					
		}
		if (empty($userInfo['mobile'])) {
			echo json_encode(array(0,'无法获取到该用户手机号码'));
			exit();	
		}
		
		$key = C('API_KEY');
		$phoneNum = $userInfo['mobile'];
		$pointsAmount = round($returnInfo['return_fee']*100,2);
		$orderno = $returnInfo['payment_id'];
		$pointsChannel = 'www';
		$sign = md5("orderno={$orderno}&phoneNum={$phoneNum}&pointsAmount={$pointsAmount}&pointsChannel={$pointsChannel}{$key}");
		//调用返现接口
		//$sign=md5('activeType=2&orderno='.$returnInfo['payment_id'].'&phoneNum='.$userInfo['mobile'].'&pointsAmount='.round($returnInfo['return_fee']*100,2).'&pointsType=1'.C('API_KEY'));
		$data=array(
			//'activeType'=>2,
			'orderno' => $orderno,
        	'phoneNum' => $phoneNum,
        	'pointsAmount' => $pointsAmount,
        	//'pointsType'=>1,
        	'pointsChannel' => $pointsChannel,
			'sign' => $sign
        );
        $result=$this->requestPost(C('API').'mallPointsService/rebate', $data);
        $res=json_decode($result,TRUE);
        if ($res['result'] != 100 || $res['errcode'] != 0) {
			echo json_encode(array(0,'返现失败！Msg：'.$res['msg']));
			exit();	        	
        }
        if ($res['data']['info']['amount'] || $res['data']['info']['transno']) {
        	unset($condition);
        	unset($data);
        	$condition = array('return_id'=>$returnId);
        	$data['returned_fee'] = round($res['data']['info']['amount']/100,2);
        	$data['ls_trade_no'] = $res['data']['info']['transno'];
        	$data['return_status'] = 'TRADE_FINISHED';
        	$data['return_time'] = time();
        	$res = $this->dReturn->updateReturn($condition,$data);
        	if ($res === false) {
        		echo json_encode(array(0,'返现失败！'));
        		exit();
        	}else{
        		echo json_encode(array(1,'返现成功！（db）'));
        		exit();
        	}
        }else{
        	echo json_encode(array(0,'返现失败（api）'));
        	exit();
        }

	}
	
	/**
	 * 满返列表
	 */
 	public function returnList() {
 		$activityList = M('company_activity')->field('aid,activity_name')->order('aid DESC')->select();
 		$activityNameMap = array();
 		foreach ($activityList as $activity) {
 			$activityNameMap[$activity['aid']] = $activity['activity_name'];
 		}
 		$map = array(
 			'status' => array('neq', -1)
 		);
 		$returnRuleList = M('return_point_rule')->where($map)->select();
 		
 		$comList = M('company_config')->where('is_delete=0')->field('com_id,com_name')->select();
 		$comNameMap = array();
 		foreach ($comList as $com) {
 			$comNameMap[$com['com_id']] = $com['com_name'];
 		}
 		$this->assign('activityList', $activityList);
 		$this->assign('activityNameMap', $activityNameMap);
 		$this->assign('returnRuleList', $returnRuleList);
 		$this->assign('comList', $comList);
 		$this->assign('comNameMap', $comNameMap);
 		$this->display();
 	}
	
 	/**
 	 * 添加满返规则
 	 */
 	public function addRule() {
 		$aid = I('post.aid', -1, 'intval');
 		$comid = I('post.comid', -1, 'intval');
 		$stime = I('post.stime', '');
 		$etime = I('post.etime', '');
 		$rules = I('post.rules');
 		$items = I('post.items', '', 'trim');
 		$ret = array('code' => -1, 'msg'=>'unkown error');
 		
 		$ruleArr = array();
 		foreach ($rules as $rule) {
 			$samount = $rule['samount'];
 			$eamount = $rule['eamount'];
 			$ramount = $rule['ramount'];
 			$ruleArr[] = array($samount, $eamount, $ramount);
 		}
 		
 		$ruleJson = json_encode($ruleArr);
 		
 		$data = array(
 			'aid' 		=> $aid,
 			'com_id' 	=> $comid,
 			'start_time'=> $stime,
 			'end_time' 	=> $etime,
 			'rule'	 	=> $ruleJson,
 			'items' 	=> $items,
 		);
 		$result = M('return_point_rule')->add($data);
 		if ($result) {
 			$ret['code'] = 1;
 			$ret['msg'] = 'success';
 		}
 		$this->ajaxReturn($ret);
 	}
 	
 	/**
 	 * 刷新订单
 	 */
 	public function refreshOrder() {
 		$ruleId = I('post.ruleId', -1, 'intval');
 		$ret = array('code' => -1, 'msg'=>'unkown error');
 		if (!is_numeric($ruleId) || $ruleId < 1) {
 			$ret['msg'] = '满返规则id错误';
 			$this->ajaxReturn($ret);
 		}
 		$map = array(
 			'rule_id' => $ruleId
 		);
 		$rule = M('return_point_rule')->where($map)->find();
 		if ($rule['status'] != 1) {
 			$ret['msg'] = '满返被禁用或者被删除';
 			$this->ajaxReturn($ret);
 		}
 		
 		$comId = $rule['com_id'] == -1 ? '' : $rule['com_id'];
 		$aid = $rule['aid'];
 		$startDate = strtotime($rule['start_time']);
 		$endDate = strtotime($rule['end_time']);
 		$ruleArr = json_decode($rule['rule'], true);
 		$items = trim($rule['items']);
 		
 		$Activity = D('Activity');
 		$result = $Activity->getActivityReturn($comId, $aid, $startDate, $endDate, $ruleArr, $items, $limit='0,10000');
 		
 		if (isset($result['returnId'])) {
 			$ret['code'] = 1;
 			$ret['msg'] = 'success';
 		} else {
 			$ret['msg'] = '刷新订单失败';
 		}
 		$this->ajaxReturn($ret);
 	}
 	
 	/**
 	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 	 * @name 微信公众号收款-正章干洗券扫码支付的手续费(列表兼搜索页)
 	 * @method get
 	 * @author lihongqiang 2017-07
 	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 	 */
 	public function procedure(){
 		$p = I('get.p', 1, 'intval');
 		$wechatModel = M('wechat_public_crowd_proceeds');
 		$getData = I('get.');
 		$field = 'a.*,b.money,b.cur_money,b.payed_time,b.pay_type,b.pay_app_id,b.pay_from,b.point_pay_type,b.pay_name,b.memo';
 		$where['a.type'] = 'ZZGXSXF';
 		if($getData['paymentId']){
 			$where['a.out_trade_no'] = $getData['paymentId'];
 		}
 		if($getData['user_id']){
 			$where['a.user_id'] = $getData['user_id'];
 		}
 		if($getData['user_mobile']){
 			$where['a.user_mobile'] = $getData['user_mobile'];
 		}
 		if($getData['transaction_id']){
 			$where['a.transaction_id'] = $getData['transaction_id'];
 		}
 		if(!empty($getData['status'])){
 			$where['a.status'] = $getData['status'];
 		}
 		$countNum = $wechatModel->alias('a')->join('ectools_payments b ON a.out_trade_no = b.payment_id')->where($where)->count();
 		$size = 15;//显示条数
 		$start = $size*($p-1);
 		$limit = "{$start},$size";
 		$list = $wechatModel->alias('a')->join('ectools_payments b ON a.out_trade_no = b.payment_id')->where($where)->order('id desc')->field($field)->limit($limit)->select();
 		foreach ($list as &$val){
 			$val['predict_total_fee'] =sprintf('%.2f', $val['predict_total_fee']/100);//把钱的单位（分）转换为（元）
 			$val['practical_total_fee'] =sprintf('%.2f', $val['practical_total_fee']/100);//把钱的单位（分）转换为（元）
 			if($val['practical_total_fee']==='0.00'){
 				$val['practical_total_fee'] = 0;
 			}
 		}
 		$this->assign('list',$list);
 		$this->assign('countNum',$countNum);//总记录数
 		$this->assign('page',showPage($countNum, $size));
 		$this->assign('searchData',$getData);
 		$this->display();
 	}
 	
 	
 	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 干洗券兑换列表
	 * @method get
	 * @author lihongqiang 2017-07
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
 	public function dryclean(){
 		$p = I('get.p', 1, 'intval');
 		$wechatModel = M('ectools_payments');
 		$getData = I('get.');
 		//$field = '';
 		$where['pay_type'] = 'zzgxdh';
 		if($getData['paymentId']){
 			$where['payment_id'] = $getData['paymentId'];
 		}
 		if($getData['user_id']){
 			$where['user_id'] = $getData['user_id'];
 		}
 		if($getData['user_name']){
 			$where['user_name'] = $getData['user_name'];
 		}
 		if($getData['ls_trade_no']){
 			$where['ls_trade_no'] = $getData['ls_trade_no'];
 		}
 		if(!empty($getData['status'])){
 			$where['status'] = $getData['status'];
 		}
 		if(!empty($getData['zz_trade_no'])){
 			$where['created_time'] = $getData['zz_trade_no'];
 		}
 		$countNum = $wechatModel->where($where)->count();
 		$size = 15;//显示条数
 		$start = $size*($p-1);
 		$limit = "{$start},$size";
 		$list = $wechatModel->where($where)->order('payment_id desc')->field($field)->limit($limit)->select();
 		$zzgxBarcodeModel = M('zzgx_barcode');
 		$field1 = '';
 		foreach ($list as &$val){
 			$val['money'] =$val['money']*100;//把钱的单位（元）转换为（分）
 			$val['cur_money'] =$val['cur_money']*100;//把钱的单位（元）转换为（分）
 			$val['payed_time'] = date("Y-m-d H:i",$val['payed_time']);
 			$condition['payment_id'] = $val['payment_id'];
 			$val['barcode'] = $zzgxBarcodeModel->where($condition)->field($field1)->select();
 		}
 		$this->assign('list',$list);
 		$this->assign('countNum',$countNum);//总记录数
 		$this->assign('page',showPage($countNum, $size));
 		$this->assign('searchData',$getData);
 		$this->display();
 	}
 	
 	
 	
 	
}
