<?php   
namespace Home\Model;
use Think\Model;
class ActivityModel extends Model{
	/*author :章锐
	 * 2016/7/19
	 * 企业秀部分
	 * */
	public function __construct(){
		$this->modelConfig=M('company_config');
		$this->modelNewsCenter=M('company_news_center');
		$this->modelActivityP=M('company_activity_person');
	}	 	
	public function getpics($comId){
		//取出企业海报轮播图
		$condition['is_delete']=0;
		$condition['com_id']=$comId;
		$res=$this->modelConfig->where($condition)->getField('carousel_poster');
		return $res;
	}
	public function updatapics($comId,$picStr){
		//更新企业海报的数据
		$condition['is_delete']=0;
		$condition['com_id']=$comId;
		$data['carousel_poster']=$picStr;		
		$res=$this->modelConfig->where($condition)->data($data)->save();
		return $res;
	}
	public function getFieldConfig($comId,$field){
		$condition['is_delete']=0;
		$condition['com_id']=$comId;
		$res=$this->modelConfig->where($condition)->field($field)->find();
		return $res;		
	}
	public function updataComConfig($comId,$data){
		//更新公司基本配置
		$condition['is_delete']=0;
		$condition['com_id']=$comId;
		$res=$this->modelConfig->where($condition)->data($data)->save();
		return $res;
	}
	//新闻中心添加新闻
	public function addNews($data){
		return $res=$this->modelNewsCenter->data($data)->add();
	}
	//新闻中心更新、编辑、删除
	public function updataNews($newsId,$data){
		$condition['news_id']=$newsId;
		return $res=$this->modelNewsCenter->where($condition)->data($data)->save();
	}
	//一定条件下的新闻总数
	public function getNewsCount($condition){
		return $res=$this->modelNewsCenter->where($condition)->count();
	}
	//一定条件下取出新闻
	public function getNews($condition,$limit){
		return $res=$this->modelNewsCenter->where($condition)->order('rank desc,modify_time desc,creat_time desc')->limit($limit)->select();
	}
	//取出指定news_id下的新闻信息
	public function getThisNews($newsId){
		$condition['news_id']=$newsId;
		$condition['is_delete']=0;
		return $res=$this->modelNewsCenter->where($condition)->find();
	}
	//活动管理（超级新人王/月度寿星）添加
	public function addActivityPerson($data){
		return $res=$this->modelActivityP->data($data)->add();
	}
	//一定条件下的新人王/月度寿星总数
	public function getActivityPersonCount($condition){
		return $res=$this->modelActivityP->where($condition)->count();
	}	
	//一定条件下取出新人王/月度寿星信息
	public function getActivityPerson($condition,$limit){
		return $res=$this->modelActivityP->where($condition)->order('modify_time desc,creat_time desc')->limit($limit)->select();
	}	
	//取出指定id下的新人王/月度寿星的信息
	public function getThisActivityPerson($aPid){
		$condition['id']=$aPid;
		$condition['is_delete']=0;
		return $res=$this->modelActivityP->where($condition)->find();
	}	
	//更新新人王/月度寿星信息
	public function updataActivityPerson($acitivityPid,$data){
		$condition['id']=$acitivityPid;
		return $res=$this->modelActivityP->where($condition)->data($data)->save();		
	}
	//取出指定id下的活动回顾、头号人物的信息
	public function getThisActivityInfo($newId){
		$condition['news_id']=$newId;
		$condition['is_delete']=0;
		return $res=$this->modelNewsCenter->where($condition)->field('news_id,title,author,content,pic,review_category,abstract,activity_type')->find();
	}	
	
}
