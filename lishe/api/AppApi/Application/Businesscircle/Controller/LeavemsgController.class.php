<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[LeavemsgController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-3-27 10:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Controller;
use Businesscircle\Service\CircleService;

use Businesscircle\Service\LeavemsgService;

use Businesscircle\Service\CommentService;
use Common\Common\Classlib\UploadFile\UploadImages;
use Common\Controller\RootController;
use Think\Controller;
class LeavemsgController extends RootController {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	
	 * @author:	lihongqiang	2017-03-15 15:07
	 * @method:	POST
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//index	BEGIN
    public function index(){
    	$this->retError();
    }
    
    
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_给HR留言_发布留言
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
    			$errorInfo['message'] = "亲，你还没填写留言内容";
    			$this->retError($errorInfo);
    		}else{
    			if(empty($com_id)||empty($user_id)){
    				$errorInfo['status'] = -1003;
    				$errorInfo['msg'] = "参数user_id或com_id缺失";
    				$errorInfo['message'] = "服务繁忙，请稍后再试";
    				$this->retError($errorInfo);
    			}else{
    				$CirService = new CircleService();
    				$IS_HR = $CirService->IS_HR($user_id);
    				if($IS_HR){
    					$errorInfo['status'] = -1004;
    					$errorInfo['msg'] = "HR没有使用发布私信功能的权限";
    					$errorInfo['message'] = "不能自己给自己发私信";
    					$this->retError($errorInfo);
    				}else{
    					$data['com_id'] = $com_id;//发布人企业ID
    					$data['user_id'] = $user_id;//发布人ID
    					$data['root_id'] = 0;//发布人ID
    					$data['content'] = $content;//内容
    					if(empty($is_anonymity)){
    						$data['is_anonymity'] = 1;//是否匿名。1：公开(默认)；0：匿名
    					}else{
    						$data['is_anonymity'] = $is_anonymity;//是否匿名。1：公开；0：匿名
    					}
    					$data['to_user_id'] = $this->getHRID($com_id);
    					$data['status'] = C('DATA_STATUS')[1]['key'];
    					$data['ls_status'] = C('LS_STATUS')[1]['key'];
    					$data['create_time'] = getNow();
    					$data['update_time'] = getNow();
    					$Service = new LeavemsgService();
    					$boolData = $Service->addLeavemsg($data);
    					if($boolData){
    						$successInfo['status'] = 1000;
    						$successInfo['message'] = "留言成功";
    						$successInfo['data'] = array('key'=>$boolData);
    						$this->retSuccess($successInfo);
    					}else{
    						$errorInfo['status'] = -1005;
    						$errorInfo['msg'] = "数据写入失败";
    						$errorInfo['message'] = "服务繁忙，请稍后再试";
    						$this->retError($errorInfo);
    					}
    				}
    			}
    		}
    	}
    }//publish	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	获取HR的id
     * @author:	lihongqiang	2016-03-27	PM
     * @method:	传参请求
     * @param:	string	$com_id;
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getHRID BEGIN
    private function getHRID($com_id){
    	if($com_id){
    		$where['com_id'] = $com_id;
    		$where['isHr'] = 1;
    		$Service = new LeavemsgService();
    		$hrInfo = $Service->findSysuserUserDeposit($where,$field='user_id,isHr');
    		if($hrInfo){
    			return $hrInfo['user_id'];
    		}else{
    			return false;
    		}
    	}else{
    		return false;
    	}
    }	//getHRID	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_给HR留言_HR回复留言
     * @author:	lihongqiang	2016-03-28	PM
     * @method:	POST
     * @param:	root_id	to_user_id	content	is_anonymity(非HR)	
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//reply BEGIN
    public function reply(){
    	$postData = I('post.');
    	if(empty($postData)){
    		$errorInfo['status'] = -1001;
    		$errorInfo['msg'] = "参数为空，没有提交任何数据";
    		$errorInfo['message'] = "服务繁忙，操作失败";
    		$this->retError($errorInfo);
    	}else{
    		$com_id = $_SESSION['com_id'];
    		$root_id = $postData['root_id'];
    		$user_id = $_SESSION['user_id'];
    		$content = $postData['content'];
    		$is_anonymity = $postData['is_anonymity'];
    		$to_user_id = $postData['to_user_id'];
    		if(empty($content)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数content为空";
    			$errorInfo['message'] = "亲，你还没填写留言内容";
    			$this->retError($errorInfo);
    		}else{
    			if(empty($com_id)||empty($user_id)||empty($to_user_id)||empty($root_id)){
    				$errorInfo['status'] = -1003;
    				$errorInfo['msg'] = "参数user_id或com_id缺失";
    				$errorInfo['message'] = "服务繁忙，请稍后再试";
    				$this->retError($errorInfo);
    			}else{
    				$data['com_id'] = $com_id;//发布人企业ID
    				$data['root_id'] = $root_id;//根ID
    				$data['user_id'] = $user_id;//发布人ID
    				$data['to_user_id'] = $to_user_id;//接收人ID
    				$data['content'] = $content;//内容
    				if(empty($is_anonymity)){
    					$data['is_anonymity'] = 1;//是否匿名。1：公开(默认)；0：匿名
    				}else{
    					$data['is_anonymity'] = $is_anonymity;//是否匿名。1：公开；0：匿名
    				}
    				$data['status'] = C('DATA_STATUS')[1]['key'];
    				$data['ls_status'] =C('LS_STATUS')[1]['key'];
    				$data['create_time'] = getNow();
    				$data['update_time'] = getNow();
    				$Service = new LeavemsgService();
    				$boolData = $Service->addLeavemsg($data);
    				if($boolData){
    					$successInfo['status'] = 1000;
    					$successInfo['message'] = "回复成功";
    					$successInfo['data'] = array('key'=>$boolData);
    					$this->retSuccess($successInfo);
    				}else{
    					$errorInfo['status'] = -1005;
    					$errorInfo['msg'] = "数据写入失败";
    					$errorInfo['message'] = "服务繁忙，请稍后再试";
    					$this->retError($errorInfo);
    				}	
    			}
    		}
    	}
    }	//reply END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_给HR留言_我的留言列表(非HR)
     * @author:	lihongqiang	2016-03-29	PM
     * @method:	POST
     * @param:	root_id	to_user_id	content	is_anonymity(非HR)
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//reply BEGIN
    public function myleavemsg(){
    	$com_id = $_SESSION['com_id'];
    	$user_id = $_SESSION['user_id'];
    	if($com_id && $user_id){
    		$where['com_id'] = $com_id;
    		$where['user_id'] = $user_id;
    		$where['root_id'] = 0;
    		$where['status'] = C('DATA_STATUS')[1]['key'];
    		$where['ls_status'] =C('LS_STATUS')[1]['key'];
    		$Service = new LeavemsgService();
    		$LeaveMsgList = $Service->selectLeavemsg($where,$field = '');
    		if(!empty($LeaveMsgList)){
    			$CirService = new CircleService();
    			foreach ($LeaveMsgList as &$V){
    				$where1['root_id'] = $V['id'];
    				$where1['com_id'] = $com_id;
    				$where1['status'] = C('DATA_STATUS')[1]['key'];
    				$where1['ls_status'] =C('LS_STATUS')[1]['key'];
    				$replyMsgList = $Service->selectLeavemsg($where1,$field = '');
    				foreach ($replyMsgList as &$X){
    					$findUser1 = $CirService->getUserInfo($X['user_id']);
    					$X['userName'] = $findUser1['username'];
    					$tofindUser1 = $CirService->getUserInfo($X['to_user_id']);
    					$X['toUserName'] = $tofindUser1['username'];
    				}
    				$findUser = $CirService->getUserInfo($V['user_id']);
    				$V['userName'] = $findUser['username'];
    				$tofindUser = $CirService->getUserInfo($V['to_user_id']);
    				$V['toUserName'] = $tofindUser['username'];
    				$V['replyMsgList'] = $replyMsgList;
    			}
    			$successInfo['status'] = 1000;
    			$successInfo['message'] = "留言列表信息返回成功";
    			$successInfo['data'] = $LeaveMsgList;
    		}else{
    			$successInfo['status'] = 1000;
    			$successInfo['message'] = "您暂时还没有相关留言信息";
    			$successInfo['data'] = '';
    		}
    		$this->retSuccess($successInfo);
    	}else{
    		$errorInfo['status'] = 0;
    		$errorInfo['msg'] = "com_id,user_id没有获取到";
    		$errorInfo['message'] = "服务繁忙，请重新登录";
    		$this->retError($errorInfo);
    	}
    }
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_给HR留言_HR的留言列表(HR)
     * @author:	lihongqiang	2016-03-29	PM
     * @method:	POST
     * @param:	root_id	to_user_id	content	is_anonymity(非HR)
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//reply BEGIN
    public function hrleavemsg(){
    	$com_id = $_SESSION['com_id'];
    	$user_id = $_SESSION['user_id'];
    	if($com_id && $user_id){
    		$where['com_id'] = $com_id;
    		$where['to_user_id'] = $user_id;
    		$where['root_id'] = 0;
    		$where['status'] = C('DATA_STATUS')[1]['key'];
    		$where['ls_status'] =C('LS_STATUS')[1]['key'];
    		$Service = new LeavemsgService();
    		$LeaveMsgList = $Service->selectLeavemsg($where,$field = '');
    		$CirService = new CircleService();
    		if(!empty($LeaveMsgList)){
    			foreach ($LeaveMsgList as &$V){
    				$where1['root_id'] = $V['id'];
    				$where1['com_id'] = $com_id;
    				$where1['status'] = C('DATA_STATUS')[1]['key'];
    				$where1['ls_status'] =C('LS_STATUS')[1]['key'];
    				$replyMsgList = $Service->selectLeavemsg($where1,$field = '');
    				foreach ($replyMsgList as &$X){
    					$findUser1 = $CirService->getUserInfo($X['user_id']);
    					$X['userName'] = $findUser1['username'];
    					$tofindUser1 = $CirService->getUserInfo($X['to_user_id']);
    					$X['toUserName'] = $tofindUser1['username'];
    				}
    				$findUser = $CirService->getUserInfo($V['user_id']);
    				$V['userName'] = $findUser['username'];
    				$tofindUser = $CirService->getUserInfo($V['to_user_id']);
    				$V['toUserName'] = $tofindUser['username'];
    				$V['replyMsgList'] = $replyMsgList;
    			}
    			$successInfo['status'] = 1000;
    			$successInfo['message'] = "留言列表信息返回成功";
    			$successInfo['data'] = $LeaveMsgList;
    		}else{
    			$successInfo['status'] = 1000;
    			$successInfo['message'] = "您暂时还没有相关留言信息";
    			$successInfo['data'] = '';
    		}
    		$this->retSuccess($successInfo);
    	}else{
    		$errorInfo['status'] = 0;
    		$errorInfo['msg'] = "com_id,user_id没有获取到";
    		$errorInfo['message'] = "服务繁忙，请重新登录";
    		$this->retError($errorInfo);
    	}
    }	//reply	END
    
   
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	留言详情
     * @author:	lihongqiang	2016-03-29	PM
     * @method:	POST
     * @param:	leavemsg_id
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
    		$leavemsg_id = $postData['leavemsg_id'];
    		if(empty($leavemsg_id)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数leavemsgid为空";
    			$errorInfo['message'] = "服务繁忙，查看失败";
    			$this->retError($errorInfo);
    		}else{
    			$where['id'] = $leavemsg_id;
    			$where['status'] = C('DATA_STATUS')[1]['key'];
    			$where['ls_status'] =C('LS_STATUS')[1]['key'];
    			$Service = new LeavemsgService();
    			$findLeaveMsg = $Service->findLeavemsg($where);
    			$CirService = new CircleService();
    			$findUser = $CirService->getUserInfo($findLeaveMsg['user_id']);
    			$findLeaveMsg['userName'] = $findUser['username'];
    			$tofindUser = $CirService->getUserInfo($findLeaveMsg['to_user_id']);
    			$findLeaveMsg['toUserName'] = $tofindUser['username'];
    			$where1['root_id'] = $leavemsg_id;
    			$where1['status'] = C('DATA_STATUS')[1]['key'];
    			$where1['ls_status'] =C('LS_STATUS')[1]['key'];
    			$replyMsgList = $Service->selectLeavemsg($where1,$field = '');
    			foreach ($replyMsgList as &$X){
    				$findUser1 = $CirService->getUserInfo($X['user_id']);
    				$X['userName'] = $findUser1['username'];
    				$tofindUser1 = $CirService->getUserInfo($X['to_user_id']);
    				$X['toUserName'] = $tofindUser1['username'];
    			}
    			$findLeaveMsg['replyMsgList'] = $replyMsgList;
    			$successInfo['status'] = 1000;
    			$successInfo['message'] = "留言详情返回成功";
    			$successInfo['data'] = $findLeaveMsg;
    			$this->retSuccess($successInfo);
    		}
    	}
    }	//detail	END
    
    
    
    
    
    
    
    
    
    
    
}