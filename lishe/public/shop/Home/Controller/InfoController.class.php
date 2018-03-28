<?php
namespace Home\Controller;

class InfoController extends CommonController {
		public function __construct(){
		parent::__construct();
        $this->itemModel = M('sysitem_item');//商品表
        $this->itemDescModel = M('sysitem_item_desc');//商品描述
        $this->freepostageItemModel = M('syspromotion_freepostage_item');//包邮信息表
        $this->freepostageModel = M('syspromotion_freepostage');//包邮信息表
        $this->propsModel = M('syscategory_props');//属性表
        $this->propValuesModel = M('syscategory_prop_values');//属性值表
        $this->itemSkuModel = M("sysitem_sku");//库存表
        $this->itemSkuStoreModel = M("sysitem_sku_store");//库存表
        $this->siteAreaModel = M('site_area');//地区表
        $this->userAddressModel = M('sysuser_user_addrs');//用户地址表
        $this->cartModel = M('systrade_cart');//购物车表
        $this->itemHistoryModel = M('sysitem_item_history');//浏览记录
        $this->itemStatusModel = M('sysitem_item_status');//商品状态表
        $this->userFavModel = M('sysuser_user_fav');//商品收藏、缺货登记表  
        $this->shopFavModel = M("sysuser_shop_fav");//店铺收藏 
        $this->shopShopModel = M('sysshop_shop');//店铺表
        $this->catModel = M("syscategory_cat");//分类表
        $this->traderateModel = M('sysrate_traderate');//商品评论表
        $this->consultationModel = M('sysrate_consultation');//商品咨询表  
        $this->modelGoodsCat=D('Goodscat');
        //$this->modelInfo = D('Info');

	}
    public function index(){
    	$itemId = I('get.itemId',0,'intval');
    	$defaultSkuId = I('get.skuId',-1,'intval');
        if (!empty($itemId)) {     

            //商品信息
            $itemInfo = $this->itemModel->where('item_id = '.$itemId)->find();
            //判断商品价格和成本价格
            $isPrice = 1;
            if ($itemInfo['price'] <= $itemInfo['cost_price']) {
                $isPrice = 0;
            }
            if (empty($itemInfo)) {
                $this->error('您访问的商品不存在！');
                exit();
            }
        	//商品状态
        	 $itemStatus = getItemStatus($itemId);
            //商品描述
            $itemDesc = $this->itemDescModel->where('item_id = '.$itemId)->find();
            //获取导航分类
            $resCatList = array();
            if (getCatInfo($itemInfo['cat_id'])) {
                $resCatList = getCatInfo($itemInfo['cat_id']);
            }               
            //获取品牌信息
            $resBrandInfo = getBrandInfo($itemInfo['brand_id']);
            //用户登录了记录用户浏览记录
            if ($this->uid) {
                $this->itemHistory($itemInfo);
            }
            //字符串转数组方便调用
            if (!empty($itemInfo['list_image'])) {
                $newItemInfoImage = explode(',',$itemInfo['list_image']);
                $itemInfo['new_list_images'] = $newItemInfoImage;            
            }
            //优惠信息（包邮）
            if ($itemInfo['shop_id']) {
                $freepostageInfo = $this->freepostageModel->where('shop_id = '.$itemInfo['shop_id'])->find();
                if ($freepostageInfo['freepostage_id']) {
                    $freepostageItemInfo = $this->freepostageItemModel->where('freepostage_id = '.$freepostageInfo['freepostage_id'])->find();
                    if ($freepostageItemInfo['item_id'] == 0) {
                        $freepostageLimitMoney = "全场满&nbsp;".sprintf("%.2f",$freepostageInfo['limit_money'])."包邮";
                    }else{
                        $freepostageLimitMoney = "满 ".sprintf("%.2f",$freepostageInfo['limit_money'])." 包邮";
                    }
                }
            }
            //商品属性
            $arrItemSpecDesc = unserialize($itemInfo['spec_desc']);            
            $specValue = '';
            $specValueId = 0;
            if (is_array($arrItemSpecDesc)) {
                foreach ($arrItemSpecDesc as $key => $value) {
                    $specValue .= $key.",";
                    foreach ($value as $k => $val) {
                        $specValueId .= $k.",";
                    }
                }
            }
            //查属性表
            if (!empty($specValue)) {
                $where['prop_id'] = array('in',$specValue);;
                $propsList = $this->propsModel->where($where)->select();
                
            }
            //查属性值表
            if (!empty($specValueId)) {
                $where['prop_value_id'] = array('in',$specValueId);
                $propValuesList = $this->propValuesModel->where($where)->select();
            }
            //合并两个数组
            $newPropsValuesList = array();
            foreach ($propsList as $key => $value) {
                $newPropsValuesList[$key] = $value;
                foreach ($propValuesList as $k => $val) {
                    if ($val['prop_id'] == $value['prop_id']) {                    
                        $newPropsValuesList[$key]['item'][$k] = $val;
                    }
                }                
            }
            //查询库存表
            $map = array(
            	'item_id' => $itemId,
            	'disable' => 0
            );
            $skuList = $this->itemSkuModel->where($map)->select();
            $newSkuList = array();
            $skuPriceArr = array();
            if (!empty($skuList)) {
                foreach ($skuList as $key => $value) {
                    $newSkuList[$value['sku_id']] = $value;
                    $skuPriceArr[$value['sku_id']] = $value['price'];
                }
                unset($skuList);
                
                foreach ($newSkuList as $key => $value) {
                    if ($value['parent_sku_id'] != '') {
                    	if ($value['type'] == 7) { //组合购隐藏
                    		unset($newSkuList[$key]);
                    		continue;
                    	}
                        if ($value['start_time'] < time() && $value['end_time'] > time()) {          
                            unset($newSkuList[$value['parent_sku_id']]);
                        }
                    }
                }
                foreach ($newSkuList as $key => $value) {
                    $skuList[$key] = $value;
                }
            }
            /*团购*/
            $groupBuyMap = array();
            $groupBuyArr = array();
            $groupBuyItemArr = array();
            $groupBuyCount = array();
            /*秒杀*/
            $seckilArr = array();
            /*活动id集合*/
            $atcSkuIdArr = array();
            /*活动商品购买次数限制*/
            $actSkuTimesArr = array();
            /*活动集合*/
            $acidArr = array();
            $actArr = array();
            
            if(!empty($skuList)){
				foreach($skuList as $key=>$value){
					$skuId = $value['sku_id'];
					$type = $value['type'];
					$parentSkuId = $value['parent_sku_id'];
					if($key==0){
						$selectedSku = $skuId;
					}
					$sku[$skuId]=$value;
                    $skuIds[$key] = $skuId;
                    
                    //处理活动相关部分
                    $acid = $value['activity_config_id'];
                    //团购、秒杀
                    if ($type == 4) {
                    	$groupBuyMap[$skuId] = $acid;
                    	$groupBuyItemArr[$parentSkuId][$acid] = $value;
                    	$atcSkuIdArr[] = $skuId;
                    	$acidArr[] = $acid;
                    }else if ($type == 1) {
                    	$seckilArr[$skuId] = $value;
                    	$atcSkuIdArr[] = $skuId;
                    	$acidArr[] = $acid;
                    }
				}
				$skuStore = $this->itemSkuStoreModel->where('sku_id in ('.implode(',',$skuIds).')')->select();
				foreach($skuStore as $key=>$value){
                    foreach ($skuList as $k => $val) {
                        if ($val['sku_id'] == $value['sku_id']) {
                            $sku[$value['sku_id']]['remain']=$value['store']-$value['freez'];
                        }
                    }					
				}
				//团购信息
				if (!empty($groupBuyMap)) {
					/*计算团购人数*/
					$map = array(
						'sku_id' => array('in', array_keys($groupBuyMap)),
						'status' => array('in', array(
							'WAIT_SELLER_SEND_GOODS',
							'WAIT_BUYER_CONFIRM_GOODS',
							'WAIT_COMMENT',
							'TRADE_FINISHED'
						))
					);
					$SystradeOrder = M('systrade_order');
					$orderList = $SystradeOrder->field('sku_id,user_id')->where($map)->group('sku_id,user_id')->select();
					foreach ($orderList as $order) {
						$skuId = $order['sku_id'];
						if (isset($groupBuyCount[$skuId])) {
							$groupBuyCount[$skuId]++;
						}else{
							$groupBuyCount[$skuId] = 1;
						}
					}
				}
				
				if (!empty($acidArr)) {
					$map = array(
						'activity_config_id' => array('in', $acidArr)
					);
					$actList = M('company_activity_category')
								->field('activity_config_id,cat_content,start_time,end_time,achieve_num,max_join_num,group_buy_rule,type')
								->where($map)
								->select();
					foreach ($actList as $act) {
						$acid = $act['activity_config_id'];
						$actArr[$acid] = $act;
					}
				}
				if ($this->uid && !empty($atcSkuIdArr)) {
					/*购买数量限制*/
					$map = array(
						'user_id'=> $this->uid,
						'sku_id' => array('in', $atcSkuIdArr),
						'status' => array('in', array(
							'WAIT_SELLER_SEND_GOODS',
							'WAIT_BUYER_CONFIRM_GOODS',
							'WAIT_COMMENT',
							'TRADE_FINISHED'
						))
					);
					$SystradeOrder = M('systrade_order');
					$orderList = $SystradeOrder->field('sku_id,num')->where($map)->select();
					foreach ($orderList as $order) {
						$skuId = $order['sku_id'];
						if (isset($actSkuTimesArr[$skuId])) {
							$actSkuTimesArr[$skuId] += $order['num'];
						} else {
							$actSkuTimesArr[$skuId] = $order['num'];
						}
					}
				}
			}         
			
            //更多精选商品
            //产品利润率
            $conditionRate = '';
            if($this->uid && $this->comId){
                if ($this->comId == '-1') {
                    $profitRate = C("PROFIT_RATE");
                    if (!empty($profitRate)) {
                        $conditionRate =" AND (price-cost_price)/price >=".$profitRate;
                    }
                }else{
                    $profitRate = $this->modelGoodsCat->getCompanyConf('com_id='.$this->comId);
                    if ($profitRate['profit_rate']) {
                        $conditionRate =" AND (price-cost_price)/price >=".$profitRate['profit_rate']/100;
                    }
                }
            }else{
                $profitRate = C("PROFIT_RATE");
                if (!empty($profitRate)) {
                    $conditionRate =" AND (price-cost_price)/price >=".$profitRate;
                }
            }
            $itemList = $this->itemModel->table('sysitem_item a,sysitem_item_status b,sysitem_item_store s')
                        ->where('a.item_id=b.item_id and a.item_id = s.item_id and b.approve_status="onsale" and a.disabled=0 and s.store > 0 and a.cat_id ='.$itemInfo['cat_id'].$conditionRate)
                        ->order('rand()')
                        ->limit(6)
                        ->select();
            
            //地区
            $area = $this->siteAreaModel->where(array('jd_pid' => 0))->select();
            //浏览记录
            $browList = array();
            if (!empty($this->uid)){
                $browList = $this->itemHistoryModel->where('user_id ='.$this->uid)->limit(5)->select();
            }            
            //相关分类
            $catParentInfo = $this->catModel->where('cat_id ='.$itemInfo['cat_id'])->find();
            if (!empty($catParentInfo['parent_id'])) {
                $catList = $this->catModel->where('parent_id ='.$catParentInfo['parent_id'])->select();
            }            
            //店铺信息
            $shopInfo = $this->shopShopModel->where('shop_id ='.$itemInfo['shop_id'])->find();
            
            $itemInfo['newPrice'] = sprintf("%.2f",$itemInfo['price']);//价格保留两位小数
            $itemInfo['integral'] = $itemInfo['newPrice']*100;//积分价格
            $itemInfo['status'] = $itemStatus;
            
            $this->assign('defaultSkuId', $defaultSkuId);
            $this->assign('resCatList',$resCatList);
            $this->assign('resBrandInfo',$resBrandInfo);
            $this->assign('itemInfo',$itemInfo);
            $itemDesc['pc_desc'] = $this->charback($itemDesc['pc_desc']);
            $this->assign('itemDesc',$itemDesc);
            $this->assign('freepostageLimitMoney',$freepostageLimitMoney);
            $this->assign('propsList',$propsList);
            $this->assign('propsListEmpty','<span class="js-sku-s1">请选择</span>');
            $this->assign('propValuesList',$propValuesList);
            $this->assign('newPropsValuesList',$newPropsValuesList);
            $this->assign('sKuList',$sku);
            $this->assign('selectedSku',$selectedSku);
            $this->assign('itemList',$itemList);
            $this->assign('skuPriceArr', $skuPriceArr);
            $this->assign('seckilArr', $seckilArr);
            $this->assign('actSkuTimesArr', $actSkuTimesArr);
            $this->assign('groupBuyItemArr', $groupBuyItemArr);
            $this->assign('actArr', $actArr);
            $this->assign('groupBuyCount', $groupBuyCount);
            $this->assign('area',$area);
            $this->assign('browList',$browList);
            $this->assign('catList',$catList);
            $this->assign('shop_type',C(strtoupper($shopInfo['shop_type'])));
            $this->assign('traderateList',$traderateList);
            $this->assign('isPrice',$isPrice);
            $this->display('info');
        }else{
            $this->error('您访问的商品不存在！');
        }        
    }
    //商品评论
    public function traderateList(){
        $itemId = I("get.itemId");
        $rate = I("get.rateVal");
        if (empty($rate) || $rate == 'rateAll') {
            $rateCond = "t.result <> ''";
        }else{
            $rateCond = "t.result ='".$rate."'";
        }
        //$field = "";
        $condition = 't.oid=o.oid and t.disabled = 1 and t.item_id ='.$itemId." and ".$rateCond;
        $size=10;
        $count = M()->table(array('sysrate_traderate'=>'t','systrade_order'=>'o'))->where($condition)->count();
        //实例化分页类
        $page = new \Think\Page($count,$size);
        $rollPage = 5; //分页栏显示的页数个数；
        $page -> setConfig('first' ,'首页');
        $page -> setConfig('last' ,'尾页');
        $page -> setConfig('prev' ,'上一页');
        $page -> setConfig('next' ,'下一页');
        $start = $page->firstRow;  //起始行数
        $pagesize = $page->listRows;   //每页显示的行数
        $limit = "$start,$pagesize";
        $style = "badge";
        $onclass = "pageon";
        $pagestr = $page -> show($style,$onclass);  //组装分页字符串
        $traderateList = M()->table(array('sysrate_traderate'=>'t','systrade_order'=>'o'))->where($condition)->limit($limit)->select();
        $this->assign('pagestr',$pagestr);
        $this->assign('traderateList',$traderateList);
        $this->display("traderateAjax");
    }
    //商品咨询表
    public function consulList(){
        $itemId = I("get.itemId");
        $consType = I("get.consulVal");
        if (empty($consType) || $consType == 'consulAll') {
            $consCond = "consultation_type <> ''";
        }else{
            $consCond = "consultation_type ='".$consType."'";
        }
        $condition = 'be_reply_id = 0 and item_id ='.$itemId." and ".$consCond;
        $size=10;
        $count = $this->consultationModel->where($condition)->count();
        //实例化分页类
        $page = new \Think\Page($count,$size);
        $rollPage = 5; //分页栏显示的页数个数；
        $page -> setConfig('first' ,'首页');
        $page -> setConfig('last' ,'尾页');
        $page -> setConfig('prev' ,'上一页');
        $page -> setConfig('next' ,'下一页');
        $start = $page->firstRow;  //起始行数
        $pagesize = $page->listRows;   //每页显示的行数
        $limit = "$start,$pagesize";
        $style = "badge";
        $onclass = "pageon";
        $pagestr = $page -> show($style,$onclass);  //组装分页字符串
        $consulList = $this->consultationModel->where($condition)->limit($limit)->select();
        $this->assign('pagestr',$pagestr);
        $this->assign('consulList',$consulList);
        $this->display("consulAjax");
    }
    //提交问题咨询
    public function consulAdd(){
        $data['consultation_type'] = I("post.consulType");
        $data['content'] = I("post.centent");
        $data['contack'] = I("post.mobile");
        $data['author'] = !empty($this->account) ? $this->account : "游客";
        $data['item_id'] = I("post.itemId");
        $data['item_title'] = I("post.itemName");
        $data['shop_id'] = I("post.shopId");
        $data['shop_name'] = I("post.shopName");
        $data['created_time'] = time();
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        if (empty($data['item_id']) || empty($data['shop_id']) || empty($data['consultation_type']) || empty($data['content'])) {
            echo json_encode(array(0,'数据参数不能为空！'));
            exit();
        }
        $result = $this->consultationModel->add($data);
        if ($result) {
            echo json_encode(array(1,'提交成功！'));
            exit();
        }else{
            echo json_encode(array(0,'提交失败！'));
            exit();
        }



    }
    /**
     * 获取地区
     */
    public function ajaxList(){
        // 获取参数
        $pid = I("post.pid");
        if(empty($pid)){
            $pid = 0;
        }
        // 获取地区列表
        $reg_arr = $this->getAreaList($pid);
    
        // 判断是否存在数据 不存在返回null
        if(empty($reg_arr)){
            echo 'null';exit;
        }
    
        // 返回JSON串
        
        echo json_encode($reg_arr);
        exit;
    }
    //获取地区表数据
    public function getAreaList($pid){     
        $area = $this->siteAreaModel->where('jd_pid = '.$pid)->select();
        return $area;
    }


    // 选择不同地区时调用京东接口判断库存 
    public function checkJdStock(){
        $item_id = I('post.item_id','','intval'); // 得到库存的ids
        $jd_ids = I('post.jd_ids','','trim');    
        $where['item_id'] = $item_id;
        $res = $this->itemModel->field('jd_sku')->where($where)->find();
        if($res['jd_sku']>0){       
            $sku[]=array(
                'id'=>$item_id,
                'num'=>1
            );
            $data=array(
                'items'=>json_encode($sku),
                'area'=>trim($jd_ids,'_')
            );
            $result = $this->requestPost(C('API_STORE').'checkCartStock',$data);    
            $retArr = json_decode($result,true);
            $stock=array('status'=>$retArr['data'][$item_id],'msg'=>$retArr['msg']);
        }else{
            $stock=array('status'=>33,'msg'=>'有货');
        }       
        echo $stock['status'];
    }
    
    
    //加入购物车
    public function addItemCart(){        
        if (empty($this->uid)) {
            echo json_encode(array(0,'请先登录！',__APP__."/Sign/index"));
            exit();
        }
        //商品id
        $itemId = I('post.itemId');
        //库存id
        $skuId = I('post.skuId');
        //购买数量
        $quantity = I('post.quantity');
        $shopId = I('post.shopId');
        $jd_ids = I('post.jd_ids');
        if (!$itemId && !$skuId && !$quantity) {
            echo json_encode(array(0,'参数错误！',''));
            exit();
        }
        //团购秒杀购买次数限制
		$this->actLimited($skuId, $quantity);  
        //查询库存详细信息
        $skuInfo = $this->itemSkuStoreModel->where('sku_id ='.$skuId)->find();
        if ($shopId == C('JD_SHOP_ID')) {
            $getUrl = C('COMMON_API')."Jd/checkJdStock/item_id/".$itemId."/jd_ids/".$jd_ids."/num/".$quantity;
            $jdSku = file_get_contents($getUrl);
            $jdSku = trim($jdSku, "\xEF\xBB\xBF");//去除BOM头 
            if ($jdSku != 33) {
                echo json_encode(array(0,'库存不足',''));
                exit();
            }
        }else{
            if ($skuInfo['store']-$skuInfo['freez'] < $quantity) {
                echo json_encode(array(0,'库存不足',''));
                exit();
            }            
        }
        //商品状态
        $itemStatus = $this->itemStatusModel->where('item_id='.$itemId)->find();
        if ($itemStatus['approve_status'] == "instock") {
            echo json_encode(array(0,'商品已下架，无法购买！',''));
            exit();
        }
        //查询购物车是否有该商品，如果有的话就直接增加数量
        $cartInfo = $this->cartModel->where('user_id = '.$this->uid.' and sku_id = '.$skuId)->find();
        if ($shopId == C('JD_SHOP_ID')) {
            $addNum = $cartInfo['quantity'] + $quantity;
            $getUrl = C('COMMON_API')."Jd/checkJdStock/item_id/".$itemId."/jd_ids/".$jd_ids."/num/".$addNum;
            $jdSku = file_get_contents($getUrl); 
            $jdSku = trim($jdSku, "\xEF\xBB\xBF");//去除BOM头 
            if ($jdSku != 33) {
                echo json_encode(array(0,'购物车数量超过库存数量，无法购买！',''));
                exit();
            }
        }else{
            if ($cartInfo['quantity']+$quantity > $skuInfo['store']-$skuInfo['freez']) {
                echo json_encode(array(0,'购物车数量超过库存数量，无法购买！',''));
                exit();
            }            
        }
        if ($cartInfo) {
            $res = $this->cartModel->where('cart_id = '.$cartInfo['cart_id'])->setInc('quantity',$quantity);
            if ($res) {
                echo json_encode(array(1,'加入购物车成功！',''));
            }else{
                echo json_encode(array(0,'加入购物车失败！',''));
            }
        }else{
            //查询商品详细信息
            $itemInfo = $this->itemModel->where('item_id = '.$itemId)->find();
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

            $result = $this->cartModel->data($data)->add();
            if ($result) {
                 echo json_encode(array(1,'加入购物车成功！',''));
            }else{
                echo json_encode(array(0,'加入购物车失败！',''));
            }
        }           
     
    }
    //动态更改购物车数据
    public function updateCartNum(){
    	if($this->uid){
	        $cartCount = $this->cartModel->where('user_id ='.$this->uid)->count();
	        if ($cartCount) {
	            echo $cartCount;
	        }else{
	            echo 0;
	        }
    	}else{
            echo 0;
    	}
    }
    //商品详细信息格式处理
    public function charback($str){
        $str=str_replace(array("&#039;","&quot;","&lt;","&gt;","&amp;reg;","&amp;","&nbsp;",'<p><br />','</p><br />','<br>','\\'),array("'","\"","<",">","&reg;","&"," ",'<p>','</p>','<br />',''),$str);
        $str=preg_replace( '@<script(.*?)</script>@is','&lt;script\1&lt;/script&gt;',$str);
        $str=preg_replace( '@<iframe(.*?)</iframe>@is','',$str);        
        return preg_replace('@<style(.*?)</style>@is', '',$str);
    }

    //商品浏览记录
    public function itemHistory($itemInfo){
        if (empty($this->uid)) {
            header("Location:".__APP__."/login");
        }else{
            $itemHistoryModel = M("sysitem_item_history");
            $data['user_id'] = $this->uid;
            $data['item_id'] = $itemInfo['item_id'];
            $data['cat_id'] = $itemInfo['cat_id'];
            $data['title'] = $itemInfo['title'];
            $data['img'] = $itemInfo['image_default_id'];
            $data['price'] = $itemInfo['price'];
            $data['add_time'] = time();
            if ($data) {
                $historyCount = $itemHistoryModel->where('user_id ='.$this->uid)->count();
                $isHistory = $itemHistoryModel->where('user_id ='.$this->uid.' and item_id ='.$data['item_id'])->count();
                if ($isHistory < 1 ) {
                    if ($historyCount > 9) {
                        $historyInfo = $itemHistoryModel->where('user_id ='.$this->uid)->order('add_time asc')->find();
                        $itemHistoryModel->where('history_id ='.$historyInfo['history_id'])->save($data); 
                    }else{
                        $itemHistoryModel->add($data);
                    }                   
                }

            }
        }
    }

    //收藏商品
    public function favGoods(){
        $itemId = I("post.itemId");
        if (!$this->uid) {
            echo json_encode(array(0,'请先登录！'));
            exit();
        }
        if($this->userFavModel->where("item_id =".$itemId." and user_id=".$this->uid)->find()){
            //已经收藏，取消收藏
            $userFavDel = $this->userFavModel->where('item_id = '.$itemId.' and user_id='.$this->uid)->delete();
            if ($userFavDel) {
                //array(arr1,arr2,arr3);arr1:状态，arr2：提示信息，arr3：显示信息
                echo json_encode(array(1,'取消收藏成功！','收藏商品'));
                exit();
            }else{
                echo json_encode(array(0,'取消收藏失败！','取消收藏'));
                exit();
            }
        }else{
            //商品信息
            $itemInfo = $this->itemModel->where('item_id = '.$itemId)->find();
            if ($itemInfo) {
                $data['item_id'] = $itemInfo['item_id'];
                $data['shop_id'] = $itemInfo['shop_id'];
                $data['user_id'] = $this->uid;
                $data['cat_id'] = $itemInfo['cat_id'];
                $data['goods_name'] = $itemInfo['title'];
                $data['goods_price'] = $itemInfo['price'];
                $data['image_default_id'] = $itemInfo['image_default_id'];
                $data['create_time'] = time();
                $data['object_type'] = 'goods';                
                if ($this->userFavModel->add($data)) {
                    echo json_encode(array(1,'收藏成功！','取消收藏'));
                    exit();
                }else{
                    echo json_encode(array(1,'收藏失败！','收藏商品'));
                    exit();                    
                }
            }
        }
    }

    //收藏店铺
    public function favShop(){
        $shopId = I("post.shopId");
        if (!$this->uid) {
            echo json_encode(array(0,'请先登录！'));
            exit();
        }
        if($this->shopFavModel->where("shop_id =".$shopId." and user_id=".$this->uid)->find()){
            //已经收藏，取消收藏
            $shopFavDel = $this->shopFavModel->where('shop_id = '.$shopId.' and user_id='.$this->uid)->delete();
            if ($shopFavDel) {
                echo json_encode(array(1,'取消收藏成功！','收藏店铺'));
                exit();
            }else{
                echo json_encode(array(0,'取消收藏失败！','取消收藏'));
                exit();
            }
        }else{
            //商品信息
            $shopInfo = $this->shopShopModel->where('shop_id = '.$shopId)->find();
            if ($shopInfo) {
                $data['shop_id'] = $shopId; 
                $data['user_id'] = $this->uid;
                $data['shop_name'] = $shopInfo['shop_name'];
                $data['shop_logo'] = $shopInfo['shop_logo'];
                $data['create_time'] = time();            
                if ($this->shopFavModel->add($data)) {
                    echo json_encode(array(1,'收藏成功！','取消收藏'));
                    exit();
                }else{
                    echo json_encode(array(1,'收藏失败！','收藏店铺'));
                    exit();                    
                }
            }
        }
    }
    
    /**
     * 活动限制
     * @param unknown $skuid
     * @param unknown $quantity
     * @return boolean
     */
    private function actLimited ($skuid, $quantity) {
    	$map = array(
    		'sku_id' => $skuid,
    	);
    	$SysitemSku = M('sysitem_sku');
    	$sku = $SysitemSku->where($map)->field('sku_id,type,activity_config_id')->find();
    	if(empty($sku)){
    		echo json_encode(array(0,'sku错误',''));
    		exit();
    	}
    	$skuType = $sku['type'];
    	$acid = $sku['activity_config_id'];
    	if($skuType != 1 && $skuType != 4){
    		return false;
    	}
    	//检索限制数量
    	$map = array(
    		'activity_config_id' => $acid,
    	);
    	$ActivityCategory = M('company_activity_category');
    	$activity = $ActivityCategory->where($map)->field('activity_config_id,max_join_num')->find();
    	if (empty($activity)) {
    		return false;
    	}
    	
    	$maxNum = $activity['max_join_num'];
    	 
    	if ($maxNum == 0) {
    		return false;
    	}
    	//检索购物车数量
    	$map = array(
    		'user_id' => $this->uid,
    		'sku_id' => $skuid,
    	);
    	$totalNum = $quantity;
    	$cartQuantity = $this->cartModel->where($map)->getField('quantity');
    	$cartQuantity = empty($cartQuantity) ? 0 : $cartQuantity;
    	$totalNum += $cartQuantity;
    	if($totalNum > $maxNum){
    		echo json_encode(array(0,'超过购买数量',''));
    		exit();
    	}
    	$map = array(
    		'user_id' => $this->uid,
    		'activity_id' => $acid,
    		'status' => array('in', array(
    			'WAIT_SELLER_SEND_GOODS',
    			'WAIT_BUYER_CONFIRM_GOODS',
    			'WAIT_COMMENT',
    			'TRADE_FINISHED'
    		))
    	);
    	$SystradeTrade = M('SystradeTrade');
    	$tradeList = $SystradeTrade->where($map)->field('tid,activity_id')->select();
    	if (empty($tradeList)) {
    		return false;
    	}
    	$tidArr = array();
    	foreach ($tradeList as $trade) {
    		$tidArr[] = $trade['tid'];
    	}
    	$map = array(
    		'tid' => array('in', $tidArr),
    		'sku_id' => $skuid,
    	);
    	$SystradeOrder = M('systrade_order');
    	$orderList = $SystradeOrder->where($map)->field('oid,num')->select();
    	
    	foreach ($orderList as $order) {
    		$totalNum += $order['num'];
    	}
    	
    	if( $totalNum > $maxNum){
    		echo json_encode(array(0,'超过购买数量',''));
    		exit();
    	}
    	return true;
    }
}