<?php
/**
 +------------------------------------------------------------------------------
 * EmptyController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: EmptyController.class.php v001 2016-11-12
 * @description 本地接口封装
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class EmptyController extends Controller {
    public function index(){
        $this->show('→_→ 404...');
    }
}