<?php  
namespace Home\Model;
use Think\Model;
class ItemsModel extends Model{
	/*
	 * awen
	 * addtime 20170316
	 * */
	 
	public function __construct(){
		$this->dbKeywords=M('sysitem_keywords');		
		
	}
	//获取商品关键字列表
	public function getKeywordsList($condition,$field,$limit){
		if (!$condition) {
			return false;
		}else{
			return $this->dbKeywords->where($condition)->field($field)->limit($limit)->select();
		}
	}	
}
