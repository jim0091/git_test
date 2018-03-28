<?php   
namespace Home\Model;
use Think\Model;
class TempSetModel extends Model{
	/*author :章锐
	 * 企业秀部分
	 * */	
	public function __construct(){
		$this->modelTemp=M('company_templete');
		$this->modelComfig=M('company_config');
	}	 	
	public function getTempCount($condition){
		return	$res=$this->modelTemp->where($condition)->count();
	}
	public function getTempInfo($condition,$field,$limit,$order){
		$res=$this->modelTemp->where($condition)->field($field)->limit($limit)->order($order)->select();
		return $res;
	}
	//公司所用模板
	public function comTemp($comId){
		$condition['is_delete']=0;
		$condition['com_id']=$comId;
		return $res=$this->modelComfig->where($condition)->field('templete')->find();
	}
	//获取模板名
	public function getTempName($tempId){
		$condition['temp_id']=$tempId;
		$condition['is_delete']=0;
		return $res=$this->modelTemp->where($condition)->getField('temp_name');
	}
	
	
	
}
