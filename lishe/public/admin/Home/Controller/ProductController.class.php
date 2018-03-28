<?php  
namespace Home\Controller;
use Org\Util\Excel;
class ProductController extends CommonController {
/*
 * 产品管理
 * 2016/8/23
 * zhangrui
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dGoods=D('Goods');
		$this->dCategory=D('Category');
		$this->dShop=D('Shopinfo');	
		$this->dOrder=D('Order');	
	}
	public function searchDatas($id){
		$data=array(
			"onsale"=>array(
				"status" => "onsale",
				"name"   => "上架中"
			),
			"instock"=>array(
				"status" => "instock",
				"name"   => "已下架"			
			)
		);
		$info=$data[$id];
		return $info;
	}	
	//搜索条件
	public function condition(){
		if(I()){
			if($_GET['keywords']){
				$_GET['keywords']=urldecode($_GET['keywords']);
			}
			$conditions =I();
			$searchData=$conditions;
			//毛利率
			if($conditions['srate'] || $conditions['erate']){
				$condition['profit_rate']=array('between',''.$conditions['srate'].','.$conditions['erate'].'');
				
			}
			//进货价
			if($conditions['jsrate'] || $conditions['jerate']){
				$condition['cost_price']=array('between',''.$conditions['jsrate'].','.$conditions['jerate'].'');
			}
			//销售价
			if($conditions['csrate'] || $conditions['cerate']){
				$condition['price']=array('between',''.$conditions['csrate'].','.$conditions['cerate'].'');
			}
			//关键词
			if($conditions['keywords']){
				$condition['title']=array('like','%'.$conditions['keywords'].'%');
			}
			//状态
			if($conditions['status']){
				//状态显示start
					$searchData['status']=$this->searchDatas($conditions['status']);
				//end
				if($conditions['status']=="onsale"){
					//上架
					$status="onsale";
				}else if($conditions['status']=="instock"){
					//下架
					$status="instock";
				}
					$res=$this->dGoods->getStatusIds($status);
					$condition['item_id']=array('in',$res);
			}
			//所属店铺
			if($conditions['shopId']){
				//start公司名字显示用
					$searchData['shop']=A('Order')->serachName($conditions['shopId'],"shop");
				//end
				$condition['shop_id']=$conditions['shopId'];
			}
			//商品ID
			if($conditions['itemId']){
				$condition['item_id']=trim($conditions['itemId']);
			}
			//所属供应商
			if($conditions['supplierId']){
				$condition['supplier_id']=$conditions['supplierId'];
			}
			//商品分类条件
			$catPath=$this->dCategory->getcatPath();
			foreach($catPath as $key => $value){
				$catPath[$key]['cat_path'] = explode(",", $value['cat_path']);
			}
			if($conditions['catone']){
				//start一级分类
					$searchData['catone']=A('Order')->serachName($conditions['catone'],"category",1);
				//end					
				if($conditions['cattwo']){
				//start2级分类
					$searchData['cattwo']=A('Order')->serachName($conditions['cattwo'],"category",2);
				//end						
					if($conditions['catthree']){
						//分类项到3级
						//start3级分类
							$searchData['catthree']=A('Order')->serachName($conditions['catthree'],"category",3);
						//end							
							$condition['cat_id']=$conditions['catthree'];
					}else{
						//分类项到两级
						foreach($catPath as $key => $value){
								if(in_array($conditions['cattwo'], $value['cat_path'])){
										$searchCatIDs[]=$value['cat_id'];
								}
						}
					}				
				}else{
						//分类在一级
						foreach($catPath as $key => $value){
								if(in_array($conditions['catone'], $value['cat_path'])){
										$searchCatIDs[]=$value['cat_id'];
								}
						}		
										
				}		
				if(!empty($searchCatIDs)){
					
					$condition['cat_id']=array('in',$searchCatIDs);
				}
					
			}
			//用于搜索后显示搜索条件begin
			$this->assign('condition',$searchData);		
			//用于搜索后显示搜索条件end		
		}	
		return 	$condition;
	} 
	public function productList(){
		$page=empty($_GET['p'])?1:intval($_GET['p']);
		$pageSize=20;
		$goods=array();
		$condition=$this->condition();
		if(I('order')==1){
			$orderby="profit_rate desc";
		}		
		$condition['disabled']=0;
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			$execlData=$this->dGoods->getItemListExcel($condition,$orderby);
			$execlData=$this->cartDeal($execlData);
			$execlData=$execlData['list'];
			$this->proExportExcel($execlData);
				
		}		
		$goods=$this->dGoods->getItemList($page,$pageSize,$condition,$orderby);
		$goods=$this->cartDeal($goods);
		$pageObj=new \Think\Page($goods['count'],$pageSize);
		$pageObj -> setConfig('first' ,'首页');
		$pageObj -> setConfig('last' ,'尾页');
		$pageObj -> setConfig('prev' ,'上一页');
		$pageObj -> setConfig('next' ,'下一页');		
		$show=$pageObj->show();// 分页显示输出
		$style = "pageos";
		$onclass = "pageon";
		$show = $pageObj -> show($style,$onclass); 		
		$parentCategory=$this->dCategory->getSubCategory();
		//所有供应商
		$supplierAll=$this->dGoods->getAllSupplier('supplier_id,company_name');
		$this->assign('supplierAll',$supplierAll);
		$this->assign('goods',$goods);
		$this->assign('parentCategory',$parentCategory);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	public function cartDeal($goods){
		if(!empty($goods['list'])){
			foreach($goods['list'] as $key => $value){
				$value['cat_id']=str_replace(",", "", $value['cat_id']);
				$catIds[]=$value['cat_id'];
				$shopIds[]=$value['shop_id'];
				$itemIds[]=$value['item_id'];
				$supplierIds[]=$value['supplier_id'];
			}
			//所属供应商
			if(!empty($supplierIds)){
				$supplierInfo=$this->dGoods->getConditionsupplierUser($supplierIds);
				foreach($goods['list'] as $key => $value){
					foreach($supplierInfo as $keys=>$values){
						if($value['supplier_id'] == $values['supplier_id']){
							$goods['list'][$key]['supperName']=$values['company_name'];
						}
					}
				}	
			}
			if(!empty($itemIds)){
				//商品状态
				$status=$this->dGoods->getStatus(array('in'=>array('in',$itemIds)));
			//商品sku信息	
				$itemSkuInfo=$this->dGoods->getAllItemSkuInfo($itemIds);
				foreach($itemSkuInfo as $key=>$value){
					$itemSkuIds[]=$value['sku_id'];
				}
			//库存
				if(!empty($itemSkuIds)){
					$store=$this->dGoods->getStores(array('sku_id'=>array('in',$itemSkuIds)));				
				}	
				foreach($itemSkuInfo as $key=>$value){
					foreach($store as $keys=>$values){
						if($value['sku_id']==$values['sku_id']){
							$itemSkuInfo[$key]['store']=$values['store'];
							$itemSkuInfo[$key]['freez']=$values['freez'];
							$itemSkuInfo[$key]['inferior']=$values['inferior'];
						}
					}					
				}	
				foreach($goods['list'] as $key => $value){
					foreach($status as $keyu=>$valueu){
						if($value['item_id'] == $valueu['item_id']){
						
							$goods['list'][$key]['status']=$valueu['approve_status'];
						}
					}	
					foreach($itemSkuInfo as $keyk=>$valuek){
						if($value['item_id']==$valuek['item_id']){
							$goods['list'][$key]['skuInfo'][]=$valuek;
						}
					}
				
				}								
			}
			//所属分类 begin
			if(!empty($catIds)){
				unset($condition);
				$catIds=implode(",", $catIds);
				$condition['cat_id'] = array('in',$catIds);
				$field="cat_name,cat_id";
				$catname=$this->dCategory->getCateinfo($condition,$field);
				foreach($goods['list'] as $key => $value){
					foreach($catname as $keys=>$values){
						if($value['cat_id'] == $values['cat_id']){
							$goods['list'][$key]['cat_name']=$values['cat_name'];
						}
					}
				}
			//分类为空给于分类ID展示
				foreach($goods['list'] as $key => $value){
						if(empty($value['cat_name'])){
							$goods['list'][$key]['cat_name']="暂无分类(ID：".$value['cat_id'].")";
						}
				}
			
			
			}
		//所属分类 end
			if(!empty($shopIds)){
				unset($condition);
				unset($field);
					$shopIds=implode(",", $shopIds);
					$condition['cat_id'] = array('in',$shopIds);
					$field="shop_id,shop_name";
					$shopname=$this->dShop->getShopInfo($condition,$field);
					foreach($goods['list'] as $key => $value){
						foreach($shopname as $keys=>$values){					
							if($value['shop_id'] == $values['shop_id']){
								$goods['list'][$key]['shop_name']=$values['shop_name'];
							}
						}
					}		
				//店铺为空给于店铺ID展示
					foreach($goods['list'] as $key => $value){
							if(empty($value['shop_name'])){
								$goods['list'][$key]['shop_name']="暂无(店铺ID：".$value['shop_id'].")";
							}
					}							
			}	

		}
		return $goods;
	}	
//导出execl
	public function proExportExcel($execlData){
		header("Content-type:text/html;charset=utf-8");
		if(empty($execlData)){
			exit;
		}
		foreach($execlData as $key=>$value){
			$catIds[]=$value['cat_id'];
		}
		$catInfo=$this->dOrder->getInCategoryInfo($catIds,'cat_id,cat_path');
		foreach($execlData as $key=>$value){
			foreach($catInfo as $keys=>$values){
				if($value['cat_id']==$values['cat_id']){
					$arr=explode(',', $values['cat_path']);
					$execlData[$key]['cat_one_id']=$arr[1];
					$execlData[$key]['cat_two_id']=$arr[2];
					unset($arr);
				}
			}		
		}
		unset($catInfo);
		foreach($execlData as $key=>$value){
			$catIdones[]=$value['cat_one_id'];
			$catIdtwos[]=$value['cat_two_id'];
		}		
		$allCatIds=array_unique(array_merge($catIds,$catIdones,$catIdtwos));
		if($allCatIds){
			$allCatInfo=$this->dOrder->getInCategoryInfo($allCatIds);
		}
		foreach($execlData as $key=>$value){
			foreach($allCatInfo as $keys=>$values){
				if($value['cat_one_id']==$values['cat_id']){
					$execlData[$key]['cat_one_name']=$values['cat_name'];
				}
				if($value['cat_two_id']==$values['cat_id']){
					$execlData[$key]['cat_two_name']=$values['cat_name'];
				}				
			}		
		}	
		unset($allCatInfo);			
		foreach($execlData as $key => $value){
			foreach($value['skuInfo'] as $keys => $values){
				if($keys==0){
					$lastData[$values['sku_id']]['item_id']=$value['item_id'];
					$lastData[$values['sku_id']]['jd_sku']=$value['jd_sku'];
					$lastData[$values['sku_id']]['cat_one_name']=$value['cat_one_name'];
					$lastData[$values['sku_id']]['cat_two_name']=$value['cat_two_name'];
					$lastData[$values['sku_id']]['cat_name']=$value['cat_name'];
					$lastData[$values['sku_id']]['shop_name']=$value['shop_name'];
					$lastData[$values['sku_id']]['supperName']=$value['supperName'];
					$lastData[$values['sku_id']]['title']=$value['title'];
					$lastData[$values['sku_id']]['no']=" ".$value['bn'];
					if($value['status']=="onsale"){
						$lastData[$values['sku_id']]['status']="上架中";
					}else{
						$lastData[$values['sku_id']]['status']="已下架";
					}
				}else{
					$lastData[$values['sku_id']]['item_id']="";
					$lastData[$values['sku_id']]['jd_sku']="";	
					$lastData[$values['sku_id']]['cat_one_name']="";
					$lastData[$values['sku_id']]['cat_two_name']="";
					$lastData[$values['sku_id']]['cat_name']="";
					$lastData[$values['sku_id']]['shop_name']="";	
					$lastData[$values['sku_id']]['supperName']="";
					$lastData[$values['sku_id']]['title']="";	
					$lastData[$values['sku_id']]['no']="";
					$lastData[$values['sku_id']]['status']="";		
							
				}
				$lastData[$values['sku_id']]['sku_id']=$values['sku_id'];   //skuId
				$lastData[$values['sku_id']]['spec_info']=$values['spec_info'];   //规格
				$lastData[$values['sku_id']]['bn']=" ".$values['bn'];   //商品编码
				$lastData[$values['sku_id']]['barcode']=" ".$values['barcode'];   //商品条形码
				$lastData[$values['sku_id']]['price']=$values['price'];   //价格
				$lastData[$values['sku_id']]['cost_price']=$values['cost_price'];   //进货价
				$lastData[$values['sku_id']]['profit_rate']=sprintf("%.2f", (($values['price']-$values['cost_price'])/$values['price'])*100);   //毛利率
				$lastData[$values['sku_id']]['store']=$values['store'];   //库存
				$lastData[$values['sku_id']]['inferior']=$values['inferior'];   //库存
				$lastData[$values['sku_id']]['sold_quantity']=$values['sold_quantity'];   //销量
			}
		}
		unset($execlData);
		$ex=new Excel;
		$columnName=array('商品ID','京东sku','所属分类1','所属分类2','所属分类3','所属店铺','所属供应商','商品名称','货号','状态','skuID','规格','商品编码','商品条形码','销售价(元)','进货价(元)','毛利率(%)','库存(件)','残次品(件)','销量');
		$ex->getExcel($lastData,$columnName,"商品列表(".date('Y年m月d日H时i分',time()).")");		
	}

//获取一级分类
	public function getCatOne(){
		$res=$this->dCategory->getCatsInfo();
		echo json_encode($res);
	}
//获取相应二级分类
	public function getCatTwo(){
		$parentId = I('parent_id');
		$level=I('level');
		$res=$this->dCategory->getCatTnfos($parentId,$level);
		echo json_encode($res);
		
	}		
//获取所有店铺名
	public function getShop(){
		$res=$this->dShop->getAllShopName();
		echo json_encode($res);
	}	
/*
 * 分类管理
 * */	
	public function category(){
		$res=$this->dCategory->getAllCategory();		
		
		$this->assign('list',$res);
		$this->display();
	}
/*
 * 修改/添加分类
 * */
	public function categoryEdit(){
		$catId=I('catId');
		if($catId){
			$catInfo=$this->dCategory->getThisCategory($catId);
			$this->assign('info',$catInfo);
		}
		$parentId=I('parentId');
		$level=I('level');
		$this->assign('level',$level);
		$this->assign('parentId',$parentId);
		$this->display();
	} 
/*
 * 修改/添加分类处理
 * */
	public function categoryEditDeal(){
		$catId=I('catId');
		$data=I('data');
		$data['modified_time']=time();
		if(!empty($_FILES['img']['tmp_name'])){
		    $upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   =     3145728 ;// 设置附件上传大小
		    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath  =      './Upload/catLogo/'; // 设置附件上传根目录
		    // 上传单个文件 
		    $info   =   $upload->uploadOne($_FILES['img']);
		    if(!$info) {// 上传错误提示错误信息
		        $this->error($upload->getError());
		    }else{// 上传成功 获取上传文件信息
		         $data['cat_logo']=C('TMPL_PARSE_STRING.__LISHE__').'/Upload/catLogo/'.$info['savepath'].$info['savename'];
		    }			
		}		
		if($catId){
			//修改
			$res=$this->dCategory->editThisCategory($catId,$data);
			if($res){
				$this->redirect('Product/categoryEdit', array('catId' => $catId), 1, '保存成功,页面跳转中...');
			}else{
				$this->redirect('Product/categoryEdit', array('catId' => $catId), 1, '请修改内容,再保存页面跳转中...');
			}
			
		}else{
			//添加
			 $res=$this->dCategory->addCategory($data);
			if($res){
				$this->redirect('Product/categoryEdit', array('catId' => $res), 1, '添加成功,页面跳转中...');
			}else{
				$this->redirect('Product/categoryEdit', array('catId' => $res), 1, '请修改内容,再保存页面跳转中...');
			}			 
		}
	} 	
/*
 * 启用/禁用该分类
 * **/
	public function dealCategory(){
		$catId=I('catId');
		$type=I('type');
		if($catId){
			if($type == 0){
				$disabled=1;
			}else if($type == 1){
				$disabled=0;
			}
			$res=$this->dCategory->dealThisCategory($catId,$disabled);
			$this->ajaxReturn($res);
		}
		
	}
/*
 * 修改产品所属分类
 * */
	public function modifyCategory(){
		$itemId=I('itemId');
		if($itemId){
			$list=$this->dShop->getShopList();
			if (empty($list)) {
				$this->error("获取店铺列表失败！");
			}
			foreach ($list as $key => $value) {
				if ($value['shop_id'] == 10) {
					unset($list[$key]);
				}
			}
			$sitemId=I("get.sitem_id");
			$sendType = I("get.send_type");
			$this->assign("sitem_id",$sitemId);
			$this->assign("item_id",$itemId);
			$this->assign("sendType",$sendType);
			$this->assign("list",$list);
			$this->assign('itemId',$itemId);
			$this->display();
		}
	}
/*
 *修改产品所属分类处理 
 * */
	public function modifyCategoryDeal(){
		$itemId=I('itemId');
		$catId=I('catthree');
		if($catId){
			$res=$this->dGoods->editItemCategory($itemId,$catId);
			if($res){
				echo "<div style='padding-left:42%;padding-top:13%;color:#5a98de;font-weight: bolder;font-size: x-large;'>分类修改成功！</div>";
			}else{
				$this->error('修改失败！请重试~~~~~');
			}
		}else{
			$this->error('未选择三级分类');
		}
	}
	//修改商品所属店铺分类
	public function modifyShopCate(){
		$itemId = I('itemId');		
		$shopId=(int)I("shop_id");
		$shopCat = I('shop_cat',0);//店铺分类
		if(empty($shopId)){
			$this->error("商铺ID为空");
		}
		if (empty($itemId)) {
			$this->error('系统繁忙，请稍后重试！');
		}
		if (empty($shopCat)) {
			$shopCat = I('shopCat');
			if (empty($shopCat)) {
				$this->error("请选择店铺分类");
			}			
		}
		unset($condition);
		$condition = array('item_id'=>$itemId);
		$itemInfo['shop_cat_id']=$shopCat;
		$itemInfo['shop_id']=$shopId;
		$itemRes = $this->dGoods->updateItem($condition,$itemInfo);
		if (is_numeric($itemRes)) {
			M('sysitem_item_status')->where($condition)->setField('shop_id', $shopId);
			echo "<div style='padding-left:42%;padding-top:13%;color:#5a98de;font-weight: bolder;font-size: x-large;'>分类修改成功！</div>";
		}else{
			$this->error('修改失败！');
		}
	}
/*
 * 产品选择供应商  
 * */
	public function modifySupplierUser(){
		$itemId=I('itemId');
		if($itemId){
			$info=$this->dGoods->getThisItemInfo($itemId,'supplier_id,send_type');
			$supplierInfo=$this->dGoods->getThissupplierUser($info['supplier_id']);
			$info=array_merge($info,$supplierInfo);
			$this->assign('info',$info);
			$this->assign('itemId',$itemId);
			$this->display();
		}
	}	
/*
 * 产品选择供应商  处理
 * */
	public function modifySupplierUserDeal(){
		$itemId = I('itemId');
		$supplierId = I('supplierId');
		$sendType = I('sendType');
		M()->startTrans();
		if(empty($supplierId) || empty($sendType)){
			$this->error('请选择完整再保存');
		}			
		//选择顺丰校验库存
		if($sendType == 3){
            $map['item_id'] = $itemId;
            $skuIds = M('sysitem_sku')->where($map)->getField('sku_id', TRUE);
            if (!$skuIds) {
                $this->error('该商品没有对应sku信息!');
            }
            $url = C('API_AOSERVER').'sf/item/inventoryQuery';
            $paramArr = array(
                'skuNos' => implode(',', $skuId)
            );
            $param = json_encode($paramArr);
            $result = $this->requestJdPost($url, $param);
            $ret = json_decode($result, true);
			$modelSkuStore = M('sysitem_sku_store');
            if($ret['code'] == 100 && $ret['errCode'] == 0){
                $storeInfo = $ret['data']['rtInventorys'];
                foreach ($storeInfo as $key => $val) {
					//更新库存
					if($val['result'] == 1){
						$map = array(
							'sku_id' => $val['header']['skuNo']
						);
						if(intval($val['header']['inventoryStatus']) == 10){
							$res = $modelSkuStore->where($map)->setField('store', intval($val['header']['availableQty']));
						}else{
							$res = $modelSkuStore->where($map)->setField('inferior', intval($val['header']['availableQty']));
						}								
						if(!$res){
							M()->rollback();
							$this->this->error("sku:{$vals['skuNo']}更新库存失败..");
						}
					}else if($val['result'] == 2){
						//重新推送商品
						$url = C('COMMON_API')."Sf/pushItemSkuSF/";
						$data = array(
							'skuId' => $val['header']['skuNo'],
						);
						$return = $this->requestPost($url,$data);
						$resu = json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
						if($resu['code'] == 100 && $resu['errCode'] == 0){
							foreach($resu['data']['items'] as $keys => $vals){
								if($vals['result'] == 2){
									M()->rollback();
				                    $this->error("sku:{$vals['skuNo']}推送顺丰失败");
								}
							}
						}else{
							M()->rollback();
                			$this->error($resu['msg']);
						}							
					}
                }
            }else{
                $this->error($ret['msg']);
            }
        }
		$data['send_type'] = $sendType;
		$data['supplier_id'] = $supplierId;
		$res = $this->dGoods->editThisItemInfo($itemId,$data);
		if(!$res){
			M()->rollback();
			$this->this->error("更新发货类型失败...");
		}			
		$resu = $this->dGoods->editSupplierItemInfo($itemId,$data);
		if(!$resu){
			M()->rollback();
			$this->this->error("供应商商品更新发货类型失败...");
		}	
		M()->commit();			
		echo "<div style='padding-left:42%;padding-top:13%;color:#5a98de;font-weight: bolder;font-size: x-large;'>修改成功！</div>";

	}		
/*
 * 
 * 列出所有供应商
 * */
	public function allSupplierUser(){
		$keyword=I('keyword');
		if($keyword){
			$condition['company_name']=array('like','%'.$keyword.'%');
		}
		$condition['status']=1;
		$res=$this->dGoods->getAllsupplierUser($condition);
		echo json_encode($res);
	}
/*
 * 
 * 分类导航管理
 * */
	public function categoryNav(){
		$catNavInfo=$this->dGoods->getAllCategoryNav();
		$catNavSon=$this->dGoods->getAllCatgoryNavSon();
		foreach($catNavSon as $key=>$value){
			$catIds[]=$value['cat_id'];
		}
		if(!empty($catIds)){
			$res=$this->dGoods->getCatInfos($catIds);//三级分类名称
		}
		if($res){
			foreach($catNavSon as $key=>$value){
				foreach($res as $keys=>$values){
					if($value['cat_id']==$values['cat_id']){
						$catNavSon[$key]['catInfo']=$values;
					}
				}			
			}		
		}
		foreach($catNavInfo as $key=>$value){
			foreach($catNavSon as $keys=>$values){
				if($value['cat_nav_id']==$values['cat_nav_id']){
					$catNavInfo[$key]['son'][]=$values;
				}
			}			
		}
		$this->assign('list',$catNavInfo);
		$this->display();
	}
/*
 * 
 * 添加分类导航
 * */
	public function editCategoryNav(){
		$navId=I('navId');
		if($navId){
			$res=$this->dGoods->getOneCategoryNav($navId,"cat_nav_id,nav_name,nav_banner,order_sort");
			$this->assign('info',$res);
		}
		$this->display();
	}
/*
 *  保存分类导航
 * */
 	public function saveCategoryNav(){
 		$data=I('data');
		$navId=I('navId');
		if(empty($data['nav_name'])){
			$this->error("请输入分类导航名称!");	
		}		
		$banner=A('Activity')->uploadImg('banner');
		if(!empty($banner)){
			$data['nav_banner']=$banner;
		}
		if($navId){
			//编辑
			$data['modifyine_time']=time();
			$res=$this->dGoods->editCatgoryNav($navId,$data);
			if($res){
				$success=1;				
			}
		}else{
			//添加
			if(empty($data['nav_banner'])){
				$this->error("请上传分类导航的banner图!");	
			}
			$data['created_time']=time();
			$returnNavId=$this->dGoods->addCatgoryNav($data);	
			if($returnNavId){
				$success=1;				
				$navId=$returnNavId;
			}
		}
		if($success==1){
			$this->success('成功');
		}else{
			$this->error('失败');
		}
		
 	}
/*
 * 删除导航分类
 * */	
	public function delCategoryNav(){
		$navId=I('navId');
		if($navId){
			$data['modifyine_time']=time();
			$data['is_delete']=1;
			$res=$this->dGoods->editCatgoryNav($navId,$data);
			if($res){
				$resu=$this->dGoods->editWhereCatgoryNavSon(array('cat_nav_id'=>$navId),$data,'modifyine_time,is_delete');
			}
			echo json_decode($resu);
		}
		
	} 
/*
 * 添加导航栏子分类
 * */	
	public function addCategoryNavSon(){
		$navId=I('navId');
		$res=$this->dGoods->getAllCatgoryNavSon(array('cat_nav_id'=>$navId),'cat_id');
		foreach($res as $key=>$value){
			$catIds[]=$value['cat_id'];
		}
		$this->assign('navId',$navId);
		$this->assign('hasCatIds',$catIds);
		$this->display();
	}
/*
 * 保存导航添加的子分类
 * */	
	public function saveCategoryNavSon(){
		$catIds=I('catIds');
		$data['cat_nav_id']=I('navId');
		$data['order_sort']=I('orderSort/d');
		//已存在的catId
		$res=$this->dGoods->getAllCatgoryNavSon(array('cat_nav_id'=>$data['cat_nav_id']),'cat_id');
		foreach($res as $key=>$value){
			$hascatIds[]=$value['cat_id'];
		}
		//比较相同地方
		if(!empty($hascatIds)){
			$sameCatId=array_intersect($hascatIds, $catIds);
			//存储数据库不存在的catId
			$catIds=array_diff($catIds, $sameCatId);
		}
		if(empty($catIds)){
			$this->error('请选择分类导航的子分类！');
		}		
		if(!empty($data['cat_nav_id'])){
			foreach($catIds as $key => $value){
				$data['cat_id']=$value;
				$data['created_time']=time();
				$res[]=$this->dGoods->addCatgoryNavSon($data);
			}
		}
		if(!empty($res)){
			$this->success('子分类添加成功！');
		}
	}	
/*
 * 编辑分类导航的子分类
 * */
	public function editCategoryNavSon(){
		$sonId=I('sonId');
		$this->assign('sonId',$sonId);
		$this->display();
	}
/*
 * 编辑分类导航的子分类
 * */
	public function editCategoryNavSonDeal(){
		$sonId=I('sonId');
		$data['is_delete']=I('isdelete');
		$data['order_sort']=I('orderSort','','intval');
		$data['modifyine_time']=time();
		if($sonId && !empty($data)){
			$res=$this->dGoods->editCatgoryNavSon($sonId,$data,'order_sort,modifyine_time,is_delete');
			if($res && $data['order_sort']){
				echo "<div style='padding-left:40%;color:#5a98de;font-weight: bolder;font-size: x-large;'>设置成功</div>";
			}
			if($data['is_delete']){
				echo json_encode($res);
			}			
			
		}
	}
/*
 * 之前的订单同步商品自发代发类型，及供应商id
 * */	
	public function syncSendType(){
		$items=M('systrade_order')->where('supplier_id is null or send_type =0')->getField('item_id',TRUE);
		$items=array_unique($items);
		$condition['item_id']=array('in',$items);
		$itemInfos=M('sysitem_item')->where($map)->field('item_id,supplier_id,send_type')->select();
		$a=0;
		foreach($itemInfos as $k=>$v){
			$res=M('systrade_order')->where(array('item_id'=>$v['item_id']))->field('supplier_id,send_type')->save(array('supplier_id'=>$v['supplier_id'],'send_type'=>$v['send_type']));
			if($res){
				$a++;
			}
		}
		echo '更新成功'.$a.'种订单商品！';
	}
/*
 * 修改sku信息
 * */	
	public function modifySkuInfo(){
		$skuId=I('get.skuId','','intval');
		if($skuId){
			$skuInfo=$this->dGoods->getThisSkuInfo($skuId);
			$skuStore=$this->dGoods->getThisSkuStores($skuId);
			$skuInfo=array_merge($skuInfo,$skuStore);
			$this->assign('info',$skuInfo);
		}else{
			echo 'Error';
		}
		$this->display();		
	}
/*
 * 修改sku处理
 * */	
	public function modifySkuInfoDeal(){
		$skuId=I('get.skuId','','intval');
		$data=I('data');
		if($skuId && $data){
			$data['modified_time']=time();
			$data['price']=trim($data['price']);
			$data['cost_price']=trim($data['cost_price']);
			if($data['cost_price']>$data['price']){
				$this->error('销售价应大于进货价!');
			}
			$infoRes=$this->dGoods->editThisSkuInfo($skuId,$data,'bn,price,cost_price,barcode,modified_time');
			$storeRes=$this->dGoods->editThisSkuStore($skuId,$data);
			if($infoRes || $storeRes){
				$this->success('sku信息修改成功!');
			}else{
				$this->error('sku信息修改失败!');
			}
		}
	}
/*
 * 同步首页分类
 * */	
    //生成分类列表
    public function creatCat(){
		$catList = $this->dCategory->getThisAllcat(1);
		$catListI = $this->dCategory->getThisAllcat(2);
		$catListS = $this->dCategory->getThisAllcat(3);
        $data = "";
        foreach ($catList as $key => $value) {
            $data .= "<li><a href=\"/shop.php/Items/itemList/catId/".$value['cat_id']."/level/1\">".$value['cat_name']."</a>";
            $data .= "<div class=\"details\">";
            foreach ($catListI as $ke => $val) {
                if (empty($ke)) {
                    $data .= "<div class=\"goods up\">";
                }else{
                    $data .= "<div class=\"goods\">";
                }
                if ($value['cat_id'] == $val['parent_id']) {
                    
                    $data .="<div class=\"goodstitle\">";
                    $data .="<a href=\"/shop.php/Items/itemList/catId/".$val['cat_id']."/level/2\">".$val['cat_name']."<span>&gt;</span></a>";
                    $data .="</div>";
                    $data .="<ul>";
                    foreach ($catListS as $k => $v) {
                        if ($val['cat_id'] == $v['parent_id']) {  
                            $data .="<li><a href=\"/shop.php/Items/itemList/catId/".$v['cat_id']."\">".$v['cat_name']."</a></li>";
                        }
                    }
                    $data .="</ul>";                 
                }
                $data .="</div>";  
            }            
            $data .="</div>";
            $data .="</li>";
        }
		$catFile=C('MALLCAT');
		$catCompanyFile=C('COMPANYMALLCAT');
        @unlink($catFile);
        @unlink($catCompanyFile);
       if(@file_put_contents($catFile,$data."\n",FILE_APPEND) && @file_put_contents($catCompanyFile,$data."\n",FILE_APPEND)){
       	echo "同步商城导航分类成功!";
       }else{
       	echo "同步商城导航分类失败!";
       }
    }
    //生成分类旧版首页
    public function creatOldCat(){
		$catList = $this->dCategory->getThisAllcat(1);
		$catListI = $this->dCategory->getThisAllcat(2);
		$catListS = $this->dCategory->getThisAllcat(3);
        $data = "";
        foreach ($catList as $key => $value) {
            $data .= "<li class=\"category-primary-li\"><div class=\"category-primary-item\"><a href=\"/shop.php/Items/itemList/catId/".$v['cat_id']."/level/1\" >";
            $data .= $value['cat_name']."<i style=\"background-position: 0px 0px;\"></i></a><span class=\"active-arrow\"></span></div>";
            $data .="<div class=\"category-sub\" style=\"min-height:416px\">";   
            foreach ($catListI as $ke => $val) {                
                if ($value['cat_id'] == $val['parent_id']) {                 
                    $data .=" <div class=\"category-sub-item\">";
                    $data .="<div class=\"category-sub-title\"><a href=\"/shop.php/Items/itemList/catId/".$v['cat_id']."/level/2\" >".$val['cat_name']."&nbsp;&nbsp;></a></div>";
                    $data .="<div class=\"category-sub-sub-title\">";
                    $data .="<ul>";
                    foreach ($catListS as $k => $v) {
                        if ($val['cat_id'] == $v['parent_id']) {  
                            $data .="<li><a href=\"/shop.php/Items/itemList/catId/".$v['cat_id']."\">".$v['cat_name']."</a></li>";
                        }
                    }
                    $data .="</ul>";
                    $data .="</div>";
                    $data .="</div>";               
                }   
            }
            $data .="</div>";           
            $data .="</li>";
        }
		$catFile=C('MALLINDEXCAT');
        unlink ($catFile);
       if(@file_put_contents($catFile,$data."\n",FILE_APPEND)){
      	 	echo "同步商城导航分类成功!";	
       }else{
     	  	echo "同步商城导航分类失败!";
       }
    }
/*
 * 商品上下架
 * */
	public function changeItemStatus(){
		$itemId=I('itemId');
		$goodsStatus=I('goodsStatus');
		if(empty($itemId) || empty($goodsStatus)){
			$this->ajaxReturn(0);			
		}
		if($goodsStatus == 'instock'){
			$data['approve_status']='onsale';
			$data['is_force']=1;
			$data['list_time']=time();
		}else if($goodsStatus == "onsale"){
			$data['approve_status']='instock';
			$data['is_force']=2;
			$data['delist_time']=time();
		}	
		$res=$this->dGoods->changeItemStatus($itemId,$data);		
		if($res){
			$this->ajaxReturn(1);			
		}else{
			$this->ajaxReturn(0);			
		}	
		
	}

	/**
	*	商品详情 awen 2017-02-15
	**/
	public function productInfo(){
		$itemId = I('itemId');
		if (empty($itemId)) {
			$this->error("缺少商品Id");	
		}
		//商品详情
		$itemInfo = $this->dGoods->getThisItemInfo($itemId,$field);
		if (empty($itemInfo)) {
			$this->error("未查询到商品信息");	
		}
		//商品图片
        if (!empty($itemInfo['list_image'])) {
            $newItemInfoImage = explode(',',$itemInfo['list_image']);
            $itemInfo['new_list_images'] = $newItemInfoImage;            
        }
        //商品详情
        unset($condition);
		unset($field);
		$condition = array('item_id'=>$itemId);
		$field = array('pc_desc');
        $itemDescInfo = $this->dGoods->getItemDesc($condition,$field);
        $itemInfo['itemDesc'] = $this->charback($itemDescInfo['pc_desc']);
		//商品sku列表
		$condition = 'st.item_id ='.$itemId;
		$field = array('st.sku_id','bn','price','cost_price','mkt_price','cash','point','barcode','spec_info','sold_quantity','store','freez','parent_sku_id');
		$itemSkuList = $this->dGoods->getSkuList($condition,$field);
		//店铺信息
		unset($condition);
		unset($field);
		$condition = array('shop_id'=>$itemInfo['shop_id']);
		$field = array('shop_name');
        $shopInfo = $this->dGoods->getShopInfo($condition,$field);
        //分类信息
        if ($itemInfo['cat_id']) {        
			unset($condition);
			unset($field);
			$condition = array('cat_id'=>$itemInfo['cat_id']);
			$field = array('cat_id,parent_id,cat_name');
			$catList = $this->dGoods->getCatAllList($condition,$field);
			$itemInfo['cat_name'] = $catList[0]['cat_name'].'>'.$catList[1]['cat_name'].'>'.$catList[2]['cat_name'];
		}
		//品牌信息
		if ($itemInfo['brand_id']) {
			unset($condition);
			unset($field);
			$condition = array('brand_id'=>$itemInfo['brand_id']);
			$field = array('brand_name');
			$brandInfo = $this->dGoods->getBrandInfo($condition,$field);
			$itemInfo['brand_name'] = $brandInfo['brand_name'];
		}
		//查询供应商信息
		if ($itemInfo['supplier_id']) {
			unset($condition);
			unset($field);
			$condition = array('supplier_id'=>$itemInfo['supplier_id']);
			$field = array('company_name');
			$supplierInfo = $this->dGoods->getSupplierInfo($condition,$field);
			$itemInfo['supplier_name'] = $supplierInfo['company_name'];			
		}


        $itemInfo['shop_name'] = $shopInfo['shop_name'];
		$this->assign('itemInfo',$itemInfo);
		$this->assign('itemSkuList',$itemSkuList);
		$this->display();
	}
	public function charback($str){
        $str=str_replace(array("&#039;","&quot;","&lt;","&gt;","&amp;reg;","&amp;","&nbsp;",'<p><br />','</p><br />','<br>','\\'),array("'","\"","<",">","&reg;","&"," ",'<p>','</p>','<br />',''),$str);
        $str=preg_replace( '@<script(.*?)</script>@is','&lt;script\1&lt;/script&gt;',$str);
        $str=preg_replace( '@<iframe(.*?)</iframe>@is','',$str);        
        return preg_replace('@<style(.*?)</style>@is', '',$str);
    }
	//修改商品信息优化数据
	public function updateItem(){
		$itemId = I('itemId');
		if (!$itemId) {
			$this->error('系统繁忙，请稍后重试！');
		}
		$data['title'] = I('title');
		$data['sub_title'] = I('sub_title');
		$data['warm_reminder'] = I('warm_reminder');
		$data['keywords'] = I('keywords');
		$data['mkt_price'] = I('mkt_price');
		$condition = array('item_id'=>$itemId);
		try {			
			$res = $this->dGoods->updateItem($condition,$data);
			$resSku = $this->dGoods->updateItemSku($condition,array('title'=>$data['title']));
			$resSitem = $this->dGoods->updateSupplierItem($condition,array('title'=>$data['title']));
			$resSitemSku = $this->dGoods->updateSupplierItemSku($condition,array('title'=>$data['title']));
		} catch (\Exception $e) {
			echo $e->getMessage();
			$this->error('修改失败！');
		}
		if ($res === false && $resSku === false && $resSitem === false && $resSitemSku === false) {			
			$this->error('修改失败！');
		}else{
			$this->success('修改成功！');
		}
	}
	//修改商品价格和积分
	public function editCashPoints(){
		$skuId = I('skuId');
		if (empty($skuId)) {
			$this->error('系统繁忙，请稍后重试！');
		}
		$condition = array('sku_id'=>$skuId);
		$field = array('sku_id','item_id','price','cash','point');
		$skuInfo = $this->dGoods->getSkuInfo($condition,$field);
		$this->assign('skuInfo',$skuInfo);
		$this->display();
	}
	//修改商品价格和积分
	public function updateCashPoints(){
		$skuId = I('skuId');
		$itemId = I('itemId');
		$cash = I('cash');
		$point = I('point');
		$price = I('price');
		if (empty($skuId) || empty($itemId) || empty($price)) {
			$this->error('系统繁忙，请稍后重试！');
		}	
		if (!is_float(round($cash,3))) {
			$this->error('请输入正确的价格！');
		}
		if (!is_numeric($point)) {
			$this->error('请输入正确的积分！');
		}

		$this->model = new \Think\Model(); 
		$this->model->startTrans();		

		//修改售价和积分
		$condition = array('sku_id'=>$skuId);
		$data = array('cash'=>$cash,'point'=>$point,'price'=>$price);
		try {
			$resUpdateSku = $this->dGoods->updateSku($condition,$data);
			if (!$resUpdateSku) {			
				$this->error("编辑失败！");
			}
		} catch (\Exception $e) {
			$this->error("编辑失败！");
		}
		
		//查询最低价格商品
		unset($condition);
		unset($field);
		$condition = array('item_id'=>$itemId);
		$field =array('sku_id','price','cash','point');
		$group = "price ASC";
		$skuGroup = $this->dGoods->getSkuGroup($condition,$field,$group);
		if (!$skuGroup) {
			$this->model->rollback();
			$this->error("编辑失败！");
		}
		if ($skuGroup[0]['sku_id'] == $skuId) {
			unset($condition);
			unset($data);
			$condition = array('item_id'=>$itemId);
			$data =array('cash'=>$cash,'point'=>$point);
			try {
				$resItem = $this->dGoods->updateItem($condition,$data);
				if ($resItem === false) {
					$this->model->rollback();
					$this->error("编辑失败！");				
				}
			} catch (\Exception $e) {
				$this->model->rollback();
				$this->error("编辑失败！");
			}			
		}
		$this->model->commit();
		$this->success("编辑成功！");
	}
	/**
	 * 品牌列表
	 */
	public function brandList(){
		$p = I('get.p', 1, 'intval');
		$keyword = I('keyword', '', 'trim,strip_tags,stripslashes');
		$status = I('status', 0,'intval');
		$size = 20;
		if($keyword){
			$map['brand_id|brand_name|brand_alias|brand_desc'] = array('like', "%{$keyword}%");
			$p = 1;
		}
		//状态
		if($status == 1){
			$map['disabled'] = 0;
		}else if($status == 2){
			$map['disabled'] = 1;
		}
		$num = $this->dGoods->getBrandCount($map);
		$list = $this->dGoods->getBrands($map,$p,$size);
	 	//检测品牌是否可删
	 	$brandIds = arrGetField($list,'brand_id');
		//底下商品数
		if($brandIds){
			$map = array(
				'brand_id' => array('in',$brandIds)
			);
			$itemArr = $this->dGoods->fetFieItemImfo($map,'item_id,brand_id');
//			$itemIds = array_keys($itemArr);
//			if($itemIds){
//				$map = array(
//					'item_id' => array('in', $itemIds),
//					'approve_status' => 'onsale'
//				);
//				$onsaleitems = $this->dGoods->getOnsaleItem($map);
//			}
			$brandItems = array();
			foreach($itemArr as $key=>$val){
//				if(in_array($key, $onsaleitems)){
					$brandItems[$val][] = $key;
//				}
			}
		}
//		$noCanDelIds = $this->dGoods->getCatRelBrand($brandIds);          
		$this->assign('list',$list);
//		$this->assign('noCanDelIds',array_unique($noCanDelIds));
		$this->assign('brandItems',$brandItems);
		$this->assign('count',$num);
		$this->assign('pageStr',showPage($num, $size));
		$this->assign('keyword',$keyword);
		$this->assign('status',$status);
		$this->assign('nothing',$nothing);
		$this->display();	
	}	
	/**
	 * 添加编辑品牌
	 */
	public function brandEdit(){
		$brandId = I('brandId');
		if($brandId){
			if(!is_numeric($brandId)){
				exit('ERROR!');
			}
			$map = array(
				'brand_id' => $brandId,
				'disabled' => 0
			);
			$info = $this->dGoods->getBrandInfo($map);
			$this->assign('info',$info);
		}
		$this->display();
	}
	/**
	 * 编辑品牌处理
	 */
	public function brandEditDeal(){
		$data = I('data');
		$brandId = I('brandId');
		if(!empty($brandId) && !is_numeric($brandId)){
			$this->error("ID有误!");	
		}
		if(!is_array($data)){
			$this->error("数据有误!");	
		} 
		$data = trimArrVal($data);
		if(empty($data['brand_name'])){
			$this->error("请输入品牌名称!");	
		}
		$banner=A('Activity')->uploadImg('banner','images');
		if(!empty($banner)){
			$data['brand_logo'] = $banner;
		}
		$data['modified_time'] = time();
		if($brandId){
			//编辑
			$res = $this->dGoods->editBrand($brandId,$data);
		}else{
			//添加
			$res = $this->dGoods->addBrand($data);
		}	
		if($res){
			$this->success('SUCCESS!');
		}else{
			$this->error('FAIL!');
		}
	} 
	/**
	 * 删除品牌
	 */
	public function chgBrand(){
		$brandId = I('brandId');
		$val = I('val');
		$ret = array(
			'code' => 0,
			'msg'  => 'UNKNOW ERROR!'	 
		);
		if(!is_numeric($brandId)){
			$ret['msg'] = 'ID有误!';
			$this->ajaxReturn($ret);
		}
		if(!in_array($val, array(0,1))){
			$val = 1;
		}
		$data = array('disabled'=>$val);
		$res = $this->dGoods->editBrand($brandId,$data);
		if($res){
			$ret['code'] = 1;
			$ret['msg'] = '操作成功!';
		}else{
			$ret['msg'] = '操作失败!';
		}
		$this->ajaxReturn($ret);
					
	}
	/**
	 * 选择品牌
	 */
	public function choiceBrand(){
		$catId = I('catId');	
		$p = I('get.p', 1, 'intval');
		$type = I('type','all','trim');
		$keyword = I('keyword', '', 'trim,strip_tags,stripslashes');
		if(!is_numeric($catId)){
			exit('分类ID有误!');
		}
		$level=$this->dGoods->getFieldCategory($catId,'level');
		if(empty($level) || $level!=3){
			exit('该分类不支持选择品牌!');
		}
		$reBrandIds = $this->dGoods->getCatBrand($catId);  //已关联品牌
		$size = 100;
		if($keyword){
			$map['brand_name|brand_alias|brand_desc'] = array('like', "%{$keyword}%");
			$p = 1;
		}
		$map['disabled'] = 0;
		$map['supplier_id'] = 0;
		if($type == 'rel'){
			//已关联品牌
			if(!empty($reBrandIds)){
				$map['brand_id'] = array('in', $reBrandIds);
			}else{
				$map['brand_id'] = 0;
			}
		}
		$num = $this->dGoods->getBrandCount($map);
		$list = $this->dGoods->getBrands($map,$p,$size);
		$nowPageBrandIds = arrGetField($list,'brand_id'); //当前页品牌id
		$relBrandsed = array_intersect($nowPageBrandIds, $reBrandIds);  //已关联品牌
		$this->assign('relBrandsed',implode(',', $relBrandsed));
		$this->assign('list',$list);
		$this->assign('count',$num);
		$this->assign('pageStr',showPage($num, $size));
		$this->assign('keyword',$keyword);		
		$this->assign('reBrandIds',$reBrandIds);
		$this->assign('catId',$catId);
		$this->assign('type',$type);
		$this->display();
	}
	/**
	 * 选择品牌处理
	 */
	public function choiceBrandDeal(){
		$catId = I('catId');	
		$checkedBrand = I('checkedBrand');
		$relBrandsed= I('relBrandsed');
		if(!is_numeric($catId)){
			$this->error('分类ID有误!');
		}
		$oldRels = explode(',', $relBrandsed);
		if(empty($checkedBrand)){
			$checkedBrand = array();
		}
		if(!empty($oldRels)){
			$cancelRelIds = array_diff($oldRels, $checkedBrand);   //取消关联的id
			$addRelIds = array_diff($checkedBrand, $oldRels);      //新建关联
		}else{
			$addRelIds = $checkedBrand;
		}
		if(empty($cancelRelIds) && empty($addRelIds)){
			$this->error('请选择品牌关联或取消关联!');
		}
		if(!empty($cancelRelIds) && is_array($cancelRelIds)){
			//原关联的品牌取消关联
			$map = array(
				'cat_id' => $catId,
				'brand_id' => array('in' ,$cancelRelIds)
			);
			$ret = $this->dGoods->cancelRelBrand($map);
		}
		if(!empty($addRelIds)){
			$data = array();
			foreach($addRelIds as $key=>$val){
				$data['brand_id'] = $val;
				$data['cat_id'] = $catId;
				$res[] = $this->dGoods->addRelbrands($data);
			}
		}
		if($ret || $res){
			$this->success('品牌关联成功!');
		}
	}
/**
 * 店铺分类
 */
	public function shopCategory(){
		//店铺
		$map = array(
			'status' => 'active'
		);
		$shopList = M('sysshop_shop')->where($map)->field('shop_id,shop_name')->select();
		//店铺分类
		$categoryInfos = M('sysshop_shop_cat')->getField('cat_id,shop_id,parent_id,level,cat_name,disabled');
		$category = array();
		$nextCategory = array();
		foreach($categoryInfos as $key=>$val){
			if($val['level'] == 1){
				$category[$val['shop_id']][] = $val['cat_id'];
			}else if($val['level'] == 2){
				$nextCategory[$val['parent_id']][] = $val['cat_id'];
			}
		}
		$this->assign('shopList',$shopList);
		$this->assign('categoryInfos',$categoryInfos);
		$this->assign('category',$category);
		$this->assign('nextCategory',$nextCategory);
		$this->display();
	}
/**
 * 修改、添加店铺分类页
 */
	public function shopCategoryEdit(){
		$catId = I('catId');
		$parentId = I('parentId');
		$shopId = I('shopId');
		if($catId){
			$map = array(
				'cat_id' => $catId 
			);
			 $catInfo = M('sysshop_shop_cat')->where($map)->field('cat_name,cat_id,order_sort,parent_id,shop_id')->find();
		}
		$this->assign('info',$catInfo);
		$this->assign('shopId',$shopId);
		$this->assign('parentId',$parentId);		
		$this->display();
	}
/**
 * 保存店铺分类
 */ 
 	public function shopCategoryDeal(){
		$catId=I('catId');
		$data=I('data');
		$data['modified_time']=time();
		$modelShopCat = M('sysshop_shop_cat');
		if(empty($catId) && $data['parent_id'] > 0){
			$data['is_leaf'] = 1;
			$data['level'] = 2;
			$data['cat_path'] = $data['parent_id'].',';
		}
		if($catId){
			//修改
			$map = array(
				'cat_id' => $catId
			);
			$res = $modelShopCat->where($map)->field('cat_name,order_sort')->save($data);
		}else{
			//添加
			$res = $modelShopCat->data($data)->add();
		} 
		if($res){
			$this->success('Success...');
		}else{
			$this->error('Fail...');
		}		
		
 	}
/**
 * 店铺分类的禁用启用
 */ 
 	public function dealShopCategory(){
		$catId=I('catId');
		$type=I('type');
		if($catId){
			if($type == 0){
				$disabled = 1;
			}else if($type == 1){
				$disabled = 0;
			}
			$map = array(
				'cat_id' => $catId
			);			
			$res = M('sysshop_shop_cat')->where($map)->setField('disabled', $disabled);
			$this->ajaxReturn($res);
		} 		
 	}

 	/*搜索自动出关键字
	* awen 20170316
	*/
	public function selectKeyword(){
		$keywords = I('keywords');
		$keywordsHtml = "";
		
		//$t1 = microtime(true);//计算时间，记得删除---------------------------

		if (empty($keywords)) {
			echo $keywordsHtml;
			exit();
		}
		$condition['_string'] = " status = 1 and company_name like '%".$keywords."%'";
		$field = 'company_name,supplier_id';
		$limit = 10;			
	 	$itemList = M('supplier_user')->where($condition)->field($field)->limit($limit)->select();
		if (!is_array($itemList)) {
			exit($keywordsHtml);
		}
		if (empty($itemList)) {
			exit($keywordsHtml);
		}	
		$keywordsHtml .= "<ul class='keywords_ul'>";
		foreach ($itemList as $key => $value) {
			$keywordsHtml .= "<li class='keywords_li'><span class='keywords' data-supplierId='".$value['supplier_id']."'>".$value['company_name']."</span></li>";
		}
		$keywordsHtml .= "</ul>";

		//$t2 = microtime(true);//计算时间，记得删除---------------------------
		//echo '耗时'.round($t2-$t1,3).'秒';//计算时间，记得删除---------------------------

		echo $keywordsHtml;	
	}

	
	//上传商品详情图片
	public function uploadDescPic(){
		$ret = array('state' => 'unkown error');
		 
		$rootPath = '/data/www/b2b2c/public/images';
		$host = C("TMPL_PARSE_STRING.__LISHE__");
		 
		//限制图片尺寸，长，宽
		$file = $_FILES['upfile'];
		$imginfo = getimagesize($file['tmp_name']);
		$width = $imginfo[0];
		//$height = $imginfo[1];
		 
		if($width != 750){
			$ret['state'] = '图片宽度应为750';
			$this->ajaxReturn($ret);
		}
		 
		 
		if(empty($rootPath)){
			$ret['state'] = '路径不存在';
			$this->ajaxReturn($ret);
		}
		$randStr = md5(microtime().UID);
		$subName = '/'.substr($randStr,0,2).'/'.substr($randStr,2,2).'/'.substr($randStr,4,2);
	
		$config = array(
			'mimes'         =>  array('image/jpg','image/gif','image/png','image/jpeg'), //允许上传的文件MiMe类型
			'maxSize'       =>  2*1024*1024, //限制1M, 上传的文件大小限制 (0-不做限制)
			'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
			'autoSub'       =>  true, //自动子目录保存文件
			'subName'       =>  $subName, //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
			'rootPath'      =>  $rootPath, //保存根路径
			'savePath'      =>  '', //保存路径
			'saveName'      =>  array('md5', $randStr), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
			'replace'       =>  false, //存在同名是否覆盖
			'hash'          =>  false, //是否生成hash编码
		);
		$Upload = new \Think\Upload($config);// 实例化上传类
		$result = $Upload->upload();
		if($result){
			$rootPath = ltrim($rootPath, '.');
			$imgInfo = $result['upfile'];
			$imgUrl = $host . '/images' .$imgInfo['savepath'] . $imgInfo['savename'];
	
			$ret = array(
				'original' => $imgInfo['name'],
				'size' => $imgInfo['size'],
				'state' => 'SUCCESS',
				'title' => $imgInfo['savename'],
				'type' => $imgInfo['ext'],
				'url' => $imgUrl,
			);
	
			$this->ajaxReturn($ret);
		}else{
			$ret['state'] = $Upload->getError();
			$this->ajaxReturn($ret);
		}
	}
	
	//保存商品详情
	public function saveItemDesc(){
		$itemid = I('post.itemid','', 'intval');
		$desc = I('post.desc',''); //不过滤了
		$ret = array('code' => -1, 'msg' => 'unkown error');
		if(!is_numeric($itemid) || $itemid < 1){
			$ret['msg'] = '商品id有误';
			$this->ajaxReturn($ret);
		}
		$map = array(
			'item_id' => $itemid,
		);
		$SysitemItemDesc = M('sysitem_item_desc');
		$result = $SysitemItemDesc->where($map)->getField('item_id');
		if(empty($result)){
			//不存在添加
			$data = array(
				'item_id' => $itemid,
				'pc_desc' =>$desc,
			);
			$result = $SysitemItemDesc->add($data);
		}else{
			//存在更新
			$result = $SysitemItemDesc->where($map)->setField('pc_desc', $desc);
		}
		if (!$result) {
			$ret['msg'] = '保存商品详情失败';
			$this->ajaxReturn($ret);
		}
		//更新供应商商品信息
		$map = array(
			'item_id' => $itemid,
		);
		$SupplierItem = M('supplier_item');
		$result = $SupplierItem->where($map)->getField('sitem_id');
		if(!empty($result)){
			$map = array(
				'sitem_id' => $result,
			);
			M('supplier_item_desc')->where($map)->setField('pc_desc', $desc);
		}
		
		$ret['code'] = 1;
		$ret['msg'] = 'success';
		$this->ajaxReturn($ret);
	}
/**
 * 商品上下架
 * 商品状态1：上架 2：下架 3 ：永久下架
 */
	public function modifyItemStatus(){
		$itemId = I('itemId');
		$map = array(
			'item_id' => $itemId
		);
		$itemStatus = M('sysitem_item_status')->where($map)->find();
		if(!$itemStatus){
			exit('暂无记录!');			
		}
		if($itemStatus['approve_status'] == 'instock'){
			//下架
			$itemStatus['status'] = 2;
			if($itemStatus['is_force'] == 2){
				$itemStatus['status'] = 2;
			}else if($itemStatus['is_force'] == 3){
				$itemStatus['status'] = 3;
			}
		}else if($itemStatus['approve_status'] == 'onsale'){
			//上架
			$itemStatus['status'] = 1;
		}
		$this->assign('info',$itemStatus);
		$this->display();
	}
/**
 * 保存上下架
 */
	public function itemStatusDeal(){
		$itemId = I('itemId');
		$status = I('status', 2, 'intval');
		if(!$itemId){
			$this->error('商品ID有误!');
		}
		if(!in_array($status, array(1,2,3))){
			$this->error('商品状态有误!');
		}
		if($status == 1){
			$data['approve_status'] = 'onsale';
			$data['is_force'] = 1;
			$data['list_time'] = time();
		}else{
			$data['approve_status'] = 'instock';
			$data['delist_time'] = time();	
			if($status == 2){
				$data['is_force'] = 2;
			}else if($status == 3){
				$data['is_force'] = 3;
			}					
		}
		$map = array(
			'item_id' => $itemId
		);
		$res = M('sysitem_item_status')->where($map)->save($data);
		if($res){
			$this->success('Success..');
		}else{
			$this->error('Fail..');
		}
	}
/**
 * 一键禁用无上架商品的品牌
 */	
	public function onkeyDisBrand(){
		$ret = array('code' => 0, 'msg' => 'Unkonow');
		$map = array(
			'disabled' => 0
		);
		$modelBrand = M('syscategory_brand');
		$brandIds = $modelBrand->where($map)->getField('brand_id', TRUE);
		if(empty($brandIds)){
			$ret['msg'] = '无需要禁用的品牌';
			$this->ajaxReturn($ret);	
		}
		//存在商品的品牌
		$map = array(
			'brand_id' => array('in', $brandIds)
		);
		$itemBrands = M('sysitem_item')->where($map)->getField('brand_id', TRUE);
		$needDis = array_diff($brandIds, array_unique($itemBrands));
		if(empty($needDis)){
			$ret['msg'] = '无需要禁用的品牌.';
			$this->ajaxReturn($ret);	
		}		
		$map = array(
			'brand_id' => array('in', $needDis)
		);		
		$res = $modelBrand->where($map)->setField('disabled', 1);
		if(!$res){
			$ret['msg'] = '禁用品牌失败';
			$this->ajaxReturn($ret);			
		}
		$ret['code'] = 1;
		$ret['msg'] = '成功禁用'.count($needDis).'个品牌';
		$this->ajaxReturn($ret);		
	}
	
 	
}