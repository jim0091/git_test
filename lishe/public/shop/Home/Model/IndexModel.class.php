<?php   
namespace Home\Model;
use Think\Model;
class IndexModel extends Model{
	public function __construct(){
		$this->dbUserDeposit=M('sysuser_user_deposit');
		$this->dbCompanyConf = M('company_config');
        $this->dbShufflingDetail = M('mall_shuffling_figure_detail');
        $this->dbShuFigure = M("mall_shuffling_figure");
	}
	//获取用户积分
	public function getUserDeposit($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbUserDeposit->where($condition)->find();
		}
	}
	//获取集团名称
	public function getComGroup($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbCompanyConf->where($condition)->getField('com_group');
		}
	}
	//获取需要显示的图片类型
	public function getShuFigure($condition,$field="*"){
		if (!$condition) {
			return false;
		}else{
			return $this->dbShuFigure->where($condition)->getField($field);
		}		
	}
	//获取首页轮播图
	public function getShufflingDetail($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbShufflingDetail->where($condition)->order("order_sort desc")->select();
		}
	}
}
