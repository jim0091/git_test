<?php
/**
  +------------------------------------------------------------------------------
 * AreaController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰<10199720@qq.com>
 * @version  	$Id: AreaController.class.php v001 2016-06-01
 * @description 配送地址功能
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class AreaController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelArea=M('site_area');
		$this->modelUserAddr=M('sysuser_user_addrs');
	}
  
	//获取特定地区的下级数据
	public function getSubArea(){
		$parentId = I('get.parentId');
		$level = I('get.level')+1;
		$condition['jd_pid']=$parentId;
		$area=$this->modelArea->field('name,jd_id,level')->where($condition)->select();
		$areaStr='<select onchange="selectArea(\''.$level.'\');" id="areaLevel_'.$level.'"><option value="0">-请选择-</option>';
		if(!empty($area)){
			foreach($area as $key=>$value){
				if(intval($value['jd_id'])===$selected){
					$select=' selected="selected"';
				}else{
					$select='';
				}
				$areaStr.='<option data="'.$value['name'].'" value="'.$value['jd_id'].'"'.$select.'>'.$value['name'].'</option>';
			}
			echo $areaStr;
		}else{
			echo NULL;
		}		
	}
	
	public function saveAddr(){
		$areaJson=trim($_POST['areaJson']);
		$addrId=I('post.addr_id');
		$isDefault=I('post.def_addr');
		$area = json_decode($areaJson,true);
		foreach($area[0] as $key=>$value){
			$areaId[]=$key;
			$areaName[]=$value;
		}
		
		$data=array(
			'user_id'=>I('post.userId'),
			'name'=>I('post.name'),
			'area'=>implode('/',$areaName).':'.implode('/',$areaId),
			'addr'=>I('post.addr'),
			'zip'=>I('post.zip'),
			'tel'=>I('post.tel'),
			'mobile'=>I('post.mobile'),
			'def_addr'=>$isDefault
		);
		if($isDefault==1){
			$this->modelUserAddr->where('user_id='.$data['user_id'])->save(array('def_addr'=>0));
		}
		if(!empty($addrId)){
			echo $this->modelUserAddr->where('addr_id='.$addrId)->save($data);
		}else{
			echo $this->modelUserAddr->add($data);
		}
	}
	
	public function format(){
		$area=$this->modelArea->field('name,jd_id,jd_pid,level')->where('level<3')->order('jd_id ASC')->select();
		foreach($area as $key=>$val){
			$areas[$val['jd_id']]=$val;
			$sarea[$val['jd_id']]=array(
									'id'=>$val['jd_id'],
									'value'=>$val['name'],
									'parentId'=>$val['parent_id']
								);
			if($val['level']!=1){
				$parea[$val['parent_id']][]=array(
					'id'=>$val['jd_id'],
					'value'=>$val['name'],
					'parentId'=>$val['parent_id']
				);
			}
			
		}
		foreach($sarea as $key=>$val){
			if($areas[$val['id']]['level']>0){
				$sarea[$key]['children']=$parea[$val['id']];
			}
		}
		foreach($sarea as $key=>$val){
			if($val['parentId']==1 and !empty($val['children'])){
				$areass[]=$val;
			}
		}
		print_r($areass);
		file_put_contents('area.json',json_encode($areas));
		//print_r(json_encode($areass));
	}
	
}
?>