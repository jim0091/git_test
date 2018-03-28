<?php
/**
  +------------------------------------------------------------------------------
 * SignController
  +------------------------------------------------------------------------------
 * @author   	awen
 * @version  	$Id: SignController.class.php v001 2017-3-13
 * @description 供应商入住首页
  +------------------------------------------------------------------------------
 */  
namespace Home\Controller;
class SupplierController extends CommonController{
	public function __construct(){
		parent::__construct();		
	}
	
	public function index(){
		$this->display();
	}

	//发送邮件
	public function addMessage(){ 
		$title = trim(I('title'));
		$content = trim(I("content"));
		if(empty($title) || empty($content)) {
			echo json_encode(array(0,"请填写邮件标题和邮件内容！"));
			exit();
		}
	    if(sendMail($title,$content)){
	        echo json_encode(array(1,"发送成功！"));
			exit();
	    }else{
	        echo json_encode(array(0,"发送失败！"));
			exit();
        }
 	}

}
