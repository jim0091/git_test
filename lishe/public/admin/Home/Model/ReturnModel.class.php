<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	zzw
 * @version  	$Id: ReturnModel.class.php v001 2017-6-19
 * @description 返现管理
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class ReturnModel extends CommonModel{
	public function __construct(){
		$this->dbReturn= M('systrade_return');
		$this->dbActivity = M('company_activity');
		$this->dbUser = M('sysuser_user');
		$this->dbAccount = M('sysuser_account');
		
	}	
	//统计数量
	public function getReturnCount($conditon){
		return $this->dbReturn->where($conditon)->count('return_id');
	}
	//查询列表
	public function getReturnList($conditon,$field,$limit){
		return $this->dbReturn->where($conditon)->field($field)->limit($limit)->select();
	}
	//查询活动
	public function getActivity($condition,$field){
		
		return $this->dbActivity->where($condition)->field($field)->select();
	}
	//会员查询
	public function getUser($condition,$field){
		if (!$condition) {
			return false;
		}
		return $this->dbUser->where($condition)->field($field)->select();
	}
	//返现查询
	public function getReturnInfo($condition,$field){
		if (!condition) {
			return false;
		}
		return $this->dbReturn->where($condition)->field($field)->find();
	}
	//会员资料查询
	public function getUserInfo($condition,$field){
		if (!$condition) {
			return false;
		}
		return $this->dbAccount->where($condition)->field($field)->find();
	}
	//更新返现信息
	public function updateReturn($condition,$data){
		if (!condition || !$data) {
			return false;
		}
		return $this->dbReturn->where($condition)->save($data);
	}
	
	
}  
?>  	