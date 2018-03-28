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
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
		$this->areaModel=M('site_area');
		$this->atradeModel = M('company_activity_trade');//活动订单表
		$this->aorderModel = M('company_activity_order');//活动订单子表
      	$this->paymentsModel = M("ectools_payments");//支付表
      	$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
      	$this->userAccountModel = M('sysuser_account');//用户登录表
      	$this->userDepositModel = M('sysuser_user_deposit');//积分表
      	$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
		
		$this->assign('index',$this->index);
	}	
	
	public function order(){
		header("Content-type:text/html;charset=utf-8");
		$whereAddr=array(
        	'user_id'=>$this->uid,
        );
		$addrInfo=$this->addrModel->where($whereAddr)->select();
		if($addrInfo){
			foreach($addrInfo as $k=>$v){
				$currArea=explode(':',trim($v['area']));
				$addrInfo[$k]['area']=$currArea[0];
			}
			$this->assign('addrInfo',$addrInfo);
		}

		$itemId=I('get.itemId',0,'trim');
		$itemInfo = $this->modelActivityItem->where('aitem_id='.$itemId)->find();
		$this->assign('itemInfo',$itemInfo);
		$this->display();
	}

	//提交订单
    public function createOrder(){    	
        $itemId = intval(I('post.itemId'));//商品id 
        //检查是否已经选择收货地址
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"您没有设置配货地址，请先设置！\");\r\n"; 
			echo "window.open('http://www.lishe.cn/user.php/Address/addressList');\r\n"; 
			echo "</script>";
			exit(); 
		}
        
        $remark = I('post.remark');//买家留言
        $num = intval(I('post.num'));//商品数量
		if(empty($num)){
			$num=1;
		}
        if (!$itemId) {
            echo json_encode(array(0,"获取活动商品Id失败！"));
            exit();
       	} 

   	    $itemInfo = $this->modelActivityItem->where('aitem_id='.$itemId)->find();
      	if (!$itemInfo) {
            echo json_encode(array(0,"获取活动商品失败！"));
            exit();
      	} 
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$key.$this->uid;//订单编号
        $data['aid'] = $itemInfo['aid'];//活动id
        $data['activity_name'] = $itemInfo['activity_name'];//活动名称
        $data['title'] = $itemInfo['item_name'];//订单标题
        $data['item_id'] = $itemInfo['item_id'];//商品关联ID
        $data['com_id'] = $this->comId;//企业ID
        $data['user_id'] = $this->uid;//会员id
        $data['account'] = $this->userName;//用户账号
        $data['item_num'] = $num;//订单商品数量
        $data['send_num'] = 0;//发货数量
        $data['total_fee'] = $itemInfo['price']*$num;//订单总价
        $data['post_fee'] = $itemInfo['post_fee'];//邮费
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
        $data['item_img'] = $itemInfo['item_img'];//商品图片
        $data['creat_time'] = time();//创建时间

        $res = $this->atradeModel->data($data)->add();
        if (!$res) {
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

        //生成订单子表
        $da['atid'] = $data['atid'];
        $da['user_id'] = $this->uid;//会员id
        $da['aid'] = $itemInfo['aid'];//活动id
        $da['aitem_id'] = $itemInfo['aitem_id'];//活动id
        $da['item_id'] = $itemInfo['item_id'];//商品关联ID
        $da['price'] = $itemInfo['price'];//商品价格
        $da['cost_price'] = $itemInfo['cost_price'];//商品成本价
        $da['post_fee'] = $itemInfo['post_fee'];//邮费
        $da['item_name'] = $itemInfo['item_name'];//商品名称
        $da['item_img'] = $itemInfo['item_img'];//商品图片
        $da['weight'] = $itemInfo['weight'];
        $orderRes = $this->aorderModel->data($da)->add();
        if (!$orderRes) {
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }
		//生成支付数据
        $paymentId = $this->creatPayments($data['atid']);;
        if (!$paymentId) {
            echo json_encode(array(0,"支付单生成失败！"));
            exit();
        }

        //积分支付
        $payRes = $this->operPay($paymentId);

        if ($payRes) {
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"订单提交成功！\");\r\n"; 
			echo "window.location.href=\"http://www.lishe.cn/user.php/Order/activityOrderList.html\"\r\n"; 
			echo "</script>";
			exit();         	
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n";
			echo " alert(\"支付失败，积分不足！\");\r\n"; 
			echo "window.location.href=\"http://www.lishe.cn/user.php/Order/activityOrderList.html\"\r\n";  
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
        	$payRes = $this->dedect($this->uid,$userAccountInfo['mobile'],$paymentid,$paymentInfo['money'],$paymentInfo['memo']);
			$payType='deposit';
            
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
                    $zdata['ls_trade_no'] = $payRes['data']['info']['transno'];
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
    
    //修改默认的收货地址 开始
    public function modifyDefAddr(){
       $addr_id=I('post.addr_id',0,'intval');
       $whereOne=array(
          'addr_id'=>$addr_id,
          'user_id'=>$this->uid
        );
       $dataOne=array('def_addr'=>1);
       $dataTwo=array('def_addr'=>0);

       $whereTwo['addr_id']=array('neq',$addr_id);
       $whereTwo['user_id']=$this->uid;
       
       $this->addrModel->where($whereTwo)->data($dataTwo)->save();
       $addrMod=$this->addrModel->where($whereOne)->data($dataOne)->save();
       if($addrMod){
        echo 'defAddrSuccess';
        exit;
       }else{
       	echo '';
       	exit;
       }

    }
     //修改收货地址信息
    public function chgAddressInfo(){
    	$this->assign('httprefer',$_SERVER['HTTP_REFERER']);
    	$addrId=I('get.addrId','','trim');
    	if($addrId){
    		$where=array(
    			'addr_id'=>$addrId,
    			'user_id'=>$this->uid
    			);
    		$currAddressInfo=$this->addrModel->where($where)->find();
    		// var_dump($currAddressInfo);
    		if($currAddressInfo){
    			$areaStr=explode(':',trim($currAddressInfo['area']));
        		$areaArr=explode('/',$areaStr[1]);
        		foreach($areaArr as $k=>$v){
		          if($k==0){ //省级
		             $this->assign('selectProvince',$v);
		               //省份 start
		            $where=array(
		                'level'=>1,
		                'jd_pid'=>0
		                );
		            $provinceArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('provinceArr',$provinceArr);
		            //省份 end
		          }elseif($k==1){ //市级
		             $this->assign('selectCity',$v);
		               //市级 start
		            $where=array(
		                'level'=>2,
		                'jd_pid'=>$areaArr[0]
		                );
		            $cityArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('cityArr',$cityArr);
		            //市级 end
		             
		          }elseif($k==2){ //区级

		             $this->assign('selectBal',$v);
		               //区级 start
		            $where=array(
		                'level'=>3,
		                'jd_pid'=>$areaArr[1]
		                );
		            $balArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('balArr',$balArr);
		            //区级 end

		          }elseif($k==3){//街道
		             $this->assign('selectTown',$v);
		               //区级 start
		            $where=array(
		                'level'=>4,
		                'jd_pid'=>$areaArr[2]
		                );
		            $townArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('townArr',$townArr);
		            //区级 end

		          }

		        }
    			$this->assign('currAddressInfo',$currAddressInfo);
    		}

    	}
    	$this->display('address');
    }
    //修改默认的收货地址 结束

    // 对用户收货地址的编辑操作 start
    public function editUserAddrInfo(){

        $addrId=I('post.address_id',0,'intval');
        $consignee=I('post.consignee','','trim');
        $province=I('post.province','','trim');
        $provinceId=I('post.province_id','','trim');
        $city=I('post.city','','trim');
        $cityId=I('post.city_id','','trim');
        $area=I('post.area','','trim');
        $areaId=I('post.area_id','','trim');
        $town=I('post.town','','trim');
        $townId=I('post.town_id','','trim');
        $address=I('post.address','','trim');
        $zipcode=I('post.zipcode','','trim');
        $mobile=I('post.mobile','','trim');
        $isDefault=I('post.isDefault',0,'intval');

         //判断四级地址是否正确
      	if(empty($townId)){
          $fourLevel=$this->areaModel->field('jd_id')->where('jd_pid='.$areaId.' and level=4')->find();
          if($fourLevel['jd_id']){
            echo 'modFailed';
            exit;
          }
        }

        if($townId != ''){
          $addrDetail=$province.'/'.$city.'/'.$area.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$areaId.'/'.$townId;
        }else{
          $addrDetail=$province.'/'.$city.'/'.$area.':'.$provinceId.'/'.$cityId.'/'.$areaId;
        }

        if($isDefault == 1){
          $data=array('def_addr'=>0);
          $this->addrModel->where('user_id='.$this->uid)->data($data)->save();
        }
        $info=array(
            'name'=>$consignee,
            'area'=>$addrDetail,
            'addr'=>$address,
            'zip'=>$zipcode,
            'tel'=>'',
            'mobile'=>$mobile,
            'def_addr'=>$isDefault
            );
        $where=array(
            'addr_id'=>$addrId,
            'user_id'=>$this->uid
          );
        $addrModifier=$this->addrModel->where($where)->data($info)->save();
        if($addrModifier){
          echo 'addrModSuccess';
          exit;
        } 
    }
     // 对用户收货地址的编辑操作 end
    public function getCity(){
        $proItem=I('post.proItem',0,'intval');

        if($proItem > 0){
            $where=array(
                 'level'=>2,
                 'jd_pid'=>$proItem
                );
           $cityArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
           $cityHtml.="<option value='0' selected>请选择</option>";
           if($cityArr){
               foreach($cityArr as $k => $v){
                 $cityHtml.= "<option value='".$v['jd_id']."' data-value='".$v['name']."'>".$v['name']."</option>";
               }
               echo $cityHtml;
           }else{
               echo 0;
           }
            
        }
    }

    public function getArea(){
        $cityItem=I('post.cityItem',0,'intval');
        if($cityItem > 0){
           $where=array(
                'level'=>3,
                'jd_pid'=>$cityItem
            );
           $areaArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
           $areaHtml.="<option value='0' selected>请选择</option>";
           if($areaArr){
                foreach($areaArr as $k1 => $v1){
                  $areaHtml.= "<option value='".$v1['jd_id']."' data-value='".$v1['name']."'>".$v1['name']."</option>";
                }
                echo $areaHtml;
           }else{
                echo 0;
           }
           
        }
    }

    public function getTown(){
      $areaItem=I('post.areaItem',0,'intval');
      if($areaItem > 0){
         $where=array(
                'level'=>4,
                'jd_pid'=>$areaItem
            );
      }
      $townArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
      $townHtml.="<option value='0' selected>请选择</option>";
      if($townArr){
                foreach($townArr as $k1 => $v1){
                  $townHtml.= "<option value='".$v1['jd_id']."' data-value='".$v1['name']."'>".$v1['name']."</option>";
                }
            echo $townHtml;
       }else{
            echo 0;
       }
    }
}