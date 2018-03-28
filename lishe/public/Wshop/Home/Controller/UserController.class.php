<?php
/**
  +------------------------------------------------------------------------------
 * UserController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: UserController.class.php v001 2016-06-02
 * @description 短信接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class UserController extends CommonController {
	public function __construct(){
		parent::__construct();
        if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        } 		
		$this->modelDeposit=M('sysuser_user_deposit'); //用户积分表
		$this->modelAccount=M('sysuser_account'); //用户登录信息表
		$this->modelUserCoupon=M("sysuser_user_coupon");
		$this->modelTrade=M('systrade_trade');//订单主表
		$this->modelUser=D('User');//订单主表
	}

	// 用户中心  20160729 start
	public function userCenter(){
		$orderList = $this->modelTrade->where(array('user_id'=>$this->uid,'status'=>array('neq','TRADE_FINISHED')))->field('status')->select();
		$count['WAIT_BUYER_PAY'] = 0;//待付款 WAIT_BUYER_PAY
		$count['WAIT_SELLER_SEND_GOODS'] = 0;//待发货 WAIT_SELLER_SEND_GOODS
		$count['WAIT_BUYER_CONFIRM_GOODS'] = 0;//待收货 WAIT_BUYER_CONFIRM_GOODS
		$count['WAIT_COMMENT'] = 0;//待评价 WAIT_COMMENT

		foreach($orderList as $item){
			if($item['status'] == "WAIT_BUYER_PAY"){
				$count['WAIT_BUYER_PAY']++;
			}
			if ($item['status'] == "WAIT_SELLER_SEND_GOODS") {
				$count['WAIT_SELLER_SEND_GOODS']++;
			}
			if ($item['status'] == "WAIT_BUYER_CONFIRM_GOODS") {
				$count['WAIT_BUYER_CONFIRM_GOODS']++;
			}
			if ($item['status'] == "WAIT_COMMENT") {
				$count['WAIT_COMMENT']++;
			}
		}
		$condition =array('user_id'=>$this->uid);
		$userInfo = $this->modelUser->getAccountInfo($condition);
		$depositInfo=$this->modelDeposit->where('user_id='.$this->uid)->field('balance,commonAmount,limitAmount,comName')->find();
		if($depositInfo){
			$this->assign('depositInfo',$depositInfo);
		}
		$this->assign("userInfo",$userInfo);
		$this->assign("count",$count);
		$this->display();
	}

	//修改基本资料 
	public function editUserInfo(){
		$condition =array('user_id'=>$this->uid);
		$userInfo = $this->modelUser->getAccountInfo($condition);
		$this->assign('account',$this->account);
		$this->assign('Name',$userInfo['name']);
		$this->display();
	}

	//修改资料信息
	public function updateUserInfo(){
		$name=I('post.nickName','','trim');
		if($name==''){
			echo json_encode(array(0,'请填写您的昵称！'));
			exit;
		}
		$dataName=array(
			'name'=>$name
			);
		$condition['user_id'] = $this->uid;
		$chgName=$this->modelUser->updateUserName($condition,$dataName);
   		if($chgName){
   			echo json_encode(array(1,'修改成功！'));
   			exit;
   		}else{   			
   			echo json_encode(array(0,'修改失败！'));
   			exit;
   		}
	}

	// 修改密码操作 
	public function changePwd(){
		$oldPass=I('post.oldpwd','','trim');
		$newPass=I('post.pwd','','trim');
		$rePass=I('post.rpwd','','trim');
		if($oldPass==''){
			echo json_encode(array(0,'请填写旧密码！'));
			exit;
		};
		if($newPass==''){
			echo json_encode(array(0,'请填写新密码密码！'));
			exit;
		};
		if(strlen($newPass) < 6 || strlen($newPass) > 18){
			echo json_encode(array(0,'密码长度不能小于6，超过18！'));
			exit;
		};
		if($newPass != $rePass){
			echo json_encode(array(0,'密码和确认密码必须相同！'));
			exit;
		}
		$condition['user_id'] = $this->uid;		
        $accountInfo = $this->modelUser->getAccountInfo($condition);
		$phoneNum=$accountInfo['mobile'];
		$sign=md5('doType=up&newPass='.$newPass.'&oldPass='.$oldPass.'&phoneNum='.$phoneNum.C('API_KEY'));
		$data=array(
			'phoneNum'=>$phoneNum,
			'oldPass'=>$oldPass,
			'doType'=>'up',
			'newPass'=>$newPass,
			'sign'=>$sign
			);
		$res=$this->requestPost(C('API').'mallUser/updatePass',$data);	
        $res = trim($res, "\xEF\xBB\xBF");//去除BOM头
		$info=json_decode($res,TRUE);
		if($info['result']==100){
			echo json_encode(array(1,'修改成功！'));
			exit;
		}else{
			echo json_encode(array(0,$info['msg']));
			exit;
		}

	}

	// 客服 20160805 start
	public function customerServer(){
		$this->display('customServer');
	}
	// 客服 20160805 end

	//我的资产
	public function assets(){
		$depositInfo = $this->modelDeposit->where("user_id=".$this->uid)->find();
		$userCouponCount = $this->modelUserCoupon->where("user_id=".$this->uid)->count();
		$this->assign('userCouponCount',$userCouponCount);
		$this->assign('depositInfo',$depositInfo);
		$this->display();
	}

	//我的优惠卷
	public function coupons(){
		$userCouponInfo = $this->modelUserCoupon->field('pc.coupon_name,pc.coupon_desc,pc.deduct_money,pc.canuse_start_time,pc.canuse_end_time,pc.limit_money')->table('sysuser_user_coupon uc,syspromotion_coupon pc')->where('uc.user_id='.$this->uid.' and uc.coupon_id = pc.coupon_id')->select();
		$this->assign('userCouponInfo',$userCouponInfo);
		$this->display();
	}

	//用户收货地址列表
    public function addrList(){
    	$refer = I('refer');
    	$url = str_replace("-","/",$url);
        if(empty($url)){
            $url=$_SERVER['HTTP_REFERER'];
            $url = str_replace("-","/",$url);
        }
        $this->assign("refer",urldecode($url));
       	$condition=array(
            'user_id'=>$this->uid
            );
        $addrList=$this->modelUser->getUserAddressList($condition);
        if($addrList){
            foreach($addrList as $k=>$v){
              $addrArr=explode(':',$v['area']);
              $addrList[$k]['area']=$addrArr[0];
            }
            $this->assign('addrList',$addrList); 
        }
      	$this->display('addressList');
    }

    // 删除收货地址
    public function deleteAddr(){
		$addrId=I('get.addr_id',0,'intval');
		$condition=array(
			'addr_id'=>$addrId,
			'user_id'=>$this->uid 
		);
		$delResult=$this->modelUser->delAddress($condition);
		if($delResult){
			$url = __APP__.'/User/addrList';
			header("location:$url");
			exit;
		}
    }
    //添加收货地址
    public function addAddress(){
    	$refer=I('refer');
        if(empty($refer)){
            $refer=$_SERVER['HTTP_REFERER'];
        }
        $this->assign('refer',urldecode($refer));
    	$this->display();
    }
    //地址操作（增加和修改地址）
    public function opAddress(){
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
	            echo json_encode(array(0,'地址不正确！'));
	            exit;
          	}
        }
        
        if ($townId) {
        	$addrDetail = $province.'/'.$city.'/'.$county.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$countyId.'/'.$townId;
        }else{
        	$addrDetail=$province.'/'.$city.'/'.$county.':'.$provinceId.'/'.$cityId.'/'.$countyId;
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
          	echo json_encode(array(1,'操作成功！'));
          	exit;
        }else{
          	echo json_encode(array(0,'操作失败！'));
          	exit;
        } 	

    }

    // 编辑添加收货地址 start
    public function editAddress(){
      	$addrId=I('get.addrId',0,'intval');
    	$refer=I('refer');
        if(empty($refer)){
            $refer=$_SERVER['HTTP_REFERER'];
        }
      	if (!$addrId) {
      		$this->error("系统繁忙，请刷新重试！");
      	}      	
      	$condition=array(
        	'addr_id'=>$addrId,
        	'user_id'=>$this->uid
        );
        $addressInfo = $this->modelUser->getAddressInfo($condition);
        if (!$addressInfo) {
        	$this->error("系统繁忙，请刷新重试！");
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
        $this->assign('refer',urldecode($refer));
		$this->assign('provinceInfo',$provinceInfo);
		$this->assign('cityInfo',$cityInfo);
		$this->assign('countyInfo',$countyInfo);
		$this->assign('townInfo',$townInfo);
		$this->assign('addressInfo',$addressInfo);
		$this->display();
    }


    //修改默认的收货地址
    public function modifyDefAddr(){
       	$addressId=I('post.addressId',0,'intval');
       	$condition=array(
          	'addr_id'=>$addressId,
          	'user_id'=>$this->uid
        );
       	$result = $this->modelUser->updateDefaultAddress('user_id='.$this->uid,'def_addr=0');
       	$res = $this->modelUser->updateDefaultAddress($condition,'def_addr=1');
   		if($result && $res){
        	echo json_encode(array(1,"修改成功！"));
       	}else{
       		echo json_encode(array(1,"修改失败，请重试！"));
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
    		echo json_encode(array(1,$siteList));
    	}else{
    		echo json_encode(array(0,"无下级地址"));
    	}
    	
    	
    }

    //设置支付密码
    public function setPayPwd(){
    	$refer=I('refer');
        if(empty($refer)){
            $refer=$_SERVER['HTTP_REFERER'];
        }
        $paymentid = I('get.paymentid');
        $this->assign('refer',urldecode($refer));
        $this->assign('paymentid',$paymentid);
        $this->display();
    }
    //设置支付密码操作
    public function doSetPayPwd(){
        $data['md5_password'] = md5(I('post.payPwd'));
        $condition['user_id'] = $this->uid;
        $res = $this->modelUser->updatePayPassword($condition,$data);
        if ($res) {
            echo json_encode(array(1,'修改成功！'));
        }else{
            echo json_encode(array(0,'修改失败，请重试！'));
        }
    }
    //修改支付密码
    public function retrievePwd(){
    	$refer=I('refer');
        if(empty($refer)){
            $refer=$_SERVER['HTTP_REFERER'];
        }
        $paymentid = I('get.paymentid');
        $condition['user_id'] = $this->uid;
        $accountInfo = $this->modelUser->getAccountInfo($condition);
        $this->assign('refer',urldecode($refer));
        $this->assign('accountInfo',$accountInfo);
        $this->assign('paymentid',$paymentid);
        $this->display();
    }
    //修改支付密码
   	public function doRetrievePwd(){
        $phoneCode = I('post.code');
        $data['md5_password'] = md5(I('post.payPwd'));
        $condition['user_id'] = $this->uid;
        if (md5($phoneCode) == cookie('phoneCode') || md5($phoneCode) == session('phoneCode')) {
            $res = $this->modelUser->updatePayPassword($condition,$data); 
	        if ($res) {
	            echo json_encode(array(1,'修改成功！'));
	        }else{
	            echo json_encode(array(0,'修改失败，请重试！'));
	        }
        }else{
            echo json_encode(array(0,'验证码错误！'));
        }
    }
    
    /**********************************************************************************************************************/
	/*******************************************************订单列表*******************************************************/	
    /**********************************************************************************************************************/

    // $aftersalesStatus=array(
    //     'WAIT_EARLY_PROCESS'=>'待审核',
    //     'WAIT_PROCESS'=>'待商家审核',
    //     'SELLER_REFUSE'=>'商家拒绝',
    //     'REFUND_PROCESS'=>'待退款',
    //     'WAIT_BUYER_SEND_GOODS'=>'待用户回寄',
    //     'WAIT_SELLER_CONFIRM_GOODS'=>'待商家收货',
    //     'SELLER_SEND_GOODS'=>'商家已回寄',
    //     'SUCCESS'=>'已完成',
    // );  
    // $this->assign('aftersalesStatus',$aftersalesStatus);  

    //订单状态 开始 20160729
    // public function orderStatus(){
    // 	redirect(__APP__."/Order/orderList");
    // }
    //订单列表
    public function orderList(){
		$status=I('get.status','');
		if (empty($status)) {
			$condition =array('user_id'=>$this->uid);
		}elseif($status == 'NO_APPLY'){
			$condition =array('user_id'=>$this->uid,'order_status'=>array('neq','NO_APPLY'));
		}else{
			if ($status == 'WAIT_SELLER_SEND_GOODS') {
				$condition =array('user_id'=>$this->uid,'status'=>array('in',array('WAIT_SELLER_SEND_GOODS','IN_STOCK')));
			}else{				
				$condition =array('user_id'=>$this->uid,'status'=>$status);
			}
		}
		$size = 10;
		$number = $this->modelUser->getOrderCount($condition);
		$page = new \Think\PagePn($number, $size);
		$rollPage = 1;
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$style = "custom-paginations-prev";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串	
		$orderList = $this->modelUser->getOrderList($condition,$limit);
		if ($orderList) {
			foreach ($orderList as $key => $value) {
				$shopIds[$key] = $value['shop_id'];
				$tids[$key] = $value['tid'];
				$trade[$value['tid']]=$value;
			}
			//查询店铺信息
			$shopIdStr=implode(',',$shopIds);		
			$conditionShop['shop_id']=array('in',$shopIdStr);
			$shopList = $this->modelUser->getShopList($conditionShop);
			foreach ($trade as $key => $value) {
				foreach ($shopList as $k => $val) {
					if ($value['shop_id']  == $val['shop_id']) {
						$trade[$key]['shopInfo'] = $val;
					}
				}
			}
			//查询商品信息		
			$tidsStr=implode(',',$tids);
			$conditionTid['tid'] = array('in',$tidsStr);
			$orderItemList = $this->modelUser->getOrderItemList($conditionTid);
			foreach ($orderItemList as $key => $value) {
				$order[$value['tid']][$value['oid']]=$value;
			}
			//查询支付子表
			unset($condition);		
			$tidsStr=implode(',',$tids);
			$condition['tid'] = array('in',$tidsStr);
			$condition['user_id'] = $this->uid;	
			$paymentBillList = $this->modelUser->getPaymentBillList($condition);

			$payIds = array();
				foreach ($paymentBillList as $key => $value) {
					$resultPaymentList[$value['tid']]['payId']= $value['payment_id'];
					$resultPaymentList[$value['tid']]['status']= $value['status'];
					$resultPaymentList[$value['tid']]['ctime']= ($value['created_time']+60*60*24) < time() ? 0 : 1 ;
					$payIds[] = $value['payment_id'];
				}
				//查询支付主表信息
				$payIdsStr=implode(',',array_unique($payIds));
				$conditionPayId['payment_id'] = array('in',$payIdsStr);
				$field = array('payment_id','cash_fee','point_fee','payed_cash','payed_point');
				$paymentList = $this->modelUser->getPaymentList($conditionPayId,$field);
				$paymentIdList = array();
				if (!empty($paymentList)) {
					foreach ($paymentList as $key => $value) {
						$paymentIdList[$value['payment_id']] = $value;
					}
				}
				$paymentTrade = array();
				foreach ($resultPaymentList as $krpl => $vrpl) {
					$paymentTrade[$vrpl['payId']]['paymentId'] = $vrpl['payId'];
					$paymentTrade[$vrpl['payId']]['payStatus'] = $vrpl['status'];
					$paymentTrade[$vrpl['payId']]['ctime'] = $vrpl['ctime'];
					$paymentTrade[$vrpl['payId']]['tradeInfo'][$krpl]['trade']=$trade[$krpl];
					$paymentTrade[$vrpl['payId']]['tradeInfo'][$krpl]['order']=$order[$krpl];
					$paymentTrade[$vrpl['payId']]['paymentInfo'] = $paymentIdList[$vrpl['payId']];					
				}
				$this->assign('paymentTrade',$paymentTrade);
				//var_dump($paymentTrade);
			// foreach ($orderList as $key => $value) {
			// 	foreach ($shopList as $ke => $val) {
			// 		if ($value['shop_id'] == $val['shop_id']) {
			// 			$orderList[$key]['shopInfo'] = $val;
			// 		}
			// 	}
			// 	foreach ($orderItemList as $k => $v) {
			// 		if ($value['tid'] == $v['tid']) {
			// 			$orderList[$key]['orderList'][$k]= $v;
			// 		}
			// 	}
			// }
			//var_dump($orderItemList);
			//$this->assign('orderList',$orderList);
		}
		$this->assign('pagestr',$pagestr);
		$this->assign('status',$status);
		$this->display();
    }
   

    // 订单状态的修改
	public function orderChgStatus(){
		$orderId=I('get.orderId','','trim');
		$status=I('get.status','','trim');
		if (!$orderId || !$status) {
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}
		$condition=array(
			'tid'=>$orderId,
			'user_id'=>$this->uid
		);
		if ($status == 'CANCEL') {			
			//用户取消
			$data['status']='TRADE_CLOSED_BY_USER';
			$data['modified_time']=time();
			$res = $this->modelUser->updateTrade($condition,$data);
			$result = $this->modelUser->updateOrder($condition,$data);
			if ($res && $result) {
				echo json_encode(array(1,'订单取消成功！'));
				exit();
			}else{
				echo json_encode(array(0,'订单取消失败！'));
				exit();
			}
		}elseif($status == 'CONFIRM'){
			//确认收货
			$data=array(
				'status'=>'TRADE_FINISHED',
				'modified_time'=>time()
			);
			$res = $this->modelUser->updateTrade($condition,$data);
			$result = $this->modelUser->updateOrder($condition,$data);
			if ($res && $result) {
				echo json_encode(array(1,'确认收货成功！'));
				exit();
			}else{
				echo json_encode(array(0,'确认收货失败！'));
				exit();
			}
		}else{
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}

	}
    public function orderRefund(){ //申请退款页面
      $tid=I('get.tid','','trim');
      $currStautsNum=I('get.currStautsNum','','trim');
      $this->assign('currStautsNum',$currStautsNum);
      $this->assign('tid',$tid);
      $this->display();
    }     


    //20160829 物流显示页面 开始
    public function flow(){
		$orderId=I('get.orderId',0,'trim');
		$where['tid']=$orderId;
		$where['user_id']=$this->uid;
		$logiNo=$this->deliveryModel->where($where)->field('tid,logi_no,shop_id,logi_name')->find();
		$this->assign('OrderId',$logiNo['tid']);
		if($logiNo['shop_id']==10){
			if($logiNo['logi_no']){
				$logiNoArr=explode(',',trim($logiNo['logi_no']));
				$this->assign('expressNumber',$logiNoArr);
			}
		}else{
			$this->assign('baseInfo',$logiNo);
		}
		$this->display();
    }
    
	public function ajaxFlow(){
		$orderId=I('get.orderId',0,'trim');
		$where['tid']=$orderId;
		$where['user_id']=$this->uid;
		$logiNo=$this->deliveryModel->where($where)->field('tid,logi_no,shop_id,logi_name')->find();
		if($logiNo['shop_id']==10){
			if($logiNo['logi_no']){
				$logiNoArr=explode(',',trim($logiNo['logi_no']));
				if(I('logiNo')){
					$JdflowId=I('logiNo');
				}else{
					$JdflowId=$logiNoArr[0];
				}
				$url="http://www.lishe.cn/business/api.php/Interface/getJdExpress/";
				$data=array(
					'orderId'=>$JdflowId
				);
				$res=$this->requestPost($url,$data);
				$res=json_decode($res,true);
				$this->assign('expressInfo',$res['data']['orderTrack']);
				//当前页订单

			}
		}else{
			$this->assign('baseInfo',$logiNo);
		}			
		$this->display();
	}
    //20160829 物流显示页面 结束

	//用户修改资料
    public function userSetup(){
    	$this->display();
    }
    //创建目录
	public function creatDir(){
		if (empty($fileName)) {
			$fileName = '/home/wwwroot/bbc/public/Upload/headImg/'.date('Ym').'/';
		}
		if(strpos($fileName,'/')){
			$dirArray = explode('/',$fileName);
			array_pop($dirArray);
			foreach($dirArray as $val){
				$dir .= $val.'/';
				$oldumask = umask(0);
				if(!is_dir($dir)){
					mkdir($dir,0777);
				}
				chmod($dir,0777);
				umask($oldumask);
			}
			return true;
		}
		return false;
	}
    public function uploadImg(){
	    $path = 'Upload/headImg/'.date('Ym').'/';
	    $ispath = $this->creatDir($path);
	    $base64_string = $_POST['base64_string'];
	    $savename = uniqid().'.jpeg';//localResizeIMG压缩后的图片都是jpeg格式
	    $savepath = $path.$savename; 
	    $image = $this->base64_to_img( $base64_string, $savepath );
	    if($image){
	        echo '{"status":1,"content":"上传成功","url":"'.$image.'","路径":"'.$ispath.'"}';
	    }else{
	        echo '{"status":0,"content":"上传失败"}';
	    }  
    }

    private function base64_to_img( $base64_string, $output_file ) {
        $ifp = fopen( $output_file, "wb" ); 
        fwrite( $ifp, base64_decode( $base64_string) ); 
        fclose( $ifp ); 
        return( $output_file ); 
    }

    public function test(){
    	$this->display();
    }
    
}