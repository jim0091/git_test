<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[Home模块接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[CommonApi/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexService.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-2-27 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace CommonApi\Service;
use Think\Controller;
class IndexService extends Controller {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
    public function index(){
    	return false;
    }
    
    //获取排序数字
    public function getSortNumber($com_id){
    	if(empty($com_id)){
    		return false;
    	}else{
	    	$ModelObj = D('BusinesscircleBannerConfig');
	    	$where['com_id'] = $com_id;
	    	$where['status'] = 1;
	    	$count = $ModelObj->where($where)->count();
	    	return $count+1;
    	}
    }
    
    //获取数量
    public function getCountNumber($com_id){
    	if(empty($com_id)){
    		return false;
    	}else{
    		$ModelObj = D('BusinesscircleBannerConfig');
    		$where['com_id'] = $com_id;
    		$where['status'] = 1;
    		$count = $ModelObj->where($where)->count();
    		return $count;
    	}
    }
    
    //添加数据
    public function add($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('BusinesscircleBannerConfig');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }
    
}