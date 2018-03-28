<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[Index模块接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Index/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Index\Controller;
use Common\Controller\CommonController;
use Think\Controller;
class IndexController extends CommonController {
	
    public function index(){
       $Service = new \Index\Service\IndexService();
       $parameter = 1;
       $result = $Service->index($parameter);
//        var_dump($result);
//        var_dump($result['status']);exit;
       $this->retSuccess($result);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}