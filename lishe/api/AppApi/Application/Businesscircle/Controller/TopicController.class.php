<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[TopicController.class.php];	
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
namespace Businesscircle\Controller;
use Common\Controller\RootController;
use Businesscircle\Service\DiscusscommentService;

use Businesscircle\Service\DiscussService;

use Businesscircle\Service\TopicService;
use Businesscircle\Service\CircleService;
use Common\Common\Classlib\UploadFile\UploadImages;
use Think\Controller;
class TopicController extends RootController {
	//继承RootController,代表执行本控制器内的所有方法都需要登录
	//继承CommonController,代表执行本控制器内的所有方法都不需要登录
	
	/**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_帖子内容发布（只有HR可以操作）
     * @author:	lihongqiang	2017-03-20 17:01
     * @method:	POST
     * @param:	content	images(可选参数)
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
    		$title = $postData['title'];
    		$content = $postData['content'];
    		if(empty($content)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数content为空";
    			$errorInfo['message'] = "亲，你还没填写发布内容";
    			$this->retError($errorInfo);
    		}else{
    			if(empty($com_id)||empty($user_id)){
    				$errorInfo['status'] = -1003;
    				$errorInfo['msg'] = "参数user_id或com_id缺失";
    				$errorInfo['message'] = "服务繁忙，请稍后再试";
    				$this->retError($errorInfo);
    			}else{
    				//判断登录人是不是HR
    				$CirService = new CircleService();
    				$is_HR = $CirService->IS_HR($user_id);
    				if($is_HR){
    					$data = array();
    					if($_FILES){
    						$filesNum = count($_FILES);
    						$upload = new UploadImages();
    						//$uploadConfig['rootPath'] = './Public/';//根路径
    						$uploadConfig['savePath'] = 'UploadImages/Businesscircle/Topic/';//相对根路径
    						$uploadConfig['subName'] = array('date','Ymd'); //子目录创建方式，采用数组或者字符串方式定义
    						$uploadInfo = $upload->ImagesUpload($uploadConfig);
    						if($uploadInfo){
    							$actualFilesNum = count($uploadInfo);//实际上传成功数量
    							if ($actualFilesNum!=$filesNum){
    								$errorInfo['status'] = -1005;
    								$errorInfo['msg'] = "图片的上传数量与实际上传数量不相符，选择了".$filesNum."张，上传成功了".$actualFilesNum."张";
    								$errorInfo['message'] = "网络繁忙，请稍后再试";
    								$this->retError($errorInfo);
    							}else{
    								$data['com_id'] = $com_id;//发布人企业ID
    								$data['hr_id'] = $user_id;//发布人HR的ID
    								$data['title'] = $title;//话题标题
    								$data['content'] = $content;//内容
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
    								$data['comment_num'] = 0;//评论累计，初始化为0
    								$data['praise_num'] = 0;//点赞累计(暂时没有点赞功能，保留字段)
    								$data['sort_num'] = $this->getSortNum($com_id);//排序
    								$data['status'] = C('DATA_STATUS')[1]['key'];
    								$data['ls_status'] = C('LS_STATUS')[1]['key'];
    								$data['create_time'] = date('Y-m-d H:i:s');
    								$data['update_time'] = date('Y-m-d H:i:s');
    							}
    						}else{
    							$errorInfo['status'] = -1006;
    							$errorInfo['msg'] = "图片的上传失败";
    							$errorInfo['message'] = "网络暂忙，请重新发布";
    							$this->retError($errorInfo);
    						}
    					}else{
    						$data['com_id'] = $com_id;//发布人企业ID
    						$data['hr_id'] = $user_id;//发布人HR的ID
    						$data['title'] = $title;//话题标题
    						$data['content'] = $content;//内容
    						$data['comment_num'] = 0;//评论累计，初始化为0
    						$data['praise_num'] = 0;//点赞累计(暂时没有点赞功能，保留字段)
    						$data['sort_num'] = $this->getSortNum($com_id);//排序
    						$data['status'] = C('DATA_STATUS')[1]['key'];
    						$data['ls_status'] = C('LS_STATUS')[1]['key'];
    						$data['create_time'] = date('Y-m-d H:i:s');
    						$data['update_time'] = date('Y-m-d H:i:s');
    					}
    					if (empty($data)){
    						$errorInfo['status'] = -1007;
    						$errorInfo['msg'] = "数据设置失败，data为空";
    						$errorInfo['message'] = "网络繁忙，请稍后再试";
    						$this->retError($errorInfo);
    					}else{
    						$Service = new TopicService();
    						$boolData = $Service->addTopic($data);
    						if($boolData){
    							$successInfo['status'] = 1000;
    							$successInfo['message'] = "发布成功";
    							$successInfo['data'] = array('key'=>$boolData);
    							$this->retSuccess($successInfo);
    						}else{
    							$errorInfo['status'] = -1008;
    							$errorInfo['msg'] = "数据写入失败";
    							$errorInfo['message'] = "网络繁忙，请稍后再试";
    							$this->retError($errorInfo);
    						}
    					}
    				}else{
    					$errorInfo['status'] = -1004;
	    				$errorInfo['msg'] = "非HR没有该功能的操作权限";
	    				$errorInfo['message'] = "服务繁忙，非法操作";
	    				$this->retError($errorInfo);
    				}
    			}
    		}
    	}
    }//publish	END
   
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_帖子_获取排序数字
     * @author:	lihongqiang	2017-03-20 17:57
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getSortNum	BEGIN
    protected function getSortNum($com_id){
    	$Service = new TopicService();
    	$where['com_id'] = $com_id;
    	$count = $Service->countTopic($where);
    	return $count+1;
    }	//getSortNum END

    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_帖子列表展示（首页）
     * @author:	lihongqiang	2017-03-20 17:57
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//index	BEGIN
    public function index(){
    	//$_SESSION['com_id'] = '1467166836740';////////////////////////////////
    	$com_id = $_SESSION['com_id'];
    	$user_id = $_SESSION['user_id'];
    	if (empty($com_id)||empty($user_id)){
    		$errorInfo['status'] = -1001;
    		$errorInfo['msg'] = "登录异常，参数user_id或com_id缺失";
    		$errorInfo['message'] = "登录过期了";
    		$this->retError($errorInfo);
    	}else{
    		$where['com_id'] = $com_id;
    		$where['status'] = C('DATA_STATUS')[1]['key'];
    		$where['ls_status'] = C('LS_STATUS')[1]['key'];
    		$Service = new TopicService();
    		$topicList = $Service->selectTopic($where);
    		$CirService = new CircleService();
    		if(empty($topicList)){
    			$topicList = "";
    		}else{
    			foreach ($topicList as &$V){
    				if($V['images1']){
    					$V['images1'] = C('IMAGES_PREFIX').$V['images1'];
    				}else{
    					$V['images1'] = '';
    				}
    				if($V['images2']){
    					$V['images2'] = C('IMAGES_PREFIX').$V['images2'];
    				}else{
    					$V['images2'] = '';
    				}
    				if($V['images3']){
    					$V['images3'] = C('IMAGES_PREFIX').$V['images3'];
    				}else{
    					$V['images3'] = '';
    				}
    				if($V['images4']){
    					$V['images4'] = C('IMAGES_PREFIX').$V['images4'];
    				}else{
    					$V['images4'] = '';
    				}
    				if($V['images5']){
    					$V['images5'] = C('IMAGES_PREFIX').$V['images5'];
    				}else{
    					$V['images5'] = '';
    				}
    				if($V['images6']){
    					$V['images6'] = C('IMAGES_PREFIX').$V['images6'];
    				}else{
    					$V['images6'] = '';
    				}
    				if($V['images7']){
    					$V['images7'] = C('IMAGES_PREFIX').$V['images7'];
    				}else{
    					$V['images7'] = '';
    				}
    				if($V['images8']){
    					$V['images8'] = C('IMAGES_PREFIX').$V['images8'];
    				}else{
    					$V['images8'] = '';
    				}
    				if($V['images9']){
    					$V['images9'] = C('IMAGES_PREFIX').$V['images9'];
    				}else{
    					$V['images9'] = '';
    				}
    				if($V['hr_id'] ==$user_id){
    					$V['is_Ipublish'] = true;//标识为自己发布的
    				}else{
    					$V['is_Ipublish'] = false;
    				}
    				$V['createTime'] = wordTime($V['create_time']);//处理发布时间
    				$userInfo = $CirService->getUserInfo($V['hr_id'],'username');
    				$V['userName'] = $userInfo['username'];
    		
    				//查部门名称(传user_id,返回字符串)
    				$V['department'] = $CirService->getDepartment();
    				////查参与的讨论内容
    				////$discussData = $this->getDiscuss($V['id']);
    				////$V['discussCount'] = count($discussData);//参与数量
    				////$V['discussData'] = $discussData;
    				$optimumDiscuss = $this->optimumDiscuss($V['id']);//查询各自的最佳回答
    				if($optimumDiscuss){
    					$V['optimumDiscuss'] = $optimumDiscuss;//最佳回答
    				}else{
    					$V['optimumDiscuss'] ='';//暂时没有最佳回答
    				}
    			}
    		}
    		//判断当前浏览人（即当前登录人）是否是HR(如果是HR，页面出现发布按钮)
    		$is_HR = $CirService->IS_HR($user_id);
    		if($is_HR){
    			$data['is_HR'] = 1;
    		}else{
    			$data['is_HR'] = 0;
    		}
    		$successInfo['status'] = 1000;
    		if(!$topicList){
    			$successInfo['message'] = "HR暂时没有发布过话题";
    		}else{
    			$successInfo['message'] = "话题列表返回成功";
    		}
    		$data['topicList'] = $topicList;
    		$successInfo['data'] = $data;
    		$this->retSuccess($successInfo);
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_HR话题详情
     * @author:	lihongqiang	2017-03-16 09:40
     * @method:	POST
     * @param int topic_id
     * @return: array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//detail	BEGIN
    public function detail(){
    	$postData = I('post.');
    	if(empty($postData)){
    		$errorInfo['status'] = -1001;
    		$errorInfo['msg'] = "参数为空，没有提交任何数据";
    		$errorInfo['message'] = "服务繁忙，请稍后再试";
    		$this->retError($errorInfo);
    	}else{
    		$com_id = $_SESSION['com_id'];
    		$user_id = $_SESSION['user_id'];
    		$topic_id = $postData['topic_id'];
    		if(empty($com_id)||empty($user_id)||empty($topic_id)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数topic_id为空或登录失效";
    			$errorInfo['message'] = "服务繁忙，请稍后再试";
    			$this->retError($errorInfo);
    		}else{
    			$where['id'] = $topic_id;
    			$where['status'] = C('DATA_STATUS')[1]['key'];
    			$where['ls_status'] = C('LS_STATUS')[1]['key'];
    			$Service = new TopicService();
    			$findTopic = $Service->findTopic($where);//获取这条数据
    			if($findTopic['images1']){
    				$findTopic['images1'] = C('IMAGES_PREFIX').$findTopic['images1'];
    			}else{
    				$findTopic['images1'] = '';
    			}
    			if($findTopic['images2']){
    				$findTopic['images2'] = C('IMAGES_PREFIX').$findTopic['images2'];
    			}else{
    				$findTopic['images2'] = '';
    			}
    			if($findTopic['images3']){
    				$findTopic['images3'] = C('IMAGES_PREFIX').$findTopic['images3'];
    			}else{
    				$findTopic['images3'] = '';
    			}
    			if($findTopic['images4']){
    				$findTopic['images4'] = C('IMAGES_PREFIX').$findTopic['images4'];
    			}else{
    				$findTopic['images4'] = '';
    			}
    			if($findTopic['images5']){
    				$findTopic['images5'] = C('IMAGES_PREFIX').$findTopic['images5'];
    			}else{
    				$findTopic['images5'] = '';
    			}
    			if($findTopic['images6']){
    				$findTopic['images6'] = C('IMAGES_PREFIX').$findTopic['images6'];
    			}else{
    				$findTopic['images6'] = '';
    			}
    			if($findTopic['images7']){
    				$findTopic['images7'] = C('IMAGES_PREFIX').$findTopic['images7'];
    			}else{
    				$findTopic['images7'] = '';
    			}
    			if($findTopic['images8']){
    				$findTopic['images8'] = C('IMAGES_PREFIX').$findTopic['images8'];
    			}else{
    				$findTopic['images8'] = '';
    			}
    			if($findTopic['images9']){
    				$findTopic['images9'] = C('IMAGES_PREFIX').$findTopic['images9'];
    			}else{
    				$findTopic['images9'] = '';
    			}
    			if($findTopic['hr_id'] == $user_id){
    				$findTopic['is_Ipublish'] = true;//标识为自己发布的
    			}else{
    				$findTopic['is_Ipublish'] = false;
    			}
    			$findTopic['createTime'] = wordTime($findTopic['create_time']);//处理发布时间
    			$CirService = new CircleService();
    			$userInfo = $CirService->getUserInfo($findTopic['hr_id'],'username');
    			$findTopic['userName'] = $userInfo['username'];
    			//查部门名称(传user_id,返回字符串)
    			$findTopic['department'] = $CirService->getDepartment();
    			//查讨论列表
    			$discussList = $this->getDiscussList($findTopic['id']);
    			if($discussList){
    				$findTopic['discussList'] =$discussList;
    			}else{
    				$findTopic['discussList'] = "";
    			}
    			//判断当前浏览人（即当前登录人）是否是HR(如果是HR，页面出现发布按钮)
    			$CirService = new CircleService();
    			$is_HR = $CirService->IS_HR($user_id);
    			if($is_HR){
    				$data['is_HR'] = 1;
    			}else{
    				$data['is_HR'] = 0;
    			}
    			$successInfo['status'] = 1000;
    			$successInfo['message'] = "话题内容返回成功";
    			$data['topicData'] = $findTopic;
    			$successInfo['data'] = $data;
    			$this->retSuccess($successInfo);
    		}
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	返回某个话题的最佳讨论
     * @method:	传参请求
     * @param int $Topic_id
     * @return: array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//optimumDiscuss BEGIN
     protected function optimumDiscuss($topic_id){
     	if(empty($topic_id)){
     		return false;
     	}else{
     		$where['topic_id']= $topic_id;
     		$where['optimum'] = 1;//等于1是最佳的那条讨论
     		$where['status'] = C('DATA_STATUS')[1]['key'];
     		$where['ls_status'] = C('LS_STATUS')[1]['key'];
     		$Service = new DiscussService();
     		$findDiscuss = $Service->findDiscuss($where);
     		if(!empty($findDiscuss)){
     			if($findDiscuss['images1']){
     				$findDiscuss['images1'] = C('IMAGES_PREFIX').$findDiscuss['images1'];
     			}else{
     				$findDiscuss['images1'] = '';
     			}
     			if($findDiscuss['images2']){
     				$findDiscuss['images2'] = C('IMAGES_PREFIX').$findDiscuss['images2'];
     			}else{
     				$findDiscuss['images2'] = '';
     			}
     			if($findDiscuss['images3']){
     				$findDiscuss['images3'] = C('IMAGES_PREFIX').$findDiscuss['images3'];
     			}else{
     				$findDiscuss['images3'] = '';
     			}
     			if($findDiscuss['images4']){
     				$findDiscuss['images4'] = C('IMAGES_PREFIX').$findDiscuss['images4'];
     			}else{
     				$findDiscuss['images4'] = '';
     			}
     			if($findDiscuss['images5']){
     				$findDiscuss['images5'] = C('IMAGES_PREFIX').$findDiscuss['images5'];
     			}else{
     				$findDiscuss['images5'] = '';
     			}
     			if($findDiscuss['images6']){
     				$findDiscuss['images6'] = C('IMAGES_PREFIX').$findDiscuss['images6'];
     			}else{
     				$findDiscuss['images6'] = '';
     			}
     			if($findDiscuss['images7']){
     				$findDiscuss['images7'] = C('IMAGES_PREFIX').$findDiscuss['images7'];
     			}else{
     				$findDiscuss['images7'] = '';
     			}
     			if($findDiscuss['images8']){
     				$findDiscuss['images8'] = C('IMAGES_PREFIX').$findDiscuss['images8'];
     			}else{
     				$findDiscuss['images8'] = '';
     			}
     			if($findDiscuss['images9']){
     				$findDiscuss['images9'] = C('IMAGES_PREFIX').$findDiscuss['images9'];
     			}else{
     				$findDiscuss['images9'] = '';
     			}
     			$CirService = new CircleService(); 
     			$userInfo = $CirService->getUserInfo($findDiscuss['user_id'],'username');
     			$toUserInfo = $CirService->getUserInfo($findDiscuss['to_user_id'],'username');
     			$findDiscuss['userName'] = $userInfo['username'];
     			$findDiscuss['toUserName'] = $toUserInfo['username'];
     			$findDiscuss['createTime'] = wordTime($findDiscuss['create_time']);
     			return $findDiscuss;
     		}else{
     			return null;
     		}
     	}
     }	//optimumDiscuss END
    
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	返回单条帖子内容的评论
      * @author:	lihongqiang	2017-03-20	PM
      * @method:	传参请求
      * @param int $Topic_id
      * @return: array2
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//getComment	BEGIN
     protected function getDiscussList($topic_id){
     	if(empty($topic_id)){
     		return false;
     	}else{
     		$where['topic_id']= $topic_id;
     		$where['status'] = C('DATA_STATUS')[1]['key'];
     		$where['ls_status'] = C('LS_STATUS')[1]['key'];
     		$Service = new DiscussService();
     		$discussData = $Service->selectDiscuss($where,$field='');
     		if($discussData){
     			foreach ($discussData as &$V){
     				if($V['images1']){
     					$V['images1'] = C('IMAGES_PREFIX').$V['images1'];
     				}else{
     					$V['images1'] = '';
     				}
     				if($V['images2']){
     					$V['images2'] = C('IMAGES_PREFIX').$V['images2'];
     				}else{
     					$V['images2'] = '';
     				}
     				if($V['images3']){
     					$V['images3'] = C('IMAGES_PREFIX').$V['images3'];
     				}else{
     					$V['images3'] = '';
     				}
     				if($V['images4']){
     					$V['images4'] = C('IMAGES_PREFIX').$V['images4'];
     				}else{
     					$V['images4'] = '';
     				}
     				if($V['images5']){
     					$V['images5'] = C('IMAGES_PREFIX').$V['images5'];
     				}else{
     					$V['images5'] = '';
     				}
     				if($V['images6']){
     					$V['images6'] = C('IMAGES_PREFIX').$V['images6'];
     				}else{
     					$V['images6'] = '';
     				}
     				if($V['images7']){
     					$V['images7'] = C('IMAGES_PREFIX').$V['images7'];
     				}else{
     					$V['images7'] = '';
     				}
     				if($V['images8']){
     					$V['images8'] = C('IMAGES_PREFIX').$V['images8'];
     				}else{
     					$V['images8'] = '';
     				}
     				if($V['images9']){
     					$V['images9'] = C('IMAGES_PREFIX').$V['images9'];
     				}else{
     					$V['images9'] = '';
     				}
     				if($V['is_anonymity']!=1){//匿名讨论了
     					$V['userName'] = C('USER_ANONYMITY_NAME');
     					$V['userflag'] = 'Y';//有可能会员昵称也叫匿名人,所以添加一个标识，用于显示的时候专门把匿名评论的人设置为其它颜色
     				}else{
     					$V['userflag'] = 'N';
     					$Service2 = new CircleService();
     					$userInfo = $Service2->getUserInfo($V['user_id'],'username');
     					$V['userName'] = $userInfo['username'];
     					if(!empty($V['to_user_id']) && $V['to_user_id']!=0){
     						$toUserInfo = $Service2->getUserInfo($V['to_user_id'],'username');
     						$V['toUserName'] = $toUserInfo['username'];
     					}
     				}
     				//查询当前登录人是否对该讨论点过赞
     				$isPraise = $this->getUserIsPraise($topic_id, $V['id']);
     				if($isPraise){
     					$V['isPraise'] = '1';
     				}else{
     					$V['isPraise'] = "0";
     				}
     				//查点赞列表（暂时不需要，需要时开启即可）
     				//$duscussPraiseList = $this->getDiscussPraiseList($topic_id,$V['id']);
     				//if($duscussPraiseList){
     				//	$V['praiseList'] = $duscussPraiseList;
     				//}else{
     				//	$V['praiseList'] = "";
     				//}
     				//查讨论的评论
     				$duscussCommentList = $this->getDiscussComment($V['id']);
     				if($duscussCommentList){
     					$V['commentList'] = $duscussCommentList;
     				}else{
     					$V['commentList'] = "";
     				}
     			}
     			return $discussData;
     		}else{
     			return false;
     		}
     	}
     }	//getComment	END
     
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	查询当前登录人是否对该话题的某个讨论内容点过赞
      * @author:	lihongqiang	2017-05-05 15:40
      * @method:	传参请求
      * @param: int	$topic_id ;int	$topic_discuss_id
      * @return: false or null or array2
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//getDiscussPraise	BEGIN
     protected function getUserIsPraise($topic_id,$topic_discuss_id){
     	if(empty($topic_id)||empty($topic_discuss_id)){
     		return false;
     	}else{
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
     	}
     }
     
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	返回单个讨论内容的点赞列表
      * @author:	lihongqiang	2017-05-05 15:40
      * @method:	传参请求
      * @param:	int	$topic_id ;int	$topic_discuss_id
      * @return: false or null or array2
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//getDiscussPraise	BEGIN
     protected function getDiscussPraiseList($topic_id,$topic_discuss_id){
     	if(empty($topic_id)||empty($topic_discuss_id)){
     		return false;
     	}else{
     		$where['topic_id'] = $topic_id;
     		$where['topic_discuss_id'] = $topic_discuss_id;
     		$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
     		$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
     		$field = 'id,topic_id,topic_discuss_id,user_id';
     		$Service = new DiscussService();
     		$praiseList = $Service->selectPraise($where,$field);
     		if(empty($praiseList)){
     			return null;
     		}else{
     			return $praiseList;
     		}
     	}
     }
     
    
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	返回单个讨论内容的评论列表
      * @author:	lihongqiang	2017-03-16 09:40
      * @method:	传参请求
      * @param int $topic_discuss_id
      * @return: array2
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//getComment	BEGIN
     protected function getDiscussComment($topic_discuss_id){
     	if(empty($topic_discuss_id)){
     		return false;
     	}else{
     		$where['topic_discuss_id']= $topic_discuss_id;
     		$where['status'] = C('DATA_STATUS')[1]['key'];
     		$where['ls_status'] = C('LS_STATUS')[1]['key'];
     		$Service = new DiscusscommentService();
     		$commentList = $Service->selectComment($where,$field='');
     		if($commentList){
     			$Service2 = new CircleService();
     			foreach ($commentList as &$V){
     				if($V['is_anonymity']!=1){//匿名评论了
     					$V['userName'] = C('USER_ANONYMITY_NAME');
     					$V['userflag'] = 'Y';//有可能会员昵称也叫匿名人,所以添加一个标识，用于显示的时候专门把匿名评论的人设置为其它颜色
     				}else{
     					$V['userflag'] = 'N';
     					$userInfo = $Service2->getUserInfo($V['user_id'],'username');
     					$V['userName'] = $userInfo['username'];
     				}
     				
     				if(!empty($V['to_user_id']) && $V['to_user_id']!=0){
     					//回复
     					if($V['father_id']!=0){
     						$where1['id'] = $V['father_id'];
     						//$where['status'] = C('DATA_STATUS')[1]['key'];
     						//$where['ls_status'] = C('LS_STATUS')[1]['key'];
     						$huifu = $Service->findComment($where1,'');
     						if($huifu['is_anonymity']!=1){//上一条内容即点击的那条数据内容发布时匿名了，因此之后基于那条数据发布的回复都不能出现实名
     							$V['toUserName'] = C('USER_ANONYMITY_NAME');
     							$V['toUserflag'] = 'Y';
     						}else{
     							$toUserInfo = $Service2->getUserInfo($V['to_user_id'],'username');
     							$V['toUserflag'] = 'N';
     							$V['toUserName'] = $toUserInfo['username'];
     						}
     					}else{
     						$toUserInfo = $Service2->getUserInfo($V['to_user_id'],'username');
     						$V['toUserflag'] = 'N';
     						$V['toUserName'] = $toUserInfo['username'];
     					}
     				}	
     			}
     			return $commentList;
     		}else{
     			return false;
     		}
     	}
     }	//getComment	END
     
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	企业圈_HR话题详情
      * @author:	lihongqiang	2017-03-16 09:40
      * @method:	POST
      * @param:		int topic_id
      * @return: array1
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//remove	BEGIN
     public function remove(){
     	$postData = I('post.');
     	if(empty($postData)){
     		$errorInfo['status'] = -1001;
     		$errorInfo['msg'] = "参数为空，没有提交任何数据";
     		$errorInfo['message'] = "服务繁忙，请稍后再试";
     		$this->retError($errorInfo);
     	}else{
     		$com_id = $_SESSION['com_id'];
     		$user_id = $_SESSION['user_id'];
     		$topic_id = $postData['topic_id'];
     		if(empty($com_id)||empty($user_id)||empty($topic_id)){
     			$errorInfo['status'] = -1002;
     			$errorInfo['msg'] = "参数topic_id为空或登录失效";
     			$errorInfo['message'] = "服务繁忙，请稍后再试";
     			$this->retError($errorInfo);
     		}else{
     			$where['id'] = $topic_id;
     			$where['status'] = C('DATA_STATUS')[1]['key'];
     			$where['ls_status'] = C('LS_STATUS')[1]['key'];
     			$Service = new TopicService();
     			$findTopic = $Service->findTopic($where);//获取这条数据
     			if($findTopic){
     				if ($user_id==$findTopic['hr_id']){
     					$bool = $Service->removeTopic($topic_id);
     					if($bool){
     						$successInfo['status'] = 1000;
     						$successInfo['message'] = "删除成功";
     						$successInfo['data'] = array('key'=>$bool);
     						$this->retSuccess($successInfo);
     					}else{
     						$errorInfo['status'] = -1005;
     						$errorInfo['msg'] = "数据删除失败，topic_id:".$topic_id;
     						$errorInfo['message'] = "服务繁忙，删除失败";
     						$this->retError($errorInfo);
     					}
     				}else{
     					$errorInfo['status'] = -1004;
     					$errorInfo['msg'] = "非本人发布的数据没有权限删除";
     					$errorInfo['message'] = "亲，您没有权限删除非本人发布的数据内容哦";
     					$this->retError($errorInfo);
     				}
     			}else{
     				$errorInfo['status'] = -1003;
     				$errorInfo['msg'] = "要删除的数据不存在，可能已经删除或下线，topic_id:".$topic_id;
     				$errorInfo['message'] = "服务繁忙，请刷新重试";
     				$this->retError($errorInfo);
     			}
     		}
     	}
     }	//remove	END
     
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	企业圈_HR话题_置顶
      * @author:lihongqiang	2017-03-16 09:40
      * @method:POST
      * @param:	int topic_id
      * @return: array1
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//stick	BEGIN
     public function stick(){
     	$postData = I('post.');
     	if(empty($postData)){
     		$errorInfo['status'] = -1001;
     		$errorInfo['msg'] = "参数为空，没有提交任何数据";
     		$errorInfo['message'] = "服务繁忙，操作失败";
     		$this->retError($errorInfo);
     	}else{
     		$com_id = $_SESSION['com_id'];
     		$user_id = $_SESSION['user_id'];
     		$topic_id = $postData['topic_id'];
     		if(empty($topic_id)){
     			$errorInfo['status'] = -1002;
     			$errorInfo['msg'] = "重要参数缺失，topic_id没有提交";
     			$errorInfo['message'] = "服务繁忙，请稍后再试";
     			$this->retError($errorInfo);
     		}else{
     			if(empty($com_id)||empty($user_id)){
     				$errorInfo['status'] = -1003;
     				$errorInfo['msg'] = "session参数缺失，user_id或com_id为空";
     				$errorInfo['message'] = "服务繁忙，请稍后再试";
     				$this->retError($errorInfo);
     			}else{
     				$Service = new TopicService();
     				//$where['id'] = $topic_discuss_id;
     				$where['id'] = $topic_id;
     				$where['status'] = C('DATA_STATUS')[1]['key'];
     				$where['ls_status'] = C('LS_STATUS')[1]['key'];
     				$findTopic = $Service->findTopic($where,$field = '');
     				if($findTopic){
     					//开始置顶操作：把stick为1的更新为0，把该条需要置顶的数据改为1
     					$bool = $this->setStick($topic_id);
     					if($bool){
     						$successInfo['status'] = 1000;
     						$successInfo['message'] = "置顶成功";
     						$successInfo['data'] = array('key'=>1);
     						$this->retSuccess($successInfo);
     					}else{
     						$errorInfo['status'] = -1005;
     						$errorInfo['msg'] = "置顶失败";
     						$errorInfo['message'] = "服务繁忙，置顶失败";
     						$this->retError($errorInfo);
     					}
     				}else{
     					$errorInfo['status'] = -1004;
     					$errorInfo['msg'] = "置顶的数据不存在，请检查是否已经下线或删除";
     					$errorInfo['message'] = "服务繁忙，请稍后再试";
     					$this->retError($errorInfo);
     				}
     			}
     		}
     	}
     }	//stick END
     
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	企业圈_HR话题_设置置顶
      * @author:	lihongqiang	2017-04-12 PM
      * @method:	传参请求
      * @param:	int topic_id
      * @return: boolean
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//stick	BEGIN
     protected function setStick($topic_id){
     	if($topic_id){
     		$transActionObj = M('');//事务对象
     		$transActionObj->startTrans();
     		$condition['com_id'] = $_SESSION['com_id'];
     		$condition['stick'] = 1;
     		$condition['status'] = C('DATA_STATUS')[1]['key'];
     		$condition['ls_status'] = C('LS_STATUS')[1]['key'];
     		$Service = new TopicService();
     		$topicList = $Service->findTopic($condition,$field = '');
     		if(empty($topicList)){
     			$flags = true;
     		}else{
     			$save1['stick'] = 0;
     			$save1['update_time'] = getNow();
     			$bool1 = $Service->updateTopic($condition, $save1);
     			if($bool1){
     				$flags = true;
     			}else{
     				$flags = false;
     			}
     		}
     		if($flags){
     			$where['id'] = $topic_id;
     			$where['status'] = C('DATA_STATUS')[1]['key'];
     			$where['ls_status'] = C('LS_STATUS')[1]['key'];
     			$save2['stick'] = 1;
     			$save2['update_time'] = getNow();
     			$bool2 = $Service->updateTopic($where, $save2);
     			if($bool2){
     				$transActionObj->commit();
     				return true;
     			}else{
     				$transActionObj->rollback();
     				return false;
     			}
     		}else{
     			return false;
     		}
     	}else{
     		return false;
     	}
     }
     
     
     
     /**
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      * @name:	企业圈_HR话题__取消置顶
      * @author:	lihongqiang	2017-04-12 PM
      * @method:	POST
      * @param:		int topic_id
      * @return: array1
      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
      */	//unstick	BEGIN
     public function unstick(){
     	$postData = I('post.');
     	if(empty($postData)){
     		$errorInfo['status'] = -1001;
     		$errorInfo['msg'] = "参数为空，没有提交任何数据";
     		$errorInfo['message'] = "服务繁忙，请稍后再试";
     		$this->retError($errorInfo);
     	}else{
     		$com_id = $_SESSION['com_id'];
     		$user_id = $_SESSION['user_id'];
     		$topic_id = $postData['topic_id'];
     		if(empty($com_id)||empty($user_id)||empty($topic_id)){
     			$errorInfo['status'] = -1002;
     			$errorInfo['msg'] = "参数topic_id为空或登录失效";
     			$errorInfo['message'] = "服务繁忙，请稍后再试";
     			$this->retError($errorInfo);
     		}else{
     			$where['id'] = $topic_id;
     			$where['status'] = C('DATA_STATUS')[1]['key'];
     			$where['ls_status'] = C('LS_STATUS')[1]['key'];
     			$Service = new TopicService();
     			$findTopic = $Service->findTopic($where);//获取这条数据
     			if($findTopic){
     				if($findTopic['stick']!=0){
     					$save['stick'] = 0;
     					$save['update_time'] = getNow();
     					$bool = $Service->updateTopic($where, $save);
     					if($bool){
     						$successInfo['status'] = 1000;
     						$successInfo['message'] = "取消成功";
     						$successInfo['data'] = array('key'=>1);
     						$this->retSuccess($successInfo);
     					}else{
     						$errorInfo['status'] = -1005;
     						$errorInfo['msg'] = "取消置顶失败了,topic_id:".$topic_id;
     						$errorInfo['message'] = "取消失败";
     						$this->retError($errorInfo);
     					}
     				}else{
     					$errorInfo['status'] = -1004;
     					$errorInfo['msg'] = "该条数据非置顶数据，无需取消置顶";
     					$errorInfo['message'] = "服务繁忙，请稍后刷新重试";
     					$this->retError($errorInfo);
     				}
     			}else{
     				$errorInfo['status'] = -1003;
     				$errorInfo['msg'] = "置顶的数据不存在，请检查是否已经下线或删除";
     				$errorInfo['message'] = "服务繁忙，请稍后再试";
     				$this->retError($errorInfo);
     			}
     		}
     	}
     }	//unstick	END
     
     
//     /**
//      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//      * @name:	返回单条帖子内容的讨论
//      * @author:	lihongqiang	2017-03-16 09:40
//      * @method:	传参请求
//      * @param int $Topic_id
//      * @return: array2
//      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//      */	//getComment	BEGIN
//     protected function getDiscuss($topic_id){
//     	if(empty($topic_id)){
//     		return false;
//     	}else{
//     		$where['topic_id']= $topic_id;
//     		$where['status'] = C('DATA_STATUS')[1]['key'];
//     		$where['ls_status'] = C('LS_STATUS')[1]['key'];
//     		$Service = new DiscussService();
//     		$discussData = $Service->selectDiscuss($where,$field='');
//     		if($discussData){
//     			foreach ($discussData as &$V){
//     				if($V['is_anonymity']!=1){//匿名评论了
//     					$V['userName'] = C('USER_ANONYMITY_NAME');
//     					$V['userflag'] = 'Y';//有可能会员昵称也叫匿名人,所以添加一个标识，用于显示的时候专门把匿名评论的人设置为其它颜色
//     				}else{
//     					$V['userflag'] = 'N';
//     					$Service2 = new CircleService();
//     					$userInfo = $Service2->getUserInfo($V['user_id'],'username');
//     					$V['userName'] = $userInfo['username'];
//     					if(!empty($V['to_user_id'])||$V['to_user_id']!=0){
//     						$toUserInfo = $Service2->getUserInfo($V['user_id'],'username');
//     						$V['toUserName'] = $toUserInfo['username'];
//     					}
//     				}
//     			}
//     			return $discussData;
//     		}else{
//     			return false;
//     		}
//     	}
//     }	//getComment	END

     
     
     
     
     
     
    
    
    
    
    
    
    


}