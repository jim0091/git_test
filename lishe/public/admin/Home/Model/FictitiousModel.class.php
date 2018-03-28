<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: OrderModel.class.php v001 2016-08-29
 * @description 订单操作
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class FictitiousModel extends CommonModel{
	public function __construct(){
		$this->modelItem=M('sysitem_item');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelTradeVr=M('systrade_trade_vr');
		$this->modelJd=M('systrade_sync_trade');
		$this->modelOrderVr=M('systrade_order_vr');
		$this->modelAccount=M('sysuser_account');
		$this->modelCompany=M('company_config');
		$this->modelShop=M('sysshop_shop');
		$this->modelUser=M('sysuser_user');
		$this->modelCategory=M('syscategory_cat');
		$this->modelExpress=M('syslogistics_delivery');
		$this->modelSingle=M('ectools_trade_paybill');
		$this->modelPayment=M('ectools_payments');
		
		
		$this->mdodelAtrade=M('company_activity_trade');
		$this->mdodelAorder=M('company_activity_order');
		$this->modelAInfo=M('company_activity_item');
		
	}	
	//订单数量	
	public function getOrderCount($condition){
		return $res=M('')->table('systrade_trade_vr t')->where($condition)->count();
	}
	//订单	
	public function getOrder($condition,$limit){
		return $res=M('')->table('systrade_trade_vr t')->field('vtid,tid,com_id,shop_id,receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,receiver_mobile,created_time,payment,payed_fee,pay_time,status,pay_type,order_status,refund_fee')->where($condition)->order('created_time desc')->limit($limit)->select();
	}
	//订单-》导出execl
	public function getOrderExecl($condition){
		$condition['_string']='o.tid=t.tid';
		return $res=M('')->table('systrade_order_vr o,systrade_trade_vr t')->where($condition)->field('t.vtid,t.tid,t.user_id,o.item_id,o.title,o.spec_nature_info,o.price,o.num,t.com_id,t.shop_id,t.receiver_name,t.receiver_state,t.receiver_city,t.receiver_district,t.receiver_address,t.receiver_mobile,t.created_time,o.consign_time,t.payment,t.payed_fee,t.pay_time,t.status')->select();
	}
	//订单-》京东订单号订单
	public function getJdOrderNumber($tids){
		$condition['tid']= array('in',$tids);
		return $res=$this->modelJd->field('tid,sync_order_id')->where($condition)->select();
	}		
	//订单中的所有商品
	public function getOrderItems($tids){
		$condition['tid']= array('in',$tids);
//		$condition['disabled']= 0;
		return $res=$this->modelOrderVr->field('oid,tid,item_id,title,spec_nature_info,price,num,aftersales_status,refund_fee,aftersales_num,disabled')->where($condition)->select();
	}	
	//下单者所属公司
	public function getThisCompany($comIds){
		$condition['tid']= array('in',$comIds);
		return $res=$this->modelCompany->where($condition)->field('com_id,com_name')->select();		
	}
	//所有公司
	public function getAllCompany(){
		$condition['is_delete']=0;
		return $res=$this->modelCompany->where($condition)->field('com_id,com_name')->select();
	}
	//所有店铺
	public function getAllShop(){
		return $res=$this->modelShop->where($condition)->field('shop_id,shop_name,shop_descript,shop_type,open_time,shop_area,shop_addr,shopuser_name')->find();
	}
	//通过员工手机号取得员工id
	public function getUserId($phone){
		$condition['mobile']=$phone;
		return $res=$this->modelAccount->where($condition)->getField('user_id',true);
	}
	//通过模糊查询取出符合的订单号
	public function getOrderItemsToNum($condition){
		return $res=$this->modelOrderVr->where($condition)->getField('tid',true);
	}	
	//取出一定分类下的所有商品id
	public function getAllCartItenIds($cartIds){
		$condition['cat_id']=array('in',$cartIds);
		return $res=$this->modelItem->where($condition)->getField('item_id',true);
		
	}	
	//订单详情
	public function getOrderDetail($tid){
		$res=$this->modelTradeVr->where('tid='.$tid)->find();
		$info=$this->modelOrderVr->where('tid='.$tid)->select();
		$res['more']=$info;
		return $res;
	}
	//店铺信息
	public function getShopInfo($shopIds){
		$condition['shop_id']=$shopIds;
		return $res=$this->modelShop->where($condition)->field('shop_id,shop_name,shop_descript,shop_type,open_time,shop_area,shop_addr,shopuser_name')->find();
	}
	//订单-》京东订单号订单
	public function getThisJdOrderNumber($tid){
		$condition['tid']= $tid;
		return $res=$this->modelJd->where($condition)->getField('sync_order_id');
	}	
	//用户所属公司
	public function getUserCompany($comId){
		$condition['com_id']=$comId;
		return $res=$this->modelCompany->where($condition)->getField('com_name');
	}	
	//用户信息
	public function getUserInfo($userId){
		return $res=M('')->table('sysuser_user a,sysuser_account b')->where('a.user_id = b.user_id and a.user_id='.$userId)->field('a.name,a.birthday,a.userName,a.sex,a.reg_ip,a.regtime,a.source,b.mobile')->find();
	}
	//取出相应分类的id和名字
	public function getThisCategoryInfo($catId,$level){
		return $res=$this->modelCategory->where('cat_id='.$catId.' and level='.$level)->field('cat_id,cat_name')->find();
	}
	//取出一定范围内分类id的名字
	public function getInCategoryInfo($catIds){
		$condition['cat_id']=array('in',$catIds);
		return $res=$this->modelCategory->where($condition)->field('cat_id,cat_name')->select();
	}	
	//取出该信息的物流信息
	public function getExpress($tid){
		return $res=$this->modelExpress->where('tid='.$tid)->field('delivery_id,post_fee,logi_name,logi_no,status,t_begin')->find();
	}
	//取出指定部分订单号的物流信息
	public function getInExpress($tids){
		$condition['tid']=array('in',$tids);
		return $res=$this->modelExpress->where($condition)->field('tid,delivery_id,post_fee,logi_name,logi_no,status,t_begin')->select();
	}	
	
	//取出相应条件的用户名
	public function getThiUserInfo($userIds){
		$condition['user_id']=array('in',$userIds);
		return $res=$this->modelAccount->where($condition)->field('user_id,mobile')->select();
	}
	//查合单中的所有订单号
	public function getSingleOrderNums($tid){
		$paymentId=$this->mdodelSingle->where('tid='.$tid)->getField('payment_id');
		if($paymentId){
			$tids=$this->mdodelSingle->where('payment_id='.$paymentId)->field('status,payment_id,tid')->select();
		}
		return $tids;
	}
	//订单详情
	public function getOrderDetails($tids){
		$condition['tid']=array('in',$tids);
		$res=$this->modelTradeVr->where($condition)->select();
		$info=$this->modelOrderVr->where($condition)->field('tid,item_id,title,spec_nature_info,price,num,sendnum,total_fee,aftersales_status,aftersales_num')->select();
		foreach($res as $key=>$value){
			foreach($info as $keys=>$values){
					if($value['tid']==$values['tid']){
						$res[$key]['more'][]=$values;
					}
			}
		}
		return $res;
	}	
	//查合单中的所有订单号
	public function getTradePaymentId($tid){
		$bill=$this->modelSingle->where('tid='.$tid)->getField('payment_id',TRUE);
		if(!empty($bill)){
			foreach($bill as $key=>$value){
				$tids[]=$value;
			}
			$condition=array(
				'status'=>'succ',
				'payment_id'=>array('in',implode(',',$tids))
			);
			return $this->modelPayment->where($condition)->getField('payment_id');
		}else{
			return NULL;
		}
	}
	
	//查询活动订单 赵尊杰 2016-09-03
	public function getActivityTradeList($condition,$page=1,$pageSize=0){
    	$start=$this->pageStart($page,$pageSize);
    	$count=$this->mdodelAtrade->field('orderId')->where($condition)->count();
    	$order=$this->mdodelAtrade->where($condition)->order('creat_time DESC')->limit($start.','.$pageSize)->select();
    	return array('count'=>$count,'list'=>$order);
    }
	public function getActivityOrder($condition,$limit){
		return $this->mdodelAtrade->where($condition)->limit($limit)->order('creat_time DESC')->select();
	}
	public function getAcitityOrderCount($condition){
		return $this->mdodelAtrade->where($condition)->count();
	}
	//所有套餐信息
	public function getActivityInfo(){
		return $this->modelAInfo->field('aitem_id,market_price')->select();
	}
	//取出活动订单详情
	public function getThisAOrderDetail($tid){
		$condition['atid']=$tid;
		return M('')->table('company_activity_trade t,company_activity_order o')->where('t.atid="'.$tid.'" and t.atid=o.atid')->field('t.*,o.consign_time,o.shipping_type,o.logistics_company,o.invoice_no,o.refund_fee')->find();
	}
	//取出订单的套餐信息
	public function getThisActivityInfo($aid){
		return $this->modelAInfo->where('aitem_id='.$aid)->find();
	}
//同步京东订单前取消同步指定商品 设置disabled=1	
	public function cancelThisGoods($oid){
		return $this->modelOrderVr->where('oid='.$oid)->setField('disabled',1);
	}
//查询京东sku
	public function getItemJdsku($itemIds){
		$condition['item_id']=array('in',$itemIds);
		return $this->modelItem->where($condition)->field('item_id,jd_sku')->select();
	}	
//申请退款等退款/退货/换货/维修
	public function editOrderStatus($tid,$type='REFUND',$status='WAIT_PROCESS'){
		 $res=$this->modelTradeVr->where('tid='.$tid)->setField('order_status',$type);
		 $data['aftersales_status']=$status;
		 $resu=$this->modelOrderVr->where('tid='.$tid)->field('aftersales_status')->data($data)->save();
		 if($res && $resu){
		 	return $res;
		 }
	}
//取出所所有商品
	public function getAllItem($itemIds){
		$condition['item_id']=array('in',$itemIds);
		return $this->modelItem->where($condition)->field('item_id,cat_id,price,cost_price')->select();
	}
//取出单条systrade_trade_vr表数据
	public function getThisOrderInfo($tid,$field){
		$condition['tid']=$tid;
		return $resu=$this->modelTradeVr->where($condition)->field($field)->find();		
	}	
//添加systrade_trade_vr表数据
	public function addTradeVrInfo($data){
		return $this->modelTradeVr->data($data)->add();		
	}		
//添加systrade_order_vr表数据
	public function addOrderVrInfo($data){
		return $this->modelOrderVr->data($data)->add();		
	}		
//修改systrade_order表多条数据
	public function editThisConditionOrderInfo($condition,$data,$field){
		return $this->modelOrderVr->where($condition)->data($data)->field($field)->save();
	}	
//order主单该数据
	public function editOrderStatusData($tid,$datas){
		 $condition['tid']=$tid;
		 return $this->modelTradeVr->where($condition)->data($datas)->save();
	}	
}  
?>  	