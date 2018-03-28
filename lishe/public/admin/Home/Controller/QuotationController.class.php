<?php
namespace Home\Controller;
use Think\Think;

class QuotationController extends CommonController{
	/*
     * 2017/3/1
     *	awen
     * */
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->modelQuotation=D("Quotation");
	}
 	//单列表（状态：-1审核不通过，1供应商未提交审核；2采销专员审核；3采销经理审核；4采销总监审核；5财务审核;9完成审核；）0显示所有
 	public function quotationList($status=0){ 		
 		$condition = "";
 		if (!empty($status)) {
 			$condition = " and q.status = ".$status;
 		}
 		$size = 20;
 		$number=$this->modelQuotation->getQuotationCount(array('status'=>$status));
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
 		$field = array('quotation_id,u.username,q.create_time,q.status');
 		$quotationList = $this->modelQuotation->getQuotationList($condition,$limit,$field);
 		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		if ($quotationList) {
			foreach ($quotationList as $key => $value) {
				$quotationIds[] = $value['quotation_id'];
			}
			$quotationIdsStr = implode(',', $quotationIds);
			unset($condition);
			$condition['_string'] = "quotation_id IN ($quotationIdsStr)";
			$quotationAuditList = $this->modelQuotation->getSsitemQuotationList($condition);
			$newQuotationAuditList = array();
			foreach ($quotationAuditList as $key => $value) {
				$newQuotationAuditList[$value['quotation_id']] = $value;
			}
			foreach ($quotationList as $key => $value) {
				if (!empty($newQuotationAuditList[$value['quotation_id']])) {
					$quotationList[$key]['back'] = 1;
				}else{
					$quotationList[$key]['back'] = 0;
				}			
			}
		}
		$this->assign('page',$pagestr);
		$this->assign('count',$number);
		$this->assign('status',$status);
 		$this->assign('quotationList',$quotationList);
 		
 	}
 	//全部报价单
 	public function allQuotationList(){
 		$this->quotationList();
 		$this->display('quotationList');
 	}
 	//采销专员
 	public function attacheQuotationList(){
 		$this->quotationList(2);
 		$this->display('quotationList');
 	}
 	//采销经理
 	public function managerQuotationList(){
 		$this->quotationList(3);
 		$this->display('quotationList');
 	}
 	//采销总监
 	public function directorQuotationList(){
 		$this->quotationList(4);
 		$this->display('quotationList');
 	}
 	//财务
 	public function financeQuotationList(){
 		$this->quotationList(5);
 		$this->display('quotationList');
 	}
 	//报价成功
 	public function achieveQuotationList(){
 		$this->quotationList(9);
 		$this->display('quotationList');
 	}
 	//报价单审核
 	public function quotationReviewed(){
 		$quotationId = I('quotationId');
 		$status = I('status');
 		if (empty($quotationId) || empty($status)) {
 			$this->error("系统繁忙，稍后再试！");
 		}
 		//审核日志
 		$condition['quotation_id'] = $quotationId;
 		$ssitemQuotationList = $this->modelQuotation->getSsitemQuotationList($condition);
 		$this->assign('quotationId',$quotationId);
 		$this->assign('ssitemQuotationList',$ssitemQuotationList);
 		$this->assign('status',$status); 		
 		$this->display();
 	}
 	//报价单审核操作
 	public function quotationReviewedOp(){
 		$status = I('status');
 		$nowStatus = I('nowStatus');

 		$data['quotation_id'] = I('quotationId');
 		$data['audit_uid'] = $this->adminId;
 		$data['audit_name'] = $this->adminName;
 		$data['remark'] = I('remark');
 		$data['type'] = $status;
 		$data['status'] = $nowStatus;
 		$data['create_time'] = time();
 		$quotationId = $data['quotation_id'];

 		$remark = trim($data['remark']," ");
 		//不通过的情况下备注必须填写
 		if (empty($nowStatus) && empty($remark)) {
 			$this->error("请填写不通过的备注说明！");
 		}

 		if (empty($data['quotation_id']) || empty($status)) {
 			$this->makeLog('quotationReviewedOp',"error(1001) msg:缺少必传参数quotationId:".I('quotationId'));
 			$this->error("系统繁忙，稍后再试！");
 		} 	
 		unset($condition);
 		unset($field);
 		$condition['quotation_id'] = $data['quotation_id'];
 		$condition['status'] = 1;
 		$field = array('sitem_id,ssku_id,special_supply');
 		//查询所有报价单商品sku
 		$resQuGoodsList = $this->modelQuotation->getQSkuList($condition,$field); 
 		if (!$resQuGoodsList) {
 			$this->error('未查询到商品，无法通过该报价单！');
 		} 
 		//查询所有商品/sku信息修改到对应的商品表/sku表中 
 		$newQuGoodsList	= array();		
		foreach ($resQuGoodsList as $key => $value) {
			$newQuGoodsList[$value['ssku_id']] = $value;
			$sskus[] = $value['ssku_id'];
			$sitems[] =$value['sitem_id'];
		}
		$sskusStr = implode(',', $sskus);
		$sitemStr = implode(',', array_unique($sitems));
		//审核通过
		if (!empty($nowStatus)) {	
	 		//检查报价单是否有不通过的报价商品(所有的SKU毛利率超过30%（或选择特供）才可提交审核通过)	 		
			//查询供应商商品表和商品sku表信息	
			unset($condition);
			unset($field);
			$condition['_string'] = "sku.sitem_id = i.sitem_id and sku.ssku_id = g.ssku_id and sku.ssku_id IN (".$sskusStr.")";
			$field = "sku.cost_price,sku.ssku_id,i.input_tax,i.freight,i.sitem_id,g.price,g.id,sku.price as skuPrice";
			$sitemAndSsku = $this->modelQuotation->getSitemAndSsku($condition,$field);
			if (!$sitemAndSsku) {
				$this->error('未查询到商品，无法通过该报价单！');
			}
			foreach ($sitemAndSsku as $key => $value) {	
				if ($this->salesMargin($value['skuPrice'],$value['cost_price'],$value['input_tax'],$value['freight']) < 0.3 && $newQuGoodsList[$value['ssku_id']]['special_supply'] == 0 ) {
					$this->error("存在无法满足条件的商品，无法通过该报价单！ID:".$value['sitem_id']);
				}
			}
		}else{
			if (empty($data['remark'])) {
				$this->error('请填写备注！');
			}
		}
		//开启事物
		$this->model = new \Think\Model(); 
		$this->model->startTrans();			
 		try { 			
	 		//添加审核日志
	 		$resAudit = $this->modelQuotation->addAudit($data);

	 		if (!$resAudit) {
	 			//回滚
	 			$this->model->rollback();
	 			$this->makeLog('quotationReviewedOp',"error(1002) msg:添加审核日志失败quotationId:".I('quotationId'));
	 			$this->error("审核失败！");
	 		}
 		} catch (\Exception $e) { 			
 			$this->model->rollback();
	 		$this->makeLog('quotationReviewedOp',"error(1003) msg:添加审核日志失败quotationId:".I('quotationId').';'.$e->getMessage());
 			$this->error("审核失败！");
 		}

		//审核通过
		if (!empty($nowStatus)) {	
			//设置审核通过的下个状态	 		
	 		if ($status == 5) {
	 			unset($status);
	 			$status = 9;
	 			$skuQuoteStatus = 3;
	 			$itemQuoteStatus = 2;
	 			$isReviewed = 0;
	 		}else{
	 			$status++;
	 		}
	 	}else{
	 		//审核不通过	 				
			if ($status == 2) {
	 			unset($status);
	 			$status = -1;
	 			$skuQuoteStatus = 1;
	 			$itemQuoteStatus = 0;	
	 			$isReviewed = 2;		
	 		}else{
	 			unset($status);
	 			$status = 2;
	 		}	 
	 	}
 		//修改报价单表的审核状态
 		unset($condition);
 		$condition['quotation_id'] = $data['quotation_id'];
 		$dataQu['status'] = $status;
 		try {
 			$resQuotation = $this->modelQuotation->updateQuotation($condition,$dataQu);
	 		if ($resQuotation === false) {
	 			//回滚
	 			$this->model->rollback();
	 			$this->makeLog('quotationReviewedOp',"error(1004) msg:修改报价单表的审核状态失败quotationId:".I('quotationId')." Status:".$status);
	 			$this->error("审核失败！");		 			
	 		}
 		} catch (\Exception $e) {	 			
 			//回滚
 			$this->model->rollback();
 			$this->makeLog('quotationReviewedOp',"error(1005) msg:修改报价单表的审核状态失败quotationId:".I('quotationId').";".$e->getMessage);
 			$this->error("审核失败！");
 		}
 		//报价单最终审核通过
 		if ($status == 9 || $status == -1) { 
 			unset($condition);	 
 			unset($data);		
 			$condition['ssku_id'] = array('in',$sskusStr); 			
 			$data['quote_status'] = $skuQuoteStatus;
 			$data['is_reviewed'] = $isReviewed;
 			try {
 				//修改供应商表中的sku的状态
	 			$resSitemSku = $this->modelQuotation->updateSitemSku($condition,$data);
	 			if ($resSitemSku === false) {
		 			//回滚
		 			$this->model->rollback();
		 			$this->makeLog('quotationReviewedOp',"error(1007) msg:修改供应商表中的sku的状态失败quotationId:".I('quotationId'));
		 			$this->error("审核失败！");		 				
	 			}
 			} catch (\Exception $e) {
	 			//回滚
	 			$this->model->rollback();
	 			$this->makeLog('quotationReviewedOp',"error(1008) msg:修改供应商表中的sku的状态失败quotationId:".I('quotationId').';'.$e->getMessage());
	 			$this->error("审核失败！");	
 			}

 			if ($status == 9) {
 				unset($condition);
	 			unset($data);
	 			$condition['sitem_id'] = array('in',$sitemStr);
	 			$data['quote_status'] = $itemQuoteStatus;
	 			$data['is_reviewed'] = 0;
	 			try {
	 				//修改供应商商品表的状态
	 				$resSitem = $this->modelQuotation->updateSitem($condition,$data);
	 				$this->insertQuotationHistory($quotationId);
	 				if ($resSitem === false) {
			 			//回滚
			 			$this->model->rollback();
			 			$this->makeLog('quotationReviewedOp',"error(1009) msg:修改供应商商品表的状态失败quotationId:".I('quotationId'));
			 			$this->error("审核失败！");		 					
	 				}
	 			} catch (\Exception $e) {
		 			//回滚
		 			$this->model->rollback();
		 			$this->makeLog('quotationReviewedOp',"error(1010) msg:修改供应商商品表的状态失败quotationId:".I('quotationId').';'.$e->getMessage());
		 			$this->error("审核失败！");			 				
	 			}

	 			//检测该sku是否已经推送到商城（已推送商城直接修改商城商品sku价格，否则不做处理）
	 			unset($condition);
	 			unset($data);
	 			$condition['ssku_id'] = array('in',$sskusStr);
	 			$condition['sku_id'] = array('neq',0);
	 			$field = array('sku_id','price','cost_price','mkt_price');
	 			$pushedSitemSkuList = $this->modelQuotation->getPushedSitemSkuList($condition,$field);	 			
	 			if (!empty($pushedSitemSkuList) && is_array($pushedSitemSkuList)) {
	 				foreach ($pushedSitemSkuList as $key => $value) {
	 					if (!empty($value['sku_id'])) {
	 						$value['point'] = $value['price'] * 100;
	 						$value['cash'] = 0;
	 						try {
	 							$pricePush = $this->modelQuotation->updateSkuPrice($value);
	 							if ($pricePush === false) {
				 					$this->makeLog('quotationReviewedOp',"error(1011) msg:商城sku价格更新失败quotationId:".I('quotationId')." sku_id:".$value['sku_id']);
				 				}
	 						} catch (\Exception $e) {
	 							$this->makeLog('quotationReviewedOp',"error(1012) msg:商城sku价格更新失败quotationId:".I('quotationId')." sku_id:".$value['sku_id'].'errorMsg:'.$e->getMessage());
	 						}
	 					}
		 				
	 				}
	 			}
 			}	
 		}
 		//提交事务
 		$this->model->commit();
 		$this->success("审核成功！");
 	}
 	/**
	 * 插入报价单历史记录
	 * @author zhangxiaobo
	 */
	public function insertQuotationHistory($quotId){
		$quotGoods = M();
		$list = $quotGoods->table('supplier_quotation_goods good, supplier_item item, supplier_item_sku sku')
				->where('good.quotation_id = %d and good.sitem_id = item.sitem_id and item.sitem_id = sku.sitem_id and good.ssku_id = sku.ssku_id', array($quotId))
				->field('good.*, item.send_type, item.freight, item.price suggest_price, item.input_tax, sku.cost_price, sku.mkt_price, sku.mkt_price_url')
				->select();
		$quotHis = M('supplier_quotation_history');
		$maxVersion = $quotHis->where("quotation_id = %d", array($quotId))->max('version');
		if($maxVersion){
			$maxVersion = $maxVersion + 1;
		}else{
			$maxVersion = 1;
		}
		if($list){
			foreach($list as &$val){
				unset($val['id']);
				$val['version'] = $maxVersion;
				$val['audit_status'] = 1;
				$quotHis->data($val)->add();
			}
		}
	}

 	/*销售毛利率计算【含税销售单价/1.17-含税采购单价/（1+单品税率%）-运费】/（含税销售单价/1.17）
	*$price:含税销售单价,$costPrice:含税采购单价,$inputTax:单品税率（进项税）,$freight:运费
	*/
 	public function salesMargin($price,$costPrice,$inputTax,$freight){
 		$outputTax = C('OUTPUT_TAX');//销项税
 		if (empty($price) && empty($costPrice) && empty($inputTax) && empty($outputTax)) {
 			return false;
 		}
 		return round(($price/(1+$outputTax)-$costPrice/(1+$inputTax/100)-$freight)/($price/(1+$outputTax)),3);
 	}

 	//查看报价单中的商品sku
 	public function skuList(){
 		$quotationId = I('quotationId');
 		$status = I('status');
 		if (empty($quotationId)) { 
 			$this->error("不存在的报价单！");
 		}
 		$count = $this->modelQuotation->getSitemCount($quotationId);
 		$size = 20;
		$page = new \Think\Page($count,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
 		$field = 'sitem_id, item_id, supplier_id, cat_id, brand_id, title, status, input_tax, send_type, freight';
 		$order = 'sitem_id desc';
 		//查询报价单中的商品列表
		$sitemList = $this->modelQuotation->getsitemList($quotationId,$field,$order,$limit);
 	// 	$sitemList = M("supplier_item")->where('EXISTS(select 1 from supplier_quotation_goods q where q.sitem_id = supplier_item.sitem_id and q.quotation_id = %d)', array($quotationId))
		// ->field('sitem_id, item_id, supplier_id, cat_id, brand_id, title, status, input_tax, send_type') //output_tax,
		// 		->order('sitem_id desc')
		// 		->select();
		if (!$sitemList) {
 			$this->error("未查询到报价单中的商品！");
 		}
 		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
 		//查询报价单中的商品sku列表
 		$condition = array('quotation_id'=>$quotationId,'status'=>1);
 		$qSkuList = $this->modelQuotation->getQSkuList($condition);
		if (!$qSkuList) {
 			$this->error("未查询到报价单中的商品！");
 		}
 		//组合查询条件
 		foreach ($qSkuList as $key => $value) {
 			$sskus[] = $value['ssku_id'];
 		} 		
 		$sskuStr=implode(',',$sskus);

		//查询sku信息
		unset($condition);
		$condition['ssku_id']=array('in',$sskuStr);
		$sskuList = $this->modelQuotation->getSskuList($condition);
		
		if (!$sskuList) {
			$this->error("未查询到供应商sku信息！");
		}
		//ssku_id作为key重新组合
		$newSskuList = array();
		foreach ($sskuList as $key => $value) {
			$newSskuList[$value['ssku_id']] = $value;
		}
		//组合报价单中的商品信息和供应商商品sku信息
		foreach ($qSkuList as $key => $value) {
			$qSkuList[$key]['skuInfo'] = $newSskuList[$value['ssku_id']];			
		}
		//组合查询条件
		foreach ($sitemList as $key => $value) {
			$scats[] = $value['cat_id'];
			$sbrands[] = $value['brand_id'];
		}
 		$scatsStr=implode(',',$scats);
 		$sbrandsStr = implode(',',$sbrands);
 		//查询所有分类
 		unset($condition); 		
 		$condition['cat_id'] = array('in',$scatsStr);
 		$field = array('cat_id,cat_name');
 		$scatList = $this->modelQuotation->getScatList($condition,$field);
 		if (!$scatList) {
 			$this->error("未查询到分类信息！");
 		}
 		//cat_id作为key重新组合
 		$newScatList = array();
 		foreach ($scatList as $key => $value) {
 			$newScatList[$value['cat_id']] = $value;
 		}
 		//查询品牌
 		unset($condition);
 		unset($field);
 		$condition['brand_id'] = array('in',$sbrandsStr);
 		$field = array('brand_id,brand_name');
 		$sbrandList = $this->modelQuotation->getSbrandList($condition,$field);
 		if (!$sbrandList) {
 			$this->error("未查询到品牌信息！");
 		}
 		//brand_id作为key重新组合
 		$newSbrandlist = array();
 		foreach ($sbrandList as $key => $value) {
 			$newSbrandlist[$value['brand_id']] = $value;
 		}
 		//cat_name,brand_name添加到商品信息中
 		foreach ($sitemList as $key => $value) {
 			$sitemList[$key]['catInfo'] = $newScatList[$value['cat_id']];
 			$sitemList[$key]['brandInfo'] = $newSbrandlist[$value['brand_id']];
 		}
		//组合供应商商品信息和商品sku信息
		foreach ($sitemList as $key => $value) {
			foreach ($qSkuList as $k => $val) {
				if ($value['sitem_id'] == $val['sitem_id']) {
					$sitemList[$key]['skuInfo'][$k] = $val;
					$sitemList[$key]['skuInfo'][$k]['salesMargin'] = $this->salesMargin($val['price'],$val['skuInfo']['cost_price'],$value['input_tax'],$value['freight']);
				}
			}
			
		}
		$this->assign('count',$count);
		$this->assign('page',$pagestr);
 		$this->assign('sitemList',$sitemList);
 		$this->assign('status',$status);
 		$this->display();
 	}

 	//审核商品备注
 	public function goodsRemark(){
 		$id = I('id');
 		$status = I('status');
 		if (empty($id)) {
 			echo "未查询到信息！";
 			exit();
 		}
 		$condition['id'] = $id;
 		$field = array('id,ssku_id,price,special_supply,remark');
 		$goodsInfo = $this->modelQuotation->getGoodesInfo($condition,$field);
 		$this->assign('goodsInfo',$goodsInfo);
 		$this->assign('status',$status);
 		$this->display();
 	}
 	//提交审核商品备注
 	public function goodsRemarkOp(){
 		$id = I('id');
 		if (empty($id)) {
 			$this->error("系统繁忙，请关闭重试！");
 		}
 		$ssku_id = I('ssku_id');
 		$data['price'] = I('price');
 		if (empty($ssku_id)) {
 			$this->error("系统繁忙，请关闭重试！");
 		}
 		//更新供应商sku中的礼舍售价信息
 		try {
 			$condition['ssku_id'] = $ssku_id;
 			$resSsku = $this->modelQuotation->updateSsku($condition,$data); 			
 		} catch (\Exception $e) {
 			$this->error("修改失败！"); 			
 		}
 		$data['special_supply'] = I('special_supply');
 		$data['remark'] = I('remark');
 		unset($condition);
 		$condition['id'] = $id;
 		$remark = trim($data['remark'] ,' ');
 		if (!empty($data['special_supply']) && empty($remark)) {
 			$this->error("请填写特价商品说明！");
 		}
 		//修改报价sku信息
 		try {
 			$resGoods = $this->modelQuotation->updateGoods($condition,$data);
 			if ($resGoods) {
 				$this->success("修改成功！");
	 		}else{
	 			$this->error('修改失败！');
	 		}
 		} catch (\Exception $e) {
 			$this->error('修改失败！');
 		}
 		
 		
 	}

}
