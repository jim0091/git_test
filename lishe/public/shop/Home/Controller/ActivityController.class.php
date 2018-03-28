<?php
namespace Home\Controller;
class ActivityController extends CommonController {
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
		$this->assign('public',C('ROOT').'Show/Home/View/Activity/');
		$this->assign('index',$this->index);
	}
	public function activity($aid){
		if(empty($aid)){
			$aid=1;
		}
		$activityConfig=$this->modelActivityConfig->where('activity_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->order('order_sort DESC')->select();
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
				if($aid=='4'){	
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->select();
				}else{
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->limit(10)->select();

				}
			}
		}		
		return array('activity'=>$activity,'list'=>$itemList);
	}

	//列表页面到详细页面
	public function listDetailActivity(){
		$aid=I('get.aid','','trim');
		if(empty($aid)){
			$aid=14; //aid不存在
		}
		// echo $aid;
		if($aid){
			$actCateInfo=$this->comActivityCate->where('aid='.$aid)->field('activity_config_id,aid,cat_banner,cat_name')->select();
			if($actCateInfo){
				// foreach($actCateInfo as $k1=>$v1){
				// 	$cateArr[$k1]=$v1['activity_config_id'];
				// }
				$this->assign('actCateInfo',$actCateInfo);
				// dump($actCateInfo);
				$this->assign('cat_name',$actCateInfo[0]['cat_name']);
			}
		 
				$cateItemInfo=$this->comActivityCateItem->where('aid='.$aid)->select();
				if($cateItemInfo){
					foreach($cateItemInfo as $k=>$v){
						$cateItemArr[$k]=trim($v['recommend_id']);
					}
					$info=$this->modelItem->table('sysitem_item a,company_activity_category_item b')->where('a.item_id=b.recommend_id and a.item_id in('.implode(',',$cateItemArr).')')->field('b.cate_id,a.item_id,a.title,a.image_default_id,a.price,a.mkt_price,a.flag')->select();
					if($info){
						// var_dump($info);
						$this->assign('info',$info);
					}
				}
			


		}
		if($aid==14){
			$this->display('Activity/nationalDay/bags/index');
		}elseif($aid==15){
			$this->display('Activity/nationalDay/kitchen/index');
		}elseif($aid==16){
			$this->display('Activity/nationalDay/snacks/index');
		}
		
	}
		
	//专题活动页面
	public function index(){
		$aid=intval($_GET['aid']);
		$activity=$this->activity($aid);
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->display();
	}
	


    
    public function oil(){
		$aid=I('get.aid','','trim');
		if(empty($aid)){
			$aid=8;
		}

		$activity=$this->activity($aid);
		$this->assign('list',$activity['list']);
		$this->display('Activity/oil/index');
	}
	

    //提交订单日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    }


	//提交订单
    public function addUserOrder(){    	
        $item_id = intval(I('post.item_id'));//商品id 
        //检查用户是否已经购买该商品
        $count = $this->atradeModel->where('user_id ='.$this->uid." and aid=0")->count();
        if ($count) {
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"每个用户ID仅限参与一次0元好礼活动！\");\r\n"; 
			echo "window.location.href='haiHeTaoActivity/aid/4'\r\n"; 
			echo "</script>";
			exit();
        }
        //检查是否已经选择收货地址
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"您的配货地址没有选中，请重新选择！\");\r\n"; 
			echo "window.location.href='haiHeTaoActivity/aid/4'\r\n"; 
			echo "</script>";
			exit(); 
		}
        
        $remark = I('post.remark');//买家留言
        $num = intval(I('post.num'));//商品数量

        if (!$item_id) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品Id失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品Id失败！"));
            exit();
       	} 

   	    $itemInfo = $this->modelItem->where('item_id='.$item_id)->find();
      	if (!$itemInfo) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品失败！"));
            exit();
      	} 
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$key.$this->uid;//订单编号
        $data['aid'] = 0;//活动id
        $data['activity_name'] = "海核淘0元购";//活动名称
        $data['title'] = $itemInfo['title'];//订单标题
        $data['item_id'] = $itemInfo['item_id'];//商品关联ID
        $data['com_id'] = $this->comId;//企业ID
        $data['user_id'] = $this->uid;//会员id
        $data['account'] = $this->userName;//用户账号
        $data['item_num'] = $num;//订单商品数量
        $data['send_num'] = 0;//发货数量
        $data['total_fee'] = 8;//订单总价
        $data['post_fee'] = 8;//邮费
        $data['payment'] = $data['total_fee'];//实际要支付的金额
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
        $data['item_img'] = $itemInfo['image_default_id'];//商品图片
        $data['creat_time'] = time();//创建时间

        $res = $this->atradeModel->data($data)->add();
        if (!$res) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '主表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

        //生成订单子表
        $da['atid'] = $data['atid'];
        $da['aitem_id'] = 0;//活动id
        $da['item_id'] = $itemInfo['item_id'];//商品关联ID
        $da['price'] = $itemInfo['price'];//商品价格
        $da['cost_price'] = $itemInfo['cost_price'];//商品成本价
        $da['post_fee'] = 8;//邮费
        $da['item_img'] = $itemInfo['image_default_id'];//商品图片
        $da['weight'] = $itemInfo['weight'];
        $orderRes = $this->aorderModel->data($da)->add();
        if (!$orderRes) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '子表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

		if (!$data['atid']) {
			  	$thisRes['log'] = array(
	                'rel_id' =>1,
	                'op_name' =>"系统",
	                'op_role' =>"system",
	                'behavior' =>"cancel",
	                'log_text' => '缺少tid无法生成支付数据！',
	                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付数据生成失败！"));
            exit();
		}
		//生成支付数据
        $paymentId = $this->creatPayments($data['atid']);;
        if (!$paymentId) {
		  	$thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '支付单生成失败！',
                'log_time' =>time()
        	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付单生成失败！"));
            exit();
        }

        //积分支付
        $payRes = $this->operPay($paymentId);

        if ($payRes) {
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"订单提交成功！\");\r\n"; 
			echo "window.location.href=\"/business/user.php/Order/orderList\"\r\n"; 
			echo "</script>";
			exit();         	
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n";
			echo " alert(\"支付失败，积分不足！\");\r\n"; 
			echo "window.location.href=\"/business/index.php/Activity/orderList\"\r\n";  
			echo "</script>";
			exit();
        }
        
    }


    //生成支付数据
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


    //支付操作
    public function operPay($paymentid){
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //用户登录信息表
        $userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->find();
        
        //支付子表
        $paymentBillInfo = $this->tradePaybillModel->where('payment_id = '.$paymentid)->select();
        $tidarry = array();
        foreach ($paymentBillInfo as $key => $value) {
            $tidarry[$key] = $value['tid'];
        }

        //检查是否已经支付
        if ($paymentInfo['status'] =='succ') {
            //已经支付不可再次支付
            return json_encode(array(2,"该订单已经支付！"));
        }else{
        	if($userDepositInfo['comId']=='1467166836740'){
				$payRes = $this->syncEcardOrder($userAccountInfo['mobile'],$paymentInfo['money'],$paymentid,implode(',',$tidarry));
				$payType='e-card';
			}else{
				$payRes = $this->dedect($this->uid,$userAccountInfo['mobile'],$paymentid,$paymentInfo['money'],$paymentInfo['memo']);
				$payType='deposit';
			}
            
            if($payRes['result']==100){
                if($payRes['errcode']>0){
                    //支付失败，日志表
                    $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>'支付失败：'.$payRes['msg'],'logtime'=>time());
                    $this->userDataDepositLogModel->data($logdata)->add();
                    return false;
                }else{
                    //支付成功，更新本地积分
                    $this->userDepositModel->where('user_id ='.$this->uid)->setDec('deposit',$paymentInfo['money']);
                    $this->userDepositModel->where('user_id ='.$this->uid)->setDec('balance',$paymentInfo['money']*100);
                    $this->userDepositModel->where('user_id ='.$this->uid)->setDec('commonAmount',$paymentInfo['money']*100);

                    //支付流水号
                    $where['atid'] = array('in',implode(',',$tidarry)); 
                    $trdeDate = array(
                    	'transno'=>$payRes['data']['info']['transno'],
                    	'status'=>"WAIT_SELLER_SEND_GOODS",
                    	'payed_fee'=>$paymentInfo['money'],
                    	'pay_type'=>$payType,
                    	'pay_time'=>time()
                    );
                    $this->atradeModel->where($where)->data($trdeDate)->save();

                    //更新支付主表
                    $zdata['cur_money'] = $paymentInfo['money'];
                    $zdata['pay_type'] = 'online';
                    $zdata['pay_app_id'] = $payType;
                    $zdata['payed_time'] = time();
                    $zdata['status'] = 'succ';
                    $zdata['trade_no'] = $payRes['data']['info']['transno'];
                    $zres = $this->paymentsModel->where('payment_id ='.$paymentid)->data($zdata)->save();

                    //更新支付副表
                    $fda['status'] = 'succ';
                    $fda['payed_time'] = time();                    
                    $fda['modified_time'] = time();
                    $fres = $this->tradePaybillModel->where('payment_id ='.$paymentid)->data($fda)->save();

			    	
                    //日志表
                    $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>$paymentInfo['memo'],'logtime'=>time());
                    $this->userDataDepositLogModel->data($logdata)->add();
                    return true;
                
                }

            }else{
                //接口通讯失败，日志表                        
                $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>$payRes['msg'],'logtime'=>time());
                $this->userDataDepositLogModel->data($logdata)->add();                        
                return false;                
            }
        }
    }
    
    //E卡通支付接口
    public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}
    public function syncEcardOrder($mobile,$totalFee,$paymentId,$atid){
    	//日志
		$log=array(
			'payment_id'=>$paymentId,
			'tid'=>$atid,
			'sync_order_id'=>'',
			'log_type'=>'syncGd10086Order',
			'code'=>100,
			'partener'=>'gd10086',
			'modified_time'=>time()
		);
		$postData=json_encode($_POST);
		//记录开始支付日志
		$log['code']=1;
		$log['detail']='开始支付,post:'.$postData;
		$this->makeSqlLog($log);
		
    	//检查是否有权限支付
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
		$sign=md5('appKey='.$appKey.'&mobileNo='.$mobile.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'mobileNo'=>$mobile,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$ret=json_decode($return,true);
    	
    	$log['code']=$ret['code'];
    	$log['detail']='用户信息,return:'.$return;
		$this->makeSqlLog($log);    	
    	
    	if($ret['code']==100){
	    	//推送订单
	    	$empCode=$ret['data']['empCode'];//员工编号
	    	$createTime=date('Y-m-d H:i:s');
	    	$url=C('API_AOSERVER').'card/insertOrderData';
	    	$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderMoney='.$totalFee.'&orderNum='.$paymentId.'&orderTime='.$createTime.'&orderTotalMoney='.$totalFee.'&orderType=1'.C('API_AOSERVER_KEY'));
			$orderPost=array(
	    		'appKey'=>$appKey,
	    		'empCode'=>$empCode,
	    		'orderMoney'=>$totalFee,
	    		'orderNum'=>$paymentId,
	    		'orderTime'=>$createTime,
	    		'orderTotalMoney'=>$totalFee,
	    		'orderType'=>1,
	    		'sign'=>$sign
	    	);
	    	$returns=$this->accreditPost($url,json_encode($orderPost),$user,$password);
	    	$rets=json_decode($returns,true);
	    	$log['code']=$rets['code'];
    		$log['detail']='支付信息,data:'.json_encode($orderPost).' return:'.$returns;
			$this->makeSqlLog($log);
			$return=array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>$rets['msg']
			);
			if($rets['code']!=100){
				$return['errcode']=100;
			}else{
				$return['data']['info']['transno']=$paymentId;
			}
	    	return $return;
	    }
    }

    /**
     * 会员扣费接口
     *
     * @params userId int 会员id
     * @params operator string 操作用户的账号/手机号码
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function dedect($userId, $operator, $orderNumber, $fee, $memo){
        $url = C('API').'mallPoints/payOrder';
        $payFee=$fee*100;
        $sign=md5('orderno='.$orderNumber.'&phoneNum='.$operator.'&pointsAmount='.$payFee.'&pointsType=1lishe_md5_key_56e057f20f883e');

        $data=array(
            'phoneNum'=>$operator,
            'orderno'=>$orderNumber,
            'pointsAmount'=>$payFee,
            'pointsType'=>1,
            'sign'=>$sign
        );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt ( $ch, CURLOPT_POST, 1 ); //启用POST提交
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        $file_contents = curl_exec ( $ch );
        curl_close ( $ch );
        return json_decode($file_contents,TRUE);
    }

    //提交订单 20160908  
     public function addUserOrderInfo(){    	
        $aitem_id = intval(I('post.aitem_id'));//商品id
        $curr_price=intval(I('post.curr_price',0,'trim'));
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"您的配货地址没有选中，请重新选择！\");\r\n"; 
			// echo "window.location.href='/business/index.php/Haihetao/order'\r\n"; 
			echo "window.history.back();";
			echo "</script>";
			exit(); 
		}

        $remark = I('post.remark');//买家留言
        $num = intval(I('post.num'));//商品数量

        if (!$aitem_id) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品Id失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品Id失败！"));
            exit();
       	} 

   	    $aItemInfo = $this->modelActivityItem->where('aitem_id='.$aitem_id)->find();
   	    if(intval($aItemInfo['store']) == 0){
   	    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"该商品今天已抢完！\");\r\n"; 
			// echo "window.location.href='/business/index.php/Haihetao/order'\r\n"; 
			echo "window.history.back();";
			echo "</script>";
			exit(); 
   	    }
   	    // dump($aItemInfo);
   	    // exit;
      	if (!$aItemInfo) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品失败！"));
            exit();
      	} 
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$key.$this->uid;//订单编号
        $data['aid'] = $aitem_id;//活动id
        $data['activity_name'] = $aItemInfo['activity_name'];//活动名称
        $data['title'] = $aItemInfo['item_name'];//订单标题
        $data['item_id'] = $aItemInfo['item_id'];//商品关联ID
        $data['com_id'] = $this->comId;//企业ID
        $data['user_id'] = $this->uid;//会员id
        $data['account'] = $this->userName;//用户账号
        $data['item_num'] = $num;//订单商品数量
        $data['send_num'] = 0;//发货数量
        $data['total_fee'] = $curr_price*$num+$aItemInfo['post_fee'];//订单总价
        $data['post_fee'] = $aItemInfo['post_fee'];//邮费
        $data['payment'] = $data['total_fee'];//实际要支付的金额
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
        $data['price'] = $aItemInfo['price'];//商品价格
        $data['cost_price'] = $aItemInfo['cost_price'];//商品成本价
        $data['item_img'] = $aItemInfo['item_img'];//商品图片
        $data['creat_time'] = time();//创建时间

        $res = $this->atradeModel->data($data)->add();
        if (!$res) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '主表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

        //生成订单子表
        $da['atid'] = $data['atid'];
        $da['aitem_id'] = $aitem_id;//活动id
        $da['item_id'] = $aItemInfo['item_id'];//商品关联ID
        $da['price'] = $aItemInfo['price'];//商品价格
        $da['cost_price'] = $aItemInfo['cost_price'];//商品成本价
        $da['post_fee'] = $aItemInfo['post_fee'];
        $da['item_img'] = $aItemInfo['item_img'];//商品图片
        $da['weight'] = $aItemInfo['weight'];
        $orderRes = $this->aorderModel->data($da)->add();
        if (!$orderRes) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '子表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

		if (!$data['atid']) {
			  	$thisRes['log'] = array(
	                'rel_id' =>1,
	                'op_name' =>"系统",
	                'op_role' =>"system",
	                'behavior' =>"cancel",
	                'log_text' => '缺少tid无法生成支付数据！',
	                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付数据生成失败！"));
            exit();
		}
		//生成支付数据
        $paymentId = $this->creatPayments($data['atid']);
        if (!$paymentId) {
		  	$thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '支付单生成失败！',
                'log_time' =>time()
        	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付单生成失败！"));
            exit();
        }

        //积分支付
        $payRes = $this->operPay($paymentId);

        if ($payRes) {
        	//提交成功，库存减一
        	$this->modelActivityItem->where('aitem_id='.$aitem_id)->setDec('store',1);
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"订单提交成功！\");\r\n"; 
			echo "window.location.href=\"/member-index.html\"\r\n"; 
			echo "</script>";
			exit();         	
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n";
			echo " alert(\"支付失败，积分不足！\");\r\n"; 
			// echo "window.location.href=\"/business/index.php/Haihetao/moonActivity\"\r\n";
			echo "window.history.back()";   
			echo "</script>";
			exit();
        }
        
    }

    //20160908  开始
	
    //富安娜中秋专场
    public function fuanna(){
    	$this->display('Activity/fuanna/index');
    }
	//元旦礼舍推荐
	public function promote(){
		
		$this->display('Activity/newYear/index');
	}
	//加入购物车
	public function addCart(){
		if(empty($this->uid)){
			var_dump("未登入");
		}
		$aitemId=I('aitemId');
		if($aitemId){
			
		}
	}
	//订单确认页
	//提交订单页面 20160811 开始
	public function orderPage(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Sign/index");
			exit;			
		}
		$aitemId=I('get.aitemId',0,'trim');
		$itemInfo=$this->dActivity->getAItemInfo(array('aitem_id'=>$aitemId));
		if($itemInfo){
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
			$itemInfo['itemIds']=explode(',', $itemInfo['item_ids']);
			//判断是否有货
			$moreNum=array('20382'=>2,'20383'=>3,'19346'=>2);
			$skuStore=$this->dActivity->getItemStore(array('sku_id'=>array('in',$itemInfo['sku_ids'])));
			$itemInfo['store']=1;
			foreach($skuStore as $key=>$value){
				$num=1;
				if(array_key_exists(intval($value['sku_id']), $moreNum)){
					$num=$moreNum[intval($value['sku_id'])];
				}
				$noFreez=$value['store']-$value['freez'];
	            if ( $noFreez< 1 || $num >  $noFreez) { 			
					$itemInfo['store']=0;
					break;
				}
			}			
			//邮费
			if($itemInfo['price']>50){
				$itemInfo['post_fee']=0;
			}
			//总价格
			$itemInfo['total_fee']=$itemInfo['price']+$itemInfo['post_fee'];
			$this->assign('itemInfo',$itemInfo);
		}else{
			$this->error();
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
		//判断是否有货
		$moreNum=array('20382'=>2,'20383'=>3,'19346'=>2);
		$skuStore=$this->dActivity->getItemStore(array('sku_id'=>array('in',$itemInfo['sku_ids'])));
		$itemInfo['store']=1;
		foreach($skuStore as $key=>$value){
			$num=1;
			if(array_key_exists(intval($value['sku_id']), $moreNum)){
				$num=$moreNum[intval($value['sku_id'])];
			}
			$noFreez=$value['store']-$value['freez'];
            if ( $noFreez< 1 || $num >  $noFreez) { 			
				$thisRes['log']['log_text']="套餐中存在商品无货";
	            $this->orderLog($thisRes['log']);
				$this->error("生成订单失败，错误信息：套餐中存在商品无货！");				
				break;
			}
		}
		//邮费
		if($itemInfo['price']>50){
			$itemInfo['post_fee']=0;
		}
		//总价格
		$itemInfo['total_fee']=$itemInfo['price']+$itemInfo['post_fee'];	
		$itemInfo['itemIds']=explode(',', $itemInfo['item_ids']);//预占库存---减库存
		$itemInfo['skuIds']=explode(',', $itemInfo['sku_ids']);
		if(empty($itemInfo['itemIds']) || empty($itemInfo['itemIds'])){
			$thisRes['log']['log_text']="商品信息不正确";
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败：商品信息不正确！");				
		}	
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$key.$this->uid;//订单编号
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
		foreach($itemInfo['itemIds'] as $key=>$value){
			$num=1;
			if(array_key_exists(intval($itemInfo['skuIds'][$key]),$moreNum)){
				$num=$moreNum[intval($itemInfo['skuIds'][$key])];
			}
	        //下单增加购买数量
	        $itemCount = $this->modelItemCount->where(array('item_id'=>$value))->setInc('buy_count',$num);
	        //下单预占item库存
	        $resItemStore = $this->modelItemStore->where(array('item_id'=>$value))->setInc('freez',$num);
		}
		foreach($itemInfo['skuIds'] as $key=>$value){
	        //下单预占sku库存
	        $num=1;
			if(array_key_exists(intval($value),$moreNum)){
				$num=$moreNum[intval($value)];
			}
	        $resStore = $this->modelItemSkuStore->where(array('sku_id'=>$value))->setInc('freez',$num);
		}	
        if (!$resStore && !$itemCount && !$resItemStore) {
			$thisRes['log']['log_text']="错误信息：预占库存、增加下单数量失败";
            $this->orderLog($thisRes['log']);
			$this->error("生成订单失败：预占库存、增加下单数量失败！");				
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
    //订单列表
    public function orderList(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Sign/index");
			exit;			
		}    	
		$size=5;
    	$number = $this->atradeModel->where('user_id ='.$this->uid)->count();
		$page = new \Think\Page($number,$size);
		$rollPage = 5; 
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'<<');
		$page -> setConfig('next' ,'>>');
		$start = $page -> firstRow;  
		$pagesize = $page -> listRows;	
		$limit = "$start , $pagesize";				
    	$orderList = $this->atradeModel->where('user_id ='.$this->uid)->order('creat_time desc')->limit($limit)->select();
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass); 
		$this -> assign('pagestr',$pagestr);			
    	$this->assign('orderList',$orderList);
    	$this->display();
    }	
	//元旦更多
	public function more(){
		$activityConfId=I('activityConfId');
		if($activityConfId){
			$res=$this->dActivity->getACategoryInfo(array('activity_config_id'=>$activityConfId),'item_ids');
		}
		if(!empty($res['item_ids'])){
			$size=25;
			$condition='i.item_id IN('.$res['item_ids'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
			$number=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
			$page = new \Think\Page($number,$size);
			$rollPage = 5; 
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page -> firstRow;  
			$pagesize = $page -> listRows;
			$itemIdArr=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->order('i.flag ASC,i.cat_id DESC,i.profit_rate DESC')->select();
			foreach($itemIdArr as $key=>$value){
				$itemId[]=$value['item_id'];
			}
			$itemId=array_slice($itemId,$start,$pagesize);
			$condition='item_id IN('.implode(',',$itemId).')';
			$itemList=$this->modelItem->field('item_id,title,image_default_id,price,mkt_price,flag')->where($condition)->select();
			$style = "pageos";
			$onclass = "pageon";
			$pagestr = $page -> show($style,$onclass); 
			$this -> assign('pagestr',$pagestr);					
		}
		$this->assign('activityConfId',$activityConfId);
		$this->assign('list',$itemList);
		$this->display('Activity/newYear/more');
	}		
/**
 * 居家惠生活，限量低价抢实惠
 * @author Zhangrui
 * 
 */
	public function BenefitLife(){
//		$facial = array(62012,62011,62019,62018); //面部护理
//		$oral   = array(18053,18177,18067,18049,18079,18077,18178,18038); //口腔护理
//		$line   = array(18173,18171,18166,18165,18164,18162,18161,18160,18159,18153,18157,51055,51031,51028); //洗护精选
//		$beauty = array(48516,48517,63267,63268,63269,46420,53744,48514); //滋润养颜
//		$home   = array(53192,53089,53075,53039,52964,62037,53515,8711); //居家精选
//		$itemIds = array_merge($facial,$oral,$line,$beauty,$home);
		$itemIds = array(18053,18079,18077,18171,18164,18161,18159,51055,51031,51028,18058,18035,18067,12577,17903,12591,18133,17927,18162,17895,17925,18075,17923,18094,12592,17904,17907,18055,18033,17918,17919,18036,17909,18020,18131,18073,17921,18066,17957,18177,18011,18065,18110,18132,17930,18108,18165,17929,18095,18166,17940,17916,17961,17998,18029,18061,18109,17962,18070,17999,18100,18044,18153,17952,18101,17938,17960,18072,18125,17897,17963,18017,17905,18023,18104,18107,18178,18009,18102,18103,17944,17920,17928,17956,17936,12578,17946,17958,18038,17894,18018,18040,18062,18019,18060,17943,17947,18064,17945,17997,18014,18016,18052,62297,62298,62300,17901,18027,18173,804,62299,18057,18056,17908,869,17914,909,50571,18013,18043,18078,18012,18096,18015,18160,808,817,834,18049,12589,18002);
		$map = array(
			'item_id' => array('in', $itemIds)
		);
		$itemInfo = M('sysitem_item')->where($map)->order('price asc')->getField('item_id,title,price,image_default_id,shop_id');
		$ascItemIds = array_keys($itemInfo);
		$itemArr = array_chunk($ascItemIds, 6);
		$this->assign('itemArr',$itemArr);
		$this->assign('itemInfo',$itemInfo);
		$this->display('Activity/BenefitLife/home');
	} 
/**
 * 查询商品sku
 */ 
 	public function getItemSku(){
 		$itemId = I('itemId');
		$ret = array('code'=>0,'skuId'=>0);
		if(!is_numeric($itemId)){
			$this->ajaxReturn($ret);
		}
		$map = array(
			'item_id' => $itemId,
			'disable' => 0,
			'parent_sku_id' => 0
		);
		$skuInfo = M('sysitem_sku')->where($map)->getField('sku_id',TRUE);
		if(count($skuInfo) != 1){
			$this->ajaxReturn($ret);
		}
		$ret['code'] = 1;
		$ret['skuId'] = $skuInfo[0];
		$this->ajaxReturn($ret);		
 	}
/**
 *取出二维数组数组单值数组 
 * @author Zhangrui
 */
	private function arrGetField($arr,$field){
		if(empty($arr) || !is_array($arr)){
			return array();
		}
		$newArr = array();
		foreach($arr as $key => $val){
			$newArr[] = $val[$field];		
		}
		return array_unique($newArr);
		
	}	
/**
 * 端午活动页
 * 
 */
	public function duanwuDisc(){
		//活动
		$activIden = 'duanwuDisc';
		//专题
		$brandIden = 'duanwuBrand';//品质大牌
		$moreIden = 'duanwuMore';	//更多精彩	
		$idenArr = array($brandIden,$moreIden,$activIden);		
		$map = array(
			'Identification' => array('in', $idenArr)
		);			
		$activitys = M('company_activity')->where($map)->getField('aid,type,Identification');
		$brandAid = 0;
		$moreAid = 0;
		$seckillAid = 0;
		$groupAid = 0;
		$combinatAid = 0;
		foreach($activitys as $key=>$val){
			if($brandIden == $val['Identification'] && $val['type'] == 2){
				$brandAid = $key;//品质大牌
			}
			if($moreIden == $val['Identification'] && $val['type'] == 2){
				$moreAid = $key;//更多精彩
			}
			if($activIden == $val['Identification'] && $val['type'] == 1){
				$seckillAid = $key; //秒杀
			}
			if($activIden == $val['Identification'] && $val['type'] == 4){
				$groupAid = $key; //团购
			}
			if($activIden == $val['Identification'] && $val['type'] == 7){
				$combinatAid = $key; //组合购
			}			
		}
		$actiAids = array(
			'brand' => $brandAid,
			'more'  => $moreAid,
			'seckill' => $seckillAid,
			'group'   => $groupAid,
			'combinat' => $combinatAid
		);
		$aids = array_keys($activitys);
		if(empty($aids)){
			exit('无法获取活动...');
		}
		//活动分类
		$map = array(
			'aid' => array('in', $aids)
		);
		$field = 'activity_config_id,aid,start_time,end_time,achieve_num,max_join_num,cat_banner,recommend,type';
		$activityCats = M('company_activity_category')->where($map)->order('order_sort desc')->getField($field);
		if(empty($activityCats)){
			exit('无法获取分类...');
		}		
		$recommend = array();
		$itemIds = array();
		$actiConfIds = array();
		$catetorys = array();
		foreach($activityCats as $key=>$val){
			$recommend = explode(',', $val['recommend']);
			$activityCats[$key]['recommend'] = $recommend;
			$itemIds = array_merge($itemIds, $recommend);
			$actiConfIds[] = $val['activity_config_id'];
			$catetorys[$val['aid']][] = $val['activity_config_id'];
		}
		//专题商品
		if(!empty($itemIds)){
			$map = array(
				'item_id' => array('in', $itemIds)
			);
			$itemInfos = M('sysitem_item')->where($map)->getField('item_id,title,price,image_default_id');
		}
		//活动商品
		$map = array(
			'activity_config_id' => array('in', $actiConfIds),
			'disable' => 0
		);
		$field = 'aitem_id,activity_config_id,sku_id,item_name,item_info,item_id,price,shop_price,item_img,store,parent_sku_id';
		$aitems = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);
		//商品id 
		$checkItemIds = array();
		foreach($aitems as $key=>$val){
			$checkItemIds[] = $val['item_id'];
		}
		if(!$checkItemIds){
			exit('无商品ID');
		}
		//下架商品
		$map = array(
			'item_id' => array('in', $checkItemIds),
			'approve_status' => 'instock'
		);
		$instockItemIds = M('sysitem_item_status')->where($map)->getField('item_id', TRUE);
		//组合购商品
		$aitemInfos = array();
		$skuIds = array();
		$groupSkuIds = array();
		foreach($aitems as $key=>$val){
			if(!in_array($val['item_id'], $instockItemIds)){
				$aitemInfos[$val['activity_config_id']][] = $val;
			}
			$skuIds[] = $val['sku_id'];
			if(in_array($val['activity_config_id'], $catetorys[$groupAid])){
				//团购sku
				$groupSkuIds[] = $val['sku_id'];
			}
		}
		//查找剩余库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$skuStore = M('sysitem_sku_store')->where($map)->field('sku_id,store,freez')->select();
		foreach($skuStore as $key=>$val){
			$store[$val['sku_id']] = $val['store'] - $val['freez'];
		}
		//团购场次成功的团购次
		$map = array(
			'activity_id' => array('in', $catetorys[$groupAid]),
			'payed_fee' => array('gt', 0),
			'pay_time' => array('gt', 0)
		);
		$tids = M('systrade_trade')->where($map)->getField('tid',TRUE);
		if($tids){
			$map = array(
				'tid' => array('in', $tids),
				'sku_id' => array('in', $groupSkuIds),
			);
			$trade = M('systrade_order')->where($map)->getField('tid,sku_id');
			$groupTrade = array();
			foreach($trade as $key=>$val){
				$groupTrade[$val][] = $key;
			}
		}
		$this->assign('activitys', $activitys);
		$this->assign('actiAids', $actiAids);
		$this->assign('activityCats', $activityCats);
		$this->assign('catetorys', $catetorys);
		$this->assign('itemInfos', $itemInfos);
		$this->assign('aitemInfos', $aitemInfos);
		$this->assign('groupTrade', $groupTrade);
		$this->assign('store',$store);
		$this->display('Activity/duanwuTow/duanwuTowPC');
	}
/*
 * 组合购加入购物车
 * */
	public function aitemAddCart(){
		$aitemId = I('aitemId');
		$num = I('num', 1, 'intval');
		$ret = array('code'=>0,'msg'=>'Unknow');
		if(!$this->uid){
			$ret['msg'] = '请先登录';
			$ret['code'] = 3;
			$this->ajaxReturn($ret);
		}
		if(empty($aitemId) || !is_numeric($aitemId)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);
		}
		$map = array(
			'aitem_id' => $aitemId
		);
		$itemInfo = M('company_activity_item')->where($map)->getField('item_info');
		if(empty($itemInfo)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);			
		}
		$info = json_decode($itemInfo, TRUE);
		$skuIds = array();
		foreach($info as $key=>$val){
			$skuIds[] = $val['sku_id'];
		}
		if(empty($skuIds)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);			
		}
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$skuInfo = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
		$itemIds = array();
		foreach($skuInfo as $key=>$val){
			$itemIds[] = $val;
		}
		if(empty($itemIds)){
			$ret['msg'] = '组合购有误';
			$this->ajaxReturn($ret);			
		}	
		//判断上下架
		$map = array(
			'item_id' => array('in', $itemIds),
			'approve_status' => 'instock'
		);
		$isInstock = M('sysitem_item_status')->where($map)->getField('item_id');
		if($isInstock){
			$ret['msg'] = '存在商品已下架，无法加入购物车';
			$this->ajaxReturn($ret);				
		}
		//判断库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$minStore = M('sysitem_sku_store')->where($map)->min('store-freez');
		if($num > $minStore){
			$ret['msg'] = '库存不足，无法加入购物车';
			$this->ajaxReturn($ret);				
		}
		
		$cartModel = M('systrade_cart');
		$map = array(
			'user_id' => $this->uid,
			'sku_id'  => array('in', $skuIds)
		);
        $catIds = $cartModel->where($map)->getField('cart_id', TRUE);
		M()->startTrans();
		if($catIds){
			//加数量
			$map = array(
				'cart_id' => array('in', $catIds)
			);
            $res = $cartModel->where($map)->setInc('quantity',$num);
			if(!$res){
				M()->rollback();
				$ret['msg'] = '加入购物车失败';
				$this->ajaxReturn($ret);				
			}
		}else{
			//添加购物车
	        //查询商品详细信息
			$map = array(
				'item_id' => array('in', $itemIds)
			);        
	        $itemInfo = M('sysitem_item')->where($map)->getField('item_id,shop_id,title,image_default_id');
			foreach($skuInfo as $key=>$val){
		        $data['user_ident']= md5($this->uid);//会员ident,会员信息和session生成的唯一值
		        $data['user_id'] = $this->uid;//用户id
		        $data['shop_id'] = $itemInfo[$val]['shop_id'];//店铺ID
		        $data['obj_type'] = 'item';//购物车对象类型
		        $data['obj_ident'] = 'item_'.$skuInfo[$val]['sku_id'];//item_商品id
		        $data['item_id'] = $val;//商品id
		        $data['sku_id'] = $key;//sku的id
		        $data['title'] = $itemInfo[$val]['title'];//商品标题
		        $data['image_default_id'] = $itemInfo[$val]['image_default_id'];//商品默认图
		        $data['quantity'] = $num;//数量
		        $data['created_time'] = time();//加入购物车时间
		        $result = $cartModel->data($data)->add();	
				if(!$result){
					M()->rollback();
					$ret['msg'] = '加入购物车失败';
					$this->ajaxReturn($ret);				
				}	
			}			
		}
		M()->commit();
		$ret['code'] = 1;
		$ret['msg'] = '添加成功';
		$this->ajaxReturn($ret);			
		
	}
/**
 * 新人礼遇
 */
	public function newCourtesy(){
		$iden = 'newCourtesy';   //newCourtesy
		$map = array(
			'Identification' => $iden,
			'type' => array('in', array(2,7))
		);
		$aids = M('company_activity')->where($map)->getField('type,aid');
		if(empty($aids)){
			exit('无法获取活动...');
		}
		//活动分类
		$map = array(
			'aid' => array('in', $aids)
		);
		$field = 'activity_config_id,aid,start_time,end_time,cat_banner,cat_name,item_ids';
		$activityCats = M('company_activity_category')->where($map)->order('order_sort desc')->getField($field);
		if(empty($activityCats)){
			exit('无法获取分类...');
		}		
		$recommend = array();
		$itemIds = array();
		$actiConfIds = array();
		$catetorys = array();
		foreach($activityCats as $key=>$val){
			if($val['aid'] == $aids[7]){
				//组合购
				$actiConfIds[] = $val['activity_config_id'];
				$catetorys[] = $val['activity_config_id'];
			}else if($val['aid'] == $aids[2]){
				$recommend = explode(',', $val['item_ids']);
				$activityCats[$key]['item_ids'] = $recommend;
				$itemIds = array_merge($itemIds, $recommend);				
			}
		}
		if(empty($itemIds)){
			exit('无活动专题');
		}
		//过滤下架
		$map = array(
			'item_id' => array('in', $itemIds),
			'approve_status' => 'onsale'
		);
		$itemIds = M('sysitem_item_status')->where($map)->getField('item_id',TRUE);		
		if(empty($actiConfIds)){
			exit('暂无活动分类');
		}
		//活动商品
		$map = array(
			'activity_config_id' => array('in', $actiConfIds),
			'disable' => 0
		);
		$field = 'aitem_id,activity_config_id,item_name,item_info,price,shop_price,item_img';
		$aitems = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);	
		$skuIds = array();
		foreach($aitems as $key=>$val){
			$item = json_decode($val['item_info'],TRUE);
			foreach($item as $keys=>$vals){
				$skuIds[] = $vals['sku_id'];
				if($vals['price'] < $vals['cost_price']){
					$aitems[$key]['lastPrice'] = $vals['price'];
					$aitems[$key]['lastSkuId'] = $vals['sku_id'];					
				}
			}
		}	
		if(!empty($skuIds)){
			$map = array(
				'sku_id' => array('in', $skuIds)
			);
			$aitemIds = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
			$store = M('sysitem_sku_store')->where($map)->getField('sku_id,store,freez');
			$skuStore = array();
			foreach($store as $key=>$val){
				$skuStore[$key] = $val['store']-$val['freez'];
			}
		}
		//组合购商品
		$aitemInfos = array();
		$skuIds = array();
		$groupSkuIds = array();
		foreach($aitems as $key=>$val){
			$aitemInfos[$val['activity_config_id']][] = $val;
		}		
		$allItemIds = array_merge($itemIds,$aitemIds);
		//商品
		if(!empty($allItemIds)){
			$map = array(
				'item_id' => array('in', $allItemIds)
			);
			$itemInfos = M('sysitem_item')->where($map)->getField('item_id,title,price,image_default_id,shop_id');
		}	
		$this->assign('itemInfos', $itemInfos);
		$this->assign('catetorys', $catetorys);
		$this->assign('aitemIds',$aitemIds);	
		$this->assign('activityCats',$activityCats);	
		$this->assign('itemIds',$itemIds);
		$this->assign('aitemInfos',$aitemInfos);
		$this->assign('skuStore',$skuStore);
		$this->display('Activity/newCourtesy/newPerson');	
	}
/**
 * 中秋新版活动页
 */	
	public function midAutumnFestival(){
		//账户余额
		$uid = $this->uid;
		if($uid){
			$map = array(
				'user_id' => $uid
			);
			$deposit = M('sysuser_user_deposit')->where($map)->getField('deposit');
		}	
		//公司名称
		$comId = $this->comId;
		if($comId){
			$map = array(
				'com_id' => $comId
			);
			$comName = M('company_config')->where($map)->getField('com_name');
		}
		$special = 'midAutumnSpecial';//专题
		$package = 'midAutumnPackage';	//礼包	
		$idenArr = array($special,$package);		
		$map = array(
			'Identification' => array('in', $idenArr)
		);			
		$aids = M('company_activity')->where($map)->getField('Identification,aid');		
		//专题排序中第一个：时令节日，第二个：爆款推荐，其他单品凑单
		if(empty($aids)){
			exit('无法获取活动...');
		}
		//活动分类
		$map = array(
			'aid' => array('in', $aids)
		);
		$field = 'activity_config_id,aid,cat_name,cat_content,recommend';
		$activityCats = M('company_activity_category')->where($map)->order('order_sort desc')->getField($field);	
		$specialArr = array();
		$packageArr = array();
		foreach($activityCats as $key=>$val){
			if($val['aid'] == $aids[$special]){
				//专题内容
				$val['rec'] = explode(',', $val['recommend']);
				$specialArr[] = $val;				
			}else if($val['aid'] == $aids[$package]){
				//礼包内容
				$packageArr[] = $val;				
			}
		}
		//时令节日
		$seasonal = $specialArr[0];
		//爆款推荐
		$hotRecommend = $specialArr[1];
		//单品凑单
		$singleProList= array_reverse(array_slice(array_reverse($specialArr),0,4));
		//推荐的商品
		$remItemIds = array_merge($seasonal['rec'], $hotRecommend['rec']);
		$itemInfo = $this->getItemInfo($remItemIds);
		$this->assign('uid',$uid);
		$this->assign('deposit',$deposit);
		$this->assign('comName',$comName);
		$this->assign('packageArr',$packageArr);
		$this->assign('seasonal',$seasonal);
		$this->assign('hotRecommend',$hotRecommend);
		$this->assign('singleProList',$singleProList);
		$this->assign('itemInfo',$itemInfo);
		$this->display('Activity/midAutumnFestival/index');	
	}
/**
 * 单品套餐ajax获取商品信息
 */
 	public function getSingleProList(){
 		$aConfId = I('get.aConfId', 0, 'intval');
		$map = array(
			'activity_config_id' => $aConfId
		);
		$recommend = M('company_activity_category')->where($map)->getField('recommend');			
		$itemIds = explode(',', $recommend);
		$list = $this->getItemInfo($itemIds);
		$this->assign('aConfId',$aConfId);
		$this->assign('list',$list);
		$this->display('Activity/midAutumnFestival/getSingleProList');	
 	}
/**
 * ajax获取礼包信息
 */	
 	public function getPackageList(){
 		$aConfId = I('get.aConfId', 0, 'intval');
		$map = array(
			'activity_config_id' => $aConfId,
			'disable' => 0
		);
		$field = 'aitem_id,activity_config_id,item_name,item_info,price,shop_price,item_img';
		$list = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);
		//取出礼包中第一个sku
		$skuIds = array();
		foreach($list as $key=>$val){
			$item = current(json_decode($val['item_info'],TRUE));
			$list[$key]['fSkuId'] = $item['sku_id'];
			$skuIds[] = $item['sku_id'];
		}		
		if(!empty($skuIds)){
			$map = array(
				'sku_id' => array('in', $skuIds)
			);
			//库存
			$store = M('sysitem_sku_store')->where($map)->getField('sku_id,store,freez');
			//无货的sku
			$noGoodsSku = array();
			foreach($store as $key=>$val){
				if(($val['store'] - $val['freez']) < 1){
					$noGoodsSku[$key] = $key;
				}
			}
		}	
		//过滤无货的礼包
		foreach($list as $key=>$val){
			if(array_key_exists($val['fSkuId'], $noGoodsSku)){
				unset($list[$key]);
			}
		}	
		$this->assign('aConfId',$aConfId);
		$this->assign('list',$list);		
		$this->display('Activity/midAutumnFestival/getPackageList');	
 	}
/**
 * 获取商品基本信息
 */	
	private function getItemInfo($itemIds){
		if(empty($itemIds)){
			return array();
		}
		//过滤下架
		$map = array(
			'item_id' => array('in', $itemIds),
			'approve_status' => 'onsale'
		);
		$itemIds = M('sysitem_item_status')->where($map)->getField('item_id',TRUE);		
		//商品基本信息
		if(empty($itemIds)){
			return array();
		}
		$map = array(
			'item_id' => array('in', $itemIds)
		);
		return  M('sysitem_item')->where($map)->getField('item_id,title,price,image_default_id,shop_id');		
	}
/**
 * 查询skuid是否有货
 */	
	public function checkSkuStore(){
		$skuId = I('skuId', 0, 'intval');
		$num = I('num', 1, 'intval');
		$map = array(
			'sku_id' => $skuId
		);
		$store = M('sysitem_sku_store')->where($map)->field('store,freez')->find();
		$surplus = $store['store'] - $store['freez'];
		if($num > $surplus){
			$this->ajaxReturn(FALSE);	
		}
		$this->ajaxReturn(TRUE);	
	}
/**
 * 查询礼包库存
 */
	public function checkPageStore(){
		$aitemId = I('aitemId');
		$num = I('num', 1, 'intval');
		$ret = array('code'=>0,'msg'=>'Unknow');
		$map = array(
			'aitem_id' => $aitemId
		);
		$itemInfo = M('company_activity_item')->where($map)->getField('item_info');
		if(empty($itemInfo)){
			$ret['msg'] = '组合购有误';
			$this->ajaxReturn($ret);			
		}
		$info = json_decode($itemInfo, TRUE);
		$skuIds = array();
		foreach($info as $key=>$val){
			$skuIds[] = $val['sku_id'];
		}
		if(empty($skuIds)){
			$ret['msg'] = '组合购有误';
			$this->ajaxReturn($ret);			
		}
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$skuInfo = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
		$itemIds = array();
		foreach($skuInfo as $key=>$val){
			$itemIds[] = $val;
		}
		if(empty($itemIds)){
			$ret['msg'] = '组合购有误';
			$this->ajaxReturn($ret);			
		}	
		//判断库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$minStore = M('sysitem_sku_store')->where($map)->min('store-freez');
		if($num > $minStore){
			$ret['msg'] = '库存不足，无法加入购物车';
			$this->ajaxReturn($ret);				
		}	
		$ret['code'] = 1;
		$ret['msg'] = 'succ';
		$this->ajaxReturn($ret);		
			
	}
	public function pkg() {
		$acid = I('get.acid', -1, 'intval');
		
		$map = array(
			'aid' => 46, //活动id
			'activity_config_id' => $acid
		);
		$field = 'aitem_id,activity_config_id,item_name,item_info,price,shop_price,item_img';
		$aitems = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);
		if (empty($aitems)) {
			exit ('→_→ empty');
		}
		//检索商品
		$skuIdArr = array();
		$skuArr = array();
		foreach ($aitems as &$item) {
			$aitem_id = $item['aitem_id'];
			$tmpItemArr = json_decode($item['item_info'], true);
			$item['itemCount'] = count($tmpItemArr);
			$skuArr[$aitem_id] = $tmpItemArr;
			foreach ($tmpItemArr as $sku) {
				$skuIdArr[] = $sku['sku_id'];
			}
		}
		$map = array(
			'sku_id' => array('in', $skuIdArr),
		);
		$skuItemMap = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
		
		$map = array(
			'item_id' => array('in', array_unique($skuItemMap))
		);
		$itemPicMap = M('sysitem_item')->where($map)->getField('item_id,image_default_id');
		
		//查询公司名称
		$comName = '';
		if (is_numeric($this->comId) && $this->comId > 1) {
			$map = array(
				'com_id' => $this->comId,
			);
			$comName = M('company_config')->where($map)->getField('com_name');
		}
		//查询积分
		$deposit = 0;
		if($this->uid) {
			$map = array(
				'user_id' => $this->uid
			);
			$deposit = M('sysuser_user_deposit')->where($map)->getField('deposit');
		}
		$map = array(
			'activity_config_id' => $acid,
		);
		$catContent = M('company_activity_category')->where($map)->getField('cat_content');
		#TODO
		//print_r($skuArr);exit();
		$this->assign('catContent', $catContent);
		$this->assign('comName', $comName);
		$this->assign('deposit', $deposit);
		$this->assign('aitems', $aitems);
		$this->assign('skuArr', $skuArr);
		$this->assign('skuItemMap', $skuItemMap);
		$this->assign('itemPicMap', $itemPicMap);
		$this->assign('STATIC', '/shop/Home/View/Activity/pkg');
		$this->display('Activity/pkg/index');
	}
	
	//单品区域
	public function singleArea() {
		$acid = I('get.acid', -1, 'intval');
		$sortby = I('get.sortby', 1, 'intval'); //1.综合排序 2.销量降序 3.价格降序 4.价格升序 5.上架时间
		$sprice = I('get.sp', '');
		$eprice = I('get.ep', '');
		$page = I('get.page', 1, 'intval');
		$pageItems = 30;
		//$acid = 87;  /////////////////////////////////////测试
		
		$map = array(
			'activity_config_id' => $acid,
		);
		$itemIds = M('company_activity_category')->where($map)->getField('item_ids');
		$itemIdArr = explode(',', $itemIds);
		
		if(!is_array($itemIdArr) || empty($itemIdArr)) {
			exit ('→_→ empty');
		}
		
		$SysitemItem = M('sysitem_item')->alias('i');
		$SysitemItem->join('LEFT JOIN sysitem_item_status s ON i.item_id= s.item_id');
		$map = array(
			'i.item_id' => array('in', $itemIdArr),
			's.approve_status' => 'onsale'
		);
		//排序
		if ($sortby == 1) {
			//综合排序
		} else if($sortby == 2) {
			//销量降序
			$SysitemItem->join('LEFT JOIN sysitem_item_count c ON i.item_id= c.item_id');
			$SysitemItem->order('c.sold_quantity DESC,i.item_id DESC');
		} else if($sortby == 3) {
			//价格降序
			$SysitemItem->order('i.price DESC');
		} else if($sortby == 4) {
			//价格升序
			$SysitemItem->order('i.price ASC');
		} else if($sortby == 5) {
			//上架时间
			$SysitemItem->order('s.list_time DESC');
		}
		//价格区间
		if (!empty($sprice) && is_numeric($sprice)) {
			$map['i.price'][] = array('egt', $sprice);
		}
		
		if(!empty($eprice) && is_numeric($eprice)) {
			$map['i.price'][] = array('elt', $eprice);
		}
		$itemList = $SysitemItem
						->field('i.item_id,i.title,i.shop_id,i.price,i.image_default_id')
						->where($map)
						->page($page, $pageItems)
						->select();
						
		$itemCount = $SysitemItem
						->alias('i')
						->join('LEFT JOIN sysitem_item_status s ON i.item_id= s.item_id')
						->where($map)
						->count('i.item_id');
		//计算分页总数
		$pageCount = 1;
		if (($itemCount % $pageItems) === 0) {
			$pageCount = intval($itemCount / $pageItems);
		} else {
			$pageCount = intval($itemCount / $pageItems) + 1;
		}
		
		//查询公司名称
		$comName = '';
		if (is_numeric($this->comId) && $this->comId > 1) {
			$map = array(
				'com_id' => $this->comId,
			);
			$comName = M('company_config')->where($map)->getField('com_name');
		}
		//查询积分
		$deposit = 0;
		if($this->uid) {
			$map = array(
				'user_id' => $this->uid
			);
			$deposit = M('sysuser_user_deposit')->where($map)->getField('deposit');
		}
		$map = array(
			'activity_config_id' => $acid,
		);
		$catContent = M('company_activity_category')->where($map)->getField('cat_content');
		$this->assign('catContent', $catContent);
		$this->assign('comName', $comName);
		$this->assign('deposit', $deposit);
		$this->assign('acid', $acid);
		$this->assign('sortby', $sortby);
		$this->assign('sp', $sprice);
		$this->assign('ep', $eprice);
		$this->assign('page', $page);
		$this->assign('pageCount', $pageCount);
		$this->assign('itemCount', $itemCount);
		$this->assign('itemList', $itemList);
		$this->assign('STATIC', '/shop/Home/View/Activity/singleArea');
		$this->display('Activity/singleArea/index');
	}
}