<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[IndexService接口服务];					@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Index/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexService.class.php];	
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
namespace Index\Service;
use Think\Controller;
class IndexService extends Controller{
    public function index($data){
    	if($data==1){
    		$Info['status'] = 1;
    		$Info['message'] = "深圳礼舍科技欢迎你";
    		return $Info;
    	}else{
    		$Info['status'] = 0;
    		$Info['message'] = "欢迎回调深圳礼舍科技API接口";
    		return $Info;
    	}
    }
}