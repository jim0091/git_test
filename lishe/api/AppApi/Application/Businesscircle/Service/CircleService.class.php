<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口服务];						@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle\Service];
 * +----------------------------------------------------------------------
 * |@Name:			[CircleService.class.php];	
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
class CircleService extends Controller {
	
    public function index(){
    	return '';
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_晒福利_获取部门名称
     * @author:	lihongqiang	2017-03-16 10:45
     * @method:	传参请求
     * @param:	string $user_id 
     * @return: string	
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getDepartment	BEGIN//
    public function getDepartment($user_id){
    	if(!empty($user_id)){
    		$ModelObj = D('SysuserUserDeposit');
    		$where['user_id'] = $user_id;
    		$findData = $ModelObj->where($where)->field('user_id,department')->find();
    		if($findData){
    			if (empty($findData['department'])||$findData['department']==''){
    				return "未知";//未知部门
    			}else{
    				return $findData['department'];
    			}
    		}else{
    			return "未知";//未知部门
    		}
    	}else{
    		return "未知";//未知部门
    	}
    }	//getDepartment	END//
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_晒福利_数据入库
     * @author:	lihongqiang	2017-03-16 10:45
     * @method:	传参请求
     * @param:	array $data //写入数据库的数组信息
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addBusiness	BEGIN
    public function addBusiness($data){
    	if(empty($data)){
    		return false;
    	}else{
    		$ModelObj = D('Businesscircle');
    		$boolData = $ModelObj->add($data);
    		if($boolData){
    			return $boolData;//返回ID
    		}else{
    			return false;
    		}
    	}
    }//addBusiness END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_晒福利_查询圈子内容数据（返回多条数据）
     * @author:	lihongqiang	2017-03-16 10:26
     * @method:	传参请求
     * @param array $where //查询条件的数组信息
     * @param string $field //查询的字段
     * @return: boolean	or	array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectBusiness	BEGIN
    public function selectBusiness($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Businesscircle');
    		$arrData = $ModelObj->where($where)->field($field)->order('create_time desc')->select();
    		return $arrData;//返回二维数组
    	}
    }//selectBusiness END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_晒福利_查询圈子内容数据（返回单条数据）
     * @author:	lihongqiang	2017-03-20 11:26
     * @method:	传参请求
     * @param array $where //查询条件的数组信息
     * @param string $field //查询的字段
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findBusiness	BEGIN
    public function findBusiness($where,$field = ''){
    	if(empty($where)){
    		return false;//禁止不带条件查询
    	}else{
    		$ModelObj = D('Businesscircle');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		return $findData;//返回二维数组
    	}
    } //findBusiness END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询用户信息（多用于取手机号）
     * @author:	lihongqiang	2017-03-16 14:06
     * @method:	传参请求
     * @param:	int $user_id  //查询条件
     * @param:	string	$field	//查询字段
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getUserAccount	BEGIN
    public function getUserInfo($user_id,$field = ''){
    	if(empty($user_id)){
    		return false;
    	}else{
    		$where ['user_id'] = $user_id;
    		$ModelObj = D('SysuserUser');
    		$findUser = $ModelObj->where($where)->field($field)->find();
    		if($findUser){
    			if(empty($findUser['username'])){
    				if(!empty($findUser['name'])){
    					$findUser['username'] = $findUser['name'];
    				}else{
    					if(empty($_SESSION['account'])){
    						$findUser['username'] = $_SESSION['account'];
    					}else{
    						$where1['user_id'] = $user_id;
    						$accountInfo= $this->getUserAccount($where,'mobile');
    						$findUser['username'] = $accountInfo['mobile'];
    					}
    				}
    			}
    			$isPhoneNumber = isPhoneNumber($findUser['username']);
    			if ($isPhoneNumber){
    				$mobileString = cutMobile($findUser['username']);
    				$findUser['username'] = $mobileString;
    			}
    			return $findUser;
    		}else{
    			return false;
    		}
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询用户账号表信息（多用于取手机号）
     * @author:	lihongqiang	2017-03-16 14:26
     * @method:	传参请求
     * @param:	array	$where  //查询条件
     * @param:	string	$field	//查询字段
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getUserAccount	BEGIN
    public function getUserAccount($where,$field = ''){
    	$accountModel = D('SysuserAccount');
    	$accountInfo = $accountModel->where($where)->field($field)->find();
    	if(empty($accountInfo)){
    		return false;
    	}else{
    		return $accountInfo;
    	}
    }//getUserAccount END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	判断是不是HR
     * @author:	lihongqiang	2017-03-16 14:26
     * @method:	传参请求
     * @param:	int or string	$user_id	查询的字段
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//IS_HR	BEGIN
    public function IS_HR($user_id){
    	if(empty($user_id)){
    		return false;
    	}else{
    		$ModelObj = D('SysuserUserDeposit');
    		$where['user_id'] = $user_id;
    		$accountInfo = $ModelObj->where($where)->field('user_id,isHr')->find();
    		if($accountInfo){
    			if((!empty($accountInfo['isHr'])) && $accountInfo['isHr']==1){
    				return true;
    			}else{
    				return false;
    			}	
    		}else{
    			return false;
    		}
    	}
    }//IS_HR END 
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询点赞表（find）
     * @author:	lihongqiang	2017-03-17 14:26
     * @method:	传参请求
     * @param:	array $where	条件
     * @param:	string $field	查询的字段
     * @return: boolean  or  array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findPraise	BEGIN
    public function findPraise($where,$field=''){
    	$ModelObj = D('Praise');
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
    }//findPraise END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询点赞表(select)
     * @author:	lihongqiang	2017-03-20 13:54
     * @method:	传参请求
     * @param:	array $where	条件
     * @param:	string $field	查询的字段
     * @return: boolean  or  array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectPraise	BEGIN
    public function selectPraise($where,$field=''){
    	$ModelObj = D('Praise');
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
     * @author:	lihongqiang	2017-03-17 14:38
     * @method:	传参请求
     * @param:	array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//addPraise	BEGIN
    public function addPraise($data){
    	$ModelObj = D('Praise');
    	$boolData = $ModelObj->add($data);
    	if($boolData){
    		return $boolData;//返回点赞记录的ID
    	}else{
    		return false;
    	}
    }//addPraise END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	内容表的累计点赞+1
     * @author:	lihongqiang	2017-03-17 14:38
     * @method:	传参请求
     * @param:	array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//plusPraiseNum	BEGIN
    public function plusPraiseNum($businesscircle_id){
    	$ModelObj = D('Businesscircle');
    	$where['id'] = $businesscircle_id;
    	$bool = $ModelObj->where($where)->setInc('praise_num',1);
    	if($bool){
    		return $bool;
    	}else{
    		return false;
    	}
    }//plusPraiseNum END
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	内容表的累计点赞-1
     * @author:	lihongqiang	2017-03-17 14:38
     * @method:	传参请求
     * @param:	array $data  //新增的数据
     * @return: bool
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//subtractPraiseNum	BEGIN
    public function subtractPraiseNum($businesscircle_id){
    	$ModelObj = D('Businesscircle');
    	$where['id'] = $businesscircle_id;
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
    }//subtractPraiseNum END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	删除点赞表的记录
     * @author:	lihongqiang	2017-03-17 15:27
     * @method:	传参请求
     * @param:	array $where  //条件
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//deletePraise	BEGIN
    public function deletePraise($where){
    	$ModelObj = D('Praise');
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
    }//deletePraise END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	返回点赞数量
     * @author:	lihongqiang	2017-03-17 15:27
     * @method:	传参请求
     * @param:	array $where  //条件
     * @return: boolean
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//countPraise	BEGIN
    public function countPraise($where){
    	if($where){
    		$ModelObj = D('Praise');
    		$countPraise = $ModelObj->where($where)->count();
    		return $countPraise;
    	}else{
    		return 0;
    	}
    }	//countPraise END
}