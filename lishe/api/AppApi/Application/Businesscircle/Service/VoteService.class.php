<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈_HR发帖的接口服务];						@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[VoteService.class.php];	
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
class VoteService extends Controller {
	
    public function index(){
    	return '';
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_数据入库
     * @author:	lihongqiang	2017-05-09 11:35
     * @method:	传参请求
     * @param:	array $data //写入数据库的数组信息
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addVote	BEGIN
    public function addVote($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('Vote');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }//addVote END
    
  
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_查询投票内容数据（返回多条数据）
     * @author:	lihongqiang	2017-03-16 10:26
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectVote	BEGIN
    public function selectVote($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Vote');
    		$arrData = $ModelObj->where($where)->field($field)->order('id DESC')->select();
    		return $arrData;//返回二维数组
    	}
    }	//selectVote END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_查询投票内容数据（返回单条数据）
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findVote	BEGIN
    public function findVote($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Vote');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		return $findData;//返回二维数组
    	}
    }	//findVote END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_删除内容数据（非物理删除）
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param: 	string $vote_id //查询条件的数组信息
     * @param:	string $data //修改的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//removeVote	BEGIN
    public function removeVote($vote_id){
    	if(empty($vote_id)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Vote');
    		$where['id'] = $vote_id;
    		$data['status'] = C('DATA_STATUS')[0]['key'];
    		$data['update_time'] = getNow();
    		$bool = $ModelObj->where($where)->save($data);
    		if($bool){
    			return $bool;//返回bool记录条数
    		}else{
    			return false;
    		}
    	}
    }	//removeVote END
    
    
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
    public function updateVote($where,$save){
    	$ModelObj = D('Vote');
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
     * @author:	lihongqiang	2017-05-09 PM
     * @method:	传参请求
     * @param:	array $where
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//countTopic	BEGIN
    public function countVote($where){
    	$ModelObj = D('Vote');
    	$count = $ModelObj->where($where)->count();
    	return $count;
    }	//countTopic END

    
    
    
    
    
    
    
    //===============================主题结束，选项开始========================================================
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_选项数据入库
     * @author:	lihongqiang	2017-05-09 11:35
     * @method:	传参请求
     * @param:	array $data //写入数据库的数组信息
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addVote	BEGIN
    public function addVoteOption($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('VoteOption');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }//addVote END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_查询投票选项内容数据（返回多条数据）
     * @author:	lihongqiang	2017-05-10 11:26
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectVote	BEGIN
    public function selectVoteOption($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('VoteOption');
    		$arrData = $ModelObj->where($where)->field($field)->select();
    		return $arrData;//返回二维数组
    	}
    }	//selectVote END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_查询投票选项内容数据（返回单条数据）
     * @author:	lihongqiang	2017-05-10 11:26
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findVoteOption	BEGIN
    public function findVoteOption($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('VoteOption');
    		$arrData = $ModelObj->where($where)->field($field)->find();
    		return $arrData;//返回二维数组
    	}
    }	//findVoteOption END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_参与投票_数据入库
     * @author:	lihongqiang	2017-05-09 11:35
     * @method:	传参请求
     * @param:	array $data //写入数据库的数组信息
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addVoteResult	BEGIN
    public function addVoteResult($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('VoteResult');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }//addVoteResult END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_查询投票结果数据（返回单条数据）
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findVoteResult	BEGIN
    public function findVoteResult($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('VoteResult');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		return $findData;//返回一维数组
    	}
    }	//findVoteResult END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_查询投票结果数据（返回多条数据）
     * @author:	lihongqiang	2017-03-22 PM
     * @method:	传参请求
     * @param: 	array $where //查询条件的数组信息
     * @param:	string $field //查询的字段
     * @return:	boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectVoteResult	BEGIN
    public function selectVoteResult($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('VoteResult');
    		$findData = $ModelObj->where($where)->field($field)->group('user_id')->select();
    		return $findData;//返回一维数组
    	}
    }	//selectVoteResult END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_投票内容表累计参与join_num+1
     * @author:	lihongqiang	2017-05-15 PM
     * @method:	传参请求
     * @param:	string $vote_id  
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//plusPraiseNum	BEGIN
    public function plusJoinNum($vote_id){
    	$ModelObj = D('Vote');
    	$where['id'] = $vote_id;
    	$bool = $ModelObj->where($where)->setInc('join_num',1);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//plusPraiseNum END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR发布投票_投票选项表的累计认可数量accept_num+1
     * @author:	lihongqiang	2017-05-15 PM
     * @method:	传参请求
     * @param:	string $vote_id  
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//plusPraiseNum	BEGIN
    public function plusAcceptNum($vote_id,$option_id){
    	$ModelObj = D('VoteOption');
    	$where['id'] = $option_id;
    	$where['vote_id'] = $vote_id;
    	$bool = $ModelObj->where($where)->setInc('accept_num',1);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }	//plusPraiseNum END
    
    
    
    
    
    
    
    
}