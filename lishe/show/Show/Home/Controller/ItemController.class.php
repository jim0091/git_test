<?php
namespace Home\Controller;
class ItemController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->comId=session('comId');
		$this->dShow=D('Show');	
		if(empty($this->uid)){
			header("Location:".__APP__."/Login/index");
		}		
			
	}
	/*
	 * 
	 * 企业福利
	 * */
	public function index(){
	 	$comId=$this->comId;
		$res=$this->dShow->getRecommend($comId);
		foreach($res as $key=>$value){
			$configIds[$value['item_config_id']]=$value['item_config_id'];	
			$recommendIds[$value['item_config_id']]=$value['recommend'];	
		}
		if(!empty($configIds)){
			$nextC=$this->dShow->getSubCategory($comId,$configIds);
		}
		if(!empty($recommendIds)){
			$recommendItems=$this->dShow->getRecommendItem($recommendIds,10);
		}
		foreach($res as $key=>$value){
			foreach($nextC as $k=>$v){
				if($value['item_config_id']==$v['item_config_id']){
					$res[$key]['cat'][]=$v;
				}				
			}
			foreach($recommendItems as $keys => $values){
				if($value['item_config_id']==$keys){
					$res[$key]['items']=$values;
				}
			}
		}
		$this->assign('list',$res);			
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		$templete=$this->dShow->getCompanyInfo($condition,"templete");
		$tempName=$templete['templete'];	
		if(!empty($tempName)){
			$isHasTemp=$this->dShow->isSetTemp($tempName);
			if($isHasTemp){
				$tempName=$tempName.'Index';
			}else{
				$tempName='index';
			}
		}else{
			$tempName='index';
		}				
		$this->display($tempName);	
	 }
	/*
	 * 
	 * 企业福利（商品列表）
	 * */
	public function itemList(){
		$catId=I('catId');
		$configId=I('configId');
		$comId=$this->comId;
		if(empty($catId) && empty($configId)){
			//非法操作		
			header("Location:".__APP__."/Show/welfare");
			exit;	
		}	
		$size = 50;
		if($catId){
			//有分类,读取company_category_config表item_ids
			$catRes=$this->dShow->getCatItems($comId,$catId);
			if(!empty($catRes['item_ids'])){
				$number=$this->dShow->itemCount($catRes['item_ids']);
			}else{
				$number=0;
			}
		}else{
			if($configId){
				//没分类读取公司配置表中的item_ids
				$confRes=$this->dShow->getConfigItems($comId,$configId);
				if(!empty($confRes['item_ids'])){
					$number=$this->dShow->itemCount($confRes['item_ids']);
				}else{
					$number=0;
				}			
			}		
		}
		$page = new \Think\Page($number,$size);
		$rollPage = 5; 
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  
		$pagesize = $page -> listRows; 
		$limit = "$start , $pagesize";
		//取出所有有效item_id
		if($catId){
			//有分类,读取company_category_config表item_ids
			if(!empty($catRes['item_ids'])){
				$itemIdArr=$this->dShow->getItemIds($catRes['item_ids']);
			}else{
				$itemIdArr="";
			}
		}else{
			if($configId){
				//有分类,读取company_category_config表item_ids
				if(!empty($confRes['item_ids'])){
					$itemIdArr=$this->dShow->getItemIds($confRes['item_ids']);
				}else{
					$itemIdArr="";
				}
			}			
		}
		if(!empty($itemIdArr)){
			foreach($itemIdArr as $key=>$value){
				$itemId[]=$value['item_id'];
			}
		}
		$itemIds=array_slice($itemId,$start,$pagesize);
		//取出商品列表
		if(!empty($itemIds)){
			$itemRes=$this->dShow->getItemList($itemIds);
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass); 
		$this -> assign('pagestr',$pagestr);			
		$this->assign('list',$itemRes);
		//分类展示
	 	$comId=$this->comId;
		$catName=$this->dShow->getThisCats($comId,$configId);
		$catNames=$this->dShow->getThisCatsMore($comId,$configId);
		if($catId){
			$thisName=$this->dShow->getThisCatName($comId,$catId);
			$catName['thisName']=$thisName;
		}else{
			$catName['thisName']=$confRes['cat_name'];
		}
		$this->assign('catName',$catName);
		$this->assign('catNames',$catNames);
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		$templete=$this->dShow->getCompanyInfo($condition,"templete");
		$tempName=$templete['templete'];	
		if(!empty($tempName)){
			$isHasTemp=$this->dShow->isSetTemp($tempName);
			if($isHasTemp){
				$tempName=$tempName.'ItemList';
			}else{
				$tempName='itemList';
			}			
		}else{
			$tempName='itemList';
		}				

		
		$this->display($tempName);			
	}


}