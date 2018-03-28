<?php
/**
  +------------------------------------------------------------------------------
 * ActivateController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: ActivateController.class.php v001 2016-5-22
 * @description 用户激活
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class ActivateController extends CommonController {
	public function index(){
		if(!empty($this->uid)){
			header("Location:/member-index.html");
		}
		$this->display();
	}
  
	//激活完成
	public function done(){
		$refer=I('get.refer');
		if(empty($refer)){
			$refer=__APP__."/Activity/moon";
		}
		$this->assign('refer',$refer);
		$this->display();
	}
}