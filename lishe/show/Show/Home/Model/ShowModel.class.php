<?php  
namespace Home\Model;
use Think\Model;
class ShowModel extends Model{
	/*
	 *企业秀前台模型 
	 * 章锐
	 * 2016/7/25
	 * */
	
	public function __construct(){
		$this->modelConfig=M('company_config');		
		$this->modelNewsCenter=M('company_news_center');
		$this->modelActivityPerson=M('company_activity_person');		
		//企业福利板块
		$this->modelCategory=M('company_category_config');
		$this->modelItem=M('company_item_config');
		//商品表/商品分类表
		$this->modelItems=M('sysitem_item');
		//投票表
		$this->modelVote=M('company_super_vote');
		//模板
		$this->modelTemplete=M('company_templete');
		//积分表
		$this->modelPoint=M('sysuser_user_deposit');
		//购物车
		$this->modelCart=M('systrade_cart');
		//公司商品价格
		$this->modelCompanyItemPrice=M('company_item_price');
		
	}
	
	/*
	 * 取出公司的信息（company——config表）
	 * */
	public function getCompanyInfo($condition,$field){
		
		return $res=$this->modelConfig->where($condition)->field($field)->find();
		
	}
	/*
	 * 取出公司的信息（company——config表）
	 * */
	public function getFieldCompanyInfo($condition,$field){
		
		return $this->modelConfig->where($condition)->getField($field);
		
	}	
	/*
	 * 
	 * 首页显示头条新闻
	 * */
	public function getTopNews($condition,$limit){
		$condition['is_delete']=0;
		$condition['is_activity']=0;
		return $res=$this->modelNewsCenter->where($condition)->order('modify_time desc,creat_time desc')->field('news_id,title,pic,vice_title,abstract,category,creat_time')->limit($limit)->select();
	}
	/*
	 * 
	 * 首页显示本月新人王
	 * */
	public function getNewsP($comId,$type){
		$condition['com_id']=$comId;
		$condition['category']=$type;
		$condition['is_delete']=0;
		if($type==1){
			$order="love desc,modify_time desc,creat_time desc";
		}else if($type==2){
			$thismonth=date('m');
			$condition['birthday_month']=$thismonth;			
			$order="modify_time desc,creat_time desc";
		}
		return $res=$this->modelActivityPerson->where($condition)->order($order)->field('id,pic,position,name,birthday,content')->limit(4)->select();
	}
	/*
	 * 企业简介
	 * */
	public function aboutC($comId){
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		return $this->modelConfig->where($condition)->field('com_name,com_profile_title,com_profile_content,com_profile_pic')->find();
		
	}
	/*
	 * 企业架构
	 * */
	public function getFramework($comId){
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		return $this->modelConfig->where($condition)->getField('framework_pic');		
	}
	/*
	 * 企业福利首页推荐商品id
	 * */
	public function getRecommend($comId){
		return $this->modelItem->where('com_id='.$comId)->field('item_config_id,cat_name,recommend,cat_banner,cat_content,item_ids')->order('order_sort asc')->select();
	}
	/*
	 * 企业福利首页推荐商品列表
	 * */
	public function getRecommendItem($recommendIds,$field="10"){
		foreach($recommendIds as $key=>$value){
			$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$value.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
				  ->field('a.item_id,a.shop_id,a.flag,title,price,image_default_id')->order('a.flag asc ,modified_time desc')->limit($field)->select();
			$a[$key]=$res;
		}
		return $a;
	}
	
	/*
	 * 企业福利首页一级分类下的二级分类
	 * */
	public function getSubCategory($comId,$configIds){
		$condition['com_id']=$comId;
		$condition['item_config_id']=array('in',implode(",", $configIds));
		$condition['disabled']=0;
		return $this->modelCategory->where($condition)->field('cat_config_id,item_config_id,cat_name')->order('order_sort asc')->select();
	}
	 /*
	  * 取出指定分类中的商品
	  * */
	 public function getCatItems($comId,$catId){
		$condition['com_id']=$comId;
		$condition['cat_config_id']=$catId;
		$condition['disabled']=0;	 	
	 	return $this->modelCategory->where($condition)->field('item_ids,cat_id,cat_name')->find();
	 }	
	/**
	 *有效的商品数量 
	 * */
	public function itemCount($itemIds){
		return $res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')->count();		
	}
	/*
	 * 商品列表（分页）
	 * */
	public function getItemLists($itemIds,$limit){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
				  ->field('a.item_id,a.shop_id,a.flag,title,price,image_default_id')->order('a.flag asc , modified_time desc')->limit($limit)->select();		
	}
	/*
	 * 取出一定条件下上架的的所有"item_id
	 * */
	public function getItemIds($itemIds){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
					  ->field('a.item_id')->order('a.flag ASC ,a.cat_id DESC,a.profit_rate DESC')->select();				
	}
	/*
	 * 取出有效的商品列表
	 * */
	 public function getItemList($itemIds){
		return	$res=$this->modelItems->where(' item_id in('.implode(',',$itemIds).')')->field('item_id,shop_id,title,price,image_default_id,flag')->order('flag ASC ,cat_id DESC,profit_rate DESC')->select();	 	
	 }
	
	/*
	 *公司配置表中指定item_config_id的商品
	 * */
	public function getConfigItems($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
	 	return $this->modelItem->where($condition)->field('item_ids,cat_id,cat_name')->find();		
	}
	//一定条件下的新闻总数
	public function getNewsCount($condition){
		return $res=$this->modelNewsCenter->where($condition)->count();
	}
	//一定条件下取出新闻
	public function getNews($condition,$limit){
		return $res=$this->modelNewsCenter->where($condition)->order('rank desc,modify_time desc,creat_time desc')->limit($limit)->select();
	}	
	//一定条件下的新人王/月度寿星总数
	public function getActivityPersonCount($condition){
		return $res=$this->modelActivityPerson->where($condition)->count();
	}	
	//一定条件下取出新人王/月度寿星信息
	public function getActivityPerson($condition,$field,$limit){
		return $res=$this->modelActivityPerson->where($condition)->order('modify_time desc,creat_time desc')->field($field)->limit($limit)->select();
	}	
	//一定条件下取出单个新人王/月度寿星信息
	public function getThisPerson($comId,$personId,$field){
		$condition['com_id']=$comId;
		$condition['id']=$personId;
		$condition['is_delete']=0;
		return $res=$this->modelActivityPerson->where($condition)->field($field)->find();
	}			
	//取出指定news_id下的新闻信息
	public function getThisNews($comId,$newsId){
		$condition['news_id']=$newsId;
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		return $res=$this->modelNewsCenter->where($condition)->find();
	}			
	//推荐阅读
	public function rememberRead($comId){
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		$condition['is_activity']=0;		
		return $res=$this->modelNewsCenter->where($condition)->field('news_id,title,category')->order('modify_time desc,creat_time desc')->limit(5)->select();
	}
	//测是否有资格投票
	public function checkVote($personId,$comId,$userId){
		$condition['person_id']=$personId;
		$condition['com_id']=$comId;
		$condition['user_id']=$userId;
		return $res=$this->modelVote->where($condition)->find();
	}
	//投票表增加数据
	public function addVote($personId,$comId,$userId){
		$data['person_id']=$personId;
		$data['com_id']=$comId;
		$data['user_id']=$userId;
		$data['creat_time']=time();
		return $res=$this->modelVote->data($data)->add();
	}	
	//投票成功增加票数
	public function voteSucess($personId){
		return $res=$this->modelActivityPerson->where('id='.$personId)->setInc('love'); // 用户的积分加1
	}
	//得到分类
	public function getThisCats($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
		return $res=$this->modelItem->where($condition)->field('cat_name,item_config_id')->find();
	}
	public function getThisCatsMore($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
		return $res=$this->modelCategory->where($condition)->field('cat_name,cat_config_id')->select();
	}	
	public function getThisCatName($comId,$catId){
		$condition['com_id']=$comId;
		$condition['cat_config_id']=$catId;
		return $res=$this->modelCategory->where($condition)->getField('cat_name');
	}
	//取出该公司的的选择模板
	public function getTemplete($tempId){
		$condition['temp_id']=$tempId;
		$condition['isdelete']=0;
		return $res=$this->modelTemplete->where($condition)->getField('temp_name');
	}
	//取出个人积分
	public function getPersonPoint($uid){
		 $res=$this->modelPoint->where('user_id='.$uid)->getField('deposit');
		 $res=$res*100;
		 $resu=intval($res);
		 return $resu;
	}
	//取出用户购物车的数量
	public function getCartNumber($uid){
		return $res=$this->modelCart->where('user_id='.$uid)->count();
	}
	//检测公司配置模板名在模板表是否存在
	public function isSetTemp($tempName){
		$condition['temp_name']=$tempName;
		$condition['isdelete']=0;
		return $res=$this->modelTemplete->where($condition)->getField('temp_id');		
	}
	//公司商品价格 20160824 开始
	public function getCompanyItemPrice($array){
		/**
  		* $array=array(
    	*	'sku_id'=>1,
    	*	'com_id'=>1
    	*	);
		*/
		if(!is_array($array)){
			$res=array('msg'=>'参数格式不正确');	 
		}elseif(empty($array)){
			$res=array('msg'=>'参数不能为空');	 
		}else{
			$res=$this->modelCompanyItemPrice->where($array)->field('item_id,price,condition')->select();
		}
		return $res;
	}
	//公司商品价格 20160824 结束	
	//取出有企业秀的域名名称
	public function getHasShow($comIds){
		$condition['com_id']=array('in',$comIds);
		return $this->modelConfig->where($condition)->getField('com_domain',TRUE);
	}
	
	
}
