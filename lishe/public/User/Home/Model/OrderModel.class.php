<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	awen
 * @version  	$Id: OrderModel.class.php v001 2016-11-29
 * @description 订单操作
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class OrderModel extends CommonModel{
	public function __construct(){
        $this->modelTrade=M("systrade_trade");//订单表
        $this->modelOrder=M("systrade_order");//订单表子表
        $this->modelShop=M("sysshop_shop");//店铺表
        $this->modelAtrade = M('company_activity_trade');//活动订单表
        $this->modelAorder = M('company_activity_order');//活动订单子表
        $this->modelPayment = M('ectools_payments');//支付子表
        $this->modelTradePaybill = M('ectools_trade_paybill');//支付子表
        $this->modelAdminTradeLog = M('system_admin_trade_log');//订单操作记录
        $this->modelSiteArea = M('site_area');//地址级联
        $this->modelLogisticsDelivery = M('syslogistics_delivery');//订单物流信息
        $this->modelLogisticsDlycorp = M('syslogistics_dlycorp');//物流信息表
        $this->modelAftersales = M('systrade_aftersales');//退货回寄信息表
        $this->modelSkuStore = M('sysitem_sku_store');//库存表
	}
	 

    //获取用户订单
    public function getOrderList($condition,$limit){
        if (!$condition) {
            return false;
        }else{
            return $this->modelTrade->where($condition)->field('tid,shop_id,status,order_status,buyer_area,post_fee,payment,refund_fee,created_time,disabled,cash_fee,point_fee,payed_cash,payed_point')->limit($limit)->order('created_time desc')->select();
        }
    }
    //获取支付单的数量
    public function getPaymentCount($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelTradePaybill->where($condition)->count('payment_id');
        }
    }

    //获取用户活动订单
    public function getActivityOrderList($condition,$limit){
        if (!$condition) {
            return false;
        }else{
            return $this->modelAtrade->where($condition)->field('atid,status,order_status,buyer_area,post_fee,payment,refund_fee,creat_time')->limit($limit)->order('creat_time desc')->select();
        }
    }
    //获取用户活动订单的数量
    public function getActivityOrderCount($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelAtrade->where($condition)->count('atid');
        }
    }

    //根据店铺ids查询店铺信息
    public function getShopList($condition){
        if (empty($condition)) {
            return false;
        }else{
            return $this->modelShop->where($condition)->field('shop_id,shop_name,shop_logo,wangwang,shopuser_name,qq')->select();
        }       
    }
    //根据tids获取订单中的商品信息
    public function getOrderItemList($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelOrder->table('systrade_order o,sysitem_sku s')->where('o.sku_id = s.sku_id and '.$condition)->field('o.oid,o.tid,o.shop_id,o.title,o.price,o.num,o.total_fee,o.status,o.aftersales_status,o.pic_path,o.item_id,o.sendnum,s.spec_info')->select();
        }
    }
    //根据tids获取活动订单中的商品信息
    public function getActivityOrderItemList($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelAorder->where($condition)->select();
        }
    }

    //修改订单状态
    public function updateTrade($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{            
            return $this->modelTrade->where($condition)->data($data)->save();
        }       
    }
    //修改子订单状态
    public function updateOrder($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelOrder->where($condition)->data($data)->save();
        }       
    }
    //批量添加用户操作订单记录
    public function addTradeLog($data){
        if (!$data) {
            return false;
        }else{
            return $this->modelAdminTradeLog->addAll($data);
        }
    }
    //获取订单的支付子列表
    public function getPaymentBillList($condition,$limit){
        if (!$condition) {
            return false;
        }else{
            return $this->modelTradePaybill->where($condition)->field('tid,payment_id,status,created_time')->limit($limit)->order('created_time DESC')->select();
        }
    } 
    //获取活动订单的支付子列表
    public function getActivityPaymentBillList($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelTradePaybill->where($condition)->select();
        }
    } 
    //修改支付主表状态
    public function updatePaymentStatus($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelPayment->where($condition)->data($data)->save();
        }
    } 
    //修改支付副表状态
    public function updatePaymentBillStatus($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelTradePaybill->where($condition)->data($data)->save();
        }
    }

    //修改活动订单状态
    public function updateActivityTrade($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelAtrade->where($condition)->data($data)->save();
        }       
    }
    //修改活动子订单状态
    public function updateActivityOrder($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelAorder->where($condition)->data($data)->save();
        }       
    }
	//获取订单详情
    public function getOrderInfo($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelTrade->where($condition)->find();
        }
    }
    //判断如果已经存在支付的订单则无法取消订单
    public function checkTrade($condition){
        if (!$condition) {
            return false;
        }else{
            $resTrade = $this->modelTrade->where($condition)->field('payed_fee,pay_time,status')->select();
            $resurt = true;
            foreach ($resTrade as $key => $value) {
                if (!empty($value['pay_time']) && $value['status'] == 'WAIT_SELLER_SEND_GOODS') {
                    $resurt = false;
                    break;
                }
            }
            return $resurt;
        }
    }
    //获取地址级联
    public function getAreaNames($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelSiteArea->where($condition)->select();
        }
    }
    //获取物流信息
    public function getLogistics($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelLogisticsDelivery->table('syslogistics_delivery del,syslogistics_dlycorp dly')
                                                ->field('del.tid,del.logi_id,del.logi_no,dly.corp_id,dly.corp_name,dly.website')
                                                ->where('del.logi_id = dly.corp_id and '.$condition)
                                                ->select();

        }
    }
    //根据tid查询支付单号
    public function getPaymentId($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelTradePaybill->where($condition)->field('payment_id')->find();
        }
    }
    //查询快递列表
    public function getSyslogDlyList(){
        return $this->modelLogisticsDlycorp->field('corp_id,corp_code,corp_name')->select();
    }
    //查询回寄地址信息
    public function getAftersales($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelAftersales->where($condition)->field('shop_explanation')->find();   
        }
    } 
    //用户提交物流信息
    public function updateAftersales($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelAftersales->where($condition)->data($data)->save();
        }
    }   
    //查询对应oid
    public function getOrderOidList($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelOrder->where($condition)->select();
        }
    }
    //查询购买数量
    public function getOrderItemNums($condition,$field=""){
        if (!$condition) {
            return false;
        }else{
            return $this->modelOrder->where($condition)->field($field)->select();
        }
    }
    //取消单，还原库存
    public function updateSku($condition,$num){
        if (!$condition || !$num) {
            return false;
        }else{
            return $this->modelSkuStore->where($condition)->setDec('freez',$num); 
        }
    }
    //获取支付主表信息
    public function getPaymentList($condition,$field){
        if (!$condition) {
            return false;
        }else{
            return $this->modelPayment->where($condition)->field($field)->select();
        }
    }
    //获取支付详情
    public function getPaymentInfo($condition,$field){
        if (!$condition) {
            return false;
        }else{
            return $this->modelPayment->where($condition)->field($field)->find();
        }
    }
}  
?>  	