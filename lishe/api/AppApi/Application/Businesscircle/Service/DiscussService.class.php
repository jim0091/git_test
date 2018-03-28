<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈_HR专贴_讨论接口服务];				@version:1.1
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[DiscussService.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2016-2017 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-03-21 AM
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2017-03-21 AM
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Service;
use Think\Controller;
class DiscussService extends Controller {
	
    public function index(){
    	return '';
    }
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询讨论表（不存在返回false,存在则返回一条数据）
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array	$where	条件
     * @param:	string	$field	查询的字段
     * @return: boolean  or  array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findDiscuss	BEGIN
    public function findDiscuss($where,$field=''){
    	$ModelObj = D('TopicDiscuss');
    	if(empty($where)){
    		return false;
    	}else{
    		$findData = $ModelObj->where($where)->field($field)->find();
    		if($findData){
    			return $findData;
    		}else{
    			return false;
    		}
    	}
    }	//findDiscuss END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询讨论表（不存在返回false,存在则返回多条数据）
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array	$where	条件
     * @param:	string	$field	查询的字段
     * @return: boolean  or  array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectDiscuss BEGIN
    public function selectDiscuss($where,$field=''){
    	if(empty($where)){
    		return false;
    	}else{
    		$ModelObj = D('TopicDiscuss');
    		$arrData = $ModelObj->where($where)->field($field)->order('optimum DESC')->select();
    		if(empty($arrData)){
    			return false;
    		}else{
    			return $arrData;
    		}
    	}
    }	//selectDiscuss END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	新增讨论数据
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $data
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addDiscuss	BEGIN
    public function addDiscuss($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('TopicDiscuss');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;
    		}else{
    			return false;
    		}
    	}
    }	//addDiscuss END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	讨论表删除（非物理删除）
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	int $discussID
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//removeDiscuss	BEGIN
    public function removeDiscuss($topic_discuss_id){
    	$ModelObj = D('TopicDiscuss');
    	$where['id'] = $topic_discuss_id;
    	$data['status'] = C('DATA_STATUS')[0]['key'];
    	$data['update_time'] = getNow();
    	$bool = $ModelObj->where($where)->save($data);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//removeDiscuss END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	删除讨论（物理删除）
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	int $discussID
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//deleteComment BEGIN
    public function deleteDiscuss($topic_discuss_id){
    	$ModelObj = D('TopicDiscuss');
    	$where['id'] = $topic_discuss_id;
    	$countbool = $ModelObj->where($where)->delete();
    	if($countbool){
    		return $countbool;//返回删除数量
    	}else{
    		return false;
    	}
    }	//deleteComment END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	内容表的累计讨论+1
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	@param array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//plusDiscussNum	BEGIN
    public function plusDiscussNum($topic_id){
    	$ModelObj = D('Topic');
    	$where['id'] = $topic_id;
    	$bool = $ModelObj->where($where)->setInc('discuss_num',1);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//plusDiscussNum END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	内容表的累计讨论-1
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	@param array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//subtractDiscussNum	BEGIN
    public function subtractDiscussNum($topic_id){
    	$ModelObj = D('Topic');
    	$where['id'] = $topic_id;
    	$findData = $ModelObj->where($where)->field('id,discuss_num')->find();
    	if($findData['discuss_num']>0){
    		$bool = $ModelObj->where($where)->setDec('discuss_num',1);
    		if($bool){
    			return $bool;
    		}else{
    			return false;
    		}
    	}else{
    		return true;
    	}
    }	//subtractDiscussNum END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	带条件Count
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param:	array $where
     * @return: int
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//countNum	BEGIN
    public function countNum($where){
    	$ModelObj = D('TopicDiscuss');
    	$count = $ModelObj->where($where)->count();
    	return $count;
    }	//countNum END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	更新数据
     * @author:	lihongqiang	2017-03-22 AM
     * @method:	传参请求
     * @param:	int $discussID
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//updateDiscuss	BEGIN
    public function updateDiscuss($where,$save){
    	$ModelObj = D('TopicDiscuss');
    	$bool = $ModelObj->where($where)->save($save);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//updateDiscuss END
    
    
    
    
    
    ////------------------------------讨论区结束------------------------------
    ////______________________________点赞区开始______________________________
    
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询点赞表（find）
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $where	条件
     * @param:	string $field	查询的字段
     * @return: boolean  or  array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findPraise	BEGIN
    public function findPraise($where,$field=''){
    	$ModelObj = D('TopicDiscussPraise');
    	if(empty($where)){
    		return false;
    	}else{
    		$findData = $ModelObj->where($where)->field($field)->find();
    		if($findData){
    			return $findData;
    		}else{
    			return false;
    		}
    	}
    }	//findPraise END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询点赞表(select)
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $where	条件
     * @param:	string $field	查询的字段
     * @return: boolean  or  array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectPraise	BEGIN
    public function selectPraise($where,$field=''){
    	$ModelObj = D('TopicDiscussPraise');
    	if(empty($where)){
    		return false;
    	}else{
    		$arrData = $ModelObj->where($where)->field($field)->order('create_time desc')->select();
    		if($arrData){
    			return $arrData;
    		}else{
    			return false;
    		}
    	}
    }	//selectPraise END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	写入点赞记录数据表
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addPraise	BEGIN
    public function addPraise($data){
    	$ModelObj = D('TopicDiscussPraise');
    	$boolData = $ModelObj->add($data);
    	if($boolData){
    		return $boolData;//返回点赞记录的ID
    	}else{
    		return false;
    	}
    }	//addPraise END
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	内容表的累计点赞+1
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//plusPraiseNum	BEGIN
    public function plusPraiseNum($topic_discuss_id){
    	$ModelObj = D('TopicDiscuss');
    	$where['id'] = $topic_discuss_id;
    	$bool = $ModelObj->where($where)->setInc('praise_num',1);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//plusPraiseNum END
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	内容表的累计点赞-1
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//subtractPraiseNum	BEGIN
    public function subtractPraiseNum($topic_discuss_id){
    	$ModelObj = D('TopicDiscuss');
    	$where['id'] = $topic_discuss_id;
    	$findData = $ModelObj->where($where)->field('id,praise_num')->find();
    	if($findData['praise_num']>0){
    		$bool = $ModelObj->where($where)->setDec('praise_num',1);
    		if($bool){
    			return $bool;
    		}else{
    			return false;
    		}
    	}else{
    		return true;
    	}
    }	//subtractPraiseNum END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	删除点赞表的记录
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $where  //条件
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//deletePraise	BEGIN
    public function deletePraise($where){
    	$ModelObj = D('TopicDiscussPraise');
    	$findData = $this->findPraise($where);
    	if($findData){
    		$bool = $ModelObj->where($where)->delete();
    		if($bool){
    			return true;
    		}else{
    			return false;
    		}
    	}else{
    		return true;
    	}
    }	//deletePraise END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	返回点赞数量
     * @author:	lihongqiang	2017-03-21 AM
     * @method:	传参请求
     * @param:	array $where  //条件
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//countPraise	BEGIN
    public function countPraise($where){
    	if($where){
    		$ModelObj = D('TopicDiscussPraise');
    		$countPraise = $ModelObj->where($where)->count();
    		return $countPraise;
    	}else{
    		return 0;
    	}
    }	//countPraise END
    
    
    
    
    
    
}