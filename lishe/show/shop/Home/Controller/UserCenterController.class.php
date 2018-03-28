<?php
namespace Home\Controller;
class UserCenterController extends  CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__APP__."/Sign/index");
			exit;
		}
		$this->areaModel=M('site_area');
		$this->userModel=M('sysuser_user');//用户表
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
	}

	//添加收货地址
    public function addAddress(){  
        $refer=I('refer');
        if(empty($refer)){
            $refer=$_SERVER['HTTP_REFERER'];
        }
        if(empty($refer)){
            $refer=C('LISHE_URL');
        } 
    	$where=array(
		    'level'=>1,
		    'jd_pid'=>0
	    ); 	
	    $provinceArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
        $this->assign('refer',urldecode($refer));
	    $this->assign('provinceArr',$provinceArr);
    	$this->display();
    }


	 //修改默认的收货地址
    public function modifyDefAddr(){
       $addr_id=I('post.addr_id',0,'intval');
       $whereOne=array(
          'addr_id'=>$addr_id,
          'user_id'=>$this->uid
        );
       $dataOne=array('def_addr'=>1);
       $dataTwo=array('def_addr'=>0);

       $whereTwo['addr_id']=array('neq',$addr_id);
       $whereTwo['user_id']=$this->uid;
       
       $this->addrModel->where($whereTwo)->data($dataTwo)->save();
       $addrMod=$this->addrModel->where($whereOne)->data($dataOne)->save();
       if($addrMod){
        echo 'defAddrSuccess';
        exit;
       }else{
       	echo '';
       	exit;
       }

    }    
    //修改收货地址信息
    public function chgAddressInfo(){
    	$addrId=I('get.addrId','','trim');
        $refer=I('refer');
        if(empty($refer)){
            $refer=$_SERVER['HTTP_REFERER'];
        }
        if(empty($refer)){
            $refer=C('LISHE_URL');
        } 
    	if($addrId){
    		$where=array(
    			'addr_id'=>$addrId,
    			'user_id'=>$this->uid
    			);
    		$currAddressInfo=$this->addrModel->where($where)->find();
    		if($currAddressInfo){
    			$areaStr=explode(':',trim($currAddressInfo['area']));
        		$areaArr=explode('/',$areaStr[1]);
        		foreach($areaArr as $k=>$v){
		          if($k==0){ //省级
		             $this->assign('selectProvince',$v);
		               //省份 start
		            $where=array(
		                'level'=>1,
		                'jd_pid'=>0
		                );
		            $provinceArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('provinceArr',$provinceArr);
		            //省份 end
		          }elseif($k==1){ //市级
		             $this->assign('selectCity',$v);
		               //市级 start
		            $where=array(
		                'level'=>2,
		                'jd_pid'=>$areaArr[0]
		                );
		            $cityArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('cityArr',$cityArr);
		            //市级 end
		             
		          }elseif($k==2){ //区级

		             $this->assign('selectBal',$v);
		               //区级 start
		            $where=array(
		                'level'=>3,
		                'jd_pid'=>$areaArr[1]
		                );
		            $balArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('balArr',$balArr);
		            //区级 end

		          }elseif($k==3){//街道
		             $this->assign('selectTown',$v);
		               //区级 start
		            $where=array(
		                'level'=>4,
		                'jd_pid'=>$areaArr[2]
		                );
		            $townArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
		            $this->assign('townArr',$townArr);
		            //区级 end

		          }

		        }
                $this->assign('refer',urldecode($refer));
    			$this->assign('currAddressInfo',$currAddressInfo);
    		}

    	}
    	$this->display('editAddress');
    }

    // 对用户收货地址的编辑操作 start
    public function editUserAddrInfo(){
        $addrId=I('post.address_id',0,'intval');
        $consignee=I('post.consignee','','trim');
        $province=I('post.province','','trim');
        $provinceId=I('post.province_id','','trim');
        $city=I('post.city','','trim');
        $cityId=I('post.city_id','','trim');
        $area=I('post.area','','trim');
        $areaId=I('post.area_id','','trim');
        $town=I('post.town','','trim');
        $townId=I('post.town_id','','trim');
        $address=I('post.address','','trim');
        $zipcode=I('post.zipcode','','trim');
        $mobile=I('post.mobile','','trim');
        $isDefault=I('post.isDefault',0,'intval');
        $addrDetail=$province.'/'.$city.'/'.$area.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$areaId.'/'.$townId;
        if($isDefault == 1){
          $data=array('def_addr'=>0);
          $this->addrModel->where('user_id='.$this->uid)->data($data)->save();
        }
        $info=array(
            'name'=>$consignee,
            'area'=>$addrDetail,
            'addr'=>$address,
            'zip'=>$zipcode,
            'tel'=>'',
            'mobile'=>$mobile,
            'def_addr'=>$isDefault
            );
        $where=array(
            'addr_id'=>$addrId,
            'user_id'=>$this->uid
        );
        //添加收货地址        
        if (empty($addrId)) {
        	$info['user_id'] = $this->uid;
        	$addrModifier=$this->addrModel->data($info)->add();
        }else{        	
	        //修改收货地址
	        $addrModifier=$this->addrModel->where($where)->data($info)->save();
        }
        if($addrModifier){
            echo json_encode(array(1,'修改成功！'));
        }else{
            echo json_encode(array(0,'修改失败！'));
        }
    }
     // 对用户收货地址的编辑操作 end

    public function getCity(){
        $proItem=I('post.proItem',0,'intval');

        if($proItem > 0){
            $where=array(
                 'level'=>2,
                 'jd_pid'=>$proItem
                );
           $cityArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
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
           $where=array(
                'level'=>3,
                'jd_pid'=>$cityItem
            );
           $areaArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
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
         $where=array(
                'level'=>4,
                'jd_pid'=>$areaItem
            );
      }
      $townArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
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