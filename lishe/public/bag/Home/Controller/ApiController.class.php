<?php
namespace Home\Controller;
use Think\Controller;
use Think\Exception;
class ApiController extends ApiBaseController {
	
	//还是申明一下吧
    private $sysitemItem;
    private $itemStore;
    private $sysitemSku;
    private $skuStoreModel;
    private $itemCount;
    private $giftTrade;
    private $giftTradeBag;
//     private $sysuserAccount;
//     private $sysuserUser;
//     private $sysuserUserDeposit;
	private $sysAccount;
    private $siteArea;
    private $sysTrade;
    private $systradeOrder;
    private $giftReceiverAddr;
    private $giftGroupOrder;
    private $giftGroupOrderRecever;
    private $giftBag;
    private $activityTrade;
    private $activityOrder;
    private $ectoolsPayments;
    private $ectoolsTradePaybill;
    private $activityCategory;
    private $activityItem;
    
    /**
     * 构造函数
     */
	public function __construct(){
		parent::__construct();
		
		/* 不喜欢在这里实例化
		 * 但是为了和之前的代码保持一致，还是写了
		 */
		
		$this->giftTrade 	= M('gift_trade');
		$this->giftTradeBag = M('gift_trade_bag');
		$this->sysitemItem 	= M('sysitem_item'); 
		$this->sysitemSku 	= M('sysitem_sku');
		$this->itemStore 	= M('sysitem_item_store');//商品库存
		$this->skuStoreModel= M('sysitem_sku_store');//货品的库存
		$this->itemCount 	= M('sysitem_item_count');
// 		$this->sysuserAccount = M('sysuser_account');
// 		$this->sysuserUser 	  = M('sysuser_user');
// 		$this->sysuserUserDeposit = M('sysuser_user_deposit');
		$this->sysAccount = D('account');
		$this->siteArea = M('site_area');
		$this->sysTrade = M('systrade_trade');
		$this->systradeOrder = M('systrade_order');
		$this->giftReceiverAddr = M('gift_receiver_addr');
		$this->giftGroupOrder = M('gift_group_order');
		$this->giftGroupOrderRecever = M('gift_group_order_recever');
		//$this->giftBag = M('gift_bag');
		$this->activityTrade = M('company_activity_trade');
		$this->activityOrder = M('company_activity_order');
		$this->ectoolsPayments = M('ectools_payments');
		$this->ectoolsTradePaybill = M('ectools_trade_paybill');
		$this->activityCategory = M('company_activity_category');
		$this->activityItem = M('company_activity_item');
		$this->itemStatus = M('sysitem_item_status');
	}
	
	/**
	 * 接收下单推送，此接口为下单后调用，关于item的库存、上下线状态，需要再次考虑？？
	 * 返回交易id和接收链接
	 * @author Gaolong
	 */
	public function trade(){
    	$bagId 		= I('post.bagId', -1,'intval');
    	$itemStr 	= I('post.items','','trim'); //数组[{"itemId":123,"num":21,"skuId":15465},{"itemId":123,"num":21,"skuId":-1},{"itemId":123,"num":21,"skuId":222}]
    	$comId  	= I('post.comId',0,'intval');
    	$empName 	= I('post.empName');
    	$phoneNum 	= I('post.phoneNum', '', 'trim');
    	$empId 		= I('post.welfareEmpId', -1);
    	$totalPrice = I('post.totalPrice'); //进度，小数点两位
    	$sign 		= I('post.sign');
    	
    	//礼包id
    	if(!is_numeric($bagId) || $bagId < 1){
    		$msg = '参数错误（bagId错误）';
    		self::log("msg:{$msg} bagId={$bagId}");
    		$this->retError('1001', $msg);
    	}
    	//item数组
  		$itemArr = json_decode($itemStr, true);
  		if(empty($itemArr)){
  			$msg = '参数错误（items数组为空）';
  			self::log("msg:{$msg} items={$itemStr}");
  			$this->retError('1002', $msg);
  		}
  		if(empty($itemArr[0]['itemId']) || empty($itemArr[0]['num'])){
  			$msg = '参数错误（items数组格式错误）';
  			self::log("msg:{$msg} items={$itemStr}");
  			$this->retError('1003', $msg);
  		}
  		//com_id检测
  		if(!is_numeric($comId) || $comId < 1){
  			$msg = '参数错误（comId错误）';
  			self::log("msg:{$msg} comId={$comId}");
  			$this->retError('1020', $msg);
  		}
  		//手机号检测
  		if(empty($phoneNum)){
  			$msg = '参数错误（phoneNum为空）';
  			self::log("msg:{$msg} phoneNum={$phoneNum}");
  			$this->retError('1004', $msg);
  		}
  		//员工id检测
  		if(!is_numeric($empId) || $empId < 1){
  			$msg = '参数错误（welfareEmpId错误）';
  			self::log("msg:{$msg} empId={$empId}");
  			$this->retError('1005', $msg);
  		}
  		//员工id检测
  		if(empty($empName)){
  			$msg = '参数错误（empName为空）';
  			self::log("msg:{$msg} empName={$empName}");
  			$this->retError('1019', $msg);
  		}
  		//总价检测
  		if(!is_numeric($totalPrice) || $totalPrice < 1){
  			$msg = '参数错误（totalPrice错误）';
  			self::log("msg:{$msg} totalPrice={$totalPrice}");
  			$this->retError('1006', $msg);
  		}
  		//sign校验签名
  		$result = $this->signature($_POST);
  		if(!$result){
  			$msg = '签名错误（sign值错误）';
  			self::log("msg:{$msg} sign={$sign}");
  			$this->retError('1000', $msg);
  		}
  		//遍历items数组
  		$itemIdArr = array();
  		$itemOrderNumMap = array(); //订单购买数量
  		foreach ($itemArr as $item){
  			$itemId = $item['itemId'];
  			$skuId = empty($item['skuId']) ? -1 : $item['skuId'];
  			$itemIdArr[] = $itemId;
  			$itemOrderNumMap[$itemId] = array('num'=>$item['num'], 'skuId'=>$skuId);
  		}
  		if(empty($itemIdArr)){
  			$msg = 'itemId数组为空';
  			self::log("msg:{$msg} items={$itemStr}");
  			$this->retError('1007', $msg);
  		}
  		//查询item信息
  		$where = array();
  		$where['item_id'] = array('in',$itemIdArr);
  		$field = 'item_id, shop_id, cat_id, supplier_id, jd_sku, send_type, image_default_id';
  		$itemList = $this->sysitemItem->field($field)->where($where)->select();
  		if(empty($itemList)){
  			$msg = '未查询到任何item数据';
  			self::log("msg:{$msg} items={$itemStr}");
  			$this->retError('1008', $msg);
  		}
  		
  		//核对itemList数据集，判断是否有未查寻到item信息
  		$tmpItemIdArr = array();
  		$itemArr = array();
  		foreach ($itemList as $item){
  			$tmpItemIdArr[] = $item['item_id'];
  			$itemArr[$item['item_id']] = $item;
  		}
  		unset($itemList);
  		//核对库存数据
  		$diffItemArr = array_diff($itemIdArr, $tmpItemIdArr);
  		if(!empty($diffItemArr)){
  			$itemId = current($diffItemArr);
  			$msg = "未检索到itemId={$itemId}的数据";
  			self::log("msg:{$msg} items={$itemStr} itemId={$itemId}");
  			$this->retError('1009', $msg);
  		}
  		unset($tmpItemIdArr);
  		unset($diffItemArr);
  		
  		//处理用户手机号数据，返回userId
  		$userId = $this->sysAccount->getUserId($phoneNum, $comId, $empName);
  		if(!is_numeric($userId)){
  			$msg = "未获取到userId";
  			self::log("msg:{$msg} mobile={$phoneNum} comId={$comId} empName={$empName}");
  			$this->retError('1010', $msg);
  		}
  		//获取订单sku和数量
  		$skuOrderMap = $this->skuOrder($itemOrderNumMap);//此方法有判断库存
  		//查询需要的sku信息
  		$skuIdArr = array_keys($skuOrderMap);
  		$where = array();
  		$where['sku_id'] = array('in', $skuIdArr);
  		//$where['status'] = 'normal'; //是否是要限制status状态？？
  		$field = 'sku_id, item_id, price, bn, title, spec_info, cost_price, weight, status';
  		$skuList = $this->sysitemSku->field($field)->where($where)->select();
  		//开始下单
  		//生成交易id
  		$tid = substr(date('YmdHis'),2).$empId.rand(0,9);//订单编号,会串号
  		$dataList = array();
  		foreach ($skuList as $sku){
  			$skuId = $sku['sku_id'];
  			$itemId = $sku['item_id'];
  			$item = $itemArr[$itemId];
  			$num = $skuOrderMap[$skuId];
  			$totalFee = $sku['price'] * $num;//总额
  			$totalWeight = $sku['weight'] * $num;
  		
  			$data = array();
  			$data['tid'] = $tid;
  			$data['shop_id'] = $item['shop_id'];
  			$data['user_id'] = $userId;
  			$data['com_id'] = $comId;
  			//$data['dlytmpl_id'] = $postData['dlytmplIds'][$key];//配送模板id
  			$data['supplier_id'] = $item['supplier_id'];
  			$data['item_id'] = $sku['item_id'];
  			$data['sku_id'] = $sku['sku_id'];
  			$data['cat_id'] = $item['cat_id'];
  			$data['bn'] = $sku['bn'];
  			$data['title'] = $sku['title'];
  			$data['spec_nature_info'] = empty($sku['spec_info']) ? '' : $sku['spec_info'];
  			$data['price'] = $sku['price'];
  			$data['cost_price'] = $sku['cost_price'];
  			$data['num'] = $num;
  			$data['total_fee'] = $totalFee;
  			$data['post_fee'] = 0;//无邮费
  			$data['payment'] = $totalPrice;
  			$data['total_weight'] = $totalWeight;
  			$data['send_type'] = $item['send_type'];
  			$data['pic_path'] = $item['image_default_id'];
  			$data['transno'] = 0;
  			$data['status'] = 'WAIT_SELLER_SEND_GOODS';
  			//$data['trade_status'] = 'WAIT_SELLER_SEND_GOODS';
  			$data['payed_fee'] = $totalPrice; //礼包总金额
  			$data['pay_time'] = time();
  			$data['create_time'] = time();
  			$data['modified_time'] = time();
  			$data['from'] = 'api bag';
  			$dataList[] = $data;
  		}
  		
  		$this->giftTrade->startTrans(); //暂时关闭事物吧,可能影响预占库存数据
  		$tradeBagId = 0;
  		try {
  			$result = $this->giftTrade->addAll($dataList);
  			if(!$result){
  				$this->giftTrade->rollback();
  				$msg = "添加交易记录失败(trade)";
  				self::log("msg:{$msg} tid={$tid} result={$result}");
  				$this->retError('1015', $msg);
  			}
  			//添加礼包数据
  			$data = array();
  			$data['bag_id'] = $bagId;
  			$data['com_id'] = $comId;
  			$data['emp_id'] = $empId;
  			$data['tid'] = $tid;
  			$data['user_id'] = $userId;
  			$data['phone_num'] = $phoneNum;
  			$data['total_price'] = $totalPrice;
  			//$data['post_status'] = 0; //默认0 2.已领取
  			$tradeBagId = $this->giftTradeBag->add($data);
  			if(!$tradeBagId){
  				$this->giftTrade->rollback();
  				$msg = "添加交易记录失败(bag)";
  				self::log("msg:{$msg} tid={$tid} result={$result}");
  				$this->retError('1016', $msg);
  			}
  		}catch (\Exception $e){
  			$this->giftTrade->rollback();
  			$msg = "系统错误，添加交易失败 ";
  			self::log("msg:".$e->getMessage());
  			$this->retError('1017', $msg);
  		}
  		
  		$this->giftTrade->commit();//就在这里提交吧
  		//调用接口更新订单支付数据
  		$data = array();
  		$data['id'] = $tradeBagId;
  		$data['tid'] = $tid;
  		$flag = json_encode($data);
  		$flag = authCode($flag, 'ENCODE', C('KEY'));
  		$flag = str_replace('+','%2B',$flag); //替‘+’
  		$receiveUrl = 'http://'.$_SERVER['HTTP_HOST'].U('bag/receive').'?flag='.$flag;
  		$data = array(
  			'tid' => $tid,
  			'url' => $receiveUrl,
  		);
  		$msg = '成功交易';
  		self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId}",'success');
  		$this->retSuccess($data, 'success');
    }
    
    /**
     * 接收收货人信息
     */
    public function receiver(){
    	$tid = I('post.tid', '', 'trim');
    	$receiverName  = I('post.receiverName', '', 'trim');
    	$receiverPhone = I('post.receiverPhone', '', 'trim');
    	$receiverState = I('post.receiverState', '', 'trim'); //jd_id
    	$receiverCity  = I('post.receiverCity', '', 'trim'); //jd_id
    	$receiverDistrict = I('post.receiverDistrict', '', 'trim'); //jd_id
    	$receiverAddr  = I('post.receiverAddr', '', 'trim');
    	//tid
    	if(empty($tid)){
    		$msg = '参数错误（tid为空）';
    		self::log("msg:{$msg} tid={$tid}");
    		$this->retError('1031', $msg);
    	}
    	//receiverName 收货人
    	if(empty($receiverName)){
    		$msg = '参数错误（receiverName为空）';
    		self::log("msg:{$msg} receiverName={$receiverName}");
    		$this->retError('1032', $msg);
    	}
    	//receiverPhone 收货人手机号
    	if(empty($receiverPhone)){
    		$msg = '参数错误（receiverPhone为空）';
    		self::log("msg:{$msg} receiverPhone={$receiverPhone}");
    		$this->retError('1033', $msg);
    	}
    	//receiverState 身省份
    	if(!is_numeric($receiverState)){
    		$msg = '参数错误（receiverState为空）';
    		self::log("msg:{$msg} receiverState={$receiverState}");
    		$this->retError('1034', $msg);
    	}
    	//收货城市
    	if(!is_numeric($receiverCity)){
    		$msg = '参数错误（receiverCity为空）';
    		self::log("msg:{$msg} receiverCity={$receiverCity}");
    		$this->retError('1035', $msg);
    	}
    	//收货地区
    	if(!is_numeric($receiverDistrict)){
    		$msg = '参数错误（receiverDistrict为空）';
    		self::log("msg:{$msg} receiverDistrict={$receiverDistrict}");
    		$this->retError('1036', $msg);
    	}
    	//详细地址
    	if(empty($receiverAddr)){
    		$msg = '参数错误（receiverAddr为空）';
    		self::log("msg:{$msg} receiverDistrict={$receiverAddr}");
    		$this->retError('1036', $msg);
    	}
    	//sign校验签名
    	$result = $this->signature($_POST);
    	if(!$result){
    		$msg = '签名错误（sign值错误）';
    		self::log("msg:{$msg} sign={$sign}");
    		$this->retError('1000', $msg);
    	}
    	$buyerArea = "{$receiverState}/{$receiverCity}/{$receiverDistrict}";
    	//查询京东地址表
    	$receiverStateName = ''; 
    	$receiverCityName = ''; 
    	$receiverDistrictName = ''; 
    	
    	$where = array();
    	$where['jd_id'] = array('in',array($receiverState, $receiverCity, $receiverDistrict));
    	$areaList = $this->siteArea->where($where)->select();
    	foreach ($areaList as $area){
    		if($area['jd_id'] == $receiverState){
    			$receiverStateName = $area['name'];
    		}else if($area['jd_id'] == $receiverCity){
    			$receiverCityName = $area['name'];
    		}else if($area['jd_id'] == $receiverDistrict){
    			$receiverDistrictName = $area['name'];
    		}
    	}
    	if(empty($receiverStateName)){
    		$msg = "未查寻省级地址信息(jd_id={$receiverState})";
    		self::log("msg:{$msg} jd_id={$receiverState}");
    		$this->retError('1042', $msg);
    	}
    	
    	if(empty($receiverCityName)){
    		$msg = "未查寻市级地址信息(jd_id={$receiverCity})";
    		self::log("msg:{$msg} jd_id={$receiverCity}");
    		$this->retError('1042', $msg);
    	}
    	
    	if(empty($receiverDistrictName)){
    		$msg = "未查寻区（县）级地址信息(jd_id={$receiverDistrict})";
    		self::log("msg:{$msg} jd_id={$receiverDistrict}");
    		$this->retError('1042', $msg);
    	}
    	
    	//1.获取tid,查询gift_trade表数据
    	//2.添加到trade表（注意捕捉异常）
    	$where = array();
    	$where['tid'] = $tid;
    	//查询是否已经推送过
    	$tradeBag = $this->giftTradeBag->field('id,total_price,push_addr_status')->where($where)->find();
    	$tradeBagId = $tradeBag['id'];
    	$totalPrice = $tradeBag['total_price'];
    	$pushAddrStatus = $tradeBag['push_addr_status'];
    	if(!is_numeric($tradeBagId)){
    		$msg = '未查询交易（trade bag）';
    		self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId} pushAddrStatus={$pushAddrStatus}");
    		$this->retError('1037', $msg);
    	}
    	
    	if($pushAddrStatus == 1){
    		$msg = '重复推送，该交易已经推送成功';
    		self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId} pushAddrStatus={$pushAddrStatus}");
    		$this->retError('1038', $msg);
    	}
    	
    	//查看虚拟店铺是否存在
    	$shopId = C('VSHOP');
    	if(!is_numeric($shopId)){
    		$msg = '系统错误，未配置虚拟店铺';
    		self::log("msg:{$msg} shopId={$shopId}");
    		$this->retError('1040', $msg);
    	}
    	
    	//查询交易数据记录
    	$tradeList = $this->giftTrade->where($where)->select();
    	if(empty($tradeList)){
    		$msg = '未查询交易（gift trade）';
    		self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId} pushAddrStatus={$pushAddrStatus}");
    		$this->retError('1039', $msg);
    	}
    	
    	$tradeTotalWeight = 0;
    	$dataList = array();
    	$itemNumMap = array();
    	$skurNumMap = array();
    	foreach ($tradeList as $key => $trade){
    		$itemId = $trade['item_id'];
    		$num = $trade['num'];
    		//添加trade_order
    		$order = array();
    		$order['oid'] = substr(date('YmdHis'),2).$key.$trade['user_id']; //可能串号
    		$order['tid'] = $tid;
    		$order['shop_id'] 	= $trade['shop_id'];
    		$order['user_id'] 	= $trade['user_id'];
    		$order['item_id'] 	= $itemId;
    		$order['sku_id'] 	= $trade['sku_id'];
    		$order['cat_id'] 	= $trade['cat_id'];;//类目id
    		$order['bn'] 		= $trade['bn'];
    		$order['title'] 	= $trade['title'];
    		$order['spec_nature_info'] = $trade['spec_nature_info'];
    		$order['price'] 		= $trade['price'];
    		$order['cost_price'] 	= $trade['cost_price'];
    		$order['supplier_id'] 	= $trade['supplier_id'];
    		$order['send_type'] 	= $trade['send_type'];
    		$order['num'] 			= $num;
    		$order['total_fee'] 	= $trade['total_fee'];
    		$order['total_weight'] 	= $trade['total_weight'];
    		$order['order_from'] 	= 'gift bag';
    		$order['pic_path'] 	=  $trade['pic_path'];
    		$order['status'] 	= $trade['status'];
    		$dataList[] = $order;
    		 
    		$tradeTotalWeight += $trade['total_weight'];
    		//此数据给后面减库存
    		if(!isset($itemNumMap[$itemId])){
    			$itemNumMap[$itemId] = 0;
    		}
    		$itemNumMap[$itemId] += $num;
    		$skurNumMap[$trade['sku_id']] = $num;
    	}
    	
    	$trade = $tradeList[0]; //这里取第一个，只取写商品中公用的信息
    	$data = array();
    	$data['tid'] 		= $tid;
    	$data['shop_id'] 	= $shopId;
    	$data['user_id'] 	= $trade['user_id'];
    	$data['com_id'] 	= $trade['com_id'];
    	$data['dlytmpl_id'] = 0;
    	//$data['status'] 	= $trade['status'];
    	$data['payment'] 	= $trade['payment'];
    	$data['total_fee'] 	= $trade['total_fee'];
    	$data['post_fee'] 	= 0;
    	$data['receiver_name'] 	= $receiverName;
    	$data['created_time'] 	= time();
    	$data['receiver_state'] = $receiverStateName;
    	$data['receiver_city'] 	= $receiverCityName;
    	$data['receiver_district'] = $receiverDistrictName;
    	$data['receiver_address'] = $receiverAddr;
    	$data['receiver_zip'] 	  = '';
    	$data['receiver_mobile']  = $receiverPhone;
    	$data['title'] 			  = '订单明细介绍';
    	$data['buyer_message'] 	  = '';
    	$data['receiver_phone']   = '';
    	$data['itemnum'] 		  = $trade['num'];
    	$data['buyer_area'] 	  = $buyerArea;
    	$data['total_weight']     = $tradeTotalWeight;
    	$data['transno'] 		  = $trade['transno'];
    	$data['status'] 		  = $trade['status'];
    	//$data['trade_status']	  = $trade['trade_status'];
    	$data['payed_fee'] 		  = $totalPrice;
    	$data['pay_time'] 		  = $trade['pay_time'];
    	$data['trade_from'] 	  = 'bag';
    	$data['trade_source']	  = 'gift bag';
    	
    	$this->giftTradeBag->startTrans();
    	try {
    		//添加trade_order
    		$result = $this->systradeOrder->addAll($dataList);
    		unset($dataList);
    		if(!$result){
    			$this->giftTradeBag->rollback();
    			$msg = '添加交易失败(trade order)';
    			self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId}");
    			$this->retError('1041', $msg);
    		}
    		//trade
    		$result = $this->sysTrade->add($data);
    		unset($data);
    		if(!$result){
    			$this->giftTradeBag->rollback();
    			$msg = '系统错误，添加交易失败（trade）';
    			self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId}");
    			$this->retError('1040', $msg);
    		}
    		//更新推送地址状态
    		$where = array();
    		$where['id'] = $tradeBagId;
    		$data = array();
    		$data['push_addr_status'] = 1;
    		$data['push_addr_time'] = date('Y-m-d H:i:s');
    		$result = $this->giftTradeBag->where($where)->save($data);
    		if(!is_numeric($result)){
    			$this->giftTradeBag->rollback();
    			$msg = '更新推送状态失败';
    			self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId}");
    			$this->retError('1041', $msg);
    		}
    		//成功
    		$this->giftTradeBag->commit();
    		//保存地址
    		$data = array();
    		$data['tid'] = $tid;
    		$data['receiver_name'] = $receiverName;
    		$data['receiver_phone'] = $receiverPhone;
    		$data['receiver_state'] = $receiverStateName;
    		$data['receiver_city'] = $receiverCityName;
    		$data['receiver_district'] = $receiverDistrictName;
    		$data['receiver_addr'] = $receiverAddr;
    		$data['buyer_area'] = $buyerArea;
    		$data['type'] = 1;
    		$this->giftReceiverAddr->add($data);
    		//更新库存，销量
    		foreach ($itemNumMap as $itemId => $num){
    			//购买减item_store库存
    			$this->itemStore->where("item_id=$itemId")->setDec('store', $num);
    			//购买增加item销量
    			$this->itemCount->where("item_id=$itemId")->setInc('sold_quantity', $num);
    		}
    		foreach ($skurNumMap as $skuId => $num){
    			//减sku_store库存
    			$this->skuStoreModel->where("sku_id=$skuId")->setDec('store', $num);
    			//购买增加sku销量
    			$this->sysitemSku->where("sku_id =$skuId")->setInc('sold_quantity', $num);
    		}
    		
    		$msg = '推送成功';
    		self::log("msg:{$msg} tid={$tid} tradeBagId={$tradeBagId}",'success');
    		$this->retSuccess(array(), 'success');
    	}catch (\Exception $e){
    		$this->giftTradeBag->rollback();
    		$msg = "系统错误，添加交易失败 (exception)";
    		self::log("msg:".$e->getMessage());
    		$this->retError('1045', $msg);
    	}
    }
    
// 	/**
// 	 * 根据手机号获取userId
// 	 * @param unknown $mobile
// 	 */
// 	private function getUserId($mobile, $comId, $empName){
// 		if(empty($mobile) || empty($comId) || empty($empName)){
// 			return false;
// 		}
// 		$where = array();
// 		$where['mobile'] = $mobile;
// 		$userId = $this->sysuserAccount->where($where)->getField('user_id');
// 		if(!empty($userId)){
// 			return $userId;
// 		}
// 		try {
// 			//不存在，开始注册用户
// 			return $this->registerAccount($mobile, $comId, $empName);
// 		}catch (\Exception $e){
// 			$msg = "系统错误，获取用户错误 ";
// 			self::log("msg:".$e->getMessage());
// 			$this->retError('1018', $msg);
// 		}
// 	}
	
// 	/**
// 	 * 注册本地用户
// 	 * @param unknown $mobile
// 	 */
// 	private function registerAccount($mobile, $comId, $empName){
// 		$data = array(
// 			'login_account'	=>$mobile,
// 			'mobile'		=>$mobile,
// 			'login_password'=>'activate'
// 		);
// 		$userId = $this->sysuserAccount->add($data);
// 		if(!$userId){
// 			return false;
// 		}
// 		//调用请求接口
// 		$data = array(
// 			'user_id'	=>$userId,
//         	'name'		=>$empName,
//         	'username'	=>$empName
// 		);
// 		$this->sysuserUser->add($data);
// 		$data = array(
// 			'user_id'	=>$userId,
// 			'comId'		=>$comId,
// 		);
// 		$this->sysuserUserDeposit->add($data);
// 		return $userId;
// 	}
	
    /**
     * 获取sku数据集合
     * @param unknown $itemOrderNumMap
     * @return unknown[]
     */
    private function skuOrder($itemOrderNumMap){
    	//优先查询sku库存
    	$itemSkuStoreMap = array(); //保存实际的sku库存
    	$itemStoreMap = array(); //保存实际item库存，sku数量之和
    	$itemIdArr = array_keys($itemOrderNumMap);
    	$skuOrderMap = array(); //此变量保存实际的订单sku，交易以此变量sku集合为准
    	$ret = array(); //将要返回的数据
    	$where = array();
    	$where['item_id'] = array('in', $itemIdArr);
    	$field = 'item_id,sku_id,store';
    	$skuStoreList = $this->skuStoreModel->field($field)->where($where)->select();
    	$skuStoreItemIdArr = array(); //用于核对$skuStoreList中的item结果集是否与$itemIdArr一直
    	//统计item总库存和各个sku的库存
    	foreach ($skuStoreList as $key => $tmpStore){
    		$itemId = $tmpStore['item_id'];
    		$store = $tmpStore['store'];
    		$skuStoreItemIdArr[] = $itemId;
    		if($store < 0){
    			unset($skuStoreList[$key]); //去掉没有库存的sku
    			continue;
    		}
    		$itemSkuStoreMap[$itemId][$tmpStore['sku_id']] = $store;
    		if(!isset($itemStoreMap[$itemId])){
    			$itemStoreMap[$itemId] = 0; //初始化实际总库存
    		}
    		$itemStoreMap[$itemId] += $store; //增加item总库存
    	}
    	unset($skuStoreList);
    	//判断库存数据
    	if(empty($itemStoreMap) || empty($itemSkuStoreMap)){
    		$msg = "检索库存数据异常";
    		self::log("msg:{$msg} items={$itemStr}");
    		$this->retError('1011', $msg);
    	}
    	//核对库存数据
    	$diffItemArr = array_diff($itemIdArr, $skuStoreItemIdArr);
    	if(!empty($diffItemArr)){
    		$itemId = current($diffItemArr);
    		$msg = "未检索到itemId={$itemId}库存信息";
    		self::log("msg:{$msg} items={$itemStr} itemId={$itemId}");
    		$this->retError('1012', $msg);
    	}
    	unset($skuStoreItemIdArr);
    	//判断每个item的总库存是否满足需求，并且开始选择满足需求的sku
    	foreach ($itemOrderNumMap as $itemId => $val){//num,
    		$orderNum = $val['num']; // 当前需要的数量，需要理解此变量的意义
    		$orderSkuId = $val['skuId'];
    		if(is_numeric($orderSkuId) && $orderSkuId > 1){//传了sku
    			$skuStore = $itemSkuStoreMap[$itemId][$orderSkuId];
    			if($skuStore < $orderNum){
    				//库存不满足
    				$msg = "sukId={$orderSkuId}的库存不够（库存{$skuStore}，下单数量为{$orderNum}）";
    				self::log("msg:{$msg} items={$itemStr} itemId={$itemId} skuId={$orderSkuId} num={$orderNum} skuStore={$skuStore}");
    				$this->retError('1013', $msg);
    			}else{
    				$skuOrderMap[$orderSkuId] = $orderNum;
    			}
    		}else{
    			$tmpItemStore = $itemStoreMap[$itemId];
    			if($tmpItemStore < $orderNum){
    				//总库存不满足
    				$msg = "itemId={$itemId}的库存不够（总库{$tmpItemStore}，下单数量为{$orderNum}）";
    				self::log("msg:{$msg} items={$itemStr} itemId={$itemId} num={$orderNum} itemStore={$tmpItemStore}");
    				$this->retError('1014', $msg);
    			}else{
    				//总库存满足
    				//执行到这里说明总当前的item总库存是满足需求的，开始选择sku，主要看库存，只选择有库存的sku
    				//当某个item的sku库存满足时（即大于num），就选择此sku，
    				//当某个sku库存不足时（即小于订单num），就需要选择多个sku，来满足订单需求，当前sku库存减为0，跳到下一个sku继续减，直到满足订单需求
    				//对比实际sku库存,直到满足订单需求，才会跳出该循环
    				foreach ($itemSkuStoreMap[$itemId] as $skuId => $store){
    					if($orderNum <= $store){
    						$skuOrderMap[$skuId] = $orderNum;//库存满足
    						break;//需求已经满足，跳出该循环
    					}else{ //当前库存不满足
    						$skuOrderMap[$skuId] = $store;
    						$orderNum -= $store;//当前需求减少，计算剩下的需求量
    					}
    				}
    			}
    		}
    	}
    	
    	return $skuOrderMap;
    }
    
    /**
     * 集配单接口
     * @author Gaolong
     */
    public function groupOrder(){
    	$comId = I('post.comId');
    	$activityConfigId = I('post.bagId'); //礼包id
    	$price = I('post.price');
    	$deliveryTime = I('post.deliveryTime');
    	$receiverName = I('post.receiverName', '', 'trim,strip_tags');
    	$receiverPhone = I('post.phone');
    	$receiverState = I('post.receiverState', -1, 'intval');
    	$receiverCity = I('post.receiverCity', -1, 'intval');
    	$receiverDistrict = I('post.receiverDistrict', -1, 'intval');
    	$receiverAddress = I('post.detailedAddress','','strip_tags');
    	$expireTime = I('post.expireTime'); //过期时间
    	$userList = I('post.userList','','strip_tags');
    	$sign = I('post.sign');
    	//开始数据校验
    	//$comId
    	if(empty($comId)){
    		#TODO
    		$msg = "参数异常comId为空";
    		self::log("msg:{$msg} comId={$comId}");
    		$this->retError('1100', $msg);
    	}
    	//礼包id $bagId
    	if(!is_numeric($activityConfigId)){
    		#TODO
    		$msg = "参数异常 activityConfigId为空";
    		self::log("msg:{$msg} activityConfigId={$activityConfigId}");
    		$this->retError('1101', $msg);
    	}
    	//价格 price
    	if(!is_numeric($price)){
    		#TODO
    		$msg = "参数异常 price错误";
    		self::log("msg:{$msg} price={$price}");
    		$this->retError('1102', $msg);
    	}
    	//送货时间 $deliveryTime
    	if(empty($deliveryTime)){
    		#TODO
    		$msg = "参数异常 deliveryTime错误";
    		self::log("msg:{$msg} price={$deliveryTime}");
    		$this->retError('1103', $msg);
    	}
    	//收货人
    	if(empty($receiverName)){
    		$msg = "参数异常 receiverName错误";
    		self::log("msg:{$msg} receiverName={$receiverName}");
    		$this->retError('1104', $msg);
    	}
    	//地址-省
    	if(!is_numeric($receiverState)){
    		#TODO
    		$msg = "参数异常 receiverState错误";
    		self::log("msg:{$msg} receiverState={$receiverState}");
    		$this->retError('1105', $msg);
    	}
    	//地址-市
    	if(!is_numeric($receiverCity)){
    		#TODO
    		$msg = "参数异常 receiverCity错误";
    		self::log("msg:{$msg} receiverState={$receiverCity}");
    		$this->retError('1106', $msg);
    	}
    	//地址-区、县
    	if(!is_numeric($receiverDistrict)){
    		$msg = "参数异常 receiverDistrict错误";
    		self::log("msg:{$msg} receiverDistrict={$receiverDistrict}");
    		$this->retError('1107', $msg);
    		
    	}
    	//过期时间
//     	if(empty($expireTime)){
//     		#TODO
//     		$msg = "参数异常 expireTime错误";
//     		self::log("msg:{$msg} expireTime={$expireTime}");
//     		$this->retError('1107', $msg);
//     	}
    	//手机号
    	if(empty($receiverPhone)){
    		#TODO
    		$msg = "参数异常 phone错误";
    		self::log("msg:{$msg} phone={$receiverPhone}");
    		$this->retError('1109', $msg);
    	}
    	//手机号列表
    	if(empty($userList)){
    		#TODO
    		$msg = "参数异常 userList错误";
    		self::log("msg:{$msg} userList={$userList}");
    		$this->retError('1110', $msg);
    	}
    	$userArr = json_decode($userList, true);
    	if(empty($userArr) || !is_array($userArr)){
    		#TODO
    		$msg = "参数异常 userList错误";
    		self::log("msg:{$msg} userList={$userList}");
    		$this->retError('1112', $msg);
    	}
    	foreach ($userArr as $user){
    		$phoneNum = $user['phoneNum'];
    		if(!preg_match('/^1\d{10}$/', $phoneNum)){
    			#TODO
    			$msg = "参数异常 userList中手机号（{$phoneNum}）错误";
    			self::log("msg:{$msg} userList={$userList}");
    			$this->retError('1113', $msg);
    		}
    	}
    	if(empty($sign)){
    		$msg = '参数异常 签名sign为空';
    		self::log("msg:{$msg} sign={$sign}");
    		$this->retError('1124', $msg);
    	}
    	//校验签名
    	$result = $this->signature($_POST);
    	if(!$result){
    		$msg = '签名错误（sign值错误）';
    		self::log("msg:{$msg} sign={$sign}");
    		$this->retError('1114', $msg);
    	}
    	//查询礼包信息
    	$where = array();
    	$where['activity_config_id'] = $activityConfigId;
    	//$giftBag = $this->giftBag->where($where)->find();
    	$activityCategory = $this->activityCategory->where($where)->getField('activity_config_id');
    	if(empty($activityCategory)){
    		$msg = "礼包不存在";
    		self::log("msg:{$msg} activityConfigId=$activityConfigId");
    		$this->retError('1115', $msg);
    	}
    	//核对礼包价格
    	$where = array();
    	$where['activity_config_id'] = $activityConfigId;
    	$itemPrice = $this->activityItem->where($where)->getField('price');
    	if($itemPrice != $price){
    		$msg = "礼包价格不一致";
    		self::log("msg:{$msg} price=$price itemPrice={$itemPrice}");
    		$this->retError('1126', $msg);
    	}
    	
    	//查询地址
    	$addrArr = array($receiverState, $receiverCity, $receiverDistrict);
    	$where = array();
    	$where['jd_id'] = array('in', $addrArr);
    	$areaList = $this->siteArea->where($where)->field('name, jd_id')->select();
    	if(empty($areaList)){
    		#TODO
    		$msg = "未检索到任何地址信息";
    		self::log("msg:{$msg} receiverState=$receiverState receiverCity=$receiverCity receiverDistrict=$receiverDistrict");
    		$this->retError('1116', $msg);
    	}
    	$areaArr = array();
    	foreach ($areaList as $area){
    		$areaArr[$area['jd_id']] = $area['name'];
    	}
    	//获取地址名称
    	$receiverStateName = '';
    	$receiverCityName = '';
    	$receiverDistrictName = '';
    	foreach ($addrArr as $areaId){
    		$areaName = $areaArr[$areaId];
    		if(empty($areaName)){
    			$msg = "此地址不存在 jdId=$areaId";
    			self::log("msg:{$msg} jdId=$areaId");
    			$this->retError('1117', $msg);
    		}
    		if($areaId == $receiverState){
    			$receiverStateName = $areaName;
    		}else if($areaId == $receiverCity){
    			$receiverCityName = $areaName;
    		}else if($areaId == $receiverDistrict){
    			$receiverDistrictName = $areaName;
    		}
    	}
    	
    	//加载user_id
    	$userIdArr = $this->getUserIdArr($comId, $userArr);
    	if(!$userIdArr){
    		$msg = "未检索到任何用户信息";
    		self::log("msg:{$msg} userList={$userList}");
    		$this->retError('1118', $msg);
    	}
    	//核对校验，判断所有的手机号是否已经获取到对应的user_id
    	foreach ($userArr as $user){
    		$phoneNum = $user['phoneNum'];
    		if(empty($userIdArr[$phoneNum])){
    			#TODO 手机号未获取到对应的user_id
    			$msg = "未获取到该用户信息 phone={$phoneNum}";
    			self::log("msg:{$msg} phone={$phoneNum}");
    			$this->retError('1119', $msg);
    		}
    	}
    	
    	$userId = $this->sysAccount->getUserId($receiverPhone, $comId, $receiverPhone);
    	if (!is_numeric($userId)){
    		$msg = "未获取到联系人信息 userId={$userId}";
    		self::log("msg:{$msg} phone={$receiverPhone}");
    		$this->retError('1120', $msg);
    	}
    	
    	$atid = substr(date('YmdHis'),2).$userId.rand(0,9);//订单编号
    	$paymentId = date('ymdHis').$userId.rand(0,9);//支付单号
    	
    	$receiverCount = count($userArr);
    	$data = array();
    	$data['com_id'] = $comId;
    	$data['atid'] = $atid;
    	$data['payment_id'] = $paymentId;
    	$data['activity_config_id'] = $activityConfigId;
    	$data['price'] = $price;
    	$data['delivery_time'] = $deliveryTime;
    	$data['receiver_phone'] = $receiverPhone;
    	$data['receiver_count'] = $receiverCount;
    	$data['receiver_name'] = $receiverName;
    	$data['receiver_state'] = $receiverStateName.':'.$receiverState;
    	$data['receiver_city'] = $receiverCityName.':'.$receiverCity;
    	$data['receiver_district'] = $receiverDistrictName.':'.$receiverDistrict;
    	$data['receiver_address'] = $receiverAddress;
    	try {
    		$this->giftGroupOrder->startTrans();
    		$orderId = $this->giftGroupOrder->add($data);
    		if(!$orderId){
    			$this->giftGroupOrder->rollback();
    			$msg = "添加集配单失败";
    			self::log("msg:{$msg} orderId={$orderId} atid={$atid}");
    			$this->retError('1121', $msg);
    		}
    		$data = array();
    		$dataList = array();
    		foreach ($userArr as $user){
    			$phoneNum = $user['phoneNum'];
    			$data['order_id'] = $orderId;
    			$data['com_id'] = $comId;
    			$data['user_id'] = $userIdArr[$phoneNum];
    			$data['username'] = empty($user['empName']) ? $phoneNum : $user['empName'];
    			$data['phone'] = $phoneNum;
    			$data['activity_config_id'] = $activityConfigId;
    			$data['price'] = $price;
    			$data['delivery_time'] = $deliveryTime;
    			$data['receive_status'] = 0;
    			$dataList[] = $data;
    		}
    		$result = $this->giftGroupOrderRecever->addAll($dataList);
    		if(!$result){
    			$this->giftGroupOrder->rollback();
    			$msg = "添加集配子单失败";
    			self::log("msg:{$msg} orderId={$orderId} atid={$atid}");
    			$this->retError('1122', $msg);
    		}
    		
    		$money = $receiverCount * $price;//总金额
    		$payTime = time();
    		//添加支付单
    		$data = array();
    		$data['payment_id'] = $paymentId;
    		$data['money'] = $money;
    		$data['cur_money'] = $money;
    		$data['status'] = 'succ';
    		$data['user_id'] = $userId;
    		$data['user_name'] = $receiverPhone;
    		$data['pay_name'] = '集配单';
    		$data['pay_from'] = 'pc';
    		$data['pay_type'] = 'online';
    		$data['payed_time'] = $payTime;
    		$data['created_time'] = $payTime;
    		$result = $this->ectoolsPayments->add($data);
    		if(!$result){
    			$this->giftGroupOrder->rollback();
    			$msg = "添加支付主单失败";
    			self::log("msg:{$msg} orderId={$orderId} atid={$atid} paymentId={$paymentId}");
    			$this->retError('1123', $msg);
    		}
    		
    		$data = array();
    		$data['payment_id'] = $paymentId;
    		$data['tid'] = $atid;
    		$data['status'] = 'succ';
    		$data['payment'] = $money;
    		$data['user_id'] = $userId;
    		$data['created_time'] = $payTime;
    		$data['payed_time'] = $payTime;
    		$result = $this->ectoolsTradePaybill->add($data);
    		if(!$result){
    			$this->giftGroupOrder->rollback();
    			$msg = "添加支付子单失败";
    			self::log("msg:{$msg} orderId={$orderId} atid={$atid} paymentId={$paymentId}");
    			$this->retError('1124', $msg);
    		}
    		
    		$this->giftGroupOrder->commit();
    		$msg = "添加集配单成功";
    		self::log("msg:{$msg} orderId={$orderId} atid={$atid} paymentId={$paymentId}", 'success');
    		$data = array(
    			'shopSupplyNo'=>$atid
    		);
    		$this->retSuccess($data, 'success');
    	}catch (\Exception $e){
    		$this->giftGroupOrder->rollback();
    		$msg = "系统错误，添加集配单失败";
    		self::log("msg:{$msg}  orderId={$orderId} atid={$atid} exception:".$e->getMessage());
    		$this->retError('1120', $msg);
    	}
    }
    
    /**
     * 收取礼物
     */
    public function groupOrderRec(){
    	//1.接收参数，集配单id, 用户id, sign签名
    	$orderId = I('post.orderId', -1, 'intval');
    	$aItemId = I('post.aItemId', -1, 'intval');
    	$userId = I('post.userId', -1, 'intval');
    	$sign = I('post.sign','','trim,strip_tags');
    	
    	//参数校验
    	//orderId
    	if(!is_numeric($orderId) || $orderId < 1){
    		$msg = "参数异常 orderId错误";
    		self::log("msg:{$msg} orderId={$orderId}");
    		$this->retError('1132', $msg);
    	}
    	if(!is_numeric($aItemId) || $aItemId < 1){
    		$msg = "参数异常 aItemId错误";
    		self::log("msg:{$msg} aItemId={$aItemId}");
    		$this->retError('1133', $msg);
    	}
    	
		if(!is_numeric($userId) || $userId < 1){
			$msg = "参数异常 userId错误";
			self::log("msg:{$msg} userId={$userId}");
			$this->retError('1134', $msg);
		}
		if(empty($sign)){
			$msg = "参数异常 sign为空";
			self::log("msg:{$msg} sign={$sign}");
			$this->retError('1135', $msg);
		}
		$result = $this->signature($_POST);
    	if(!$result){
    		$msg = "签名（sign）错误";
    		self::log("msg:{$msg} sign={$sign}");
    		$this->retError('1136', $msg);
    	}
    	//2.查询gift_group_order_recever表
    	$where = array();
    	$where['order_id'] = $orderId;
    	$where['user_id'] = $userId;
    	$receiver = $this->giftGroupOrderRecever->where($where)->find();
    	if(empty($receiver)){
    		$msg = "未检索到集配单";
    		self::log("msg:{$msg} orderId={$orderId} userId={$userId}");
    		$this->retError('1137', $msg);
    	}
    	$receverId = $receiver['recever_id'];
    	$activityConfigId = $receiver['activity_config_id'];
    	$orderId = $receiver['order_id'];
    	$username = $receiver['username'];
    	$receiveUserId = $receiver['user_id'];
    	$phone = $receiver['phone'];
    	$price = $receiver['price'];
    	$receiverCount = $receiver['receiver_count'];
    	$comId = $receiver['com_id'];
    	$receiveStatus = intval($receiver['receive_status']);
    	
    	if($receiveStatus !== 0){
    		#TODO
    		$msg = "该礼包已被领取";
    		self::log("msg:{$msg} orderId={$orderId} userId={$userId}");
    		$this->retError('1138', $msg);
    	}
    	
    	$where = array();
    	$where['aitem_id'] = $aItemId;
    	$aItem = $this->activityItem->where($where)->find();
    	
    	if(empty($aItem)){
    		#TODO
    		$msg = "礼包信息为空";
    		self::log("msg:{$msg} orderId={$orderId} aItemId={$aItemId}");
    		$this->retError('1140', $msg);
    	}
    	if($aItem['activity_config_id'] !=  $activityConfigId){
    		#TODO
    		$msg = "礼包Id不对应";
    		self::log("msg:{$msg} orderId={$orderId} aItemId={$aItemId}");
    		$this->retError('1141', $msg);
    	}
    	if($aItem['price'] !=  $price){
    		#TODO
    		$msg = "礼包价格不对应";
    		self::log("msg:{$msg} orderId={$orderId} aItemId={$aItemId} orderPrice={$price} aitemPrice={$aItem['price']}");
    		$this->retError('1142', $msg);
    	}
    	//取出商品信息
    	$itemList = json_decode($aItem['item_info'], true);
    	if(empty($itemList)){
    		$msg = "解析商品信息错误 aItemId={$aItemId}";
    		self::log("msg:{$msg} userId={$userId} orderId={$orderId}");
    		$this->retError('1151', $msg);
    	}
    	$skuIdArr = array();
    	$itemIdArr = array();
    	foreach ($itemList as $item){
    		$skuIdArr[] = $item['sku_id'];
    		$itemIdArr[$item['item_id']] = $item['item_id'];
    	}
    	//商品上下架判断
    	$where = array();
    	$where['item_id'] = array('in', $itemIdArr);
    	$itemStatusList = $this->itemStatus->where($where)->field('item_id,approve_status')->select();
    	$itemStatusArr = array();
    	foreach ($itemStatusList as $item){
    		$itemStatusArr[$item['item_id']] = $item['approve_status'];
    	}
    	unset($itemStatusList);
    	foreach ($itemIdArr as $itemId){
    		$itemStatus = $itemStatusArr[$itemId];
    		if(empty($itemStatus)){
    			$msg = "未找到商品状态 itemId={$itemId}";
    			self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId}");
    			$this->retError('1154', $msg);
    		}else{
    			if($itemStatus != 'onsale'){
    				$msg = "商品不是正常销售状态 itemId={$itemId}";
    				self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} status={$itemStatus}");
    				$this->retError('1155', $msg);
    			}
    		}
    	}
    	//检索库存，判断库存
    	$where = array();
    	$where['sku_id'] = array('in', $skuIdArr);
    	$skuStoreList = $this->skuStoreModel->where($where)->select();
    	$skuStoreArr = array();
    	foreach ($skuStoreList as $skuStore){
    		$skuStoreArr[$skuStore['sku_id']] = $skuStore;
    	}
    	unset($skuStoreList);
    	foreach ($itemList as $item){
    		$skuId = $item['sku_id'];
    		$store = $skuStoreArr[$skuId];
    		if(empty($store)){
    			$msg = "未找到商品库存 skuId={$skuId}";
    			self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId}");
    			$this->retError('1152', $msg);
    		}else{
    			$leftStock = $store['store'] - $store['freez'];
    			if($leftStock < $item['num']){
    				$msg = "商品库存不足 skuId={$skuId}";
    				self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} leftStock={$leftStock} orderNum={$item['num']}");
    				$this->retError('1153',$msg);
    			}
    		}
    	}
    	//查询收货地址
    	$where = array();
    	$where['order_id'] = $orderId;
    	$field = 'atid,delivery_time,receiver_name,receiver_phone,receiver_state,receiver_city,receiver_district,receiver_address';
    	$groupOrder = $this->giftGroupOrder->where($where)->field($field)->find();
    	//获取收货信息
    	list($receiverStateName, $receiverState) = explode(':',$groupOrder['receiver_state']);
    	list($receiverCityName, $receiverCity) = explode(':',$groupOrder['receiver_city']);
    	list($receiverDistrictName, $receiverDistrict) = explode(':',$groupOrder['receiver_district']);
    	$receiverAddr = $groupOrder['receiver_address'];
    	$receiverName = $groupOrder['receiver_name'];
    	$receiverPhone = $groupOrder['receiver_phone'];
    	$atid = $groupOrder['atid'];//订单编号
    	$deliveryTime = $groupOrder['delivery_time'];
    	if(empty($atid)){
    		$msg = "未找到交易号";
    		self::log("msg:{$msg} orderId={$orderId} aItemId={$aItemId} atid={$atid}");
    		$this->retError('1147', $msg);
    	}
    	$todayDate = date('Y-m-d');
    	$expireTime = date('Y-m-d', strtotime($deliveryTime.' -1 day'));
    	if($expireTime < $todayDate){
    		$msg = "礼包已过期";
    		self::log("msg:{$msg} orderId={$orderId} aItemId={$aItemId} atid={$atid} todayDate={$todayDate} expireDate={$expireTime}");
    		$this->retError('1148', $msg);
    	}
    	//获取用户名称
//     	if(empty($username)){
//     		$where = array();
//     		$where['user_id'] = $userId;
//     		$username = M('sysuser_user')->where($where)->getField('username');
//     	}
    	$username = empty($username) ? $phone : $username;//用户账号
//     	if(empty($groupOrder['atid'])){
//     		$atid = substr(date('YmdHis'),2).$userId.rand(0,9);//订单编号
//     	}else{
//     		$atid = $groupOrder['atid'];//订单编号
//     	}
    	try {
    		//生成主订单表
    		$where = array();
    		$where['atid'] = $atid;
    		$result = $this->activityTrade->where($where)->getField('atid');
    		
    		$this->activityTrade->startTrans();//开启事务
    		
    		if(empty($result)){
    			//$totalFee = $aItem['price'] + $aItem['post_fee'];
    			$totalFee = $price * $receiverCount;
    			$buyerArea = "{$receiverState}/{$receiverCity}/{$receiverDistrict}";
    			
    			$data = array();
    			$data['atid'] = $atid;//订单编号
    			$data['aid'] = $aItem['aid'];//活动id
    			$data['activity_name'] = $aItem['activity_name'];//活动名称
    			$data['title'] = $aItem['item_name'];;//订单标题
    			$data['item_id'] = $aItem['aitem_id'];//商品关联ID
    			$data['com_id'] = $comId;//企业ID
    			$data['user_id'] = $userId;//会员id
    			$data['account'] = $username;//用户账号
    			$data['item_num'] = 1;//订单商品数量
    			$data['send_num'] = 0;//发货数量
    			$data['total_fee'] = $totalFee;//订单总价
    			$data['post_fee'] = $aItem['post_fee'];//邮费
    			$data['payment'] = $totalFee;//实际要支付的金额
    			$data['pay_time'] = strtotime($deliveryTime);//支付时间
    			$data['receiver_name'] = $receiverName;//收货人姓名
    			$data['receiver_state'] = $receiverStateName;//收货人所在省份
    			$data['receiver_city'] = $receiverCityName;//收货人所在城市
    			$data['receiver_district'] = $receiverDistrictName;//收货人所在地区
    			$data['receiver_address'] = $receiverAddr;//收货人详细地址
    			//$data['pay_time'] = ;//收货人邮编
    			$data['receiver_mobile'] = $receiverPhone;//收货人手机号
    			$data['status'] = 'WAIT_SELLER_SEND_GOODS';
    			//$data['receiver_phone'] = ;//收货人电话
    			$data['buyer_area'] = $buyerArea;//买家地区ID
    			$data['price'] = $aItem['price'];//商品价格
    			$data['cost_price'] = $aItem['cost_price'];//商品成本价
    			$data['item_img'] = $aItem['item_img'];//商品图片
    			$data['creat_time'] = time();//创建时间
    			$result = $this->activityTrade->add($data);
    			if(!$result){
    				#TODO
    				$this->activityTrade->rollback();//回滚
    				$msg = "添加trade错误";
    				self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} atid={$atid}");
    				$this->retError('1144', $msg);
    			}
    		}
    		
    		//生成订单子表
    		$titlePrefix = "【集配单-{$username}】";
    		
    		$data = array();
    		$data['atid'] = $atid;
    		$data['user_id'] = $userId;//会员id
    		$data['aid'] = $aItem['aid'];//活动id
    		$data['aitem_id'] = $aItem['aitem_id'];//活动id
    		$data['item_id'] = $aItem['item_id'];//商品关联ID
    		$data['item_name'] = $titlePrefix.$aItem['item_name'];//商品名称
    		$data['price'] = $aItem['price'];//商品价格
    		$data['cost_price'] = $aItem['cost_price'];//商品成本价
    		$data['shop_price'] = $aItem['shop_price'];//商城价格
    		$data['post_fee'] =  $aItem['post_fee'];//邮费
    		$data['item_img'] = $aItem['item_img'];//商品图片
    		$data['weight'] = $aItem['weight'];
    		$data['status'] = 'WAIT_SELLER_SEND_GOODS';
    		$result = $this->activityOrder->add($data);
    		if(!$result){
    			$this->activityTrade->rollback();//回滚
    			$msg = "添加order错误";
    			self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} atid={$atid}");
    			$this->retError('1145', $msg);
    		}
    		
    		//调用接口通知
    		$url = C('API').'welfareEmp/setReceiveState';
    		$param = array();
    		$param['shopSupplyNo'] = $atid;
    		$param['phoneNum'] = $phone;
    		$param['sign'] = $this->signature($param, 'CREATE');
    		$result = curl($url,$param);
    		$retAtt = json_decode($result, true);
    		if(isset($retAtt['errcode']) && empty($retAtt['errcode'])){
    			$this->activityTrade->commit();
    			//更新数据
 				$data = array();
 				$data['atid'] = $atid;
 				$data['receive_status'] = 1;
 				$data['receive_time'] = date('Y-m-d H:i:s');
 				$where = array();
 				$where['recever_id'] = $receverId;
    			$this->giftGroupOrderRecever->where($where)->save($data);
    			//更新库存，销量
    			foreach ($itemList as $item){
    				$itemId = $item['item_id'];
    				$skuId = $item['sku_id'];
    				$num = $item['num'];
    				//购买减item_store库存
    				$this->itemStore->where("item_id=$itemId")->setDec('store', $num);
    				//购买增加item销量
    				$this->itemCount->where("item_id=$itemId")->setInc('sold_quantity', $num);
    				//减sku_store库存
    				$this->skuStoreModel->where("sku_id=$skuId")->setDec('store', $num);
    				//购买增加sku销量
    				$this->sysitemSku->where("sku_id =$skuId")->setInc('sold_quantity', $num);
    			}
    			
    			$msg = "推送成功";
    			self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} result={$result} atid={$atid}",'success');
    			$this->retSuccess(array(), 'success');
    		}else{
    			$this->activityTrade->rollback();//回滚
    			$msg = "通知接口失败";
    			self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} result={$result} atid={$atid}");
    			$this->retError('1146',$msg);
    		}
    	}catch (\Exception $e){
    		$this->activityTrade->rollback();//回滚
    		$msg = "系统错误 exception:".$e->getMessage();
    		self::log("msg:{$msg} userId={$userId} orderId={$orderId} aItemId={$aItemId} atid={$atid}");
    		$this->retError('1150', $msg);
    	}
    }
    
    /**
     * 更具手机号批量获取用户user_id，如果用户不存在，则自动注册
     * @param unknown $phoneArr
     */
    private function getUserIdArr($comId, $userArr){
    	if(empty($userArr)){
    		return false;
    	}
    	$phomeMap = array();
    	foreach ($userArr as $user){
    		$phoneNum = $user['phoneNum'];
    		$empName = empty($user['empName']) ? $phoneNum : $user['empName'];
    		$userId = $this->sysAccount->getUserId($phoneNum, $comId, $empName);
    		$phomeMap[$phoneNum] = $userId;
    	}
    	return $phomeMap;
    }
    
    /////////////////////测试方法
    public function test(){
    	//$url = C('API').'welfareEmp/setReceiveState';
    	$param = array();
    	$param['orderId'] = '1001';
    	$param['aItemId'] = '117';
    	$param['userId'] = '613';
    	$param['sign'] = $this->signature($param, 'CREATE');
    	//$result = curl($url,$param);
    	print_r($param);
    	exit();
    }
}