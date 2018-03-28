<?php
/**
  +------------------------------------------------------------------------------
 * HaihetaoController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: HaihetaoController.class.php v001 2016-5-22
 * @version  	$Id: HaihetaoController.class.php v001 章锐 2016-8-31
 * @description 海核淘
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class HaihetaoController extends CommonController {
	public function __construct(){
		parent::__construct();		
		$this->modelItemConfig=M('company_item_config');
		$this->modelCatConfig=M('company_category_config');
		$this->modelCategory=M('syscategory_cat');
		$this->modelItem=M('sysitem_item');
		$this->modelUserDeposit=M('sysuser_user_deposit');
		$this->modelstatus = M('sysitem_item_status');
		$this->tradeCartModel = M("systrade_cart");		
		$this->modelActivityConfig=M('company_activity_config');
		$this->modelAidItem=M('company_activity_item');
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
		$this->aitemModel = M('company_activity_item');//活动商品表
		$this->atradeModel = M('company_activity_trade');//活动订单表
		$this->aorderModel = M('company_activity_order');//活动订单子表

      	$this->paymentsModel = M("ectools_payments");//支付表
      	$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
      	$this->userAccountModel = M('sysuser_account');//用户登录表
      	$this->userDepositModel = M('sysuser_user_deposit');//积分表
      	$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
			

		//$this->haihetaoComId='1466483633689';
		$this->haihetaoComId=array('1466483633689','1472818301299','1472637383793','1474186315741');		

		if(empty($this->uid) && strtolower(ACTION_NAME)!='login'){
			header("Location:".__APP__."/Haihetao/login");
			exit;
		}
		if(strtolower(ACTION_NAME)!='login'){
			//if(!empty($this->comId) && $this->comId!=$this->haihetaoComId){
			if(!empty($this->comId) && !in_array($this->comId,$this->haihetaoComId)){
				header("Location:/shop.html");
				exit;
			}
			$menusConfig=$this->modelItemConfig->where('com_id='.$this->comId)->field('profit_rate,item_config_id,recommend,item_ids,cat_content,cat_icon,cat_banner,cat_name')->order('order_sort DESC')->select();
			if(!empty($menusConfig)){
				$catConfigArr=$this->modelCatConfig->field('cat_config_id,cat_id,cat_name,item_config_id')->where('disabled=0 AND com_id='.$this->comId)->order('order_sort DESC')->select();				
				foreach($menusConfig as $key=>$value){
					$menus[$value['item_config_id']]=array(
						'cfid'=>$value['item_config_id'],
						'name'=>$value['cat_name'],
						'banner'=>$value['cat_banner'],					
						'content'=>$value['cat_content'],					
						'recommend'=>$value['recommend'],					
						'item_ids'=>$value['item_ids'],
						'profit_rate'=>$value['profit_rate'],
						'cat_icon'=>$value['cat_icon'],
					);
					foreach($catConfigArr as $keys=>$values){
						if($value['item_config_id']==$values['item_config_id']){
							$menus[$value['item_config_id']]['category'][]=$values;
						}
					}						
				}
			}
			$this->menus=$menus;
			$this->assign('menus',$menus);
		}
	}
	
	public function getCategory($cfgId=0){
		$condition['disabled']=0;
		$condition['com_id']=$this->comId;
		if($cfgId>0){
			$condition['item_config_id']=$cfgId;
		}
		$catConfig=array();
		$catConfigArr=$this->modelCatConfig->field('item_config_id,cat_config_id,cat_id,cat_name,profit_rate,item_ids,shop_id')->where($condition)->order('order_sort DESC')->select();
		if(!empty($catConfigArr)){
			foreach($catConfigArr as $key=>$value){
				$catConfig[$value['item_config_id']][$value['cat_config_id']]=$value;						
			}			
		}
		return $catConfig;
	}
		
	public function index(){
		if($this->comId!='1466483633689'){
			header("Location:".__APP__."/Haihetao/moonActivity");
		}
		$menusConfig=$this->menus;
		foreach($menusConfig as $key=>$value){
			//查询上架中的推荐产品信息
			if(!empty($value['recommend'])){
				$condition='i.profit_rate>='.$value['profit_rate'].' AND i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				$itemList[$value['cfid']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag,i.shop_id')->where($condition)->order('i.flag asc,i.price desc')->limit(8)->select();
				
			}
			
		}
		$catConfig=$this->getCategory();		
		$this->assign('list',$itemList);
		$this->assign('category',$catConfig);
		$this->display();
	}
//取出一级分类下的二级分类
	public function nextCategory(){
			$cfgId=I('get.cfgId');
			$menusConfig=$this->menus;
			$categoryConfig=$menusConfig[$cfgId]['category'];	
			if($categoryConfig){
				$data=$categoryConfig;
			}else{
				$data=0;
			}
			echo json_encode($data);
	}
	public function category(){
		$cfgId=I('get.cfgId');
		$menusConfig=$this->menus;
		$itemConfig=$menusConfig[$cfgId];
		$categoryConfig=$this->getCategory($cfgId);
		$category=$categoryConfig[$cfgId];
		if(!empty($category)){	
			foreach($category as $key => $value){								
				$categorysArr=$this->modelCategory->field('cat_id')->where(array('parent_id'=>$value['cat_id']))->select();
				$categorysId =array();
				foreach ($categorysArr as $keys => $values){
					$categorysId[$key][] = $values['cat_id'];
				}
				if(!empty($categorysId[$key])){
					$condition='i.flag=0 AND i.price<=2000 AND i.profit_rate>='.$value['profit_rate'].' AND i.cat_id IN('.implode(',',$categorysId[$key]).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				}else{
					$condition = ' 1 > 1';
				}
				$list[$key] = $value;
				$list[$key]['list'] = M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.cat_id,i.flag,i.shop_id')->where($condition)->order('i.flag ASC,i.profit_rate DESC')->limit(10)->select();				
			}
		}else{
			
		}
		$this->assign('tempContent',$itemConfig);
		$this->assign('list',$list);
		$this->assign('cfgId',$cfgId);
		$this->display();
	}

	//搜索条件
	public function condition(){
		$condition='';
		if (trim(I('get.itemName'))) {
			$condition = " AND i.title like '%".$_GET['itemName']."%'";
		}
		if (trim(I('get.findPrice'))) {
			$priceInter = explode('-',trim(I('get.findPrice')));
			$priceLeft = $priceInter[0];
			$priceRight = $priceInter[1];
			$type = 1;
			if ($priceRight == 0 || $priceLeft > $priceRight) {
				$type = 2;
			}
			
			switch ($type){
				case '1':
					$condition .= " AND i.price between ".$priceLeft." and ".$priceRight;
					break;
				case '2':
					$condition .= " AND i.price > ".$priceLeft;
					break;				
				default:
					$condition = " AND i.price >= 0 ";
					break;
			}
			
		}
		return $condition;
	}
	//搜索排序
	public function sorting(){
		$price = trim(I('get.sortPrice'));
		$profit = trim(I('get.sortProfit'));
		if ($price) {
			$order = "i.flag asc,i.price ".$price;
		}
		if ($profit) {
			$order = "i.flag asc,i.profit_rate ".$profit;
		}

		return $order;
	}

	public function itemList(){
		$where = $this->condition();
		$order = $this->sorting();
		if (empty($order)) {
			$order = "i.flag ASC,i.cat_id ASC,i.profit_rate DESC";
		}
		$size = 30;
		$cfgId=I('get.cfgId');
		$catId=I('get.catId');
		$this->assign('keyword',trim(I('get.itemName')));
		
		$menusConfig=$this->menus;
		$itemConfig=$menusConfig[$cfgId];
		$this->assign('tempContent',$itemConfig);
		if(!empty($catId) && intval($catId)>0){
			$categoryConfig=$this->getCategory($cfgId);
			$category=$categoryConfig[$cfgId];
			$category=$category[$catId];
			if(!empty($category['cat_id'])){
				$categorysArr=$this->modelCategory->field('cat_id')->where('parent_id='.$category['cat_id'])->select();
				if(!empty($categorysArr)){
					foreach($categorysArr as $key=>$value){
						$catIds[]=$value['cat_id'];
					}				
					$condition='i.price<=2000 AND i.profit_rate>='.$category['profit_rate'].' AND i.cat_id IN ('.implode(',',$catIds).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$where;
					$count=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
					if(empty($where)){
						$this->modelCatConfig->where('cat_config_id='.$category['cat_config_id'])->save(array('item_count'=>$count));
					}		
					
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
					$itemList=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag,i.shop_id')->where($condition)->order($order)->limit($limit)->select();
					$this->assign('pagestr',$pagestr);
					$this->assign('empty','<div class="empty">未找到对应的商品！</div>');
					$this->assign('list',$itemList);
					$this->assign('comCategory',$category);
					$this->assign('cfgId',$cfgId);
					$this->assign('catId',$catId);
					if (I('get.ajaxpost')){
						$this->display('itemListAjax');
					}else{
						$this->display('itemList');
					}
				}
			}else{
				if(empty($category['item_ids'])){
					$category['item_ids']=$itemConfig['recommend'];
				}
				$this->showItemList($category['item_ids'],$where,$order,$cfgId,$catId);
			}
		}else{
			if(empty($itemConfig['item_ids'])){
				$itemConfig['item_ids']=$itemConfig['recommend'];
			}
			$this->showItemList($itemConfig['item_ids'],$where,$order,$cfgId,$catId);
		}		
	}
	
	public function showItemList($itemIds,$conditions,$order,$cfgId,$catId){
		$count=0;
		$itemList=array();
		$condition='i.item_id IN('.$itemIds.') AND i.price<=2000 AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$conditions;
		$itemCheck=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->order($order)->select();
		if(!empty($itemCheck)){
			foreach($itemCheck as $key=>$value){
				$itemId[]=$value['item_id'];
			}
			//实例化分页类
			$size=30;
			$count=count($itemId);				
			$page = new \Think\Page($count,$size);
			$rollPage = 5; //分页栏显示的页数个数；
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page->firstRow;  //起始行数
			$pagesize = $page->listRows;   //每页显示的行数
			$style = "badge";
			$onclass = "pageon";
			$pagestr = $page -> show($style,$onclass);  //组装分页字符串
			$itemId=array_slice($itemId,$start,$pagesize);
			$condition='i.item_id IN ('.implode(',',$itemId).') AND i.item_id=is.item_id AND is.approve_status=\'onsale\''.$conditions;
			$itemList=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag,i.shop_id')->where($condition)->order($order)->select();
		}
		$this->assign('pagestr',$pagestr);
		$this->assign('empty','<div class="empty">未找到对应的商品！</div>');
		$this->assign('list',$itemList);
		$this->assign('catinfo',$itemConfig);
		$this->assign('comCategory',$category);
		$this->assign('cfgId',$cfgId);
		$this->assign('catId',$catId);

		if (I('get.ajaxpost')){
			$this->display('itemListAjax');
		}else{
			$this->display('itemList');
		}
	}
	//用户登录
	public function login(){
		if(!empty($this->uid)){
			header("Location:".__APP__."/Haihetao/index");
		}
		if($_GET['go']=='cart'){
			$this->assign('refer','/cart.html');
		}elseif($_GET['go']=='order'){
			$this->assign('refer','/member-index.html');
		}else{
			$this->assign('refer',__APP__.'/Haihetao/moonActivity');
		}		
		$this->display();
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie('account',null);
		cookie('LSUID',null);
		cookie('UNAME',null);
		header("Location:".__APP__."/Haihetao/login");
	}

	//意见反馈
	public function feedBack(){
		$data['prom_type'] = I('post.promType'); 
		$data['user_id'] = $this->uid;
		$data['com_id'] = $this->comId;
		$data['item_name'] = I('post.itemName');
		$data['item_link'] = I('post.itemLink');
		$data['content'] = I('post.feedBack');
		$data['add_time'] = time();
		$res = M('company_feedback')->add($data);
		if ($res) {
			echo json_encode(array(1,'意见反馈成功！'));
		}else{
			echo json_encode(array(0,'意见反馈失败！'));
		}
	}

	//删除购物车商品
	public function delCart(){
		$cartId = I('post.cartId');
		if ($cartId) {
			$res = $this->tradeCartModel->where('cart_id ='.$cartId)->delete();
			if ($res) {
				echo json_encode(array(1,'删除成功！'));
			}else{
				echo json_encode(array(0,'删除失败！'));
			}
		}else{
			echo json_encode(array(0,'获取购物车id失败！'));
		}
	}

	//中秋活动 20160901 开始
	public function moonActivity(){ 
		if($this->comId=='1466483633689'){
			header("Location:".__APP__."/Haihetao/index");
		}
		$aid=I('get.aid',0,'trim');
		if(empty($aid)){
			$this->display('Moonhht');
		}
	}
	//中秋活动 20160901 结束

	//海核涛500元购 提交订单 开始
	public function order(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Haihetao/login");
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

		$itemId=I('get.aitem_id',0,'trim');
		$itemInfo=$this->modelAidItem->where('aitem_id='.$itemId)->find();
		if($itemInfo){
			$this->assign('aitem_id',$itemId);
			$this->assign('itemInfo',$itemInfo);
		}
		$this->display();
	}
	//海核涛500元购 提交订单 结束	


    //提交订单日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    }


	//提交订单
    public function addUserOrder(){    	
        $aitem_id = intval(I('post.aitem_id'));//商品id 
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n"; 
			echo " alert(\"您的配货地址没有选中，请重新选择！\");\r\n"; 
			echo "window.location.href=\"/business/index.php/Haihetao/order/aitem_id/"+$aitem_id+"\"\r\n"; 
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

   	    $aItemInfo = $this->aitemModel->where('aitem_id='.$aitem_id)->find();
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
        $data['total_fee'] = $aItemInfo['price']*$num+$aItemInfo['post_fee'];//订单总价
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
			echo "window.location.href=\"/member-index.html\"\r\n"; 
			echo "</script>";
			exit();         	
        }else{
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo "<script language=\"JavaScript\">\r\n";
			echo " alert(\"支付失败，积分不足！\");\r\n"; 
			echo "window.location.href=\"/business/index.php/Haihetao/moonActivity\"\r\n";  
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
            $payRes = $this->dedect($this->uid,$userAccountInfo['mobile'],$paymentid,$paymentInfo['money'],$paymentInfo['memo']);
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
                    	'pay_type'=>'deposit',
                    	'pay_time'=>time()
                    );
                    $this->atradeModel->where($where)->data($trdeDate)->save();

                    //更新支付主表
                    $zdata['cur_money'] = $paymentInfo['money'];
                    $zdata['pay_type'] = 'online';
                    $zdata['pay_app_id'] = 'deposit';
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

}