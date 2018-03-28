<?php   
namespace Home\Controller;
class ShowController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelActivity=D('Activity');
	}
	
	
	public function index(){
		$this->display();
	}
	//伙伴首页设置
	public function partner(){
		$comId=$this->comId;
		$field="carousel_poster";
		$posterPics=$this->modelActivity->getFieldConfig($comId,$field);
		$arrPoster=explode(',', $posterPics['carousel_poster']);
		$this->assign('posterPic',$arrPoster);
		$this->assign('show',I('local'));
		$this->display();
	}
	//公司简介设置
	public function profile(){
		$comId=$this->comId;
		$field="com_profile_title,com_profile_pic,com_profile_content,framework_pic";
		$comProfile=$this->modelActivity->getFieldConfig($comId,$field);	
		$this->assign('comProfile',$comProfile);	
		$this->assign('show',I('local'));
		$this->display();
	}
	//更改公司简介
	public function modifyProfile(){
		$fileLoad=I('file');
		if(!empty($fileLoad)){
			$modify=I('modify');
			if($modify==1){
				$fileLoad='/business/'.$fileLoad;
			}
		}
		$title=I('title');
		$content=I('content');
		$frameworkPic=I('frameworkPic');
		$comId=$this->comId;
		if(!empty($frameworkPic)){
			$frameworkPic='/business/'.$frameworkPic;
			//上传公司架构图
			$data['framework_pic']=$frameworkPic;
			$saveRes=$this->modelActivity->updataComConfig($comId,$data);
			if($saveRes){
				echo "上传成功!";
			}else{
				echo "上传失败!";
			}			
		}
		if(!empty($fileLoad) && !empty($title) && !empty($content)){
			//公司简介
			$data['com_profile_title']=$title;
			$data['com_profile_pic']=$fileLoad;
			$data['com_profile_content']=$content;
			$saveRes=$this->modelActivity->updataComConfig($comId,$data);
			if($saveRes){
				echo "保存成功!";
			}else{
				echo "保存失败!";
			}
		}
		
	}
	
	//超级新人王页面
	public function actives(){
		
		$this->assign('show',I('local'));
		$this->display();
	}
	//活动回顾页面
	public function activityReview(){
		
		$this->display();
	}
	//月度寿星页面
	public function monthBirthday(){
		
		$this->display();
	}
	/*
	 *头条新闻 
	 * */
	 public function topNews(){
	 	
		$this->assign('set',I('set'));
		$this->display();		
	 }
	 
	//新闻中心设置
	public function newsset(){
		//显示相应新闻（行业聚焦，媒体报道，企业公告）
		$this->assign('type',I('type'));
		$this->display();
	}
	//ajax新闻列表
	public function newsList(){
		$type=I('type');
		$sets=I('sets');//头条新闻设置
		$comId=$this->comId;
		$keyword=I('keyword');
		if(!empty($comId)){
			//分页
			if(!empty($keyword)){
				$where['title']=array('like','%'.$keyword.'%');
				$where['content']=array('like','%'.$keyword.'%');
				$where['_logic']='or';
				$condition['_complex']=$where;
			}
			if(!empty($type)){
				//头条新闻页面
				$condition['category']=$type;
			}else{
				if(empty($sets)){
					//头条新闻显示
					$condition['rank']=array('neq',1);
				}else{
					//设置头条新闻
					$this->assign('sets',$sets);	
					
				}
			}
			$condition['com_id']=$comId;
			$condition['is_delete']=0;
			$condition['is_activity']=0;
			$number=$this->modelActivity->getNewsCount($condition);
			$size=6;
			$page = new \Think\PageAjax($number, $size);
			$rollPage = 5;
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page -> firstRow;  //起始行数
			$pagesize = $page -> listRows;   //每页显示的行数
			$limit = "$start , $pagesize";	
			$news=$this->modelActivity->getNews($condition,$limit);
			foreach($news as $key=>$value){
				$news[$key]['creat_time']=date('Y-m-d',$value['creat_time']);
				switch($value['rank']){
					case 1:
						$news[$key]['rankName']="普通";	
						break;
					case 2:
						$news[$key]['rankName']="头条";	
						break;						
					case 3:
						$news[$key]['rankName']="置顶头条";		
						break;					
				}
			}
			$style = "";
			$onclass = "thispage";
			$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
			$this->assign('pagestr',$pagestr);		
			$this->assign('news',$news);	
		}
		switch($type){
			case 1:
				$newsLocal="行业聚焦管理";
				break;
			case 2:
				$newsLocal="媒体报道管理";
				break;
			case 3:
				$newsLocal="企业公告管理";
				break;								
		}
		$this->assign('newsLocal',$newsLocal);	
		if(!empty($type)){
			$this->display();
		}else{
			if(empty($sets)){
				$this->display('topNewsList');
			}else{
				$this->display();
			}
		}	
		
	}
	//编辑新闻（增加、编辑）
	public function editNews(){
		$data=$_POST['data'];
		$newsId=$data['newsId'];
		$comId=$this->comId;
		$data['com_id']=$comId;
		if($data['activity_type']){
			// 活动回顾/头号人物管理的编辑和增加
			switch($data['activity_type']){
				case 1:				
					$url = __APP__."/Show/activityReview";		
					break;		
				case 2:				
					$url = __APP__."/Show/topPerson";		
					break;						
			}
			$data['is_activity']=1;
		}else{
			//新闻中心设置编辑
			$url = __APP__."/Show/newsset/type/".$data['category'];
		}		
		if($newsId){
			//编辑
			$picAll = explode('/', $data['pic']);
			if($picAll[0] == "Upload"){
				$data['pic']='/business/'.$data['pic'];
			}
			if(!empty($data)){
				$data['modify_time']=time();
				$addRes=$this->modelActivity->updataNews($newsId,$data);
			}			
			if($addRes){
				echo "<script>window.location='".$url."';</script>";				
				
			}else{
				
			}			
		}else{
			//增加
			$data['pic']='/business/'.$data['pic'];
			$data['creat_time']=time();
			$data['modify_time']=time();
			if(!empty($data)){
				$addRes=$this->modelActivity->addNews($data);
			}
			if($addRes){
				echo "<script>window.location='".$url."';</script>";				
			}else{
				
			}
		}
	}
	//删除新闻
	public function delNews(){
		$newsId=I('newsId');
		if(!empty($newsId)){
			$data['modify_time']=time();
			$data['is_delete']=1;
			$delRes=$this->modelActivity->updataNews($newsId,$data);
			if($delRes){
				echo "删除成功!";
			}else{
				echo "删除失败!";
			}
		}	
	}
	/*
	 * 新闻置顶 、设为头条、普通操作
	 * 
	 * */
	public function changeNewsRank(){
		$newsId=I('newsId');
		$rank=I('rank');
		if(!empty($newsId)  && !empty($rank)){
			$data['rank']=$rank;
			$data['modify_time']=time();
			$updataRes=$this->modelActivity->updataNews($newsId,$data);
			if($updataRes){
				echo '操作成功!';
			}
			
			
		}
		
	}

	//伙伴动态设置--头号人物
	public function topPerson(){
		
		$this->display();
	}
	//新闻中心--新闻设置
	public function newsCenter(){
		$newsId=I('newsId');
		if(!empty($newsId)){
			$newsInfo=$this->modelActivity->getThisNews($newsId);
			$this->assign('newsInfo',$newsInfo);
		}
		$this->display();
	}
	//轮播海报上传结果出来
	public function uploadres(){
		$fileLoad=I('file');
		$local=I('local'); //位置,
		if(empty($fileLoad)){
			echo "非法操作!";
			exit;
		}
		$fileLoad='/business/'.$fileLoad;
		$comId=$this->comId;
		$pics=$this->modelActivity->getpics($comId);
		if(empty($pics)){
			//企业未设置海报轮播图
			$arrPics=array(
				'0'=>"",
				'1'=>"",
				'2'=>""
			);
			$arrPics[$local]=$fileLoad;

		}else{
			//更换或设置其他轮播图
			$arrPics=explode(',', $pics);
			if($arrPics[$local]==$fileLoad){
				echo "无需重新上传!";
				exit;				
			}else{
				$arrPics[$local]=$fileLoad;
				
			}
		}
		if(!empty($arrPics)){
			$picStr=implode(',', $arrPics);
			$saveRes=$this->modelActivity->updatapics($comId,$picStr);
			if($saveRes){
				$datas="上传成功!";
			}else{
				$datas="上传失败!";
			}		
		}
		echo $datas;
		
	}
	/*
	 * 
	 * 超级新人王、月度寿星编辑添加
	 * */			
	 public function lastTwo(){
	 	$type=I('type');//（type=1时超级新人王新增、编辑）（type=2是月度寿星新增/编辑）
		$data=I('data');
		$comId=$this->comId;
		if($data){
			//生日
			$data['birthday']=strtotime($data['year']."-".$data['month']."-".$data['day']);
			$data['birthday_month']=$data['month'];
			//修改成功后跳转地址
			switch($data['category']){
				case 1:
					$url = __APP__."/Show/actives";
					break;
				case 2:
					$url = __APP__."/Show/monthBirthday";
					break;						
			}
			if($data['apid']){
				//更新
				$picAll = explode('/', $data['pic']);
				if($picAll[0] == "Upload"){
					$data['pic']='/business/'.$data['pic'];
				}	
				$data['modify_time']=time();
				$updataRes=$this->modelActivity->updataActivityPerson($data['apid'],$data);
				if($updataRes){
					echo "<script>window.location='".$url."';</script>";						
				}else{
					echo "<script>window.location='".$url."';</script>";						
				}
							
			}else{
				//添加
				if(!empty($data['pic'])){
					$data['pic']='/business/'.$data['pic'];
				}				
				$data['creat_time']=time();
				$data['modify_time']=time();
				$data['com_id']=$comId;
				$addRes=$this->modelActivity->addActivityPerson($data);
				if($addRes){
					echo "<script>window.location='".$url."';</script>";						
				}else{
					echo "<script>window.location='".$url."';</script>";						
				}
				
			}
		}
		$aPid=I('aPid');
		if($aPid){
			//修改 传id 显示内容
			$resInfo=$this->modelActivity->getThisActivityPerson($aPid);
				$resInfo['year']=date('Y',$resInfo['birthday']);
				$resInfo['month']=date('m',$resInfo['birthday']);
				$resInfo['day']=date('d',$resInfo['birthday']);
			$this->assign('info',$resInfo);
		}
		if($type){
			$this->assign('type',$type);
		}
		$this->display();
	 }
	/*
	 * 
	 * 超级新人王、月度寿星列表
	 * */	 
	 public function lastTwoList(){
	 	$type=I('type');//（type=1时超级新人王新增、编辑）（type=2是月度寿星新增/编辑）
		$condition['category']=$type;
		$condition['is_delete']=0;
		$comId=$this->comId;
		$condition['com_id']=$comId;
		$number=$this->modelActivity->getActivityPersonCount($condition);
		$size=2;
		$page = new \Think\PageAjax($number, $size);
		$rollPage = 5;
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$res=$this->modelActivity->getActivityPerson($condition,$limit);
		foreach($res as $key=>$value){
			$res[$key]['birthday']=date('Y年m月d日',$value['birthday']);
		}
		$style = "pagesty";
		$onclass = "thispage";
		$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
		$this->assign('pagestr',$pagestr);			 	
		$this->assign('list',$res);
		$this->display();		
	 }
	 /*
	  * 删除超级新人王、月度寿星
	  * */
	 public function delActivityPerson(){
		$acitivityPid=I('aPId');
		if(!empty($acitivityPid)){
			$data['modify_time']=time();
			$data['is_delete']=1;
			$delRes=$this->modelActivity->updataActivityPerson($acitivityPid,$data);
			if($delRes){
				echo "删除成功!";
			}else{
				echo "删除失败!";
			}
		}		 	
		
	 }
	 
	 /*
	  * 
	  * 活动回顾/头号人物管理的编辑和增加页面
	  * */
	 public function activityEdit(){
	 	$type=I('type');//（type=1时活动回顾新增、编辑）（type=2是头号人物新增/编辑）
		$newsId=I('newsid');
		if($newsId){
			//编辑，显示编辑内容
			$resInfo=$this->modelActivity->getThisActivityInfo($newsId);
			$this->assign('info',$resInfo);
			$type=$resInfo['activity_type'];
		}
		if($type){
			$this->assign('type',$type);
		}	 	
		$this->display();
	 }
	/*
	 * 
	 * 活动回顾、头号人物列表
	 * */	 
	 public function ActivityNewPList(){
		$activitytype=I('activitytype');  //activitytype=1活动回顾   activitytype=2头号人物
		$comId=$this->comId;
		if(!empty($comId)){
			//分页
			$condition['com_id']=$comId;
			$condition['is_delete']=0;
			$condition['is_activity']=1;
			$condition['activity_type']=$activitytype;
			$number=$this->modelActivity->getNewsCount($condition);
			$size=2;
			$page = new \Think\PageAjax($number, $size);
			$rollPage = 5;
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page -> firstRow;  //起始行数
			$pagesize = $page -> listRows;   //每页显示的行数
			$limit = "$start , $pagesize";	
			$res=$this->modelActivity->getNews($condition,$limit);
			$style = "pagesty";
			$onclass = "thispage";
			$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
			$this->assign('pagestr',$pagestr);		
			$this->assign('list',$res);	
			$this->display();
		}
		
	 }
//退出登录
	public function logout(){
		session(null);
		header("Location:http://v.lishe.cn/company/login.html");
	}	 
	 
	 
}
