<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	public function __construct(){
		parent::__construct();
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		
		$userName=cookie('userName');
		$comId=cookie('comId');
		if(empty($userName) or empty($comId)){
			$companyId=I('get.comid');
			$sign=I('get.sign');
			$userName=I('get.lgn');
			if(!empty($companyId) && !empty($sign) && !empty($userName)){
				if(md5($companyId.$userName.C('API_KEY'))!=$sign){
					header("Location:http://v.lishe.cn/company/login.html");
					exit;	
				}else{
					cookie('comId',$companyId);
					cookie('userName',$userName);
				}		
			}else{
				header("Location:http://v.lishe.cn/company/login.html");
				exit;	
			}			
		}
		
		$this->comId=$comId;
		$this->assign('root',C('ROOT')); 
		$this->assign('userName',$userName);
		$this->assign('control',$control);
		$this->assign('action',$action);
	}
}