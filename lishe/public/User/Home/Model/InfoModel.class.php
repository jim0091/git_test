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
class InfoModel extends CommonModel{
	public function __construct(){
        $this->modelUserDeposit=M("sysuser_user_deposit");//订单表
        $this->modelUserAccount = M('sysuser_account');//用户登录表    
        $this->modelUserDeposit = M('sysuser_user_deposit');  
        $this->modelFeedBack = M('company_feedback');//意见反馈
        $this->modelUser = M('sysuser_user');//用户登录表   
        $this->modelCompany = M('company_config');//企业信息表
        $this->modelUserDeposit = M('sysuser_user_deposit');//用户积分表
        $this->modelDepositLog = M('sysuser_user_deposit_log');//积分变动表
        $this->modelItemHistory = M('sysitem_item_history');//浏览记录
        $this->modelItem = M('sysitem_item');//浏览记录
        $this->modelCategory=M('syscategory_cat');//商品分类表
        $this->dbCompanyConf = M('company_config');
	}
	

    //获取用户积分
    public function getUserDeposit($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelUserDeposit->where($condition)->field('user_id,balance')->find();
        }
    }
    //积分变动明细
    public function getDepositLog($condition,$limit){
        if (!$condition) {
            return false;
        }else{
            return $this->modelDepositLog->where($condition)->limit($limit)->order('logtime DESC')->select();
        }
    }
    //统计积分记录
    public function getDepositLogCount($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelDepositLog->where($condition)->count('log_id');
        }
    }
    //获取用户信息
    public function getAccountInfo($condition){
        if (!$condition) {
            return false;
        }else{
            return  $this->modelUserAccount->where($condition)->find();
        }
    }
    public function getUser($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelUser->where($condition)->find();
        }
    }
    //查找企业信息
    public function getCompanyInfo($condition,$field=""){
        if (!$condition) {
            return false;
        }else{
            return $this->modelCompany->where($condition)->field($field)->find();
        }
    }
    //查找用户积分
    public function getUserDepositInfo($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelUserDeposit->where($condition)->find();
        }
    }
    
    //设置支付密码
    public function updatePayPassword($condition,$data){
        if (!$condition || !$data) {
            return false;
        }else{
            return $this->modelUserDeposit->where($condition)->data($data)->save();
        }
    }
    //获取用户旧支付密码
	public function getPwd($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelUserDeposit->where($condition)->field('md5_password')->find();
        }
    }
    //获取用户意见反馈
    public function getFeedBack($condition,$limit){
        if (!$condition) {
            return false;
        }else{
            return $this->modelFeedBack->where($condition)->limit($limit)->select();
        }
    }
    //统计意见反馈
    public function getFeedBackCount($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelFeedBack->where($condition)->count('feedback_id');
        }
    }
    //获取用户意见反馈
    public function  getFeedBackInfo($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelFeedBack->where($condition)->find();
        }
    }
    //获取用户历史记录
    public function getHistoryList($condition,$field,$limit,$order){
        if (!$condition) {
            return false;
        }else{
            return $this->modelItemHistory->where($condition)->field($field)->limit($limit)->order($order)->select();
        }
    }
    //根据条件查询商品
    public function getItemList($condition,$field,$limit,$order){
        if (!$condition) {
            return false;
        }else{
            $itemList=$this->modelItem->table('sysitem_item a,sysitem_item_status b,sysitem_item_store s')->where('a.item_id=b.item_id and a.item_id = s.item_id and b.approve_status="onsale" and a.disabled=0 and s.store > 0'.$condition)
                  ->field('a.item_id,a.shop_id,title,price,image_default_id')->order($order)->limit($limit)->select();
            return $itemList;
        }
    }
    //根据catids查询分类名
    public function getCatName($condition,$field){
        if (!$condition) {
            return false;
        }else{
            return $this->modelCategory->where($condition)->field($field)->select();
        }
    }
    //查询用户信息
    public function getUserInfo($condition){
        if (!$condition) {
            return false;
        }else{
            return $this->modelUser->where($condition)->find();
        }
    }
    //查询企业配置利润率
    public function getCompanyConf($condition){
        if (!condition) {
            return false;
        }else{          
            return $this->dbCompanyConf->where($condition)->field('profit_rate')->find();
        }
    }
}  
?>  	