<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈投票接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[VoteController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-5-9 11:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Controller;
use Businesscircle\Service\CircleService;

use Businesscircle\Service\VoteService;

use Common\Common\Classlib\UploadFile\UploadImages;
use Common\Controller\RootController;
use Think\Controller;
class VoteController extends  RootController{
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_发布投票
     * @author:	lihongqiang	2017-05-08 17:07
     * @method:	POST
     * @param:	title images type is_anonymity deadline (可选参数)	
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
    		$publish_id = $_SESSION['user_id'];
    		$title = $postData['title'];//投票主题
    		$type = $postData['type'];//1单选；2多选
    		$deadline = $postData['deadline'];//截止时间
    		$is_anonymity = $postData['is_anonymity'];//0：匿名；1：实名
    		$option = $postData['option'];//选项（array）
    		if(empty($title)||empty($type)||empty($deadline)||empty($option)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数title或option或type或deadline为空";
    			$errorInfo['message'] = "亲，投票信息填写不完整";
    			$this->retError($errorInfo);
    		}else{
    			if(count($option)<2){
    				$errorInfo['status'] = -1003;
    				$errorInfo['msg'] = "选项个数不够，至少要有两个选项";
    				$errorInfo['message'] = "亲，至少要有两个选项";
    				$this->retError($errorInfo);
    			}else{
    				if(empty($com_id)||empty($publish_id)){
    					$errorInfo['status'] = -1004;
    					$errorInfo['msg'] = "参数user_id或com_id缺失";
    					$errorInfo['message'] = "服务繁忙，请稍后再试";
    					$this->retError($errorInfo);
    				}else{
    					//判断登录人是不是HR
    					$CirService = new CircleService();
    					$is_HR = $CirService->IS_HR($publish_id);
    					if($is_HR){
    						$data = array();
    						if($_FILES){
    							$filesNum = count($_FILES);
    							$upload = new UploadImages();
    							//$uploadConfig['rootPath'] = './Public/';//根路径
    							$uploadConfig['savePath'] = 'UploadImages/Businesscircle/Vote/';//相对根路径
    							$uploadConfig['subName'] = array('date','Ymd'); //子目录创建方式，采用数组或者字符串方式定义
    							$uploadInfo = $upload->ImagesUpload($uploadConfig);
    							if($uploadInfo){
    								$actualFilesNum = count($uploadInfo);//实际上传成功数量
    								if ($actualFilesNum!=$filesNum){
    									$errorInfo['status'] = -1006;
    									$errorInfo['msg'] = "图片的上传数量与实际上传数量不相符，选择了".$filesNum."张，上传成功了".$actualFilesNum."张";
    									$errorInfo['message'] = "网络繁忙，请稍后再试";
    									$this->retError($errorInfo);
    								}else{
    									$data['com_id'] = $com_id;//发布人企业ID
    									$data['publish_id'] = $publish_id;//发布人HR的ID
    									$data['title'] = $title;//投票主题
    									if($uploadInfo['images']){
    										$data['images'] = $uploadInfo['images']['imagesPath'];
    									}
    									$data['type'] = $type;//选择类型
    									$data['is_anonymity'] = $is_anonymity;//是否匿名
    									$data['deadline'] = $deadline;//截止时间
    								}
    							}else{
    								$errorInfo['status'] = -1007;
    								$errorInfo['msg'] = "图片的上传失败";
    								$errorInfo['message'] = "网络暂忙，请重新发布";
    								$this->retError($errorInfo);
    							}
    						}else{
    							$data['com_id'] = $com_id;//发布人企业ID
    							$data['publish_id'] = $publish_id;//发布人HR的ID
    							$data['title'] = $title;//投票主题
    							$data['type'] = $type;//选择类型
    							$data['is_anonymity'] = $is_anonymity;//是否匿名
    							$data['deadline'] = $deadline;//截止时间
    						}
    						if (empty($data)){
    							$errorInfo['status'] = -1008;
    							$errorInfo['msg'] = "数据设置失败，data为空";
    							$errorInfo['message'] = "网络繁忙，请稍后再试";
    							$this->retError($errorInfo);
    						}else{
    							$boolData = $this->setVote($data,$option);
    							if($boolData){
    								$successInfo['status'] = 1000;
    								$successInfo['message'] = "发布成功";
    								$successInfo['data'] = array('key'=>$boolData);
    								$this->retSuccess($successInfo);
    							}else{
    								$errorInfo['status'] = -1009;
    								$errorInfo['msg'] = "数据写入失败";
    								$errorInfo['message'] = "网络繁忙，请稍后再试";
    								$this->retError($errorInfo);
    							}
    						}
    					}else{
    						$errorInfo['status'] = -1005;
    						$errorInfo['msg'] = "非HR没有该功能的操作权限";
    						$errorInfo['message'] = "服务繁忙，非法操作";
    						$this->retError($errorInfo);
    					}
    				}
    			}
    		}
    	}
    }	//publish	END
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	写入投票记录（只需要传post必须的参数就可以）
     * @author:	lihongqiang	2017-03-17 14:38
     * @method:	@param array $data
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//setVote	BEGIN
    protected function setVote($data,$option){
    	$transActionObj = M('');//事务对象
    	$transActionObj->startTrans();
    	$com_id = $_SESSION['com_id'];
    	$data['join_num'] = 0;//参与累计，初始化为0
    	$data['sort_num'] = $this->getSortNum($com_id);//排序
    	$data['status'] = C('DATA_STATUS')[1]['key'];
    	$data['ls_status'] = C('LS_STATUS')[1]['key'];
    	$data['create_time'] = date('Y-m-d H:i:s');
    	$data['update_time'] = date('Y-m-d H:i:s');
    	$Service = new VoteService();
    	$boolData = $Service->addVote($data);//正式去写入
    	if($boolData){
    		for($i=0;$i<count($option);$i++){
    			$optiondata['vote_id'] = $boolData;
    			$optiondata['content'] = $option[$i];
    			$optiondata['status'] = C('DATA_STATUS')[1]['key'];
    			$optiondata['ls_status'] = C('LS_STATUS')[1]['key'];
    			$optiondata['create_time'] = date('Y-m-d H:i:s');
    			$optiondata['update_time'] = date('Y-m-d H:i:s');
    			$bool =  $Service->addVoteOption($optiondata);
    		}
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
    }	//setVote END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_帖子_获取排序数字
     * @author:	lihongqiang	2017-03-20 17:57
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getSortNum	BEGIN
    protected function getSortNum($com_id){
    	$Service = new VoteService();
    	$where['com_id'] = $com_id;
    	$count = $Service->countVote($where);
    	return $count+1;
    }	//getSortNum END
   

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_投票列表
     * @author:	lihongqiang	2017-05-08 17:07
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//index	BEGIN
    public function index(){
    	$com_id = $_SESSION['com_id'];
    	$user_id = $_SESSION['user_id'];
    	$Service = new VoteService();
    	$where['com_id'] = $com_id;
    	$where['status'] = C('DATA_STATUS')[1]['key'];
    	$where['ls_status'] = C('LS_STATUS')[1]['key'];
    	//$field = 'id,com_id,user_id,department,content,images1,images2,images3,comment_num,favour_num,is_anonymity,status,create_time';
    	$VoteData = $Service->selectVote($where,$field='');
    	if($VoteData){
    		foreach ($VoteData as &$V){
    			if($V['images']){
    				$V['images'] = C('IMAGES_PREFIX').$V['images'];
    			}else{
    				$V['images'] = '';
    			}
    			$dateTime = date('Y-m-d H:i:s');
    			if($dateTime<$V['deadline']){
    				$V['is_Finish'] = '0';
    			}else{
    				$V['is_Finish'] = '1';
    			}
    			$voteItYes = $this->voteItYes($V['id'],$user_id);//判断当前登录人是否已经参与过投票
    			if($voteItYes){
    				$V['voteItYes'] = '1';
    			}else{
    				$V['voteItYes'] = '0';
    			}
    			$optionContent = $this->getVoteOption($V['id']);//获取投票选项
    			$V['option'] = $optionContent;
    			if($V['publish_id'] ==$user_id){
    				$V['is_Ipublish'] = true;//标识为自己发布的
    			}else{
    				$V['is_Ipublish'] = false;
    			}
    			$V['createTime'] = wordTime($V['create_time']);//处理发布时间
    			$CirService = new CircleService();
    			$userInfo = $CirService->getUserInfo($V['publish_id'],'username');
    			$V['userName'] = $userInfo['username'];
    		}
    		//$CirService = new CircleService();
    		//$is_HR = $CirService->IS_HR($V['publish_id']);
    		//if($is_HR){
    		//	$data['is_HR'] = 1;
    		//}else{
    		//	$data['is_HR'] = 0;
    		//}
    		$successInfo['message'] = "投票列表内容返回成功";
    	}else{
    		$VoteData = '';//暂时没有投票数据
    		$successInfo['message'] = "暂时没有投票数据";
    	}
    	$successInfo['status'] = 1000;
    	$data['VoteData'] = $VoteData;
    	$successInfo['data'] = $data;
    	$this->retSuccess($successInfo);
    }	//index	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_投票列表_获取投票选项
     * @author:	lihongqiang	2017-03-16 10:45
     * @method:	传参请求
     * @param:	string $vote_id
     * @return: array
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getVoteOption	BEGIN//
	protected function getVoteOption($vote_id){
		if(empty($vote_id)){
			return false;
		}else{
			$Service = new VoteService();
			$where['vote_id'] = $vote_id;
			$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
			$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
			$voteOption = $Service->selectVoteOption($where,$field='id,content,accept_num');
			if($voteOption){
				return $voteOption;
			}else{
				return null;
			}
		}
    }	//getVoteOption	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_投票详情页
     * @author:	lihongqiang	2016-03-17	PM
     * @method:	POST
     * @param:	vote_id
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
    		$vote_id = $postData['vote_id'];
    		if(empty($vote_id)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数vote_id为空";
    			$errorInfo['message'] = "服务繁忙，操作失败";
    			$this->retError($errorInfo);
    		}else{
    			$VoteData = $this->getFindVote($vote_id);
    			if($VoteData){
    				$successInfo['status'] = 1000;
    				$successInfo['message'] = "晒福利详情内容数据返回成功";
    				$data['VoteData'] = $VoteData;
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
     * @name:	返回单条投票内容
     * @author:	lihongqiang	2017-03-20 11:30
     * @method:	传参请求
     * @param int $businesscircle_id
     * @return: array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getFindVote	BEGIN
    protected function getFindVote($vote_id){
    	if(empty($vote_id)){
    		return false;
    	}else{
    		$where['id'] = $vote_id;//主键查询
    		$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
    		$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
    		$Service = new VoteService();
    		$findVoteData = $Service->findVote($where,$field='');
    		if($findVoteData){
    			if($findVoteData['images']){
    				$findVoteData['images'] = C('IMAGES_PREFIX').$V['images'];
    			}else{
    				$findVoteData['images'] = '';
    			}
    			$dateTime = date('Y-m-d H:i:s');
    			if($dateTime<$findVoteData['deadline']){
    				$findVoteData['is_Finish'] = '0';
    			}else{
    				$findVoteData['is_Finish'] = '1';
    			}
    			$user_id = $_SESSION['user_id'];
    			$voteItYes = $this->voteItYes($vote_id,$user_id);//判断当前登录人是否已经参与过投票
    			if($voteItYes){
    				$findVoteData['voteItYes'] = '1';
    			}else{
    				$findVoteData['voteItYes'] = '0';
    			}
    			
    			$CirService = new CircleService();
    			$userInfo = $CirService->getUserInfo($findVoteData['publish_id'],'username');
    			$findVoteData['userName'] = $userInfo['username'];
    			//处理发布时间
    			$findVoteData['createTime'] = wordTime($findVoteData['create_time']);//处理发布时间
    			
    			if($findVoteData['publish_id'] == $user_id){
    				$findVoteData['is_Ipublish'] = '1';//标识为自己发布的
    				$findVoteData['joinVoteUser'] = $this->getJoinVoteUser($findVoteData['id'],null);//查询投票人
    			}else{
    				$findVoteData['is_Ipublish'] = '2';
    				if($findVoteData['is_anonymity']==1){
    					$findVoteData['joinVoteUser'] = $this->getJoinVoteUser($findVoteData['id']);//查询投票人
    				}else{
    					$findVoteData['joinVoteUser'] = '';//不等于1表示匿名投票，非发布人不能从选项进入投票列表，下方不展示投票人头像
    				}
    			}
    			
    			$optionContent = $this->getVoteOption($findVoteData['id']);
    			foreach ($optionContent as &$val){
    				$thisOptionItYes = $this->voteOptionItYes($vote_id,$val['id'],$user_id);//查看是否已经勾选了这个选项
    				if($thisOptionItYes){
    					$val['optionItYes'] = '1';
    				}else{
    					$val['optionItYes'] = '0';
    				}
    			}
    			$findVoteData['option'] = $optionContent;
    			return $findVoteData;
    		}else{
    			return null;
    		}
    	}
    }	//getFindVote	END

    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	返回单条投票内容
     * @author:	lihongqiang	2017-03-20 11:30
     * @method:	传参请求
     * @param int $businesscircle_id
     * @return: array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getFindVote	BEGIN
   	public function joinVote(){
   		$postData = I('post.');
   		if(empty($postData)){
   			$errorInfo['status'] = -1001;
   			$errorInfo['msg'] = "参数为空，没有提交任何数据";
   			$errorInfo['message'] = "服务繁忙，操作失败";
   			$this->retError($errorInfo);
   		}else{
   			$com_id = $_SESSION['com_id'];
   			$user_id = $_SESSION['user_id'];
   			$vote_id = $postData['vote_id'];//投票主题id
   			$option_id = $postData['option_id'];//投票选项id（array）
   			if(empty($vote_id)||empty($option_id)){
   				$errorInfo['status'] = -1002;
   				$errorInfo['msg'] = "参数vote_id或option_id为空";
   				if(empty($option_id)){
   					$errorInfo['message'] = "至少选择一个选项";
   				}else{
   					$errorInfo['message'] = "网络暂忙，稍后再试";
   				}
   				$this->retError($errorInfo);
   			}else{
   				if(empty($com_id)||empty($user_id)){
   					$errorInfo['status'] = -1003;
   					$errorInfo['msg'] = "参数com_id或user_id为空";
   					$errorInfo['message'] = "登录失效了";
   					$this->retError($errorInfo);
   				}else{
   					$voteItYes = $this->voteItYes($vote_id,$user_id);//先判断是否已经参与过投票了
   					if($voteItYes){
   						$errorInfo['status'] = -1004;
   						$errorInfo['msg'] = "已经参与过了，不能重复参与";
   						$errorInfo['message'] = "已经参与了";
   						$this->retError($errorInfo);
   					}else{
   						$voteData = $this->getFindVote($vote_id);//查看投票数据是否存在
   						if($voteData){
   							//数据存在//判断是单选还是多选
   							$boolData = false;//初始化
   							$flag = false;//设置一个参照变量
   							if(count($option_id)>1){
   								if($voteData['type']==1){
   									$flag = false;//说明该投票只能选择一个选项而用户提交了至少两个选项
   								}else{
   									$flag = true;
   								}
   							}else{
   								$flag = true;
   							}

   							if($flag){
   								for ($i=0;$i<count($option_id);$i++){
   									$voteOptionData = $this->getFindVoteOption($option_id[$i]);//查看投票选项是否存在
   									if($voteOptionData){
   										$boolData = $this->setVoteResult($vote_id,$voteOptionData['id'],$user_id);
   									}else{
   										continue;//结束本次循环
   										//break;//终止整个循环
   										$boolData = false;
   									}
   								}
   							}else{
   								//数据不存在
   								$errorInfo['status'] = -1006;
   								$errorInfo['msg'] = "该投票只能选择一个选项而用户提交了至少两个选项";
   								$errorInfo['message'] = "只能选择一个选项";
   								$this->retError($errorInfo);
   							}
   							if($boolData){
   								$successInfo['status'] = 1000;
   								$successInfo['message'] = "投票成功";
   								$successInfo['data'] = array('key'=>$boolData);
   								$this->retSuccess($successInfo);
   							}else{
   								$errorInfo['status'] = -1007;
   								$errorInfo['msg'] = "数据写入失败";
   								$errorInfo['message'] = "网络繁忙，投票失败";
   								$this->retError($errorInfo);
   							}
   						}else{
   							//数据不存在
   							$errorInfo['status'] = -1005;
   							$errorInfo['msg'] = "投票数据或选项数据不存在，可能已经删除下线，请检查参数是否正确传递，vote_id：$vote_id";
   							$errorInfo['message'] = "网络暂忙，请刷新重试";
   							$this->retError($errorInfo);
   						}
   					}
   				}
   			}
   		}
   	}
   	
   	
   	/**
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 * @name:	返回单条投票选项内容
   	 * @author:	lihongqiang	2017-05-10 15:30
   	 * @method:	传参请求
   	 * @param int $option_id
   	 * @return: boolean
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 */	////getFindVote	BEGIN	BEGIN
   	protected function getFindVoteOption($option_id){
   		if(empty($option_id)){
   			return false;
   		}else{
   			$where['id'] = $option_id;//主键查询
   			$where['status'] = C('DATA_STATUS')[1]['key'];//数据状态等于1为活跃状态
   			$where['ls_status'] = C('LS_STATUS')[1]['key'];//礼舍App后台数据状态必须等于1
   			$Service = new VoteService();
   			$findVoteOptionData = $Service->findVoteOption($where,$field='');
   			if($findVoteOptionData){
   				return $findVoteOptionData;
   			}else{
   				return false;
   			}
   		}
   	}	////getFindVote	BEGIN	BEGIN
   	
   	
   	/**
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 * @name:	写入投票结果
   	 * @author:	lihongqiang	2017-03-17 14:38
   	 * @method:	@param array $data
   	 * @return: bool boolean
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 */	//setVoteResult	BEGIN
   	protected function setVoteResult($vote_id,$option_id,$user_id){
   		$transActionObj = M('');//事务对象
   		$transActionObj->startTrans();
   		$data['vote_id'] = $vote_id;
   		$data['option_id'] = $option_id;
   		$data['user_id'] = $user_id;
   		$data['status'] = C('DATA_STATUS')[1]['key'];
   		$data['ls_status'] = C('LS_STATUS')[1]['key'];
   		$data['create_time'] = date('Y-m-d H:i:s');
   		$data['update_time'] = date('Y-m-d H:i:s');
   		$Service = new VoteService();
   		$boolData = $Service->addVoteResult($data);//正式去写入
   		if($boolData){
   			//投票表与选项表更新数量
   			$boolean1 = $Service->plusAcceptNum($vote_id,$option_id);//更新选项的认可数量
   			$boolean2 = $Service->plusJoinNum($vote_id);//更新投票主表的参选数量
   			if($boolean1 && $boolean2){
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
   	}	//setVoteResult END
   	
   	
   	/**
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 * @name:	查看是否已经参与过投票
   	 * @author:	lihongqiang	2017-05-10
   	 * @method:	@param $vote_id $user_id
   	 * @return: bool
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 */	//setVoteResult	BEGIN
   	protected function voteItYes($vote_id,$user_id){
   		$where['vote_id'] = $vote_id;
   		$where['user_id'] = $user_id;
   		$where['status'] = C('DATA_STATUS')[1]['key'];
   		$where['ls_status'] = C('LS_STATUS')[1]['key'];
   		$Service = new VoteService();
   		$findData = $Service->findVoteResult($where,$field='');//正式去写入
   		if($findData){
   			return true;
   		}else{
   			return false;
   		}
   	}	//setVoteResult END
   	
   	
   	/**
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 * @name:	查看是否已经勾选了这个选项
   	 * @author:	lihongqiang	2017-05-24
   	 * @method:	@param array $data
   	 * @return: bool
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 */	//voteOptionItYes	BEGIN
   	protected function voteOptionItYes($vote_id,$option_id,$user_id){
   		$where['vote_id'] = $vote_id;
   		$where['option_id'] = $option_id;
   		$where['user_id'] = $user_id;
   		$where['status'] = C('DATA_STATUS')[1]['key'];
   		$where['ls_status'] = C('LS_STATUS')[1]['key'];
   		$Service = new VoteService();
   		$findData = $Service->findVoteResult($where,$field='');
   		if($findData){
   			return true;
   		}else{
   			return false;
   		}
   	}	//voteOptionItYes END
   	
   	
   	/**
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 * @name:	企业圈_删除投票
   	 * @author:	lihongqiang	2016-03-17	PM
   	 * @method:	POST
   	 * @param:	vote_id boolean
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 */	//detail BEGIN
   	public function remove(){
   		$postData = I('post.');
   		if(empty($postData)){
   			$errorInfo['status'] = -1001;
   			$errorInfo['msg'] = "参数为空，没有提交任何数据";
   			$errorInfo['message'] = "服务繁忙，操作失败";
   			$this->retError($errorInfo);
   		}else{
   			$vote_id = $postData['vote_id'];
   			if(empty($vote_id)){
   				$errorInfo['status'] = -1002;
   				$errorInfo['msg'] = "参数vote_id为空";
   				$errorInfo['message'] = "服务繁忙，操作失败";
   				$this->retError($errorInfo);
   			}else{
   				$VoteData = $this->getFindVote($vote_id);
   				if($VoteData){
   					$Service = new VoteService();
   					$bool = $Service->removeVote($vote_id);
   					if($bool){
   						$successInfo['status'] = 1000;
   						$successInfo['message'] = "删除成功";
   						$data['key'] = $bool;
   						$successInfo['data'] = $data;
   						$this->retSuccess($successInfo);
   					}else{
   						$errorInfo['status'] = -1004;
   						$errorInfo['msg'] = "删除失败，即status字段更新失败";
   						$errorInfo['message'] = "服务繁忙，删除失败";
   						$this->retError($errorInfo);
   					}
   				}else{
   					$errorInfo['status'] = -1003;
   					$errorInfo['msg'] = "数据库没有找到这条数据，可能已经删除";
   					$errorInfo['message'] = "数据已经被删除了";
   					$this->retError($errorInfo);
   				}
   			}
   		}
   	} //detail END
   	
   	
   	/**
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 * @name:	获取参与投票的人
   	 * @author:	lihongqiang	2017-05-27
   	 * @method:	@param array $data
   	 * @return: bool
   	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   	 */	//voteOptionItYes	BEGIN
   	public function getJoinVoteUser($vote_id,$option_id=null){
   		if(empty($vote_id)){
   			return false;
   		}else{
   			$where['vote_id'] = $vote_id;
   			if(!empty($option_id)){
   				$where['option_id'] = $option_id;
   			}
   			//$where['user_id'] = $user_id;
   			$where['status'] = C('DATA_STATUS')[1]['key'];
   			$where['ls_status'] = C('LS_STATUS')[1]['key'];
   			$Service = new VoteService();
   			$JoinVoteUserData = $Service->selectVoteResult($where,$field='id,vote_id,option_id,user_id,create_time');
   			if($JoinVoteUserData){
   				$CirService = new CircleService();
   				foreach ($JoinVoteUserData as &$V){
   					//处理发布时间
   					$V['createTime'] = wordTime($V['create_time']);//处理发布时间
   					$userInfo = $CirService->getUserInfo($V['user_id'],'username');
   					$V['userName'] = $userInfo['username'];
   				}
   				return $JoinVoteUserData;
   			}else{
   				return false;
   			}
   		}
   	}
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
   	
}