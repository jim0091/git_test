<?php
namespace Home\Controller;
use Think\Controller;
class PreviewController extends Controller {
/*
 * 登录页预览
 * */	
	public function __construct(){
			parent::__construct();
	}
	//元旦1
	public function newyear(){
		$this->display();
	}
	//元旦2
	public function newyear2(){
		$this->display();
	}
	//春节1
	public function springfestival(){
		$this->display();
	}
	//春节2
	public function springfestival2(){
		$this->display();
	}
	//春节3
	public function springfestival3(){
		$this->display();
	}
  	//检测企业
  	public function checkComId($comId){
		switch($comId){
			case 1467166836740:
				$referUrl="/b.php/Gd10086";
				break;
			case 1466483633689:
				$referUrl="/b.php/Haihetao";
				break;
			case 1469444223094:
				$referUrl="/b.php/Cgn";
				break;										
			default:
				$referUrl=0;
		}
		return $referUrl;
  	}		
		
		
		
}