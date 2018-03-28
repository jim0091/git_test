<?php
/**
  +----------------------------------------------------------------------------------------
 *  AdminModel
  +----------------------------------------------------------------------------------------
 * @author    王子铖 <962212011@qq.com>
 * @version  	$Id: HomeModel.class.php v001 2015-10-27
 * zhangrui 2016/10/28
 * @description 管理后台数据库操作管理员功能部分
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
	
    class HomeModel extends CommonModel{
    	public function __construct(){
    		$this->supplier_user=M('supplier_user');
			$this->supplier_order=M('supplier_order');
			$this->systrade_trade=M('systrade_trade');
			$this->systrade_order=M('systrade_order');
			$this->supplier_warehouse=M('supplier_warehouse');
			$this->supplier_sku=M('supplier_item_sku');
			$this->supplier_push=M('supplier_push_goods');
			$this->supplier_storage_order=M('supplier_storage_order');
			$this->supplier_storage_items=M('supplier_storage_items');
			$this->supplier_purchase=M('supplier_purchase');
			$this->supplier_cancel=M('supplier_cancel_history');
			$this->supplier_goods=M('supplier_order_goods');
			$this->sysitem_item_store=M("sysitem_item_store");
			$this->sysitem_sku_store=M("sysitem_sku_store");
			$this->syscategory_cat=M('syscategory_cat');
			$this->supplier_warehouse=M('supplier_warehouse');
			$this->ectools_payments=M('ectools_payments');
			$this->ectools_trade_paybill=M('ectools_trade_paybill');
			$this->supplier_saleorder_history=M('supplier_saleorder_history');
			$this->dOrder=D('Order');

		}
		//通过ID获取分类，不传参数则为所有的一级
		public function getAllCat($pid=""){
			$where=array();
			if(empty($pid)){
				$where['level']=1;
			}else{
				$where['parent_id']=$pid;
			}
			return $this->syscategory_cat->field("cat_name,cat_id")->where($where)->select();
		}

		public function getAllSupplierList(){
			return $this->supplier_user->field('supplier_id,company_name')->where('status=1')->select();
		}
		public function getAllList($table_name,$fields="",$where=null){
			$m=M($table_name);
			return $m->field($fields)->where($where)->select();
		}
		/*通过商品获取关联表列表*/
		public function getFieldByGoods($supplier_goods,$field_name,$model){
			$ids=array();
			foreach($supplier_goods as $key => $val){
				$ids[]=$val[$field_name];
			}
			$where[$field_name]=array('in',$ids);
			$model=M($model);
			$res=$model->where($where)->select();
			$data=array();
			foreach($res as $key => $val){
				$data[$val[$field_name]]=$val;
			}
			return $data;
		}
		//通过订单列表获取Sku数量
		public function getSkuCountByOrders($orderList="",$id="",$uid="0"){
			$ids=array();
			$data=array();
			foreach($orderList as $key => $val){
				//$this->supplier_sku->
				$ids[]=$val['order_id'];
				$data[$val['order_id']]=$val;
			}
			$ids=implode(",",$ids);
			if(!empty($id)){
				$ids=$id;
			}
			if(empty($ids)){
				return false;
			}
			$where=array(
				"order_id in (".$ids.")"
			);
			if($uid>0){
				$where['supplier_id']=$uid;
			}

			$skuList=$this->supplier_goods->field("*")->where($where)->select();
			foreach($skuList as $k => $v){
				$data[$v['order_id']]["prices"]+=$v['order_price']*$v['number'];
				$data[$v['order_id']]["skus"][]=$v;
			}
			foreach($data as $key => $val){
				$data[$key]['skuCount']=count($val['skus']);
			}
			return $data;
			//foreach($skuList as $k => $v){
				//$ids[$val['order_id']][]=
			//}

		}
		//修改商品
		public function editOrderGoods($list){
			$ids=array();
			foreach($list as $k => $v){
				if($v==0){
					$ids[]=$k;
					unset($list[$k]);
				}
			}
			$res="";
			if(!empty($ids)){
				$res=$this->supplier_goods->where(array('id'=>array('in',$ids)))->delete();
			}
			$ids = implode(',', array_keys($list));
			$sql = "UPDATE supplier_order_goods SET number = CASE id ";
			foreach ($list as $id => $ordinal) {
				$sql .= sprintf("WHEN %d THEN %d ", $id, $ordinal);
			}
			$sql .= "END WHERE id IN ($ids)";

			if($this->supplier_goods->execute($sql)||$res) {
				return true;
			}
			return false;
		}
		//商品入库修改数量
		public function editOrderGoodsStorage($list,$orderId,$keyId="storage_number",$setId="sku_id",$table="supplier_order_goods"){
			$ids=array();
			foreach($list as $k => $v){
				$ids[]=$v['skuNo'];
			}
			$res="";
			$ids = implode(',', $ids);
			$sql = "UPDATE $table SET $keyId = CASE $setId ";
			foreach ($list as $id => $ordinal) {
				$sql .= sprintf(" WHEN %d THEN %d ", $ordinal['skuNo'], $ordinal['actualQty']);
			}
			$sql .= "END WHERE $setId IN ($ids) and order_id = ".$orderId;
			if($this->supplier_goods->execute($sql)||$res) {
				return true;
			}
			return false;
		}
		public function checkOrder($order_id,$status,$uid,$remarks=""){
			$data=array(
				'status'=>$status,
				'supplier_check_remarks'=>$remarks,
				'supplier_check_time'=>date('Y-m-d H:i:s',time())
			);
			if($this->supplier_order->where("order_id=$order_id and supplier_id=$uid")->save($data)){

				return true;
			}
			return false;
		}
		public function getOrdersById($id="",$page=1,$where=""){
			$res="";
			$count=0;
			$where['supplier_id']=$id;
			if(!empty($id)){
				$res=$this->supplier_order->where($where)->page($page)->select();
				$count=$this->supplier_order->where($where)->count();
				$res['count']=$count;
			}
			return $res;

			/*
			$skus=$this->supplier_goods->where("supplier_id=$id and status=0")->group("order_id")->select();
			$ids=array();
			foreach($skus as $key => $val){
				$ids[]=$val['order_id'];
			}
			$ids=implode(',',$ids);
			$res="";
			if(!empty($ids)){
				$res= $this->supplier_order->where("order_id in (".$ids.")")->where($where)->page($page)->select();
				$count=$this->supplier_order->where("order_id in (".$ids.")")->where($where)->count();
				$res['count']=$count;
			}
			return $res;
			*/
		}
		public function checkPasswd($uid,$passwd){
			$res=$this->supplier_user->field("password")->where("supplier_id=$uid")->find();
			$passwd=md5($passwd);
			if($res['password']==$passwd){
				return true;
			}else{
				return false;
			}
		}
		public function editPasswd($uid="",$passwd=""){
			if(empty($passwd)||empty($uid)){
				return false;
			}
			$data=array(
				'password'=>md5($passwd)
			);
			if($this->supplier_user->where("supplier_id=$uid")->save($data)){
				return true;
			}else{
				return false;
			}


		}
		public function curl_post($url,$array){

			$curl = curl_init();
			$header = array(
				"content-type: application/x-www-form-urlencoded;
		charset=UTF-8"
			);

			curl_setopt($curl, CURLOPT_URL, $url);

			curl_setopt($curl, CURLOPT_HEADER, $header);

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($curl, CURLOPT_POST, 1);

			$post_data = $array;
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

			$data = curl_exec($curl);

			curl_close($curl);

			return $data;
		}
		public function getAllSkus($field="",$item_id=""){
			$res=$this->supplier_sku->field($field)->where("title <> '' ".$item_id)->select();

			foreach($res as $key =>$val){
				$res[$key]['barCode']=array(
					'barCode1'=>$val['barcode1']
				);
				unset($res[$key]['barcode1']);
				//$res[$key]['transportProperty']='1';
				$res[$key]['serialNumTrackInbound']="Y";
				$res[$key]['serialNumTrackOutbound']="Y";
				$res[$key]['serialNumTrackInventory']="Y";
			}
			return $res;
		}


		//通过订单号查询到sku列表
		public function getSkus($order_id="",$field="",$is_insert=true){
			$where=array();
			if(!empty($order_id)){
				$where=array(
					"order_id"=>$order_id
				);
			}
			$res=$this->supplier_goods->field($field)->where($where)->select();
			if($is_insert){
				foreach($res as $key =>$val){
					$res[$key]['barCode']=array(
						'barCode1'=>$val['barcode1']
					);
					unset($res[$key]['barcode1']);
					//$res[$key]['transportProperty']='1';
					$res[$key]['serialNumTrackInbound']="Y";
					$res[$key]['serialNumTrackOutbound']="Y";
					$res[$key]['serialNumTrackInventory']="Y";
				}
			}
			return $res;
		}
		//推送商品
		public function pushGoods($data){
			$url = C('API_SF').'item/push';
			$json=json_encode($data);
			$res=$this->curl_post($url,$json);
			$res=stristr($res,"{");
			$arr=json_decode($res);
			$items=$arr->data->items;
			foreach($data as $k => $v){
				$data[$k]['return_msg']=json_encode($items[$k]);
				$data[$k]['msg']=$items[$k]->note;
				unset($data[$k]['standardDescription']);

				$data[$k]['barCode']=reset($data[$k]['barCode']);
				unset($data[$k]['barcode1']);
			}
			if($arr->code=="100"){
				if($this->supplier_push->addAll($data)){
					return true;
				}
			}
			return false;
		}
		//顺丰取消订单
		public function cancel($order_id){
			$url = C('API_SF').'order/cancelPurchase';
			$data=array();
			$data['erpOrder']=$order_id;
			$json=json_encode($data);
			$this->supplier_cancel->add($json);
			$res=$this->curl_post($url,$json);
			$cancel['json']=$res;
			$cancel['send_json']=$json;
			$res=stristr($res,"{");
			$this->supplier_cancel->add($cancel);
			$arr=json_decode($res);
			if($arr->errCode=="0"){
					return true;
			}
			return false;

		}
		//判断仓库是否是顺丰
		public function orderIsSf($oid){
			$where=array(
				'order_id'=>$oid
			);
			$order=$this->supplier_order->where($where)->find();
			//如果是顺丰的话
			if($order['warehouse_id']==2){
				return true;
			}
			return false;
		}
		//入库接口
		public function orderSendStatus($oid){
			$url = C('API_SF').'order/querySaleOrderStatus';
			$arr=array(
				'erpOrder'=>$oid
			);
			$json=json_encode($arr);
			$res=$this->curl_post($url,$json);
			$res=stristr($res,"{");
			return $res;

		}

		//入库接口
		public function purchase($data,$order_id,$remarks){
			$url = C('API_SF').'order/purchase';
			$arr=array(
				'erpOrder'=>$order_id,
				'erpOrderType'=>"采购订单",
				'scheduledReceiptDate'=>date('Y-m-d H:i:s',strtotime("+3 day")),
				'sfOrderType'=>"采购入库"
			);
			$order=$arr;
			$inserts=array();
			$items=array();
			foreach($data as $key => $val){
				$items[]=$val;
			}
			$arr['items']=$items;
			$json=json_encode($arr);
			$res=$this->curl_post($url,$json);
			$res=stristr($res,"{");
			$arr=json_decode($res);
			foreach($data as $k => $v){
				$inserts[$k]=$v;
				$inserts[$k]['erpOrder']=$order_id;
				$inserts[$k]['erpOrderType']=$order['erpOrderType'];
				$inserts[$k]['scheduledReceiptDate']=$order['scheduledReceiptDate'];
				$inserts[$k]['sfOrderType']=$order['sfOrderType'];
				$inserts[$k]['return_msg']=json_encode($res);
				$inserts[$k]['msg']=$items->msg;
				$inserts[$k]['receiptId']=$items->data->receiptId;
				$inserts[$k]['qty']=$items[$k]['qty'];
				$inserts[$k]['note']=$remarks;
				$inserts[$k]['send_msg']=$json;
			}
			$this->makeLog("purchaseLog",$json);
			$this->makeLog("purchaseReturnLog",$res);
			$this->supplier_purchase->addAll($inserts);
			if($arr->errCode=="0"){
				return true;
			}
			return false;
		}
		//设置顺丰仓已发货状态
		public function setSended($order_id,$status){
			$data=array(
				'status'=>$status
			);
			if($this->supplier_order->where("order_id=$order_id")->save($data)){
				return true;
			}
			return false;
		}

		//保存顺风推送信息
		public function saveCallBack($data){
			$orders=array();
			$items=array();
			$inserts=array();
			foreach($data as $key => $val){
				$orders[$key]["closeDate"]=$val->closeDate;
				$orders[$key]["erpOrder"]=$val->erpOrder;
				$orders[$key]["erpOrderType"]=$val->erpOrderType;
				$orders[$key]["receiptId"]=$val->receiptId;
				$orders[$key]["status"]=$val->status;
				$orders[$key]["warehouseCode"]=$val->warehouseCode;
			}
			$id=$this->supplier_storage_order->addAll($orders);
			//$id=30;
			$store=array();
			foreach($data as $key => $val){
				$items[$key]=$data[$key]->items;

				foreach($items[$key] as $k => $v){
					$tmp=array();
					$tmp['actualQty']=$v->actualQty;
					$tmp['inventoryStatus']=$v->inventoryStatus	;
					if($v->inventoryStatus=="10"){
						//$store[$val->erpOrder]=array();
						$store[$val->erpOrder][$v->skuNo]=$v->actualQty;
					}
					$tmp['planQty']=$v->planQty;
					$tmp['receiptTime']=$v->receiptTime;
					$tmp['skuNo']=$v->skuNo;
					$tmp['storage_id']=$id;
					$inserts[]=$tmp;
				}
				$id++;
			}
			$res=$this->supplier_storage_items->addAll($inserts);
			if($res){
				foreach($store as $key => $val){
					$num=0;
					foreach($val as $k => $v){
						$num=$num+$v;

						$tmp=array(
							'store'=>array('exp',"store+".$v)
						);
						$this->sysitem_sku_store->where("sku_id=$k")->save($tmp);
						//echo $this->sysitem_sku_store->getLastSql();
					}
					$n=array(
						'store'=>array('exp',"store+".$num)
					);
//					echo $num;
					$this->sysitem_item_store->where("item_id=$key")->save($n);

				}
			}
			return $res;
		}

		public function setOrderStatus($list,$status){
			$ids=array();
			foreach ($list as $key => $val) {
				$ids[]=$val->erpOrder;
			}
			$data=array(
				"status"=>$status
			);
			$ids=implode(",",$ids);
			$res= $this->supplier_order->where("order_id in ('$ids')")->save($data);
//			echo $this->supplier_order->getLastSql();
			return $res;
		}

		public function getWarehouseName($list){
			if(empty($list)){
				return false;
			}
			$ids=array();
			foreach($list as $key => $val){
				$ids[]=$val['warehouse_id'];
			}
			$ids=implode(',',$ids);
			$res=$this->supplier_warehouse->where("warehouse_id in (".$ids.")")->select();
			$items=array();
			foreach($res as $key => $val){
				$items[$val['warehouse_id']]=$val;
			}
			return $items;
		}

		public function getWarehouse(){
			$res=$this->supplier_warehouse->select();
			var_dump($res);
		}

		//出库回调记录

		public function saleCallBack($val){
			$this->supplier_saleorder_history->add($val);
		}

		//设置发货单订单状态
		public function setSaleOrderStatus_New($list,$status,$items){
			$status_arr=array(
					'1400'=>'TRADE_CLOSED_BY_REFUND',//订单关闭
					'2300'=>'IN_STOCK',//备货中
					'2700'=>'IN_STOCK',//备货中
					'2900'=>'WAIT_BUYER_CONFIRM_GOODS',//已发货
					'3900'=>'TRADE_FINISHED'//已完成
			);
			$status=$status_arr[$status];

			//获取商品sku_id列表
			$skuIds=array();
			$skuNums=array();
			foreach($items as $key => $val){
				$skuIds[]=$val['skuNo'];
				$skuNums[$val['skuNo']]=$val['actualQty'];
			}
			if(empty($skuIds)){
				return false;
			}

			//得到 payment_id
			$keys = array_keys($list);
			$paymentId = $keys[0];
			if(empty($paymentId)){
				return false;
			}

			//通过 payment_id 获取支付过的订单号：tids
			$where=array(
					'payment_id'=>$paymentId
			);
			$orders=$this->ectools_trade_paybill->where($where)->select();
			if(!$orders){
				return false;
			}
			$tids=array();
			foreach($orders as $key => $val){
				$tids[]=$val['tid'];
			}

			// 获取通过顺丰发货的 tids, oids
			$where=array(
					'tid'=>array('in',$tids),
					'sku_id'=>array('in',$skuIds)
			);
			$orderList=$this->systrade_order->where($where)->select();
			$updateTids=array();
			$oids=array();
			foreach($orderList as $k => $v){
				if(!in_array($v['tid'], $updateTids)){
					$updateTids[]=$v['tid'];
				}
				$oids[]=$v['oid'];
			}

			//设置订单状态
			$data=array(
					'status'=>$status,
					'trade_status'=>$status
			);
			$where=array(
					'tid'=>array('in',$updateTids)
			);
			$res=$this->systrade_trade->where($where)->save($data);

			//置订单状态成功，开始设置子订单状态
			foreach($skuNums as $key=>$value) {
				$condition = array(
						'sku_id' => $key,
						'oid' => array('in', $oids),
				);
				$data = array(
						'send_num' => $value
				);
				$this->systrade_order->where($condition)->save($data);
			}

			//写发货单号
			foreach($updateTids as $key => $val){
				if($status=='WAIT_BUYER_CONFIRM_GOODS'){
					$where=array(
							//'tid'=>array('in', $val),
							'tid'=>$val,
							'sku_id'=>array('in',$skuIds)
					);
					$oskus=$this->systrade_order->where($where)->select();
					$oids=array();
					foreach($oskus as $k => $v){
						$oids[]=$v['oid'];
					}
					$pay=reset($list);
					if(!empty($oids)){
						if(!$this->sendGoodsDeal(2, $val, $pay['wayBillNo'], $oids)){
							return false;
						}
					}
				}
			}
			if($res>0||$res===0){
				return true;
			}
			return false;
		}

		//设置发货单订单状态
		public function setSaleOrderStatus($list,$status,$items){
			$status_arr=array(
				'1400'=>'TRADE_CLOSED_BY_REFUND',//订单关闭
				'2300'=>'IN_STOCK',//备货中
				'2700'=>'IN_STOCK',//备货中
				'2900'=>'WAIT_BUYER_CONFIRM_GOODS',//已发货
				'3900'=>'TRADE_FINISHED'//已完成
			);
			$status=$status_arr[$status];
			//获取商品sku
			$skuIds=array();
			$skuNums=array();
			foreach($items as $key => $val){
				$skuIds[]=$val['skuNo'];
				$skuNums[$val['skuNo']]=$val['actualQty'];
			}
			//获取sku列表的订单
			if(empty($skuIds)){
				return false;
			}
			//把payment_id转换成OrderId
			$ids=array();
			foreach($list as $key => $val){
				$ids[]=$key;
			}
			$where=array(
				'payment_id'=>array('in',$ids)
			);
			//通过payment_id 获取支付过的订单
			$orders=$this->ectools_trade_paybill->where($where)->select();
			if(!$orders){
				return false;
			}
			//通过订单号修改订单状态
			$ids=array();
			foreach($orders as $key => $val){
				$ids[]=$val['tid'];
			}
			//order表子订单where
			$where=array(
				'tid'=>array('in',$ids),
				'sku_id'=>array('in',$skuIds)
			);
			$orderList=$this->systrade_order->where($where)->select();
			$updateTid=array();
			$oids=array();
			foreach($orderList as $k => $v){
				$updateTid[]=$v['tid'];
				$oids[]=$v['oid'];
			}
			$data=array(
				'status'=>$status,
				'trade_status'=>$status
			);
			$where=array(
				'tid'=>array('in',$updateTid)
			);
			//设置订单状态
			$res=$this->systrade_trade->where($where)->save($data);
			//置订单状态成功，开始设置子订单状态
			foreach($skuNums as $key=>$value) {
				$condition = array(
					'sku_id' => $key,
					'oid' => array('in', $oids),
				);
				$data = array(
					'send_num' => $value
				);
				$this->systrade_order->where($condition)->save($data);
			}
			//写发货单号
			foreach($orders as $key => $val){
				if($status=='WAIT_BUYER_CONFIRM_GOODS'){
					$where=array(
						'tid'=>array('in',$updateTid),
						'sku_id'=>array('in',$skuIds)
					);
					$oskus=$this->systrade_order->where($where)->select();
					$oids=array();
					foreach($oskus as $k => $v){
						$oids[]=$v['oid'];
					} 
					$pay=reset($list);
					if(!empty($oids)){
						if(!$this->sendGoodsDeal(2,$val['tid'],$pay['wayBillNo'],$oids)){
							return false;
						}
					}
				}
			}
			if($res>0||$res===0){
				return true;
			}
			return false;
		}



		//通过payment_id获取tid
		public function getTidByPaymentId($pid){
			if($pid<0){
				return false;
			}
			$where=array(
				'payment_id'=>$pid
			);
			$res=$this->ectools_trade_paybill->where($where)->select();
			$arr=array();
			foreach($res as $key => $val){
				$arr[$val['payment_id']]=$val['tid'];
			}
			return $arr;
			
			
		}
		
		
		/*
		 * 发货处理
		 * logId仓库的ID
		 * tid订单ID
		 * logiNo快递单号
		 * oid trade_order ID
		 * admin
		 *
		 * */
		public function sendGoodsDeal($logId=2,$tid,$logiNo,$oids){
			$adminId=1;
			if(empty($oids)){
				return false;
			}
			if(!empty($logId) && !empty($logiNo) && !empty($tid)){
				//快递公司信息
				$expressCom=$this->dOrder->getThisExpressInfo($logId);

				$data=$this->dOrder->getThisOrderInfo($tid);
				
				$data['supplier_id']=$adminId;
				$data['logi_no']=$logiNo;
				$data['logi_id']=$expressCom['corp_id'];
				$data['logi_name']=$expressCom['corp_name'];
				$data['logi_code']=$expressCom['logi_no'];
				$data['seller_id']=$this->dOrder->getThisShopInfo($data['shop_id']);
				$data['t_begin']=time();
				$data['t_send']=time();
				$data['t_confirm']=time();
				$data['status']='succ';
				$data['delivery_id']=date('ymdHis').rand(1000000,9999999);
				//发货添加发货表
				$res=$this->dOrder->sendGoodsaddExpree($data);
				if($res){
					//发货添加发货详情表
					$orderInfo=$this->getOidsOrderInfo($oids,'oid,num,item_id,title');
					$SendData['status']='WAIT_BUYER_CONFIRM_GOODS';
					$SendData['consign_time']=time();
					$SendData['modified_time']=time();
					foreach($orderInfo as $key=>$value){
						//更改order表发货商品数量
						$SendData['sendnum']=$value['num'];
						$this->dOrder->editThisOrderInfo($value['oid'],$SendData,'sendnum,status,consign_time,modified_time');
						$itemIds[]=$value['item_id'];
						$sendOids[]=$value['oid'];
					}

					$itemInfos=$this->dOrder->getAllItem($itemIds,'jd_sku,item_id,bn');
					foreach($orderInfo as $key=>$value){
						foreach($itemInfos as $keys=>$values){
							if($value['item_id']==$values['item_id']){
								$orderInfo[$key]['sku_id']=$values['jd_sku'];
								$orderInfo[$key]['sku_bn']=$values['bn'];
							}
						}
						$orderInfo[$key]['number']=$value['num'];
						$orderInfo[$key]['sku_title']=$value['title'];
						$orderInfo[$key]['delivery_id']=$data['delivery_id'];
					}
					foreach($orderInfo as $key=>$value){
						$this->dOrder->addExpressDeatils($orderInfo[$key]);
					}
					//改变订单状态为待收货
					$nowstatus=$this->dOrder->getThisOrderInfo($tid,'status');
					if(in_array($nowstatus['status'], array('IN_STOCK','WAIT_SELLER_SEND_GOODS'))){
						//改变订单状态为待收货
						$dataStatus['status']='WAIT_BUYER_CONFIRM_GOODS';
						$orderRes=$this->dOrder->editTradeInfo($tid,$dataStatus);
					}
					//子订单状态改为待收货
					if(!empty($oids)){
						$condition['oid']=array('in',$oids);
					}else{
						$condition['tid']=$tid;
					}
					//写进管理员操作订单日志表
					$oidsString=implode(',',$oids);
					$dealType="发货";
					$remarks="配送快递：顺丰快递,快递单号：".$logiNo;
					$logData['tid']=$tid;
					$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
					if($res){
						return true;
					}
					return false;
				}
			}else{
				return false;
			}

		}
		public function getOidsOrderInfo($oids,$field){
			$condition['oid']=array('in',$oids);
			return $resu=$this->systrade_order->where($condition)->field($field)->select();		
		}	
		/*
         *
         * 写进管理员订单日志表
         * */
		public function markTradeLog($tid,$dealType,$remarks,$oidsString){
			//写进管理员操作日志表
			$logData['admin_username']="顺丰仓库";
			$logData['admin_userid']="1";
			$logData['deal_type']=$dealType;
			$logData['tid']=$tid;
			$logData['memo']=$remarks;
			$logData['oids']=$oidsString;
			$this->dOrder->markTradeLog($logData);
		}





    }
