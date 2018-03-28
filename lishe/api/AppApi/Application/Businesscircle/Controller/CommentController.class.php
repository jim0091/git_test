<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈评论接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[CommentController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2017
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Controller;
use Businesscircle\Service\CommentService;

use Common\Controller\RootController;
use Think\Controller;
class CommentController extends RootController {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_晒福利_发布评论
	 * @author:	lihongqiang	2017-03-16 11:03
	 * @method:	POST
	 * @param:	content	businesscircle_id
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//publish	BEGIN
	public function publish(){
		$postData = I('post.');
		if(empty($postData)){
			$errorInfo['status'] = -1001;
			$errorInfo['msg'] = "参数为空，没有提交任何数据";
			$errorInfo['message'] = "服务繁忙，操作失败";
			$this->retError($errorInfo);
		}else{
			$com_id = $_SESSION['com_id'];
			$user_id = $_SESSION['user_id'];
			$content = $postData['content'];
			if(empty($content)){
				$errorInfo['status'] = -1002;
				$errorInfo['msg'] = "参数content为空";
				$errorInfo['message'] = "亲，你还没填写发布内容";
				$this->retError($errorInfo);
			}else{
				$businesscircle_id = $postData['businesscircle_id'];
				if(empty($com_id)||empty($user_id)||empty($businesscircle_id)){
					$errorInfo['status'] = -1003;
					$errorInfo['msg'] = "参数businesscircle_id,user_id或com_id缺失";
					$errorInfo['message'] = "服务繁忙，请稍后再试";
					$this->retError($errorInfo);
				}else{
					$boolData = $this->setComment($postData);//去写评论数据
					if($boolData){
						$successInfo['status'] = 1000;
						if($postData['root_id']==0){
							$successInfo['message'] = "评论成功";
						}else{
							$successInfo['message'] = "回复成功";
						}
						$successInfo['data'] = array('key'=>$boolData);
						$this->retSuccess($successInfo);
					}else{
						$errorInfo['status'] = -1004;
						$errorInfo['msg'] = "数据写入失败或主表的累计评论comment_num更新失败";
						$errorInfo['message'] = "服务繁忙，评论失败";
						$this->retError($errorInfo);
					}
				}
			}
		}
	}	//publish END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	写入评论记录（只需要传post必须的参数就可以）
	 * @author:	lihongqiang	2017-03-17 14:38
	 * @method:	@param array $data
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//setComment	BEGIN
	protected function setComment($postData){
		$transActionObj = M('');//事务对象
		$transActionObj->startTrans();
		$data['businesscircle_id'] = $postData['businesscircle_id'];//评论的圈子内容的ID
		$data['user_id'] = $_SESSION['user_id'];//评论人ID
		if(empty($postData['root_id'])||$postData['root_id']==0){//root_id为0是评论
			$data['root_id'] = 0;//根ID
			$data['father_id'] = 0;//父ID
			$data['to_user_id'] = 0;//回复给谁就是谁的ID
		}else{
			$data['root_id'] = $postData['root_id'];//根ID
			$data['father_id'] = $postData['father_id'];//父ID
			$data['to_user_id'] = $postData['to_user_id'];//回复给谁就是谁的ID
		}
		$data['content'] = $postData['content'];//评论或回复内容
		$data['is_anonymity'] = $postData['is_anonymity'];//评论或回复内容
		
		$data['status'] =  C('DATA_STATUS')[1]['key'];//数据的状态//默认1,上线删除就是使用这个字段
		$data['ls_status'] =  C('LS_STATUS')[1]['key'];//礼舍后台操作的状态//默认1
		$data['create_time'] = getNow();//评论或回复时间
		$data['update_time'] = getNow();//更新时间
		$Service = new CommentService();
		$boolData = $Service->addComment($data);//正式去写入
		if($boolData){
			//把主表的评论数量+1
			$bool = $Service->plusCommentNum($data['businesscircle_id']);
			if($bool){
				$transActionObj->commit();
				return $boolData;
			}else{
				$transActionObj->rollback();
				return false;
			}
		}else{
			$transActionObj->rollback();
			return false;
		}
	}	//setComment END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_晒福利_删除评论
	 * @author:	lihongqiang	2017-03-15 15:09
	 * @method:	POST
	 * @param:	comment_id	businesscircle_id
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//remove	BEGIN
	public function remove(){
		$postData = I('post.');
		if(empty($postData)){
			$errorInfo['status'] = -1001;
			$errorInfo['msg'] = "参数为空，没有提交任何数据";
			$errorInfo['message'] = "服务繁忙，删除失败";
			$this->retError($errorInfo);
		}else{
			$user_id = $_SESSION['user_id'];
			$comment_id = $postData['comment_id'];
			$businesscircle_id = $postData['businesscircle_id'];
			if(empty($comment_id)||empty($businesscircle_id)||empty($user_id)){
				$errorInfo['status'] = -1002;
				$errorInfo['msg'] = "参数comment_id或businesscircle_id为空或登录失效";
				$errorInfo['message'] = "服务繁忙，删除失败";
				$this->retError($errorInfo);
			}else{
				$Service = new CommentService();//new一个实例化对象
				$where['id'] = $comment_id;
				$field = 'id';
				$findComment = $Service->findComment($where,$field);//查询要删除的评论是否存在
				if ($findComment){
					//有评论，去删除
					$boolean = $this->removeComment($comment_id,$businesscircle_id);
					if($boolean){
						$successInfo['status'] = 1000;
						$successInfo['message'] = "删除成功";
						$successInfo['data'] = array('key'=>1);
						$this->retSuccess($successInfo);
					}else{
						$errorInfo['status'] = -1004;
						$errorInfo['msg'] = "评论数据删除失败或评论数量更新失败";
						$errorInfo['message'] = "服务繁忙，删除失败";
						$this->retError($errorInfo);
					}
				}else{
					//没有该评论
					$errorInfo['status'] = -1003;
					$errorInfo['msg'] = "comment_id对应的评论数据不存在";
					$errorInfo['message'] = "服务繁忙，删除失败";
					$this->retError($errorInfo);
				}
			}
		}
	}	//remove END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	删除评论记录
	 * @author:	lihongqiang	2017-03-17 15:14
	 * @method:	
	 * @param int $businesscircle_id
	 * @return: boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//removePraise	BEGIN
	protected function removeComment($comment_id,$businesscircle_id){
		$transActionObj = M('');//事务对象
		$transActionObj->startTrans();
		$Service = new CommentService();
		$boolean = $Service->removeComment($comment_id);
		if($boolean){
			//传businesscircle_id过去把主表的点赞数量-1
			$bool = $Service->subtractCommentNum($businesscircle_id);
			if($bool){
				$transActionObj->commit();
				return true;
			}else{
				$transActionObj->rollback();
				return false;
			}
		}else{
			$transActionObj->rollback();
			return false;
		}
	}//removePraise END
	
	
	
	
	
	
	
	
	
	
	
	
	
}