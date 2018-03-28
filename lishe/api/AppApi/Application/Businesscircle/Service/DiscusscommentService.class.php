<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈评论接口服务];					@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[BusinesscircleService/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[DiscusscommentService.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2016-2017 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Service;
use Think\Controller;
class DiscusscommentService extends Controller {
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	查询评论表（不存在返回false,存在则返回一条数据）
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	传参请求
	 * @param:	array	$where	条件
	 * @param:	string	$field	查询的字段 			
	 * @return: boolean  or  array1
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//findComment	BEGIN
	public function findComment($where,$field=''){
		$ModelObj = D('TopicDiscussComment');
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
	}//findComment END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	查询评论表（不存在返回false,存在则返回多条数据）
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	@param	array	$where	条件
	 * 			@param	string	$field	查询的字段
	 * @return: boolean  or  array2
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	public function selectComment($where,$field=''){
		if(empty($where)){
			return false;
		}else{
			$ModelObj = D('TopicDiscussComment');
			$arrData = $ModelObj->where($where)->field($field)->select();
			if(empty($arrData)){
				return false;
			}else{
				return $arrData;
			}
		}
	}
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	新增评论或回复数据
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	@param array $data
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//addComment	BEGIN
	public function addComment($data){
		if(empty($data)){
			return false;
		}else{
			$ModelObj = D('TopicDiscussComment');
			$boolData = $ModelObj->add($data);
			if($boolData){
				return $boolData;
			}else{
				return false;
			}
		}
	}//addComment END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	评论表删除（非物理删除）
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	传参请求
	 * @param:	int $commentID
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//addComment	BEGIN
	public function removeComment($commentID){
		$ModelObj = D('TopicDiscussComment');
		$where['id'] = $commentID;
		$data['status'] = C('DATA_STATUS')[0]['key'];
		$data['update_time'] = getNow();
		$bool = $ModelObj->where($where)->save($data);
		if($bool){
			return $bool;
		}else{
			return false;
		}
	}
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	删除（物理删除）
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	传参请求
	 * @param:	int $commentID
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	public function deleteComment($commentID){
		$ModelObj = D('TopicDiscussComment');
		$where['id'] = $commentID;
		$countbool = $ModelObj->where($where)->delete();
		if($countbool){
			return $countbool;//返回删除数量
		}else{
			return false;
		}
	}
    
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	内容表的累计评论+1
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	传参请求
	 * @param: 	array $data  //新增的数据
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//plusCommentNum	BEGIN
	public function plusCommentNum($topic_discuss_id){
		$ModelObj = D('TopicDiscuss');
		$where['id'] = $topic_discuss_id;
		$bool = $ModelObj->where($where)->setInc('comment_num',1);
		if($bool){
			return $bool;
		}else{
			return false;
		}	
	}	//plusCommentNum END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	内容表的累计评论-1
	 * @author:	lihongqiang	2017-03-21	PM
	 * @method:	@param array $data  //新增的数据
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//subtractCommentNum	BEGIN
	public function subtractCommentNum($topic_discuss_id){
		$ModelObj = D('TopicDiscuss');
		$where['id'] = $topic_discuss_id;
		$findData = $ModelObj->where($where)->field('id,comment_num')->find();
		if($findData['comment_num']>0){
			$bool = $ModelObj->where($where)->setDec('comment_num',1);
			if($bool){
				return $bool;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}	//subtractCommentNum END
	
	
	
}