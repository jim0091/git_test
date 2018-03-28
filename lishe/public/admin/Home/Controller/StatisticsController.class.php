<?php 
namespace Home\Controller;
class StatisticsController extends CommonController {
/*
 * 统计管理
 * 2017/2/8
 * linjianli
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dStatistics=D('Statistics');
	}

	//月度统计	
	public function monthStat(){
		$data = $this->dStatistics->getMonthStat();
		$this->assign('sum',$data[1]);
		$this->assign('data',$data[0]);
		$this->display();
	}

	//日常统计 20条数据一页
	public function dailyStat(){
		$page 	= I('get.p', 1);
		$startDate = I('get.startdate','');
		$endDate = I('get.enddate','');
		if($startDate == '' || $endDate == ''){
			$data = $this->dStatistics->getDailyStat($page);
			$count = count($this->dStatistics->modelDaily->select());
			$page=new \Think\Page($count,30);
			$this->assign('page',$page->show());
			$this->assign('count',$count);
			$this->assign('data',$data);
			$this->display();
		}else{
			$data = $this->dStatistics->getDailyStat($page,$startDate,$endDate);
			$count = $data[1];
			$page=new \Think\Page($count,30);
			$this->assign('page',$page->show());
			$this->assign('count',$count);
			$this->assign('data',$data[0]);
			$this->display();
		}

	}
	
	//重构方法 PHP + AJAX + JQUERY 异步刷新商品统计页面
	public function goods(){
		$this->display();
	}

	public function getGoods(){
		//获取传递的所有值
		$data = I('get.');
		$table = 'modelGoods';
		$this->dStatistics->datatables($table,$data);
	}

	//商品统计
	public function goodsStat(){
		$this->display('goods');
	}

	//企业统计
	public function companyStat(){
		$page 	= I('get.p', 1);
		$comId = I('get.cid',0);
		if(empty($comId)){
			$getComId = $this->dStatistics->getCompanyId();
			$this->assign('comid',$getComId);
			$this->display();
			exit;
		}else{
			$companyName = $this->dStatistics->modelCompany->field('com_name')->where("com_id=".$comId)->select();
			$data = $this->dStatistics->getCompanyStat($page,$comId);
			$count = count($this->dStatistics->modelCom->where("com_id=".$comId)->select());
			$getComId = $this->dStatistics->getCompanyId();
			$page=new \Think\Page($count,30);
			$this->assign('page',$page->show());
			$this->assign('cname',$companyName);
			$this->assign('count',$count);
			$this->assign('comid',$getComId);
			$this->assign('sum',$data[1]);
			$this->assign('data',$data[0]);
			$this->display();
			exit;
		}
	}

	//供应商统计
	public function supplierStat(){
		$page 	= I('get.p', 1);
		$supId = I('get.sid',0);
		if(empty($supId)){
			$getComId = $this->dStatistics->getSupplierId();
			$this->assign('comid',$getComId);
			$this->display();
			exit;
		}else{
			$companyName = $this->dStatistics->modelSuppUser->field('company_name')->where("supplier_id=".$supId)->select();
			$data = $this->dStatistics->getSupplierStat($page,$supId);
			$count = count($this->dStatistics->modelSupplier->where("supplier_id=".$supId)->select());
			$getComId = $this->dStatistics->getSupplierId();
			$page=new \Think\Page($count,30);
			$this->assign('page',$page->show());
			$this->assign('cname',$companyName);
			$this->assign('count',$count);
			$this->assign('comid',$getComId);
			$this->assign('sum',$data[1]);
			$this->assign('data',$data[0]);
			$this->display();
			exit;
		}
		$this->display();
	}
}
