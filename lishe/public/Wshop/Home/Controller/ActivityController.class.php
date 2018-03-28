<?php
namespace Home\Controller;
class ActivityController extends CommonController {
		public function __construct(){
            parent::__construct();

	}
/**
 * 居家惠生活，限量低价抢实惠
 * @author Zhangrui
 * 
 */
	public function BenefitLife(){
		$facial = array(62012,62011,62019,62018); //面部护理
		$oral   = array(18053,18177,18067,18049,18079,18077,18178,18038); //口腔护理
		$line   = array(18173,18171,18166,18165,18164,18162,18161,18160,18159,18153,18157,51055,51031,51028); //洗护精选
		$beauty = array(48516,48517,63267,63268,63269,46420,53744,48514); //滋润养颜
		$home   = array(53192,53089,53075,53039,52964,62037,53515,8711); //居家精选
		$itemIds = array_merge($facial,$oral,$line,$beauty,$home);
		$map = array(
			'item_id' => array('in', $itemIds)
		);
		$itemInfo = M('sysitem_item')->where($map)->order('price asc')->getField('item_id,title,price,image_default_id,shop_id');
		$ascItemIds = array_keys($itemInfo);
		//过滤下架
		$map = array(
			'item_id' => array('in', $ascItemIds),
			'approve_status' => 'onsale'
		);
		$ascItemIds = M('sysitem_item_status')->where($map)->getField('item_id',TRUE);
		$facial = $this->itemAscSort($facial,$ascItemIds);
		$oral = $this->itemAscSort($oral,$ascItemIds);
		$line = $this->itemAscSort($line,$ascItemIds);
		$beauty = $this->itemAscSort($beauty,$ascItemIds);
		$home = $this->itemAscSort($home,$ascItemIds);
		$itemArr = array(
			'facial' => $facial,
			'oral'   => $oral,
			'line'   => $line,
			'beauty' => $beauty,
			'home'   => $home
			);
		$this->assign('itemArr',$itemArr);
		$this->assign('itemInfo',$itemInfo);
		$this->display('Activity/BenefitLife/index');
	} 
/**
 * 数组按指定另一数组指定排序
 */	
 	private function itemAscSort($arr,$roleArr){
 		$ascArr = array();
		foreach($roleArr as $key=>$val){
			foreach($arr as $keys=>$vals){
				if($vals == $val){
					$ascArr[]=$val;
				}
			}	
		}
		return $ascArr;
 	}
/**
 * 查询商品sku
 */ 
 	public function getItemSku(){
 		$itemId = I('itemId');
		$ret = array('code'=>0,'skuId'=>0);
		if(!is_numeric($itemId)){
			$this->ajaxReturn($ret);
		}
		$map = array(
			'item_id' => $itemId,
			'disable' => 0,
			'parent_sku_id' => 0
		);
		$skuInfo = M('sysitem_sku')->where($map)->getField('sku_id',TRUE);
		if(count($skuInfo) != 1){
			$this->ajaxReturn($ret);
		}
		$ret['code'] = 1;
		$ret['skuId'] = $skuInfo[0];
		$this->ajaxReturn($ret);		
 	}
    //动态更改购物车数据
    public function updateCartNum(){
    	if(!$this->uid){
            echo 0;
    		exit;
    	}
        $cartCount = M('systrade_cart')->where('user_id ='.$this->uid)->count();
        if ($cartCount) {
            echo $cartCount;
        }else{
            echo 0;
        }
    }
	//是否登入
	public function isLogin(){
		if($this->uid){
			echo TRUE;
		}else{
			echo FALSE;
		}
	}	
	//运营活动页计算次数
	public function peopleName(){
		$data = array(
			'ip'=> get_client_ip(),
		);
		M('a_monitor_clicks')->data($data)->add();
		$this->display('Activity/peopleName/peopleName');
	}
/**
 * 端午活动页
 * 
 */
	public function duanwuDisc(){
		//活动
		$activIden = 'duanwuDisc';
		//专题
		$brandIden = 'duanwuBrand';//品质大牌
		$moreIden = 'duanwuMore';	//更多精彩	
		$idenArr = array($brandIden,$moreIden,$activIden);		
		$map = array(
			'Identification' => array('in', $idenArr)
		);			
		$activitys = M('company_activity')->where($map)->getField('aid,type,Identification');
		$brandAid = 0;
		$moreAid = 0;
		$seckillAid = 0;
		$groupAid = 0;
		$combinatAid = 0;
		foreach($activitys as $key=>$val){
			if($brandIden == $val['Identification'] && $val['type'] == 2){
				$brandAid = $key;//品质大牌
			}
			if($moreIden == $val['Identification'] && $val['type'] == 2){
				$moreAid = $key;//更多精彩
			}
			if($activIden == $val['Identification'] && $val['type'] == 1){
				$seckillAid = $key; //秒杀
			}
			if($activIden == $val['Identification'] && $val['type'] == 4){
				$groupAid = $key; //团购
			}
			if($activIden == $val['Identification'] && $val['type'] == 7){
				$combinatAid = $key; //组合购
			}			
		}
		$actiAids = array(
			'brand' => $brandAid,
			'more'  => $moreAid,
			'seckill' => $seckillAid,
			'group'   => $groupAid,
			'combinat' => $combinatAid
		);
		$aids = array_keys($activitys);
		if(empty($aids)){
			exit('无法获取活动...');
		}
		//活动分类
		$map = array(
			'aid' => array('in', $aids)
		);
		$field = 'activity_config_id,aid,start_time,end_time,achieve_num,max_join_num,cat_banner,recommend,type,cat_banner_mobile';
		$activityCats = M('company_activity_category')->where($map)->order('order_sort desc')->getField($field);
		if(empty($activityCats)){
			exit('无法获取分类...');
		}		
		$recommend = array();
		$itemIds = array();
		$actiConfIds = array();
		$catetorys = array();
		foreach($activityCats as $key=>$val){
			$recommend = explode(',', $val['recommend']);
			$activityCats[$key]['recommend'] = $recommend;
			$itemIds = array_merge($itemIds, $recommend);
			$actiConfIds[] = $val['activity_config_id'];
			$catetorys[$val['aid']][] = $val['activity_config_id'];
		}
		//专题商品
		if(!empty($itemIds)){
			$map = array(
				'item_id' => array('in', $itemIds)
			);
			$itemInfos = M('sysitem_item')->where($map)->getField('item_id,title,price,image_default_id');
		}
		//活动商品
		$map = array(
			'activity_config_id' => array('in', $actiConfIds),
			'disable' => 0
		);
		$field = 'aitem_id,activity_config_id,sku_id,item_name,item_info,item_id,price,shop_price,item_img,store,parent_sku_id';
		$aitems = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);
		//商品id 
		$checkItemIds = array();
		foreach($aitems as $key=>$val){
			$checkItemIds[] = $val['item_id'];
		}
		if(!$checkItemIds){
			exit('无商品ID');
		}
		//下架商品
		$map = array(
			'item_id' => array('in', $checkItemIds),
			'approve_status' => 'instock'
		);
		$instockItemIds = M('sysitem_item_status')->where($map)->getField('item_id', TRUE);
		//组合购商品
		$aitemInfos = array();
		$skuIds = array();
		$groupSkuIds = array();
		foreach($aitems as $key=>$val){
			if(!in_array($val['item_id'], $instockItemIds)){
				$aitemInfos[$val['activity_config_id']][] = $val;
			}
			$skuIds[] = $val['sku_id'];
			if(in_array($val['activity_config_id'], $catetorys[$groupAid])){
				//团购sku
				$groupSkuIds[] = $val['sku_id'];
			}
		}
		//查找剩余库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$skuStore = M('sysitem_sku_store')->where($map)->field('sku_id,store,freez')->select();
		foreach($skuStore as $key=>$val){
			$store[$val['sku_id']] = $val['store'] - $val['freez'];
		}
		//团购场次成功的团购次
		$map = array(
			'activity_id' => array('in', $catetorys[$groupAid]),
			'payed_fee' => array('gt', 0),
			'pay_time' => array('gt', 0)
		);
		$tids = M('systrade_trade')->where($map)->getField('tid',TRUE);
		if($tids){
			$map = array(
				'tid' => array('in', $tids),
				'sku_id' => array('in', $groupSkuIds),
			);
			$trade = M('systrade_order')->where($map)->getField('tid,sku_id');
			$groupTrade = array();
			foreach($trade as $key=>$val){
				$groupTrade[$val][] = $key;
			}
		}
		$this->assign('activitys', $activitys);
		$this->assign('actiAids', $actiAids);
		$this->assign('activityCats', $activityCats);
		$this->assign('catetorys', $catetorys);
		$this->assign('itemInfos', $itemInfos);
		$this->assign('aitemInfos', $aitemInfos);
		$this->assign('groupTrade', $groupTrade);
		$this->assign('store',$store);
		$this->display('Activity/duanwuTow/duanwuTowscreen');
	}
/*
 * 组合购加入购物车
 * */
	public function aitemAddCart(){
		$aitemId = I('aitemId');
		$ret = array('code'=>0,'msg'=>'Unknow');
		if(!$this->uid){
			$ret['msg'] = '请先登录';
			$ret['code'] = 3;
			$this->ajaxReturn($ret);
		}
		if(empty($aitemId) || !is_numeric($aitemId)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);
		}
		$map = array(
			'aitem_id' => $aitemId
		);
		$itemInfo = M('company_activity_item')->where($map)->getField('item_info');
		if(empty($itemInfo)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);			
		}
		$info = json_decode($itemInfo, TRUE);
		$skuIds = array();
		foreach($info as $key=>$val){
			$skuIds[] = $val['sku_id'];
		}
		if(empty($skuIds)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);			
		}
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$skuInfo = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
		$itemIds = array();
		foreach($skuInfo as $key=>$val){
			$itemIds[] = $val;
		}
		if(empty($itemIds)){
			$ret['msg'] = '组合购有误.';
			$this->ajaxReturn($ret);			
		}	
		//判断上下架
		$map = array(
			'item_id' => array('in', $itemIds),
			'approve_status' => 'instock'
		);
		$isInstock = M('company_activity_item')->where($map)->getField('item_id');
		if($isInstock){
			$ret['msg'] = '存在商品已下架，无法加入购物车';
			$this->ajaxReturn($ret);				
		}
		//判断库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$minStore = M('sysitem_sku_store')->where($map)->min('store-freez');
		if($minStore <= 0){
			$ret['msg'] = '存在商品无货，无法加入购物车';
			$this->ajaxReturn($ret);				
		}
		
		$cartModel = M('systrade_cart');
		$map = array(
			'user_id' => $this->uid,
			'sku_id'  => array('in', $skuIds)
		);
        $catIds = $cartModel->where($map)->getField('cart_id', TRUE);
		M()->startTrans();
		if($catIds){
			//加数量
			$map = array(
				'cart_id' => array('in', $catIds)
			);
            $res = $cartModel->where($map)->setInc('quantity',1);
			if(!$res){
				M()->rollback();
				$ret['msg'] = '加入购物车失败';
				$this->ajaxReturn($ret);				
			}
		}else{
			//添加购物车
	        //查询商品详细信息
			$map = array(
				'item_id' => array('in', $itemIds)
			);        
	        $itemInfo = M('sysitem_item')->where($map)->getField('item_id,shop_id,title,image_default_id');
			foreach($skuInfo as $key=>$val){
		        $data['user_ident']= md5($this->uid);//会员ident,会员信息和session生成的唯一值
		        $data['user_id'] = $this->uid;//用户id
		        $data['shop_id'] = $itemInfo[$val]['shop_id'];//店铺ID
		        $data['obj_type'] = 'item';//购物车对象类型
		        $data['obj_ident'] = 'item_'.$skuInfo[$val]['sku_id'];//item_商品id
		        $data['item_id'] = $val;//商品id
		        $data['sku_id'] = $key;//sku的id
		        $data['title'] = $itemInfo[$val]['title'];//商品标题
		        $data['image_default_id'] = $itemInfo[$val]['image_default_id'];//商品默认图
		        $data['quantity'] = 1;//数量
		        $data['created_time'] = time();//加入购物车时间
		        $result = $cartModel->data($data)->add();	
				if(!$result){
					M()->rollback();
					$ret['msg'] = '加入购物车失败';
					$this->ajaxReturn($ret);				
				}	
			}			
		}
		M()->commit();
		$ret['code'] = 1;
		$ret['msg'] = '添加成功';
		$this->ajaxReturn($ret);			
		
	}
/**
 * 新人礼遇
 */
	public function newCourtesy(){
		$iden = 'newCourtesy';   //newCourtesy
		$map = array(
			'Identification' => $iden,
			'type' => array('in', array(2,7))
		);
		$aids = M('company_activity')->where($map)->getField('type,aid');
		if(empty($aids)){
			exit('无法获取活动...');
		}
		//活动分类
		$map = array(
			'aid' => array('in', $aids)
		);
		$field = 'activity_config_id,aid,start_time,end_time,cat_banner,cat_banner_mobile,cat_name,item_ids';
		$activityCats = M('company_activity_category')->where($map)->order('order_sort desc')->getField($field);
		if(empty($activityCats)){
			exit('无法获取分类...');
		}		
		$recommend = array();
		$itemIds = array();
		$actiConfIds = array();
		$catetorys = array();
		foreach($activityCats as $key=>$val){
			if($val['aid'] == $aids[7]){
				//组合购
				$actiConfIds[] = $val['activity_config_id'];
				$catetorys[] = $val['activity_config_id'];
			}else if($val['aid'] == $aids[2]){
				$recommend = explode(',', $val['item_ids']);
				$activityCats[$key]['item_ids'] = $recommend;
				$itemIds = array_merge($itemIds, $recommend);				
			}
		}
		if(empty($itemIds)){
			exit('无活动专题');
		}
		//过滤下架
		$map = array(
			'item_id' => array('in', $itemIds),
			'approve_status' => 'onsale'
		);
		$itemIds = M('sysitem_item_status')->where($map)->getField('item_id',TRUE);		
		if(empty($actiConfIds)){
			exit('暂无活动分类');
		}
		//活动商品
		$map = array(
			'activity_config_id' => array('in', $actiConfIds),
			'disable' => 0
		);
		$field = 'aitem_id,activity_config_id,item_name,item_info,price,shop_price,item_img';
		$aitems = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);	
		$skuIds = array();
		foreach($aitems as $key=>$val){
			$item = json_decode($val['item_info'],TRUE);
			foreach($item as $keys=>$vals){
				$skuIds[] = $vals['sku_id'];
				if($vals['price'] < $vals['cost_price']){
					$aitems[$key]['lastPrice'] = $vals['price'];
					$aitems[$key]['lastSkuId'] = $vals['sku_id'];					
				}
			}
		}	
		if(!empty($skuIds)){
			$map = array(
				'sku_id' => array('in', $skuIds)
			);
			$aitemIds = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
			$store = M('sysitem_sku_store')->where($map)->getField('sku_id,store,freez');
			$skuStore = array();
			foreach($store as $key=>$val){
				$skuStore[$key] = $val['store']-$val['freez'];
			}
		}
		//组合购商品
		$aitemInfos = array();
		$skuIds = array();
		$groupSkuIds = array();
		foreach($aitems as $key=>$val){
			$aitemInfos[$val['activity_config_id']][] = $val;
		}		
		$allItemIds = array_merge($itemIds,$aitemIds);
		//商品
		if(!empty($allItemIds)){
			$map = array(
				'item_id' => array('in', $allItemIds)
			);
			$itemInfos = M('sysitem_item')->where($map)->getField('item_id,title,price,image_default_id,shop_id');
		}	
		$this->assign('itemInfos', $itemInfos);
		$this->assign('catetorys', $catetorys);
		$this->assign('aitemIds',$aitemIds);	
		$this->assign('activityCats',$activityCats);	
		$this->assign('itemIds',$itemIds);
		$this->assign('aitemInfos',$aitemInfos);
		$this->assign('skuStore',$skuStore);
		$this->display('Activity/newCourtesy/newPerson');	
	}

	public function pkg() {
		$acid = I('get.acid', -1, 'intval');
		$map = array(
			'aid' => 46, //活动id
			'activity_config_id' => $acid
		);
		$field = 'aitem_id,activity_config_id,item_name,item_info,price,shop_price,item_img';
		$aitems = M('company_activity_item')->where($map)->order('order_sort desc')->getField($field);
		if (empty($aitems)) {
			exit ('→_→ empty');
		}
		//检索商品
		$skuIdArr = array();
		$skuArr = array();
		foreach ($aitems as &$item) {
			$aitem_id = $item['aitem_id'];
			$tmpItemArr = json_decode($item['item_info'], true);
			$item['itemCount'] = count($tmpItemArr);
			$skuArr[$aitem_id] = $tmpItemArr;
			foreach ($tmpItemArr as $sku) {
				$skuIdArr[] = $sku['sku_id'];
			}
		}
		$map = array(
			'sku_id' => array('in', $skuIdArr),
		);
		$skuItemMap = M('sysitem_sku')->where($map)->getField('sku_id,item_id');
		
		$map = array(
				'item_id' => array('in', array_unique($skuItemMap))
		);
		$itemPicMap = M('sysitem_item')->where($map)->getField('item_id,image_default_id');
		
		//查询公司名称
		$comName = '';
		if (is_numeric($this->comId) && $this->comId > 1) {
			$map = array(
					'com_id' => $this->comId,
			);
			$comName = M('company_config')->where($map)->getField('com_name');
		}
		//查询积分
		$deposit = 0;
		if($this->uid) {
			$map = array(
					'user_id' => $this->uid
			);
			$deposit = M('sysuser_user_deposit')->where($map)->getField('deposit');
		}
		$map = array(
				'activity_config_id' => $acid,
		);
		$catContent = M('company_activity_category')->where($map)->getField('cat_content');
		#TODO
		//print_r($skuArr);exit();
		$this->assign('catContent', $catContent);
		$this->assign('comName', $comName);
		$this->assign('deposit', $deposit);
		$this->assign('aitems', $aitems);
		$this->assign('skuArr', $skuArr);
		$this->assign('skuItemMap', $skuItemMap);
		$this->assign('itemPicMap', $itemPicMap);
		$this->assign('STATIC', '/Wshop/Home/View/Activity/midAutumn');
		$this->display('Activity/midAutumn/pkg');
	}
	
	//单品区域
	public function singleArea() {
		$acid = I('get.acid', -1, 'intval');
		
		if (!is_numeric($acid) || $acid < 1) {
			exit('err acid');
		}
		
		if (IS_AJAX) {
			$sortby = I('get.sortby', 1, 'intval'); //1.综合排序 2.销量降序 3.价格降序 4.价格升序 5.上架时间
			$page = I('get.page', 1, 'intval');
			$pageItems = 12;
			
			$map = array(
				'activity_config_id' => $acid,
			);
			$itemIds = M('company_activity_category')->where($map)->getField('item_ids');
			$itemIdArr = explode(',', $itemIds);
			
			if(!is_array($itemIdArr) || empty($itemIdArr)) {
				exit ('→_→ empty');
			}
			
			$SysitemItem = M('sysitem_item')->alias('i');
			$SysitemItem->join('LEFT JOIN sysitem_item_status s ON i.item_id= s.item_id');
			$map = array(
					'i.item_id' => array('in', $itemIdArr),
					's.approve_status' => 'onsale'
			);
			//排序
			if ($sortby == 1) {
				//综合排序
			} else if($sortby == 2) {
				//销量降序
				$SysitemItem->join('LEFT JOIN sysitem_item_count c ON i.item_id= c.item_id');
				$SysitemItem->order('c.sold_quantity DESC,i.item_id DESC');
			} else if($sortby == 3) {
				//价格降序
				$SysitemItem->order('i.price DESC');
			} else if($sortby == 4) {
				//价格升序
				$SysitemItem->order('i.price ASC');
			} else if($sortby == 5) {
				//上架时间
				$SysitemItem->order('s.list_time DESC');
			}
			//价格区间
			$itemList = $SysitemItem
						->field('i.item_id,i.title,i.shop_id,i.price,i.image_default_id')
						->where($map)
						->page($page, $pageItems)
						->select();
			if (empty($itemList)) {
				echo -1;
			}
// 			$itemCount = $SysitemItem
// 						->alias('i')
// 						->join('LEFT JOIN sysitem_item_status s ON i.item_id= s.item_id')
// 						->where($map)
// 						->count('i.item_id');
// 			//计算分页总数
// 			$pageCount = 1;
// 			if (($itemCount % $pageItems) === 0) {
// 				$pageCount = intval($itemCount / $pageItems);
// 			} else {
// 				$pageCount = intval($itemCount / $pageItems) + 1;
// 			}
			
			//查询公司名称
			$comName = '';
			if (is_numeric($this->comId) && $this->comId > 1) {
				$map = array(
						'com_id' => $this->comId,
				);
				$comName = M('company_config')->where($map)->getField('com_name');
			}
			//查询积分
			$deposit = 0;
			if($this->uid) {
				$map = array(
						'user_id' => $this->uid
				);
				$deposit = M('sysuser_user_deposit')->where($map)->getField('deposit');
			}
			$map = array(
				'activity_config_id' => $acid,
			);
			$catContent = M('company_activity_category')->where($map)->getField('cat_content');
			$this->assign('catContent', $catContent);
			$this->assign('comName', $comName);
			$this->assign('deposit', $deposit);
			
			$this->assign('sortby', $sortby);
			$this->assign('page', $page);
// 			$this->assign('pageCount', $pageCount);
// 			$this->assign('itemCount', $itemCount);
			$this->assign('itemList', $itemList);
			$this->display('Activity/midAutumn/singleAjax');
		} else {
			$this->assign('acid', $acid);
			$this->assign('STATIC', '/Wshop/Home/View/Activity/midAutumn');
			$this->display('Activity/midAutumn/single');
		}
	}
	
	public function midAutumn() {
		if (IS_AJAX) {
			$page = I('get.page', 1 , 'intval');
			$pageItems = 12;
			$map = array(
				'activity_config_id' => 98,
			);
			
			$itemIds = M('company_activity_category')->where($map)->getField('item_ids');
			$itemIdArr = explode(',', $itemIds);
				
			if(!is_array($itemIdArr) || empty($itemIdArr)) {
				exit ('→_→ empty');
			}
			$SysitemItem = M('sysitem_item')->alias('i');
			$SysitemItem->join('LEFT JOIN sysitem_item_status s ON i.item_id= s.item_id');
			$map = array(
				'i.item_id' => array('in', $itemIdArr),
				's.approve_status' => 'onsale'
			);
			//价格区间
			$itemList = $SysitemItem
							->field('i.item_id,i.title,i.shop_id,i.price,i.image_default_id')
							->where($map)
							->page($page, $pageItems)
							->select();
			if (empty($itemList)) {
				echo -1;
			}
			$this->assign('itemList', $itemList);
			$this->display('Activity/midAutumn/minAutumnAjax');
		} else {
			$this->assign('STATIC', '/Wshop/Home/View/Activity/midAutumn');
			$this->display('Activity/midAutumn/midAutumn');
		}
	}
}