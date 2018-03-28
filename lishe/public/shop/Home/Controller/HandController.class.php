<?php
/**
  +------------------------------------------------------------------------------
 * SystemController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰<10199720@qq.com>
 * @version  	$Id: HandController.class.php v001 2017-01-19
 * @description 手动处理问题专用控制器
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class HandController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->url='http://www.lishe.cn';
		$this->profit=15;
		$this->modelItem=M('sysitem_item');
		$this->modelSku=M('sysitem_sku');
		$this->modelSkuStore=M('sysitem_sku_store');
		$this->modelCat=M('syscategory_cat');
		$this->modelPayment=M('ectools_payments');
		$this->modelPaybill=M('ectools_trade_paybill');
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelSyncTrade=M('systrade_sync_trade');
	}
	
	//计算毛利率
	public function profit(){
		header("Content-type:text/html;charset=utf-8");
		$page=isset($_GET['page'])?$_GET['page']:1;
		$header=$this->url."/shop.php/Hand/profit/?page=".($page+1);
		$pageSize=30;
		$start=($page-1)*$pageSize;
		$itemArr=$this->modelItem->where('cost_price>0 AND price>cost_price AND profit_rate<=0')->field('price,cost_price,item_id')->limit($start.','.$pageSize)->order('item_id DESC')->select();
		if(empty($itemArr)){
			echo '处理完毕！';
			exit;
		}else{
			echo '更新第'.$page.'页！';
		};
		foreach($itemArr as $key=>$value){
			$rate=($value['price']-$value['cost_price'])*100/$value['price'];
			if($rate<=0){
				$rate=0;
			}
			$this->modelItem->where('item_id='.$value['item_id'])->save(array('profit_rate'=>$rate));
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}
	
	//自动取消订单
	public function cancelTrade(){
		$endTime=time()-86400;
		$conditon=array(
			'status'=>'WAIT_BUYER_PAY',
			'created_time'=>array('lt',$endTime)
		);
		$data=array(
			'status'=>'TRADE_CLOSED_BY_SYSTEM',
			'modified_time'=>time()
		);
		
		$trade=$this->modelTrade->where($conditon)->select($data);
		echo "<pre>";
		print_r($trade);
		echo "</pre>";
		//echo $this->modelTrade->where($conditon)->save($data);
	}
	
	//释放预占库存
	public function release(){
		header("Content-type:text/html;charset=utf-8");
		$conditon=array(
			'status'=>'WAIT_BUYER_PAY'
		);
		$trade=$this->modelTrade->where($conditon)->select();	
		if(empty($trade)){
			echo '执行完毕';
			exit;
		}
		foreach($trade as $key=>$value){
			$tid[]=$value['tid'];
		}
		$order=$this->modelOrder->where('tid IN ('.implode(',',$tid).')')->select();
		foreach($order as $key=>$value){
			$skuFreez[$value['sku_id']]+=$value['num'];
		}
		foreach($skuFreez as $key=>$value){
			$skuId[]=$key;
			echo "处理".$key."<br />";
			$ret=$this->modelSkuStore->where('sku_id='.$key)->save(array('freez'=>$value));
			echo "结果".$ret."<br />";
		}
		$this->modelSkuStore->where('sku_id NOT IN ('.implode(',',$skuId).')')->save(array('freez'=>0));
	}
	
	//自动确认收货
	public function confirmGoods(){
		$endTime=time()-86400;
		$conditon=array(
			'status'=>'WAIT_BUYER_CONFIRM_GOODS'
		);
		$this->modelTrade->where($conditon)->save($data);
	}
	
	//更新商品分类，主要用于更新京东新推送的商品
	public function catId(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header = $this->url."/shop.php/Hand/catId/?page=".($page+1);

		$pageSize=30;
		$start=($page-1)*$pageSize;
		
		$ItemList = $this->modelItem->where('shop_id=10 AND (cat_id=0 OR cat_id=100000)')->field('item_id, jd_category')->order('item_id DESC')->limit($start.','.$pageSize)->select();//查询所有的京东商品 分类为空且京东商品分类不为空的 数据
		if($ItemList){
			foreach($ItemList as $k=>$list){
				$jdCat = explode(';',$list['jd_category']);
				$jdCid = $jdCat[2];
				$catArr = $this->modelCat->where('jd_cid='.$jdCid)->field('cat_id')->find(); //通过京东分类ID找到对应的系统分类的ID值
				if($catArr['cat_id']>0){
					$catId = $catArr['cat_id'];
				}else{
					$catId =100000;
				}
				$this->modelItem->where('item_id='.$list['item_id'])->save(array('cat_id'=>$catId)); // 根据条件更新记录
			}
			//跳转请求至下一页
			echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
		}else{
			echo "处理完毕";
			exit;
		}
	}
	
	//同步顺丰库存
	public function syncStore(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header = $this->url."/shop.php/Hand/syncStore/?page=".($page+1);

		$pageSize=10;
		$start=($page-1)*$pageSize;
		
		$itemList = $this->modelItem->where('send_type=3')->field('item_id')->order('item_id DESC')->limit($start.','.$pageSize)->select();
		if($itemList){
			foreach($itemList as $key=>$value){
				$itemId[] = $value['item_id'];
			}
			$sku = $this->modelSku->where('item_id IN ('.implode(',',$itemId).') AND parent_sku_id=0')->field('sku_id')->select();
			foreach($sku as $key=>$value){
				$skuId[] = $value['sku_id'];
			}
			$url = C('API_AOSERVER').'sf/item/inventoryQuery';	
			$paramArr=array(
				'skuNos'=>implode(',',$skuId)
			);		
			$param = json_encode($paramArr);
			$result = $this->requestJdPost($url,$param);
		    $ret = json_decode($result,true);
		    $data=array();
		    if($ret['code'] == 100){
		    	if($ret['errCode'] == 0){
		    		$data=$ret['data']['rtInventorys'];
		    	}
		    	if(!empty($data)){
					foreach($data as $key=>$value){
			    		$sId = $value['header']['skuNo'];
			    		//查询活动sku
						$condition=array(
							'parent_sku_id'=>$sId,
							'start_time'=>array('egt',0),
							'end_time'=>array('elt',0),
							'disable'=>0
						);
						$asku = array();
						$asku = $this->modelSku->where($condition)->field('sku_id')->select();
						foreach($asku as $keys=>$values){
							$askuId[] = $values['sku_id'];
						}
						if(!empty($askuId)){
							$skuStore = $this->modelSkuStore->where('sku_id IN ('.implode(',',$askuId).')')->field('sku_id,store')->select();
							$askuStore=0;
							foreach($skuStore as $keyss=>$valuess){
								$askuStore+ = $valuess['store'];
							}
						}
					    		
			    		if(empty($value['header']['availableQty'])){
							$value['header']['availableQty']=0;
						}
						$store=intval($value['header']['availableQty']);
						$store=$store-$askuStore;
						if($store<0){
							$store=0;
						}
						$inventoryStatus=intval($value['header']['inventoryStatus']);
						if($inventoryStatus==10){
							$this->modelSkuStore->where('sku_id='.$sId)->save(array('store'=>$store));
						}else{
							$this->modelSkuStore->where('sku_id='.$sId)->save(array('inferior'=>$store));
						}
					}
				}		    	
		    }
		    if(in_array(19260,$skuId)){
				print_r($data);
				exit;
			}
		    echo '第'.$page.'页处理完毕!';
			//跳转请求至下一页
			echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
		}else{
			echo "处理完毕";
			exit;
		}
	}
	
	public function setPay(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$header = $this->url."/shop.php/Hand/setPay/?page=".($page+1);
		$pageSize=50;
		$start=($page-1)*$pageSize;
		
		$trade=$this->modelTrade->field('tid,pay_time,pay_type,created_time')->order('created_time DESC')->limit($start.','.$pageSize)->select();
		if(empty($trade)){
			echo '执行完毕';
			exit;
		}
		
		//foreach($trade as $k=>$v){
			//$tid[]=$v['tid'];
		//}
		//echo implode(',',$tid);
		//exit;

		foreach($trade as $k=>$v){
			unset($bids);
			unset($pids);
			echo "处理：".$v['tid']."<br />";
			$bill=$this->modelPaybill->field('paybill_id,payment_id,status')->where('tid='.$v['tid'])->order('paybill_id DESC')->select();
			if(empty($bill)){
				continue;
			}
			foreach($bill as $ks=>$vs){
				$bids[$vs['paybill_id']]=$vs['paybill_id'];
				$pids[$vs['paybill_id']]=$vs['payment_id'];
			}
			$bid=$bill[0]['paybill_id'];
			$pid=$bill[0]['payment_id'];
			unset($bids[$bid]);
			unset($pids[$bid]);
			if(!empty($bids)){
				$ret=$this->modelPaybill->where('paybill_id IN ('.implode(',',$bids).')')->save(array('status'=>'disabled'));
				echo "Paybill：".$ret."<br />";
				$ret=$this->modelPayment->where('payment_id IN ('.implode(',',$pids).')')->save(array('status'=>'disabled','cur_money'=>0));
				echo "Payment：".$ret."<br />";
			}
			
			//如果订单已经支付，更新支付单主表和子表
			if(!empty($v['pay_time'])){
				echo "更新时间：".$v['pay_time']."<br />";
				$ret=$this->modelPaybill->where('paybill_id='.$bid)->save(array('status'=>'succ','payed_time'=>$v['pay_time']));
				echo "1-Paybill：".$ret."<br />";
				if($v['pay_type']=='e-card'){
					$payAppId='e-card';
				}
				if($v['pay_type']=='online'){
					$payAppId='point';
				}
				$ret=$this->modelPayment->where('payment_id='.$pid)->save(array('status'=>'succ','payed_time'=>$v['pay_time'],'pay_app_id'=>$payAppId,'pay_type'=>'online'));
				echo "1-Payment：".$ret."<br />";	
			}else{
				$endTime=time()-86400;
				if($v['created_time']<$endTime){
					$status='cancel';
				}else{
					$status='ready';
				}
				$ret=$this->modelPaybill->where('paybill_id='.$bid)->save(array('status'=>$status,'payed_time'=>''));
				echo "2-Paybill：".$ret."<br />";
				$ret=$this->modelPayment->where('payment_id='.$pid)->save(array('status'=>$status,'payed_time'=>'','cur_money'=>0));
				echo "2-Payment：".$ret."<br />";				
			}
		}
		echo '<script type="text/javascript">window.location.href="'.$header.'"</script>';
	}
	
	//删除测试订单
	public function delOrder(){
		$condition=array('tid'=>array('in','17010411410601'));
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
		echo M('systrade_refund')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_aftersales')->where($condition)->delete();
		echo '<br />';
		echo M('systrade_refund_log')->where($condition)->delete();
		echo M('system_admin_trade_log')->where($condition)->delete();
		echo '<br />';
		if(!empty($paymentId)){
			unset($condition);
			$condition=array('payment_id'=>array('in',implode(',',$paymentId)));
			echo M('ectools_payments')->where($condition)->delete();
		}		
	}
}
?>