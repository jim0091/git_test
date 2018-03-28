<?php
/**
  +----------------------------------------------------------------------------------------
 *  AdminModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: AdminrModel.class.php v001 2015-10-27
 * zhangrui 2016/9/20
 * @description 管理后台数据库操作管理员功能部分
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
	
    class AdminModel extends CommonModel{
    	public function __construct(){
    		$this->modelAdmin=M('system_admin');
    		$this->modelAdminPowers=M('system_admin_powers');
    		$this->modelAdminRole=M('system_admin_role');
    		$this->modelAdminPowersNode=M('system_admin_powers_node');
    	}
    	
	    //登录
        public function adminLogin($userName){
        	$condition=array(
        		'admin_username'=>$userName
        	);       	

	        return $this->modelAdmin->field('admin_id,group_id,role_id,admin_password,salt,real_name,status')->where($condition)->find();
        }
    	
    	public function getAdminInfo($id){
    		$condition='businessId='.$businessId;
    		$business=$this->modelBusiness->field(self::MAIN_FIELD)->where($condition)->find();
    		$info=$this->modelBusinessInfo->field(self::MAIN_FIELD)->where($condition)->find();
    		return array_merge($business, $info);
    	}
    	
    	public function saveAdminInfo($businessId,$data){
    		$condition='businessId='.$businessId;
    		return $this->modelBusiness->where($condition)->save($data);
    	}
    	
    	public function changePass($adminId,$data){
    		$condition='adminId='.$adminId;
    		$admin=$this->modelBusiness->where($condition)->find();
    		if($admin['loginPass']!=md5($data['password'])){
				return -1;
			}
    		return $this->saveAdminInfo($adminId,array('loginPass'=>md5($data['newPassword'])));
    	}
			/*
			 * 添加权限板块
			 */
			public function addPowersPlate($data){
				return $this->modelAdminPowers->data($data)->add();
			}
			/**
			 * 修改模块版权信息
			 * */
			public function editPowersPlate($id,$data){
				return $this->modelAdminPowers->where('power_id='.$id)->data($data)->save();
			}
			/**
			 * 查找一条模块版权信息
			 * */
			public function findPowersPlate($condition){
				return $this->modelAdminPowers->where($condition)->find();
			}
			/**
			 * 查找所有模块版权信息
			 * */			
			public function getAllPowersPlate(){
				return $this->modelAdminPowers->where('is_delete=0')->select();
			}
			/**
			 * 查找一条权限节点信息
			 * */
			public function findPowersNode($condition){
				return $this->modelAdminPowersNode->where($condition)->find();
			}
			/**
			 * 查找指定条件的所有权限节点信息
			 * */
			public function getAllConditionPowersNode($condition,$field){
				$condition['is_delete']=0;
				return $this->modelAdminPowersNode->where($condition)->field($field)->select();
			}		
			/**
			 * 查找指的所有权限节点信息
			 * */
			public function getFieldAllPowersNode(){
				$condition['is_delete']=0;
				return $this->modelAdminPowersNode->where($condition)->getField('action',TRUE);
			}	
			/**
			 * 查找指的所有权限板块信息
			 * */
			public function getFieldAllPowers(){
				$condition['is_delete']=0;
				return $this->modelAdminPowers->where($condition)->getField('controller',TRUE);
			}								
			/**
			 * 修改节点信息
			 * */
			public function editPowersNode($id,$data){
				return $this->modelAdminPowersNode->where('node_id='.$id)->data($data)->save();
			}		
			/**
			 * 修改节点信息指定条件
			 * */
			public function editAllPowersNode($powerId,$contro){
				$data['controller']=$contro;
				return $this->modelAdminPowersNode->where('power_id='.$powerId)->data($data)->save();
			}					
			/*
			 * 添加权限节点
			 */
			public function addPowersNode($data){
				return $this->modelAdminPowersNode->data($data)->add();
			}			
			/**
			 * 查找所有权限节点信息
			 * */			
			public function getAllPowersNode(){
				return $this->modelAdminPowersNode->where('is_delete=0')->select();
			}
			/*
			 * 添加角色
			 */
			public function addRole($data){
				return $this->modelAdminRole->data($data)->add();
			}			
			/*
			 * 角色列表
			 * */
			public function getAllRole(){
				return $this->modelAdminRole->where('is_delete=0')->select();
				
			}
			/*
			 * 查找一条角色信息
			 * */
			public function getThisRoleInfo($roleId,$field){
				return $this->modelAdminRole->where('role_id='.$roleId)->field($field)->find();
			}
			/*
			 * 编辑角色
			 * */
			public function editThisRoleInfo($roleId,$data){
				return $this->modelAdminRole->where('role_id='.$roleId)->data($data)->save();
			}
			/*
			 * 添加管理员
			 * */
			public function addAdminMember($data){
				return $this->modelAdmin->data($data)->add();
			}
			/*
			 * 管理员列表
			 * **/												
			public function getAllAdminMember(){
				return $this->modelAdmin->select();
			}
			/*
			 * 编辑管理员信息
			 * */
			public function editThisAdminInfo($uId,$data){
				return $this->modelAdmin->where('admin_id ='.$uId)->data($data)->save();
			}
			/*
			 * 查找一条管理员信息
			 * */
			public function getThisAdminInfo($uId,$field){
				return $this->modelAdmin->where('admin_id='.$uId)->field($field)->find();
				
			}
			/*
			 * 取出所有超级管理员信息
			 * */
			public function getSuprAdminInfo(){
				return $this->modelAdmin->where('role_id=0')->select();
			}
			
    }
?>