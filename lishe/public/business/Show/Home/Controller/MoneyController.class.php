<?php
/**
  *---------------------------------------------------
  *  充值
  *---------------------------------------------------
 */
namespace Home\Controller;
class MoneyController extends CommonController {

	public function __construct(){
		parent::__construct();
		$this->modelDeposit = M('sysuser_user_deposit');
		 
	}

	public function chongMoney(){
		$cur_money_deposit = I('post.cur_money_deposit','','trim'); //应付积分
		$cur_money_deposit = intval($cur_money_deposit); 
		$user_id = I('post.user_id','','trim');     
        $deposit = $this->modelDeposit->field('deposit')->where('user_id='.$user_id)->find();//实际积分
        $balance = intval($deposit['deposit']*100);
        if($cur_money_deposit > $balance){
        	echo json_encode(array(1,$balance));			
        }else{
        	echo json_encode(array(100,$balance));
        }
	}
	 
}