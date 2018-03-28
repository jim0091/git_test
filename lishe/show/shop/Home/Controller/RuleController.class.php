<?php   
namespace Home\Controller;
use Think\Controller;
class RuleController extends CommonController{
	//公共类
	public function __construct(){
		parent::__construct();
		
	}

	//超级管理员登录 赵尊杰 2016-09-01
	public function getPayType(){
		$comId=$this->comId;
		if($comId=='1467166836740'){
			return 'gd10086_ecard';
		}
	}
}
