<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[CircleController.class.php];	
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

use Businesscircle\Service\CircleService;
use Businesscircle\Service\IndexService;
use Businesscircle\Service\CommentService;
use Common\Common\Classlib\UploadFile\UploadImages;
use Think\Controller;
class CircleController extends  RootController{
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈内容展示首页
	 * @author:	lihongqiang	2017-03-15 15:07
	 * @method:	POST
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//index	BEGIN
    public function index(){
    	$com_id = $_SESSION['com_id'];
    	$user_id = $_SESSION['user_id'];
    	$Service = new CircleService();
    	$where['com_id'] = $com_id;
    	$where['status'] = C('DATA_STATUS')[1]['key'];
    	$where['ls_status'] = C('LS_STATUS')[1]['key'];
    	//$field = 'id,com_id,user_id,department,content,images1,images2,images3,comment_num,favour_num,is_anonymity,status,create_time';
    	$CircleData = $Service->selectBusiness($where,$field='');
    	foreach ($CircleData as &$V){
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
    		if($V['user_id'] ==$user_id){
    			$V['is_Ipublish'] = true;//标识为自己发布的
    		}else{
    			$V['is_Ipublish'] = false;
    		}
    		$V['createTime'] = wordTime($V['create_time']);//处理发布时间
    		if($V['is_anonymity']!=1){//匿名发布了
    			$V['userName'] = C('USER_ANONYMITY_NAME');
    		}else{
    			$userInfo = $Service->getUserInfo($V['user_id'],'username');
    			$V['userName'] = $userInfo['username'];
    		}
    		//查部门名称(传user_id,返回字符串)
    		$V['department'] = $Service->getDepartment($V['user_id']);
    		//查询当前登录人是否对该讨论点过赞
    		$isPraise = $this->isPraise($V['id']);
    		if($isPraise){
    			$V['isPraise'] = '1';
    		}else{
    			$V['isPraise'] = "0";
    		}		
    		//查点赞数量
    		////$V['praiseCount']= $this->getPraiseNum($V['id']);
    		//查评论
    		$commentData = $this->getComment($V['id']);
    		////$V['commentCount'] = count($commentData);//评论数量
    		$V['commentData'] = $commentData;
    	}
//     	$is_HR = $Service->IS_HR($user_id);
//     	if($is_HR){
//     		$data['is_HR'] = 1;
//     	}else{
//     		$data['is_HR'] = 0;
//     	}
    	$successInfo['status'] = 1000;
    	$successInfo['message'] = "晒福利内容返回成功";
    	$data['CircleData'] = $CircleData;
    	$successInfo['data'] = $data;
    	$this->retSuccess($successInfo);
    }
        
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	返回单条圈子内容的评论
     * @author:	lihongqiang	2017-03-16 09:40
     * @method:	传参请求
     * @param int $businessId	
     * @return: array2   
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getComment	BEGIN
    protected function getComment($businesscircle_id){
    	if(empty($businesscircle_id)){
    		return false;
    	}else{
    		$where['businesscircle_id']= $businesscircle_id;
    		$where['status'] = C('DATA_STATUS')[1]['key'];
    		$where['ls_status'] = C('LS_STATUS')[1]['key'];
    		$Service = new CommentService();
    		$commentData = $Service->selectComment($where,$field='');
    		if($commentData){
    			foreach ($commentData as &$V){
    				if($V['is_anonymity']!=1){//匿名评论了
    					$V['userName'] = C('USER_ANONYMITY_NAME');
    					$V['userflag'] = 'Y';//有可能会员昵称也叫匿名人,所以添加一个标识，用于显示的时候专门把匿名评论的人设置为其它颜色
    				}else{
    					$Service2 = new CircleService();
    					$userInfo = $Service2->getUserInfo($V['user_id'],'username');
    					$V['userName'] = $userInfo['username'];
    					if(!empty($V['to_user_id'])||$V['to_user_id']!=0){
    						$toUserInfo = $Service2->getUserInfo($V['to_user_id'],'username');
    						$V['toUserName'] = $toUserInfo['username'];
    					}
    				}
    			}
    			return $commentData;
    		}else{
    			return '';
    		}
    	}
    }	//getComment	END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈内容发布
     * @author:	lihongqiang	2017-03-15 15:09
     * @method:	POST
     * @param:	content	images(可选)	is_anonymity
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
    		$is_anonymity = $postData['is_anonymity'];
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
    				if($_FILES){
    					$filesNum = count($_FILES);
    					$upload = new UploadImages();
    					//$uploadConfig['rootPath'] = './Public/';//根路径
    					$uploadConfig['savePath'] = 'UploadImages/Businesscircle/CircleContent/';//相对根路径
    					$uploadConfig['subName'] =  array('date','Ymd'); //子目录创建方式，采用数组或者字符串方式定义
    					$uploadInfo = $upload->ImagesUpload($uploadConfig);
    					if($uploadInfo){
    						$actualFilesNum = count($uploadInfo);//实际上传成功数量
    						if ($actualFilesNum!=$filesNum){
    							$errorInfo['status'] = -1005;
    							$errorInfo['msg'] = "图片的上传数量与实际上传数量不相符，选择了".$filesNum."张，上传成功了".$actualFilesNum."张";
    							$errorInfo['message'] = "服务繁忙，请重新发布";
    							$this->retError($errorInfo);
    						}else{
    							$data['com_id'] = $com_id;//发布人企业ID
    							$data['user_id'] = $user_id;//发布人ID
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
    							$data['comment_num'] = 0;//评论累计
    							$data['praise_num'] = 0;//点赞累计
    							if(empty($is_anonymity)){
    								$data['is_anonymity'] = 1;//是否匿名。1：公开(默认)；0：匿名
    							}else{
    								$data['is_anonymity'] = $is_anonymity;//是否匿名。1：公开；0：匿名
    							}
    							$data['status'] = C('DATA_STATUS')[1]['key'];
    							$data['ls_status'] = C('LS_STATUS')[1]['key'];
    							$data['create_time'] = getNow();
    							$data['update_time'] = getNow();
    						}
    					}else{
    						$errorInfo['status'] = -1004;
    						$errorInfo['msg'] = "图片的上传失败";
    						$errorInfo['message'] = "服务繁忙，请重新发布";
    						$this->retError($errorInfo);
    					}
    				}else{
    					$data['com_id'] = $com_id;//发布人企业ID
    					$data['user_id'] = $user_id;//发布人ID
    					$data['content'] = $content;//内容
    					$data['comment_num'] = 0;//评论累计
    					$data['praise_num'] = 0;//点赞累计
    					if(empty($is_anonymity)){
    						$data['is_anonymity'] = 1;//是否匿名。1：公开(默认)；0：匿名
    					}else{
    						$data['is_anonymity'] = $is_anonymity;//是否匿名。1：公开；0：匿名
    					}
    					$data['status'] = C('DATA_STATUS')[1]['key'];
    					$data['ls_status'] = C('LS_STATUS')[1]['key'];
    					$data['create_time'] = getNow();
    					$data['update_time'] = getNow();
    				}
    				$Service = new CircleService();
    				$boolData = $Service->addBusiness($data);
    				if($boolData){
    					$successInfo['status'] = 1000;
    					$successInfo['message'] = "发布成功";
    					$successInfo['data'] = array('key'=>$boolData);
    					$this->retSuccess($successInfo);
    				}else{
    					$errorInfo['status'] = -1003;
    					$errorInfo['msg'] = "数据写入失败";
    					$errorInfo['message'] = "服务繁忙，请稍后再试";
    					$this->retError($errorInfo);
    				}
    			}
    		}
    	}
    }//publish	END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	我的晒福利
     * @author:	lihongqiang	2016-03-17	AM
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//myCircle BEGIN
    public function myCircle(){
    	$com_id = $_SESSION['com_id'];
    	$user_id = $_SESSION['user_id'];
    	$Service = new CircleService();
    	$where['user_id'] = $user_id;
    	$where['com_id'] = $com_id;
    	$where['status'] = C('DATA_STATUS')[1]['key'];
    	$where['ls_status'] = C('LS_STATUS')[1]['key'];
    	//$field = 'id,com_id,user_id,department,content,images1,images2,images3,comment_num,favour_num,is_anonymity,status,create_time';
    	$CircleData = $Service->selectBusiness($where,$field='');
    	foreach ($CircleData as &$V){
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
    		if($V['is_anonymity']!=1){//匿名发布了
    			$V['userName'] = C('USER_ANONYMITY_NAME');
    		}else{
    			$userInfo = $Service->getUserInfo($V['user_id'],'username');
    			$V['userName'] = $userInfo['username'];
    		}
    		//查部门名称
    		//查询当前登录人是否对该讨论点过赞
    		$isPraise = $this->isPraise($V['id']);
    		if($isPraise){
    			$V['isPraise'] = '1';
    		}else{
    			$V['isPraise'] = "0";
    		}
    		//查点赞数量
    	
    		//查评论
    		$commentData = $this->getComment($V['id']);
    		////$V['commentCount'] = count($commentData);//评论数量
    		$V['commentData'] = $commentData;
    	
    	}
    	$successInfo['status'] = 1000;
    	$data['CircleData'] = $CircleData;
    	$successInfo['data'] = $data;
    	$successInfo['message'] = "我的晒福利内容返回成功";
    	if(empty($successInfo['data']['CircleData'])){
    		$successInfo['message'] = "您还没有晒过福利噢";
    	}
    	$this->retSuccess($successInfo);
    } //myCircle End
   
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	晒福利_详情页
     * @author:	lihongqiang	2016-03-17	PM
     * @method:	POST
     * @param:	businesscircle_id
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//detail BEGIN
    public function detail(){
    	$postData = I('post.');
    	if(empty($postData)){
    		$errorInfo['status'] = -1001;
    		$errorInfo['msg'] = "参数为空，没有提交任何数据";
    		$errorInfo['message'] = "服务繁忙，操作失败";
    		$this->retError($errorInfo);
    	}else{
    		$businesscircle_id = $postData['businesscircle_id'];
    		if(empty($businesscircle_id)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数businesscircle_id为空";
    			$errorInfo['message'] = "服务繁忙，操作失败";
    			$this->retError($errorInfo);
    		}else{
    			$CircleData = $this->getFindBusinesscircle($businesscircle_id);
    			if($CircleData){
    				$successInfo['status'] = 1000;
    				$successInfo['message'] = "晒福利详情内容数据返回成功";
    				$data['CircleData'] = $CircleData;
    				$successInfo['data'] = $data;
    				$this->retSuccess($successInfo);
    			}else{
    				$errorInfo['status'] = -1003;
    				$errorInfo['msg'] = "数据库没有找到这条数据";
    				$errorInfo['message'] = "服务繁忙，查看失败";
    				$this->retError($errorInfo);
    			}
    		}
    	}
    } //detail END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	返回单条圈子内容
     * @author:	lihongqiang	2017-03-20 11:30
     * @method:	传参请求
     * @param int $businesscircle_id
     * @return: array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getFindBusinesscircle	BEGIN
    protected function getFindBusinesscircle($businesscircle_id){
    	if(empty($businesscircle_id)){
    		return false;
    	}else{
    		$where['id'] = $businesscircle_id;//主键查询
    		$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
    		$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
    		$Service = new CircleService();
    		$findCircleData = $Service->findBusiness($where,$field='');
    		if($findCircleData){
    			if($findCircleData['images1']){
    				$findCircleData['images1'] = C('IMAGES_PREFIX').$findCircleData['images1'];
    			}else{
    				$findCircleData['images1'] = '';
    			}
    			if($findCircleData['images2']){
    				$findCircleData['images2'] = C('IMAGES_PREFIX').$findCircleData['images2'];
    			}else{
    				$findCircleData['images2'] = '';
    			}
    			if($findCircleData['images3']){
    				$findCircleData['images3'] = C('IMAGES_PREFIX').$findCircleData['images3'];
    			}else{
    				$findCircleData['images3'] = '';
    			}
    			if($findCircleData['images4']){
    				$findCircleData['images4'] = C('IMAGES_PREFIX').$findCircleData['images4'];
    			}else{
    				$findCircleData['images4'] = '';
    			}
    			if($findCircleData['images5']){
    				$findCircleData['images5'] = C('IMAGES_PREFIX').$findCircleData['images5'];
    			}else{
    				$findCircleData['images5'] = '';
    			}
    			if($findCircleData['images6']){
    				$findCircleData['images6'] = C('IMAGES_PREFIX').$findCircleData['images6'];
    			}else{
    				$findCircleData['images6'] = '';
    			}
    			if($findCircleData['images7']){
    				$findCircleData['images7'] = C('IMAGES_PREFIX').$findCircleData['images7'];
    			}else{
    				$findCircleData['images7'] = '';
    			}
    			if($findCircleData['images8']){
    				$findCircleData['images8'] = C('IMAGES_PREFIX').$findCircleData['images8'];
    			}else{
    				$findCircleData['images8'] = '';
    			}
    			if($findCircleData['images9']){
    				$findCircleData['images9'] = C('IMAGES_PREFIX').$findCircleData['images9'];
    			}else{
    				$findCircleData['images9'] = '';
    			}
    			$user_id = $_SESSION['user_id'];
    			if($findCircleData['user_id'] ==$user_id){
    				$findCircleData['is_Ipublish'] = true;//标识为自己发布的
    			}else{
    				$findCircleData['is_Ipublish'] = false;
    			}
    			
    			if($findCircleData['is_anonymity']!=1){//匿名发布了
    				$findCircleData['userName'] = C('USER_ANONYMITY_NAME');
    			}else{
    				$userInfo = $Service->getUserInfo($findCircleData['user_id'],'username');
    				$findCircleData['userName'] = $userInfo['username'];
    			}
    			//查部门名称(传user_id,返回字符串)
    			$findCircleData['department'] = $Service->getDepartment();
    			//查点赞数量
    			////$findCircleData['praiseCount']= $this->getPraiseNum($findCircleData['id']);
    			//查点赞的人
    			$praiseData = $this->getPraiseData($businesscircle_id);
    			//查询当前登录人是否对该讨论点过赞
    			$isPraise = $this->isPraise($businesscircle_id);
    			if($isPraise){
    				$findCircleData['isPraise'] = '1';
    			}else{
    				$findCircleData['isPraise'] = "0";
    			}
    			//处理发布时间
    			$findCircleData['createTime'] = wordTime($findCircleData['create_time']);//处理发布时间
    			//查评论
    			$commentData = $this->getComment($findCircleData['id']);
    			////$findCircleData['commentCount'] = count($commentData);//评论数量
    			$findCircleData['praiseData'] = $praiseData;
    			$findCircleData['commentData'] = $commentData;
    			return $findCircleData;
    		}else{
    			return false;
    		}
    	}
    }	//getFindBusinesscircle	END
    
    
    
    
    
    
    
    //____________________________内容区结束,点赞区开始____________________________
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	晒福利点赞或取消点赞
     * @author:	lihongqiang	2016-03-17	PM
     * @method:	POST
     * @param:	businesscircle_id
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
    		$businesscircle_id = $postData['businesscircle_id'];
    		if(empty($businesscircle_id)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数businesscircle_id为空";
    			$errorInfo['message'] = "服务繁忙，点赞失败";
    			$this->retError($errorInfo);
    		}else{
    			//检测是否点过赞
    			$IS_Praise = $this->isPraise($businesscircle_id);
    			if($IS_Praise){
    				//点过  ，取消点赞
    				$boolean = $this->removePraise($businesscircle_id);
    				if($boolean){
    					$successInfo['status'] = 1000;
    					$successInfo['message'] = "取消成功";
    					$successInfo['data'] = array('key'=>1);
    					$this->retSuccess($successInfo);
    				}else{
    					$errorInfo['status'] = -1004;
    					$errorInfo['msg'] = "点赞记录表数据删除失败或更新主表的累计点赞数量失败";
    					$errorInfo['message'] = "取消失败，服务繁忙";
    					$this->retError($errorInfo);
    				}
    			}else{
    				//没点过，开始点赞操作
    				$boolData = $this->setPraise($businesscircle_id);
    				if($boolData){
    					$successInfo['status'] = 1000;
    					$successInfo['message'] = "点赞成功";
    					$successInfo['data'] = array('key'=>$boolData);
    					$this->retSuccess($successInfo);
    				}else{
    					$errorInfo['status'] = -1003;
    					$errorInfo['msg'] = "写入点赞记录表或更新主表的累计点赞赞数量失败";
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
     * @param:	int $businesscircle_id
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//isPraise	BEGIN
    protected function isPraise($businesscircle_id){
    	$where['user_id'] = $_SESSION['user_id'];
    	$where['businesscircle_id'] = $businesscircle_id;
    	$field = 'id,businesscircle_id,user_id';
    	$Service = new CircleService();
    	$findPraise = $Service->findPraise($where,$field);
    	if($findPraise){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	写入点赞记录
     * @author:	lihongqiang	2017-03-17 14:38
     * @method:	传参请求
     * @param: int $businesscircle_id
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//setPraise	BEGIN
    protected function setPraise($businesscircle_id){
    	$transActionObj = M('');//事务对象
    	$transActionObj->startTrans();
    	$data['businesscircle_id'] = $businesscircle_id;
    	$data['user_id'] = $_SESSION['user_id'];
    	$data['status'] = C('DATA_STATUS')[1]['key'];
    	$data['ls_status'] = C('LS_STATUS')[1]['key'];
    	$data['create_time'] = getNow();
    	$data['update_time'] = getNow();
    	$Service = new CircleService();
    	$boolData = $Service->addPraise($data);
    	if($boolData){
    		//把主表的点赞数量+1
    		$bool = $Service->plusPraiseNum($businesscircle_id);
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
    }	//setPraise END
    
   
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	删除点赞记录
     * @author:	lihongqiang	2017-03-17 15:14
     * @method:	传参请求
     * @param:	int $businesscircle_id
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//removePraise	BEGIN
    protected function removePraise($businesscircle_id){
    	$transActionObj = M('');//事务对象
    	$transActionObj->startTrans();
    	$where['user_id'] = $_SESSION['user_id'];
    	$where['businesscircle_id'] = $businesscircle_id;
    	$Service = new CircleService();
    	$boolean = $Service->deletePraise($where);
    	if($boolean){
    		//把主表的点赞数量-1
    		$bool = $Service->subtractPraiseNum($businesscircle_id);
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
    
   
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	获取点赞数量
     * @author:	lihongqiang	2017-03-20 12:49
     * @method:	传参请求
     * @param:	int $businesscircle_id
     * @return: boolean int
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//removePraise	BEGIN
    protected function getPraiseNum($businesscircle_id){
    	$Service = new CircleService();
    	$where['businesscircle_id'] = $businesscircle_id;
    	$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
    	$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
    	$countPraiseNum = $Service->countPraise($where);
    	return $countPraiseNum;
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	获取点赞的列表
     * @author:	lihongqiang	2017-03-20 12:49
     * @method:	传参请求
     * @param:	int $businesscircle_id
     * @return: boolean int
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getPraiseData	BEGIN
    protected function getPraiseData($businesscircle_id){
    	$Service = new CircleService();
    	$where['businesscircle_id'] = $businesscircle_id;
    	$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
    	$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
    	$field = 'user_id,create_time';
    	$praiseData = $Service->selectPraise($where,$field);
    	if($praiseData){
    		foreach ($praiseData as &$V){
    			$V['createTime'] = wordTime($V['create_time']);//处理点赞时间
    			$userNameInfo = $Service->getUserInfo($V['user_id'],'username');
    			$V['userName'] = $userNameInfo['username'];
    		}
    		return $praiseData;
    	}else{
    		return null;
    	}
    }	//getPraiseData END
    
   
    
}