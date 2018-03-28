<?php
namespace Home\Controller;
class GawayController extends CommonController {

	public function __construct(){
		parent::__construct();
		$this->modelConfig=M('company_gaway_config');
		$this->modelItem=M('company_gaway_item');
		$this->modelDetail=M('company_gaway_item_detail');
		// 心意攻略
		$this->modelRaider=M('raiders_config');
		$this->modelRaiderItem=M('raiders_item_config');
	}
    
    // 赠送列表 开始  20160705
	public function index(){
	    $flag = I('get.flag',0,'intval');
	    $this -> assign('flag',$flag);
        $resConfig = $this->modelConfig ->field('id')->where('flag='.$flag)->select();
		if($resConfig){
	        foreach($resConfig as $k => $v){
	        	$arrConfig[$k] = $v['id'];
			}
       $strConfig = implode(',',$arrConfig);
       $where['pid_object'] = array('in',$strConfig);
       $resItem=$this->modelItem->where($where)->order('id desc')->select();
       if($resItem){
		$this->assign('resItem',$resItem);
		$num = (ceil((count($resItem)/3))*390).'px'; 
		$this->assign('num',$num);
       }
      
      }
       
       $configDis = $this->modelConfig->field('id,send_type,flag')->select();
       if($configDis){
	   $this-> assign('configDis',$configDis);
	   }

	   $this -> display('Gaway');
	}
	// 赠送列表 end  20160705

	// 赠送商品详情页 开始 20160705
	public function detail(){
		$id = I('get.id',0,'intval');
		$resItem = $this->modelItem->where('id ='.$id)->find();
		if($resItem){
		$this -> assign('resItem',$resItem);
	   }
	 
		$resItemDetail = $this->modelDetail->where('pid='.$id)->select();
		if($resItemDetail){
		$this->assign('resItemDetail',$resItemDetail);
	    }

       $configDis = $this->modelConfig->field('id,send_type,flag')->select();
       if($configDis){
	   $this-> assign('configDis',$configDis); 
	   }

		$this -> display('Gawaylist');
	}
	// 赠送商品详情页 结束 20160705

	// 搜索 20160707 start
	public function search(){
	   $id = I('get.id',0,'intval');
	   
	   $where['pid_object'] = $id;
	   $resItem=$this->modelItem->where($where)->order('id desc')->select();
	   if($resItem){
	   $this->assign('resItem',$resItem);
	    $num = (ceil((count($resItem)/3))*390).'px'; 
       $this->assign('num',$num);
      }

	   $configDis = $this->modelConfig->field('id,send_type,flag')->select();
	   if($configDis){
	   $this-> assign('configDis',$configDis);
	   }

	   $this -> display('Gaway');
	}
	// 搜索 20160707 start

	// 心意攻略 20160713 start
	public function strategy(){
		$id = I('get.id',0,'intval');
		$resItem = $this->modelRaider->where('id ='.$id)->find();
		if($resItem){
			$this -> assign('resItem',$resItem);
	    }

	    $resItemDetail =  $this->modelRaiderItem->where('raiders_id='.$id)->select();
		if($resItemDetail){
			$this->assign('resItemDetail',$resItemDetail);
	    }

	   $configDis = $this->modelConfig->field('id,send_type,flag')->select();
       if($configDis){
	   		$this-> assign('configDis',$configDis); 
	   }

		$this -> display('strategyDetail');



	}
	// 心意攻略 20160713 end

}