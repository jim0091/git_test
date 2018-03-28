<?php
namespace Home\Controller;
class OrderController extends  CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__APP__."/Login/index");
			exit;
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
		$this->dShow=D('Show');
	}
	//购物车页面 20160811 开始
	public function cart(){
		$shopIdInfo=$this->cartModel->distinct(true)->field('shop_id')->where('user_id='.$this->uid)->select();
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
		$cartInfo=$this->cartModel->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid)->field('b.cart_id,b.shop_id,b.item_id,b.sku_id,b.title,b.image_default_id,b.quantity,a.price')->select();
		if($cartInfo){
			foreach($cartInfo as $k=>$v){
				$itemInfoArr[$k]=trim($v['sku_id']);    
            }
            $itemInfoStr=array(
            	'sku_id'=>array('in',$itemInfoArr),
            	'com_id'=>$this->comId
            	);
            $companyItemInfo=$this->dShow->getCompanyItemPrice($itemInfoStr);
            
            	foreach($cartInfo as $k1=>$v1){
            		if($companyItemInfo){
	            		foreach($companyItemInfo as $k3=>$v3){
	            			if($v1['item_id']==$v3['item_id']){
	            				if(floatval($v3['condition']) == 0){
	            					$cartInfo[$k1]['price']=floatval($v3['price']);
	            				}else{
	            					$cartInfo[$k1]['price']=floatval($v1['price']);
	            				}
	            				break;
	            			}
	            		}
            		}

            	$cartInfo[$k1]['price']=number_format($cartInfo[$k1]['price'],2,'.','');
                $cartInfo[$k1]['goodsTotalPrice'] = floatval($cartInfo[$k1]['price']) * intval($cartInfo[$k1]['quantity']);
                $totalPrice += $cartInfo[$k1]['goodsTotalPrice'];
            	}
            

            $this->assign('totalPrice',$totalPrice); //总价格
            $this->assign('cartInfo',$cartInfo);  //购物车信息
		}

		}else{
			$url = __APP__.'/Order/cartEmpty';
			header("location:$url");
			exit;
		}

		$this->display('cart');
	}
	//购物车页面 20160811 结束

	//得到购物车中选中商品的总价 20160812 开始
	public function getSelectPrice(){
		// var_dump(I('get.'));
		$cartIdStr=I('get.cartIdStr','','trim');
		if($cartIdStr){
			$cartIdStr=rtrim($cartIdStr,','); //得到购物车id
			$cartIdStr = "(".$cartIdStr.")";
			// echo $cartIdStr;
			$cartInfo=$this->cartModel->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid.' and b.cart_id in'.$cartIdStr)->field('b.item_id,b.quantity,b.sku_id,a.price')->select();
			// var_dump($cartInfo);
			if($cartInfo){
				foreach($cartInfo as $k1=>$v1){
					$itemInfoArr[$k1]=trim($v1['sku_id']);    
	            }
	            $itemInfoStr=array(
	            	'sku_id'=>array('in',$itemInfoArr),
	            	'com_id'=>$this->comId
	            	);
	            $companyItemInfo=$this->dShow->getCompanyItemPrice($itemInfoStr);

	          
					foreach($cartInfo as $k=>$v){
						if($companyItemInfo){
							foreach($companyItemInfo as $k2=>$v2){

								if($v['item_id']==$v2['item_id']){
									if(floatval($v2['condition']) == 0){
	            						$cartInfo[$k]['price']=floatval($v2['price']);
		            				}else{	
		            					$cartInfo[$k]['price']=floatval($v['price']);
		            				}
								break;
								}
							}
					    }
		            	$currSelectGoodsPrice += floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']);
					}
				
				$currArr=array(
					'curr_num'=> intval((floatval(number_format($currSelectGoodsPrice,2,'.','')) * 100)),
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

	//得到购物车中选中商品的总价 20160812 结束

	//填写购物车商品数量 20160812 开始
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
			$chgCurrNum=$this->cartModel->where($where)->data($data)->save();
			if($chgCurrNum){
				echo $currCartQuantity;
				exit;
			}
		}else{
			echo '-1';
			exit;
		}
	}
	//填写购物车商品数量 20160812 结束

	 // 购物车中货品数量的增加/减少 start 20160812
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
        }elseif($op=='both'){
        	$dataInfo=array(
        		'quantity'=>$num
        		);
        	$cartNumChg=$this->cartModel->where($where)->data($dataInfo)->save();
        }
        $cartInfo=$this->cartModel->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid)->field('b.item_id,b.cart_id,b.sku_id,b.quantity,a.price')->select();

        if($cartInfo){
        	foreach($cartInfo as $k1=>$v1){
					$itemInfoArr[$k1]=trim($v1['sku_id']);    
	        }
            $itemInfoStr=array(
            	'sku_id'=>array('in',$itemInfoArr),
            	'com_id'=>$this->comId
            );
	        $companyItemInfo=$this->dShow->getCompanyItemPrice($itemInfoStr);
	        // dump($companyItemInfo);
	        
	            foreach($cartInfo as $k=>$v){
	                if($v['cart_id']==$gid){
	                	//1
	                	if($companyItemInfo){
		                	foreach($companyItemInfo as $k2=>$v2){
		                		if($v['item_id']==$v2['item_id']){
									if(floatval($v2['condition']) == 0){
		        						$cartInfo[$k]['price']=floatval($v2['price']);
		            				}else{	
		            					$cartInfo[$k]['price']=floatval($v['price']);
		            				}
									break;
								}

		                	}
	                	}
	                	//2

	                    $goodsTotalPrice =  floatval($cartInfo[$k]['price']) * intval($v['quantity']); //单个商品总价
	                }    
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
     // 购物车中货品数量的增加/减少 end 20160812

	//删除购物商品信息 20160812 开始
	public function deleteCartId(){
		$cartId=I('get.cartId',0,'intval');
		if($cartId != 0){
			$where=array(
				'cart_id'=>$cartId,
				'user_id'=>$this->uid
				);
			$delRes=$this->cartModel->where($where)->delete();
			if($delRes){
				echo '1'; //删除成功
				exit;
			}else{
				echo '-1'; //删除失败
				exit;
			}
		}
	}
	//删除购物商品信息 20160812 结束

	//批量删除购物车信息 开始
	public function deleteMoreCartIds(){
		$selectCartMoreStr=I('get.selectCartMoreStr','','trim');
		$selectCartMoreStr=rtrim($selectCartMoreStr,',');
		if($selectCartMoreStr){
			$whereDelInfo['user_id']=$this->uid;
			$selectCartMoreStr=explode(',',$selectCartMoreStr);
			$whereDelInfo['cart_id']=array('in',$selectCartMoreStr);
			$delMoreRes=$this->cartModel->where($whereDelInfo)->delete();
			if($delMoreRes){
				echo 'delMoreSucc';
				exit;
			}else{
				echo 'delMoreFail';
				exit;
			}
		}

	}

 	//批量删除购物车信息 结束

	//空购物车 20160811 开始
	public function cartEmpty(){
		$selectCartId=$this->cartModel->where('user_id='.$this->uid)->field('cart_id')->select();
		if($selectCartId){
			 $url = __APP__.'/Order/cart';
             header("location:$url");
             exit;
		}

		$this->display('cart');
	}
	//空购物车 20160811 开始

	//提交订单页面 20160811 开始
	public function order(){
		$selectCartStr=I('get.selectCartStr','','trim');
		$selectCartStr=rtrim($selectCartStr,',');
		if($selectCartStr){
			$whereCartInfo['user_id']=$this->uid;
			$selectCartArr=explode(',',$selectCartStr);
			$whereCartInfo['cart_id']=array('in',$selectCartArr);
			$shopIdInfo=$this->cartModel->distinct(true)->field('shop_id')->where($whereCartInfo)->select();
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
						$cartInfo=$this->cartModel->table('sysitem_item a,systrade_cart b')->where('a.item_id=b.item_id and a.shop_id=b.shop_id and b.user_id='.$this->uid.$whereCurrCartId)->field('b.cart_id,b.shop_id,b.item_id,b.sku_id,b.title,b.image_default_id,b.quantity,a.price,a.weight')->select();
						 if($cartInfo){
						 	foreach($cartInfo as $k20=>$v20){
								$itemInfoArr[$k20]=trim($v20['sku_id']);    
				            }
				            $itemInfoStr=array(
				            	'sku_id'=>array('in',$itemInfoArr),
				            	'com_id'=>$this->comId
				            	);
				            $companyItemInfo=$this->dShow->getCompanyItemPrice($itemInfoStr);
				            //3
				           
							 	foreach($cartInfo as $k=>$v){
							 		 if($companyItemInfo){
								 		foreach($companyItemInfo as $k33=>$v33){
					            			if($v['item_id']==$v33['item_id']){
					            				if(floatval($v33['condition']) == 0){
					            					$cartInfo[$k]['price']=floatval($v33['price']);
					            				}else{
					            					$cartInfo[$k]['price']=floatval($v['price']);
					            				}
					            				break;
					            			}
					            		}
				            		}
							 	 
					                $cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
					                // ++++++++++++++++++++++++++++++++++
					                $itemId[]=$v['item_id'];
					                $itemNum[$v['item_id']]=$v['quantity'];
					                $stock[$v['item_id']]=33;//默认有货
					                 // ++++++++++++++++++++++++++++++++++
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
								$totalCartDelivery="0.00";
								$postInfo=$this->postageModel->select();
								foreach($shopInfo as $k11=>$v11){
									$totalCartWeight += floatval($shopInfo[$k11]['totalWeight']);
									foreach($postInfo as $k12=>$v12){
										if($v11['shop_id']==$v12['shop_id']){

											$shopInfo[$k11]['postName']=intval($v12['limit_money']);
											// echo $shopInfo[$k11]['postName'].'<br/>';
											//判断是否增加运费
											if($shopInfo[$k11]['postName'] > $shopInfo[$k11]['totalPrice']){
												$shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'] + $shopInfo[$k11]['delivery'];
												$shopTotalPrice += $shopInfo[$k11]['delivery']; //若不包邮，总价加邮费
												$totalCartDelivery += floatval($shopInfo[$k11]['delivery']);
											}else{
												$shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'];
												$shopInfo[$k11]['delivery']="0.00";
											}	
											break;		
										}
										
									}
								}
								//包邮 结束 20160813
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

		$this->display('order');
	}
	//提交订单页面 20160811 结束

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
        $addrModifier=$this->addrModel->where($where)->data($info)->save();
        if($addrModifier){
          echo 'addrModSuccess';
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