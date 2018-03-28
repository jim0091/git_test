<?php
namespace Home\Controller;
class OrderController extends CommonController {
  public function __construct(){
    parent::__construct();
      if(empty($this->uid)){
        redirect(__APP__."/Login/login/index");
      }
      $this->areaModel=M('site_area');
      $this->userModel=M('sysuser_user');//用户表
      $this->addrModel=M('sysuser_user_addrs'); //收货地址表
      $this->cartModel=M('systrade_cart'); //购物车表
      $this->itemModel=M('sysitem_item');//产品表
      $this->skuStoreModel=M('sysitem_sku_store');//货品的库存
      $this->shopModel=M('sysshop_shop');//店铺信息
      $this->logisticsModel=M('syslogistics_dlytmpl');//快递信息表     
      $this->postageModel=M('syspromotion_freepostage');//包邮表
      $this->tradeModel=M('systrade_trade');//订单主表
      $this->orderModel=M('systrade_order');//订单附表
      $this->syncTradeModel=M('systrade_sync_trade');//物流表
      $this->deliveryModel=M('syslogistics_delivery');//物流信息表 
      $this->itemStatusModel=M('sysitem_item_status');//商品状态表  
        $this->afterSaleModel=M('systrade_aftersales');//售后订单表
        $this->adminTradeLogModel=M('system_admin_trade_log');//订单日志表  
       
      $aftersalesStatus=array(
		'WAIT_EARLY_PROCESS'=>'待审核',
		'WAIT_PROCESS'=>'待商家审核',
		'SELLER_REFUSE'=>'商家拒绝',
		'REFUND_PROCESS'=>'待退款',
		'WAIT_BUYER_SEND_GOODS'=>'待用户回寄',
		'WAIT_SELLER_CONFIRM_GOODS'=>'待商家收货',
		'SELLER_SEND_GOODS'=>'商家已回寄',
		'SUCCESS'=>'已完成',
	);  
	$this->assign('aftersalesStatus',$aftersalesStatus);     
  }
  // 提交订单  start
  public function order(){
        // 判断购物车中是否有货
        //判断shop_id的值，遍历
        $shopIdInfo=$this->cartModel->distinct(true)->field('shop_id')->where('user_id='.$this->uid)->select();
        foreach($shopIdInfo as $k2=>$v2){
        $shopIdArr[$k2] = $v2['shop_id'];
        }
        $shopIdStr=implode(',',$shopIdArr);
        if($shopIdStr){
           $where['shop_id']=array('in',$shopIdStr);
           $shopInfo=$this->shopModel->where($where)->field('shop_id,shop_name')->select();
           if(empty($shopInfo)){
            exit;
           }            
        }
        $cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid)->field('c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price,s.weight')->select();
        if(empty($cartInfo)){
         $url = __APP__.'/Order/cartEmpty';
         header("location:$url");
         exit;
        }else{
            foreach($cartInfo as $k=>$v){
                $cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
                // ++++++++++++++++++++++++++++++++++
                $itemId[]=$v['item_id'];
                $itemNum[$v['item_id']]=$v['quantity'];
                $stock[$v['item_id']]=33;//默认有货
                 // ++++++++++++++++++++++++++++++++++
                $cartInfo[$k]['goodsTotalPrice'] = floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']);
                $shopTotalPrice += $cartInfo[$k]['goodsTotalPrice'];

                foreach($shopInfo as $k1=>$v1){
                    if($v1['shop_id']==$v['shop_id']){
                      $shopInfo[$k1]['totalPrice'] += floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']); 
                       $shopInfo[$k1]['totalWeight'] += floatval($cartInfo[$k]['weight']) * intval($cartInfo[$k]['quantity']);                    
                    }
                }
            }            
        } 

        $itemStatusInfo=$this->itemStatusModel->where('item_id in('.implode(',',$itemId).')')->field('item_id,approve_status')->select();
        if($itemStatusInfo){
          foreach($cartInfo as $k15=>$v15){
            foreach($itemStatusInfo as $k16=>$v16){
              if($v15['item_id']==$v16['item_id']){
                $cartInfo[$k15]['approve_status']=$v16['approve_status'];
                break;
              }
            }
          }
       }
        //判断用户地址是否为一条
        $whereOneSql=array(
            'user_id'=>$this->uid
          );
        $addrNum=$this->addrModel->where($whereOneSql)->count();
        if($addrNum == 1){
          $dataOneSql=array(
             'def_addr'=>1   //设为默认收货地址
            );
        $this->addrModel->where($whereOneSql)->data($dataOneSql)->save();
        }
        //得到用户收货地址
        $whereAddr=array(
            'user_id'=>$this->uid, 
            'def_addr'=>1   //默认收货地址
            );
        $addrInfo=$this->addrModel->where($whereAddr)->find();
        if($addrInfo){
            $addrArr=explode(':',$addrInfo['area']);
            $addrInfo['area']=$addrArr;
            //判断库存 start+++++++++++++++++++++++++++++++++++++
            $addrDefaultIdArr=explode('/',$addrArr[1]);
            for($i=0;$i<=3;$i++){
              $areaId[$i]=intval($addrDefaultIdArr[$i]);
            }
            $areaStr=implode('_',$areaId);
            // echo $areaStr;
            $itemIdStr=implode(',',$itemId); 
            $condition=array(
           'item_id'=>array('in',$itemIdStr)
            );
            $sku=array();
            $checkItem=$this->itemModel->field('jd_sku,item_id')->where($condition)->select();
            if(!empty($checkItem)){
              foreach($checkItem as $key=>$value){
                if($value['jd_sku']>0){   //京东
                    $sku[]=array(
                      'id'=>$value['item_id'],
                      'num'=>$itemNum[$value['item_id']]
                    );
                }else{       //自营
                    $skuIdArr=$this->cartModel->where('item_id='.$value['item_id'])->field('sku_id')->find();
                    $skuStoreArr=$this->skuStoreModel->where('sku_id='.$skuIdArr['sku_id'])->field('store')->find();
                    $skuItemArr[$value['item_id']]=$skuStoreArr['store'];
                    foreach($cartInfo as $k3=>$v3){
                       foreach($skuItemArr as $k4=>$v4){
                          if($v3['item_id']==$k4){
                            if($v4 > 0){
                              $cartInfo[$k3]['store']='1'; //1表有货
                            }else{
                              $cartInfo[$k3]['store']='0'; //0表无货
                            }
                            
                          }
                       }
                    }
                }               
              }
            }
            $data=array(
                'items'=>json_encode($sku),
                 'area'=>$areaStr
             );
           $url=C('API_STORE').'checkCartStock';
           $result=$this->requestPost($url,$data);
           $retArr=json_decode($result,true);
           $jdResultArr = $retArr['data'];
           foreach($cartInfo as $k1=>$v1){
                foreach($jdResultArr as $k2=>$v2){
                    if($v1['item_id']==$k2){
                        if($v2 == 33){         //京东
                          $cartInfo[$k1]['store']='1'; //1表有货
                        }else{
                          $cartInfo[$k1]['store']='0'; //0表无货
                        }                        
                    }
                }
           }
            //判断库存  end+++++++++++++++++++++++++++++++++++++++++++++++++
            $this->assign('addrInfo',$addrInfo); 
        }

          // 算出配送方式的money start
           $addrFeeProvince=$addrDefaultIdArr[0];//省
           $addrFeeCity=$addrDefaultIdArr[1];//市
           $addrFeeArea=$addrDefaultIdArr[2];//区
           foreach($shopInfo as $k10=>$v10){
             $shopFeeAreaTotal = 0; //初始化
             $shopExpressInfo=$this->logisticsModel->where('shop_id='.$v10['shop_id'])->field('fee_conf,template_id')->find();
             $shopFeeConf=unserialize($shopExpressInfo['fee_conf']);
             
             foreach($shopFeeConf as $k9=>$v9){
                //如果省市区都不在,及自营
                if(count($shopFeeConf) == 1){
                   $shopFeeAreaTotal = floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }

                $shopPressAreaArr=array();
                $shopPressAreaArr=explode(',',$v9['area']);
                 
               
                if(in_array($addrFeeProvince,$shopPressAreaArr)){  //省
                   $shopFeeAreaTotal += floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }

                 if(in_array($addrFeeCity,$shopPressAreaArr)){ //市  $addrFeeCity
                   $shopFeeAreaTotal += floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }

                 if(in_array($addrFeeArea,$shopPressAreaArr)){ //区  $addrFeeArea
                   $shopFeeAreaTotal += floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }
                                
             }
  
             $shopInfo[$k10]['delivery'] = $shopFeeAreaTotal;
             $shopInfo[$k10]['template_id']=$shopExpressInfo['template_id'];
          }
   
          //算出配送方式的money end

          //包邮 开始 20160722

           $postInfo=$this->postageModel->select();
           foreach($shopInfo as $k11=>$v11){
              foreach($postInfo as $k12=>$v12){
                 if($v11['shop_id']==$v12['shop_id']){
                    $shopInfo[$k11]['postName']=intval($v12['limit_money']);
                    //判断是否增加运费
                    if($shopInfo[$k11]['postName'] > $shopInfo[$k11]['totalPrice']){
                        // $shopInfo[$k11]['totalPrice'] += $shopInfo[$k11]['delivery'];
                        $shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'] + $shopInfo[$k11]['delivery'];

                        $shopTotalPrice += $shopInfo[$k11]['delivery']; //若不包邮，总价加邮费
                    }else{
                        $shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'];
                        $shopInfo[$k11]['delivery']="0.00";
                    }
                    break;
                 }
              }
           }
          //包邮 结束 20160722
     $this->assign('shopInfo',$shopInfo);  
     $this->assign('shopTotalPrice',$shopTotalPrice); //总价格
     $this->assign('cartInfo',$cartInfo);  //购物车信息
     $this->display('order');
  }
  // 提交订单  end
  // 购物信息为空 start
  public function cartEmpty(){
         // 判断用户是否登录,得到用户的id
         $cartInfo=$this->cartModel->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid)->field('b.cart_id,b.shop_id,b.item_id,b.sku_id,b.title,b.image_default_id,b.quantity,a.price')->select();
         if($cartInfo){
             $url = __APP__.'/Order/cart';
             header("location:$url");
             exit;
         }
      $this->display('cartEmpty');
  }
  // 购物信息为空 end
     public function requestPost($url='', $data=array()) {
        if(empty($url) || empty($data)){
            return false;
        }
        $o="";
        foreach($data as $k=>$v){
            $o.="$k=".$v."&";
        }
        $param=substr($o,0,-1);
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
  // 购物车页面 20160715 start 
    public function cart(){
        // 判断用户是否登录,得到用户的id
        //判断shop_id的值，遍历
        $shopIdInfo=$this->cartModel->distinct(true)->field('shop_id')->where('user_id='.$this->uid)->select();
        foreach($shopIdInfo as $k2=>$v2){
        $shopIdArr[$k2] = $v2['shop_id'];
        }
        $shopIdStr=implode(',',$shopIdArr);
        if($shopIdStr){
           $where['shop_id']=array('in',$shopIdStr);
            $shopInfo=$this->shopModel->where($where)->field('shop_id,shop_name')->select();
            if($shopInfo){
               $this->assign('shopInfo',$shopInfo);
            }

        }
        $cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid)->field('c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price,s.spec_info')->select();
        if(empty($cartInfo)){
         $url = __APP__.'/Order/cartEmpty';
         header("location:$url");
         exit;
        }else{
            foreach($cartInfo as $k=>$v){
                $cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
                $cartInfo[$k]['goodsTotalPrice'] = floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']);
                $totalPrice += $cartInfo[$k]['goodsTotalPrice'];
            }
            $this->assign('totalPrice',$totalPrice); //总价格
            $this->assign('cartInfo',$cartInfo);  //购物车信息
        } 
                
      $this->display('cart');
    }
    // 购物车页面 20160715  end 

    // 购物车货品删除  start 20160718
    public function delFromCart(){
        $gid=I('get.gid',0,'intval');
        $where=array(
          'cart_id'=>$gid,
          'user_id'=>$this->uid
          );
        $delCartNum=$this->cartModel->where($where)->delete();
        if($delCartNum){
        echo "success";
        }else{
        echo "fail";
        }
    }
    // 购物车货品删除  end 20160718

    // 购物车中货品数量的增加/减少 start 20160718 
    public function opCart(){
        $op = I('get.op','','trim');
        $gid = I('get.gid',0,'intval'); 
        $num = I('get.num',0,'intval'); //当前货品的购买数量
        $sku_id=I('get.sku_id',0,'intval');
        $where=array(
          'cart_id'=>$gid,
          'user_id'=>$this->uid
          );

        if($op=='dec'){    //表货品数目减少
            $cartNumDec=$this->cartModel->where($where)->setDec('quantity',1);    
        }elseif($op=='inc'){  //表货品数目增加
            $cartNumDec=$this->cartModel->where($where)->setInc('quantity',1);
            $skuArr = $this->skuStoreModel->field('store')->where('sku_id='.$sku_id)->find();
            //echo $skuArr['store']; //货品的库存数量
            if($num >= intval($skuArr['store'])){
                echo -2;
                exit;
            }  
        }  
        $cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid)->field('c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price')->select();
        if($cartInfo){
            foreach($cartInfo as $k=>$v){
                if($v['cart_id']==$gid){
                    $goodsTotalPrice = $cartInfo[$k]['price'] * $cartInfo[$k]['quantity']; //单个商品总价
                }    
                $cartInfo[$k]['goodsTotalPrice'] = floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']);
                $totalPrice += $cartInfo[$k]['goodsTotalPrice']; //总价格
            }
        }

         $cartArr = array(
                        'goodsTotalPrice'=>$goodsTotalPrice,
                        'totalPrice'=>$totalPrice
                        );
        echo json_encode($cartArr);

    }
     // 购物车中货品数量的增加/减少 end 20160718 

    // 添加收货地址 start
    public function addAddr(){
      // 判断是否登录，得到用户id

      //测试 user_id = 112
      //得到用户收货地址信息
       $whereAddr=array(
            'user_id'=>$this->uid
            );
        $addrInfo=$this->addrModel->where($whereAddr)->select();
        if($addrInfo){
            // var_dump($addrInfo);
            foreach($addrInfo as $k=>$v){
              $addrArr=explode(':',$v['area']);
              $addrInfo[$k]['area']=$addrArr[0];
            }
            $this->assign('addrInfo',$addrInfo); 
        }


      $this->display('addnewaddr');
    }
    // 添加收货地址 end

    // 删除收货地址 20160719 start
    public function deleteAddr(){
      $addrId=I('get.addr_id',0,'intval');
      $where=array(
          'addr_id'=>$addrId,
          'user_id'=>$this->uid 
        );
      $delResult=$this->addrModel->where($where)->delete();
      if($delResult){
         $url = __APP__.'/Order/addAddr';
         header("location:$url");
         exit;
      }
    }
    // 删除收货地址 20160719 end

    //添加新的收货地址  开始  20160719
    public function saveNewAddr(){
      $consignee=I('get.consignee','','trim');
      $province=I('get.province','','trim');
      $provinceId=I('get.province_id','','trim'); //一级
      $city=I('get.city','','trim');
      $cityId=I('get.city_id','','trim'); //二级
      $area=I('get.area','','trim');
      $areaId=I('get.area_id','','trim'); //三级
      $town=I('get.town','','trim');
      $townId=I('get.town_id','','trim'); //四级
      $address=I('get.address','','trim');
      $zipcode=I('get.zipcode','','trim');
      $mobile=I('get.mobile','','trim');
      $isDefault=I('get.isDefault',0,'intval');
      //判断四级地址是否正确
      if(empty($townId)){
          $fourLevel=$this->areaModel->field('jd_id')->where('jd_pid='.$areaId.' and level=4')->find();
          if($fourLevel['jd_id']){
            echo 'addFailed';
            exit;
          }
        }

        if($townId == '' || $townId=='0' ){
          $addrDetail=$province.'/'.$city.'/'.$area.':'.$provinceId.'/'.$cityId.'/'.$areaId;
        }else{
          $addrDetail=$province.'/'.$city.'/'.$area.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$areaId.'/'.$townId;
        }
        if($isDefault == 1){
          $data=array('def_addr'=>0);
          $this->addrModel->where('user_id='.$this->uid)->data($data)->save();  //user_id =112  测试
        }
          $info=array(
            'user_id'=>$this->uid,
            'name'=>$consignee,
            'area'=>$addrDetail,
            'addr'=>$address,
            'zip'=>$zipcode,
            'tel'=>'',
            'mobile'=>$mobile,
            'def_addr'=>$isDefault
            );
          $addrAdd=$this->addrModel->data($info)->add();
          if($addrAdd){
            echo "addSuccess";
          }
    }
    //添加新的收货地址 end  20160719

    // 编辑添加收货地址 start
    public function editAddr(){
      $addr_id=I('get.addr_id',0,'intval');
      $where=array(
        'addr_id'=>$addr_id,
        'user_id'=>$this->uid
        );
      $addrInfo=$this->addrModel->where($where)->find();
      if($addrInfo){
        $areaStr=explode(':',trim($addrInfo['area']));
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
       
        $this->assign('addrInfo',$addrInfo);
      }
      $this->display('editnewaddr');
    }
    // 编辑添加收货地址 end

    // 对用户收货地址的编辑操作 start
    public function editUserAddrInfo(){
        $addrId=I('post.address_id',0,'intval');
        $consignee=I('post.consignee','','trim');
        $province=I('post.province','','trim');
        $provinceId=I('post.province_id','','trim'); //一级
        $city=I('post.city','','trim');
        $cityId=I('post.city_id','','trim'); //二级
        $area=I('post.area','','trim');
        $areaId=I('post.area_id','','trim'); //三级
        $town=I('post.town','','trim');
        $townId=I('post.town_id','','trim'); //四级
        $address=I('post.address','','trim');
        $zipcode=I('post.zipcode','','trim');
        $mobile=I('post.mobile','','trim');
        $isDefault=I('post.isDefault',0,'intval');

        //判断四级地址是否正确
        if(empty($townId)){
          $fourLevel=$this->areaModel->field('jd_id')->where('jd_pid='.$areaId.' and level=4')->find();
          if($fourLevel['jd_id']){
            echo 'modFailed';
            exit;
          }
        }

        if($townId == '' || $townId=='0' ){
          $addrDetail=$province.'/'.$city.'/'.$area.':'.$provinceId.'/'.$cityId.'/'.$areaId;
        }else{
          $addrDetail=$province.'/'.$city.'/'.$area.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$areaId.'/'.$townId;
        }
        if($isDefault == 1){
          $data=array('def_addr'=>0);
          $this->addrModel->where('user_id='.$this->uid)->data($data)->save();  //user_id =112  测试
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
        $addrModifier=$this->addrModel->where($where)->data($info)->save();
        if($addrModifier){
          echo 'addrModSuccess';
          exit;
        }else{
          echo 'addrModFailed';
          exit;
        } 
    }
     // 对用户收货地址的编辑操作 end

    // 收货地址列表 start
    public function listAddr(){
        $currParam=$_SERVER["QUERY_STRING"];//得到当前参数
        if(isset($currParam) && !empty($currParam)){
          $currNewParam=explode('=',$currParam);
          $this->assign('currParam',$currNewParam[1]);
        }else{
          $this->assign('currParam','');
        }
        $where=array(
            'level'=>1,
            'jd_pid'=>0
            );
        $provinceArr = $this->areaModel->field('jd_id,name,level')->where($where)->select();
        if($provinceArr){
             $this->assign('provinceArr',$provinceArr);
        }
      $this->display('editaddr');
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

       }

    }
    // 收货地址列表 end

    // 判断用户是否选择默认的收货地址 start
    public function defaultAddrInfo(){
       //得到用户的user_id   112 测试
      $where=array(
        'user_id'=>$this->uid,
        'def_addr'=>1
        );
      $defArr=$this->addrModel->where($where)->select();
      if($defArr){
      echo '1';
      }else{
      echo '0';
      }
      
    }
    // 判断用户是否选择默认的收货地址 end

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
    
    //订单状态 开始 20160729
    public function orderStatus(){
    	redirect(__APP__."/Order/orderList");
    }
    public function orderList(){
		$statusNum=I('get.status',0,'intval');
		if($statusNum==0){  //全部
			$whereSys=array(
				'user_id'=>$this->uid
			);
		}elseif($statusNum==1){ //待付款 WAIT_BUYER_PAY
			$statusOrder="WAIT_BUYER_PAY";
		}elseif($statusNum==2){ //待发货 WAIT_SELLER_SEND_GOODS
			$statusOrder="WAIT_SELLER_SEND_GOODS";
		}elseif($statusNum==3){ //待收货 WAIT_BUYER_CONFIRM_GOODS
			$statusOrder="WAIT_BUYER_CONFIRM_GOODS";
		}elseif($statusNum==4){ //待评价 TRADE_FINISHED
			$statusOrder="TRADE_FINISHED";
		}
		$this->assign('statusNum',$statusNum);
		if($statusNum != 0){
			$whereSys=array(
				'user_id'=>$this->uid,
				'status'=>$statusOrder
			);
		}
		
		$sysTradeInfo=$this->tradeModel->where($whereSys)->field('tid,shop_id,status,order_status,buyer_area,post_fee,payment,refund_fee')->order('created_time desc')->select();

		if($sysTradeInfo){
			foreach($sysTradeInfo as $k3=>$v3){
				$shopId[]=$v3['shop_id'];
				$tid[]=$v3['tid'];
        $sysTradeInfo[$k3]['post_fee']=number_format($v3['post_fee'],2,'.','');	
        $sysTradeInfo[$k3]['payment']=number_format($v3['payment'],2,'.','');  	
			}
			$shopArr=$this->shopModel->where('shop_id IN ('.implode(',',$shopId).')')->field('shop_id,shop_name')->select();
			if(!empty($shopArr)){
				foreach($shopArr as $key=>$value){
					$shop[$value['shop_id']]=$value['shop_name'];
				}
			}
			$this->assign('shop',$shop);
			$orderInfo=$this->orderModel->where('tid IN('.implode(',',$tid).')')->field('tid,oid,item_id,title,price,num,total_weight,pic_path')->select();
			if(!empty($orderInfo)){
				foreach($orderInfo as $key=>$value){
          $value['price']=number_format($value['price'],2,'.','');
					$order[$value['tid']][]=$value;

				}
			}
			$this->assign('order',$order);			
			$this->assign('sysTradeInfo',$sysTradeInfo);
		}
		$this->display();
    }
    
    //订单状态 结束 20160729

    // 订单状态的修改 20160801 start
	public function orderChgStatus(){
		$orderId=I('get.orderId','','trim');
		$statusNum=I('get.statusNum',0,'intval');
		$where=array(
			'tid'=>$orderId,
			'user_id'=>$this->uid
		);
		if($statusNum==-1){
			//TRADE_CLOSED_BY_USER
			//SUCCESS
			//用户取消
			$data['status']='TRADE_CLOSED_BY_USER';
			$data['cancel_status']='SUCCESS';
			$data['cancel_reason']='用户取消';
			$orderStatus=$this->tradeModel->data($data)->where($where)->save();
			//WAIT_PROCESS  //退款的状态
			if($orderStatus){
				echo '-1-succ';
				exit;
			}else{
				echo '-1-fail';
				exit;
			}
		}elseif($statusNum==1){ //支付
			echo A('Pay')->creatPaymentsTwo($orderId);
			exit;
		}elseif($statusNum==3){ //待收货
			$data=array(
				'status'=>'TRADE_FINISHED'
			);
			$orderStatusInfo=$this->tradeModel->where($where)->data($data)->save();
			if($orderStatusInfo){
                echo '3-succ';
                exit;
			}else{
                echo '3-fail';
                exit;
			}
		}elseif($statusNum==5){ //申请退款
            $cancelReason=I('get.cancelReason','','trim');
            $refundRemark=I('get.refundRemark','','trim');
            if(empty($cancelReason)){
                echo 'reasonEmpty';
                exit;
            }

            $dataTrade['order_status']='REFUND';
            $refundInfo=$this->tradeModel->where($where)->data($dataTrade)->save();

            $dataOrder['aftersales_status']='WAIT_EARLY_PROCESS';
            $aftersaleNum=$this->orderModel->where($where)->field('oid,tid,shop_id,title,num')->select();
            if($aftersaleNum){
                foreach($aftersaleNum as $k=>$v){
                    $where['oid']=trim($v['oid']);
                    $dataOrder['aftersales_num']=trim($v['num']); 
                    $this->orderModel->where($where)->data($dataOrder)->save();
                    $afterSale[]=array(
                        'aftersales_bn'=>date('ymdHis').rand(100000,999999),
                        'user_id'=>$this->uid,
                        'shop_id'=>trim($v['shop_id']),
                        'aftersales_type'=>'ONLY_REFUND',
                        'tid'=>trim($v['tid']),
                        'oid'=>trim($v['oid']),
                        'title'=>trim($v['title']),
                        'num'=>trim($v['num']),
                        'reason'=>$cancelReason,
                        'description'=>$refundRemark,
                        'created_time'=>time(),
                        'modified_time'=>time()
                    );
                }
                $aftersaleNum=$this->afterSaleModel->addAll($afterSale);
            }
            $dataLog=array(
                'admin_username'=>'会员:'.$this->userName,
                'user_id'=>$this->uid,
                'created_time'=>time(),              
                'deal_type'=>'申请退款',
                'tid'=>$orderId,
                'memo'=>'申请理由：'.$cancelReason.'，描述：'.$refundRemark,
                'ip'=>$_SERVER["REMOTE_ADDR"]
            );
            $adminTradeLogAdd = $this->adminTradeLogModel->data($dataLog)->add();

            if($refundInfo && $aftersaleNum && $adminTradeLogAdd){
                echo 'applySucc';
                exit;
            }else{
                echo 'applyFail';
                exit;
            }
        }
	}
    public function orderRefund(){ //申请退款页面
      $tid=I('get.tid','','trim');
      $currStautsNum=I('get.currStautsNum','','trim');
      $this->assign('currStautsNum',$currStautsNum);
      $this->assign('tid',$tid);
      $this->display();
    }     


    //20160829 物流显示页面 开始
    public function flow(){
		$orderId=I('get.orderId',0,'trim');
		$where['tid']=$orderId;
		$where['user_id']=$this->uid;
		$logiNo=$this->deliveryModel->where($where)->field('tid,logi_no,shop_id,logi_name')->find();
		$this->assign('OrderId',$logiNo['tid']);
		if($logiNo['shop_id']==10){
			if($logiNo['logi_no']){
				$logiNoArr=explode(',',trim($logiNo['logi_no']));
				$this->assign('expressNumber',$logiNoArr);
			}
		}else{
			$this->assign('baseInfo',$logiNo);
		}
		$this->display();
    }
    
	public function ajaxFlow(){
		$orderId=I('get.orderId',0,'trim');
		$where['tid']=$orderId;
		$where['user_id']=$this->uid;
		$logiNo=$this->deliveryModel->where($where)->field('tid,logi_no,shop_id,logi_name')->find();
		if($logiNo['shop_id']==10){
			if($logiNo['logi_no']){
				$logiNoArr=explode(',',trim($logiNo['logi_no']));
				if(I('logiNo')){
					$JdflowId=I('logiNo');
				}else{
					$JdflowId=$logiNoArr[0];
				}
				$url="http://www.lishe.cn/business/api.php/Interface/getJdExpress/";
				$data=array(
					'orderId'=>$JdflowId
				);
				$res=$this->requestPost($url,$data);
				$res=json_decode($res,true);
				$this->assign('expressInfo',$res['data']['orderTrack']);
				//当前页订单

			}
		}else{
			$this->assign('baseInfo',$logiNo);
		}			
		$this->display();
	}
    //20160829 物流显示页面 结束
}