<?php
namespace Home\Controller;
class PromotionController extends CommonController {
	public function __construct() {
		parent::__construct();
			$this->comActivity=M('company_activity');
			$this->comActivityCate=M('company_activity_category');
			$this->comActivityCateItem=M('company_activity_category_item');
			$this->modelActivityConfig=M('company_activity_config');
			$this->modelActivityItem=M('company_activity_item');
			$this->modelActivityTime=M('company_activity_time');
			$this->modelConfig=M('company_gaway_config');
			$this->modelItem=M('sysitem_item');
			$this->shopModel=M('sysshop_shop');//店铺信息
			$this->addrModel=M('sysuser_user_addrs'); //收货地址表
			$this->areaModel=M('site_area');
	        $this->modelArea=M('site_area');
			$this->atradeModel = M('company_activity_trade');//活动订单表
			$this->aorderModel = M('company_activity_order');//活动订单子表
	      	$this->paymentsModel = M("ectools_payments");//支付表
	      	$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
	      	$this->userAccountModel = M('sysuser_account');//用户登录表
	      	$this->userDepositModel = M('sysuser_user_deposit');//积分表
	      	$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
			$this->dActivity=D('Activity');
			$this->modelOrder=D('Order');
			$this->modelDeposit=M('sysuser_user_deposit');
			$this->modelDepositLog=M('sysuser_user_deposit_log');	
	        $this->modelItemCount=M('sysitem_item_count');
	        $this->modelItemStore=M('sysitem_item_store');//商品库存
	        $this->modelItemSkuStore=M('sysitem_sku_store');
	      	$this->modelTrade=M('systrade_trade');
	      	$this->companyActCartModel=M('company_activity_cart');
			$this->activityModel=M('company_activity');
	        $this->modelActivCategory = M('company_activity_category');
			$this->assign('index',urldecode($this->index));
	}
/*
 * 校验用户是否符合一元购大米资格
 * */
	public function checkBuyRice(){
		$activityInfo=$this->activityModel->where(array('Identification'=>'oneBuyRice'))->field('start_time,end_time')->find();
		$condition=array(
			'b.user_id'=>$this->uid,
			'a.cur_money'=>array('egt',499),
			'a.created_time'=>array('between',array($activityInfo['start_time'],$activityInfo['end_time'])),
			'a.status'=>'succ',
		);
		$condition['_string'] = 'b.payment_id = a.payment_id';
		$paymentIds=M('')->table('ectools_payments a, ectools_trade_paybill b')->where($condition)->field('a.payment_id')->select(); 
		foreach($paymentIds as $key=>$value){
			$canBuy[]=$value['payment_id'];
		}
		$canBuyNum=count(array_unique($canBuy));//满足活动可买件
		if($canBuyNum>0){
			$tradeCondition=array(
				'user_id'=>$this->uid,
				'aid'=>22,
				'payed_fee'=>array('gt',0),
				'created_time'=>array('between',array($activityInfo['start_time'],$activityInfo['end_time'])),
				'pay_time'=>array('between',array($activityInfo['start_time'],$activityInfo['end_time'])),
			);
			$buyedNum=$this->atradeModel->where($tradeCondition)->field('atid')->count();  //已买件数
			if($canBuyNum>$buyedNum){
				return TRUE;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
/*
 * 校验是否符合双旦0元购苹果
 * */
 	public function checkBuyApple(){
		$activityInfo=$this->activityModel->where(array('Identification'=>'christmasApple'))->field('aid,start_time,end_time')->find();
 		$condition=array(
			'b.user_id'=>$this->uid,
			'a.cur_money'=>array('gt',0),
			'a.created_time'=>array('between',array($activityInfo['start_time'],$activityInfo['end_time'])),
			'a.status'=>'succ',
			'b.tid'=>array('gt',0),
		);
		$nowTime=time();
		if($nowTime<$activityInfo['start_time']){
			$ret['msg']="活动还未开始!";
			return $ret;
		}
		if($nowTime>$activityInfo['end_time']){
			$ret['msg']="活动已结束!";
			return $ret;
		}		
		$condition['_string'] = 'b.payment_id = a.payment_id';
		$paymentId=M('')->table('ectools_payments a, ectools_trade_paybill b')->where($condition)->field('a.payment_id')->find();
		$ret=array('code'=>0,'msg'=>'null');
		if($paymentId){
			//符合条件可以购买苹果
			$tradeCondition=array(
				'user_id'=>$this->uid,
				'aid'=>$activityInfo['aid'],
				'created_time'=>array('gt',0),
				'pay_time'=>array('gt',0),
			);
			$isBuyed=$this->atradeModel->where($tradeCondition)->field('atid')->find();  //已买件数
			if($isBuyed){
				//购买过
				$ret['msg']="每个用户只限抢购1次!";
			}else{
				//符合资格
				$ret=array('code'=>1,'msg'=>'符合资格!');
			}
		}else{
			$ret['msg']="暂无抢购圣诞平安果资格!";
		} 
		return $ret;
 	}
/*
 * 单击购买时校验是否有资格
 * */ 
 	public function checkQualification(){
 		$uid=$this->uid;
		if(empty($uid)){
			echo json_encode(array(0,'您还未登录,请先登录！'));
			exit;				
		}
		if($this->checkBuyRice()){
			//符合资格	
			$aid=$this->activityModel->where(array('Identification'=>'oneBuyRice'))->getField('aid');
			$aitemId=$this->modelActivityItem->where(array('aid'=>$aid))->getField('aitem_id');
			echo json_encode(array(1,'恭喜享有资格！',C('TMPL_PARSE_STRING.__LISHE_URL__').'/shop.php/promotion/orderPage/aitemId/'.$aitemId));
			exit;		
		}else{
			//不符合资格
			echo json_encode(array(0,'您暂不享有领取资格，请先完成付款订单后购买！'));
			exit;			
		}
 	}
	public function activity($aid,$limit="10"){		
		if(empty($aid)){
			$aid=1;
		}
		$activityConfig=$this->modelActivCategory->where('activity_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->order('order_sort DESC')->select();
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
				if($aid=='8'){
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->select();	
				}else{	
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->limit($limit)->select();
				}
			}
		}		
		return array('activity'=>$activity,'list'=>$itemList);
	}	
/*
 * 感恩节页面
 * */
 	public function thanksgivig(){
		$aid=$this->activityModel->where(array('Identification'=>'thanksgivig'))->getField('aid');
 		$res=$this->activity($aid,8);
		$j=0;
		for($i=0;$i<16;$i=$i+4){
			$list[$j]['cats']=array_slice($res['activity'], $i,4,true);
			$list[$j]['list']=array_slice($res['list'], $i,4,true);
			$j++;
		}
		$this->assign('list',$list);
		$this->display('Promotion/Thanksgivig/thanksgivig');
 	}
/*
 * 礼包、多商品活动公用活动方法
 * */
	public function getThisActivityInfo($Identification,$itemField,$catField){
		if(empty($Identification)){
			$this->error("请写入活动标识！");
		}
		if(empty($itemField)){
			$itemField='aitem_id,item_id,activity_config_id,item_name,price,shop_price,market_price,item_img';
		}
		if(empty($catField)){
			$catField='cat_content,activity_config_id';
		}
		$aid=$this->activityModel->where(array('Identification'=>$Identification))->getField('aid');
		if(!empty($aid)){
			$list=$this->modelActivCategory->where(array('aid'=>$aid))->order('order_sort desc')->field($catField)->select();
		}
		foreach($list as $key=>$value){
			$activityConfIds[]=$value['activity_config_id'];
		}
		if(!empty($activityConfIds)){
			$condition=array(
				'activity_config_id'=>array('in',$activityConfIds),
				'aid'=>$aid
			);
			$detail=$this->modelActivityItem->where($condition)->field($itemField)->select();
			foreach($list as $key=>$value){
				foreach($detail as $keys=>$values){
					if($value['activity_config_id']==$values['activity_config_id']){
						$list[$key]['items'][]=$values;
					}
				}
			}		
		}
		return $list;
	}
/*
 * 抢年货页面活动
 * */
	public function holidayItems(){
		$list=$this->getThisActivityInfo('holidayItems');
		$this->assign('comId',$this->comId);		
		$this->assign('list',$list);
		$this->display('Promotion/holidayItems/index');		
	}
 	//订单确认页
	public function orderPage(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Sign/index");
			exit;			
		}
		$aitemId=I('get.aitemId',0,'trim');
		$itemInfo=$this->dActivity->getAItemInfo(array('aitem_id'=>$aitemId));
		if($itemInfo){
			//一元购大米活动  ---校验资格
			$Identification=$this->activityModel->where(array('aid'=>$itemInfo['aid']))->getField('Identification');
			if($Identification=="oneBuyRice"){
				if(!$this->checkBuyRice()){
					//符合资格	
					$this->error("您暂不享有领取资格，请先完成付款订单后购买！");
				}				
			}
		    //得到所有收货地址信息 开始	
	       	$whereAddr=array(
	        	'user_id'=>$this->uid,
	        );
		    //查询用户默认地址
	        $userAddressInfo = $this->modelOrder->getUserAddress($this->uid);
	        if (!$userAddressInfo) {
	        	$this->error('请完善您的地址信息,页面跳转中...',U('Home/UserCenter/addAddress'));
	        }			
	    	$addrList=$this->modelOrder->getAddressList($whereAddr);
	    	if($addrList){
	    		foreach($addrList as $key=>$value){
	    			$addrArr=explode(':',$value['area']);
						$addrList[$key]['area']=rtrim($addrArr[0],'/');
						$addrList[$key]['areaID']=rtrim($addrArr[1],'/');
	    		}
	    		$this->assign('addrList',$addrList);
	    	}	
			$itemStoreId=json_decode($itemInfo['item_info'],TRUE);	
			if($itemStoreId){
				foreach($itemStoreId as $key=>$value){
					$skuIds[]=$value['sku_id'];
				}
				if(!empty($skuIds)){
					$skuStore=$this->dActivity->getItemStore(array('sku_id'=>array('in',$skuIds)));
				}
				//判断是否有货
				$itemInfo['store']=1;
				if(!empty($skuStore)){
					foreach($skuStore as $key=>$value){
						foreach($itemStoreId as $keys=>$values){
							if($value['sku_id']==$values['sku_id']){
								$noFreez=$value['store']-$value['freez'];
					            if ( $noFreez< 1 || $values['num'] >  $noFreez) {
									$itemInfo['store']=0;
									break;
								}	
							}
						}				
					}				
				}
			}else{
				$itemInfo['store']=1;
			}
			//总价格
			$itemInfo['total_fee']=$itemInfo['price']+$itemInfo['post_fee'];
			$this->assign('itemInfo',$itemInfo);
		}else{
			$this->error("暂无该活动商品！");
		}
		$this->display();
	}
    //提交订单
    public function creatOrder(){
        $remark = I('post.remark');//买家留言
        $aitemId = I('post.aitemId');
        $thisRes['log'] = array(
            'rel_id' =>1,
            'op_name' =>"系统",
            'op_role' =>"system",
            'behavior' =>"error",
            'log_text' => '获取活动商品Id失败',
            'log_time' =>time()
        	);   
        //查询用户comid 
        $userDepositInfo = $this->getUserDeposit($this->uid); 
		$userDepositInfo = $this->modelDeposit->where('user_id ='.$this->uid)->find();			     
		if(empty($this->uid) || empty($userDepositInfo)){
			$thisRes['log']['log_text']="uid为空";
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败，错误信息：无法获取用户信息！");	
		}       
        //检查是否已经选择收货地址
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        //判断地址是否准确
        if($this->checkJdAddress(trim(strstr($addressInfo['area'],':'),":")) == false){
			$thisRes['log']['behavior']="error";
			$thisRes['log']['log_text']="错误信息：地址信息有误";
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败，错误信息：地址信息有误！");
        }
        $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));	
   		$itemInfo=$this->dActivity->getAItemInfo(array('aitem_id'=>$aitemId));
        if (!$aitemId || empty($itemInfo)) {
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败，错误信息：获取活动商品Id失败！");
       	} 
		//  ---校验资格
		$Identification=$this->activityModel->where(array('aid'=>$itemInfo['aid']))->getField('Identification');
		if($Identification=="oneBuyRice"){
			//一元购大米活动
			if(!$this->checkBuyRice()){
				//符合资格	
				$this->error("您暂不享有领取资格，请先完成付款订单后购买！");
			}				
		}else if($Identification=="christmasApple"){
			$checkRes=$this->checkBuyApple();
			if($checkRes['code']==0){
				//不满足资格
				$this->error($checkRes['msg']);
			}			
		}		
		//判断是否有货
		$itemStoreId=json_decode($itemInfo['item_info'],TRUE);	
		if($itemStoreId){
			foreach($itemStoreId as $key=>$value){
				$skuIds[]=$value['sku_id'];
			}
			if(!empty($skuIds)){
				$skuStore=$this->dActivity->getItemStore(array('sku_id'=>array('in',$skuIds)));
			}
			//判断是否有货
			if(!empty($skuStore)){
				foreach($skuStore as $key=>$value){
					foreach($itemStoreId as $keys=>$values){
						if($value['sku_id']==$values['sku_id']){
							$noFreez=$value['store']-$value['freez'];
				            if ( $noFreez< 1 || $values['num'] >  $noFreez) {
								$this->error("生成订单失败，商品无货！");
								break;
							}	
						}
					}				
				}				
			}
		}		
		//需要时新建个方法校验是否有货---预留位置
		//总价格
		$itemInfo['total_fee']=$itemInfo['price']+$itemInfo['post_fee'];	
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$this->uid;//订单编号
        $data['aid'] = $itemInfo['aid'];//活动id
        $data['activity_name'] = $itemInfo['activity_name'];//活动名称
        $data['title'] = $itemInfo['item_name'];//订单标题
        $data['item_id'] = $itemInfo['aitem_id'];//商品关联ID
        $data['com_id'] = $this->comId;//企业ID
        $data['user_id'] = $this->uid;//会员id
        $data['account'] = $this->userName;//用户账号
        $data['item_num'] = 1;//订单商品数量
        $data['send_num'] = 0;//发货数量
        $data['total_fee'] = $itemInfo['total_fee'];//订单总价
        $data['post_fee'] = $itemInfo['post_fee'];//邮费
        $data['payment'] = $itemInfo['total_fee'];//实际要支付的金额
        $data['receiver_name'] = $addressInfo['name'];//收货人姓名
        $data['receiver_state'] = $newTakeAddress[0];//收货人所在省份                        
        $data['receiver_city'] = $newTakeAddress[1];//收货人所在城市
        $data['receiver_district'] = $newTakeAddress[2];//收货人所在地区
        $data['receiver_address'] = $addressInfo['addr'];//收货人详细地址
        $data['receiver_zip'] = $addressInfo['zip'];//收货人邮编
        $data['receiver_mobile'] = $addressInfo['mobile'];//收货人手机号
        $data['receiver_phone'] = $addressInfo['tel'];//收货人电话
        $data['buyer_message'] = $remark;//买家留言
        if ($addressInfo['area']) {
            $areaIds = trim(strstr($addressInfo['area'],':'),":");
        }
       	$data['buyer_area'] = $areaIds;//买家地区ID
        $data['price'] = $itemInfo['price'];//商品价格
        $data['cost_price'] = $itemInfo['cost_price'];//商品成本价
        $data['item_img'] = $itemInfo['item_img'];//商品图片
        $data['creat_time'] = time();//创建时间
        $res = $this->atradeModel->data($data)->add();
        if (!$res) {
			$thisRes['log']['log_text']="生成订单失败";
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败！");				
        }
        //生成订单子表
        $da['atid'] = $data['atid'];
        $da['aid'] = $itemInfo['aid'];//活动id
        $da['user_id'] = $this->uid;//会员id
        $da['aitem_id'] = $itemInfo['aitem_id'];//活动id
        $da['item_id'] = $itemInfo['item_id'];//商品关联ID
        $da['item_name'] = $itemInfo['item_name'];//商品名称
        $da['price'] = $itemInfo['price'];//商品价格
        $da['cost_price'] = $itemInfo['cost_price'];//商品成本价
        $da['shop_price'] = $itemInfo['shop_price'];//商城价格
        $da['post_fee'] =  $itemInfo['post_fee'];//邮费
        $da['item_img'] = $itemInfo['item_img'];//商品图片
        $da['weight'] = $itemInfo['weight'];
        $orderRes = $this->aorderModel->data($da)->add();
        if (!$orderRes) {
			$thisRes['log']['log_text']="子表生成订单失败";
            $this->orderLog($thisRes['log']);
			$this->error("子表生成订单失败！");						
        }
		//预占库存---减库存
		//需要时新建个方法预占库存---减库存--预留位置
		//预占库存---减库存
		if($itemInfo['item_info']){
			$itemStoreId=json_decode($itemInfo['item_info'],TRUE);	
			if(!empty($itemStoreId)){
				foreach($itemStoreId as $key=>$value){
					if(!empty($value['item_id']) && !empty($value['sku_id']) && !empty($value['num'])){
				        //下单增加购买数量
				        $itemCount = $this->modelItemCount->where(array('item_id'=>$value['item_id']))->setInc('buy_count',$value['num']);
				        //下单预占item库存
				        $resItemStore = $this->modelItemStore->where(array('item_id'=>$value['item_id']))->setInc('freez',$value['num']);
				        $resStore = $this->modelItemSkuStore->where(array('sku_id'=>$value['sku_id']))->setInc('freez',$value['num']);
				        if (!$resStore && !$itemCount && !$resItemStore) {
							$thisRes['log']['log_text']="错误信息：预占库存、增加下单数量失败";
				            $this->orderLog($thisRes['log']);
							$this->error("生成订单失败：预占库存、增加下单数量失败！");	
							exit;			
				        }			
					}
				}		
			}
		}
		//生成支付单号
        $paymentId = $this->creatPayments($data['atid']);
        if (!$paymentId) {
			$thisRes['log']['log_text']="支付单生成失败";
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败：支付单生成失败！");				
        }
        //调用支付单生成接口
        redirect(__APP__.'/Pay/activityPay/paymentid/'.$paymentId);  
		    
    }
	public function getUserDeposit($uid){
		if (empty($uid)) {
			return false;
		}
		$userDepositInfo = M('sysuser_user_deposit')->where('user_id ='.$uid)->find();
		if ($userDepositInfo) {
			return $userDepositInfo;
		}else{
			return false;
		}
	}
	//校验是否有四级地址，防止京东商品无法下单
    public function checkJdAddress($jdIds){
        if (empty($jdIds)) {
            return false;
        }
        $addressArr =  explode("/",trim($jdIds,'/'));
        if (!is_array($addressArr)) {
            return false;
        }
        if (count($addressArr) < 4) {
            $count = $this->modelArea->where('jd_pid='.$addressArr[2])->find();
            if ($count) {
                //有四级地址，返回false
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }        
    } 
     //生成支付单号
    public function creatPayments($atid){
    	$atid = array($atid);
        $thisRes = array();
        if ($atid) {
            //获取订单表信息
            $where['atid']  = array('in',implode(',', $atid));
            $tradeList = $this->atradeModel->where($where)->select();

            $toallPrice = 0 ;
            if ($tradeList) {
                foreach ($tradeList as $key => $value) {
                    $toallPrice += $value['total_fee'];
                }
            }
            //插入支付表
            $data['payment_id'] = date(YmdHis).$this->uid.'1';//支付单号
            $data['money'] = floatval($toallPrice);//需要支付的金额
            $data['cur_money'] = 0;//支付货币金额
            $data['user_id'] = $this->uid;
            $data['user_name'] = $this->userName;
            $data['op_name'] = $this->userName; //操作员
            $data['bank'] = '预存款';//收款银行
            $data['pay_account'] ='用户';//支付账号
            $data['created_time'] = time();
            $result = $this->paymentsModel->data($data)->add();
            if ($result) {
                foreach ($atid as $key => $value) {
                    $da['payment_id'] = $data['payment_id'];//主支付单编号
                    $da['tid'] = $value;
                    if ($tradeList) {
                        $payPrice = 0 ;
                        foreach ($tradeList as $ke => $val) {
                            if ($val['atid'] == $value) {
                                $payPrice = $val['total_fee'];
                            }
                        }
                    }
                    $da['payment'] = $payPrice;
                    $da['user_id'] = $this->uid;
                    $da['created_time'] = time();  
					$da['modified_time'] = time();
                    $result = $this->tradePaybillModel->data($da)->add();                  
                    if ($result) {
                        //插入数据成功
                        $thisRes = $data['payment_id'];
                        
                    }else{
                        //插入数据失败
                        $thisRes = 0;
                    }
                }
            }else{
                //支付主表插入错误
                        $thisRes = 0;
            }            
        }else{
            //tid为空
                        $thisRes = 0;
        }
        return $thisRes;
    }
/*
 * 九阳活动页
 * */
	public function Joyoung(){
		
		$this->display('Promotion/Joyoung/index');
	}
/*
 * 双旦福利季、苹果0元购
 * */
	public function Christmas(){
		
		$this->display('Promotion/Christmas/index');
	}
/*
 * 双旦福利季、苹果0元购单击抢购时校验是否有资格
 * */ 
 	public function appleZeroQualification(){
 		$uid=$this->uid;
		if(empty($uid)){
			$this->ajaxReturn(array(0,'您还未登录,请先登录！'));		
		}
		$checkRes=$this->checkBuyApple();
		if($checkRes['code']==0){
			//不满足资格
			$this->ajaxReturn(array(0,$checkRes['msg']));		
		}else if($checkRes['code']==1){
			//符合资格
			$aid=$this->activityModel->where(array('Identification'=>'christmasApple'))->getField('aid');
			$aitemId=$this->modelActivityItem->where(array('aid'=>$aid))->getField('aitem_id');	
			if($aitemId){
				$this->ajaxReturn(array(1,$checkRes['msg'],C('TMPL_PARSE_STRING.__LISHE_URL__').'/shop.php/promotion/orderPage/aitemId/'.$aitemId));		
			}
		}
 	}
	
}