<?php
/**
 +------------------------------------------------------------------------------
 * IndexController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: IndexController.class.php v001 2016-11-01
 * @description 本地接口封装
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	$catList = A('Gift')->getAllCat();
    	$this->assign('catList', $catList);
        $this->display('index');
    }
}