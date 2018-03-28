<?php
namespace Home\Controller;
use Think\Think;

class SupplierController extends CommonController{
	/*
     * 2016/9/24
     *	王子铖
     *
     * */
	public $supplier_user;
	public $supplier_sku;
	public $sss;
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->supplier_user=M('supplier_user');
		$this->supplier_goods=M('supplier_goods');
		$this->supplier_sku=M("supplier_item_sku");
		$this->supplier_item=M('supplier_item');
		$this->sysitem_item=M('sysitem_item');
		$this->syscategory_brand=M('syscategory_brand');
		$this->category=M('syscategory_cat');
		$this->supplier=D('Supplier');
		$this->storage=D('SupplierStorage');
		$this->apply = M('Supplier_apply');
	}
	/*
	 * 首页
	 *
	 */
	public function index(){
		$this->display();
	}

	public function syncCategory(){
		$list=$this->sysitem_item->select();
		$tmp=$this->category->field("cat_id,parent_id,cat_name")->select();
		$cats=array();

		foreach($tmp as $key => $val){
			$cats[$val['cat_id']]["parent_id"]=$val['parent_id'];
			$cats[$val['cat_id']]["cat_name"]=$val['cat_name'];
		}

		foreach($list as $key => $val){
			$cat_3_id=$val['cat_id'];
			$cat_3_name=$cats[$cat_3_id];
			$cat_2_id=$this->getCategory($cat_3_id,$cats);
			$cat_1_id=$this->getCategory($cat_2_id['cat_id'],$cats);
			$this->updateCategory($val['item_id'],$cat_1_id['cat_name'],$cat_2_id['cat_name'],$cat_3_name['cat_name']);

		}
	}
	
	public function getCategory($cat_id,$cats){
		$pid=$cats[$cat_id]['parent_id'];
		$cat_name=$cats[$pid]['cat_name'];
		$arr=array(
			'cat_id'=>$pid,
			'cat_name'=>$cat_name
		);
		return $arr;
	}
	
	public function updateCategory($id,$cat_1,$cat_2,$cat_3){
		$data=array(
			'cat_id_2'=>$cat_2,
			'cat_id_1'=>$cat_1,
			'cat_id_3'=>$cat_3
		);
		if(empty($id)){
			return ;
		}
		$res=$this->sysitem_item->where("item_id=$id")->save($data);
		echo $this->sysitem_item->getLastSql();
		if($res<1){
			echo $id."错误！";
		}else{
			echo "成功";
		}
	}
	//商品列表
	public function goodsListView(){
		$page 	= I('get.p', 1);
		$like 	= I('get.like');
		$id 	= I('get.id');
		$supplierId = I('get.supplierId',-1,'intval');
		$sendType = I('get.sendType',-1,'intval');
		$isReviewed = I('isReviewed',-1,'intval');
		$where=array();
		if(!empty($like)){
			$where['title']=array('like',"%$like%");
		}
		if(!empty($id)){
			$where['id']=$id;
		}
		if(is_numeric($supplierId) && $supplierId > 0){
			$where["supplier_id"] = $supplierId;
		}
		if($sendType == 1 || $sendType == 2){
			$where["send_type"] = $sendType;
		}
		if ($isReviewed != -1) {
			$where["is_reviewed"] = $isReviewed;
		}
		
		$where["status"] = array('neq',-1); //这段代码不能注释，在商品列表里只能查询到正常的商品
		$where['_string'] = "quote_status = 2 AND (is_reviewed = 0 OR is_reviewed = 1)";
		$count=$this->supplier_item->where($where)->count('sitem_id');
		$list=$this->supplier_item->where($where)->order("sitem_id desc")->page($page.',20')->select();
		if(!empty($list)){
			$supplierUser=$this->supplier->getFieldByGoods($list,'supplier_id',$this->supplier_user);
			$brandList=$this->supplier->getFieldByGoods($list,'brand_id',$this->syscategory_brand);
			$catList=$this->supplier->getFieldByGoods($list,'cat_id',$this->category);
			$skuNumList=$this->supplier->getSkuNum($list);
			$this->assign('supplier_user',$supplierUser);
			$this->assign('brand_list',$brandList);
			$this->assign('cat_list',$catList);
			$this->assign('skuNum',$skuNumList);
		}
		
		//加载供应商
		$supplierList=$this->supplier->getAllSupplierList();
		//print_r($supplierList);
		$page=new \Think\Page($count,20);
		$this->assign('page',$page->show());
		$this->assign('supplierId',$supplierId);
		$this->assign('sendType',$sendType);
		$this->assign('isReviewed',$isReviewed);
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('supplierList',$supplierList);
		$this->display('goodsList');
	}
	
	public function goodsRecycle(){
		$page 	= I('get.p', 1);
		$like 	= I('get.like');
		$id 	= I('get.id');
		$where=array();
		if(!empty($like)){
			$where['title']=array('like',"%$like%");
		}
		if(!empty($id)){
			$where['id']=$id;
		}
		$where["status"] = -1;
		$count=$this->supplier_item->where($where)->count();
		$list=$this->supplier_item->where($where)->order("sitem_id desc")->page($page.',20')->select();
		$supplierUser=$this->supplier->getFieldByGoods($list,'supplier_id',$this->supplier_user);
		$brandList=$this->supplier->getFieldByGoods($list,'brand_id',$this->syscategory_brand);
		$catList=$this->supplier->getFieldByGoods($list,'cat_id',$this->category);
		$page=new \Think\Page($count,20);
		$this->assign('page',$page->show());
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('supplier_user',$supplierUser);
		$this->assign('brand_list',$brandList);
		$this->assign('cat_list',$catList);
		$this->display('goodsRecycle');
	}
	
	//修改商品状态
	public function setGoodsStatus(){
		$sitemId = I('post.sitem_id',-1,'intval');
		$status = I('post.status',0,'intval');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		if(!is_numeric($sitemId) || $sitemId < 1){
			$ret['msg'] = 'invalid sitem_id';
			$this->ajaxReturn($ret);
		}
		if($status != 1 && $status != -1){
			$ret['msg'] = 'invalid status';
			$this->ajaxReturn($ret);
		}
		$where['sitem_id'] = $sitemId;
		$result = $this->supplier_item->where($where)->setField('status',$status);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = success;
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}
	//商品详情
	public function goodsDetail(){
		$id=I("get.sitem_id");
		if(empty($id)){
			exit;
		}
		$item=$this->supplier->getDetail($id,'sitem_id','supplier_item');
		//商品图片
        if (!empty($item['list_image'])) {
            $newItemImage = explode(',',$item['list_image']);
            $item['new_list_images'] = $newItemImage;            
        }
		$item['itemDesc'] = $this->charback($this->supplier->getDetail($id,'sitem_id','supplier_item_desc'));
		$supplier_user=$this->supplier->getDetail($item['supplier_id'],'supplier_id','supplier_user');
		$shopList=$this->supplier->getAllList("sysshop_shop","shop_id,shop_name");
		//$condition = "sitem_id = $id and (quote_status = 3 or quote_status = 4 )";
		$condition = "sitem_id = $id AND status != -1";
		$sku=$this->supplier->getSkuList($condition);

		$shop=$this->supplier->getDetail($item['shop_id'],'shop_id','sysshop_shop');
		$cat=$this->supplier->getDetail($item['cat_id'],'cat_id','syscategory_cat');
		$brand=$this->supplier->getDetail($item['brand_id'],'brand_id','syscategory_brand');
		$users=$this->supplier->getAllSupplierList();
		$this->assign('shop',$shop);
		$this->assign('shopList',$shopList);
		$this->assign('cat',$cat);
		$this->assign('brand',$brand);
		$this->assign('supplier_user',$supplier_user);
		$this->assign('item',$item);
		$this->assign('sku',$sku);
		$this->assign("users",$users);
		$this->display("goodsDetail");
	}
	public function charback($str){
        $str=str_replace(array("&#039;","&quot;","&lt;","&gt;","&amp;reg;","&amp;","&nbsp;",'<p><br />','</p><br />','<br>','\\'),array("'","\"","<",">","&reg;","&"," ",'<p>','</p>','<br />',''),$str);
        $str=preg_replace( '@<script(.*?)</script>@is','&lt;script\1&lt;/script&gt;',$str);
        $str=preg_replace( '@<iframe(.*?)</iframe>@is','',$str);        
        return preg_replace('@<style(.*?)</style>@is', '',$str);
    }
    
    //保存商品详情
    public function saveItemDesc(){
    	$sitemid = I('post.sitemid','', 'intval'); 
    	$desc = I('post.desc',''); //不过滤了
    	$ret = array('code' => -1, 'msg' => 'unkown error');
    	if(!is_numeric($sitemid) || $sitemid < 1){
    		$ret['msg'] = '商品id有误';
    		$this->ajaxReturn($ret);
    	}
    	$map = array(
    		'sitem_id' => $sitemid,
    	);
    	$SupplierItemDesc = M('supplier_item_desc');
    	$result = $SupplierItemDesc->where($map)->getField('sitem_id');
    	if(empty($result)){
    		$data = array(
    			'sitem_id' => $sitemid,
    			'pc_desc' =>$desc,
    		);
    		$result = $SupplierItemDesc->add($data);
    	}else{
    		$result = $SupplierItemDesc->where($map)->setField('pc_desc', $desc);
    	}
		if(!is_numeric($result)){
			$ret['msg'] = '保存失败';
			$this->ajaxReturn($ret);
		}
		
		$ret['code'] = 1;
		$ret['msg'] = 'success';
		$this->ajaxReturn($ret);
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
    
	//获取分类
	public function getCat(){
		$pid=I("get.cat_id");
		$where=array(
			"parent_id"=>$pid
		);
		$list=$this->supplier->getAllList("syscategory_cat","cat_id,cat_name,parent_id",$where);
		echo $this->ajaxReturn($list);
	}
	//添加商品页面
	public function addGoodsView(){
		$users=$this->supplier->getAllSupplierList();
		$this->assign('users',$users);
		$brands=$this->supplier->getAllList("syscategory_brand","brand_id,brand_name");
		$cat_1=$this->supplier->getAllList('syscategory_cat',"cat_id,cat_name","level=1");
		$this->assign('brands',$brands);
		$this->assign("cat_1",$cat_1);
		$this->display('addGoods');
	}
	//添加商品接口
	public function addGoods(){
		$data=I('post.');
		$data['input_tax']=is_numeric($data['input_tax'])?$data['input_tax']:0.17;
		$data['output_tax']=is_numeric($data['output_tax'])?$data['output_tax']:0.17;
		$data['cost_price']=is_numeric($data['cost_price'])?$data['cost_price']:0.00;
		$data['mkt_price']=is_numeric($data['mkt_price'])?$data['mkt_price']:0.00;
		$data['jd_price']=is_numeric($data['jd_price'])?$data['jd_price']:0.00;
		$data['profit_rate']=is_numeric($data['profit_rate'])?$data['profit_rate']:0.00;
		$data['weight']=is_numeric($data['weight'])?$data['weight']:0.00;
		$data['length']=is_numeric($data['length'])?$data['length']:0.00;
		$data['height']=is_numeric($data['height'])?$data['height']:0.00;
		$data['width']=is_numeric($data['width'])?$data['width']:0.00;
		$goods=array(
			'supplier_id'=>$data['supplier_id'],
			'bn'=>$data['supplier_id'],
			'barcode'=>$data['barcode'],
			'brand_id'=>$data['brand_id'],
			'input_tax'=>$data['input_tax'],
			'output_tax'=>$data['output_tax'],
			'unit'=>$data['unit'],
			'title'=>$data['title'],
			'delivery_period'=>$data['delivery_period'],
			'shelf_life'=>$data['shelf_life'],
			'origin'=>$data['origin'],
			'case_num'=>$data['case_num'],
			'cooperation_method'=>$data['cooperation_method'],
			'settlement_method'=>$data['settlement_method'],
			'freight'=>$data['freight']
		);
		
		$id=$this->supplier_goods->add($goods);

		if($id>1){
			$item=array(
				'supplier_id'=>$data['supplier_id'],
				'bn'=>$data['supplier_id'],
				'barcode'=>$data['barcode'],
				'brand_id'=>$data['brand_id'],
				'cat_id_1'=>$data['cat_id_1'],
				'cat_id_2'=>$data['cat_id_2'],
				'cat_id'=>$data['cat_id'],
				'title'=>$data['title'],
				'delivery_period'=>$data['delivery_period'],
				'send_type'=>$data['send_type'],
				'cost_price'=>$data['cost_price'],
				'mkt_price'=>$data['mkt_price'],
				'jd_price'=>$data['jd_price'],
				'profit_rate'=>$data['profit_rate'],
				'weight'=>$data['weight'],
				'length'=>$data['length'],
				'height'=>$data['height'],
				'width'=>$data['width'],
				'shop_id'=>$id
			);
			if($this->supplier_item->add($item)){
				$this->success("添加成功,请继续添加SKU，否则该商品为非正常商品",U('Supplier/addSkuView'));
			}else{
				$this->error("添加失败");
			}
		}else{
				$this->error("添加失败");
		}
	}
	//获取单个字段值
	public function getGoodsInfo(){
		$where=array(
			"sitem_id"=>I("post.sitem_id")
		);
		$res=$this->getTable(I("post.fields_name"));
		$arr=array();

		if($res==="1"){
			$arr=$this->supplier_item->where($where)->field(I("post.fields_name"))->find();
		}else{
			$m=M($res);
			$arr=$m->where($where)->field(I("post.fields_name"))->find();
		}
		$arr=reset($arr);
		echo $arr;
	}
	//获取字段是哪张表的
	public function getTable($field){
		$goods=array(
			'supplier_id'=>1,
			'bn'=>1,
			'barcode'=>1,
			'brand_id'=>1,
			'input_tax'=>1,
			'output_tax'=>1,
			'unit'=>1,
			'title'=>1,
			'shelf_life'=>1,
			'origin'=>1,
			'case_num'=>1,
			'delivery_period'=>1,
			'cooperation_method'=>1,
			'settlement_method'=>1,
			'freight'=>1
		);
		$item=array(
			'supplier_id'=>1,
			'bn'=>1,
			'barcode'=>1,
			'brand_id'=>1,
			'cat_id_1'=>1,
			'cat_id_2'=>1,
			'cat_id'=>1,
			'title'=>1,
			'send_type'=>1,
			'cost_price'=>1,
			'mkt_price'=>1,
			'jd_price'=>1,
			'profit_rate'=>1,
			'weight'=>1,
			'height'=>1,
			'length'=>1,
			'width'=>1,
			'delivery_period'=>1,
			'shop_id'=>1
		);
		$arr=array_intersect_assoc($goods,$item);
		if($arr[$field]){
			return "1";
		}
		if($goods[$field]){
			return "supplier_goods";
		}
		if($item[$field]){
			return "supplier_item";
		}
		return false;
	}
	//修改商品页面
	public function modifyGoods(){
		$id=I("post.sitem_id");
		$field=I("post.fields_name");
		$data[$field]=I("post.content");
		$res=$this->getTable($field);
		if(!$res){
			return false;
		}
		if($res==="1"){
			if($this->supplier_item->where("sitem_id=$id")->save($data)){
				if($this->supplier_goods->where("sitem_id=$id")->save($data)){
					echo '1';
					exit;
				}
				echo '1';
			}
			exit;
		}else{
			$m=M($res);
			if($m->where("sitem_id=$id")->save($data)){
				echo '1';
				exit;
			}
			exit;
		}
	}
	//增加SKU
	public function addSkuView(){
		$id=I("get.sitem_id");
		$cat_id=I("get.cat_id");
		if(!empty($cat_id)){
			$props=$this->supplier->getSkuProp($cat_id);
			//var_dump($props);
			$vals=$this->supplier->getSkuPropVal($props);
			$this->assign("props",$props);
			$this->assign("vals",$vals);
		}
		$this->assign("sitem_id",$id);
		$this->display("addSKU");
	}
	public function getSupplierList(){
	}
	public function pushGoods(){
		$id=(int)I("post.sitem_id");
		$shopId=(int)I("post.shop_id");
		$approveStatus=I("post.approve_status");//上下架
		$keywords = I('keywords');		
		$warmReminder = I('warm_reminder');
		$pushSf = I('push_sf');//是否推送顺丰
		$shopCat = I('shop_cat',0);//店铺分类
		if(empty($shopId)){
			$this->error("商铺ID为空");
		}
		if(empty($id)){
			$this->error("ID为空");
		}
		if (empty($shopCat)) {
			$shopCat = I('shopCat');
			if (empty($shopCat)) {
				$this->error("请选择店铺分类");
			}			
		}
		$condition = array('sitem_id'=>$id);
		$field = array('send_type','item_id');
		$sitemInfo = $this->supplier->getSitemInfo($condition,$field);
		if (!$sitemInfo) {
			$this->error("未查询到商品信息！");
		}
		//查询sku中价格最低的sku
		unset($condition);
		unset($field);
		$condition['sitem_id'] = $id;
		$condition['_string'] = " (quote_status = 3 OR quote_status = 4) ";
		$field = "*";
		$order = "price ASC";
		$sitemSkuList = $this->supplier->getItemSkuList($condition,$field,$order);
		if (empty($sitemInfo['item_id'])) {
			if (!$sitemSkuList) {
				$this->error("未查询到SKU信息！");
			}
			if (!$sitemSkuList[0]['price']) {
				$this->error("未查询到商品价格信息！");
			}
		}				
		//查询供应商item表信息
		$condition = array('sitem_id'=>$id,'quote_status' => 2);
		$itemInfo = $this->supplier->getItemInfo($condition);
		if (!$itemInfo) {
			$this->error("未查询到供应商商品信息！");
		}
		//商城item表数据
		$itemInfo['shop_id']=$shopId;
		$itemInfo['shop_cat_id']=$shopCat;
		$itemInfo['modified_time']=time();
		if ($sitemSkuList[0]['price']) {
			$itemInfo['price']=$sitemSkuList[0]['price'];
		}		
		$itemInfo['keywords'] = $keywords;
		$itemInfo['warm_reminder'] = $warmReminder;
		//查询供应商商品详情
		unset($condition);
		$condition = array('sitem_id'=>$id);
		$sitemDescInfo = $this->supplier->getItemDescInfo($condition);	
		if ($sitemDescInfo['pc_desc']) {
			$sitemDescInfo['pc_desc'] = htmlspecialchars_decode($sitemDescInfo['pc_desc']);
		}
		//判断该记录是否已经推送到商城（未推送：推送商品到商城，已推送：修改推送的商品）
		if ($itemInfo['item_id']) {	
			//商城存在该商品
			unset($itemInfo['cost_price']);	
			unset($itemInfo['price']);	
			unset($itemInfo['mkt_price']);
			try{	
				unset($condition);
				$condition = array('item_id'=>$itemInfo['item_id']);
				$itemRes = $this->supplier->updateItem($condition,$itemInfo);						
				if ($itemRes === false) {
					$this->error("商城商品表修改失败！");	
				}
			}catch(\Exception $e){ 
				$this->makeLog('pushGoods',"error(1001) msg:商城商品表修改失败 sitemId:$id".$e->getMessage());
			}
			//推送商品详情
			try {
				unset($condition);
				unset($data);
				$condition = array('item_id'=>$itemInfo['item_id']);
				$data['item_id'] = $itemInfo['item_id'];
				$data['pc_desc'] = htmlspecialchars_decode($sitemDescInfo['pc_desc']);
				$resItemDesc = $this->supplier->updateItemDesc($condition,$data);
				if ($resItemDesc === false) {
					$this->makeLog('pushGoods',"error(1002) msg:商城商品详情表修改失败 sitemId:$id");
				}
			} catch (\Exception $e) {
				$this->makeLog('pushGoods',"error(1003) msg:商城商品详情表修改失败 sitemId:$id".$e->getMessage());
			}
			//修改商品状态表（上下架）
			try {
				unset($data);
				unset($condition);
				$condition = array('item_id'=>$itemInfo['item_id']);
				$data=array(
					'shop_id'=>$shopId,
					'is_force'=>1,
					'approve_status'=>$approveStatus
				);
				if ($approveStatus == 'onsale') {					
					$data['list_time'] = time();
				}else{
					$data['delist_time'] = time();
				}
				$updateItemStatus = $this->supplier->updateItemStatus($condition,$data);
				if ($updateItemStatus === false) {
					$this->makeLog('pushGoods',"error(1004) msg:修改商品状态表（上下架）失败sitemId:$id");
				}		
			} catch (\Exception $e) {				
				$this->makeLog('pushGoods',"error(1005) msg:修改商品状态表（上下架）失败sitemId:$id".$e->getMessage());
			}			
		}else{
			//商城不存在该商品
			try {
				$itemInfo['point'] = $itemInfo['price'] * 100;
				$item_id = $this->supplier->addItem($itemInfo);
				if(empty($item_id)){
					$this->error("商城商品表写入失败！");					
				}
			} catch (\Exception $e) {
				$this->makeLog('pushGoods',"error(1006) msg:商城商品表写入失败 ".$e->getMessage());
			}			
			//修改供应商商品表item_id
			try {
				unset($condition);
				unset($data);
				$condition = array('sitem_id'=>$id);
				$data = array('item_id'=>$item_id,'is_push'=>1);
				$sitemRes = $this->supplier->updateSitem($condition,$data);
				if ($sitemRes === false) {
					$this->makeLog('pushGoods',"error(1007) msg:供应商商品表修改失败sitemId:$id");
				}
			} catch (\Exception $e) {
				$this->makeLog('pushGoods',"error(1008) msg:供应商商品表修改失败 ".$e->getMessage());
			}			
			//推送商品详情
			try {
				unset($data);
				$data['item_id'] = $item_id;
				$data['pc_desc'] = htmlspecialchars_decode($sitemDescInfo['pc_desc']);
				$resItemDesc = $this->supplier->addItemDesc($data);
				if (!$resItemDesc) {
					$this->makeLog('pushGoods',"error(1009) msg:商城商品详情表添加失败 sitemId:$id");
				}
			} catch (\Exception $e) {
				$this->makeLog('pushGoods',"error(1010) msg:商城商品详情表添加失败 sitemId:$id".$e->getMessage());
			}
			//插入商品销量表
			try {
				unset($data);
				$data = array('item_id'=>$item_id);
				$addItemQuantity = $this->supplier->addItemQuantity($data);
				if (!$addItemQuantity) {
					$this->makeLog('pushGoods',"error(1011) msg:插入商品销量表失败sitemId:$id");
				}
			} catch (\Exception $e) {
				$this->makeLog('pushGoods',"error(1012) msg:插入商品销量表失败sitemId:$id".$e->getMessage());
			}
			//插入商品状态表（上下架）
			try {
				unset($data);
				$data=array(
					'item_id'=>$item_id,
					'shop_id'=>$shopId,
					'is_force'=>1,
					'approve_status'=>$approveStatus
				);
				if ($approveStatus == 'onsale') {					
					$data['list_time'] = time();
				}else{
					$data['delist_time'] = time();
				}
				$addItemStatus = $this->supplier->addItemStatus($data);
				if (!$addItemStatus) {
					$this->makeLog('pushGoods',"error(1013) msg:插入商品状态表（上下架）失败sitemId:$id");
				}		
			} catch (\Exception $e) {				
				$this->makeLog('pushGoods',"error(1014) msg:插入商品状态表（上下架）失败sitemId:$id".$e->getMessage());
			}
			
		}
		//供应商SKU信息推送到商城
		if ($sitemSkuList) {
			$itemStore = 0;
			foreach ($sitemSkuList as $key => $value) {
				$itemStore += $value['stock'];
				//item_id为空为新增sku，则插入到商城表中
				if (empty($value['sku_id'])) {
					$value['item_id'] = empty($item_id) ? $itemInfo['item_id'] : $item_id;
					$value['title'] = $itemInfo['title'];
					$value['point'] = $value['price'] * 100;
					try {
						$resItemSkuAdd = $this->supplier->addItemSku($value);
						if (!$resItemSkuAdd) {
							$this->makeLog('pushGoods',"error(1015) msg:商城sku表插入失败sitemId:$id");
						}
						//返回数据后，修改供应商sku表中的sku_id字段
						unset($condition);
						unset($data);
						$condition = array('ssku_id'=>$value['ssku_id']);
						$data = array('sku_id'=>$resItemSkuAdd,'quote_status'=>4);
						$data['item_id'] = empty($item_id) ? $itemInfo['item_id'] : $item_id;
						try {
							$resSitemSku = $this->supplier->updateSitemSku($condition,$data);
							if ($resSitemSku === false) {
								$this->makeLog('pushGoods',"error(1016) msg:修改返回sku_id到供应商sku表失败sitemId:$id");
							}
						} catch (\Exception $e) {
							$this->makeLog('pushGoods',"error(1017) msg:修改返回sku_id到供应商sku表失败sitemId:$id".$e->getMessage());
						}
					} catch (\Exception $e) {
						$this->makeLog('pushGoods',"error(1018) msg:商城sku表插入失败sitemId:$id".$e->getMessage());
					}
					//插入商品SKU库存表
					unset($data);
					$data['item_id'] = empty($item_id) ? $itemInfo['item_id'] : $item_id;
					$data['sku_id'] = $resItemSkuAdd;			
					if ($sitemInfo['send_type'] == 2) {
						$data['store'] = $value['stock'];
					}else{
						$data['store'] = 0;
					}				
					try {
						$addItemSkuStore = $this->supplier->addItemSkuStore($data);
						if (!$addItemSkuStore) {
							$this->makeLog('pushGoods',"error(1019) msg:插入商品SKU库存表失败sitemId:$id");
						}
					} catch (\Exception $e) {
						$this->makeLog('pushGoods',"error(1020) msg:插入商品SKU库存表失败sitemId:$id".$e->getMessage());
					}

				}else{//存在item_id,更新商城表sku信息
					//修改商城sku信息
					try {
						unset($condition);
						unset($value['item_id']);
						$condition = array('sku_id'=>$value['sku_id']);
						$value['title'] = $itemInfo['title'];
						$value['point'] = $value['price'] * 100;
						$value['cash'] = 0;
						$resItemSkuUpdate = $this->supplier->updateItemSku($condition,$value);
						if ($resItemSkuUpdate === false) {
							$this->makeLog('pushGoods',"error(1021) msg:修改商城sku表失败sitemId:$id");
						}
					} catch (\Exception $e) {
						$this->makeLog('pushGoods',"error(1022) msg:修改商城sku表失败sitemId:$id".$e->getMessage());
					}
					//修改商城库存信息
					try {
						unset($condition);
						unset($data);
						$condition = array('sku_id'=>$value['sku_id']);
						if ($sitemInfo['send_type'] == 2) {
							$data['store'] = $value['stock'];
						}else{
							$data['store'] = 0;
						}	
						$resItemSkuStore = $this->supplier->updateItemSkuStore($condition,$data);
						if ($resItemSkuStore === false) {
							$this->makeLog('pushGoods',"error(1023) msg:修改商城skuStore表失败sitemId:$id");
						}
					} catch (\Exception $e) {
						$this->makeLog('pushGoods',"error(1024) msg:修改商城skuStore表失败sitemId:$id".$e->getMessage());
					}
				}
			}
		}
		if (!$itemInfo['item_id']) {
			//不存在
			//插入商品库存表
			try {
				unset($data);
				$data['item_id'] = $item_id;
				if ($sitemInfo['send_type'] == 2) {
					$data['store'] = $itemStore;
				}else{
					$data['store'] = 0;
				}
				$addItemStore = $this->supplier->addItemStore($data);
				if (!$addItemStore) {
					$this->makeLog('pushGoods',"error(1025) msg:插入商品库存表失败sitemId:$id");
				}
			} catch (\Exception $e) {				
				$this->makeLog('pushGoods',"error(1026) msg:插入商品库存表失败sitemId:$id".$e->getMessage());
			}
		}else{
			//存在
			//修改商品库存
			try {
				unset($condition);
				unset($data);
			 	$condition['item_id'] = $itemInfo['item_id'];
				if ($sitemInfo['send_type'] == 2) {
					$data['store'] = $itemStore;
				}else{
					$data['store'] = 0;
				}
				$addItemStore = $this->supplier->UpdateItemStore($condition,$data);
				if ($addItemStore === false) {
					$this->makeLog('pushGoods',"error(1027) msg:修改商品库存表失败sitemId:$id");
				}
			} catch (\Exception $e) {				
				$this->makeLog('pushGoods',"error(1028) msg:修改商品库存表失败sitemId:$id".$e->getMessage());
			}
		}
		//1.推送商品到sysitem_item表
		//2.推送sku到sysitem_sku表
		//3.加入到sysitem_item_count表
		//4.加入到sysitem_item_status表
		//5.sysitem_item_store表
		//6.sysitem_sku_store表
		//修改完之后，去把supplier_item表修改一下
		//7.推送到顺丰仓库
		if (!empty($pushSf)) {		
			$item_id = empty($item_id) ? $itemInfo['item_id'] : $item_id;
			$skus=$this->supplier->getSkus("sku_id as skuNo,title as itemName,spec_info as standardDescription,barcode as barcode1",$item_id);
			if($this->supplier->pushGoods($skus)){
				$this->supplier->setPushed($item_id);
				$this->success("推送成功！");
			}else{
				$this->error("推送顺丰失败！");
			}
		}else{
			$this->success("推送商城成功！");
		}
	}
	public function pushSkus(){
		$ids=I('get.ids');
		if(empty($ids)){
			die('不能为空');
		}
		$skus=$this->supplier->getSkus("sku_id as skuNo,title as itemName,spec_info as standardDescription,barcode as barcode1",$ids);
		if($this->supplier->pushGoods($skus)){
			$this->success("推送成功");
		}else{
			$this->error("推送顺丰失败");
		}
	}

	public function pushGoodsSet(){
		$list=$this->supplier->getShopList();
		if (empty($list)) {
			$this->error("获取店铺列表失败！");
		}
		foreach ($list as $key => $value) {
			if ($value['shop_id'] == 10) {
				unset($list[$key]);
			}
		}
		$sitemId=I("get.sitem_id");
		$itemId=I("get.item_id");
		$sendType = I("get.send_type");
		$this->assign("sitem_id",$sitemId);
		$this->assign("item_id",$itemId);
		$this->assign("sendType",$sendType);
		$this->assign("list",$list);
		$this->display("pushGoods");
	}

	//获取店铺分类
	public function getShopCatList(){
		$shopId = I('shopId');
		$shopCatId = I('shopCatId'); 
		if (empty($shopId)) {
			echo 0;
		}
		if (!empty($shopId)) {
			$condition =array('shop_id'=>$shopId,'parent_id'=>0);
		}
		if (!empty($shopCatId)) {
			$condition =array('parent_id'=>$shopCatId);
		}		
		$shopCatList = $this->supplier->getShopCatList($condition);
		$catHtml.="<option value='0' selected>--请选择--</option>";
       	if($shopCatList){
           	foreach($shopCatList as $ked => $value){
             	$catHtml.= "<option value='".$value['cat_id']."' data-value='".$value['cat_name']."'>".$value['cat_name']."</option>";
           	}
           	echo $catHtml;
       	}else{
           	echo 2;
       	}
	}


	public function addSku(){
		$val=I("post.");
		$res=$this->supplier->addSku($val);
		if($res){
			$this->success("添加成功",U("Supplier/goodsListView"));
		}else{
			$this->error("添加失败");
		}
	}
	//编辑sku
	public function editSku(){
		$id=I('post.ssku_id');
		$field_name=I('post.fields_name');
		$content=I('post.content');
		$data=array(
			$field_name=>$content
		);
		$res=$this->supplier_sku->where("ssku_id=$id")->save($data);
		if($res){
			echo '1';
		}else{
			echo '2';
		}
	}
	//获取sku详情
	public function getSkuInfo(){
		$where=array(
			"ssku_id"=>I("post.ssku_id")
		);
		$id=I("post.ssku_id");
		$field=I("post.fields_name");
		if(empty($id)){
			echo '';
			exit;
		}
		$arr=array();
		$arr=$this->supplier_sku->where($where)->field($field)->find();
		$arr=reset($arr);
		echo $arr;
	}
	//删除sku
	public function delSku(){
		$id=I("post.ssku_id");
		if(empty($id)){
			echo '2';
			exit;
		}
		$status=I("post.status");
		if(empty($status)){
			$status="-1";
		}
		$data=array(
				"status"=>$status
		);


		if($this->supplier_sku->where("ssku_id=$id")->delete($data)){
			echo '1';
		}
		else{
			echo "2";
		}
		
	}
	public function shoppingListView(){
		$this->display('shopping_list');
	}
	public function orderListView(){
		$this->display('order_list');
	}
	public function supplierListView(){
		$page=empty($_GET['p'])?1:$_GET['p'];
		$like=empty($_GET['like'])?'':$_GET['like'];
		$where=array();
		$where['status'] = 1;
		if(!empty($like)){
			$where['username|contact_tel|company_name']=array('like',"%$like%");
		}
		$count = $this->supplier_user->where($where)->count('supplier_id');
		$list = $this->supplier_user->order("supplier_id desc")->where($where)->page($page.',50')->select();
		$fileList = $this->supplier->getContractFiles($list);
		$page=new \Think\Page($count,50);
		$this->assign('page',$page->show());
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('fileList',$fileList);
		$this->display('supplierList');
	}
	/**
	 * 发货人列表
	 * @author Zhangrui 
	 */
	public function supplierSenderList(){
		$page=empty($_GET['p'])?1:$_GET['p'];
		$like=empty($_GET['like'])?'':$_GET['like'];
		$where=array();		
		$SupplierSender = M('supplier_sender');
		if(!empty($like)){
			$where['supplier_username|company_name|supplier_id']=array('like',"%$like%");
		}		
		$size = 20;
		$count = $SupplierSender->where($where)->count('id');
		$list = $SupplierSender->order("id desc")->where($where)->page("{$page},{$size}")->select();
		$page=new \Think\Page($count,$size);
		$this->assign('page',$page->show());
		$this->assign('count',$count);
		$this->assign('list',$list);		
		$this->display();
	}
	/**
	 * 编辑发货人
	 * @author Zhangrui 
	 */
	public function modifySupplierSender(){
		$supplierId = I('get.supplierId');
		if(!is_numeric($supplierId) || $supplierId <= 0){
			exit("供应商信息有误");
		}
		//发货人信息
		$map = array(
			'supplier_id' => $supplierId
		);
		$senderInfo = M('supplier_sender')->where($map)->find();
		$this->assign('sender',$senderInfo);	
		$this->display();
	}
	/**
	 * 编辑发货人处理
	 */
	public function modifySenderEdit(){
		$sender = I('post.sender');  //发货人设置
		$sendSupplierId = I('post.sendSupplierId','');
		$sender['sender_status'] = I('status', 1, 'intval');
		//编辑发货人信息
		$res = $this->supplierSenderEdit($sender,$sendSupplierId,'update');		
		if($res){
			$this->success('更新成功!');
		}else{
			$this->error('更新失败!');
		}
	}
	public function supplierRecycle(){
		$page=empty($_GET['p'])?1:$_GET['p'];
		$like=empty($_GET['like'])?'':$_GET['like'];

		$where=array();
		$where['status'] = -1;
		if(!empty($like)){
			$where['username|contact_tel|company_name']=array('like',"%$like%");
		}

		$count = $this->supplier_user->where($where)->count('supplier_id');
		$list = $this->supplier_user->order("supplier_id desc")->where($where)->page($page.',20')->select();
		$fileList = $this->supplier->getContractFiles($list);
		$page=new \Think\Page($count,20);
		
		$this->assign('page',$page->show());
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('fileList',$fileList);
		$this->display('supplierRecycle');
	}
	
	//增加供应商用户view
	public function addSupplierView(){
		$this->display('addSupplier');
	}
	//增加供应商用户接口
	public function addSupplier(){
		
		//这行是判断有没有为空的值如果有会返回true
		//$is_empty = array_keys(array_map('trim', $_POST), '');

		if($this->checkUserExist($_POST['username'])){
			$this->error('用户名已存在');
			exit;
		}
		if(empty($_POST['password'])||empty($_POST['username'])){
			$this->error('用户名或密码不能为空');
			exit;
		}
		$_POST['password'] 		= md5($_POST['password']);
		$_POST['join_amount'] 	 = floatval($_POST['join_amount']);//格式化
		$_POST['deposit_amount'] = floatval($_POST['deposit_amount']);
		$sender = I('post.sender');  //发货人设置
		$sender['sender_status'] = I('status', 1, 'intval');
		//添加供应商
		$supplierId = $this->supplier_user->add($_POST);
		//添加发货人信息
		$sender['company_name'] = I('post.company_name','','strip_tags,stripslashes');
		$sender['supplier_username'] = I('post.username','','strip_tags,stripslashes');		
		$this->supplierSenderEdit($sender,$supplierId,'add');		
		if(is_numeric($supplierId)){
			//上传营业执照
			$licenseFile 	= $_FILES['license_file'];
			if(!empty($licenseFile['name'])){
				//上传文件
				$this->uploadSupplierFile($licenseFile, $supplierId, 1);
			}

			//上传税务登记复印件
			$taxFile 		= $_FILES['tax_file'];
			if(!empty($taxFile['name'])){
				$this->uploadSupplierFile($taxFile, $supplierId, 2);
			}

			//上传开户许可复印件
			$accountFile 	= $_FILES['account_file'];
			if(!empty($accountFile['name'])){
				$this->uploadSupplierFile($accountFile, $supplierId, 3);
			}

			//上传合同文件
			$contractFile = $_FILES['contract_file'];
			if(!empty($contractFile['name'])){
				//$this->uploadContract($contractFile, $supplierId);
				$contractFileId = $this->uploadSupplierFile($contractFile, $supplierId, 4);
				if(is_numeric($contractFileId)){
					//更新合同id
					$this->setSupplierContractFileId($supplierId, $contractFileId);
				}
			}
			$this->success('添加用户成功');
		}
	}

	/**
	 * @param $contractFile 上传的文件
	 * @param $supplierId 供应商id
	 * @param $fileType 供应商文件类型 1.营业执照 2.税务登记照 3.开户可证 4.战略合同
	 * @return mixed
	 */
	private function uploadSupplierFile($contractFile, $supplierId, $fileType){
		if(!is_numeric($fileType)){
			$this->error('上传合同文件失败（invalid fileType）');
		}

		if(!is_numeric($supplierId)){
			$this->error('上传合同文件失败（invalid supplier_id）');
		}

		$saveDir = '';
		$saveName = '';
		$replace  =  false; //存在同名是否覆盖
		$upplierIdMD5 = md5($supplierId);

		if($fileType == 1){
			$saveDir = './Upload/copies/';
			$saveName = $upplierIdMD5 . '_license';
			$replace = true;
		}else if($fileType == 2){
			$saveDir = './Upload/copies/';
			$saveName = $upplierIdMD5 . '_tax';
			$replace = true;
		}else if($fileType == 3){
			$saveDir = './Upload/copies/';
			$saveName = $upplierIdMD5 . '_account';
			$replace = true;
		}else if($fileType == 4){
			$saveDir = './Upload/contract/';
			$saveName = array('uniqid');
		}

		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 1024 * 1024 * 5 ;// 设置附件上传大小 5M
		//$upload->exts = array('doc', 'pdf');// 设置附件上传类型
		$upload->savePath = '';
		$upload->autoSub = false;
		$upload->saveName =$saveName;
		$upload->replace = $replace;
		$upload->rootPath  =  $saveDir; // 设置附件上传根目录

		// 上传文件
		$info  =  $upload->uploadOne($contractFile);

		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功
			$saveurl = $saveDir.$info['savename'];
			$result = false;
			$errorMsg = '';
			$supplierUser = $this->supplier_user->where("supplier_id=$supplierId");

			if($fileType == 1){
				$result = $supplierUser->setField('license_file', $saveurl);
				$errorMsg = "营业执照上传失败";
			}else if($fileType == 2){
				$result = $supplierUser->setField('tax_file', $saveurl);
				$errorMsg = "税务登记照上传失败";
			}else if($fileType == 3){
				$result = $supplierUser->setField('account_file', $saveurl);
				$errorMsg = "开户证明上传失败";
			}else if($fileType == 4){
				$file = array();
				$file['name'] =  $info['name'];
				$file['file_url'] =  $saveurl;
				$file['mark'] =  '';
				$file['supplier_id'] =  $supplierId;
				//查询供应商名称
				$signatory = $supplierUser->getField('contact_name');
				$file['signatory'] = $signatory;
				$contractFile = M('supplier_contract_file');
				$result = $contractFile->add($file);
				if($result)
					return $contractFile->getLastInsID(); //返回id
				$errorMsg = "合同上传失败";
			}

			if(is_numeric($result)){
				return $result;
			}else{
				$this->error($errorMsg);
			}
		}
	}

	/**更新供应商合同文件id
	 * @param $supplierId 供应商id
	 * @param $contractFileId 合同文件id
	 * @return mix
	 */
	private function setSupplierContractFileId($supplierId, $contractFileId){
		$result = $this->supplier_user->where("supplier_id=$supplierId")->setField('contract_file_id', $contractFileId);
		if($result !== false){
			return $result;
		}
		return false;
	}

	//查看供应商信息modify Zhangrui
	public function showSupplier(){
		$supplierId = I('get.supplier_id','');
		if(!is_numeric($supplierId) || $supplierId < 1){
			$this->error('invalid supplier_id');
		}
		$supplier = $this->supplier_user->where("supplier_id=$supplierId")->find();
		//发货人信息
		$map = array(
			'supplier_id' => $supplierId
		);
		$senderInfo = M('supplier_sender')->where($map)->find();
		$this->assign('sender',$senderInfo);		
		$this->assign('supplier',$supplier);
		$this->display('supplierShow');
	}

	public function modifySupplierView(){
		$supplier_id = I('get.supplier_id','');
		$tabidx = I('get.tabidx', 0, 'intval');
		if(!is_numeric($supplier_id)){
			echo 'ID不存在！';
			exit;
		}

		//查询附件合同
		$contractFileName = M('supplier_contract_file')
							->where("supplier_id=$supplier_id")
							->order('contract_id desc')
							->getField('name');
		$data = $this->supplier_user->where("supplier_id=$supplier_id")->find();
		//发货人信息
		$map = array(
			'supplier_id' => $supplier_id
		);
		$senderInfo = M('supplier_sender')->where($map)->find();
		$this->assign('contractFileName',$contractFileName);
		$this->assign('sender',$senderInfo);
		$this->assign('tabidx',$tabidx);
		$this->assign('data',$data);
		$this->display('supplierModify');
	}
	/**
	 * 检验管理员是否存在
	 * @author Zhangrui
	 */
	public function checkAdminAcount(){
		$account = I('post.account');
		$ret =array('code'=>0,'msg'=>'unknow Error');
		if(empty($account) || !is_numeric($account)){
			$ret['msg'] = '跟单员账户有误';
			$this->ajaxReturn($ret);
		}
		$map = array(
			'admin_username' => $account
		);
		$res = M('system_admin')->where($map)->getField('real_name');
		if($res){
			$ret['code'] = 1;
			$ret['msg'] = $res;
		}else{
			$ret['msg'] = '跟单员账户不存在';
		}
		$this->ajaxReturn($ret);
	}
	/**
	 * 添加更新发货人
	 */
	public function supplierSenderEdit($data,$supplierId,$type='add'){
		if(empty($data) || !is_array($data)){
			return false;
		}
		$SupplierSender = M('supplier_sender');
		if($type == 'update'){
			//更新
			$map = array(
				'supplier_id' => $supplierId
			);
			$data['modifyine_time'] = date('Y-m-d H:i:s');
			$res = $SupplierSender->where($map)->save($data); 
		}else if($type == 'add'){
			//添加
			$data['supplier_id'] = $supplierId;
			$res = $SupplierSender->data($data)->add();
		}
		if($res){
			return true;
		}else{
			return false;
		}
	}
	//修改供应商用户信息接口，任何修改操作，包括逻辑删除
	public function modifySupplier(){

		$supplierId = I('post.supplier_id','');
		$tabidx = I('post.tabidx', 0);
		$sender = I('post.sender');  //发货人设置
		$sendSupplierId = I('post.sendSupplierId','');
		$sender['sender_status'] = I('post.status', 1, 'intval');
		//编辑发货人信息
		if($sendSupplierId){
			$type = 'update';
		}else{
			$type = 'add';
		}
		$sender['company_name'] = I('post.company_name','','strip_tags,stripslashes');
		$sender['supplier_username'] = I('post.username','','strip_tags,stripslashes');
		$this->supplierSenderEdit($sender,$supplierId,$type);
		unset($_POST['tabidx']);

		if(!is_numeric($supplierId)){
			echo json_encode(array(-1,'ID错误！'));
			exit;
		}

		if(empty($_POST['password'])){
			unset($_POST['password']);
		}else{
			$_POST['password']=md5($_POST['password']);
		}
		$_POST['join_amount'] 	 = floatval($_POST['join_amount']);//格式化
		$_POST['deposit_amount'] = floatval($_POST['deposit_amount']);
		//上传营业执照复印件
		$licenseFile = $_FILES['license_file'];
		if(!empty($licenseFile['name'])){
			//上传文件
			$this->uploadSupplierFile($licenseFile, $supplierId, 1);
		}

		//上传税务登记复印件
		$taxFile = $_FILES['tax_file'];
		if(!empty($taxFile['name'])){
			$this->uploadSupplierFile($taxFile, $supplierId, 2);
		}

		//上传开户许可复印件
		$accountFile = $_FILES['account_file'];
		if(!empty($accountFile['name'])){
			$this->uploadSupplierFile($accountFile, $supplierId, 3);
		}
		//上传合同文件
		$contractFile = $_FILES['contract_file'];
		if(!empty($contractFile['name'])){
			$contractFileId = $this->uploadSupplierFile($contractFile, $supplierId, 4);
			if(is_numeric($contractFileId)){
				//更新合同id
				$this->setSupplierContractFileId($supplierId, $contractFileId);
			}
		}

		$status = $this->supplier_user->where("supplier_id=$supplierId")->save($_POST);
		if(is_numeric($status)){
			$param = array('supplier_id'=>$supplierId, 'tabidx'=>$tabidx);
			$this->success("更新成功", U('Supplier/modifySupplierView', $param));
		}else{
			if($status){
				echo json_encode(array(1,'更新成功！'));
			}else{
				echo json_encode(array(-2,'更新失败！'));
			}
		}
	}
	
	//修改用户状态
	public function setStatus(){
		$supplierId = I('post.supplier_id',-1,'intval');
		$status = I('post.status',0,'intval');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		if(!is_numeric($supplierId) || $supplierId < 1){
			$ret['msg'] = 'invalid supplier_id';
			$this->ajaxReturn($ret);
		}
		if($status != 1 && $status != -1){
			$ret['msg'] = 'invalid status';
			$this->ajaxReturn($ret);
		}
		$where['supplier_id'] = $supplierId;
		$result = $this->supplier_user->where($where)->setField('status',$status);
		
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = success;
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}
	
	//删除供应商接口，物理删除，未启用
// 	public function delSupplier(){
// 	}
	
	//检查用户名是否存在
	public function checkUserExist($username){
		return $this->supplier_user->where(array('username'=>$username))->find();
	}

	/**
	 * 下载战略合作合同
	 */
	public function download(){
		$contractId = I('get.contract_id',-1,'intval');
		$ftype = I('get.ftype',-1,'intval'); //1.营业执照 2.税务登记证 3.开户许可证
		$supplierId = I('get.supplier_id',-1,'intval');
		
		$fileName = '';
		$filePath = '';
		
		if(is_numeric($contractId) && $contractId > 0){
			$file = M('supplier_contract_file')
					->field('contract_id, name, file_url')
					->where(array('contract_id'=>$contractId))
					->order('contract_id desc')
					->find();
			$fileName = $file['name'];
			$filePath = $file['file_url'];
		}else if(is_numeric($supplierId) && $supplierId > 0){
			$where['supplier_id'] = $supplierId;
			$typeName="";
			if($ftype == 1){//1.营业执照
				$field = 'company_name,license_file file_url';
				$typeName = '_营业执照';
			}else if($ftype == 2){// 2.税务登记证 
				$field = 'company_name,tax_file file_url';
				$typeName = '_税务登记证';
			}else if($ftype == 3){//3.开户许可证
				$field = 'company_name,license_file file_url';
				$typeName = '_开户许可证';
			}else{
				exit();
			}
			$result = $this->supplier_user->where($where)->field($field)->find();
			$filePath = $result['file_url'];
			$fileName = $result['company_name'].$typeName.'.'.$this->getExtension($filePath);
		}
		
		
		if( !empty($filePath) && file_exists($filePath)){
 			ob_end_clean(); 
			
			$fopen = fopen($filePath, "r");
			$fileSize = filesize($filePath);
			$info = getimagesize($filePath);
			$filemime = $info['mime'];
			
			Header("Content-type: " + $filemime);
			Header("Accept-Ranges: bytes");
			header('Content-Disposition: attachment; filename="'.$fileName.'"'); //指定下载文件的描述
			header('Content-Length:'.$fileSize); //指定下载文件的大小
			
			$buffer = 1024;
			$fileCount = 0;
			//向浏览器返回数据
			while(!feof($fopen) && $fileCount < $fileSize){
				$fileContent = fread($fopen, $buffer);
				$fileCount += $buffer;
				echo $fileContent;
			}
			fclose($fopen);
		}else{
			$this->error('no file');
		}
	}
	
	//获取文件后缀
	function getExtension($file){
	 	$file = explode('.', $file);
	    return end($file);
	}
	//入库记录查询
	public function storageListView(){
		$page 	= I('get.p', 1);
		$like 	= I('get.like');
		$id 	= I('get.id');
		$where=array();
		if(!empty($like)){
			$where['title']=array('like',"%$like%");
		}
		if(!empty($id)){
			$where['id']=$id;
		}
		$where["status"] = array('neq',-1); //这段代码不能注释，在商品列表里只能查询到正常的商品
		$count=$this->storage->getStorageCount("");
		//$list=$this->supplier_item->where($where)->order("sitem_id desc")->page($page.',20')->select();
		$list=$this->storage->getStorageOrder("",$page,"20");
		if(!empty($list)){
			$supplierUser=$this->supplier->getFieldByGoods($list,'supplier_id',$this->supplier_user);
			$brandList=$this->supplier->getFieldByGoods($list,'brand_id',$this->syscategory_brand);
			$catList=$this->supplier->getFieldByGoods($list,'cat_id',$this->category);
			$this->assign('supplier_user',$supplierUser);
			$this->assign('brand_list',$brandList);
			$this->assign('cat_list',$catList);
		}
		$page=new \Think\Page($count,20);
		$this->assign('page',$page->show());
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->display('storageList');
	}
	//入库详情
	public function storageDetail(){
		$orderId=I('get.storage_id');
		if(empty($orderId)){
			echo 'ID为空';
			exit;
		}
		$list=$this->storage->getSkusByOrder($orderId);

		$this->assign("list",$list);
		$this->display("storageDetail");
	}

	//通过分类获取品牌
	public function getBrandByCat(){
		$cat_id=I("get.cat_id");
		//var_dump($cat_id);
		$list=$this->supplier->getBrandByCat($cat_id);
		$this->ajaxReturn($list);

	}
	public function addBrand(){
		$brand_name=I("post.brand_name");
		$cat_id=I("post.cat_id");

		if(!$cat_id){
			$this->ajaxReturn($cat_id);
		}
		$res=$this->supplier->addBrand($cat_id,$brand_name);

		$this->ajaxReturn($res);
	}

	//手动输入入库接口
	public function storage(){
		var_dump($_POST);
		exit;
		$json=I("post.storageCallBack");
		$json=$_POST['storageCallBack'];
		// $json='{"companyCode":"SZLS","purchaseOrders":[{"closeDate":"2016-10-11 13:46:10","erpOrder":"14","erpOrderType":"采购订单","items":[{"actualQty":"2.00","inventoryStatus":"20","planQty":"100.00","receiptTime":"2016-10-11 13:46:10","skuNo":"32273"},{"actualQty":"98.00","inventoryStatus":"10","planQty":"100.00","receiptTime":"2016-10-11 13:46:10","skuNo":"302020"},{"actualQty":"1.00","inventoryStatus":"20","planQty":"50.00","receiptTime":"2016-10-11 13:46:10","skuNo":"32273"},{"actualQty":"49.00","inventoryStatus":"10","planQty":"50.00","receiptTime":"2016-10-11 13:46:10","skuNo":"1002180"},{"actualQty":"100.00","inventoryStatus":"10","planQty":"200.00","receiptTime":"2016-10-11 13:46:10","skuNo":"32273"},{"actualQty":"98.00","inventoryStatus":"10","planQty":"200.00","receiptTime":"2016-10-11 13:46:10","skuNo":"996808"},{"actualQty":"2.00","inventoryStatus":"20","planQty":"200.00","receiptTime":"2016-10-11 13:46:10","skuNo":"996808"}],"receiptId":"SZLS16101101548434","status":"3900","warehouseCode":"571DCF"}]}';

		//var_dump($_POST);
		//$json=json_encode($json);
		$d=array(
			'json'=>$json
		);
		$this->supplier_history->add($d);
		$arr=json_decode($json);
		if(empty($arr)){
			echo '{"result":100, "errcode":1002, "msg":"json解析失败"}';
			exit;
		}
		$res=$this->home->saveCallBack($arr->purchaseOrders);
		if(!$res){
			echo '{"result":100, "errcode":1002, "msg":"插入记录失败"}';
			exit;
		}
		$re=$this->home->setOrderStatus($arr->purchaseOrders,"5");
		if(!$re){
			echo '{"result":100, "errcode":1002, "msg":"修改状态失败，订单号不存在，或者已经修改过了"}';
			exit;
		}
		echo '{"result":100, "errcode":0, "msg":"成功"} ';
	}

	/* Linjianli 供应商入驻管理  开始*/

	//供应商页面加载
	public function applylistview(){
		$page 	= I('get.p', 1);
		$data = $this->apply->order("apply_id desc")->page($page,20)->select();
		$count = count($this->apply->field("apply_id")->select());
		$page=new \Think\Page($count,20);
		$this->assign('page',$page->show());
		$this->assign('info',$data);
		$this->assign('count',$count);
		$this->display();
	}

	//供应商详细信息
	public function applyShow(){
		$apply_id = I('get.apply_id','');
		if(empty($apply_id)){
			exit("404 NOT FOUNT!");
		}
		$data = $this->apply->where("apply_id = ".$apply_id)->find();
		$this->assign('info',$data);
		$this->display('applyShow');
	}

	//供应商入驻审核
	public function Auditing(){
		$result = array('code' => 0,'msg' => 'Unkown Error');
		$aid = I('post.apply_id','');
		$type = I('post.type','');
		if(empty($aid) || empty($type)){
			$result['msg'] = 'id和类型为空';
			echo json_encode($result);
			return;
		}
		$data['type'] = $type;
		$res = $this->apply->where("apply_id = ".$aid)->save($data);
		//保存数据
		if($res){
			$result['code'] = 1;
			$result['msg'] = '审核成功';
			echo json_encode($result);
			return;
		}else{
			$result['msg'] = '审核失败';
			echo json_encode($result);
			return;
		}
		echo json_encode($result);
		return;
	}
	/* Linjianli 供应商入驻申请  结束*/

	//商品审核页面
	public function goodsReviewed(){
		$sitemId = I('sitem_id');
		if (empty($sitemId)) {
			$this->error("商品Id不存在！");
		}
		//供应商商品
		$condition = array('sitem_id'=>$sitemId);
		$field = array('sitem_id','is_reviewed','title');
		$sitemInfo = $this->supplier->getSitemInfo($condition,$field);
		//审核记录
		unset($condition);
		$condition = "sitem_id = ".$sitemId." and ir.admin_id = a.admin_id";
		$field =array("ir.*,a.real_name");
		$order = array("reviewed_id desc");
		$sitemReviewedList = $this->supplier->getReviewedList($condition,$field,$order);
		$this->assign('sitemInfo',$sitemInfo);
		$this->assign('sitemReviewedList',$sitemReviewedList);
		$this->display();
	}
	//审核商品操作
	public function goodsReviewedOp(){
		$data['sitem_id'] = I('sitem_id');
		$data['status'] = I('status');
		$data['remarks'] = I('remarks');
		$data['create_time'] = time();
		$data['admin_id'] = $this->adminId;
		if ($data['status'] == 2 && empty($data['remarks'])) {
			$this->error('请填写备注说明！');  
		}

		//开启事物
        $this->model = new \Think\Model(); 
        $this->model->startTrans();
        try{
        	$resReviewed = $this->supplier->addReviewed($data);
        	if (!$resReviewed) {
        		$this->model->rollback(); 
        		$this->error('审核失败！');    
        	}
        } catch (\Exception $e) {   
        	$this->model->rollback(); 
        	$this->error('审核失败！');      
        }
        //修改供应商商品状态 
        unset($data);  
        $condition = array('sitem_id'=>I('sitem_id'));
        $data['is_reviewed'] = I('status');   
        try{
        	$resSitem = $this->supplier->updateSitem($condition,$data);
        	if ($resSitem === false) {
        		$this->model->rollback(); 
        		$this->error('审核失败！');    
        	}
        } catch (\Exception $e) {   
        	$this->model->rollback(); 
        	$this->error('审核失败！');      
        }
        //修改供应商商品sku状态
       	unset($data);
       	$condition['status'] = array('neq', -1);
       	$data['is_reviewed'] = I('status');
        try {
        	$resSitemSku = $this->supplier->updateSitemSku($condition,$data);
        	if ($resSitemSku === false) {
        		$this->model->rollback(); 
        		$this->error('审核失败！');           		
        	}
        } catch (\Exception $e) {
    		$this->model->rollback(); 
    		$this->error('审核失败！');           	
        }
        $this->model->commit();	
        $this->success('审核成功！');	
 	}
}
