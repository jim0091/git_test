<?php 
namespace Home\Controller;
/*
 * 评论管理
 * 2016/11/08
 * zhangrui
 * 
 * */	
class CommentController extends CommonController{
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dCategory=D('Category');
		$this->dGoods=D('Goods');
		$this->dOrder=D('Order');
		$this->dComment=D('Comment');
	}	
	//取出条件的名字
	public function serachName($id,$type,$level){
		//反馈类型
		if($type=="feedbackType"){
			$staus=array(
				1 =>array(
					"status" => "1",
					"name"   => "意见反馈"
				),
				2 =>array(
					"status" => "2",
					"name"   => "商品登记"
				),
			);
			$info=$staus[$id];
		}
		if($type=="sign"){
			$stauss=array(
				1 =>array(
					"status" => "1",
					"name"   => "标记"
				),
				2 =>array(
					"status" => "2",
					"name"   => "未标记"
				),
			);
			$info=$stauss[$id];
		}	
		if($type=="status"){
			$staush=array(
				1 =>array(
					"status" => "1",
					"name"   => "已回复"
				),
				2 =>array(
					"status" => "2",
					"name"   => "未回复"
				),
			);
			$info=$staush[$id];
		}				
		return $info;
	}	
/*
 * 意见反馈搜索条件
 * */	
	public function serachCondition(){
		$data=I('');
		//输出搜索条件显示
		$searchData=$data;
		//企业条件
		if(!empty($data['comId'])){
			//start公司名字显示用
				$searchData['company']=A('Order')->serachName($data['comId'],"company");
			//end
			$condition['com_id']=$data['comId'];
		}		
		//时间条件(支付时间)
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$data['start']=strtotime($data['startTime']);
			$data['end']=strtotime($data['endTime']." +24 hours");
			$str=$data['start'].','.$data['end'];
			$condition['add_time']=array('between',$str);
		}
		//员工手机号
		if(!empty($data['mobile'])){
			$userId=$this->dOrder->getUserId(trim($data['mobile']));
			$condition['user_id']=$userId;
		}	
		//反馈类型
		if(!empty($data['feedbackType'])){
			$condition['prom_type']=$data['feedbackType'];
			$searchData['feedbackType']=$this->serachName($data['feedbackType'],"feedbackType");
		}				
		//标记条件
		if(!empty($data['sign'])){
			if($data['sign']==1){
				$condition['is_sign']=1;
			}else if($data['sign']==2){
				$condition['is_sign']=0;
			}			
			$searchData['sign']=$this->serachName($data['sign'],"sign");
		}	
		//标记条件
		if(!empty($data['status'])){
			if($data['status']==1){
				$condition['status']=1;
			}else if($data['status']==2){
				$condition['status']=0;
			}			
			$searchData['status']=$this->serachName($data['status'],"status");
		}			
		$this->assign('searchData',$searchData);
		return $condition;
	}
/*
 * 意见反馈
 * */
	public function feedback(){
		$condition=$this->serachCondition();
		$number=$this->dComment->getFeedbackCount($condition);
		$size=7;
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$list=$this->dComment->getAllFeedback($condition,$limit);
		foreach($list as $key=>$value){
			if(!empty($value['cat_id'])){
				$list[$key]['catArr']=explode(',', $value['cat_id']);
				foreach($list[$key]['catArr'] as $keys=>$val){
					if(!empty($val)){
						$catIds[]=$val;
					}
				}
			}
			$userIds[]=$value['user_id'];
			$comIds[]=$value['com_id'];
		}
		//分类的名称
		if(!empty($catIds)){
			$where=array(
				'cat_id'=>array('in',$catIds),
			);
			$catNames=$this->dComment->getCategoryInfo($where);
			foreach($catNames as $key=>$val){
				$catInfo[$val['cat_id']]=$val['cat_name'];
			}
			unset($catNames);
			$this->assign('catInfo',$catInfo);
		}
		if(!empty($userIds)){
			$users=$this->dOrder->getThiUserInfo($userIds);
		}
		if(!empty($comIds)){
			$companys=$this->dOrder->getThisCompany($comIds);
		}
		foreach($list as $key=>$value){
			//所属公司
			foreach($companys as $keyc=>$valuec){
				if($value['com_id']==$valuec['com_id']){
					$list[$key]['company']=$valuec['com_name'];	
				}					
			}
			foreach($users as $keyu=>$valueu){
				if($value['user_id']==$valueu['user_id']){
					$list[$key]['userName']=$valueu['mobile'];	
				}				
			}				
		}				
		$pagestr = $page -> show("pageos","pageon");  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$list);
		$this->assign('number',$number);
		$this->display();
	}
/*
 * 
 * 留言回复
 * */
	public function reply(){
		$feedbackId=I('feedbackId');
		if($feedbackId){
			$info=$this->dComment->getThisFeedback($feedbackId);
			$this->assign('info',$info);
			$this->display();
		}
	}
/*
 * 保存回复
 * */
	public function saveReply(){
		$feedbackId=I('feedbackId');
		$content=I('content');
		$pic=A('Activity')->uploadImg('pic','images');
		if(!empty($pic)){
			$data['reply_pic']=$pic;
		}
		if($feedbackId){
			if(empty($content)){
				$this->error('请输入回复内容！');	
			}
			$data['reply_content']=$content;
			$data['reply_time']=time();
			$data['reply_admin_name']=$this->realName;
			$data['reply_admin_id']=$this->adminId;
			$data['status']=1;
			$res=$this->dComment->editThisFeedback($feedbackId,$data,'reply_content,reply_time,reply_pic,reply_admin_name,reply_admin_id,status');					
			if($res){
				$this->success('回复成功');
			}else{
				$this->success('回复失败，请重试....');
			}			
		}	
	}
/*
 * 删除/标记
 * */	
	public function changeFeedback(){
		$type=I('type');
		$val=I('val');
		$feedbackId=I('feedbackId');
		if($type=="status"){
			//禁用启用
			if($val == 1){
				$data['is_sign']=0;
			}else if($val==0){
				$data['is_sign']=1;
			} 
		}else if($type=="del"){
			//删除情景
			$data['is_delete']=1;
		}
		$res=$this->dComment->editThisFeedback($feedbackId,$data,'is_sign,is_delete');
		echo json_encode($res);
	}
	
		
		
}
