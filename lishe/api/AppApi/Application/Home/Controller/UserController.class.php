<?php
namespace Home\Controller;
use Common\Common\Classlib\UploadFile\UploadImages;

use Think\Upload;

use Think\Controller;
class UserController extends CommonController{
    public function __construct(){
    	header ( "content-type:text/html;charset=utf-8" );
        parent::__construct();
        $this->modelDeposit=M('sysuser_user_deposit'); //用户积分表
        $this->modelAccount=M('sysuser_account'); //用户登录信息表
        $this->modelUserCoupon=M("sysuser_user_coupon");
        $this->modelTrade=M('systrade_trade');//订单主表
        $this->sysuser = D('SysuserUser');
        $this->modelUser=D('User');//订单主表
        $this->modelOrder=D('Order');//订单主表
    }
    //获取用户中心
    public function userCenter(){
    	$this->uid = $_SESSION['uid'];
    	$uid = $this->uid;
        if(!$uid){
            $this->retError(-1,"用户token验证失败");
        }
        //用户基本信息
        $res=$this->sysuser->commonUserInfo($this->uid);
        $data=array();
        $userInfo=$res;
        $depositInfo=$this->sysuser->getDeposit($this->uid);
      
        $data['userInfo']=array_merge($userInfo,$depositInfo);
        $AccountObj = M('sysuser_account');
        $where['user_id'] = $uid;
        $accountData = $AccountObj->where($where)->field('mobile')->find();
        $data['userInfo']['account'] = $accountData['mobile'];
        //账号
        //订单信息
        $count=$this->sysuser->getOrderNumber($this->uid);
//        $data['count']=$count;
        $data['order_list_config']=C('ORDER_LIST_CONFIG');
        foreach($data['order_list_config'] as $key => $val){
            if(!empty($val['status'])){
                $data['order_list_config'][$key]['number']=$count[$val['status']];
            }
        }
        //列表信息
        $data['userinfo_list_config']=C("USERINFO_LIST_CONFIG");
        $this->retSuccess($data,"用户信息");
    }
    //获取用户订单
    public function orderList(){
        $statusNum=I('post.status',0,'intval');
        $statusOrder="";
        if(!$this->uid){
            $this->retError(-1,"请登录！");
        }
        $whereSys=array(
            'user_id'=>$this->uid
        );
        $sysTradeInfo="";
        switch($statusNum){
            case 1:
                //待付款 WAIT_BUYER_PAY
                $statusOrder="WAIT_BUYER_PAY";
                break;
            case 2:
                //待发货 WAIT_SELLER_SEND_GOODS
                $statusOrder="WAIT_SELLER_SEND_GOODS";
                break;
            case 3:
                //待收货 WAIT_BUYER_CONFIRM_GOODS
                $statusOrder="WAIT_BUYER_CONFIRM_GOODS";
                break;
            case 4:
                //已完成 TRADE_FINISHED
                $statusOrder="TRADE_FINISHED";
                break;
            case 5:
                //退款/退货
                //$statusOrder="TRADE_CLOSED_BY_SYSTEM";
                $sysTradeInfo=$this->sysuser->getRefundOrder($this->uid);
                break;
        }
        if($statusNum > 0){
            $whereSys['status']=$statusOrder;
        }
//         $this->assign('statusNum',$statusNum);
        if(empty($sysTradeInfo)){
            $sysTradeInfo=$this->sysuser->getOrderList($whereSys);
        }
        
        if($sysTradeInfo){
            foreach($sysTradeInfo as $k3=>$v3){
                $shopId[]=$v3['shop_id'];
                $tid[]=$v3['tid'];
                $sysTradeInfo[$k3]['post_fee']=number_format($v3['post_fee'],2,'.','');
                $sysTradeInfo[$k3]['payment']=number_format($v3['payment'],2,'.','');
            }
            $shop=$this->sysuser->getShopList($shopId);
            $order=$this->sysuser->getOrders($tid);
            $data['shopList']=$shop;
            $data['TradeInfo']=$sysTradeInfo;
            $data['orderList']=$order;
            $this->retSuccess($data,"订单列表");
        }else{
            $this->retError(-1,"订单为空");
        }
    }
    
    //新的订单列表 
    //Name:lihongqiang 
    //Email:lhq@lishe.cn
    public function order(){
    	$uid = $this->uid;
    	if($uid){
    		$postData = I('post.');
    		$status = $postData['status'];
    		if(!isset($status)){
    			$this->retError(-1,"参数缺失");
    		}else{
    			if($status==1){
    				//待付款 WAIT_BUYER_PAY
    				$OrderStatus = 'WAIT_BUYER_PAY';
    				//$OrderStatus['trade_status'] = 'WAIT_BUYER_PAY';
    				//$OrderStatus['order_status'] = 'NO_APPLY';
    				//$OrderStatus['cancel_status'] = 'NO_APPLY_CANCEL';
    			}elseif($status==2){
    				//待发货 WAIT_SELLER_SEND_GOODS
    				$OrderStatus = 'WAIT_SELLER_SEND_GOODS';
    				//$OrderStatus['trade_status'] = 'WAIT_SELLER_SEND_GOODS';
    				//$OrderStatus['order_status'] = 'NO_APPLY';
    				//$OrderStatus['cancel_status'] = 'NO_APPLY_CANCEL';
    			}elseif($status==3){
    				//待收货 WAIT_BUYER_CONFIRM_GOODS
    				$OrderStatus = 'WAIT_BUYER_CONFIRM_GOODS';
    				//$OrderStatus['trade_status'] = 'WAIT_BUYER_PAY';
    				//$OrderStatus['order_status'] = 'NO_APPLY';
    				//$OrderStatus['cancel_status'] = 'NO_APPLY_CANCEL';
    			}elseif($status==4){
    				//已完成 TRADE_FINISHED
    				$OrderStatus = 'TRADE_FINISHED';
    				//$OrderStatus['trade_status'] = 'WAIT_BUYER_PAY';
    				//$OrderStatus['order_status'] = 'NO_APPLY';
    				//$OrderStatus['cancel_status'] = 'NO_APPLY_CANCEL';
    			}elseif($status==5){
    				//退款/退货
    				//$OrderStatus = 'TRADE_CLOSED_BY_SYSTEM';//此处订单机制更改后，退款退货还需要再商议
    				//$OrderStatus = '';
    				$OrderStatus = 5;
    			}else{
    				//全部
    				$OrderStatus = null;
    			}
    		}
    		 
    		//$arrData = $this->selectOrder($uid,$OrderStatus);
    		$PayMentIdArray = $this->PayMentId($uid,$OrderStatus);
    		$OrderArray = array();
    		for ($i=0;$i<count($PayMentIdArray);$i++){
    			$findOrder = $this->getOrder($uid,$OrderStatus,$PayMentIdArray[$i]);
    			if($findOrder){
    				array_push($OrderArray, $findOrder);
    			}
    		}
    		//var_dump($OrderArray[0]['trade_paybillData'][1]['TradeData']);exit;
    		foreach ($OrderArray as &$V){
    			$V['status'] = $V['trade_paybillData'][0]['TradeData']['status'];
    			//$total_post_fee = '0';
    			//foreach ($V['trade_paybillData'] as $X =>$S){
    			//	$total_post_fee = $total_post_fee + $S['TradeData']['post_fee'];
    			//}
    			//$V['total_post_fee'] = $total_post_fee;
    		}
    		$this->retSuccess($OrderArray,"订单信息返回成功");
    	}else{
    		$this->retError(0,"请重新登录");
    	}
    }
    
    
    /**
     * //查询订单
     * @name getOrder
     * @param string $uid 会员iD
     * @param string $OrderStatus 订单状态
     * @param string $PayMentId 支付的id
     * @example 查询订单（根据paymentid查询，所返回的数据就是一整个订单
     * @author lihongqiang  lhq@lishe.cn
     */
    protected function getOrder($uid,$OrderStatus,$PayMentId){
	    $ectools_paymentsObj = M('ectools_payments');
	    $where['user_id'] = $uid;
	    $where['pay_type'] = 'online';
	    $where['payment_id'] = $PayMentId;
	    $findPayment = $ectools_paymentsObj->where($where)->field('payment_id,status,money,created_time')->find();
	    if ($findPayment){
	    	$ectools_trade_paybill = M('ectools_trade_paybill');
	    	$systrade_tradeObj = M('systrade_trade');
	    	$systrade_orderObj = M('systrade_order');
	    	$shopModel = M('sysshop_shop');//店铺信息
	    	$sysItemObj = M('sysitem_item');
	    	$sysItemSkuObj = M('sysitem_sku');
	    	
    		$findPayment['created_time'] = date("Y-m-d H:i:s", $findPayment['created_time']);
    		$where1['payment_id'] = $findPayment['payment_id'];
    		$findPayment['trade_paybillData'] =$ectools_trade_paybill->where($where1)->field('payment_id,status,tid,payment')->select();
    		foreach ($findPayment['trade_paybillData'] as &$B){
    			$where2['tid'] = $B['tid'];
    			if($OrderStatus){
    				$where2['status'] = $OrderStatus;
    			}
    			//$where2['trade_status'] = 'WAIT_BUYER_PAY';
    			//$where2['order_status'] = 'NO_APPLY';
    			//$where2['cancel_status'] = 'NO_APPLY_CANCEL';
    			$B['TradeData']= $systrade_tradeObj->where($where2)->field('tid,shop_id,status,trade_status,order_status,cancel_status,sync_pay_status,payment,sync_tid,points_fee,total_fee,post_fee,relief_fee,payed_fee')->find();
    			$shopWhere['shop_id'] = $B['TradeData']['shop_id'];
    			$shopInfo = $shopModel->where($shopWhere)->find();
    			$B['TradeData']['shopName'] = $shopInfo['shop_name'];
    			$where3['tid'] = $B['TradeData']['tid'];
    			$B['TradeData']['orderList'] = $systrade_orderObj->where($where3)->field('tid,oid,shop_id,title,item_id,sku_id,price,num,total_weight,pic_path')->select();
    			foreach ($B['TradeData']['orderList'] as &$O){
    				$where4['item_id'] = $O['item_id'];
    				$where4['sku_id'] = $O['sku_id'];
    				$shopData = $sysItemSkuObj->where($where4)->field('spec_info')->find();
    				$O['spec_info'] = $shopData['spec_info'];
    				if(empty($O['spec_info'])){
    					$O['spec_info'] = "";
    				}
    			}
	    	}
	    	return $findPayment;
	    }else{
	    	return null;
	    }	
    }
    
    
    
    //全部订单
    public function allorder(){
    	$uid = $this->uid;
    	if($uid){
    		$orderList = $this->getAllOrder($uid);
    		if($orderList){
    			$this->retSuccess($orderList,"全部订单返回成功");
    		}else{
    			$this->retSuccess($orderList,"还没有订单");
    		}
    	}
    }
    //查询某个用户的所有订单
    protected function getAllOrder($uid){
    	$uid = $this->uid;
    	if ($uid){
    		$ectools_paymentsObj = M('ectools_payments');
    		$where['user_id'] = $uid;
    		$where['pay_type'] = 'online';//属于支付的
    		$arrData = $ectools_paymentsObj->where($where)->field('payment_id,status,money,created_time')->select();
    		if ($arrData){
    			$ectools_trade_paybill = M('ectools_trade_paybill');
    			$systrade_tradeObj = M('systrade_trade');
    			$systrade_orderObj = M('systrade_order');
    			$shopModel = M('sysshop_shop');//店铺信息
    			$sysItemObj = M('sysitem_item');
    			$sysItemSkuObj = M('sysitem_sku');
    			foreach ($arrData as &$V){
    				$V['created_time'] = date("Y-m-d H:i:s", $V['created_time']);
    				$where1['payment_id'] = $V['payment_id'];
    				$V['trade_paybillData'] =$ectools_trade_paybill->where($where1)->field('payment_id,tid,status,payment')->select();
    				foreach ($V['trade_paybillData'] as &$B){
    					$where2['tid'] = $B['tid'];
    					//if($OrderStatus['status']){
    					//	$where2['status'] = $OrderStatus;
    					//}
    					//$where2['trade_status'] = 'WAIT_BUYER_PAY';
    					//$where2['order_status'] = 'NO_APPLY';
    					//$where2['cancel_status'] = 'NO_APPLY_CANCEL';
    					$B['TradeData']= $systrade_tradeObj->where($where2)->field('tid,shop_id,status,trade_status,order_status,cancel_status,sync_pay_status,payment,sync_tid,points_fee,total_fee,post_fee,relief_fee,payed_fee')->find();
    					$shopWhere['shop_id'] = $B['TradeData']['shop_id'];
    					$shopInfo = $shopModel->where($shopWhere)->find();
    					$B['TradeData']['shopName'] = $shopInfo['shop_name'];
    					$where3['tid'] = $B['TradeData']['tid'];
    					$B['TradeData']['orderList'] = $systrade_orderObj->where($where3)->field('tid,oid,shop_id,item_id,sku_id,price,num,total_weight,pic_path')->select();
    					foreach ($B['TradeData']['orderList'] as &$O){
    						$where4['item_id'] = $O['item_id'];
    						$where4['sku_id'] = $O['sku_id'];
    						$shopData = $sysItemSkuObj->where($where4)->field('spec_info')->find();
    						$O['spec_info'] = $shopData['spec_info'];
    						if(empty($O['spec_info'])){
    							$O['spec_info'] = "";
    						}
    					}
    				}	
    			}
    			return $arrData;
    		}else{
    			return null;
    		}
    	}else{
    		return null;
    	}
    }
    
    //根据订单状态查询满足条件的支付id
    public function PayMentId($uid,$status){
    	$where['user_id'] = $uid;
    	if(!empty($status)){
    		if($status==5){
    			//退款退货处理';
    			$where = "`status`!='WAIT_BUYER_PAY' and `status`!='WAIT_BUYER_CONFIRM_GOODS' and `status`!='WAIT_SELLER_SEND_GOODS' and `status`!='TRADE_FINISHED' and `status`!='SUCCESS' and `status`!='TRADE_CLOSED_BY_USER' and `status`!='TRADE_CLOSED_BY_SYSTEM' and user_id=$uid";    
    		}else{
    			$where['status'] = $status;
    		}
    	}
    	$ectools_paymentsObj = M('ectools_payments');
    	$ectools_trade_paybill = M('ectools_trade_paybill');
    	$systrade_tradeObj = M('systrade_trade');
    	$arrDataTid = $systrade_tradeObj->distinct(true)->where($where)->field('tid')->order('created_time desc')->select();
    	$PayMentIdArray = array();
    	foreach ($arrDataTid as &$T){
    		$where1['tid'] = $T['tid'];
    		$Tpayment_id = $ectools_trade_paybill->where($where1)->field('tid,payment_id')->find();
    		//$T['zhifuid'] = $Tpayment_id;
    		if(!in_array($Tpayment_id['payment_id'], $PayMentIdArray)){
    			array_push($PayMentIdArray, $Tpayment_id['payment_id']);
    		}
    	}
    	return $PayMentIdArray;
    }

    //用户收货地址列表
    public function addrList(){
        $condition=array('user_id'=>$this->uid);
        $addrList=$this->modelUser->getUserAddressList($condition);
        if($addrList){
            foreach($addrList as $k=>$v){
                $addrArr=explode(':',$v['area']);
                $addrList[$k]['area']=$addrArr[0];
                $addrList[$k]['area_id']=$addrArr[1];
            }

        }
        $this->retSuccess($addrList);
    }

    // 删除收货地址
    public function deleteAddr(){
        $addrId=I('post.addr_id',0,'intval');
        $condition=array(
            'addr_id'=>$addrId,
            'user_id'=>$this->uid
        );
        $delResult=$this->modelUser->delAddress($condition);
        if($delResult){
            $data=array(
                'msg'=>"删除成功"
            );
            $this->retSuccess($data);
            exit;
        }else{
            $data=array(
                'msg'=>"删除失败"
            );
            $this->retError(-1,$data);
        }
    }
    //添加收货地址
    public function addAddress(){
    	$postData = I('post.');
    	if($postData){
    		$addressId = trim($postData['addressId']);//编辑的时候携带的地址id，增加地址的时候不需要
    		$userName =trim($postData['userName']);//收货人姓名，不能为空
    		$provinceId = trim($postData['provinceId']);//省的id，不能为空
    		$cityId = trim($postData['cityId']);//市的id，不能为空
    		$countyId = trim($postData['countyId']);//区的id，不能为空
    		$townId = trim($postData['townId']);//四级id，可以为空
    		$address = trim($postData['address']);//详细街道地址，不能为空
    		$zipcode = trim($postData['zipcode']);//邮编。可以为空
    		$mobile = trim($postData['mobile']);//手机号，不能为空
    		$isDefault = trim($postData['isDefault']);//四级id，可以为空（1：设为默认收货地址;空：不处理）
    		$uid = $this->uid;
//     		$userName = 'lhq';
//     		$provinceId = 25;
//     		$cityId = 2247;
//     		$countyId = 2256;
//     		$townId = 21309;
//     		$zipcode = 518057;
//     		$address = '岩竹中石口';
//     		$mobile = 13066899989;
//     		$isDefault = 1;
//     		$uid = $this->uid;
    		if(empty($userName)||empty($provinceId)||empty($cityId)||empty($countyId)||empty($mobile)||empty($address)){
    			$this->retError(-1,array('msg'=>"重要参数缺失"));
    		}else{
    			if(!empty($townId)){
    				$where['jd_pid'] = $countyId;
    				$where['jd_id'] = $townId;
    				$where['level'] = '4';
    				$fourLevel=$this->modelUser->getAddressTown($where);
    				if($fourLevel['jd_id']!=$townId){
    					$this->retError(-1,array('msg'=>"地址有误！"));
    					exit;
    				}else{
    					$town = $fourLevel['name'];
    				}
    				
    			}
    			$regionModel = M('site_area');
    			$where1['jd_id'] = $provinceId; 
    			$where1['level'] = 1;
    			$findProvince = $regionModel->where($where1)->find();
    			$province = $findProvince['name'];
    			
    			$where2['jd_id'] = $cityId;
    			$where2['level'] = 2;
    			$findCity = $regionModel->where($where2)->find();
    			$city = $findCity['name'];
    			
    			$where3['jd_id'] = $countyId;
    			$where3['level'] = 3;
    			$findCounty = $regionModel->where($where3)->find();
    			$county = $findCounty['name'];
    			
    			if ($townId) {
    				$addrDetail = $province.'/'.$city.'/'.$county.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$countyId.'/'.$townId;
    			}else{
    				$addrDetail = $province.'/'.$city.'/'.$county.':'.$provinceId.'/'.$cityId.'/'.$countyId;
    			}
    			if($isDefault == 1){
    				$data1['user_id'] = $uid; 
    				$data2['def_addr']=0;
    				$this->modelUser->updateDefaultAddress($data1,$data2);
    			}else{
    				$isDefault = 0;
    			}
    			
    			$info['name'] = $userName;
    			$info['area'] = $addrDetail;
    			$info['addr'] = $address;
    			$info['zip'] =$zipcode;
    			$info['mobile'] =$mobile;
    			$info['def_addr'] = $isDefault;
    			$info['user_id'] = $uid;
    			if($addressId){
    				//编辑
    				$condition['addr_id'] = $addressId;
    				$condition['user_id'] = $uid;
    				$addrModifier=$this->modelUser->updateAddress($condition,$info);
    			}else{
    				//添加
    				$addrAdd=$this->modelUser->addAddress($info);
    			}
    			if($addrModifier || $addrAdd){
    				if ($addrModifier){
    					$this->retSuccess(array('msg'=>"编辑地址成功！"));
    					exit;
    				}else{
    					$this->retSuccess(array('msg'=>"添加成功！"));
    					exit;
    				}
    			}else{
    				$this->retError(-2,array('msg'=>"操作失败！"));
    				exit;
    			}
    		}
    	}else{
    		 $this->retError(-1,array('msg'=>"参数缺失"));
    	}
    }
    
    
    //地址操作（增加和修改地址）
    public function opAddress(){
    	$jsons = json_decode('{"address":"esterterterter","city":"\u8861\u9633\u5e02","cityId":"1501","county":"\u8861\u5357\u53bf","countyId":"1506","isDefault":"1","mobile":"18575683432","province":"\u6e56\u5357","provinceId":"18","token":"0531023962634ee1642083e93ee90674","town":"","townId":"","userName":"yixiaofei","zipcode":""}');
    	var_dump($jsons);exit;
    	$postData = $_POST;
    	$json = json_encode($postData);
    	$bool = $this->appReceiveDataLog($json);
        $addressId=I('post.addressId',0,'intval');
        $userName=I('post.userName','','trim');
        $province=I('post.province','','trim');
        $provinceId=I('post.provinceId','','trim'); //一级
        $city=I('post.city','','trim');
        $cityId=I('post.cityId','','trim'); //二级
        $county=I('post.county','','trim');
        $countyId=I('post.countyId','','trim'); //三级
        $town=I('post.town','','trim');
        $townId=I('post.townId','','trim'); //四级
        $address=I('post.address','','trim');
        $zipcode=I('post.zipcode','','trim');
        $mobile=I('post.mobile','','trim');
        $isDefault=I('post.isDefault',0,'intval');
        //判断四级地址是否正确
        if(empty($townId)){
            $condition = array("jd_pid"=>$countyId,"level"=>"4");
            $fourLevel=$this->modelUser->getAddressTown($condition);
            if($fourLevel['jd_id']){
                $this->retError(-1,array('msg'=>"地址不正确！"));
                exit;
            }
        }

        if ($townId) {
            $addrDetail = $province.'/'.$city.'/'.$county.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$countyId.'/'.$townId;
        }else{
            $addrDetail = $province.'/'.$city.'/'.$county.':'.$provinceId.'/'.$cityId.'/'.$countyId;
        }
        if($isDefault == 1){
            $data=array('def_addr'=>0);
            $this->modelUser->updateDefaultAddress('user_id='.$this->uid,$data);
        }
        $info=array(
            'name'=>$userName,
            'area'=>$addrDetail,
            'addr'=>$address,
            'zip'=>$zipcode,
            'tel'=>'',
            'mobile'=>$mobile,
            'def_addr'=>$isDefault,
            'user_id'=>$this->uid
        );
        $condition=array(
            'addr_id'=>$addressId,
            'user_id'=>$this->uid
        );
        if ($addressId) {
            //编辑
            $addrModifier=$this->modelUser->updateAddress($condition,$info);
        }else{
            //添加
            $addrAdd=$this->modelUser->addAddress($info);
        }
        if($addrModifier || $addrAdd){
            $this->retSuccess(array('msg'=>"操作成功！"));
            exit;
        }else{
            $this->retError(-2,array('msg'=>"操作失败！"));
            exit;
        }
    }
    
    

    // 编辑添加收货地址 start
    public function editAddress(){
        $addrId=I('get.addrId');
        $refer=I('refer');
        $uid = $this->uid;
        if(!$uid){
        	$this->retError(-1,'请登录');
        }else{
	        if(empty($refer)){
	            $refer=$_SERVER['HTTP_REFERER'];
	        }
	        if (!$addrId) {
	            $this->retError(-1,array('msg'=>'地址ID不能为空'));
	        }
	        $condition=array(
	            'addr_id'=>$addrId,
	            'user_id'=>$uid
	        );
	        $addressInfo = $this->modelUser->getAddressInfo($condition);
	        if (!$addressInfo) {
	            $this->retError(-1,"系统繁忙，请刷新重试！");
	        }
	        $areaStr=explode(':',trim($addressInfo['area']));
	        
	        $siteJdIds = str_replace("/",",",$areaStr[1]);
	       
	        $conditionSite['jd_id'] = array('in',$siteJdIds);
	        $siteList = $this->modelUser->getSiteList($conditionSite);
	        $townInfo = array();
	        if ($siteList) {
	            foreach ($siteList as $key => $value) {
	                if ($key == 0) {
	                    $provinceInfo = $value;
	                }elseif ($key == 1) {
	                    $cityInfo = $value;
	                }elseif ($key == 2) {
	                    $countyInfo = $value;
	                }else{
	                    $townInfo = $value;
	                }
	            }
	        }
	        $data=array();
	        $data['refer']=urldecode($refer);
	        $data['privinceInfo']=$provinceInfo;
	        $data['cityInfo']=$cityInfo;
	        $data['countyInfo']=$cityInfo;
	        if($townInfo){
	        	$data['townInfo']=$townInfo;
	        }else{
	        	$data['townInfo']='';
	        }
	        
	        $data['addressInfo']=$addressInfo;
	        $this->retSuccess($data);
        }
    }


    //修改默认的收货地址
    public function modifyDefAddr(){
        $addressId=I('post.addrId',0,'intval');
        $condition=array(
            'addr_id'=>$addressId,
            'user_id'=>$this->uid
        );
        $result = $this->modelUser->updateDefaultAddress('user_id='.$this->uid,'def_addr=0');
        $res = $this->modelUser->updateDefaultAddress($condition,'def_addr=1');
        if($result && $res){
            $data=array(
                'msg'=>"修改成功"
            );
            $this->retSuccess($data);
        }else{
            $this->retError("-1","修改失败");
        }
    }
    // 判断用户是否选择默认的收货地址 start
    public function defaultAddrInfo(){
        //得到用户的user_id   112 测试
        $where=array(
            'user_id'=>$this->uid,
            'def_addr'=>1
        );
        $defArr=$this->addrModel->where($where)->select();
        if($defArr){
            echo '1';
        }else{
            echo '0';
        }
    }
    //获取地址级联
    public function getSiteList(){
        $condition['jd_pid'] = I("get.pId",0,"intval");
        $condition['level'] = I("get.level",1,"intval");
        $siteList = $this->modelUser->getSiteList($condition);
        if ($siteList) {
            $this->retSuccess($siteList);
        }else{
            $this->retError(-1,"无下级地址");
        }
    }
    
    //我的福利
    public function welfare(){
    	$uid = $this->uid;
    	if(empty($uid)){
    		exit();
    	}else{
    		$data = null;
    		if($data){
    			$this->display();
    		}else{
    			$this->display('norecord');
    		}
    	}
    }
    
    //意见与反馈
    public function feedback(){
    	$postData = I('post.');
    	$json = json_encode($postData);
    	$this->appReceiveDataLog($json);
    	if(!empty($postData)){
    		$uid = $this->uid;
    		$comId = $this->comId;
    		if(!empty($uid)&&!empty($comId)){
    			$linkman = $postData['linkman'];//反馈人姓名
    			$mobile = $postData['mobile'];//反馈人手机号
    			$content = $postData['content'];//意见反馈的内容
//     			$upload = new \Think\Upload();// 实例化上传类
//     			$upload->maxSize   =     31457280 ;// 设置附件上传大小
//     			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
//     			$upload->rootPath  =     './Public/Upload'; // 设置附件上传根目录
//     			$upload->savePath  =     '/User/Feedback/'; // 设置附件上传（子）目录
//     			// 上传文件
    			$upload = new UploadImages();
    			$uploadConfig['rootPath'] = './Public/';//根路径
    			$uploadConfig['savePath'] = 'UploadImages/User/Feedback/';//相对根路径
    			$uploadInfo = $upload->ImagesUpload($uploadConfig);
    			if($uploadInfo){
    				if($uploadInfo['images1']){
    					$imgPath1 = $uploadInfo['images1']['imagesPath'];
    				}
    				if($uploadInfo['images2']){
    					$imgPath2 = $uploadInfo['images2']['imagesPath'];
    				}
    				if($uploadInfo['images3']){
    					$imgPath3 = $uploadInfo['images3']['imagesPath'];
    				}
    			}
    			
//     			$info =  $upload->upload();
//     			if($_FILES['images1']){
//     				$imgPath1 =$upload->rootPath.$info['images1']['savepath'].$info['images1']['savename'];
//     			}
//     			if($_FILES['images2']){
//     				$imgPath2 =$upload->rootPath.$info['images2']['savepath'].$info['images2']['savename'];
//     			}
//     			if($_FILES['images3']){
//     				$imgPath3 =$upload->rootPath.$info['images3']['savepath'].$info['images3']['savename'];
//     			}
    			if(empty($mobile)||empty($linkman)||empty($content)){
    				$this->retError(-3,"重要参数缺失");
    			}else{
    				$feedbackObj = M('company_feedback');
    				$data['user_id'] = $uid;
    				$data['com_id'] = $comId;//公司的id
    				$data['prom_type'] = 1;//类型（1，意见反馈2，商品登记）
    				$data['content'] = $content;
    				if($imgPath1){
    					$data['images1'] = $imgPath1;
    				}
    				if($imgPath2){
    					$data['images2'] = $imgPath2;
    				}
    				if($imgPath3){
    					$data['images3'] = $imgPath3;
    				}
    				$data['mobile'] = $mobile;
    				$data['linkman'] = $linkman;
    				$data['add_time'] = time();
    				$data['status'] = 0;
    				$boolData = $feedbackObj->add($data);
    				if($boolData){
    					$result['status'] = $boolData;
    					$result['msg'] = '意见反馈提交成功';
    					$this->retSuccess($result);
    				}else{
    					$this->retError(-4,$result);
    				}
    			}
    		}else{
    			$this->retError(-2,"登录失效");
    		}
    	}else{
    		$this->retError(-1,"参数缺失");
    	}
    }
    
    
    //接口返回结果
    public function retSuccess($data=array(),$msg='操作成功'){
    	$ret=array(
    			'result'=>100,
    			'errcode'=>0,
    			'msg'=>$msg,
    			'data'=>$data
    	);
    	echo json_encode($ret);
    	exit;
    }
    
    //接口返回错误信息
    public function retError($errCode=1,$msg='操作失败'){
    	$ret=array(
    			'result'=>100,
    			'errcode'=>$errCode,
    			'msg'=>$msg
    	);
    	echo json_encode($ret);
    	exit;
    }
    
    public function appReceiveDataLog($postData){
    	$app_receive_data = M('app_receive_data');
    	$data['postData'] = $postData;
    	$data['createTime'] = date('Y-m-d H:i:s');
    	$boolData =$app_receive_data->add($data) ;
    	return $boolData;
    }
    
    //我的资产
    public function assets(){
    	$uid = $this->uid;
    	if($uid){
    		$this->modelDeposit=M('sysuser_user_deposit'); //用户积分表
    		$depositInfo = $this->modelDeposit->where("user_id=".$uid)->find();
    		
    		if(empty($depositInfo['balance'])){
    			$depositInfo['balance'] = '0';
    		}
    		if(empty($depositInfo['commonAmount'])){
    			$depositInfo['commonAmount'] ='0';
    		}
    		if(empty($depositInfo['limitAmount'])){
    			$depositInfo['limitAmount'] = '0';
    		}
    		$data['balance'] =$depositInfo['balance'];
    		$data['balance#_explain'] = "balance是可用总积分";
    		$data['commonAmount'] = $depositInfo['commonAmount'];
    		$data['commonAmount#_explain'] = "commonAmount是通用总积分";
    		$data['limitAmount'] = $depositInfo['limitAmount'];
    		$data['limitAmount#_explain'] = "limitAmount是可用限制积分";
    		$this->retSuccess($data,"资产信息返回成功");
    	}else{
    		$this->retError(0,"请重新登录");
    	}
    }
    
    //我的优惠卷
    public function coupons(){
    	$uid = $this->uid;
    	if($uid){
	    	$userCouponCount = $this->modelUserCoupon->where("user_id=".$uid)->count();
	    	$userCouponInfo = $this->modelUserCoupon->field('pc.coupon_name,pc.coupon_desc,pc.deduct_money,pc.canuse_start_time,pc.canuse_end_time,pc.limit_money')->table('sysuser_user_coupon uc,syspromotion_coupon pc')->where('uc.user_id='.$uid.' and uc.coupon_id = pc.coupon_id')->select();
	    	if($userCouponCount<0 ||$userCouponCount==0){
	    		$userCouponCount = '0';
	    	}
	    	if($userCouponInfo){
	    		$data['userCouponInfo'] = $userCouponInfo;//优惠券
	    	}else{
	    		$data['userCouponInfo'] = '';
	    	}
	    	$data['userCouponCount'] = $userCouponCount;
	    	$this->retSuccess($data,"优惠券信息返回成功");
    	}else{
    		$this->retError(0,"请重新登录");
    	}
    }
    
    //我的礼舍币
    public function lisheMoney(){
    	$uid = $this->uid;
    	if($uid){
    		$lisheMoney = '0';
    		if($lisheMoney){
    			$data['lisheMoney'] = $lisheMoney;//优惠券
    		}else{
    			$data['lisheMoney'] = '0';
    		}
    		$this->retSuccess($data,"礼舍币信息返回成功");
    	}else{
    		$this->retError(0,"请重新登录");
    	}
    }
    
    //设置支付密码
    public function updatePayPassword($condition,$data){
    	if (!$condition || !$data) {
    		return false;
    	}else{
    		$this->modelUserDeposit = M('sysuser_user_deposit');//积分表
    		
    		return $this->modelUserDeposit->where($condition)->data($data)->save();
    	}
    }
    //获取用户信息
    public function getAccountInfo($condition){
    	if (!$condition) {
    		return false;
    	}else{
    		$modelUserAccount = M('sysuser_account');//用户登录表
    		return  $modelUserAccount->where($condition)->find();
    	}
    }
    
    //获取用户信息
    public function getUserInfo(){
    	$uid = $this->uid;
    	if($uid){
    		$userObj=M('sysuser_user');
    		$where['a.user_id'] = $uid;
    		$findUser = $userObj->alias('a')->join('sysuser_account b ON a.user_id= b.user_id')->where($where)->field('a.*,b.mobile')->find();
    		if($findUser){
    			if($findUser['birthday']){
    				$findUser['birthday'] = date("Y-m-d",$findUser['birthday']);
    			}
    			$this->retSuccess($findUser,"用户信息返回成功");
    		}else{
    			$this->retError(-1,'非法操作');
    		}
    	}else{
    		$this->retError(0,'请重新登录');
    	}
    }
    
    
    //设置昵称
    public function updateUsernickName(){
    	$uid = $this->uid;
    	$postData = I('post.');
    	$nickName = $postData['nickName'];
    	if(empty($nickName)){
    		$this->retError(-1,'请输入您的昵称');
    	}else{
    		if($uid){
    			$userObj=M('sysuser_user');
    			$where['user_id'] = $uid;
    			$findUser = $userObj->where($where)->find();
    			if($findUser){
    				$data['name'] = $nickName;
    				$bool = $userObj->where($where)->save($data);
    				if($bool){
    					$this->retSuccess(1,'昵称设置成功');
    				}else{
    					if($findUser['name']==$nickName){
    						$this->retSuccess(0,'您没有修改');
    					}else{
    						$this->retError(-3,'修改失败，系统繁忙，请稍后再试');
    					}
    				}
    			}else{
    				$this->retError(-2,'非法操作');
    			}
    		}else{
    			$this->retError(0,'请重新登录');
    		}
    	}
    }
    
    //修改生日
    public function updateUserBirthday(){
    	$uid = $this->uid;
    	$postData = I('post.');
    	$birthday = $postData['birthday'];
    	if(empty($birthday)){
    		$this->retError(-1,'请选择您的出生日期');
    	}else{
    		if($uid){
    			$userObj=M('sysuser_user');
    			$where['user_id'] = $uid;
    			$findUser = $userObj->where($where)->find();
    			if($findUser){
    				$birthday = strtotime($birthday);//把日期转换成时间戳
    				$data['birthday'] = $birthday;
    				$bool = $userObj->where($where)->save($data);
    				if($bool){
    					$this->retSuccess(1,'生日设置成功');
    				}else{
    					$this->retError(-3,'设置失败，系统繁忙，请稍后再试');
    				}
    			}else{
    				$this->retError(-2,'非法操作');
    			}
    		}else{
    			$this->retError(0,'请重新登录');
    		}
    	}
    } 
}