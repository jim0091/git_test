<?php
/**
  +------------------------------------------------------------------------------
 * CartController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: CartController.class.php v001 2016-10-15
 * @description 购物车相关接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class CartController extends CommonController{
	public function __construct(){
		parent::__construct();
		
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemSkuStore=M('sysitem_sku_store');
		$this->modelItemStatus=M('sysitem_item_status');
	}
	
	//加入购物车接口封装 赵尊杰 2016-09-09注意：后面加事务处理
	public function apiAddToCart(){
		$userId = $this->uid;
		$itemId = I('post.itemId');
		$skuId = I('post.skuId');
		$quantity = I('post.quantity');
	}
	
	public function apiDelFromCart(){
		
	}	
	
	//检测购物车库存状态 赵尊杰 2016-10-17
	public function apiCheckCartStock(){
		$itemSku = trim($_POST['itemsSkus']);
		$area = I('post.area');
		$itemSku=str_replace('&quot;','"',$itemSku);
		$itemSkuParam=json_decode($itemSku,true);
		if(!empty($itemSkuParam)){
			foreach($itemSkuParam as $key=>$value){
				$itemId[$key]=$value['itemId'];
				$skuId[$key] = $value['skuId'];
				$itemNum[$value['skuId']]=$value['num'];
				$stock[$value['skuId']]=33;//默认有货
			}
		}else{
			$this->retError(1001,'必要参数不能为空');
		}
		$sku=array();
		//查商品表
		$conditionItem=array(
			'item_id'=>array('in',$itemId)
		);		
		$checkItem=$this->modelItem->where($conditionItem)->field('jd_sku,item_id')->select();
		//查库存表
		$conditionSku=array('sku_id'=>array('in',$skuId));
		$checkSku = $this->modelItemSkuStore->where($conditionSku)->select();
		//合并array
		foreach ($checkItem as $kItem => $vItem) {
			foreach ($checkSku as $kSku => $vSku) {
				if ($vItem['item_id'] == $vSku['item_id']) {
					$newItemSku[] = array_merge($vItem,$vSku);
				}
			}
		}		
		if(!empty($newItemSku)){
			foreach($newItemSku as $key=>$value){
				if($value['jd_sku']>0){
					$sku[]=array(
						'skuId'=>$value['jd_sku'],
						'num'=>$itemNum[$value['sku_id']]
					);
				}else{
					$noFreez = $value['store']-$value['freez'];
					if ($noFreez < 0 || $itemNum[$value['sku_id']] > $noFreez) {
						$stock[$value['sku_id']]=34;
					}
				}
				$jdItemId[$value['jd_sku']]=$value['sku_id'];
				$jdSkus[] = $value['jd_sku'];
			}
		}
		
		if(!empty($sku)){
			$data=array(
				'skuNums'=>$sku,
				'area'=>$area
			);
			$url=C('API_AOSERVER').'jd/product/checkstock';
            $result=$this->requestJdPost($url,json_encode($data));
            $retArr=json_decode($result,true);
            
            if($retArr['code']==100){
				foreach($retArr['data'] as $key=>$value){
					$stock[$jdItemId[$value['skuId']]]=$value['stockStateId'];
					if(($value['stockStateId']==33 or $value['stockStateId']==39 or $value['stockStateId']==40) and $value['remainNum']>0){
						$checkSku[]=$value['skuId'];
					}
				}		
			}else{//通讯失败设置为无货
				foreach($retArr['data'] as $key=>$value){
					$stock[$jdItemId[$value['skuId']]]=34;
				}
				//$this->makeLog('checkstock','url:'.$url.',data:'.json_encode($data).',return:'.$result."\n");
			}
			//$this->makeLog('checkstock','url:'.$url.',data:'.json_encode($data).',return:'.$result."\n");
		}
		//验证是否可售
		if(!empty($checkSku)){
			$url=C('API_AOSERVER').'jd/product/checkRep';
			$data='{"skuIds":"'.implode(',',$jdSkus).'"}';
			$result=$this->requestJdPost($url,$data);
            $retArr=json_decode($result,true);
            if($retArr['code']==100){
            	unset($checkSku);
				foreach($retArr['data'] as $key=>$value){
					//如果不可售设置为无货
					if($value['saleState']==0 or $value['isCanVAT']==0){
						$stock[$jdItemId[$value['skuId']]]=34;
					}else{
						$checkSku[]=$value['skuId'];
					}
				}		
			}else{
				$this->makeLog('checkRep','url:'.$url.',data:'.$data.',return:'.$result."\n");
			}
			//$this->makeLog('checkRep','url:'.$url.',data:'.$data.',return:'.$result."\n");
		}
		//验证是否支持配送
		if(!empty($checkSku)){
			$areas=explode('_',$area);
			$url=C('API_AOSERVER').'jd/product/checkAreaLimit';
			$data='{"skuIds":"'.implode(',',$jdSkus).'","province":'.intval($areas[0]).',"city":'.intval($areas[1]).',"county":'.intval($areas[2]).',"town":'.intval($areas[3]).'}';
			$result=$this->requestJdPost($url,$data);
            $retArr=json_decode($result,true);
            if($retArr['code']==100){
				foreach($retArr['data'] as $key=>$value){
					//如果有区域限制设置为无货
					if($value['isAreaRestrict']==true){
						$stock[$jdItemId[$value['skuId']]]=34;
					}
				}		
			}else{
				//$this->makeLog('checkAreaLimit','url:'.$url.',data:'.$data.',return:'.$result."\n");
			}
			//$this->makeLog('checkAreaLimit','url:'.$url.',data:'.$data.',return:'.$result."\n");
		}
		$this->retSuccess($stock,'操作成功');		
	}
	
}