<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口服务];						@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[LeavemsgService.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2016-2017 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-3-27 10:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2017
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Service;
use Think\Controller;
class LeavemsgService extends Controller {
	
    public function index(){
    	return '';
    }
    
  
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR留言_数据入库
     * @author:	lihongqiang	2017-3-27 10:45
     * @method:	传参请求
     * @param:	array $data //写入数据库的数组信息
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addBusiness	BEGIN
    public function addLeavemsg($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('Leavemsg');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }//addBusiness END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR留言_查询留言内容数据（返回多条数据）
     * @author:	lihongqiang	2017-3-27 10:26
     * @method:	传参请求
     * @param array $where //查询条件的数组信息
     * @param string $field //查询的字段
     * @return: boolean	or	array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectBusiness	BEGIN
    public function selectLeavemsg($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Leavemsg');
    		$arrData = $ModelObj->where($where)->field($field)->order('create_time desc')->select();
    		return $arrData;//返回二维数组
    	}
    }//selectBusiness END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR留言_查询留言内容数据（返回单条数据）
     * @author:	lihongqiang	2017-3-27 11:26
     * @method:	传参请求
     * @param array $where //查询条件的数组信息
     * @param string $field //查询的字段
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findBusiness	BEGIN
    public function findLeavemsg($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Leavemsg');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		return $findData;//返回二维数组
    	}
    } //findBusiness END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询积分表（返回单条数据）
     * @author:	lihongqiang	2017-3-27 11:26
     * @method:	传参请求
     * @param array $where //查询条件的数组信息
     * @param string $field //查询的字段
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findBusiness	BEGIN
    public function findSysuserUserDeposit($where,$field = ''){
    	if(empty($where)){
    		return false;
    	}else{
    		$ModelObj = D('SysuserUserDeposit');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		if($findData){
    			return $findData;
    		}else{
    			return false;
    		}
    	}
    } //findBusiness END
  
}