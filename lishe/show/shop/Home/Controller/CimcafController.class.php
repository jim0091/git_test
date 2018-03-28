<?php
namespace Home\Controller;
class CimcafController extends CommonController {
	public function __construct() {
		parent::__construct();
	}
	
	//专题活动页面
	public function index(){
		$this->display('Activity/cimcaf/index');
	}
	
}