<?php
namespace Home\Controller;
class ActivityController extends CommonController {
	public function __construct() {
		parent::__construct();
		$this->modelComActCate=M('company_activity_category');
		$this->modelActivityConfig=M('company_activity_config');
		$this->modelActivityItem=M('company_activity_item');
		$this->modelActivityTime=M('company_activity_time');
		$this->modelConfig=M('company_gaway_config');
		$this->modelItem=M('sysitem_item');
		$this->cartModel=M('systrade_cart'); //购物车表
		$this->skuModel=M('sysitem_sku');//库存表
		$this->skuStoreModel=M('sysitem_sku_store');//货品的库存
		$this->shopModel=M('sysshop_shop');//店铺信息
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
		$this->areaModel=M('site_area');
		$this->atradeModel = M('company_activity_trade');//活动订单表
		$this->aorderModel = M('company_activity_order');//活动订单子表
      	$this->paymentsModel = M("ectools_payments");//支付表
      	$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
      	$this->userAccountModel = M('sysuser_account');//用户登录表
      	$this->userDepositModel = M('sysuser_user_deposit');//积分表
      	$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
      	$this->logisticsModel=M('syslogistics_dlytmpl');//快递信息表     
		$this->postageModel=M('syspromotion_freepostage');//包邮表
      	$this->itemStatusModel = M('sysitem_item_status');//商品状态表
      	$this->companyActCartModel=M('company_activity_cart');
		
		$this->assign('public',C('ROOT').'Show/Home/View/Activity/');
		$this->assign('index',urldecode($this->index));
	}
	//京东食用油 开始
	public function jdOilActivity(){//商品列表页
		$aid=I('get.aid','','trim');
		if(empty($aid)){
			$aid=8;
		}

		$activity=$this->activity($aid);
		// dump($activity['list']);
		// $this->assign('activityInfo',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->display('Activity/jdOilActivity/index');
	}
	/**
	* 判断库存
	*/

	public function jdAddOilCart(){//加入购物车操作
		 //商品id
        $itemId = I('post.itemId');
        $quantity = 1;
        //库存id
         // $skuId = I('post.skuId');
        $skuId =$this->skuModel->where('item_id='.$itemId)->getField('sku_id');
        if($itemId && $skuId){
        	$itemStatus = $this->itemStatusModel->where('item_id='.$itemId)->find();
        	if(!$itemStatus && $itemStatus['approve_status'] == 'instock'){//判断商品是否上下架
                echo 'instock';
                exit();
            }
             //查询购物车是否有该商品，如果有的话就直接增加数量
            $cartInfo = $this->companyActCartModel->where('user_id = '.$this->uid.' and sku_id = '.$skuId)->find();
             if($cartInfo){
             	 $res = $this->companyActCartModel->where('cart_id = '.$cartInfo['cart_id'])->setInc('quantity',$quantity);
             	 if ($res) {
                    echo '1';
                }else{
                    echo '0';
                }
             }else{
             	 //查询商品详细信息
                $itemInfo = $this->modelItem->where('item_id = '.$itemId)->find();
                //查询库存详细信息
                $skuInfo = $this->skuModel->where('sku_id ='.$skuId)->find();
				 //购物车数据
                $data['user_ident']= md5($this->uid);//会员ident,会员信息和session生成的唯一值
                $data['user_id'] = $this->uid;//用户id
                $data['shop_id'] = $itemInfo['shop_id'];//店铺ID
                $data['obj_type'] = 'item';//购物车对象类型
                $data['obj_ident'] = 'item_'.$skuInfo['sku_id'];//item_商品id
                $data['item_id'] = $itemId;//商品id
                $data['sku_id'] = $skuInfo['sku_id'];//sku的id
                $data['title'] = $itemInfo['title'];//商品标题
                $data['image_default_id'] = $itemInfo['image_default_id'];//商品默认图
                $data['quantity'] = $quantity;//数量
                $data['created_time'] = time();//加入购物车时间

                $result = $this->companyActCartModel->data($data)->add();
                if($result){
                    echo "success";
                }else{
                    echo 0;
                }

             }
        }else{
        	 //参数错误
             echo 0;
        }

		 
	}

	public function jdOilCart(){
		//购物车列表
		$shopIdInfo=$this->companyActCartModel->distinct(true)->field('shop_id')->where('user_id='.$this->uid)->select();
		if($shopIdInfo){
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
		$cartInfo=$this->companyActCartModel->table('sysitem_item a,company_activity_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid)->field('b.cart_id,b.shop_id,b.item_id,b.sku_id,b.title,b.image_default_id,b.quantity,a.price')->select();
		if($cartInfo){
            	foreach($cartInfo as $k1=>$v1){
            	$cartInfo[$k1]['price']=number_format($cartInfo[$k1]['price'],2,'.','');
                $cartInfo[$k1]['goodsTotalPrice'] = floatval($cartInfo[$k1]['price']) * intval($cartInfo[$k1]['quantity']);
                $totalPrice += $cartInfo[$k1]['goodsTotalPrice'];
            	}
            
            // var_dump($cartInfo);
            $this->assign('totalPrice',$totalPrice); //总价格
            $this->assign('cartInfo',$cartInfo);  //购物车信息
		}

		}else{
			$url = __APP__.'/Order/cartEmpty';
			header("location:$url");
			exit;
		}

		$this->display('Activity/jdOilActivity/cart');
	}
	public function getSelectPrice(){
		$cartIdStr=I('get.cartIdStr','','trim');
		if($cartIdStr){
			$cartIdStr=rtrim($cartIdStr,','); //得到购物车id
			$cartIdStr = "(".$cartIdStr.")";
			$cartInfo=$this->companyActCartModel->table('sysitem_item a,company_activity_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid.' and b.cart_id in'.$cartIdStr)->field('b.item_id,b.quantity,b.sku_id,a.price')->select();
			if($cartInfo){
					foreach($cartInfo as $k=>$v){	 
					    $cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
		            	$currSelectGoodsPrice += floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']);
					}
				
				$currArr=array(
					'curr_num'=> floatval($currSelectGoodsPrice) * 100,
					'curr_money'=> number_format($currSelectGoodsPrice,2,'.','')
					);
				echo json_encode($currArr);
				exit;
			}
		}else{
			echo '-1';
			exit;
		}
	}

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
            $cartNumDec=$this->companyActCartModel->where($where)->setDec('quantity',1);    
        }elseif($op=='inc'){  //表货品数目增加
            $cartNumDec=$this->companyActCartModel->where($where)->setInc('quantity',1);
            $skuArr = $this->skuStoreModel->field('store')->where('sku_id='.$sku_id)->find();
            if($num >= intval($skuArr['store'])){
                echo -2;
                exit;
            }  
        }elseif($op=='both'){
        	$dataInfo=array(
        		'quantity'=>$num
        		);
        	$cartNumChg=$this->companyActCartModel->where($where)->data($dataInfo)->save();
        }
        $cartInfo=$this->companyActCartModel->table('sysitem_item a,company_activity_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid)->field('b.item_id,b.cart_id,b.sku_id,b.quantity,a.price')->select();

        if($cartInfo){
	            foreach($cartInfo as $k=>$v){
	                if($v['cart_id']==$gid){
	                	$cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
	                    $goodsTotalPrice =  floatval($cartInfo[$k]['price']) * intval($v['quantity']); //单个商品总价
	                }
	                $v['price']=number_format($v['price'],2,'.','');
	                $cartInfo[$k]['goodsTotalPrice'] = floatval($v['price']) * intval($v['quantity']);
	                $totalPrice += $cartInfo[$k]['goodsTotalPrice']; //总价格
	            }
        	

            $cartArr = array(
                        'goodsTotalPrice'=>$goodsTotalPrice,
                        'totalPrice'=>$totalPrice
                        );
            echo json_encode($cartArr);
        }
    }

    public function chgCartNum(){
		$currCartId=I('get.currCartId',0,'intval');
		$currCartQuantity=I('get.currCartQuantity','','trim');
		$currCartQuantity=intval($currCartQuantity);
		if($currCartQuantity > 0){
			$where=array(
				'cart_id'=>$currCartId,
				'user_id'=>$this->uid
				);
			$data=array(
				'quantity'=>$currCartQuantity
				);
			$chgCurrNum=$this->companyActCartModel->where($where)->data($data)->save();
			if($chgCurrNum){
				echo $currCartQuantity;
				exit;
			}
		}else{
			echo '-1';
			exit;
		}
	}

	public function deleteCartId(){
		$cartId=I('get.cartId',0,'intval');
		if($cartId != 0){
			$where=array(
				'cart_id'=>$cartId,
				'user_id'=>$this->uid
				);
			$delRes=$this->companyActCartModel->where($where)->delete();
			if($delRes){
				echo '1'; //删除成功
				exit;
			}else{
				echo '-1'; //删除失败
				exit;
			}
		}
	}

	public function deleteMoreCartIds(){
		$selectCartMoreStr=I('get.selectCartMoreStr','','trim');
		$selectCartMoreStr=rtrim($selectCartMoreStr,',');
		if($selectCartMoreStr){
			$whereDelInfo['user_id']=$this->uid;
			$selectCartMoreStr=explode(',',$selectCartMoreStr);
			$whereDelInfo['cart_id']=array('in',$selectCartMoreStr);
			$delMoreRes=$this->companyActCartModel->where($whereDelInfo)->delete();
			if($delMoreRes){
				echo 'delMoreSucc';
				exit;
			}else{
				echo 'delMoreFail';
				exit;
			}
		}

	}

	public function jdOilOrder(){//确认订单页
		$selectCartStr=I('get.selectCartStr','','trim');
		$selectCartStr=rtrim($selectCartStr,',');
		if($selectCartStr){
			$whereCartInfo['user_id']=$this->uid;
			$selectCartArr=explode(',',$selectCartStr);
			$whereCartInfo['cart_id']=array('in',$selectCartArr);
			$shopIdInfo=$this->companyActCartModel->distinct(true)->field('shop_id')->where($whereCartInfo)->select();
			if($shopIdInfo){
				foreach($shopIdInfo as $k2=>$v2){
		        	$shopIdArr[$k2] = $v2['shop_id'];
		        }
		        $shopIdStr=implode(',',$shopIdArr);
		        if($shopIdStr){
		           $where['shop_id']=array('in',$shopIdStr);
		           $shopInfo=$this->shopModel->where($where)->field('shop_id,shop_name')->select();
		           if(!empty($shopInfo)){
		           		$whereCurrCartId=" and b.cart_id in(".$selectCartStr.")";
						$cartInfo=$this->companyActCartModel->table('sysitem_item a,company_activity_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid.$whereCurrCartId)->field('b.cart_id,b.shop_id,b.item_id,b.sku_id,b.title,b.image_default_id,b.quantity,a.price,a.weight')->select();
						 if($cartInfo){
							 	foreach($cartInfo as $k=>$v){
					                //$cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
					                // ++++++++++++++++++++++++++++++++++
					                $itemId[]=$v['item_id'];
					                $itemNum[$v['item_id']]=$v['quantity'];
					                $stock[$v['item_id']]=33;//默认有货
					                 // ++++++++++++++++++++++++++++++++++
					                $cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
					                $cartInfo[$k]['goodsTotalPrice'] = floatval($cartInfo[$k]['price']) * intval($v['quantity']);
					                $shopTotalPrice += $cartInfo[$k]['goodsTotalPrice'];
					                $totalCartQuantity += intval($v['quantity']);

					                foreach($shopInfo as $k1=>$v1){
					                    if($v1['shop_id']==$v['shop_id']){ //得每个店铺的总价，与商品总重量
					                      $shopInfo[$k1]['totalPrice'] += floatval($cartInfo[$k]['price']) * intval($v['quantity']); 
					                       $shopInfo[$k1]['totalWeight'] += floatval($v['weight']) * intval($v['quantity']); 
					                       $shopInfo[$k1]['totalNum'] += 1;
					                       $shopInfo[$k1]['totalMarginTop'] = 250 + 100 * (intval($shopInfo[$k1]['totalNum']) - 1);
					                       $shopInfo[$k1]['totalMarginBottom'] = intval($shopInfo[$k1]['totalMarginTop']) - 20;                 
					                    }
					                }
				           		}
				           		
				           	//4
				           		$this->assign('cartTotalPrice',$shopTotalPrice);
				           		$this->assign('totalCartQuantity',$totalCartQuantity);
				           		//判断商品是否下架
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
				           		
				            //得到所有收货地址信息 开始	
				           	$whereAddr=array(
				            'user_id'=>$this->uid,
				            );
				        	$addrInfo=$this->addrModel->where($whereAddr)->select();
				        	if($addrInfo){
				        		foreach($addrInfo as $k2=>$v2){
				        			$addrArr=explode(':',$v2['area']);
   									$addrInfo[$k2]['area']=rtrim($addrArr[0],'/');
   									$addrInfo[$k2]['areaID']=rtrim($addrArr[1],'/');
				        		}
				        		// dump($addrInfo);
				        		$this->assign('addrInfo',$addrInfo);
				        	}
							//得到默认收货地址信息 开始
							$whereAddrDefault=array(
				            'user_id'=>$this->uid,
				            'def_addr'=>1   //默认收货地址
				            );
							$addrDefaultInfo=$this->addrModel->where($whereAddrDefault)->field('area')->find();
							if($addrDefaultInfo){
								// dump($addrDefaultInfo);
								$addrArr=explode(':',$addrDefaultInfo['area']);
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
					            $checkItem=$this->modelItem->field('jd_sku,item_id')->where($condition)->select();
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


							
								//算出配送方式的money start
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
								 
								 //包邮 开始 20160812
								$postFee=$this->modelActivityConfig->where('activity_id=8')->getField('post_fee');
								$totalCartDelivery="0.00";
								// $postInfo=$this->postageModel->select();
								foreach($shopInfo as $k11=>$v11){
									$totalCartWeight += floatval($shopInfo[$k11]['totalWeight']);
									if(floatval($shopInfo[$k11]['totalPrice']) >= floatval($postFee) ){
										$shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'];
									    $shopInfo[$k11]['delivery']="0.00";
									}else{
										$shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'] + $shopInfo[$k11]['delivery'];
										$shopTotalPrice += $shopInfo[$k11]['delivery']; //若不包邮，总价加邮费
										$totalCartDelivery += floatval($shopInfo[$k11]['delivery']);
									}
								}
								//包邮 结束 20160813
								// var_dump($shopInfo);
								$this->assign('totalCartDelivery',$totalCartDelivery);
								$this->assign('totalCartWeight',$totalCartWeight);
								$this->assign('shopInfo',$shopInfo);
     							$this->assign('shopTotalPrice',$shopTotalPrice); //总价格
     							$this->assign('cartInfo',$cartInfo);  //购物车信息
						    }


						}
		           }            
		        }

			}else{ //异常购物单，返回上一级
				$url=$_SERVER['HTTP_REFERER'];
				echo "<script>window.location.href='".$url."'</script>";
				exit; 
			}
		}

		$this->display('Activity/jdOilActivity/order');
	}

	//京东食用油 结束
	public function activity($aid){
		if(empty($aid)){
			$aid=1;
		}
		$activityConfig=$this->modelComActCate->where('activity_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->order('order_sort DESC')->select();
		foreach($activityConfig as $key=>$value){
			$activity[$value['activity_config_id']]=array(
				'id'=>$value['activity_config_id'],
				'name'=>$value['cat_name'],
				'banner'=>$value['cat_banner'],					
				'content'=>$value['cat_content'],
				'item_ids'=>$value['item_ids'],
				'more_link'=>$value['more_link']
			);
			if(!empty($value['recommend'])){
				$condition='i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				if($aid=='4'){
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->select();
				}elseif($aid=='8'){
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->select();
				}else{
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->limit(10)->select();

				}
			}
		}		
		return array('activity'=>$activity,'list'=>$itemList);
	}
		
	//专题活动页面
	public function index(){
		$aid=intval($_GET['aid']);
		$activity=$this->activity($aid);
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->display();
	}
	// 20160907  大闸蟹活动 开始

	public function gdListActivity(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Gd10086/login");
		}

		$aid=I('get.aid',0,'trim');
		$this->assign('aid',$aid);

		$activityConInfo=$this->modelActivityConfig->where('activity_id='.$aid)->field('start_time,end_time')->find();
		if($activityConInfo){
			$actTimeInfo=$this->modelActivityTime->where('aid='.$aid)->field('start_time,end_time')->select();
			$time=time();
			$timestamp = strtotime(date('Y-m-d', $time));//当天0时0分0秒时间戳
			if($actTimeInfo){
				foreach($actTimeInfo as $k=>$v){
					$start_time=floatval($v['start_time']);
					$end_time=floatval($v['end_time']);
					$startTime=$timestamp + (floatval($v['start_time']) * 3600); //当天某段开始时间戳
					$endTime=$timestamp + (floatval($v['end_time']) * 3600);//当天某段结束时间戳
					if($time >= intval($activityConInfo['start_time']) && $time < intval($activityConInfo['end_time']) && $time >=$startTime && $time < $endTime){
						$status='1';
						break;	 
					}elseif($time >= intval($activityConInfo['start_time']) && $time < intval($activityConInfo['end_time']) && $time < $startTime){
						$status='0';
						break;
					}elseif($time < intval($activityConInfo['start_time']) || $time > intval($activityConInfo['end_time'])){
						$status='0';
						break;
					}else{
						$status='0';

						// break;
					}
				}
				 
					$this->assign('startNewTime',$start_time);
					$this->assign('endNewTime',$end_time);

					$this->assign('endTime',$endTime);
					$this->assign('startTime',$startTime);
					$this->assign('status',$status);
				 	
			}

		}
		$activityInfo=$this->modelActivityItem->where('aid='.$aid)->field('aitem_id,item_id,price,shop_price,store,limit_num')->select();
		if($activityInfo){
			// dump($activityInfo);
			$this->assign('activityInfo',$activityInfo);	
		}
		
		$this->display('Activity/gdActivity/gdListActivity');
	}
	 
	public function gdOrderActivity(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Gd10086/login");
		}
		$whereAddr=array(
			            'user_id'=>$this->uid,
			            );
		$addrInfo=$this->addrModel->where($whereAddr)->select();
		if($addrInfo){
			foreach($addrInfo as $k=>$v){
				$currArea=explode(':',trim($v['area']));
				$addrInfo[$k]['area']=$currArea[0];
			}
			$this->assign('addrNum',count($addrInfo));
			$this->assign('addrInfo',$addrInfo);
		}

		$aid=I('get.aid',0,'trim');
		$aitem_id=I('get.aitem_id',0,'trim');
		$where=array(
			'aitem_id'=>$aitem_id,
			'aid'=>$aid		
			);
		$activityConInfo=$this->modelActivityConfig->where('activity_id='.$aid)->field('start_time,end_time')->find();
		if($activityConInfo){
			$activityInfo=$this->modelActivityItem->where($where)->find();
			if($activityInfo){
				$actTimeInfo=$this->modelActivityTime->where('aid='.$aid)->field('start_time,end_time')->select();
				$time=time();
				$timestamp = strtotime(date('Y-m-d', $time));//当天0时0分0秒时间戳

				if($actTimeInfo){
					foreach($actTimeInfo as $k=>$v){
						$startTime=$timestamp + (floatval($v['start_time']) * 3600); //当天某段开始时间戳
						$endTime=$timestamp + (floatval($v['end_time']) * 3600);//当天某段结束时间戳
						if($time >= intval($activityConInfo['start_time']) && $time < intval($activityConInfo['end_time']) && $time >=$startTime && $time < $endTime){
							$activityInfo['curr_price']=$activityInfo['price'];
							break;	 
						}elseif($time >= intval($activityConInfo['start_time']) && $time < intval($activityConInfo['end_time']) && $time < $startTime){
							$activityInfo['curr_price']=$activityInfo['shop_price'];
							break;
						}elseif($time < intval($activityConInfo['start_time']) || $time > intval($activityConInfo['end_time'])){
							$activityInfo['curr_price']=$activityInfo['shop_price'];
							break;
						}else{
							$activityInfo['curr_price']=$activityInfo['shop_price'];
							// break;
						}
					}
				}

				$this->assign('activityInfo',$activityInfo);	
			}
		}

		 
		// dump($activityInfo);
		$this->display('Activity/gdActivity/gdOrderActivity');
	}

	public function gdStoreActivity(){
		$aItemId=I('get.aItemId',0,'trim');
		$store=$this->modelActivityItem->where('aitem_id='.$aItemId)->getField('store');
		if(intval($store) == 0){
			echo 'finish';
			exit;
		}else{
			echo 'onsale';
			exit;
		}
	}
	// 20160907  大闸蟹活动 结束

	//海核涛0元购列表 20160831 开始
	public function haiHeTaoActivity(){
		if(empty($this->uid)){
			header("Location:http://hht.lishe.cn/b.php/Haihetao/login");
		}
		$aid=intval($_GET['aid']);
		$activity=$this->activity($aid);
		$this->assign('list',$activity['list']);
		$this->display('nuclear');
	}
	//海核涛0元购列表 20160831 结束

	//海核涛0元购 提交订单 开始
	public function order(){
		header("Content-type:text/html;charset=utf-8");
		$userDeposit=$this->userDepositModel->where('user_id='.$this->uid)->field('deposit')->find();
		if($userDeposit){			
			$checkTrade=$this->atradeModel->field('trade_id,disabled')->where('aid=0 and pay_time>0 and user_id='.$this->uid)->order('trade_id DESC')->find();
			//如果没有参加过活动
			if(empty($checkTrade['trade_id'])){
				if(floatval($userDeposit['deposit']) < 4500){	
					echo "<script>alert('账户积分满4500元才可以抢购~');</script>";
					echo "<script>window.history.go(-1);</script>";
					exit;
				}
			}else{//如果参加过活动且没有取消订单
				if($checkTrade['disabled']==0){	
					echo "<script>alert('您已经参加过该活动了~');</script>";
					echo "<script>window.history.go(-1);</script>";
					exit;
				}
			}			
		}
		$whereAddr=array(
        	'user_id'=>$this->uid,
        );
		$addrInfo=$this->addrModel->where($whereAddr)->select();
		if($addrInfo){
			foreach($addrInfo as $k=>$v){
				$currArea=explode(':',trim($v['area']));
				$addrInfo[$k]['area']=$currArea[0];
			}
			$this->assign('addrInfo',$addrInfo);
		}

		$itemId=I('get.item_id',0,'trim');
		$itemInfo=$this->modelItem->where('item_id='.$itemId)->field('item_id,jd_sku,shop_id,title,price,weight,image_default_id')->find();
		if($itemInfo){
			$itemInfo['price']=number_format($itemInfo['price'],2,'.','');
			$itemInfo['post_fee']=$this->modelActivityConfig->where('activity_id=4')->getField('post_fee');
			$shopName=$this->shopModel->where('shop_id='.$itemInfo['shop_id'])->getField('shop_name');
			if($shopName){
				$itemInfo['shop_name']=$shopName;
			}
			// dump($itemInfo);
			$this->assign('itemInfo',$itemInfo);
		}
		$this->display();
	}
	//海核涛0元购 提交订单 结束

	//海核涛0元购 修改默认的收货地址 开始
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
    	$this->assign('httprefer',$_SERVER['HTTP_REFERER']);
    	$addrId=I('get.addrId','','trim');
    	if($addrId){
    		$where=array(
    			'addr_id'=>$addrId,
    			'user_id'=>$this->uid
    			);
    		$currAddressInfo=$this->addrModel->where($where)->find();
    		// var_dump($currAddressInfo);
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
    			$this->assign('currAddressInfo',$currAddressInfo);
    		}

    	}
    	$this->display('address');
    }
    //海核涛0元购 修改默认的收货地址 结束

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

         //判断四级地址是否正确
      	if(empty($townId)){
          $fourLevel=$this->areaModel->field('jd_id')->where('jd_pid='.$areaId.' and level=4')->find();
          if($fourLevel['jd_id']){
            echo 'modFailed';
            exit;
          }
        }

        if($townId != ''){
          $addrDetail=$province.'/'.$city.'/'.$area.'/'.$town.':'.$provinceId.'/'.$cityId.'/'.$areaId.'/'.$townId;
        }else{
          $addrDetail=$province.'/'.$city.'/'.$area.':'.$provinceId.'/'.$cityId.'/'.$areaId;
        }

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
        $addrModifier=$this->addrModel->where($where)->data($info)->save();
        if($addrModifier){
          echo 'addrModSuccess';
          exit;
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
  	
	public function fifth(){
		$res = C('ACTIVITY');
		foreach($res as $k=>$v){
			foreach($v['item'] as $key => $value){
				$itemId[$key] = $key;
			}
		}
		$status = M('sysitem_item_status');
		$condition['item_id'] = array('in', $itemId);
		$result = $status->where($condition)->field('item_id,approve_status')->select();
		foreach($result as $key => $value){
			if($value['approve_status'] != "onsale"){
				$resId[] = $value['item_id'];
			}
		}
		foreach($res as $key => $value){
			foreach($value['item'] as $keys => $values){
				if(in_array($keys, $resId)){
					unset($res[$key]['item'][$keys]);
				}
			}
		}

		foreach($res as $key => $value){
				if(count($value['item']) > 10){
					$res[$key]['item']=array_slice($value['item'],0,10,true);
					
				}
			
		}

		// 20160712  start
	   $configDis = $this->modelConfig->field('id,send_type,flag')->select();
	   $this-> assign('configDis',$configDis);
	   // 20160712 end
		$this->assign('item',$res);
		$this->display('Activity/fifth/index');
	}


 	public function moon(){
		$activity=$this->activity(3);	
		$first=array(
			'activity' => current($activity['activity']),
			'list'     => current($activity['list'])
		);
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->assign('first',$first);
		$this->display('Activity/moon/index');
	}
	//更多
	public function itemList(){
		$activityId=I('activityId');
		if($activityId){
			$activityConfig=$this->modelActivityConfig->where('activity_config_id='.$activityId)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->find();
			$activity=array(
				'id'=>$activityConfig['activity_config_id'],
				'name'=>$activityConfig['cat_name'],
				'banner'=>$activityConfig['cat_banner'],					
				'content'=>$activityConfig['cat_content'],
				'item_ids'=>$activityConfig['item_ids'],
				'more_link'=>$activityConfig['more_link']
			);
			if(!empty($activityConfig['item_ids'])){
				$size=50;
				$condition='i.item_id IN('.$activityConfig['item_ids'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				$number=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
				$page = new \Think\Page($number,$size);
				$rollPage = 5; 
				$page -> setConfig('first' ,'首页');
				$page -> setConfig('last' ,'尾页');
				$page -> setConfig('prev' ,'上一页');
				$page -> setConfig('next' ,'下一页');
				$start = $page -> firstRow;  
				$pagesize = $page -> listRows;
				
				$itemIdArr=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->order('i.flag ASC,i.cat_id DESC,i.profit_rate DESC')->select();
				foreach($itemIdArr as $key=>$value){
					$itemId[]=$value['item_id'];
				}
				$itemId=array_slice($itemId,$start,$pagesize);
				$condition='item_id IN('.implode(',',$itemId).')';
				$itemList=$this->modelItem->field('item_id,title,image_default_id,price,mkt_price,flag')->where($condition)->select();
				$style = "pageos";
				$onclass = "pageon";
				$pagestr = $page -> show($style,$onclass); 
				$this -> assign('pagestr',$pagestr);					
			}
			$first=array('activity'=>$activity,'list'=>$itemList);		
			$this->assign('first',$first);
			$this->display('Activity/moon/itemList');
		}
	}



	/**************************************************生成订单、开始支付**********************************************************/
	

    //提交订单日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    }


	//提交订单
    public function addUserOrder(){    	
        $item_id = intval(I('post.item_id'));//商品id 
        //检查用户是否已经购买该商品
        $count = $this->atradeModel->where('user_id ='.$this->uid." and aid=0 and disabled=0")->count();
        if ($count) {
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"每个用户ID仅限参与一次0元好礼活动！\");\r\n"; 
			echo "window.location.href='haiHeTaoActivity/aid/4'\r\n"; 
			echo "</script>";
			exit();
        }
        //检查是否已经选择收货地址
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"您的配货地址没有选中，请重新选择！\");\r\n"; 
			echo "window.location.href='haiHeTaoActivity/aid/4'\r\n"; 
			echo "</script>";
			exit(); 
		}
        
        $remark = I('post.remark');//买家留言
        $num = intval(I('post.num'));//商品数量

        if (!$item_id) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品Id失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品Id失败！"));
            exit();
       	} 

   	    $itemInfo = $this->modelItem->where('item_id='.$item_id)->find();
      	if (!$itemInfo) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品失败！"));
            exit();
      	} 
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$key.$this->uid;//订单编号
        $data['aid'] = 0;//活动id
        $data['activity_name'] = "海核淘0元购";//活动名称
        $data['title'] = $itemInfo['title'];//订单标题
        $data['item_id'] = $itemInfo['item_id'];//商品关联ID
        $data['com_id'] = $this->comId;//企业ID
        $data['user_id'] = $this->uid;//会员id
        $data['account'] = $this->userName;//用户账号
        $data['item_num'] = $num;//订单商品数量
        $data['send_num'] = 0;//发货数量
        $data['total_fee'] = 0;//订单总价
        $data['post_fee'] = 0;//邮费
        $data['payment'] = $data['total_fee'];//实际要支付的金额
        $data['receiver_name'] = $addressInfo['name'];//收货人姓名
        $data['receiver_state'] = $newTakeAddress[0];//收货人所在省份                        
        $data['receiver_city'] = $newTakeAddress[1];//收货人所在城市
        $data['receiver_district'] = $newTakeAddress[2];//收货人所在地区
        $data['receiver_address'] = $addressInfo['addr'];//收货人详细地址
        $data['receiver_zip'] = $addressInfo['zip'];//收货人邮编
        $data['receiver_mobile'] = $addressInfo['mobile'];//收货人手机号
        $data['receiver_phone'] = $addressInfo['tel'];//收货人电话
        $data['buyer_message'] = $remark;//买家留言
        if ($addressInfo['area']) {
            $areaIds = trim(strstr($addressInfo['area'],':'),":");
        }
       	$data['buyer_area'] = $areaIds;//买家地区ID
        $data['price'] = $itemInfo['price'];//商品价格
        $data['cost_price'] = $itemInfo['cost_price'];//商品成本价
        $data['item_img'] = $itemInfo['image_default_id'];//商品图片
        $data['creat_time'] = time();//创建时间

        $res = $this->atradeModel->data($data)->add();
        if (!$res) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '主表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

        //生成订单子表
        $da['atid'] = $data['atid'];
        $da['aitem_id'] = 0;//活动id
        $da['item_name'] = $itemInfo['title'];//订单标题
        $da['item_id'] = $itemInfo['item_id'];//商品关联ID
        $da['price'] = $itemInfo['price'];//商品价格
        $da['cost_price'] = $itemInfo['cost_price'];//商品成本价
        $da['post_fee'] = 0;//邮费
        $da['item_img'] = $itemInfo['image_default_id'];//商品图片
		$da['user_id']=$this->uid;
        $da['weight'] = $itemInfo['weight'];
        $orderRes = $this->aorderModel->data($da)->add();
        if (!$orderRes) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '子表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

		if (!$data['atid']) {
			  	$thisRes['log'] = array(
	                'rel_id' =>1,
	                'op_name' =>"系统",
	                'op_role' =>"system",
	                'behavior' =>"cancel",
	                'log_text' => '缺少tid无法生成支付数据！',
	                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付数据生成失败！"));
            exit();
		}
		//生成支付数据
        $paymentId = $this->creatPayments($data['atid']);;
        if (!$paymentId) {
		  	$thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '支付单生成失败！',
                'log_time' =>time()
        	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付单生成失败！"));
            exit();
        }

        //积分支付
        $payRes = $this->operPay($paymentId);

        if ($payRes) {
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"订单提交成功！\");\r\n"; 
			echo "window.location.href=\"/user.php/Order/activityOrderList\"\r\n";
			echo "</script>";
			exit();         	
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n";
			echo " alert(\"支付失败，积分不足！\");\r\n"; 
			echo "window.location.href=\"/user.php/Order/activityOrderList\"\r\n";
			echo "</script>";
			exit();
        }
        
    }


    //生成支付数据
    public function creatPayments($atid){
    	$atid = array($atid);
        $thisRes = array();
        if ($atid) {
            //获取订单表信息
            $where['atid']  = array('in',implode(',', $atid));
            $tradeList = $this->atradeModel->where($where)->select();

            $toallPrice = 0 ;
            if ($tradeList) {
                foreach ($tradeList as $key => $value) {
                    $toallPrice += $value['total_fee'];
                }
            }
            //插入支付表
            $data['payment_id'] = date(YmdHis).$this->uid.'1';//支付单号
            $data['money'] = floatval($toallPrice);//需要支付的金额
            $data['cur_money'] = 0;//支付货币金额
            $data['user_id'] = $this->uid;
            $data['user_name'] = $this->userName;
            $data['op_name'] = $this->userName; //操作员
            $data['bank'] = '预存款';//收款银行
            $data['pay_account'] ='用户';//支付账号
            $data['created_time'] = time();
            $result = $this->paymentsModel->data($data)->add();
            if ($result) {
                foreach ($atid as $key => $value) {
                    $da['payment_id'] = $data['payment_id'];//主支付单编号
                    $da['tid'] = $value;
                    if ($tradeList) {
                        $payPrice = 0 ;
                        foreach ($tradeList as $ke => $val) {
                            if ($val['tid'] == $value) {
                                $payPrice = $val['total_fee'];
                            }
                        }
                    }
                    $da['payment'] = $payPrice;
                    $da['user_id'] = $this->uid;
                    $da['created_time'] = time();  
					$da['modified_time'] = time();
                    $result = $this->tradePaybillModel->data($da)->add();                  
                    if ($result) {
                        //插入数据成功
                        $thisRes = $data['payment_id'];
                        
                    }else{
                        //插入数据失败
                        $thisRes = 0;
                    }
                }
            }else{
                //支付主表插入错误
                        $thisRes = 0;
            }            
        }else{
            //tid为空
                        $thisRes = 0;
        }
        return $thisRes;
    }


    //支付操作
    public function operPay($paymentid){
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //用户登录信息表
        $userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->find();
        
        //支付子表
        $paymentBillInfo = $this->tradePaybillModel->where('payment_id = '.$paymentid)->select();
        $tidarry = array();
        foreach ($paymentBillInfo as $key => $value) {
            $tidarry[$key] = $value['tid'];
        }

        //检查是否已经支付
        if ($paymentInfo['status'] =='succ') {
            //已经支付不可再次支付
            return json_encode(array(2,"该订单已经支付！"));
        }else{
        	if($userDepositInfo['comId']=='1467166836740'){
				$payRes = $this->syncEcardOrder($userAccountInfo['mobile'],$paymentInfo['money'],$paymentid,implode(',',$tidarry));
				$payType='e-card';
			}else{
				if($paymentInfo['money']>0){
					$payRes = $this->dedect($this->uid,$userAccountInfo['mobile'],$paymentid,$paymentInfo['money'],$paymentInfo['memo']);
				}else{
					$payRes['result']=100;
            		$payRes['errcode']=0;
            		$payRes['transno']=100;
				}
				$payType='deposit';            	
			}
            if($payRes['result']==100){
                if($payRes['errcode']>0){
                    //支付失败，日志表
                    $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>'支付失败：'.$payRes['msg'],'logtime'=>time());
                    $this->userDataDepositLogModel->data($logdata)->add();
                    return false;
                }else{
                    //支付成功，更新本地积分
                    $this->userDepositModel->where('user_id ='.$this->uid)->setDec('deposit',$paymentInfo['money']);
                    $this->userDepositModel->where('user_id ='.$this->uid)->setDec('balance',$paymentInfo['money']*100);
                    $this->userDepositModel->where('user_id ='.$this->uid)->setDec('commonAmount',$paymentInfo['money']*100);

                    //支付流水号
                    $where['atid'] = array('in',implode(',',$tidarry)); 
                    $trdeDate = array(
                    	'transno'=>$payRes['data']['info']['transno'],
                    	'status'=>"WAIT_SELLER_SEND_GOODS",
                    	'payed_fee'=>$paymentInfo['money'],
                    	'pay_type'=>$payType,
                    	'pay_time'=>time()
                    );
                    $this->atradeModel->where($where)->data($trdeDate)->save();

                    //更新支付主表
                    $zdata['cur_money'] = $paymentInfo['money'];
                    $zdata['pay_type'] = 'online';
                    $zdata['pay_app_id'] = $payType;
                    $zdata['payed_time'] = time();
                    $zdata['status'] = 'succ';
                    $zdata['trade_no'] = $payRes['data']['info']['transno'];
                    $zres = $this->paymentsModel->where('payment_id ='.$paymentid)->data($zdata)->save();

                    //更新支付副表
                    $fda['status'] = 'succ';
                    $fda['payed_time'] = time();                    
                    $fda['modified_time'] = time();
                    $fres = $this->tradePaybillModel->where('payment_id ='.$paymentid)->data($fda)->save();

			    	
                    //日志表
                    $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>$paymentInfo['memo'],'logtime'=>time());
                    $this->userDataDepositLogModel->data($logdata)->add();
                    return true;
                
                }

            }else{
                //接口通讯失败，日志表                        
                $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>$payRes['msg'],'logtime'=>time());
                $this->userDataDepositLogModel->data($logdata)->add();                        
                return false;                
            }
        }
    }
    
    //E卡通支付接口
    public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}
    public function syncEcardOrder($mobile,$totalFee,$paymentId,$atid){
    	//日志
		$log=array(
			'payment_id'=>$paymentId,
			'tid'=>$atid,
			'sync_order_id'=>'',
			'log_type'=>'syncGd10086Order',
			'code'=>100,
			'partener'=>'gd10086',
			'modified_time'=>time()
		);
		$postData=json_encode($_POST);
		//记录开始支付日志
		$log['code']=1;
		$log['detail']='开始支付,post:'.$postData;
		$this->makeSqlLog($log);
		
    	//检查是否有权限支付
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
		$sign=md5('appKey='.$appKey.'&mobileNo='.$mobile.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'mobileNo'=>$mobile,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$ret=json_decode($return,true);
    	
    	$log['code']=$ret['code'];
    	$log['detail']='用户信息,return:'.$return;
		$this->makeSqlLog($log);    	
    	
    	if($ret['code']==100){
	    	//推送订单
	    	$empCode=$ret['data']['empCode'];//员工编号
	    	$createTime=date('Y-m-d H:i:s');
	    	$url=C('API_AOSERVER').'card/insertOrderData';
	    	$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderMoney='.$totalFee.'&orderNum='.$paymentId.'&orderTime='.$createTime.'&orderTotalMoney='.$totalFee.'&orderType=1'.C('API_AOSERVER_KEY'));
			$orderPost=array(
	    		'appKey'=>$appKey,
	    		'empCode'=>$empCode,
	    		'orderMoney'=>$totalFee,
	    		'orderNum'=>$paymentId,
	    		'orderTime'=>$createTime,
	    		'orderTotalMoney'=>$totalFee,
	    		'orderType'=>1,
	    		'sign'=>$sign
	    	);
	    	$returns=$this->accreditPost($url,json_encode($orderPost),$user,$password);
	    	$rets=json_decode($returns,true);
	    	$log['code']=$rets['code'];
    		$log['detail']='支付信息,data:'.json_encode($orderPost).' return:'.$returns;
			$this->makeSqlLog($log);
			$return=array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>$rets['msg']
			);
			if($rets['code']!=100){
				$return['errcode']=100;
			}else{
				$return['data']['info']['transno']=$paymentId;
			}
	    	return $return;
	    }
    }

    /**
     * 会员扣费接口
     *
     * @params userId int 会员id
     * @params operator string 操作用户的账号/手机号码
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function dedect($userId, $operator, $orderNumber, $fee, $memo){
        $url = C('API').'mallPoints/payOrder';
        $payFee=$fee*100;
        $sign=md5('orderno='.$orderNumber.'&phoneNum='.$operator.'&pointsAmount='.$payFee.'&pointsType=1lishe_md5_key_56e057f20f883e');

        $data=array(
            'phoneNum'=>$operator,
            'orderno'=>$orderNumber,
            'pointsAmount'=>$payFee,
            'pointsType'=>1,
            'sign'=>$sign
        );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt ( $ch, CURLOPT_POST, 1 ); //启用POST提交
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        $file_contents = curl_exec ( $ch );
        curl_close ( $ch );
        return json_decode($file_contents,TRUE);
    }

    //订单列表
    public function orderList(){
    	$orderList = $this->atradeModel->where('user_id ='.$this->uid)->select();
    	$this->assign('orderList',$orderList);
    	$this->display();
    }

    //提交订单 20160908  
     public function addUserOrderInfo(){    	
        $aitem_id = intval(I('post.aitem_id'));//商品id
        $curr_price=intval(I('post.curr_price',0,'trim'));
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"您的配货地址没有选中，请重新选择！\");\r\n"; 
			// echo "window.location.href='/business/index.php/Haihetao/order'\r\n"; 
			echo "window.history.back();";
			echo "</script>";
			exit(); 
		}

        $remark = I('post.remark');//买家留言
        $num = intval(I('post.num'));//商品数量

        if (!$aitem_id) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品Id失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品Id失败！"));
            exit();
       	} 

   	    $aItemInfo = $this->modelActivityItem->where('aitem_id='.$aitem_id)->find();
   	    if(intval($aItemInfo['store']) == 0){
   	    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"该商品今天已抢完！\");\r\n"; 
			// echo "window.location.href='/business/index.php/Haihetao/order'\r\n"; 
			echo "window.history.back();";
			echo "</script>";
			exit(); 
   	    }
   	    // dump($aItemInfo);
   	    // exit;
      	if (!$aItemInfo) {
            $thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '获取活动商品失败',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"获取活动商品失败！"));
            exit();
      	} 
      	//生成主订单表
        $data['atid'] = substr(date(YmdHis),2).$key.$this->uid;//订单编号
        $data['aid'] = $aitem_id;//活动id
        $data['activity_name'] = $aItemInfo['activity_name'];//活动名称
        $data['title'] = $aItemInfo['item_name'];//订单标题
        $data['item_id'] = $aItemInfo['item_id'];//商品关联ID
        $data['com_id'] = $this->comId;//企业ID
        $data['user_id'] = $this->uid;//会员id
        $data['account'] = $this->userName;//用户账号
        $data['item_num'] = $num;//订单商品数量
        $data['send_num'] = 0;//发货数量
        $data['total_fee'] = $curr_price*$num+$aItemInfo['post_fee'];//订单总价
        $data['post_fee'] = $aItemInfo['post_fee'];//邮费
        $data['payment'] = $data['total_fee'];//实际要支付的金额
        $data['receiver_name'] = $addressInfo['name'];//收货人姓名
        $data['receiver_state'] = $newTakeAddress[0];//收货人所在省份                        
        $data['receiver_city'] = $newTakeAddress[1];//收货人所在城市
        $data['receiver_district'] = $newTakeAddress[2];//收货人所在地区
        $data['receiver_address'] = $addressInfo['addr'];//收货人详细地址
        $data['receiver_zip'] = $addressInfo['zip'];//收货人邮编
        $data['receiver_mobile'] = $addressInfo['mobile'];//收货人手机号
        $data['receiver_phone'] = $addressInfo['tel'];//收货人电话
        $data['buyer_message'] = $remark;//买家留言
        if ($addressInfo['area']) {
            $areaIds = trim(strstr($addressInfo['area'],':'),":");
        }
       	$data['buyer_area'] = $areaIds;//买家地区ID
        $data['price'] = $aItemInfo['price'];//商品价格
        $data['cost_price'] = $aItemInfo['cost_price'];//商品成本价
        $data['item_img'] = $aItemInfo['item_img'];//商品图片
        $data['creat_time'] = time();//创建时间

        $res = $this->atradeModel->data($data)->add();
        if (!$res) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '主表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

        //生成订单子表
        $da['atid'] = $data['atid'];
        $da['aitem_id'] = $aitem_id;//活动id
        $da['user_id'] = $this->uid;//会员id
        $da['item_name'] = $aItemInfo['item_name'];//订单标题
        $da['item_id'] = $aItemInfo['item_id'];//商品关联ID
        $da['price'] = $aItemInfo['price'];//商品价格
        $da['cost_price'] = $aItemInfo['cost_price'];//商品成本价
        $da['post_fee'] = $aItemInfo['post_fee'];
        $da['item_img'] = $aItemInfo['item_img'];//商品图片
        $da['weight'] = $aItemInfo['weight'];
        $orderRes = $this->aorderModel->data($da)->add();
        if (!$orderRes) {
            $thisRes['log'] = array(
                'rel_id' =>$data['atid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '子表生成订单失败！',
                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"生成订单失败！"));
            exit();
        }

		if (!$data['atid']) {
			  	$thisRes['log'] = array(
	                'rel_id' =>1,
	                'op_name' =>"系统",
	                'op_role' =>"system",
	                'behavior' =>"cancel",
	                'log_text' => '缺少tid无法生成支付数据！',
	                'log_time' =>time()
            	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付数据生成失败！"));
            exit();
		}
		//生成支付数据
        $paymentId = $this->creatPayments($data['atid']);
        if (!$paymentId) {
		  	$thisRes['log'] = array(
                'rel_id' =>1,
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '支付单生成失败！',
                'log_time' =>time()
        	);
            $this->orderLog($thisRes['log']);
            echo json_encode(array(0,"支付单生成失败！"));
            exit();
        }

        //积分支付
        $payRes = $this->operPay($paymentId);

        if ($payRes) {
        	//提交成功，库存减一
        	$this->modelActivityItem->where('aitem_id='.$aitem_id)->setDec('store',1);
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"订单提交成功！\");\r\n"; 
			echo "window.location.href=\"/member-index.html\"\r\n"; 
			echo "</script>";
			exit();         	
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n";
			echo " alert(\"支付失败，积分不足！\");\r\n"; 
			// echo "window.location.href=\"/business/index.php/Haihetao/moonActivity\"\r\n";
			echo "window.history.back()";   
			echo "</script>";
			exit();
        }
        
    }

    //20160908  开始


	
}