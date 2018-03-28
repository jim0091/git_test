<?php
namespace Home\Controller;
class WelfareController extends CommonController {
	/*
	 *企业福利 
	 * 章锐
	 * modify 2016/8/17
	 * */	
		public function __construct(){
		parent::__construct(); 
        if(empty($this->uid)){
            $url = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            redirect(__APP__."/Login/login/?refer=".$url);
        }
        $this->modelShow=D('Show');
		$field="item_config_id,cat_name";
		$cartgorys=$this->modelShow->getRecommend($this->comId,$field);
		foreach($cartgorys as $key=>$value){
			$configIds[$value['item_config_id']]=$value['item_config_id'];	
		}	
		if(!empty($configIds)){
			$nextC=$this->modelShow->getSubCategory($this->comId,$configIds);
		}	
		foreach($cartgorys as $key=>$value){
			foreach($nextC as $k=>$v){
				if($value['item_config_id']==$v['item_config_id']){
					$cartgorys[$key]['cat'][]=$v;
				}				
			}
		}	
		$this->assign('cartList',$cartgorys);
	}

    public function index(){
    	if(empty($_GET['from'])){
    		//header("Location:".__APP__."/Welfare/activity");
    		//exit;
    	}
        $comId=$this->comId;
		$res=$this->modelShow->getRecommend($comId);
		foreach($res as $key=>$value){
			$configIds[$value['item_config_id']]=$value['item_config_id'];	
			$recommendIds[$value['item_config_id']]=$value['recommend'];	
		}
		if(!empty($configIds)){
			$nextC=$this->modelShow->getSubCategory($comId,$configIds);
		}
		if(!empty($recommendIds)){
			$recommendItems=$this->modelShow->getRecommendItem($recommendIds,10);
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
        $this->display();  
    }
	public function cartgoryIndex(){
        $configId=I('cfgId');
        $comId=$this->comId;	
		if($configId){
			//取出该分类下的二级分类
			$carts=$this->modelShow->getThiCategory($comId,$configId);
			foreach($carts as $key=>$value){
				$itemIds[$value['cat_config_id']]=$value['item_ids'];	
			}
			if(!empty($itemIds)){
				$thisItems=$this->modelShow->getRecommendItem($itemIds,10);
			}
			foreach($carts as $key=>$value){
				foreach($thisItems as $keys=>$values){
					if($value['cat_config_id']==$keys){
						$carts[$key]['items']=$values;
					}
				}
					if(empty($value['item_ids'])){
						$catIds[]=$value['cat_id'];
					}				
			}
			foreach($catIds as $key => $value){
				if(!empty($value)){
					$cartgory[$value]=$this->modelShow->getLastCartgoey($value);
				}
			}
			foreach($cartgory as $key => $value){
				if(!empty($value)){
					$catIdres[$key]=$this->modelShow->getCatItem($value,10);
				}
			}	
			foreach($carts as $key=>$value){
				foreach($catIdres as $keys=>$values){
					if($value['cat_id']==$keys){
						$carts[$key]['items']=$values;
					}
				}				
			}
  		  	$this->assign('list',$carts);     
			$this->assign('thisId',$configId);
			$this->display();
		}
	}

    /*
     * 
     * 企业福利（商品列表）
     * */
    public function itemList(){
        $catId=I('cartId');
        $configId=I('cfgId');
		$ajaxget = I('ajaxget');
        $comId=$this->comId;
        if(empty($catId) && empty($configId)){
            //非法操作      
            header("Location:".__APP__."/Welfare/index");
            exit;   
        }   
        $size = 20;
        if($catId){
            //有分类,读取company_category_config表item_ids
            $catRes=$this->modelShow->getCatItems($comId,$catId);
            if(!empty($catRes['item_ids'])){
                $number=$this->modelShow->itemCount($catRes['item_ids']);
            }else{
            	if(!empty($catRes['cat_id'])){
	            	//查询三级分类
	            	$catIds=$this->modelShow->getLastCartgoey($catRes['cat_id']);
					//符合的商品数量
					$number=$this->modelShow->getCatItemCount($catIds);
				}
            }
			$cartName=$catRes['cat_name'];
        }else{
            //没分类读取公司配置表中的item_ids
            $confRes=$this->modelShow->getConfigItems($comId,$configId);
     
            if(!empty($confRes['item_ids'])){
                $number=$this->modelShow->itemCount($confRes['item_ids']);
            }else{
                $number=0;
            }  
			$cartName=$confRes['cat_name'];
        }
        $page = new \Think\PagePn($number, $size);
        $rollPage = 1;
        $page -> setConfig('prev' ,'上一页');
        $page -> setConfig('next' ,'下一页');
        $start = $page -> firstRow;  
        $pagesize = $page -> listRows; 
        $limit = "$start , $pagesize";
		if($catId){
			$items=$catRes['item_ids'];
		}else{
			$items=$confRes['item_ids'];
		}
        if(!empty($catRes['item_ids']) || !empty($confRes['item_ids'])){
            $itemIdArr=$this->modelShow->getItemListsIn($items,'a.item_id');
			foreach($itemIdArr as $key=>$value){
				$itemId[]=$value['item_id'];
			}
			$itemId=array_slice($itemId,$start,$pagesize);
			if (!empty($itemId)) {
				$itemRes=$this->modelShow->getItemListsIn(implode(',',$itemId));
			}            
        }else{
        	//读取三级分类
        	if(!empty($catIds)){
				$catItems=$this->modelShow->getThisCatItems($catIds);
	            $itemIdArr=$this->modelShow->getItemListsIn(implode(',',$catItems),'a.item_id');
				foreach($itemIdArr as $key=>$value){
					$itemId[]=$value['item_id'];
				}
				$itemId=array_slice($itemId,$start,$pagesize);
				if (!empty($itemId)) {
	            	$itemRes=$this->modelShow->getItemListsIn(implode(',',$itemId));   
	            }    	
        	}
        }	
        $style = "pageos";
        $onclass = "pageon";
        $pagestr = $page -> show($style,$onclass); 
		$itemArray=array(
			'list' => $itemRes,
			'name' => $cartName
		);
        $this -> assign('pagestr',$pagestr);            
        $this->assign('first',$itemArray);
		$this->assign('thisId',$configId);
        if (!empty($ajaxget)) {
			$this->display('itemListAjax');	
		}else{
			$this->display();
		}
    }
    public function activity(){
    	$this->display();
    }
    
    

}