<?php
/**
  +------------------------------------------------------------------------------
 * InterfaceController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: InterfaceController.class.php v001 2016-06-02
 * @description 本地接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class TestController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->url='http://test.lishe.cn';
		$this->modelItem=M('sysitem_item');
		$this->modelTrade=M('systrade_trade');
		$this->modelSyncTrade=M('systrade_sync_trade');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelCategory=M('syscategory_cat');
		$this->modelCatConfig=M('company_category_config');
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
		$this->areaModel=M('site_area');
		
		$this->dActivity=D('Activity');
	}
	
	public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}
	
	public function jdSendGoods(){
		header("Content-type:text/html;charset=utf-8");
		$header=$this->url."/admin.php/Test/jdSendGoods/";
		$condition=array(
			'shop_id'=>10,
			'payed_fee'=>array('gt',0),
			'created_time'=>array('gt',time()-3600),
			'shop_memo'=>array('eq',''),
			'status'=>'IN_STOCK'
		);		
		$trade=$this->modelTrade->where($condition)->field('tid')->order('created_time DESC')->find();
		$tid=$trade['tid'];
		$sync=$this->modelSyncTrade->where('tid='.$tid)->field('sync_order_id')->order('modified_time DESC')->find();
		$jdtid=$sync['sync_order_id'];
		if(!empty($tid) && !empty($jdtid)){
			$this->modelTrade->where('tid='.$tid)->save(array('shop_memo'=>1));
			$this->assign('tid',$tid);
			$this->assign('jdtid',$jdtid);			
			$this->display();
		}else{
			echo $tid.'找不到京东订单号';
		}
	}
	
	//计算毛利率、更新上下架状态
	public function updateItem(){		
		header("Content-type:text/html;charset=utf-8");
		$page=isset($_GET['page'])?$_GET['page']:1;
		$header=$this->url."/business/index.php/Test/updateItem/?page=".($page+1);
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr=$this->modelItem->where('cost_price>0')->field('price,cost_price,item_id')->limit($start.','.$pageSize)->order('item_id DESC')->select();
		if(empty($itemArr)){
			echo '更新完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		}
		foreach($itemArr as $key=>$value){
			$rate=($value['price']-$value['cost_price'])*100/$value['price'];
			$this->modelItem->where('item_id='.$value['item_id'])->save(array('profit_rate'=>$rate));
			if($rate<15){
				echo $value['item_id']."已下架<br />";
				echo $this->modelItemStatus->where('item_id='.$value['item_id'])->save(array('approve_status'=>'instock'));
			}
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}

	public function updatePrice(){		
		header("Content-type:text/html;charset=utf-8");
		$page=isset($_GET['page'])?$_GET['page']:1;
		$header=$this->url."/business/index.php/Test/updatePrice/?page=".($page+1);
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr=$this->modelItem->where('cost_price>0')->field('price,cost_price,item_id')->limit($start.','.$pageSize)->order('item_id DESC')->select();
		if(empty($itemArr)){
			echo '更新完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		}
		foreach($itemArr as $key=>$value){
			$itemId[]=$value['item_id'];
			$price=round($value['price'],1);
			if($price!=$value['price']){
				echo "更新".$value['item_id']."<br />";
				$this->modelItem->where('item_id='.$value['item_id'])->save(array('price'=>$price));
			}
		}
		$itemSkuArr=$this->modelItemSku->where('item_id IN('.implode(',',$itemId).')')->field('price,sku_id,item_id')->select();
		if(!empty($itemSkuArr)){
			foreach($itemSkuArr as $key=>$value){
				$price=round($value['price'],1);
				if($price!=$value['price']){
					$this->modelItemSku->where('sku_id='.$value['sku_id'])->save(array('price'=>$price));
				}
			}
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}
	            
	public function requestJdPost($url='', $data=''){
        if(empty($url) || empty($data)){
            return false;
        }      
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
	
	public function delOrder(){
		$condition=array('tid'=>array('in','1607130930260099,1607081937260074,1607072012360188,1607072009390188,1606171154090099,1606161142360066,1606161003500066,1606142040020075,1606142033460075,1606141621120060,1606141436080062,1606131047370066,1606081541390062,1606021636210066,1606021634430066,1606012112320099,1606012106150099,1606012059090099,1606012040240074,1606010929390062, 1607072006090188,1607131639030225'));
		echo M('sysaftersales_refunds')->where($condition)->delete();
		echo M('systrade_trade_cancel')->where($condition)->delete();
		echo '<br />';
		$payment=M('ectools_trade_paybill')->field('payment_id')->where($condition)->select();
		if(!empty($payment)){
			foreach($payment as $key=>$value){
				$paymentId[]=$value['payment_id'];
			}
		}
		echo M('ectools_trade_paybill')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_trade')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_order')->where($condition)->delete();
		echo '<br />';
		if(!empty($paymentId)){
			unset($condition);
			$condition=array('payment_id'=>array('in',implode(',',$paymentId)));
			echo M('ectools_payments')->where($condition)->delete();
		}		
	}
	
	public function setCatNum(){	
		$categorysArr=$this->modelCategory->field('cat_id')->where('level=3')->select();
		foreach($categorysArr as $key=>$value){
			$cid=$value['cat_id'];
			$count=$this->modelItem->where('cat_id='.$cid)->count();
			echo $count;
			echo "<br />";
		}		
	}
	
	public function syncShopCat(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header= $this->url."/business/index.php/Test/syncShopCat/?page=".($page+1);
		$catArr= M('syscategory_cat')->where('level=2')->field('cat_name,jd_cid')->select();
		foreach($catArr as $key=>$value){
			$cat[$value['jd_cid']]=$value['cat_name'];
		}
		$shopCatArr= M('sysshop_shop_cat')->where('level=2 AND shop_id=10')->field('cat_name,cat_id')->select();
		foreach($shopCatArr as $key=>$value){
			$shopCat[$value['cat_name']]=$value['cat_id'];
		}
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr = $this->modelItem->field('item_id,jd_category')->where('jd_sku>0 AND shop_cat_id=\'0\'')->order('item_id DESC')->limit($start.','.$pageSize)->select();
		if(empty($itemArr)){
			echo '更新完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		}
		foreach($itemArr as $key=>$value){
			if(!empty($value['jd_category']) && empty($value['shop_cat_id'])){
				$catIdArr=explode(';',$value['jd_category']);
				$shopCatId=$shopCat[$cat[$catIdArr[1]]];
				if($shopCatId>0){
					$this->modelItem->where('item_id='.$value['item_id'])->save(array('shop_cat_id'=>','.$shopCatId.','));
				}else{
					$shopCatId=$shopCat[$cat[$catIdArr[0]]];
				}
				if(empty($shopCatId)){
					echo $value['item_id']."-".$value['jd_category']."找不到店铺分类<br />";
					file_put_contents('cat.txt','itemId:'.$value['item_id']."-".$value['jd_category']."找不到店铺分类\n",FILE_APPEND);
				}
			}			
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}
	
	public function updataCatID(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header_url = $this->url."/business/index.php/Test/updataCatID/?page=".($page+1);
		
		$Item = M('sysitem_item');
		$Category = M('syscategory_cat');
		$pageSize=30;
		$start=($page-1)*$pageSize;
		
		$Item_List = $Item->where('shop_id=10 AND (cat_id=0 OR cat_id=100000)')->field('item_id, jd_category')->order('item_id DESC')->limit($start.','.$pageSize)->select();//查询所有的京东商品 分类为空且京东商品分类不为空的 数据
		if($Item_List){
			foreach($Item_List as $k=>$list){
				$jd_category_array = explode( ';',$list['jd_category']);
				
				$key = count($jd_category_array)-1;
				$jd_cateid = $jd_category_array[$key];//取得切割字符串;获得的最后一个数组的值
				$cat_id_arr = $Category->where('jd_cid='.$jd_cateid)->field('cat_id')->find(); //通过京东分类ID找到对应的系统分类的ID值；
				if(empty($cat_id_arr['cat_id'])){
					$key = count($jd_category_array)-2;
					$jd_cateid = $jd_category_array[$key];//取得切割字符串;获得的最后一个数组的值
					$cat_id_arr = $Category->where('jd_cid='.$jd_cateid)->field('cat_id')->find(); //通过京东分类ID找到对应的系统分类的ID值；
				}
				if($cat_id_arr['cat_id']>0)
				{
					$Item->cat_id = $cat_id_arr['cat_id'];
				}else{
					$Item->cat_id =100000;
				}
				$Item->where('item_id='.$list['item_id'])->save(); // 根据条件更新记录
			}
			//跳转请求至下一页
			echo '<script type="text/javascript">window.location.href="'.$header_url.'"</script>';
		}else{
			echo "没有数据了";
			exit;
		}
	}

	public function findAddrId(){
		ini_set('max_execution_time', '1000000');  //设置最大执行时间
		// $sum=$this->addrModel->count();
		$addrInfo=$this->addrModel->field('addr_id,area')->select();
		if($addrInfo){
			foreach($addrInfo as $k=>$v){
				$addrId=trim($v['addr_id']);
				$areaArr=explode(':',trim($v['area']));
				$areaIdArr=explode('/',trim(trim($areaArr[1]),'/')); //去掉左右边多余的‘/’
				if(count($areaIdArr)==3){ //得到所有的三级
					$numThree=$areaIdArr[2];//得到第三级的ID
					//查找第四级的信息;
					$numFourInfo=$this->areaModel->where('jd_pid='.$numThree.' and level=4')->find();
					if($numFourInfo){
						$time=date('Ymd',time());
						//file_put_contents('logs/errAddressId/log'.$time.'.txt',$addrId."\r\n",FILE_APPEND);
						echo $addrId;
						echo '<br/>';
					}
				
				}
		
			}
		}
	}
	
}