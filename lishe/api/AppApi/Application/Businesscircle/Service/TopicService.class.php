<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈_HR发帖的接口服务];						@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[TopicService.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2016-2017 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-3-20 17:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2017
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Service;
use Think\Controller;
class TopicService extends Controller {
	
    public function index(){
    	return '';
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发帖_数据入库
     * @author:	lihongqiang	2017-03-20 17:35
     * @method:	传参请求
     * @param:	array $data //写入数据库的数组信息
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addTopic	BEGIN
    public function addTopic($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('Topic');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }//addTopic END
    
  
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发帖_查询帖子内容数据（返回多条数据）
     * @author:	lihongqiang	2017-03-16 10:26
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectTopic	BEGIN
    public function selectTopic($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Topic');
    		$arrData = $ModelObj->where($where)->field($field)->order('stick DESC,sort_num DESC')->select();
    		return $arrData;//返回二维数组
    	}
    }	//selectTopic END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发帖_查询帖子内容数据（返回单条数据）
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findTopic	BEGIN
    public function findTopic($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Topic');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		return $findData;//返回二维数组
    	}
    }	//findTopic END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发帖_删除内容数据（非物理删除）
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param: 	string $topic_id //查询条件的数组信息
     * @param:	string $data //修改的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//removeTopic	BEGIN
    public function removeTopic($topic_id){
    	if(empty($topic_id)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Topic');
    		$where['id'] = $topic_id;
    		$data['status'] = C('DATA_STATUS')[0]['key'];
    		$data['update_time'] = getNow();
    		$bool = $ModelObj->where($where)->save($data);
    		if($bool){
    			return $bool;//返回bool记录条数
    		}else{
    			return false;
    		}
    	}
    }	//removeTopic END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	更新数据
     * @author:	lihongqiang	2017-04-12 PM
     * @method:	传参请求
     * @param:	array $where
     * @param:	array $save
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//updateTopic	BEGIN
    public function updateTopic($where,$save){
    	$ModelObj = D('Topic');
    	$bool = $ModelObj->where($where)->save($save);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//updateTopic END
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	获取排序数字
     * @author:	lihongqiang	2017-04-19 PM
     * @method:	传参请求
     * @param:	array $where
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//countTopic	BEGIN
    public function countTopic($where){
    	$ModelObj = D('Topic');
    	$count = $ModelObj->where($where)->count();
    	return $count;
    }	//countTopic END
    
}