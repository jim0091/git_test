<?php 
namespace Home\Controller;
class IndexController extends CommonController{
/*
 * 2016/8/22
 *	zhangrui 
 * 
 * */
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
	}
	/*
	 * 首页
	 * */
	public function index(){
		$this->display();
	}
	/*
	 *无权限跳转页 
	 * */
	public function noPowerPage(){
		
		
		
		$this->display();
	}
	
	/*
	 * 结果展示页
	 * */
	public function resultShow(){
		
		
		$this->display();
		
	}
	
}
