<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈_HR专贴_讨论接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[DiscussController.class.php];	
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
use Common\Controller\RootController;
use Businesscircle\Service\CircleService;
use Businesscircle\Service\DiscussService;
use Common\Common\Classlib\UploadFile\UploadImages;
use Think\Controller;
class DiscussController extends RootController {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
	/**
	 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_HR话题_发布讨论
	 * @author:	lihongqiang	2017-03-21 AM
	 * @method:	POST
	 * @param:	topic_id root_id father_id to_user_id is_anonymity	content	images(可选参数)	
	 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
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
				$errorInfo['message'] = "亲，你还没填写发表内容";
				$this->retError($errorInfo);
			}else{
				$topic_id = $postData['topic_id'];
				if(empty($com_id)||empty($user_id)||empty($topic_id)){
					$errorInfo['status'] = -1003;
					$errorInfo['msg'] = "参数topic_id,user_id或com_id缺失";
					$errorInfo['message'] = "服务繁忙，请稍后再试";
					$this->retError($errorInfo);
				}else{
					$data = array();
					if($_FILES){
						$filesNum = count($_FILES);
						$upload = new UploadImages();
						//$uploadConfig['rootPath'] = './Public/';//根路径
						$uploadConfig['savePath'] = 'UploadImages/Businesscircle/DiscussContent/';//相对根路径
						$uploadInfo = $upload->ImagesUpload($uploadConfig);
						if($uploadInfo){
							$actualFilesNum = count($uploadInfo);//实际上传成功数量
							if ($actualFilesNum!=$filesNum){
								$errorInfo['status'] = -1005;
								$errorInfo['msg'] = "图片的上传数量与实际上传数量不相符，选择了".$filesNum."张，上传成功了".$actualFilesNum."张";
								$errorInfo['message'] = "服务繁忙，请重新发布";
								$this->retError($errorInfo);
							}else{
								if($uploadInfo['images1']){
									$data['images1'] = $uploadInfo['images1']['imagesPath'];
								}
								if($uploadInfo['images2']){
									$data['images2'] = $uploadInfo['images2']['imagesPath'];
								}
								if($uploadInfo['images3']){
									$data['images3'] = $uploadInfo['images3']['imagesPath'];
								}
								if($uploadInfo['images4']){
									$data['images4'] = $uploadInfo['images4']['imagesPath'];
								}
								if($uploadInfo['images5']){
									$data['images5'] = $uploadInfo['images5']['imagesPath'];
								}
								if($uploadInfo['images6']){
									$data['images6'] = $uploadInfo['images6']['imagesPath'];
								}
								if($uploadInfo['images7']){
									$data['images7'] = $uploadInfo['images7']['imagesPath'];
								}
								if($uploadInfo['images8']){
									$data['images8'] = $uploadInfo['images8']['imagesPath'];
								}
								if($uploadInfo['images9']){
									$data['images9'] = $uploadInfo['images9']['imagesPath'];
								}
								$data['topic_id'] = $topic_id;//评论的圈子内容的ID
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
							}
						}else{
							$errorInfo['status'] = -1004;
							$errorInfo['msg'] = "图片的上传失败";
							$errorInfo['message'] = "网络暂忙，请重新发布";
							$this->retError($errorInfo);
						}
					}else{
						$data['topic_id'] = $topic_id;//评论的圈子内容的ID
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
					}
					if(empty($data)){
						$errorInfo['status'] = -1006;
						$errorInfo['msg'] = "数据设置失败，data为空";
						$errorInfo['message'] = "服务繁忙，发表失败";
						$this->retError($errorInfo);
					}else{
						$boolData = $this->setDiscuss($data);//去写评论数据和增加主表的讨论数量
						if($boolData){
							$successInfo['status'] = 1000;
							if($postData['root_id']==0){
								$successInfo['message'] = "发表成功";
							}else{
								$successInfo['message'] = "回复成功";
							}
							$successInfo['data'] = array('key'=>$boolData);
							$this->retSuccess($successInfo);
						}else{
							$errorInfo['status'] = -1007;
							$errorInfo['msg'] = "数据写入失败或主表的累计讨论discuss_num更新失败";
							$errorInfo['message'] = "服务繁忙，发表失败";
							$this->retError($errorInfo);
						}
					}
				}
			}
		}
	}	//publish END
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_HR帖子_写入评论记录（只需要传post必须的参数就可以）
	 * @author:	lihongqiang	2017-03-21 AM
	 * @method:	@param array $data
	 * @return: bool
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//setComment	BEGIN
	protected function setDiscuss($setData){
		if(empty($setData)){
			return false;
		}else{
			$transActionObj = M('');//事务对象
			$transActionObj->startTrans();
			$data['topic_id'] = $setData['topic_id'];//评论的圈子内容的ID
			$data['user_id'] = $_SESSION['user_id'];//评论人ID
			if(empty($setData['root_id'])||$setData['root_id']==0){//root_id为0是评论
				$data['root_id'] = 0;//根ID
				$data['father_id'] = 0;//父ID
				$data['to_user_id'] = 0;//回复给谁就是谁的ID
			}else{
				$data['root_id'] = $setData['root_id'];//根ID
				$data['father_id'] = $setData['father_id'];//父ID
				$data['to_user_id'] = $setData['to_user_id'];//回复给谁就是谁的ID
			}
			$data['content'] = $setData['content'];//评论或回复内容
			$data['is_anonymity'] = $setData['is_anonymity'];//评论或回复内容
			if($setData['images1']){
				$data['images1'] = $setData['images1'];
			}
			if($setData['images2']){
				$data['images2'] = $setData['images2'];
			}
			if($setData['images3']){
				$data['images3'] = $setData['images3'];
			}
			if($setData['images4']){
				$data['images4'] = $setData['images4'];
			}
			if($setData['images5']){
				$data['images5'] = $setData['images5'];
			}
			if($setData['images6']){
				$data['images6'] = $setData['images6'];
			}
			if($setData['images7']){
				$data['images7'] = $setData['images7'];
			}
			if($setData['images8']){
				$data['images8'] = $setData['images8'];
			}
			if($setData['images9']){
				$data['images9'] = $setData['images9'];
			}
			$data['status'] =  C('DATA_STATUS')[1]['key'];//数据的状态//默认1,上线删除就是使用这个字段
			$data['ls_status'] =  C('LS_STATUS')[1]['key'];//礼舍后台操作的状态//默认1
			$data['create_time'] = getNow();//评论或回复时间
			$data['update_time'] = getNow();//更新时间
			$Service = new DiscussService();
			$boolData = $Service->addDiscuss($data);//正式去写入
			if($boolData){
				//把主表的讨论数量+1
				$bool = $Service->plusDiscussNum($data['topic_id']);
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
		}
	}//setComment END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_HR话题_删除讨论操作
	 * @author:	lihongqiang	2017-03-21 AM
	 * @method:	POST
	 * @param:	topic_id	topic_discuss_id
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
			$topic_discuss_id = $postData['topic_discuss_id'];
			$topic_id = $postData['topic_id'];
			if(empty($topic_discuss_id)||empty($topic_id)||empty($user_id)){
				$errorInfo['status'] = -1002;
				$errorInfo['msg'] = "参数topic_discuss_id或topic_id为空或登录失效";
				$errorInfo['message'] = "服务繁忙，删除失败";
				$this->retError($errorInfo);
			}else{
				$Service = new DiscussService();//new一个实例化对象
				$where['id'] = $topic_discuss_id;
				$field = 'id';
				$findDiscuss = $Service->findDiscuss($where,$field);//查询要删除的评论是否存在
				if ($findDiscuss){
					//有评论：1、去删除；2、减一
					$boolean = $this->removeDiscuss($topic_discuss_id,$topic_id);
					if($boolean){
						$successInfo['status'] = 1000;
						$successInfo['message'] = "删除成功";
						$successInfo['data'] = array('key'=>1);
						$this->retSuccess($successInfo);
					}else{
						$errorInfo['status'] = -1004;
						$errorInfo['msg'] = "评论数据删除失败或主贴的讨论数量减1失败,讨论的ID(".$topic_discuss_id.")";
						$errorInfo['message'] = "服务繁忙，删除失败";
						$this->retError($errorInfo);
					}
				}else{
					//没有该评论
					$errorInfo['status'] = -1003;
					$errorInfo['msg'] = "discuss_id对应的评论数据不存在";
					$errorInfo['message'] = "服务繁忙，删除失败";
					$this->retError($errorInfo);
				}
			}
		}
	}//remove END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_HR帖子_删除讨论记录
	 * @author:	lihongqiang	2017-03-21	AM
	 * @method:	@param int $topic_id
	 * @return: boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//removeDiscuss	BEGIN
	protected function removeDiscuss($topic_discuss_id,$topic_id){
		$transActionObj = M('');//事务对象
		$transActionObj->startTrans();
		$Service = new DiscussService();
		$boolean = $Service->removeDiscuss($topic_discuss_id);
		if($boolean){
			//传topic_id过去把主表的点赞数量-1
			$bool = $Service->subtractDiscussNum($topic_id);
			if($bool){
				$transActionObj->commit();//提交事务
				return true;
			}else{
				$transActionObj->rollback();//回滚事务
				return false;
			}
		}else{
			$transActionObj->rollback();//回滚事务
			return false;
		}
	}	//removeDiscuss END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	HR把该条讨论内容设为最佳
	 * @author:	lihongqiang	2016-03-22	AM
	 * @method:	POST
	 * @param:	topic_discuss_id
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//optimum BEGIN
	public function optimum(){
		$postData = I('post.');
		if(empty($postData)){
			$errorInfo['status'] = -1001;
			$errorInfo['msg'] = "参数为空，没有提交任何数据";
			$errorInfo['message'] = "服务繁忙，删除失败";
			$this->retError($errorInfo);
		}else{
			$user_id = $_SESSION['user_id'];
			$topic_discuss_id = $postData['topic_discuss_id'];
			$topic_id = $postData['topic_id'];
			if(empty($topic_discuss_id)||empty($topic_id)||empty($user_id)){
				$errorInfo['status'] = -1002;
				$errorInfo['msg'] = "参数topic_discuss_id或topic_id为空或登录失效";
				$errorInfo['message'] = "服务繁忙，设置失败";
				$this->retError($errorInfo);
			}else{
				$CirService = new CircleService();
				$is_HR = $CirService->IS_HR($user_id);
				if($is_HR){
					$Service = new DiscussService();//new一个实例化对象
					$where['id'] = $topic_discuss_id;
					$field = 'id,optimum';
					$findDiscuss = $Service->findDiscuss($where,$field);//查询要设为最佳的讨论是否存在
					if($findDiscuss){
						if($findDiscuss['optimum']==1){
							//已经设为最佳回答了
							$errorInfo['status'] = -1005;
							$errorInfo['msg'] = "该条讨论内容已经被设为最佳了";
							$errorInfo['message'] = "已经是最佳了";
							$this->retError($errorInfo);
						}else{
							$boolean = $this->setOptimum($topic_id,$topic_discuss_id);
							if($boolean){
								$successInfo['status'] = 1000;
								$successInfo['message'] = "设置成功";
								$successInfo['data'] = array('key'=>1);
								$this->retSuccess($successInfo);
							}else{
								$errorInfo['status'] = -1006;
								$errorInfo['msg'] = "服务异常";
								$errorInfo['message'] = "服务繁忙，请稍后重试";
								$this->retError($errorInfo);
							}
						}
					}else{
						$errorInfo['status'] = -1004;
						$errorInfo['msg'] = "该条讨论内容不存在或已经被删除";
						$errorInfo['message'] = "服务繁忙，请稍后重试";
						$this->retError($errorInfo);
					}	
				}else{
					$errorInfo['status'] = -1003;
					$errorInfo['msg'] = "非HR没有设为最佳的操作权限";
					$errorInfo['message'] = "亲，只有HR才能设为最佳哟";
					$this->retError($errorInfo);
				}
			}
		}
	}	//optimum END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	设为最佳(过渡端)
	 * @author:	lihongqiang	2017-03-22 PM
	 * @method:	传参请求
	 * @param:	int $topic_id
	 * @param:	int $topic_discuss_id
	 * @return: boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//isPraise	BEGIN
	protected function setOptimum($topic_id,$topic_discuss_id){
		if($topic_id && $topic_discuss_id){
			$transActionObj = M('');//事务对象
			$Service = new DiscussService();
			$condition['topic_id'] = $topic_id;
			$condition['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
			$condition['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
			$condition['optimum'] = 1;
			$countNum = $Service->countNum($condition);
			if($countNum>0){
				$bool1 = false;
				$cond['topic_id'] = $topic_id;
				////$cond['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
				////$cond['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
				$cond['optimum'] = 1;
				$data['optimum'] = 0;
				$data['update_time'] = getNow();
				$bool1 = $Service->updateDiscuss($cond,$data);//先把optimum=1的数据全更新为0
			}else{
				$bool1 = true;
			}
			if($bool1){
				$where['id'] = $topic_discuss_id;
				$where['topic_id'] = $topic_id;
				$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
				$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
				$save['optimum'] = 1;
				$save['update_time'] = getNow();
				$bool2 = $Service->updateDiscuss($where,$save);
				if($bool2){
					$transActionObj->commit();//提交事务
					return true;
				}else{
					$transActionObj->rollback();//回滚事务
					return false;
				}
			}else{
				$transActionObj->rollback();//回滚事务
				return false;
			}
		}else{
			return false;
		}
	}	//isPraise	END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_HR话题_讨论内容_点赞或取消点赞
	 * @author:	lihongqiang	2016-03-17	PM
	 * @method:	POST
	 * @param:	topic_id	topic_discuss_id
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//praise BEGIN
	public function praise(){
		$postData = I('post.');
		if(empty($postData)){
			$errorInfo['status'] = -1001;
			$errorInfo['msg'] = "参数为空，没有提交任何数据";
			$errorInfo['message'] = "服务繁忙，点赞失败";
			$this->retError($errorInfo);
		}else{
			$user_id = $_SESSION['user_id'];
			$topic_id = $postData['topic_id'];
			$topic_discuss_id = $postData['topic_discuss_id'];
			if(empty($topic_id)||empty($topic_discuss_id)){
				$errorInfo['status'] = -1002;
				$errorInfo['msg'] = "参数topic_discuss_id或topic_id为空";
				$errorInfo['message'] = "服务繁忙，点赞失败";
				$this->retError($errorInfo);
			}else{
				//检测是否点过赞
				$IS_Praise = $this->isPraise($topic_id,$topic_discuss_id);
				if($IS_Praise){
					//点过  ，取消点赞
					$boolean = $this->removePraise($topic_id,$topic_discuss_id);
					if($boolean){
						$successInfo['status'] = 1000;
						$successInfo['message'] = "取消成功";
						$successInfo['data'] = array('key'=>1);
						$this->retSuccess($successInfo);
					}else{
						$errorInfo['status'] = -1004;
						$errorInfo['msg'] = "删除点赞记录表数据失败或更新主表的累计点赞数量失败";
						$errorInfo['message'] = "取消失败，服务繁忙";
						$this->retError($errorInfo);
					}
				}else{
					//没点过，开始点赞操作
					$boolData = $this->setPraise($topic_id,$topic_discuss_id);
					if($boolData){
						$successInfo['status'] = 1000;
						$successInfo['message'] = "点赞成功";
						$successInfo['data'] = array('key'=>$boolData);
						$this->retSuccess($successInfo);
					}else{
						$errorInfo['status'] = -1003;
						$errorInfo['msg'] = "写入点赞记录表或更新主表的累计点赞数量失败";
						$errorInfo['message'] = "点赞失败，服务繁忙";
						$this->retError($errorInfo);
					}
				}
			}
		}
	}	//praise END
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	检测是否点过赞了
	 * @author:	lihongqiang	2017-03-17 14:36
	 * @method:	传参请求
	 * @param:	int $topic_id
	 * @param:	int $topic_discuss_id
	 * @return: boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//isPraise	BEGIN
	protected function isPraise($topic_id,$topic_discuss_id){
		if($topic_id && $topic_discuss_id){
			$where['user_id'] = $_SESSION['user_id'];
			$where['topic_id'] = $topic_id;
			$where['topic_discuss_id'] = $topic_discuss_id;
			$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
			$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
			$field = 'id,topic_id,topic_discuss_id,user_id';
			$Service = new DiscussService();
			$findPraise = $Service->findPraise($where,$field);
			if($findPraise){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}	//isPraise	END
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	写入点赞记录
	 * @author:	lihongqiang	2017-03-17 14:38
	 * @method:	传参请求
	 * @param: int $topic_id
	 * @param: int $topic_discuss_id
	 * @return: boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//setPraise	BEGIN
	protected function setPraise($topic_id,$topic_discuss_id){
		$transActionObj = M('');//事务对象
		$transActionObj->startTrans();
		$data['topic_id'] = $topic_id;
		$data['topic_discuss_id'] = $topic_discuss_id;
		$data['user_id'] = $_SESSION['user_id'];
		$data['status'] = C('DATA_STATUS')[1]['key'];
		$data['ls_status'] = C('LS_STATUS')[1]['key'];
		$data['create_time'] = getNow();
		$data['update_time'] = getNow();
		$Service = new DiscussService();
		$boolData = $Service->addPraise($data);
		if($boolData){
			//把主表的点赞数量+1
			$bool = $Service->plusPraiseNum($topic_discuss_id);
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
	}//setPraise END
	
	 
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	删除点赞记录
	 * @author:	lihongqiang	2017-03-17 15:14
	 * @method:	传参请求
	 * @param:	int $topic_id
	 * @param:	int $topic_discuss_id
	 * @return: boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//removePraise	BEGIN
	protected function removePraise($topic_id,$topic_discuss_id){
		$transActionObj = M('');//事务对象
		$transActionObj->startTrans();
		$where['user_id'] = $_SESSION['user_id'];
		$where['topic_id'] = $topic_id;
		$where['topic_discuss_id'] = $topic_discuss_id;
		$Service = new DiscussService();
		$boolean = $Service->deletePraise($where);
		if($boolean){
			//把主表的点赞数量-1
			$bool = $Service->subtractPraiseNum($topic_discuss_id);
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