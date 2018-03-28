<?php  
namespace Home\Controller;
class AdminController extends CommonController {
/*
 * 管理员管理
 * 2016/9/20
 * zhangrui
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		if($this->roleId!=0 && (!in_array(ACTION_NAME,array('saveMemberEdit','memberEdit')))){
			header("Location:".__APP__."/Index/noPowerPage");
			exit;			
		}
		$this->dAdmin=D('Admin');
	}
/*
 * 权限管理页面
 * 
 */	
	public function permission(){
		$this->powerNodes();
		$this->display();
	}
/*
 *权限及节点查询后的内容 
 * */
	public function powerNodes(){
		$res=$this->dAdmin->getAllPowersPlate();
		$nodes=$this->dAdmin->getAllPowersNode();
		foreach($res as $key=>$value){
			foreach($nodes as $keys=>$values){
				if($value['power_id']==$values['power_id']){
					$res[$key]['node'][]=$values;
				}
			}
		}
		$this->assign('power',$res);		
	}
 	
/*
 * 
 * 添加权限板块
 */	
	public function addPowersPlate(){
		$powerId=I('powerId');
		if($powerId){
			$condition['power_id']=$powerId;
			$findRes=$this->dAdmin->findPowersPlate($condition);
			$this->assign('info',$findRes);
		}
		$this->display();
	}
/*
 *
 * 权限板块添加、编辑
 *  
 * */
	public function SavePlate(){
		$data=I('data');
		$powerId=I('powerId');
		if(!empty($data['name']) && !empty($data['controller'])){
				$data['controller']=strtolower(trim($data['controller']));
				$condition['controller']=$data['controller'];
				$condition['name']=$data['name'];
				$findRes=$this->dAdmin->findPowersPlate($condition);
				if(!$findRes){
					if($powerId){
						//编辑
						if($findRes['controller']!=$data['controller']){
							//修改节点表controller
							$this->dAdmin->editAllPowersNode($powerId,$data['controller']);
						}
						$data['modifyine_time']=time();
						$res=$this->dAdmin->editPowersPlate($powerId,$data);
						if($res){
							$this->redirect('Admin/addPowersPlate', array('powerId' => $powerId), 1, '修改成功,页面跳转中...');
						}else{
							$this->redirect('Admin/addPowersPlate', array('powerId' => $powerId), 1, '修改失败，请重试!...');
						}						
					}else{
						//添加
						$data['creat_time']=time();
						$res=$this->dAdmin->addPowersPlate($data);
						if($res){
							$this->redirect('Admin/addPowersPlate', '', 1, '添加成功,页面跳转中...');
						}else{
							$this->redirect('Admin/addPowersPlate', '', 1, '添加失败，请重试!...');
						}	
					}
				}else{
					$this->redirect('Admin/addPowersPlate', '', 1, '添加的板块名或控制器名已存在,页面跳转中...');
				}
		}else{
			$this->redirect('Admin/addPowersPlate', '', 1, '请输入完整信息,页面跳转中...');
		}
	}
/*
 * 
 * 添加权限节点
 */	
	public function addPowersNode(){
		$nodeId=I('nodeId');
		if($nodeId){
			$condition['node_id']=$nodeId;
			$findRes=$this->dAdmin->findPowersNode($condition);
			unset($condition);
			$condition['power_id']=$findRes['power_id'];
			$powerInfo=$this->dAdmin->findPowersPlate($condition);	
			$this->assign('info',$findRes);
			$this->assign('powers',$powerInfo);
		}
		$this->display();
	}	
/*
 *
 * 节点添加、编辑
 *  
 * */
	public function SavePowerNode(){
		$data=I('data');
		$nodeId=I('nodeId');
		if(!empty($data['name']) && !empty($data['action']) && !empty($data['power_id'])){
				$data['action']=strtolower(trim($data['action']));
				$condition['action']=$data['action'];
				$findRes=$this->dAdmin->findPowersNode($condition);
				if($findRes && $findRes['node_id']!=$nodeId){
					$this->error('该方法已存在方法,请重新输入另外的方法名,页面跳转中...');
					exit;
				}
				unset($condition);
				$condition['power_id']=$data['power_id'];
				$powerInfo=$this->dAdmin->findPowersPlate($condition);
				$data['controller']=$powerInfo['controller'];			
				$data['power_id']=$powerInfo['power_id'];			
				if($nodeId){
					//编辑
					$data['modifyine_time']=time();
					$res=$this->dAdmin->editPowersNode($nodeId,$data);
					if($res){
						$this->redirect('Admin/addPowersNode', array('nodeId' => $nodeId), 1, '修改成功,页面跳转中...');
					}else{
						$this->redirect('Admin/addPowersNode', array('nodeId' => $nodeId), 1, '修改失败，请重试!...');
					}						
				}else{
					//添加
					$data['creat_time']=time();
					$res=$this->dAdmin->addPowersNode($data);
					if($res){
						$this->redirect('Admin/addPowersNode', '', 1, '添加成功,页面跳转中...');
					}else{
						$this->redirect('Admin/addPowersNode', '', 1, '添加失败，请重试!...');
					}	
				}
		}else{
			$this->redirect('Admin/addPowersNode', '', 1, '请输入完整信息,页面跳转中...');
		}
	}	
/*
 * 取出所有板块信息
 * */
	public function getPower(){
		$res=$this->dAdmin->getAllPowersPlate();
		echo json_encode($res);
	}
/*
 * 
 * 删除节点
 **/
	public function dealThisNode(){
		$nodeId=I('get.nodeId');
		$powerId=I('get.powerId');
		$data['is_delete']=1;
		if($nodeId){
			$res=$this->dAdmin->editPowersNode($nodeId,$data);
		}else if($powerId){
			$res=$this->dAdmin->editPowersPlate($powerId,$data);			
		}
		echo json_encode($res);
	}
/*
 * 
 * 角色管理首页
 * */
	public function role(){
		$roleList=$this->dAdmin->getAllRole();
		$list=$this->dAdmin->getAllAdminMember();
		foreach($roleList as  $key=>$value){
			foreach($list as $keys=>$values){
				if($value['role_id']==$values['role_id']){
					$roleList[$key]['member'][]=$values['real_name'];
				}
			}
		}	
		//超级管理员
		$admin=$this->dAdmin->getSuprAdminInfo();
		$this->assign('admin',$admin);
		$this->assign('list',$roleList);
		$this->display();
	}
/*
 * 角色添加、编辑
 * */	
	public function roleAdd(){
		$roleId=I('roleId');
		if($roleId){
			$info=$this->dAdmin->getThisRoleInfo($roleId);
		}
		$this->assign('info',$info);
		$this->powerNodes();
		$this->display();
	}
/*
 * 角色添加编辑处理
 * */	
	public function roleDeal(){
		$data=I('data');
		$roleId=I('roleId');
		if(!empty($data['name'])){
			if(!empty($data['nodes'])){
				$data['node_ids']=implode(',', $data['nodes']);
			}else{
				$data['node_ids']=0;
			}
			if($roleId){
				//编辑
				$data['modifyine_time']=time();
				$res=$this->dAdmin->editThisRoleInfo($roleId,$data);
				if($res){
					$this->redirect('Admin/roleAdd', array('roleId'=>$roleId), 2, '修改成功,页面跳转中...');
				}else{
					$this->redirect('Admin/roleAdd',  array('roleId'=>$roleId), 2, '修改失败，请重试!...');
				}					
			}else{
				//添加
				$data['creat_time']=time();
				$res=$this->dAdmin->addRole($data);		
				if($res){
					$this->redirect('Admin/roleAdd', '', 2, '添加成功,页面跳转中...');
				}else{
					$this->redirect('Admin/roleAdd', '', 2, '添加失败，请重试!...');
				}						
			}
		}else{
			$this->redirect('Admin/roleAdd', '', 2, '角色名称未填写,页面跳转中...');
		}
		
	}
/*
 * 删除角色
 * */
	public function dealThisRole(){
		$roleId=I('roleId');
		if($roleId){
			$data['is_delete']=1;
			$res=$this->dAdmin->editThisRoleInfo($roleId,$data);
			echo json_encode($res);
		}
	}
/*
 * 管理员列表
 * */
	public function memberList(){
		$list=$this->dAdmin->getAllAdminMember();
		$roleList=$this->dAdmin->getAllRole();
		foreach($list as $key=>$value){
			foreach($roleList as  $keys=>$values){
				if($value['role_id']==$values['role_id']){
					$list[$key]['roleName']=$values['name'];
				}else if($value['role_id']==0){
					$list[$key]['roleName']="超级管理员";
				}
			}
		}
		$this->assign('list',$list);
		$this->display();
	}
/**
 * 添加管理员
 * */	
	public function memberAdd(){
		$uid=I('uid');
		if($uid){
			$info=$this->dAdmin->getThisAdminInfo($uid);
			$roleName=$this->dAdmin->getThisRoleInfo($info['role_id']);
			$this->assign('info',$info);
			$this->assign('roleName',$roleName['name']);
		}
		$this->display();
	}
/*
 * 添加管理员处理
 * */	
	public function memberEditDeal(){
		$data=I('data');
		$data['admin_username']=$data['mobile'];
		$data['pass']=trim($data['pass']);
		$data['passed']=trim($data['passed']);		
		if($data['pass'] != $data['passed']){
			$this->redirect('Admin/memberAdd', '', 3, '输入两次密码不一致！请重新输入...');
			exit;
		}
		$check=$this->dAdmin->adminLogin($data['mobile']);
		$uid=I('uid');
		if($uid){
			//修改
			if($check['admin_id']!=$uid){
				$this->redirect('Admin/memberAdd', '', 3, '该手机号已存在...');
				exit;
			}		
			$data['modifyine_time']=time();
			$res=$this->dAdmin->editThisAdminInfo($uid,$data);
			if($res){
				$this->redirect('Admin/memberAdd', array('uid'=>$uid), 2, '修改成功,页面跳转中...');
			}else{
				$this->redirect('Admin/memberAdd', array('uid'=>$uid), 2, '修改失败，请重试!...');
			}					
		}else{
			//添加
			if($check){
				$this->redirect('Admin/memberAdd', '', 3, '该手机号已存在...');
				exit;
			}			
			$data['salt']=randStr(6);
			$data['admin_password']=md5($data['pass'].$data['salt']);
			$data['created_time']=time();
			$res=$this->dAdmin->addAdminMember($data);
			if($res){
				$this->redirect('Admin/memberAdd', '', 2, '管理员添加成功,页面跳转中...');
			}else{
				$this->redirect('Admin/memberAdd', '', 2, '添加失败，请重试!...');
			}		
			
		}
	}
	
/*
 * 取出所有角色的信息
 * */	
	public function getAllRole(){
		$res=$this->dAdmin->getAllRole();
		echo json_encode($res);
	}
/*
 * 改变管理员的状态
 * */
	public function changeAdminStatus(){
		$uId=I('uId');
		$status=I('status');
		if($uId){
			$data['status']=$status;
			$res=$this->dAdmin->editThisAdminInfo($uId,$data);
			echo json_encode($res);
		}
		
	}
/**
 * 管理员信息、修改
 * */	
	public function memberEdit(){
		$adminId=$this->adminId;
		if($adminId){
			$info=$this->dAdmin->getThisAdminInfo($adminId,'admin_id,admin_username,real_name');
			$this->assign('info',$info);
		}
		$this->display();
	}
/**
 * 管理员信息、修改保存
 * */	
	public function saveMemberEdit(){
		$data=I('data');
		$uid=I('uid');
		$data['pass']=trim($data['pass']);
		$data['passed']=trim($data['passed']);
		$data['oldPass']=trim($data['oldPass']);
		if($data['pass'] != $data['passed']){
			$this->error('输入两次密码不一致！请重新输入...');
		}
		if($uid){
			//检测旧密码是否正确
			$info=$this->dAdmin->getThisAdminInfo($uid,'admin_password,salt');
			$password=md5($data['oldPass'].$info['salt']);
			if($info['admin_password']!=$password){
				$this->error('原密码输入不正确！！请重新输入...');
			}else if($data['pass']==$data['oldPass']){
				$this->error('新密码与旧密码相同！请重新输入...');
			}
			//修改
			$data['admin_password']=md5($data['pass'].$info['salt']);
			$data['modifyine_time']=time();
			$res=$this->dAdmin->editThisAdminInfo($uid,$data);
			if($res){
				session(null);
				cookie('adminAccount',null);
				header("Location:".__APP__."/Login");				
			}else{
				$this->error("信息修改失败!");
			}
		}
	}
	
}