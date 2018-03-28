<?php
/**
  +----------------------------------------------------------------------------------------
 *  AdminModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: AdminrModel.class.php v001 2015-10-27
 * zhangrui 2016/9/20
 * @description 管理后台数据库操作管理员功能部分
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
	
    class OrderModel extends CommonModel{
    	public function __construct(){
				$this->modelItem=M('sysitem_item');
				$this->modelItemStatus=M('sysitem_item_status');
				$this->modelItemSku=M('sysitem_sku');
				$this->modelItemStore=M('sysitem_sku_store');
				
				$this->modelTrade=M('systrade_trade');
				$this->modelJd=M('systrade_sync_trade');
				$this->modelOrder=M('systrade_order');
				$this->modelAccount=M('sysuser_account');
				$this->modelCompany=M('company_config');
				$this->modelShop=M('sysshop_shop');
				$this->modelShopSeller=M('sysshop_seller');
				$this->modelUser=M('sysuser_user');
				$this->modelCategory=M('syscategory_cat');
				
				$this->modelExpreeCom=M('syslogistics_dlycorp');
				$this->modelExpress=M('syslogistics_delivery');
				$this->modelExpressDetail=M('syslogistics_delivery_detail');
				
				$this->modelSingle=M('ectools_trade_paybill');
				$this->modelPayment=M('ectools_payments');
				
				$this->modelAtrade=M('company_activity_trade');
				$this->modelAorder=M('company_activity_order');
				$this->modelAInfo=M('company_activity_item');
				
				$this->modelTradeLog=M('system_admin_trade_log');	
    	}
			//取出符合的订单号
			public function getOrderItemsToNum($condition){
				$condition['send_type']= 2;
			  return $this->modelOrder->where($condition)->order('oid desc')->getField('tid',TRUE);
			}	
			//取出符合的订单号
			public function getConditionOrderTid($condition){
			  return $this->modelTrade->where($condition)->order('created_time desc')->getField('tid',TRUE);
			}			
			//订单	
			public function getOrder($condition,$limit){
				return M('')->table('systrade_trade t')->field('tid,com_id,shop_id,receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,receiver_mobile,created_time,payment,payed_fee,pay_time,status,trade_status,pay_type,order_status,refund_fee,buyer_message')->where($condition)->order('created_time desc')->limit($limit)->select();
			}		
			//订单中的所有商品
			public function getOrderItems($condition,$adminId){
				$condition['supplier_id']= $adminId;
				$condition['send_type']= 2;
				return $this->modelOrder->where($condition)->select();
			}				
			/**
			 * 管理员操作记录日志表
			 */
			public function markTradeLog($data) {
				$ip=get_client_ip ();
				$data['created_time']=time();
				$data['ip']=$ip;
				return M ( 'system_admin_trade_log' )->data ($data)->add ();
			}		
		//修改systrade_order表多条数据
			public function editThisConditionOrderInfo($condition,$data,$field){
				return $this->modelOrder->where($condition)->data($data)->field($field)->save();
			}		
		//修改systrade_trade表
			public function editTradeInfo($tid,$data){
				$condition['tid']=$tid;
				return $resu=$this->modelTrade->where($condition)->data($data)->save();		
			}			
		//取出单条systrade_trade表数据
			public function getThisOrderInfo($tid,$field){
				$condition['tid']=$tid;
				return $resu=$this->modelTrade->where($condition)->field($field)->find();		
				
			}			
		//取出指定systrade_order表数据
			public function getThisTradeInfo($tid,$adminId,$field){
				$condition['tid']=$tid;
				$condition['supplier_id']= $adminId;
				$condition['send_type']= 2;				
				return $resu=$this->modelOrder->where($condition)->field($field)->select();		
			}			
		//取出所有的快递
			public function getAllexpressInfo(){
				return $this->modelExpreeCom->field('corp_id,corp_name')->order('order_sort asc')->select();
			}
		//取出指定快递信息
		public function getThisExpressInfo($logId){
				return $this->modelExpreeCom->where('corp_id='.$logId)->field('corp_id,corp_name,corp_code')->find();
		}		
		//取出店铺的一些信息
			public function getThisShopInfo($shopId){
				return $this->modelShopSeller->where('shop_id='.$shopId)->getField('seller_id');
			}			
		//发货添加至发货表
			public function sendGoodsaddExpree($data){
				return $this->modelExpress->data($data)->add();
			} 	
	//取出指定范围的Oid  systrade_order表数据
		public function getOidsOrderInfo($oids,$adminId,$field){
			$condition['oid']=array('in',$oids);
			$condition['supplier_id']= $adminId;
			return $resu=$this->modelOrder->where($condition)->field($field)->select();		
		}				
	//修改systrade_order表单条数据
		public function editThisOrderInfo($oid,$data,$field){
			return $this->modelOrder->where('oid='.$oid)->data($data)->field($field)->save();
		}			
	//取出所所有商品
		public function getAllItem($itemIds,$field='item_id,cat_id,price,cost_price,barcode'){
			$condition['item_id']=array('in',$itemIds);
			return $this->modelItem->where($condition)->field($field)->select();
		}		
	//发货添加至发货详情表
		public function addExpressDeatils($data){
			return $this->modelExpressDetail->data($data)->add();
			
		}		
	//订单详情
	public function getOrderDetail($tid,$adminId){
		$res=$this->modelTrade->where('tid='.$tid)->find();
		$info=$this->modelOrder->where('tid='.$tid.' and supplier_id ='.$adminId.' and send_type=2')->select();
		$res['more']=$info;
		return $res;
	}		
	//取出该信息的物流信息
	public function getExpress($tid,$adminId){
		return $res=$this->modelExpress->where('tid='.$tid.' and supplier_id ='.$adminId)->field('delivery_id,post_fee,logi_name,logi_no,status,t_begin')->select();
	}		
//取出所所有商品sku下
	public function getAllSkuItem($skuIds,$field='sku_id,item_id,bn,barcode'){
		$condition['sku_id']=array('in',$skuIds);
		return $this->modelItemSku->where($condition)->field($field)->select();
	}						
    }
?>