<?php
/**
  +------------------------------------------------------------------------------
 * InterfaceController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: InterfaceController.class.php v001 2016-06-02
 * @description 本地接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class TestController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->url='http://www.lishe.cn';
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelCategory=M('syscategory_cat');
		$this->modelCatConfig=M('company_category_config');
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
		$this->areaModel=M('site_area');
		
	}
	public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents("/data/www/b2b2c/public/business/logs/".$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}
		
	public function index(){
		echo date('Y-m-d H:i:s','1472182182');exit;
	}
	public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}
    
    //增加授权模拟请求方法 赵尊杰 2016-07-01
    public function accreditPost($url,$data,$user,$password){
        if(empty($url) || empty($data) || empty($user) || empty($password)){
            return false;
        }
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_USERPWD,''.$user.':'.$password.'');       
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
    
    public function updataStore(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header_url = $this->url."/business/index.php/Test/updataStore/?page=".($page+1);
		
		$Item = M('sysitem_item_store');
		$sku = M('sysitem_sku_store');
		$pageSize=30;
		$start=($page-1)*$pageSize;
		
		$Item_List = $Item->field('item_id,store,freez')->order('item_id DESC')->limit($start.','.$pageSize)->select();//查询所有的京东商品 分类为空且京东商品分类不为空的 数据
		if($Item_List){
			foreach($Item_List as $k=>$list){
				$store=0;
				$freez=0;
				$arr = $sku->field('store,freez')->where('item_id='.$list['item_id'])->select();
				foreach($arr as $key=>$value){
					$store+=$value['store'];
					$freez+=$value['freez'];
				}
				$Item->where('item_id='.$list['item_id'])->save(array('store'=>$store,'freez'=>$freez));
				echo $list['item_id']."处理完毕<br />";
			}
			//跳转请求至下一页
			echo '<script type="text/javascript">window.location.href="'.$header_url.'"</script>';
		}else{
			echo "没有数据了";
			exit;
		}
	}
	
	//计算毛利率、更新上下架状态
	public function updateItem(){		
		header("Content-type:text/html;charset=utf-8");
		$page=isset($_GET['page'])?$_GET['page']:1;
		$header=$this->url."/business/index.php/Test/updateItem/?page=".($page+1);
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr=$this->modelItem->where('shop_id=10')->field('price,cost_price,item_id,shop_id')->limit($start.','.$pageSize)->order('item_id DESC')->select();
		if(empty($itemArr)){
			echo '更新完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		}
		foreach($itemArr as $key=>$value){
			$rate=($value['price']-$value['cost_price'])*100/$value['price'];
			$this->modelItem->where('item_id='.$value['item_id'])->save(array('profit_rate'=>$rate));
			if($rate>=15){
				$status=$this->modelItemStatus->where('item_id='.$value['item_id'])->find();
				if($status['is_force']==1){
					echo $value['item_id']."已上架<br />";
					echo $this->modelItemStatus->where('item_id='.$value['item_id'])->save(array('approve_status'=>'onsale'));
				}				
			}
			
			if($value['shop_id']==10){
				$skuArr=$this->modelItemSku->where('item_id='.$value['item_id'])->field('price,cost_price,sku_id')->find();
				if(empty($skuArr['sku_id']) or $skuArr['price']!=$value['price'] or $skuArr['cost_price']!=$value['cost_price']){
					$this->modelItem->where('item_id='.$value['item_id'])->save(array('disabled'=>1));
					echo $this->modelItemStatus->where('item_id='.$value['item_id'])->save(array('approve_status'=>'instock'));
				}
			}		
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}

	public function updatePrice(){		
		header("Content-type:text/html;charset=utf-8");
		$page=isset($_GET['page'])?$_GET['page']:1;
		$header=$this->url."/business/index.php/Test/updatePrice/?page=".($page+1);
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr=$this->modelItem->where('cost_price>0')->field('price,cost_price,item_id')->limit($start.','.$pageSize)->order('item_id DESC')->select();
		if(empty($itemArr)){
			echo '更新完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		}
		foreach($itemArr as $key=>$value){
			$itemId[]=$value['item_id'];
			$price=round($value['price'],1);
			if($price!=$value['price']){
				echo "更新".$value['item_id']."<br />";
				$this->modelItem->where('item_id='.$value['item_id'])->save(array('price'=>$price));
			}
			
		}
		$itemSkuArr=$this->modelItemSku->where('item_id IN('.implode(',',$itemId).')')->field('price,sku_id,item_id')->select();
		if(!empty($itemSkuArr)){
			foreach($itemSkuArr as $key=>$value){
				$price=round($value['price'],1);
				if($price!=$value['price']){
					$this->modelItemSku->where('sku_id='.$value['sku_id'])->save(array('price'=>$price));
				}
			}
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}
	
	            
	public function requestJdPost($url='', $data=''){
        if(empty($url) || empty($data)){
            return false;
        }      
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
	
	public function delOrder(){
		$condition=array('tid'=>array('in','1609070951242103'));
		echo M('sysaftersales_refunds')->where($condition)->delete();
		echo M('systrade_trade_cancel')->where($condition)->delete();
		echo '<br />';
		$payment=M('ectools_trade_paybill')->field('payment_id')->where($condition)->select();
		if(!empty($payment)){
			foreach($payment as $key=>$value){
				$paymentId[]=$value['payment_id'];
			}
		}
		echo M('ectools_trade_paybill')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_trade')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_order')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_refund')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_aftersales')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_refund_log')->where($condition)->delete();
		echo M('system_admin_trade_log')->where($condition)->delete();
		echo '<br />';
		if(!empty($paymentId)){
			unset($condition);
			$condition=array('payment_id'=>array('in',implode(',',$paymentId)));
			echo M('ectools_payments')->where($condition)->delete();
		}		
	}
	
		
	public function handEcardPay(){
		header("Content-type:text/html;charset=utf-8");
		$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');
    	
		//$empCode='1121452174';//员工编号
    	//$createTime=date('Y-m-d H:i:s');
    	//$totalFee=136.2;
    	//$tid=16082611284907450;
    	$url=C('API_AOSERVER').'card/insertOrderData';
    	$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderMoney='.$totalFee.'&orderNum='.$tid.'&orderTime='.$createTime.'&orderTotalMoney='.$totalFee.'&orderType=1'.C('API_AOSERVER_KEY'));
		$orderPost=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'orderMoney'=>$totalFee,
    		'orderNum'=>$tid,
    		'orderTime'=>$createTime,
    		'orderTotalMoney'=>$totalFee,
    		'orderType'=>1,
    		'sign'=>$sign
    	);
    	$returns=$this->accreditPost($url,json_encode($orderPost),$user,$password);
    	$rets=json_decode($returns,true);
    	if($rets['code']==100){
	    	$log=array(
				'payment_id'=>$tid,
				'tid'=>$tid,
				'sync_order_id'=>'',
				'log_type'=>'syncGd10086Order',
				'code'=>100,
				'partener'=>'gd10086',
				'modified_time'=>time()
			);
			$log['detail']='手动扣款成功,post:'.$postData.'data:'.json_encode($orderPost).',return:'.$returns;
			$this->makeSqlLog($log);
		}
    	print_r($rets);
	}
	
	public function handEcardRefund(){
		header("Content-type:text/html;charset=utf-8");
		$paymentId='16092516430164012511';
		$refundFee=130.1;
		$refundSn='16092516394912510';
		$empCode=1121453382;
		//$return=$this->ecardRefund($paymentId,$refundFee,$refundSn,$empCode);
		print_r(json_decode($return,TRUE));
	}
	
	//东莞移动E卡通订单退单接口 赵尊杰 2016-09-07
	public function ecardRefund($paymentId,$refundFee,$refundSn,$empCode){
		$url=C('API_AOSERVER').'card/updateOrderCannel';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');
    	$createTime=date('Y-m-d H:i:s');    	
		$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderNum='.$paymentId.'&rtnMoney='.$refundFee.'&rtnNum='.$refundSn.'&rtnTime='.$createTime.C('API_AOSERVER_KEY'));
		$param=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'orderNum'=>$paymentId,
    		'rtnMoney'=>$refundFee,
    		'rtnNum'=>$refundSn,
    		'rtnTime'=>$createTime,
    		'sign'=>$sign
    	);
    	$this->makeLog('refund','url:'.$url.' param:'.json_encode($param));
    	$return=$this->accreditPost($url,json_encode($param),$user,$password);    	
    	$this->makeLog('refund','param:'.json_encode($param).' return:'.$return);
    	return $return;
	}
	
	public function setCatNum(){	
		$categorysArr=$this->modelCategory->field('cat_id')->where('level=3')->select();
		foreach($categorysArr as $key=>$value){
			$cid=$value['cat_id'];
			$condition='i.cat_id='.$cid.' AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
			$count=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
			if($count==0){
				$this->modelCategory->where('cat_id='.$cid)->save(array('disabled'=>1));
				echo $cid.'已屏蔽';
				echo "<br />";
			}
		}
		
	}
	
	public function syncShopCat(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header= $this->url."/business/index.php/Test/syncShopCat/?page=".($page+1);
		$catArr= M('syscategory_cat')->where('level=2')->field('cat_name,jd_cid')->select();
		foreach($catArr as $key=>$value){
			$cat[$value['jd_cid']]=$value['cat_name'];
		}
		$shopCatArr= M('sysshop_shop_cat')->where('level=2 AND shop_id=10')->field('cat_name,cat_id')->select();
		foreach($shopCatArr as $key=>$value){
			$shopCat[$value['cat_name']]=$value['cat_id'];
		}
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr = $this->modelItem->field('item_id,jd_category')->where('jd_sku>0 AND shop_cat_id=\'0\'')->order('item_id DESC')->limit($start.','.$pageSize)->select();
		if(empty($itemArr)){
			echo '更新完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		}
		foreach($itemArr as $key=>$value){
			if(!empty($value['jd_category']) && empty($value['shop_cat_id'])){
				$catIdArr=explode(';',$value['jd_category']);
				$shopCatId=$shopCat[$cat[$catIdArr[1]]];
				if($shopCatId>0){
					$this->modelItem->where('item_id='.$value['item_id'])->save(array('shop_cat_id'=>','.$shopCatId.','));
				}else{
					$shopCatId=$shopCat[$cat[$catIdArr[0]]];
				}
				if(empty($shopCatId)){
					echo $value['item_id']."-".$value['jd_category']."找不到店铺分类<br />";
					file_put_contents('cat.txt','itemId:'.$value['item_id']."-".$value['jd_category']."找不到店铺分类\n",FILE_APPEND);
				}
			}			
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}
	
	public function updataCatID(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header_url = $this->url."/business/index.php/Test/updataCatID/?page=".($page+1);
		
		$Item = M('sysitem_item');
		$Category = M('syscategory_cat');
		$pageSize=30;
		$start=($page-1)*$pageSize;
		
		$Item_List = $Item->where('shop_id=10 AND (cat_id=0 OR cat_id=100000)')->field('item_id, jd_category')->order('item_id DESC')->limit($start.','.$pageSize)->select();//查询所有的京东商品 分类为空且京东商品分类不为空的 数据
		if($Item_List){
			foreach($Item_List as $k=>$list){
				$jd_category_array = explode(';',$list['jd_category']);
				$jdCid=$jd_category_array[2];				
				$cat_id_arr = $Category->field('cat_id')->where('jd_cid='.$jdCid)->find();
				$catId=$cat_id_arr['cat_id'];
				if($catId>0){
					$itemId=$list['item_id'];
					$ret=$Item->where('item_id='.$itemId)->save(array('cat_id'=>$catId));
					if($ret==0){
						echo $Item->_SQL();					
						echo $itemId.'更新失败！';
						exit;
					}
				}else{
					echo $list['jd_category'];
					echo "<br />";
					echo $jdCid.'不存在！';
				}
			}
			//跳转请求至下一页
			echo '<script type="text/javascript">window.location.href="'.$header_url.'"</script>';
		}else{
			echo "没有数据了";
			exit;
		}
	}
	
	public function findAddrId(){
		ini_set('max_execution_time', '1000000');  //设置最大执行时间
		// $sum=$this->addrModel->count();
		$addrInfo=$this->addrModel->field('addr_id,area')->select();
		if($addrInfo){
			foreach($addrInfo as $k=>$v){
				$addrId=trim($v['addr_id']);
				$areaArr=explode(':',trim($v['area']));
				$areaIdArr=explode('/',trim(trim($areaArr[1]),'/')); //去掉左右边多余的‘/’
				if(count($areaIdArr)==3){ //得到所有的三级
					$numThree=$areaIdArr[2];//得到第三级的ID
					//查找第四级的信息;
					$numFourInfo=$this->areaModel->where('jd_pid='.$numThree.' and level=4')->find();
					if($numFourInfo){
						$time=date('Ymd',time());
						//file_put_contents('logs/errAddressId/log'.$time.'.txt',$addrId."\r\n",FILE_APPEND);
						echo $addrId;
						echo '<br/>';
					}
				
				}
		
			}
		}
	}
	
	public function syncJdOrder(){
		header("Content-type:text/html;charset=utf-8");
		$sku[]=array(
			'id'=>997664,
			'num'=>80
		);
		$sku[]=array(
			'id'=>1523698,
			'num'=>80
		);
		$sku[]=array(
			'id'=>2206285,
			'num'=>80
		);
		
		$param = array(
			"thirdOrder"=>21061018160901,//第三方的订单单号
			"sku"=>$sku,
			"name"=>'秦焕钦',//收货人
			"province"=>19,//一级地址
			"city"=>1655,//2级地址
			"county"=>36102,//3级地址
			"town"=>0,//4级地址
			"address"=>'东莞市东城区380号 （东莞移动通信服务楼）',//详细地址
			"zip"=>'510000',//邮编
			"phone"=>'13580776066',//座机号
			"mobile"=>'13580776066',//手机号
			"email"=>'severs@lishe.cn',//必选 //邮箱
			"unpl"=>'', 
			"remark"=>'',//备注
			"invoiceState"=>2,//开票方式(1为随货开票，0为订单预借，2为集中开票 )
			"invoiceType"=>'2',//发票类型，1-普票，2-增值税发票
			"invoiceName"=>'蔡慧丽',//增值票收票人姓名
			"invoicePhone"=>'15811818115',//增值票收票人电话
			"invoiceProvice"=>'19',//增值票收票人所在省(京东地址编码)
			"invoiceCity"=>'1607',//增值票收票人所在市(京东地址编码)
			"invoiceCounty"=>'3155',//增值票收票人所在区/县(京东地址编码)
			"invoiceTown"=>'0',//
			"invoiceAddress"=>'高新区中区科研路9号比克科技大厦20楼2001-B',//增值票收票人所在地址
			"regCompanyName"=>'深圳礼舍科技有限公司',
			"regCode"=>'440300071136394',
			"regAddr"=>'深圳市南山区高新区中区科研路9号比克科技大厦20楼2001-B',
			"regPhone"=>'0755-66632121',
			"regBank"=>'中国建设银行深圳市莲花北支行',
			"regBankAccount"=>'44201567100052523926',
			"selectedInvoiceTitle"=>'5',//4个人，5单位
			"companyName"=>'深圳礼舍科技有限公司',//发票抬头  (如果selectedInvoiceTitle=5则此字段Y)
			"invoiceContent"=>'1',//1:明细，3：电脑配件，19:耗材，22：办公用品
			"paymentType"=>4,//1货到付款，2邮局付款，4在线支付（余额支付），5公司转账，6银行转账，7网银钱包， 101金采支付
			"isUseBalance"=>'1',//预存款【即在线支付（余额支付）】下单固定1 使用余额非预存款下单固定0 不使用余额
			"submitState"=>1,//是否预占库存，0是预占库存（需要调用确认订单接口），1是不预占库存
			"doOrderPriceMode"=>'',
			"orderPriceSnap"=>''
		);
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/uniteSubmit',json_encode($param));
    	$ret=json_decode($result,true);
    	//记录日志
    	$log=array(
			'payment_id'=>'001',
			'tid'=>'21061010093101',
			'log_type'=>'order',
			'code'=>$ret['code'],
			'detail'=>$result,
			'modified_time'=>time()
		);
		
    	if($ret['code']==100 && $ret['errCode']==0){
			$msg=$ret['data']['jdOrderId'];
			$log['sync_order_id']=$ret['data']['jdOrderId'];
		}else{
			$msg=-1;
		}
		M('systrade_sync_log')->add($log);		
    	print_r($ret);exit;
	}
	
	public function handJdOrder(){
		header("Content-type:text/html;charset=utf-8");
		$sku[]=array(
			'id'=>1695535,
			'num'=>70
		);
		$trade=array(
			'tid'=>'21061024160801',
			'receiver_name'=>'彭丽君',
			'receiver_address'=>' 南山区高新区中区科研路9号比克科技大厦20楼2001B',
			'receiver_phone'=>'15112575383',
			'receiver_mobile'=>'15112575383',			
		);
		$province=19;
		$city=1607;
		$county=3155;
		$town=0;

		$param = array(
			"thirdOrder"=>$trade['tid'],//第三方的订单单号
			"sku"=>$sku,
			"name"=>$trade['receiver_name'],//收货人
			"province"=>intval($province),//一级地址
			"city"=>intval($city),//2级地址
			"county"=>intval($county),//3级地址
			"town"=>intval($town),//4级地址
			"address"=>$trade['receiver_address'],//详细地址
			"zip"=>'100000',//邮编
			"phone"=>$trade['receiver_phone'],//座机号
			"mobile"=>$trade['receiver_mobile'],//手机号
			"email"=>'severs@lishe.cn',//必选 //邮箱
			"unpl"=>'', 
			"remark"=>'',//备注
			"invoiceState"=>2,//开票方式(1为随货开票，0为订单预借，2为集中开票 )
			"invoiceType"=>'2',//发票类型，1-普票，2-增值税发票
			"invoiceName"=>'蔡慧丽',//增值票收票人姓名
			"invoicePhone"=>'15811818115',//增值票收票人电话
			"invoiceProvice"=>'19',//增值票收票人所在省(京东地址编码)
			"invoiceCity"=>'1607',//增值票收票人所在市(京东地址编码)
			"invoiceCounty"=>'3155',//增值票收票人所在区/县(京东地址编码)
			"invoiceTown"=>'0',//
			"invoiceAddress"=>'高新区中区科研路9号比克科技大厦20楼2001-B',//增值票收票人所在地址
			"regCompanyName"=>'深圳礼舍科技有限公司',
			"regCode"=>'440300071136394',
			"regAddr"=>'深圳市南山区高新区中区科研路9号比克科技大厦20楼2001-B',
			"regPhone"=>'0755-66632121',
			"regBank"=>'中国建设银行深圳市莲花北支行',
			"regBankAccount"=>'44201567100052523926',
			"selectedInvoiceTitle"=>'5',//4个人，5单位
			"companyName"=>'深圳礼舍科技有限公司',//发票抬头  (如果selectedInvoiceTitle=5则此字段Y)
			"invoiceContent"=>'1',//1:明细，3：电脑配件，19:耗材，22：办公用品
			"paymentType"=>4,//1货到付款，2邮局付款，4在线支付（余额支付），5公司转账，6银行转账，7网银钱包， 101金采支付
			"isUseBalance"=>'1',//预存款【即在线支付（余额支付）】下单固定1 使用余额非预存款下单固定0 不使用余额
			"submitState"=>1,//是否预占库存，0是预占库存（需要调用确认订单接口），1是不预占库存
			"doOrderPriceMode"=>'',
			"orderPriceSnap"=>''
		);
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/uniteSubmit',json_encode($param));
		$ret=json_decode($result,true);
		echo "<pre>";
		print_r($ret);
		echo "</pre>";
	}
	
	public function handRecharge(){
		$param = array(
			"paymentId"=>'2016101319241123701',
			"paidFee"=>'1',
			"tradeNo"=>'2016101321001004390240882420'
		);
		$result=$this->requestPost($this->url.'/api.php/Api/apiDoRecharge',$param);
		$ret=json_decode($result,true);
		echo "<pre>";
		print_r($ret);
		echo "</pre>";
	}
	
	public function opImg(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header_url = $this->url."/business/index.php/Test/opImg/?page=".($page+1);
		$pageSize=1;
		$start=($page-1)*$pageSize;
		$itemArr=$this->modelItem->field('item_id,image_default_id,list_image,outer_default_img,outer_imglist')->where("outer_imglist<>''")->order('item_id DESC')->limit($start.','.$pageSize)->select();
		$item=$itemArr[0];
		$default=str_replace('http://www.lishe.cn/images/jdImages/','images/jd'.date('Ymd').'/',$item['image_default_id']);
		$cd=$this->creatDir($default);
		if($cd){
			$this->getImage($item['outer_default_img'],$default);
			$list=explode(',',$item['list_image']);
			$outer=explode(',',$item['outer_imglist']);
			
			foreach($list as $key => $value){
				$value=str_replace('http://www.lishe.cn/images/jdImages/','images/jd'.date('Ymd').'/',$value);
				$this->getImage($outer[$key],$value);
			}
			$data=array(
				'image_default_id'=>str_replace('images/jdImages/','images/jd'.date('Ymd').'/',$item['image_default_id']),
				'list_image'=>str_replace('images/jdImages/','images/jd'.date('Ymd').'/',$item['list_image'])
			);
			$this->modelItem->where('item_id='.$item['item_id'])->save($data);
			echo '<script type="text/javascript">window.location.href="'.$header_url.'"</script>';
		}else{
			echo $item['item_id'].'创建目录失败';
			exit;
		}
	}
	
	public function getImage($url,$filename='',$type=1){
		if($url==''){return false;}
		//文件保存路径
		if($type){
			$ch=curl_init();
			$timeout=5;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			$img=curl_exec($ch);
			curl_close($ch);
		}else{
			ob_start();
			readfile($url);
			$img=ob_get_contents();
			ob_end_clean();
		}
		$size=strlen($img);
		//文件大小
		$dir = '/data/www/b2b2c/public/';
		$fp2=fopen($dir.$filename,'w');
		fwrite($fp2,$img);
		fclose($fp2);
		//生成缩略图
		$ext=strrchr($url,".");	
		$img_m=$dir.$filename."_m".$ext;
		$img_t=$dir.$filename."_t".$ext;
		$img_l=$filename."_l".$ext;
		$this->img2thumb($dir.$filename, $img_m, 300, 300);
		$this->img2thumb($dir.$filename, $img_t, 100, 100);
		$this->img2thumb($dir.$filename, $img_l, 100, 100);
		return $filename;
	}
	
	public function creatDir($fileName){
		if(strpos($fileName,'/')){
			$dirArray = explode('/',$fileName);
			array_pop($dirArray);
			$dir = '/data/www/b2b2c/public/';
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
	
	/**
	 * 生成缩略图
	 * @author yangzhiguo0903@163.com
	 * @param string     源图绝对完整地址{带文件名及后缀名}
	 * @param string     目标图绝对完整地址{带文件名及后缀名}
	 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
	 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
	 * @param int        是否裁切{宽,高必须非0}
	 * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
	 * @return boolean
	 */
	public function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
	{
		if(!is_file($src_img))
		{
			return false;
		}
		$ot = $this->fileext($dst_img);
		$otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
		$srcinfo = getimagesize($src_img);

		$src_w = $srcinfo[0];
		$src_h = $srcinfo[1];
		$type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
		
		$createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
	 
		$dst_h = $height;
		$dst_w = $width;
		$x = $y = 0;
	 
		/**
		 * 缩略图不超过源图尺寸（前提是宽或高只有一个）
		 */
		if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
		{
			$proportion = 1;
		}
		if($width> $src_w)
		{
			$dst_w = $width = $src_w;
		}
		if($height> $src_h)
		{
			$dst_h = $height = $src_h;
		}
	 
		if(!$width && !$height && !$proportion)
		{
			return false;
		}
		if(!$proportion)
		{
			if($cut == 0)
			{
				if($dst_w && $dst_h)
				{
					if($dst_w/$src_w> $dst_h/$src_h)
					{
						$dst_w = $src_w * ($dst_h / $src_h);
						$x = 0 - ($dst_w - $width) / 2;
					}
					else
					{
						$dst_h = $src_h * ($dst_w / $src_w);
						$y = 0 - ($dst_h - $height) / 2;
					}
				}
				else if($dst_w xor $dst_h)
				{
					if($dst_w && !$dst_h)  //有宽无高
					{
						$propor = $dst_w / $src_w;
						$height = $dst_h  = $src_h * $propor;
					}
					else if(!$dst_w && $dst_h)  //有高无宽
					{
						$propor = $dst_h / $src_h;
						$width  = $dst_w = $src_w * $propor;
					}
				}
			}
			else
			{
				if(!$dst_h)  //裁剪时无高
				{
					$height = $dst_h = $dst_w;
				}
				if(!$dst_w)  //裁剪时无宽
				{
					$width = $dst_w = $dst_h;
				}
				$propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
				$dst_w = (int)round($src_w * $propor);
				$dst_h = (int)round($src_h * $propor);
				$x = ($width - $dst_w) / 2;
				$y = ($height - $dst_h) / 2;
			}
		}
		else
		{
			$proportion = min($proportion, 1);
			$height = $dst_h = $src_h * $proportion;
			$width  = $dst_w = $src_w * $proportion;
		}
	 
		$src = $createfun($src_img);
		$dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
		$white = imagecolorallocate($dst, 255, 255, 255);
		imagefill($dst, 0, 0, $white);
	 
		if(function_exists('imagecopyresampled'))
		{
			imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		}
		else
		{
			imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		}
		$otfunc($dst, $dst_img);
		imagedestroy($dst);
		imagedestroy($src);
		return true;
	}

	
	public function fileext($file){
		return pathinfo($file, PATHINFO_EXTENSION);
	}
	
	public function statFreez(){
		$skuObj = M('sysitem_sku_store');
		$tradeObj = M('systrade_trade');
		$orderObj = M('systrade_order');
		$trade=$tradeObj->field('tid')->where("status='WAIT_BUYER_PAY'")->order('created_time DESC')->select();
		foreach($trade as $key=>$value){
			$tid[]=$value['tid'];
		}
		$condition=array('tid'=>array('in',implode(',',$tid)));
		$order=$orderObj->field('sku_id,num')->where($condition)->select();
		foreach($order as $key=>$value){
			$num[$value['sku_id']]+=$value['num'];
		}
		foreach($num as $key=>$value){
			echo $skuObj->where('sku_id='.$key)->save(array('freez'=>$value));
			echo "<br />";
			echo $key.'执行完毕';
			echo "<br />";
		}
	}
	
	public function payJdOrder(){
		$orderId=trim($_GET['id']);
		if(empty($orderId)){
			echo 'orderId is empty';
			exit;
		}
		$ret=A('Interface')->payJdOrder($orderId);
		print_r($ret);
		return pathinfo($file, PATHINFO_EXTENSION);
	}
	
	
}