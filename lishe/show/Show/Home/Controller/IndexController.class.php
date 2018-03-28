<?php
namespace Home\Controller;
class IndexController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->comId=session('comId');
		$this->dShow=D('Show');	
		$this->host=$_SERVER['HTTP_HOST'];	
		if(empty($this->uid)){
			header("Location:https://".$this->host.__APP__."/Login/index");
			exit;
		}else{
			$comId=$this->comId;
			$referUrl=A('Preview')->checkComId($comId);
			if($referUrl!==0){
				header("Location:".$referUrl); 	
				exit;		
			}
				
		}
		$host=$_SERVER['HTTP_HOST'];
		$hosts=current(explode(".",$host));
		$hasShowDomain=D('Show')->getHasShow(array('1464317460037','1463542509407'));
		if(!in_array($hosts,$hasShowDomain)){
			$referIndex=$this->dShow->getFieldCompanyInfo(array('com_domain'=>$hosts),'index');		
			if(($referIndex != $host) && (!strripos($referIndex,$host.'/index'))  && (!strripos($referIndex,$host))){
				if(!empty($referIndex)){
					header("Location:".$referIndex);
					exit;
				}			
			}
		}		
		$account=session('account');
		$this->uid=$account['id'];
	}
	/*
	 * 
	 * 企业秀前台首页
	 * */
	
	public function index(){
		//取出企业轮播图
		$comId=$this->comId;
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		$field="carousel_poster";
		$companyInfo=$this->dShow->getCompanyInfo($condition,$field);		
		$pics=$companyInfo['carousel_poster'];
		$picsArr=explode(",", $pics);
		//图片zr
		$this->assign('pics',$picsArr);
		//头条新闻
		unset($condition);
		$condition['com_id']=$comId;
		$condition['rank']=3;
		$toTop=$this->dShow->getTopNews($condition,1);
		unset($condition);
		$condition['com_id']=$comId;
		$condition['rank']=2;
		$top=$this->dShow->getTopNews($condition,3);
		foreach($top as $key => $value){
			$top[$key]['creat_time']=date('Y-m-d',$value['creat_time']);
		}
		$topNews=array(
			'toTop'=> $toTop[0],
			'top'  => $top
		);
		$this->assign('news',$topNews);
		//首页超级新人王
		$newP=$this->dShow->getNewsP($comId,1);
		//图片zr
		//首页月度寿星
		$monthB=$this->dShow->getNewsP($comId,2);
		foreach($monthB as $key=>$value){
			$monthB[$key]['birthday']=date('m月d日',$value['birthday']);
		}
		$this->assign('newP',$newP);
		$this->assign('monthB',$monthB);
		
		$this->display();
	}
	
	//关于我们
	public function about(){
		$comId=$this->comId;
		//图片zr
		$res=$this->dShow->aboutC($comId);
		$this->assign('company',$res);		
		$this->display();
	}
	
	//企业简介
	public function company(){
		$comId=$this->comId;
		//图片zr
		$res=$this->dShow->aboutC($comId);
		$this->assign('info',$res);
		$this->display();
	}
	
	//组织架构
	public function organization(){
		$comId=$this->comId;
		$res=$this->dShow->getFramework($comId);
		//图片zr
		$this->assign('pic',$res);
		$this->display();
	}
	
	//伙伴动态
	public function partner(){
		$this->display();
	}	
	
	//活动回顾页面
	public function activity(){	
		$this->display();
	}
	
	public function activityList(){
		$comId=$this->comId;
		$activitytype=I('activitytype');  //activitytype=1活动回顾   activitytype=2头号人物
		if(!empty($comId)){
			//分页
			$condition['com_id']=$comId;
			$condition['is_delete']=0;
			$condition['is_activity']=1;
			$condition['activity_type']=$activitytype;
			$number=$this->dShow->getNewsCount($condition);
			$size=3;
			$page = new \Think\PageAjax($number, $size);
			$rollPage = 5;
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page -> firstRow;  //起始行数
			$pagesize = $page -> listRows;   //每页显示的行数
			$limit = "$start , $pagesize";	
			$res=$this->dShow->getNews($condition,$limit);
			foreach($res as $key=>$value){
				$res[$key]['creat_time']=date('Y-m-d',$value['creat_time']);
			}
			$style = "pageos";
			$onclass = "pageon";
			$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
			$this->assign('pagestr',$pagestr);		
			$this->assign('list',$res);		
			if($activitytype==1){
				//活动回顾页面列表
				$this->display();
			}else{
				//头号人物页面列表
				$this->display('importantList');
			}
			
		}	
	}


	//头号人物页面
	public function important(){
		
		$this->display();
	}
	
	public function importantList(){
		
		$this->display();
	}
	
	//超级新人王页
	public function super(){
		$comId=$this->comId;
		$condition['com_id']=$comId;
		$condition['category']=1;
		$condition['is_delete']=0;
		$field="id,pic";
		$res=$this->dShow->getActivityPerson($condition,$field);
		$personId=I('personId');
		unset($field);
		$fild="name,position,pic,content,love";
		if(empty($personId)){
			$defaultPerson=$this->dShow->getActivityPerson($condition,$field,1);
			$defaultPerson=$defaultPerson[0];
		}else{
			//默认
			$defaultPerson=$this->dShow->getThisPerson($comId,$personId,$field);
		}
		//图片轮播
		$this->assign('imgList',$res);
		$this->assign('personInfo',$defaultPerson);
		$this->display();		
	}
	
	//月度寿星页	 
	 public function birthday(){
	 	$thismonth=date('m');
		$thismonth=str_replace("0", "", $thismonth);
		$this->assign('thismonth',$thismonth);
		$this->display();
	 }
	 //月度寿星列表
	 public function birthdayList(){
	 	$type=I('type');//（type=1时超级新人王新增、编辑）（type=2是月度寿星新增/编辑）
		$condition['category']=$type;
		$condition['is_delete']=0;
		$comId=$this->comId;
		$condition['com_id']=$comId;
		$thismonth=date('m');
		$condition['birthday_month']=$thismonth;
		$number=$this->dShow->getActivityPersonCount($condition);
		$size=9;
		$page = new \Think\PageAjax($number, $size);
		$rollPage = 5;
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$field="id,name,pic,position,birthday";
		$res=$this->dShow->getActivityPerson($condition,$field,$limit);
		if($res){
			foreach($res as $key=>$value){
				$res[$key]['birthday']=date('m月d日',$value['birthday']);
			}
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
		$this->assign('pagestr',$pagestr);			 	
		$this->assign('list',$res);	 	
		$this->display();
	 }
	 //月度寿星细节页
	 public function birthdayDetail(){
	 	$personId=I('personId');
		$comId=$this->comId;
		if($personId){
			$filed="name,position,pic,birthday_wish,birthday,content,creat_time";
			$personInfo=$this->dShow->getThisPerson($comId,$personId,$field);
			$personInfo['monthDay']=date('m月d日',$personInfo['birthday']);
			$personInfo['birthday']=date('m月d日',$personInfo['birthday']);
			$personInfo['creat_time']=date('Y-m-d H:i',$personInfo['creat_time']);
			$this->assign('personInfo',$personInfo);
			$this->display();
		}else{
			header("Location:".__APP__."/Index/birthdayList");
		}
	 }
	
	
	//行业聚焦
	public function industry(){
		
		
		$this->display();
	}
	
	//媒体报道
	public function coverage(){
		
		
		$this->display();
	}
	//新闻部分
	public function news(){
		$type=I('type');
		$comId=$this->comId;
		if(!empty($comId)){
			//分页
			$condition['category']=$type;
			$condition['com_id']=$comId;
			$condition['is_delete']=0;
			$condition['is_activity']=0;
			$number=$this->dShow->getNewsCount($condition);
			$size=10;
			$page = new \Think\PageAjax($number, $size);
			$rollPage = 5;
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page -> firstRow;  //起始行数
			$pagesize = $page -> listRows;   //每页显示的行数
			$limit = "$start , $pagesize";	
			$news=$this->dShow->getNews($condition,$limit);
			foreach($news as $key=>$value){
				$news[$key]['creat_time']=date('Y-m-d',$value['creat_time']);
			}
			$style = "pageos";
			$onclass = "pageon";
			$pagestr = $page -> show($style,$onclass,"");  //组装分页字符串	
			$this->assign('pagestr',$pagestr);		
			$this->assign('news',$news);	
		}
		if($type==1){
			$this->display('industryList');
		}else if($type==2){
			$this->display('coverageList');
		}else if($type==3){
			$this->display('noticeList');
		}		
	}
	//媒体报道新闻详情页
	public function coverageDetail(){
		$newsId=I('newsId');
		$type=I('type');
		if($newsId){
			$comId=$this->comId;
			$newsInfo=$this->dShow->getThisNews($comId,$newsId);
			$newsInfo['creat_time']=date('Y-m-d H:i',$newsInfo['creat_time']);
			$this->assign('newsInfo',$newsInfo);		
			$this->assign('type',$type);	
			$this->display();
		}else{
			header("Location:".__APP__."/Index/industry");
		}
	}	
	 	
	//企业公告
	public function notice(){
		$this->display();
	}
	//新人王投票
	public function vote(){
		$personId=I('personId');
		$userId=$this->uid;
		$comId=$this->comId;
		if(!empty($personId) && !empty($userId) && !empty($comId)){
			$checkRes=$this->dShow->checkVote($personId,$comId,$userId);
			if($checkRes){
				echo "你已经投过票了!";
			}else{
				$addRes=$this->dShow->addVote($personId,$comId,$userId);
				if($addRes){
					$voteRes=$this->dShow->voteSucess($personId);
					if($voteRes){
						echo 1;
					}else{
						echo "投票失败!";
					}
				}
				
			}
		}
	}
}