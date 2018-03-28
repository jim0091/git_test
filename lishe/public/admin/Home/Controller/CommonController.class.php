<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	public function __construct(){
		parent::__construct();
		
		$accountSession=session('adminAccount');
		if(!empty($accountSession)){
			$account=$accountSession;
		}
		$accountCookie=cookie('adminAccount');
		if(empty($accountSession) && !empty($accountCookie)){
			$account=json_decode($accountCookie,true);
		}
		
		if(!empty($account['uid']) and !empty($account['userName']) and !empty($account['realName'])){
			$this->adminId=$account['uid'];
			$this->adminName=$account['userName'];
			$this->realName=$account['realName'];
			$this->roleId=$account['role_id'];
		}else{
			session(null);
			cookie(null);
			$this->adminId=0;
			$this->adminName='';
			$this->realName='';
		}		
		
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		if((empty($this->adminId) or empty($this->adminName)) and $control!='login' and $control!='jdSendGoods'){
			header("Location:".__APP__."/Login");
			exit;
		}
		//角色权限  begin
		 $allNodeAction=D('Admin')->getFieldAllPowersNode();
		 if($this->roleId!=0){
			 $nowController=strtolower(CONTROLLER_NAME);  //当前控制器
			 $nowaction=strtolower(ACTION_NAME);		//当前方法
		 	 $nodeInfo=D('Admin')->getThisRoleInfo($this->roleId,'node_ids');
			 //获取所有设权限的方法
			 $allNodeController=D('Admin')->getFieldAllPowers();
			 array_unique($allNodeController);
			 //该角色拥有的权限
			 $condition['node_id']=array('in',$nodeInfo['node_ids']);
			 $powerInfo=D('Admin')->getAllConditionPowersNode($condition,'node_id,action,controller');
			 foreach($powerInfo as $key=>$value){
			 	$nodeAction[$value['node_id']]=$value['action'];
			 	$nodeController[$value['node_id']]=$value['controller'];
			 }
			 //---所拥有权限访问的控制器或方法
			 $this->assign('nodeController',$nodeController);
			 //---	
			 if(in_array($nowaction, $allNodeAction) && !in_array($nowaction, $nodeAction)){
				header("Location:".__APP__."/Index/noPowerPage");
				exit;					 	
			 } 
		 }else if($this->roleId==0){
		 	$nodeAction=$allNodeAction;
		 }
		 $this->assign('nodeAction',$nodeAction);
		 $this->nodeAction=$nodeAction;
		//角色权限 end
		$this->assign('root',C('ROOT'));
		$this->assign('uid',$this->adminId); 
		$this->assign('userName',$this->adminName);
		$this->assign('realName',$this->realName);
		$this->assign('roleId',$this->roleId);
		$this->assign('control',$control);
		$this->assign('action',$action);
		$siteCfg=C('SITE_CFG');
		$this->assign('site',$siteCfg);
		$this->assign('siteTitle',$siteCfg['title']);
	}
	
	//模拟提交
	public function requestPost($url='', $data=array()) {
        if(empty($url) || empty($data)){
            return false;
        }
        $o="";
        foreach($data as $k=>$v){
            $o.="$k=".$v."&";
        }
        $param=substr($o,0,-1);
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
	public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}  
	
	//权限判断
	public function checkPowerNode($nodes){
		$roleId=$this->roleId;
		$nodeAction=$this->nodeAction;
	 	$applFundId=array_search($nodes, $nodeAction);
		 if($applFundId || $roleId==0){
		 	return true;
		 }else{
		 	return false;
		 }	
	}  
    public function requestJdPost($url='', $data=''){
    	if(empty($url) || empty($data)){
    		return false;
    	}
    	$ch=curl_init();//初始化curl
    	curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
    	curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    	curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
    	curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
    	$return=curl_exec($ch);//运行curl
    	curl_close($ch);
    	return $return;
    }


    
}