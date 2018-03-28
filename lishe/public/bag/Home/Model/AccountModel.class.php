<?php  
namespace Home\Model;
use Think\Model;

class AccountModel extends Model{
	
	private $sysuserUser;
	private $sysuserUserDeposit;
	
	public function __construct(){
		$this->tablePrefix = 'sysuser_';
		parent::__construct();
		$this->sysuserUser 	  = M('sysuser_user');
		$this->sysuserUserDeposit = M('sysuser_user_deposit');
	}
	
	/**
	 * 根据手机号获取userId
	 * @param unknown $mobile
	 */
	public function getUserId($mobile, $comId, $empName){
		if(empty($mobile) || empty($comId) || empty($empName)){
			return false;
		}
		$where = array();
		$where['mobile'] = $mobile;
		$userId = $this->where($where)->getField('user_id');
		if(!empty($userId)){
			return $userId;
		}
		try {
			//不存在，开始注册用户
			return $this->registerAccount($mobile, $comId, $empName);
		}catch (\Exception $e){
			$msg = "系统错误，获取用户错误 ";
			self::log("msg:".$e->getMessage());
			$this->retError('1018', $msg);
		}
		
		return $userId;
	}
	
	/**
	 * 注册本地用户
	 * @param unknown $mobile
	 */
	public function registerAccount($mobile, $comId, $empName){
		$data = array(
				'login_account'	=>$mobile,
				'mobile'		=>$mobile,
				'login_password'=>'activate'
		);
		$userId = $this->add($data);
		if(!$userId){
			return false;
		}
		//调用请求接口
		$data = array(
				'user_id'	=>$userId,
				'name'		=>$empName,
				'username'	=>$empName,
				'regtime'	=>time(),
		);
		$this->sysuserUser->add($data);
		$data = array(
				'user_id'	=>$userId,
				'comId'		=>$comId,
		);
		$this->sysuserUserDeposit->add($data);
		return $userId;
	}
}
