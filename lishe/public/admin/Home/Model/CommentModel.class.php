<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: CommentModel.class.php v001 2016-11-08
 * @description 评论管理
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class CommentModel extends CommonModel{
	public function __construct(){
		$this->modelComFeedback=M('company_feedback');
		$this->modelCategory=M('syscategory_cat');
	}	
/*
 * 取出所有意见反馈总数
 * */
 	public function getFeedbackCount($condition){
		$condition['is_delete']=0;
		return $this->modelComFeedback->where($condition)->count();
	}	
/*
 * 取出所有意见反馈
 * */
	public function getAllFeedback($condition,$limit){
		$condition['is_delete']=0;
		return $this->modelComFeedback->where($condition)->limit($limit)->order('add_time desc')->select();
	}
/*
 * 取出指定意见反馈
 * */
	public function getThisFeedback($feedbackId){
		$condition['is_delete']=0;
		$condition['feedback_id']=$feedbackId;
		return $this->modelComFeedback->where($condition)->find();
	}
/*
 * 编辑意见反馈
 * */
	public function editThisFeedback($feedbackId,$data,$field){
		$condition['feedback_id']=$feedbackId;
		return $this->modelComFeedback->where($condition)->field($field)->save($data);
	}	
	/*
	 * 取出指定分类的信息
	 * */
	public function getCategoryInfo($where){
		return $this->modelCategory->where($where)->field('cat_id,cat_name')->select();
	}	
	

}  
?>  	