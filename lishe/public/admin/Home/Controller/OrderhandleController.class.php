<?php
namespace Home\Controller;
class OrderhandleController extends CommonController{
/*
 * 订单处理
 * 2016/12/19 zhangrui
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dActivity=D('Activity');
		$this->dOrder=D('Order');
		$this->dGoods=D('Goods');		
	}	
/*
 * 订单修改用户收货信息
 * */	
	public function modifyAddr(){
		$tid=I('tid');
		if($tid){
			$info=$this->dOrder->getThisOrderInfo($tid,'tid,buyer_area,receiver_name,receiver_mobile,receiver_address');
    		if($info){
        		$areaArr=explode('/',$info['buyer_area']);
        		foreach($areaArr as $k=>$v){
		          if($k==0){ //省级
		             $this->assign('selectProvince',$v);
		               //省份 start
		            $provinceArr = $this->dOrder->addrDetail(1,0);
		            $this->assign('provinceArr',$provinceArr);
		            //省份 end
		          }else if($k==1){ //市级
		             $this->assign('selectCity',$v);
		               //市级 start
		            $cityArr =  $this->dOrder->addrDetail(2,$areaArr[0]);
		            $this->assign('cityArr',$cityArr);
		            //市级 end
		          }else if($k==2){ //区级

		             $this->assign('selectBal',$v);
		               //区级 start
		            $balArr = $this->dOrder->addrDetail(3,$areaArr[1]);
		            $this->assign('balArr',$balArr);
		            //区级 end

		          }else if($k==3){//街道
		             $this->assign('selectTown',$v);
		               //区级 start
		            $where=array(
		                'level'=>4,
		                'jd_pid'=>$areaArr[2]
		                );
		            $townArr = $this->dOrder->addrDetail(4,$areaArr[2]);
		            $this->assign('townArr',$townArr);
		            //区级 end
		          }

		        }
    			$this->assign('currAddressInfo',$currAddressInfo);
				$this->assign('info',$info);
    		}	
			$this->display('Order/modifyAddr');		
		}else{
			echo 'Error : no tid';			
		}
	}
/*
 * 修改订单收货信息
 * */	
	public function editOrderAddr(){
      	$tid=I('post.tid',0);
        $data['receiver_name']=I('post.consignee','','trim');
        $data['receiver_state']=I('post.province','','trim');
        $provinceId=I('post.province_id','','trim');
        $data['receiver_city']=I('post.city','','trim');
        $cityId=I('post.city_id','','trim');
        $data['receiver_district']=I('post.area','','trim');
        $areaId=I('post.area_id','','trim');
        $townId=I('post.town_id','','trim');
        $data['receiver_address']=I('post.address','','trim');
        $data['receiver_mobile']=I('post.mobile','','trim');
        $data['buyer_area']=$provinceId.'/'.$cityId.'/'.$areaId.'/'.$townId;	
		$field='receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,receiver_mobile,buyer_area';
		$res=$this->dOrder->editTradeInfo($tid,$data,$field);
		if($res){
			$this->ajaxReturn(array(1,'订单:'.$tid.'收货信息更新成功!'));
		}else{
			$this->ajaxReturn(array(0,'订单:'.$tid.'收货信息更新失败!'));
		}
	}
    public function getCity(){
        $proItem=I('post.proItem',0,'intval');
        if($proItem > 0){
           $cityArr = $this->dOrder->addrDetail(2,$proItem);
           $cityHtml.="<option value='0' selected>请选择</option>";
           if($cityArr){
               foreach($cityArr as $k => $v){
                 $cityHtml.= "<option value='".$v['jd_id']."' data-value='".$v['name']."'>".$v['name']."</option>";
               }
               echo $cityHtml;
           }else{
               echo 0;
           }
            
        }
    }

    public function getArea(){
        $cityItem=I('post.cityItem',0,'intval');
        if($cityItem > 0){
           $areaArr = $this->dOrder->addrDetail(3,$cityItem);
           $areaHtml.="<option value='0' selected>请选择</option>";
           if($areaArr){
                foreach($areaArr as $k1 => $v1){
                  $areaHtml.= "<option value='".$v1['jd_id']."' data-value='".$v1['name']."'>".$v1['name']."</option>";
                }
                echo $areaHtml;
           }else{
                echo 0;
           }
           
        }
    }

     public function getTown(){
      $areaItem=I('post.areaItem',0,'intval');
      if($areaItem > 0){
       $townArr = $this->dOrder->addrDetail(4,$areaItem);
      $townHtml.="<option value='0' selected>请选择</option>";
      if($townArr){
            foreach($townArr as $k1 => $v1){
              $townHtml.= "<option value='".$v1['jd_id']."' data-value='".$v1['name']."'>".$v1['name']."</option>";
            }
            echo $townHtml;
       }else{
            echo 0;
       }
  	  }
	 }	 	

	
	
}
