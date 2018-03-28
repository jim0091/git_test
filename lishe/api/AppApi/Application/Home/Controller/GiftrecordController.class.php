<?php
/**
 * +----------------------------------------------------------------------------------
 * |@Category:		[礼物记接口];							@version:1.0
 * +----------------------------------------------------------------------------------
 * |@Namespace:		[Giftrecord/Controller/];
 * +----------------------------------------------------------------------------------
 * |@Name:			[IndexController.class.php];	
 * +----------------------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-19 15:06
 * +----------------------------------------------------------------------------------
 * |@Email:		<Angelljoy@sina.com>		@OverTime:	
 * +----------------------------------------------------------------------------------
 *  */
namespace Home\Controller;
use Think\Cache\Driver\Redis;

use Think\Controller;
class GiftrecordController extends Controller {
	
	public function __construct(){
		parent::__construct();
		$this->redis=new Redis();
		$token=I("post.token");
		if(empty($token)){
			$token=I("get.token");
		}
		if($token){
			$userInfo=$this->redis->get($token);
			if($userInfo){
				$this->uid=$userInfo['id'];
				$this->comId=$userInfo['comId'];
				$this->account=$userInfo['account'];
				$this->userName=$userInfo['userName'];
				$_SESSION['uid'] = $userInfo['id'];
				$_SESSION['comId'] = $userInfo['comId'];
				$_SESSION['account'] = $userInfo['account'];
				$_SESSION['userName'] = $userInfo['userName'];
				//                 $uid = $_SESSION['uid'];
			}
		}
	}
	
	/**
	 * 礼物记首页
	 */
    public function index(){
    	$this->redis=new Redis();
    	$token=I("post.token");
    	if(empty($token)){
    		$token=I("get.token");
    	}
    	if($token){
    		$userInfo=$this->redis->get($token);
    		if($userInfo){
    			$this->uid=$userInfo['id'];
    			$this->comId=$userInfo['comId'];
    			$this->account=$userInfo['account'];
    			$this->userName=$userInfo['userName'];
    		}
    	}
    	
    	$uid = $this->uid;
    	$giftrecordObj = M('giftrecord');
    	$where['status'] = 1;
    	$arrData = $giftrecordObj->where($where)->order('createTime desc')->limit()->select();
    	$accountObj = M('sysuser_account');
    	$account_userObj = M('sysuser_user');
    	foreach ($arrData as &$v){
    		$cond['user_id'] = $v['uid'];
    		$userData = $account_userObj->where($cond)->field('username')->find();
    		if($userData['username']){
    			$v['username'] = $userData['username'];
    		}else{
    			$userData = $accountObj->where($cond)->field('mobile')->find();
    			$v['username'] = $userData['mobile'];
    		}
    		
    		if(!empty($uid)){
    			$conds['userID'] = $uid;
    			$conds['giftrecordID'] = $v['id'];
    			$giftrecord_favourObj = M('giftrecord_favour');
    			$IS_Favour = $giftrecord_favourObj->where($conds)->find();
    			if($IS_Favour){
    				$v['is_favour'] = 1;
    			}else{
    				$v['is_favour'] = 2;
    			}
    		}else{
    			$v['is_favour'] = 2;
    		}
    		
    	}
    	
    	$this->assign('arrData',$arrData);
    	$this->assign('uid',$this->uid);
    	$this->assign('token',$token);
    	$this->display();
    }
    
    /**
     * 礼物记详情页
     */
    public function detail(){
    	$this->redis=new Redis();
    	$token=I("post.token");
    	if(empty($token)){
    		$token=I("get.token");
    	}
    	if($token){
    		$userInfo=$this->redis->get($token);
    		if($userInfo){
    			$this->uid=$userInfo['id'];
    			$this->comId=$userInfo['comId'];
    			$this->account=$userInfo['account'];
    			$this->userName=$userInfo['userName'];
    		}
    	}
    	$uid = $this->uid;
    	$giftID = I('get.id');
    	if($giftID){
    		$giftrecordObj = M('giftrecord');
    		$where['id']= $giftID;
    		$findData = $giftrecordObj->where($where)->find();
    		if($findData){
    			$giftrecord_cateObj = M('giftrecord_category');
    			$where1['id'] = $findData['cateid'];
    			$cateData2 = $giftrecord_cateObj->where($where1)->find();//子级分类
    			$where2['id'] = $cateData2['father'];
    			$cateData1 = $giftrecord_cateObj->where($where2)->find();//父级分类
    			$accountObj = M('sysuser_account');
    			$account_userObj = M('sysuser_user');
    			$cond['user_id'] = $findData['uid'];
    			$userData = $account_userObj->where($cond)->field('username')->find();
    			if($userData['username']){
    				$findData['username'] = $userData['username'];
    			}else{
    				$userData = $accountObj->where($cond)->field('mobile')->find();
    				$findData['username'] = $userData['mobile'];
    				$findData['userid'] = $userData['user_id'];
    			}
    			
    			//相关礼物记
    			$correlation = $this->correlation($findData['cateid'],$giftID);
    			if($uid){
    				$conds['userID'] = $uid;
    				$conds['attention'] = $findData['uid'];
    				$giftrecord_favourObj = M('giftrecord_attention');
    				$IS_Favour = $giftrecord_favourObj->where($conds)->find();
    				if($IS_Favour){
    					$findData['is_att'] = 1;
    				}else{
    					$findData['is_att'] = 2;
    				}
    			}else{
    				$findData['is_att'] = 2;
    			}
    			
    			foreach ($correlation as &$v){
    				$condition['user_id'] = $v['uid'];
    				$userData = $account_userObj->where($condition)->field('username')->find();
    				if($userData['username']){
    					$v['username'] = $userData['username'];
    				}else{
    					$userData = $accountObj->where($cond)->field('mobile')->find();
    					$v['username'] = $userData['mobile'];
    				}
    			
    				if($uid){
    					$condi['userID'] = $uid;
    					$condi['giftrecordID'] = $v['id'];
    					$giftrecord_favourObj = M('giftrecord_favour');
    					$IS_Favour = $giftrecord_favourObj->where($condi)->find();
    					if($IS_Favour){
    						$v['is_favour'] = 1;
    					}else{
    						$v['is_favour'] = 2;
    					}
    				}else{
    					$v['is_favour'] = 2;
    				}
    			}
    			
    			$this->assign('correlation',$correlation);//相关礼物记
    			$this->assign('cateData1',$cateData1['name']);//父级分类
    			$this->assign('cateData2',$cateData2['name']);//子级分类
    			$this->assign('findData',$findData);
    			$this->assign('uid',$uid);
    			$this->assign('token',$token);
    			$this->display();
    		}else{
    			$this->retError(-2,'服务繁忙');
    		}
    	}else{
    		$this->retError(-1,'礼物记的ID不能为空');
    	}
    }
    
    //相关礼物记
    public function correlation($cateid,$giftID){
    	$giftrecordObj = M('giftrecord');
    	$where['status'] = 1;
    	$where['cateid'] = $cateid;
    	$where['id'] = array('NEQ',$giftID);
    	$arrData = $giftrecordObj->where($where)->order('createTime desc')->select();
    	$accountObj = M('sysuser_account');
    	$account_userObj = M('sysuser_user');
    	foreach ($arrData as &$v){
    		$cond['user_id'] = $v['uid'];
    		$userData = $account_userObj->where($cond)->field('username')->find();
    		if($userData['username']){
    			$v['username'] = $userData['username'];
    		}else{
    			$userData = $accountObj->where($cond)->field('mobile')->find();
    			$v['username'] = $userData['mobile'];
    		}
    	}
    	return $arrData;
    }
    
    //礼物记的分类
    public function category(){
    	$giftrecord_cateObj = M('giftrecord_category');
    	$where['father'] = 0;
    	$where['level'] = 1;
    	$cateFather = $giftrecord_cateObj->where($where)->field('id,name')->select();
    	foreach($cateFather as &$v){
    		$where1['father'] = $v['id'];
    		$v['son'] = $giftrecord_cateObj->where($where1)->field('id,name')->select();
    	}
    	$this->retSuccess($cateFather,'礼物记分类返回成功');
    }
    
    /**
     * @name App礼物记(搜索)接口
     * @version 1.0
     * @method get
     * @param string $keywords
     * @author lihongqiang
     */
    public function search(){
    	$keywords = I("get.keywords");//获取get请求的关键字
    	$this->redis=new Redis();
    	$token=I("post.token");
    	if(empty($token)){
    		$token=I("get.token");
    	}
    	if($token){
    		$userInfo=$this->redis->get($token);
    		if($userInfo){
    			$this->uid=$userInfo['id'];
    			$this->comId=$userInfo['comId'];
    			$this->account=$userInfo['account'];
    			$this->userName=$userInfo['userName'];
    		}
    	}
    	$uid = $this->uid;
    	
    	$giftrecordObj = M('giftrecord');
    	$like = '%' . $keywords . '%';
    	$where['title'] = array('like', $like);
    	$giftrecordData = $giftrecordObj->where($where)->select();
    	$accountObj = M('sysuser_account');
    	$account_userObj = M('sysuser_user');
    	foreach ($giftrecordData as &$v){
    		$cond['user_id'] = $v['uid'];
    		$userData = $account_userObj->where($cond)->field('username')->find();
    		if($userData['username']){
    			$v['username'] = $userData['username'];
    		}else{
    			$userData = $accountObj->where($cond)->field('mobile')->find();
    			$v['username'] = $userData['mobile'];
    		}
    		if($uid){
    			$condi['userID'] = $uid;
    			$condi['giftrecordID'] = $v['id'];
    			$giftrecord_favourObj = M('giftrecord_favour');
    			$IS_Favour = $giftrecord_favourObj->where($condi)->find();
    			if($IS_Favour){
    				$v['is_favour'] = 1;
    			}else{
    				$v['is_favour'] = 2;
    			}
    		}else{
    			$v['is_favour'] = 2;
    		}
    	}
    	
    	$this->assign('arrData',$giftrecordData);
    	$this->assign('uid',$uid);
    	$this->assign('token',$token);
    	$this->display();
    }
    
   
    
    
    //礼物记点赞
    public function favour(){
    	$this->redis=new Redis();
    	$token=I("post.token");
    	if(empty($token)){
    		$token=I("get.token");
    	}
    	if($token){
    		$userInfo=$this->redis->get($token);
    		if($userInfo){
    			$this->uid=$userInfo['id'];
    			$this->comId=$userInfo['comId'];
    			$this->account=$userInfo['account'];
    			$this->userName=$userInfo['userName'];
    		}
    	}
    	$uid = $this->uid;
    	if(empty($uid)){
    		$Info['status'] = 0;
    		$Info['message'] = "登录后才可以点赞哦";
    		$this->ajaxReturn($Info);
    	}else{
    		$postData = I('post.');
    		if(empty($postData)){
    			$Info['status'] = 0;
    			$Info['message'] = "服务繁忙，点赞失败！";
    			$this->ajaxReturn($Info);
    		}else{
    			$giftrecord_id = $postData['giftrecord_id'];
    			if(empty($giftrecord_id)){
    				$Info['status'] = 0;
    				$Info['message'] = "服务繁忙，点赞失败！";
    				$this->ajaxReturn($Info);
    			}else{
    				$giftrecordFavourObj = M('giftrecord_favour');
    				$where1['giftrecordID'] = $giftrecord_id;
    				$where1['userID'] = $uid;
    				$findData = $giftrecordFavourObj->where($where1)->find();
    				if($findData){
    					$boolData = $giftrecordFavourObj->where($where1)->delete();
    					if($boolData){
    						//取消点赞
    						$giftrecordObj = M('giftrecord');
    						$where2['id'] = $giftrecord_id;
    						$favourNum1 = $giftrecordObj->where($where2)->field('favour')->find();
    						if($favourNum1['favour']>0){
    							$bool = $giftrecordObj->where($where2)->setDec('favour',1);
    						}else{
    							$bool = true;
    						}
    						if($bool){
    							$favourNum = $giftrecordObj->where($where1)->field('favour')->find();
    							$Info['status'] = $favourNum['favour'];
    							$Info['message'] = "取消点赞成功";
    							$this->ajaxReturn($Info);
    						}else{
    							$Info['status'] = 0;
    							$Info['message'] = "取消点赞失败";
    							$this->ajaxReturn($Info);
    						}
    					}else{
    						$Info['status'] = 0;
    						$Info['message'] = "取消点赞失败";
    						$this->ajaxReturn($Info);
    					}
    					$Info['status'] = 0;
    					$Info['message'] = "您已经点过赞了";
    					$this->ajaxReturn($Info);
    				}else{
    					//点赞表插入一条记录
    					$data['giftrecordID'] = $giftrecord_id;
    					$data['type'] = 1;//类型，1为点赞，2为收藏，默认为1
    					$data['userID'] = $uid;
    					$data['status'] = 1;//状态，默认为1
    					$data['createTime'] = date('Y-m-d H:i:s');
    					$boolData = $giftrecordFavourObj->add($data);
    					if($boolData){
    						$giftrecordObj = M('giftrecord');
    						$where['id'] = $giftrecord_id;
    						$bool = $giftrecordObj->where($where)->setInc('favour',1);
    						if($bool){
    							$favourNum = $giftrecordObj->where($where)->field('favour')->find();
    							$Info['status'] = $favourNum['favour'];
    							$Info['message'] = "点赞成功";
    							$this->ajaxReturn($Info);
    						}else{
    							$Info['status'] = $boolData;
    							$Info['message'] = "点赞失败";
    							$this->ajaxReturn($Info);
    						}
    					}else{
    						$Info['status'] = $boolData;
    						$Info['message'] = "点赞失败";
    						$this->ajaxReturn($Info);
    					}	
    				}
    			}
    		}
    	}
    }
    
    //礼物记取消点赞(不使用)
    public function cancelFavour(){
    	$uid = $this->uid;
    	if(empty($uid)){
    		$Info['status'] = 0;
    		$Info['message'] = "登录后才可以点赞哦";
    		$this->ajaxReturn($Info);
    	}else{
    		$postData = I('post.');
    		if(empty($postData)){
    			$Info['status'] = 0;
    			$Info['message'] = "服务繁忙，取消失败！";
    			$this->ajaxReturn($Info);
    		}else{
    			$giftrecord_id = $postData['giftrecord_id'];
    			if(empty($giftrecord_id)){
    				$Info['status'] = 0;
    				$Info['message'] = "服务繁忙，取消失败！";
    				$this->ajaxReturn($Info);
    			}else{
    				$giftrecordFavourObj = M('giftrecord_favour');
    				$where['giftrecordID'] = $giftrecord_id;
    				$where['userID'] = $uid;
    				$findFavour = $giftrecordFavourObj->where($where)->find();
    				if($findFavour){
    					$boolData = $giftrecordFavourObj->where($where)->delete();
    					if($boolData){
    						$giftrecordObj = M('giftrecord');
    						$bool = $giftrecordObj->where($where)->setDec('favour',1);
    						if($bool){
    							$favourNum = $giftrecordFavourObj->where($where)->field('favour')->find();
    							$Info['status'] = $favourNum['favour'];
    							$Info['message'] = "取消点赞成功";
    							$this->ajaxReturn($Info);
    						}else{
    							$Info['status'] = 0;
    							$Info['message'] = "取消点赞失败";
    							$this->ajaxReturn($Info);
    						}
    					}else{
    						$Info['status'] = 0;
    						$Info['message'] = "取消点赞失败";
    						$this->ajaxReturn($Info);
    					}
    				}else{
    					$Info['status'] = 0;
    					$Info['message'] = "取消点赞失败";
    					$this->ajaxReturn($Info);
    				}
    			}
    		}
    	}
    }
    
    //礼物记关注
    public function attention(){
    	$this->redis=new Redis();
    	$token=I("post.token");
    	if(empty($token)){
    		$token=I("get.token");
    	}
    	if($token){
    		$userInfo=$this->redis->get($token);
    		if($userInfo){
    			$this->uid=$userInfo['id'];
    			$this->comId=$userInfo['comId'];
    			$this->account=$userInfo['account'];
    			$this->userName=$userInfo['userName'];
    		}
    	}
    	$uid = $this->uid;
    	if(empty($uid)){
    		$Info['status'] = 0;
    		$Info['message'] = "登录后才可以关注哦";
    		$this->ajaxReturn($Info);
    	}else{
    		$postData = I('post.');
    		if(empty($postData)){
    			$Info['status'] = 0;
    			$Info['message'] = "服务繁忙，关注失败！";
    			$this->ajaxReturn($Info);
    		}else{
    			$attention = $postData['attention'];//礼物记发布人的user_id
    			if(empty($attention)){
    				$Info['status'] = 0;
    				$Info['message'] = "服务繁忙，关注失败！";
    				$this->ajaxReturn($Info);
    			}else{
    				$giftrecordAttentionObj = M('giftrecord_attention');
    				$where['userID'] = $uid;
    				$where['attention'] = $attention;
    				$findData = $giftrecordAttentionObj->where($where)->find();
    				if($findData){
    					$boolData = $giftrecordAttentionObj->where($where)->delete();
    					if($boolData){
    						$Info['status'] = $boolData;
    						$Info['message'] = "取消成功";
    						$this->ajaxReturn($Info);
    					}else{
    						$Info['status'] = 0;
    						$Info['message'] = "取消失败";
    						$this->ajaxReturn($Info);
    					}
    				}else{
    					$data['userID'] = $uid;
    					$data['attention'] = $attention;
    					$data['status'] = 1;//状态，默认为1
    					$data['createTime'] = date('Y-m-d H:i:s');
    					$boolData = $giftrecordAttentionObj->add($data);
    					if($boolData){
    						$Info['status'] = $boolData;
    						$Info['message'] = "关注成功";
    						$this->ajaxReturn($Info);
    					}else{
    						$Info['status'] = $boolData;
    						$Info['message'] = "关注失败";
    						$this->ajaxReturn($Info);
    					}
    				}
    			}
    		}
    	}
    }
    
    //礼物记关注（不使用）
    public function cancelAttention(){
    	$uid = $this->uid;
    	if(empty($uid)){
    		$Info['status'] = 0;
    		$Info['message'] = "登录后才可以关注哦";
    		$this->ajaxReturn($Info);
    	}else{
    		$postData = I('post.');
    		if(empty($postData)){
    			$Info['status'] = 0;
    			$Info['message'] = "服务繁忙！";
    			$this->ajaxReturn($Info);
    		}else{
    			$attention = $postData['attention'];//礼物记发布人的user_id
    			if(empty($attention)){
    				$Info['status'] = 0;
    				$Info['message'] = "服务繁忙，关注失败！";
    				$this->ajaxReturn($Info);
    			}else{
    				$where['userID'] = $uid;
    				$where['attention'] = $attention;
    				$giftrecordAttentionObj = M('giftrecord_attention');
    				$findData = $giftrecordAttentionObj->where($where)->find();
    				if($findData){
    					$boolData = $giftrecordAttentionObj->where($where)->delete();
    					if($boolData){
    						$Info['status'] = $boolData;
    						$Info['message'] = "取消成功";
    						$this->ajaxReturn($Info);
    					}else{
    						$Info['status'] = 0;
    						$Info['message'] = "取消失败";
    						$this->ajaxReturn($Info);
    					}
    				}else{
    					$Info['status'] = 0;
    					$Info['message'] = "您还没有关注";
    					$this->ajaxReturn($Info);
    				}
    			}
    		}
    	}
    }
    
//     //发布礼物记(未启用)
//     public function addgiftrecord(){
//     	if(IS_POST){
//     		$postData = I('post.');
//     		if($postData){
//     			$uid = $this->uid;
//     			if($uid){
//     				$giftrecordObj = M('giftrecord');
//     				$data['cateid'] = $postData['cateid'];//礼物记分类的二级ID
//     				$data['title'] = $postData['title'];//礼物记标题
//     				$data['images'] = $postData['images'];//礼物记完整的图片地址
//     				$data['content'] = $postData['content'];//礼物记的内容
//     				$data['uid'] = $uid;//礼物记完整的图片地址
//     				$data['favour'] = 0;//礼物记点赞合计
//     				$data['status'] = 0;//礼物记状态
//     				$data['createTime'] = date('Y-m-d H:i:s');//礼物记发布时间
//     				$data['updateTime'] = date('Y-m-d H:i:s');//礼物记更新时间
//     				$boolData = $giftrecordObj->add($data);
//     				if($boolData){
//     					$result['status'] = $boolData;
//     					$this->retSuccess($result,'礼物记发布成功');
//     				}else{
//     					$this->retError(-4,'礼物记发布失败');
//     				}
//     			}else{
//     				$this->retError(-3,'请重新登录');
//     			}
//     		}else{
//     			$this->retError(-2,'参数缺失');
//     		}
//     	}else{
//     		$this->retError(-1,'请求方式错误');
//     	}
//     }
    
    
    
    //接口返回错误信息
    public function retError($errCode=1,$msg='操作失败'){
    	$ret=array(
    			'result'=>100,
    			'errcode'=>$errCode,
    			'msg'=>$msg
    	);
    	echo json_encode($ret);
    	exit;
    }
    
    //接口返回结果
    public function retSuccess($data=array(),$msg='操作成功'){
    	$ret=array(
    			'result'=>100,
    			'errcode'=>0,
    			'msg'=>$msg,
    			'data'=>$data
    	);
    	echo json_encode($ret);
    	exit;
    }
}