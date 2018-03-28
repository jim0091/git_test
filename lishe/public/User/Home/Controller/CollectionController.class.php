<?php
/**
 +------------------------------------------------------------------------------
* OrderController
+------------------------------------------------------------------------------
* @author   	
* @version  	$Id: CollectionController.class.php v001 2016-5-22
* @description 收藏管理
+------------------------------------------------------------------------------
*/
namespace Home\Controller;
class CollectionController extends CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__SHOP__."/Sign/index");
			exit;
		}
		$this->modelOrder=D('Collection');
	}

	//店铺收藏
	public function shopList(){
		$shopList = $this->modelOrder->getShopList('user_id='.$this->uid);
		$this->assign('shopList',$shopList);
		$this->display();
	}
	//删除店铺收藏
	public function shopDel(){
		$snotifyId = I('sid');
		if (empty($snotifyId)) {
			echo 0;
			exit();
		}
		$res = $this->modelOrder->delShop('snotify_id='.$snotifyId);
		if ($res) {
			echo 1;
		}else{
			echo 0;
		}
	}

	//商品收藏
	public function itemList(){
		//实例化分页类
		$count = $this->modelOrder->getItemCount('user_id='.$this->uid);
		$size=18;
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
		$itemList = $this->modelOrder->getItemList('user_id='.$this->uid,$limit);
		$this->assign('itemList',$itemList);
		$this->assign('pagestr',$pagestr);
		$this->display();
	}
	//删除商品收藏
	public function itemDel(){
		$gnotifyId = I('gid');
		if (empty($gnotifyId)) {
			echo 0;
			exit();
		}
		$res = $this->modelOrder->delItem('gnotify_id='.$gnotifyId);
		if ($res) {
			echo 1;
		}else{
			echo 0;
		}
	}

}