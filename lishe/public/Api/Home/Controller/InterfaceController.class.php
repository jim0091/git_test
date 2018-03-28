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
class InterfaceController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelEctools=M('ectools_payments');
		$this->modelDepositLog=M('sysuser_user_deposit_log');
		$this->modelCItemConfig=M('company_item_config');
		$this->modelCCategoryConfig=M('company_category_config');
	}
	
	public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}

	public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents("/data/www/b2b2c/public/business/logs/".$type."/".$type.'_'.date('YmdH').'.txt',$data,FILE_APPEND);
		}
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
	
	//更改京东产品价格 赵尊杰
	public function updateJdPrice(){
		$sku = I('post.sku');
		$costPrice = I('post.price');//京东结算价
		$jdPrice = I('post.jdPrice');//京东销售价		
		if(!empty($sku) && !empty($price) && !empty($jdPrice)){
			$this->retError(1001,'必要参数不能为空');
		}
		$condition['jd_sku']=$sku;
		$price=C('JD_PRICE_DISCOUNT')*$jdPrice/100;
		$checkItem=$this->modelItem->field('item_id,shop_id')->where($condition)->find();
		if(empty($checkItem['item_id'])){
			$this->retError(1002,'没有找到该产品');
		}
		$data['jd_price']=$jdPrice;
		$data['mkt_price']=$jdPrice;
		$data['price']=round($price,1);
		$data['cost_price']=$costPrice;
		$data['profit_rate']=($data['price']-$data['cost_price'])*100/$data['price'];
		if($data['profit_rate']<=0){
			$data['price']=$jdPrice;
			$data['profit_rate']=0;
		}
		
		$ret=$this->modelItem->where($condition)->save($data);
		if(empty($ret)){
			$this->retError(1003,'更新失败');
		}else{
			//更新item_sku表
			$skuData['price']=$data['price'];
			$skuData['cost_price']=$data['cost_price'];
			$skuData['mkt_price']=$data['mkt_price'];
			$this->modelItemSku->where('item_id='.$checkItem['item_id'])->save($skuData);
			
			//计算利润率，更新上下架状态
			if($data['profit_rate']<C('JD_PROFIT_RATE')){
				//下架
				$status='instock';
				$this->updateItemStatus($checkItem['item_id'],$checkItem['shop_id'],$status);
			}else{
				//上架
				//$status='onsale';
				$status='';
			}
			//$this->updateItemStatus($checkItem['item_id'],$checkItem['shop_id'],$status);
			$this->makeLog('updateJdPrice','sku:'.$sku.',itemId:'.$checkItem['item_id'].',costPrice:'.$costPrice.',jdPrice:'.$jdPrice.' status:'.$status."\n");
			$this->retSuccess(array('itemId'=>$checkItem['item_id']),'更新成功');
		}
	}
	
	//更改京东产品上下架状态,$status 100: 上架;0: 下架; 赵尊杰
	public function updateJdSaleStatus(){
		$sku = I('post.sku');//京东产品SKU
		$status = I('post.saleStatus');//上下架状态：0-下架，100上架
		if(!empty($sku) && in_array($status,array(0,100))){
			$condition['jd_sku']=$sku;
			$item=$this->modelItem->field('item_id,shop_id')->where($condition)->find();
			if(empty($item['item_id'])){
				$this->retError(1002,'没有找到该产品');
				$this->makeLog('updateJdSaleStatus','sku:'.$sku.',status:'.$status.",1002\n");
			}
		}else{
			$this->retError(1001,'必要参数不能为空');
			$this->makeLog('updateJdSaleStatus','sku:'.$sku.',status:'.$status.',itemId:'.$item['item_id'].",1001\n");
		}
		$statusArr=array(
			0=>'instock',
			100=>'onsale'
		);
		$ret= $this->updateItemStatus($item['item_id'],$item['shop_id'],$statusArr[$status]);
		$this->makeLog('updateJdSaleStatus','sku:'.$sku.',status:'.$status.',itemId:'.$item['item_id'].','.$ret."\n");
		$this->retSuccess(array('itemId'=>$item['item_id']),'更新成功');
	}
	
	//更新产品上下架状态,$status onsale: 出售中;instock: 库中; 赵尊杰
	public function updateItemStatus($itemId,$shopId,$status){
		$condition['item_id']=$itemId;
		$data=array(
			'shop_id'=>$shopId,
			'approve_status'=>$status
		);
		if($status=='onsale'){
			$data['list_time']=time();
		}else{
			$data['delist_time']=time();
		}
		$item=$this->modelItemStatus->field('item_id')->where($condition)->find();
		if(empty($item['item_id'])){
			$data['item_id']=$itemId;
			return $this->modelItemStatus->add($data);
		}else{
			return $this->modelItemStatus->where($condition)->save($data);
		}		
	}
	
	//更新京东产品库存、活动状态
	public function updateItemFlag(){
		$sku = I('post.sku');//京东产品SKU
		$flag = I('post.flag');//库存、活动状态：1-广东无货，2-活动不可售
		if(empty($sku) and empty($flag)){
			$this->retError(1001,'必要参数不能为空');
		}
		$condition['jd_sku']=$sku;
		$item=$this->modelItem->field('item_id')->where($condition)->find();
		if(empty($item['item_id'])){
			$this->retError(1002,'找不到该商品');
			exit;
		}
		$data=array(
			'flag'=>$flag
		);
		$return=$this->modelItem->where($condition)->save($data);
		if(empty($return)){
			$this->retError(1003,'更新失败');
		}else{
			$this->retSuccess(array('itemId'=>$item['item_id']),'更新成功');
		}			
	}
	
	//通知京东订单支付状态 赵尊杰
	public function notifyJdPay(){
		$sku=I('post.sku');//京东产品SKU
	}

	// 选择不同地区时调用京东接口判断库存 20160613 start
	public function checkJdStock(){
		$item_id = I('get.item_id','','intval'); // 得到库存的ids
		$jd_ids = I('get.jd_ids','','trim');		 
		$where['item_id'] = $item_id;
		$res = $this->modelItem ->field('jd_sku')->where($where) -> find();		 
		if($res['jd_sku']>0){		
			$url=C('API_AOSERVER').'jd/product/checkstock';	
			$data='{"skuNums":[{"skuId":'.$res['jd_sku'].',"num":1}],"area":"'.$jd_ids.'"}';
            $result = $this->requestJdPost($url,$data);        
            $retArr = json_decode($result,true);
            if($retArr['data'][0]['stockStateId']==33 or $retArr['data'][0]['stockStateId']==39 or $retArr['data'][0]['stockStateId']==40){
				$url=C('API_AOSERVER').'jd/product/checkRep';
				$data='{"skuIds":"'.$res['jd_sku'].'"}';
				$result=$this->requestJdPost($url,$data);
	            $retArr=json_decode($result,true);
	            if($retArr['code']==100){
	            	if($retArr['data'][0]['saleState']==0){
	            		$stock=array('status'=>34,'msg'=>'无货');
	            	}else{
	            		$url=C('API_AOSERVER').'jd/product/checkAreaLimit';
						$areas=explode('_',$jd_ids);
						$data='{"skuIds":"'.$res['jd_sku'].'","province":'.intval($areas[0]).',"city":'.intval($areas[1]).',"county":'.intval($areas[2]).',"town":'.intval($areas[3]).'}';
						$result=$this->requestJdPost($url,$data);
			            $retArr=json_decode($result,true);
			            if($retArr['code']==100){
			            	if($retArr['data'][0]['isAreaRestrict']==true){
			            		$stock=array('status'=>34,'msg'=>'无货');
			            	}else{
								$stock=array('status'=>33,'msg'=>'有货');
							}
			            }						
					}
	            }
			}else{
				$stock=array('status'=>34,'msg'=>'无货');		
			}
		}else{
			$stock=array('status'=>33,'msg'=>'有货');
		}		
		echo $stock['status'];
	}
	
	public function checkCartStock(){
		$item = trim($_POST['items']);
		$area = I('post.area');
		$itemParam=json_decode($item,true);
		if(!empty($itemParam)){
			foreach($itemParam as $key=>$value){
				$itemId[]=$value['id'];
				$itemNum[$value['id']]=$value['num'];
				$stock[$value['id']]=33;//默认有货
			}
		}else{
			$this->retError(1001,'必要参数不能为空');
		}
		$condition=array(
			'item_id'=>array('in',$itemId)
		);
		$sku=array();
		$checkItem=$this->modelItem ->field('jd_sku,item_id')->where($condition)->select();
		if(!empty($checkItem)){
			foreach($checkItem as $key=>$value){
				if($value['jd_sku']>0){
					$sku[]=array(
						'skuId'=>$value['jd_sku'],
						'num'=>$itemNum[$value['item_id']]
					);
				}
				$jdItemId[$value['jd_sku']]=$value['item_id'];
			}
		}
		
		if(!empty($sku)){
			$data=array(
				'skuNums'=>$sku,
				'area'=>$area
			);
			$url=C('API_AOSERVER').'jd/product/checkstock';
            $result=$this->requestJdPost($url,json_encode($data));
            $retArr=json_decode($result,true);
            if($retArr['code']==100){
				foreach($retArr['data'] as $key=>$value){
					$stock[$jdItemId[$value['skuId']]]=$value['stockStateId'];
					if($value['stockStateId']==33 or $value['stockStateId']==39 or $value['stockStateId']==40){
						$checkSku[]=$value['skuId'];
					}
				}		
			}else{//通讯失败设置为无货
				foreach($retArr['data'] as $key=>$value){
					$stock[$jdItemId[$value['skuId']]]=34;
				}
				$this->makeLog('checkstock','url:'.$url.',data:'.json_encode($data).',return:'.$result."\n");
			}
			//$this->makeLog('checkstock','url:'.$url.',data:'.json_encode($data).',return:'.$result."\n");
		}
		
		//验证是否可售
		if(!empty($checkSku)){
			$url=C('API_AOSERVER').'jd/product/checkRep';
			$data='{"skuIds":"'.implode(',',$checkSku).'"}';
			$result=$this->requestJdPost($url,$data);
            $retArr=json_decode($result,true);
            if($retArr['code']==100){
            	unset($checkSku);
				foreach($retArr['data'] as $key=>$value){
					//如果不可售设置为无货
					if($value['saleState']==0){
						$stock[$jdItemId[$value['skuId']]]=34;
					}else{
						$checkSku[]=$value['skuId'];
					}
				}		
			}else{
				$this->makeLog('checkRep','url:'.$url.',data:'.$data.',return:'.$result."\n");
			}
			//$this->makeLog('checkRep','url:'.$url.',data:'.$data.',return:'.$result."\n");
		}
		
		//验证是否支持配送
		if(!empty($checkSku)){
			$areas=explode('_',$area);
			$url=C('API_AOSERVER').'jd/product/checkAreaLimit';
			$data='{"skuIds":"'.implode(',',$checkSku).'","province":'.intval($areas[0]).',"city":'.intval($areas[1]).',"county":'.intval($areas[2]).',"town":'.intval($areas[3]).'}';
			$result=$this->requestJdPost($url,$data);
            $retArr=json_decode($result,true);
            if($retArr['code']==100){
				foreach($retArr['data'] as $key=>$value){
					//如果有区域限制设置为无货
					if($value['isAreaRestrict']==true){
						$stock[$value['skuId']]=34;
					}
				}		
			}else{
				$this->makeLog('checkAreaLimit','url:'.$url.',data:'.$data.',return:'.$result."\n");
			}
			//$this->makeLog('checkAreaLimit','url:'.$url.',data:'.$data.',return:'.$result."\n");
		}
		
		$this->retSuccess($stock,'操作成功');		
	}
	
	
	public function syncOrderItem($paymentId=''){
		if(empty($paymentId)){
			$paymentId = I('post.paymentId');
		}		
		if(!empty($paymentId)){
			$orderPay=M('ectools_trade_paybill')->field('tid')->where('payment_id='.$paymentId)->select();
			if(!empty($orderPay)){
				foreach($orderPay as $key=>$value){
					$tid[]=$value['tid'];
				}
				$condition=array(
						'shop_id'=>array('neq',C('JD_SHOP_ID')),
						'tid'=>array('in',''.implode(',',$tid).'')
					);
				$order=$this->modelOrder->field('oid,item_id,sku_id')->where($condition)->select();
				foreach($order as $key=>$value){
					$itemId[]=$value['item_id'];
					$skuId[]=$value['sku_id'];
				}
				$skuCondition=array(
					'sku_id'=>array('in',''.implode(',',$skuId).'')
				);
				$sku=$this->modelItemSku->field('sku_id,cost_price')->where($skuCondition)->select();
				foreach($sku as $key=>$value){
					$costPrice[$value['sku_id']]=$value['cost_price'];
				}
				$itemCondition=array(
					'item_id'=>array('in',''.implode(',',$itemId).'')
				);
				$item=$this->modelItem->field('item_id,supplier_id,send_type')->where($itemCondition)->select();
				foreach($item as $key=>$value){
					$supplier[$value['item_id']]=$value['supplier_id'];
					$sendType[$value['item_id']]=$value['send_type'];
				}
				
				foreach($order as $key=>$value){
					$data=array(
						'supplier_id'=>$supplier[$value['item_id']],
						'send_type'=>$sendType[$value['item_id']],
						'cost_price'=>$costPrice[$value['sku_id']],
					);
					$this->modelOrder->where('oid='.$value['oid'])->save($data);
				}
			}
		}
	}
	
	//同步订单到第三方 赵尊杰 2016-06-15
	public function syncOrder(){
		$paymentId = I('post.paymentId');
		//if($_GET['from']=='hand'){
			//$paymentId = I('get.paymentId');
		//}		
		if(!empty($paymentId)){
			echo $this->syncJdOrder($paymentId);
		}		
	}
	
	//同步订单到京东 赵尊杰 2016-06-15
	public function syncJdOrder($paymentId){
		$orderPay=M('ectools_trade_paybill')->field('tid')->where('payment_id='.$paymentId)->select();
		if(!empty($orderPay)){
			foreach($orderPay as $key=>$value){
				$tid[]=$value['tid'];
			}
			$condition=array(
				'shop_id'=>C('JD_SHOP_ID'),
				'tid'=>array('in',''.implode(',',$tid).'')
			);
			
			$trade=$this->modelTrade->field('tid,pay_time,payed_fee,buyer_area,receiver_name,receiver_address,receiver_mobile,receiver_phone')->where($condition)->find();
			//如果订单存在并且已支付
			if(!empty($trade['tid']) && !empty($trade['pay_time']) && !empty($trade['payed_fee'])){
				list($province,$city,$county,$town)=explode('/',$trade['buyer_area']);
				$order=M('systrade_order')->field('item_id,num')->where('disabled=0 AND tid='.$trade['tid'])->select();
				foreach($order as $key=>$value){
					$itemId[]=$value['item_id'];
					$num[$value['item_id']]=$value['num'];
				}
				$jdSkuArr=M('sysitem_item')->field('jd_sku,item_id')->where('item_id IN ('.implode(',',$itemId).')')->select();
				foreach($jdSkuArr as $key=>$value){
					$sku[]=array(
						'id'=>$value['jd_sku'],
						'num'=>$num[$value['item_id']]
					);
				}
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
            	//记录日志
            	$log=array(
        			'payment_id'=>$paymentId,
        			'tid'=>$trade['tid'],
        			'log_type'=>'synsJdOrder',
        			'code'=>$ret['code'],
        			'detail'=>$result,
        			'modified_time'=>time()
        		);
				
            	if($ret['code']==100){
            		if($ret['errCode']===0){
						$data=array(
	            			'payment_id'=>$paymentId,
	            			'tid'=>$trade['tid'],
	            			'sync_order_id'=>$ret['data']['jdOrderId'],
	            			'modified_time'=>time()
	            		);
	            		$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'success','sync_pay_status'=>'success','sync_memo'=>$result,'status'=>'IN_STOCK'));
						M('systrade_sync_trade')->add($data);
						$msg=$ret['data']['jdOrderId'];
						$log['sync_order_id']=$ret['data']['jdOrderId'];
					}else{
						$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_pay_status'=>'failure','sync_memo'=>$result));
						$msg=-1;
					}            		
				}else{
					$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_pay_status'=>'failure','sync_memo'=>$result));
					$msg=-1;
				}
				$this->makeSqlLog($log);
				return $msg;
			}
		}
		return NULL;
	}
	
	//支付第三方订单 赵尊杰 2016-06-15
	public function paySyncOrder(){
		$orderId = I('post.orderId');//第三方订单号
		//查询本地订单
		$result=$this->payJdOrder($orderId);
		$ret=json_decode($result,true);
		$order=M('systrade_sync_trade')->where('sync_order_id='.$orderId)->find();
		//记录日志
    	$log=array(
			'payment_id'=>$order['payment_id'],
			'tid'=>$order['tid'],
			'sync_order_id'=>$orderId,
			'log_type'=>'jdPay',
			'code'=>$ret['code'],
			'detail'=>$result,
			'modified_time'=>time()
		);
		$this->makeSqlLog($log);
		
		if($ret['code']==100){
			if($ret['errCode']===0){
				$this->retSuccess(array('orderId'=>$orderId),'支付成功');
			}else{
				$this->modelTrade->where('tid='.$order['tid'])->save(array('sync_pay_status'=>'failure'));
				$this->retError(1001,'支付失败，错误代码：'.$ret['code'].'，错误信息：'.$ret['msg']);
			}
		}else{
			$this->modelTrade->where('tid='.$order['tid'])->save(array('sync_pay_status'=>'failure'));
			$this->retError(1001,'支付失败，错误代码：'.$ret['code'].'，错误信息：'.$ret['msg']);
		}
	}
	
	//支付京东订单 赵尊杰 2016-06-15
	public function payJdOrder($orderId){
		return $this->requestJdPost(C('API_AOSERVER').'jd/order/doPay','{"jdOrderId":'.$orderId.'}');
	}
	
	//查询京东订单物流 赵尊杰 2016-06-23
	public function getJdExpress(){ 
		header("Content-type:text/html;charset=utf-8");
		$orderId = I('get.orderId');//第三方订单号
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryOrderTrack','{"jdOrderId":'.$orderId.'}');
		$ret=json_decode($result,true);
		echo '<pre>';
		print_r($ret);
		echo '</pre>';
	}
	
	//查询京东订单详情 赵尊杰 2016-06-23
	public function getJdOrderInfo(){
		header("Content-type:text/html;charset=utf-8");
		$orderId = I('get.orderId');//第三方订单号
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryJdOrderInfo','{"jdOrderId":'.$orderId.'}');
		$ret=json_decode($result,true);
		echo '<pre>';
		print_r($ret);
		echo '</pre>';
	}	

	// 积分充值 20160629 start
	public function recharge(){
		$ponit=array(
			//'0.01'=>51000,//测试用，上线前一定要删除
	        '30.00'=>3010,
	        '50.00'=>5050,
	        '100.00'=>10150,
	        '200.00'=>20350,
	        '300.00'=>30500,
	        '500.00'=>51000
	    );

		$totalFee=I('post.totalFee');
		$userId=I('post.userId');
		$mobile=I('post.mobile');
		$tradeNo=I('post.tradeNo');
		$addPoint=$ponit[$totalFee]; //增加的积分
		if(empty($addPoint)){
			$addPoint=$totalFee*100; //增加的积分
		}
        //执行数据积分表表的更新操作 
    	$this->modelDeposit->where('user_id ='.$userId)->setInc('deposit',($addPoint/100)); // 用户的积分加3
        $this->modelDeposit->where('user_id ='.$userId)->setInc('balance',$addPoint);
        $this->modelDeposit->where('user_id ='.$userId)->setInc('commonAmount',$addPoint);

        $dataInsert = array(
        	'payment_id' =>$tradeNo,	
            'money'=>$totalFee,
            'cur_money'=>$totalFee,
            'status'=>'succ',
            'user_id'=>$userId,
            'user_name'=>$mobile,
            'pay_type'=>'online',
            'pay_app_id'=>'alipay',
            'pay_name'=>'支付宝',
            'payed_time'=>time(),
            'trade_no'=>$tradeNo
        );
        $this->modelEctools->data($dataInsert)->add();

		//写入充值记录
		$message = "积分充值，用户名：".$mobile.",支付单号：".$tradeNo;
		$dataInsertLog = array(
             'type'=>'add',
             'user_id'=>$userId,
             'operator'=>$mobile,
             'fee'=>$totalFee,
             'message'=>$message,
             'logtime'=>time()
			);

        $this->modelDepositLog->data($dataInsertLog)->add();
	 
        $sign=md5('orderno='.$tradeNo.'&phoneNum='.$mobile.'&pointsAmount='.$addPoint.'&pointsType=1'.C('API_KEY'));
        $url=C('API').'mallPoints/recharge';
        $data=array(
            'phoneNum'=>$mobile,
            'pointsAmount'=>$addPoint,
            'orderno'=>$tradeNo,
            'pointsType'=>1,
            'sign'=>$sign
        );		
        $return=$this->requestPost($url,$data);
        $this->makeLog('recharge','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
        return $return;
	}
    // 积分充值 20160629 end
    
	//同步东莞移动E卡通订单 赵尊杰 2016-07-01
	public function syncEcardOrder(){
		$paymentId = I('post.paymentId');//订单号
		$this->syncOrderItem($paymentId);
		$fee = I('post.fee');//订单金额
		$tids = I('post.tids');//订单号
		$payPassword = I('post.payPassword');//支付密码
		$userId = I('post.userId');
		$ip = I('post.ip');
		$orderPay=$this->modelEctools->field('user_id,money,cur_money,user_name,status')->where('payment_id='.$paymentId)->find();
		//日志
		$log=array(
			'payment_id'=>$paymentId,
			'tid'=>$tids,
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
		
		if(empty($orderPay)){
			//记录日志
			$log['code']=1001;
	    	$log['detail']='未找到'.$paymentId.'对应订单,post:'.$postData;
			$this->makeSqlLog($log);
			$this->retError(1001,'支付失败，未查询到对应订单！');
		}
		if($orderPay['status']=='succ'){
			//记录日志
			$log['code']=1002;
	    	$log['detail']='订单'.$paymentId.'已支付,post:'.$postData;
			$this->makeSqlLog($log);
			$this->retError(1002,'该订单已支付，请勿重复支付！');
		}
		
		//检查是否有权限支付
		$userName=$orderPay['user_name'];
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
		$sign=md5('appKey='.$appKey.'&mobileNo='.$userName.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'mobileNo'=>$userName,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$this->makeLog('gd10086_user','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
    	$ret=json_decode($return,true);
    	if($ret['code']==100){
    		if($ret['errCode']==0){			
		    	//推送订单
		    	$empCode=$ret['data']['empCode'];//员工编号
		    	$createTime=date('Y-m-d H:i:s');
		    	$totalFee=$orderPay['money'];
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
		    	$this->makeLog('gd10086_ecard','url:'.$url.',data:'.json_encode($orderPost).',return:'.$returns."\n");
		    	$rets=json_decode($returns,true);
		    	if($rets['code']==100){
		    		if($rets['errCode']==0){
			    		//更新订单状态	    		
			    		$order=$this->modelTrade->field('tid,payment')->where('tid IN ('.$tids.')')->select();
			    		$data=array(
			    			'sync_pay_status'=>'success',
			    			'sync_trade_status'=>'success',
			    			'pay_time'=>time(),
			    			'transno'=>$paymentId,
			    			'pay_type'=>'e-card',
			    			'pay_memo'=>$returns,
			    			'status'=>'WAIT_SELLER_SEND_GOODS'
			    		);
			    		foreach($order as $key=>$value){
							$data['payed_fee']=$value['payment'];
							$this->modelTrade->where('tid='.$value['tid'])->save($data);
						}
						$orderData=array(
			    			'pay_time'=>time(),
			    			'status'=>'WAIT_SELLER_SEND_GOODS'
			    		);
						$this->modelOrder->where('tid IN ('.$tids.')')->save($orderData);
						
			    		$payData=array(
			    			'cur_money'=>$totalFee,//实际支付金额
			    			'status'=>'succ',
			    			'pay_type'=>'online',
			    			'pay_app_id'=>'e-card',
			    			'pay_name'=>'东莞移动E卡通',
			    			'payed_time'=>time(),
			    			'op_id'=>$userId,
			    			'op_name'=>$userName,
			    			'pay_account'=>$empCode,//东莞移动员工编号
			    			'modified_time'=>time(),
			    			'currency'=>'CNY',
			    			'ip'=>$ip,
			    			'memo'=>$returns,
			    			'trade_no'=>$paymentId,
			    			'tids'=>$tids
			    		);
			    		$this->modelEctools->where('payment_id='.$paymentId)->save($payData);
			    		
						//记录日志
						$log['detail']='支付成功,post:'.$postData.'data:'.json_encode($payData).',return:'.$returns;
						$this->makeSqlLog($log);
						$this->retSuccess(array('orderId'=>$paymentId),'支付成功');
					}else{
						$this->retError(1004,'支付失败，错误信息：'.$rets['msg']);
					}
				}else{
					//记录日志
					$log['code']=1004;
					$log['detail']='支付失败,post:'.$postData.',return:'.$returns;
					$this->makeSqlLog($log);
					$this->retError(1004,'支付失败，错误信息：'.$ret['msg']);
				}
			}else{
				$this->retError(1005,'支付失败，错误信息：'.$ret['msg']);
			}			
	    }else{
	    	$log['code']=1003;
	    	$log['detail']='用户验证失败,post:'.$postData.',return:'.$return;
			$this->makeSqlLog($log);
			$this->retError(1003,'支付失败，错误信息：'.$ret['msg']);
		}
	}
	
	//东莞移动E卡通订单退单接口 赵尊杰 2016-07-01
	public function notifyEcardRefund(){
		$paymentId = I('post.paymentId');//订单号
		
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');
    	
    	$createTime=date('Y-m-d H:i:s');
		$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderNum='.$paymentId.'&rtnMoney='.$refundFee.'&rtnNum='.$goodsNum.'&rtnTime='.$createTime.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'orderNum'=>$paymentId,
    		'rtnMoney'=>$refundFee,
    		'rtnNum'=>$goodsNum,
    		'rtnTime'=>$createTime,
    		'sign'=>$sign
    	);  
    	  	
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$ret=json_decode($result,true);	
	}
	
	//查询东莞移动E卡通余额 赵尊杰 2016-07-01
	public function getEcardBalance(){
		
	}
	
	//东莞移动E卡通订单签收接口 赵尊杰 2016-07-01
	public function notifyEcardOrder(){
		
	}
	
	
	
	//企业商品配置接口  章锐 2016-07-27
	public function syncItemConfig(){
		header("content-type:textml;charset=utf-8");
			$datas=$_POST['data'];
			$data=json_decode($datas,true);
			if(!empty($data)){
					$categoryData=array(
						'cat_id'         => 0,
						'profit_rate'    => 30
					);	
					if(!empty($data['item_config_id'])){
						$categoryData['item_config_id']=$data['parentId'];
					}				
					if(!empty($data['com_id'])){
						$categoryData['com_id']=$data['com_id'];
					}			
					if(!empty($data['cat_name'])){
						$categoryData['cat_name']=$data['cat_name'];
					}																
					$itemData=array(
						'cat_content' => 0,
						'cat_banner'  => 0,
						'recommend'   => 0,
						'profit_rate' => 30,
					);		
					if(!empty($data['com_id'])){
						$itemData['com_id']=$data['com_id'];
					}		
					if(!empty($data['cat_name'])){
						$itemData['cat_name']=$data['cat_name'];
					}	
					if(!empty($data['order_sort'])){
						$itemData['order_sort']=$data['order_sort'];
						$categoryData['order_sort']=$data['order_sort'];
					}											
					$data['creat_time']=time();
					if($data['type']==1){
							if(empty($data['com_id']) || empty($data['cat_name'])){
										$this->retError(1001,'公司ID或专区名称不能为空');
										exit;
							}
						if($data['parentId']>0){
								//加入商品二级类目					
									//添加
									$categoryData['item_config_id']=$data['parentId'];
									$addRes=$this->modelCCategoryConfig->data($categoryData)->add();	
									if($addRes){
	//									添加成功/返回自增id
										$this->retSuccess(array('insertId'=>$addRes),'添加成功');
									}else{
	//									添加失败
										$this->makeLog('addcompanyCategory','添加company_category_config表失败,"\n"data:'.json_encode($data).',"\n"sql语句:'.M('company_category_config')->getLastSql()."\n");
										$this->retError(1004,'添加失败');
									}											
							}else if($data['parentId']==0){
									//加入company_config表
									if(empty($data['order_sort'])){
											$this->retError(1001,'排序规则不能为空');
											exit;
									}
										//添加
									$itemData['order_sort']=$data['order_sort'];
									$itemData['creat_time']=$data['creat_time'];
									$addRes=$this->modelCItemConfig->data($itemData)->add();
									if($addRes){
	//									添加成功
										$this->retSuccess(array('insertId'=>$addRes),'添加成功');
									}else{
	//									添加失败
										$this->makeLog('addcompanyItem','添加company_item_config表失败,"\n"data:'.json_encode($data).',"\n"sql语句:'.M('company_item_config')->getLastSql()."\n");
										$this->retError(1004,'添加失败');
									}									
								}
							}else if($data['type']==2){
								//更新
								if(!empty($data['item_config_id'])){
										$itemData['modifyine_time']=time();
										unset($condition);
										$condition['item_config_id']=$data['item_config_id'];
										if(!empty($data['item_ids'])){
											$itemData['recommend']=$data['item_ids'];
										}else{
											$itemData['recommend']=0;
										}									
										$updateRes=$this->modelCItemConfig->where($condition)->data($itemData)->save();
										if($updateRes){
											//成功
													$this->retSuccess(array('success'=>1),'更新成功');
										}else{
											//失败
													$this->makeLog('updatecompanyItem','更新company_item_config表失败,"\n"data:'.json_encode($data).',"\n"sql语句:'.M('company_item_config')->getLastSql()."\n");
													$this->retError(1003,'更新失败');							
										}		
								
								}else if(!empty($data['cat_config_id'])){
										unset($condition);
										$condition['cat_config_id']=$data['cat_config_id'];
										if(!empty($data['item_ids'])){
											$categoryData['item_ids']=$data['item_ids'];
										}else{
											$categoryData['item_ids']=0;
										}
										$updateRes=$this->modelCCategoryConfig->where($condition)->data($categoryData)->save();
										if($updateRes){
											//成功
													$this->retSuccess(array('success'=>1),'更新成功');
										}else{
											//失败
													$this->makeLog('updatecompanyCategory','更新company_category_config表失败,"\n"data:'.json_encode($data).',"\n"sql语句:'.M('company_category_config')->getLastSql()."\n");
													$this->retError(1003,'更新失败');							
										}									
								}
							}else if($data['type']==3){
									//删除
								if(!empty($data['item_config_id'])){
										//删除企业产品表的数据
									$delRes=$this->modelCItemConfig->where('item_config_id='.$data['item_config_id'])->delete();
									if($delRes){
											$this->retSuccess(array('success'=>1),'删除成功');
									}else{
											$this->makeLog('delcompanyConfig','删除company_item_config表失败,"\n"data:'.json_encode($data).',"\n"sql语句:'.M('company_item_config')->getLastSql()."\n");
											$this->retError(1004,'删除失败');												
									}
								}else if(!empty($data['cat_config_id'])){
										//删除企业分类表的数据
									$delRes=$this->modelCCategoryConfig->where('cat_config_id='.$data['cat_config_id'])->delete();
									if($delRes){
											$this->retSuccess(array('success'=>1),'删除成功');
									}else{
											$this->makeLog('delcompanyCategory','删除company_category_config表失败,"\n"data:'.json_encode($data).',"\n"sql语句:'.M('company_category_config')->getLastSql()."\n");
											$this->retError(1004,'删除失败');												
									}						
								}									
							}
			}else{
					$this->retError(1001,'必要参数不能为空');
			}
	}

}