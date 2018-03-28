<?php  
/**
  +----------------------------------------------------------------------------------------
 *  CategoryModel
  +----------------------------------------------------------------------------------------
 * @author   	林建立 
 * @description 统计
 * 2017/2/8
 * 
  +-----------------------------------------------------------------------------------------
 */
 namespace Home\Model;
 use Think\Model;
 class StatisticsModel extends CommonModel{
	public function __construct(){
		//日程统计表
        $this->modelDaily = M('stat_daily');
        //商品总统计表
        $this->modelGoods = M('stat_goods');
        //公司统计表
        $this->modelCom = M('stat_company');
        //供应商统计表
        $this->modelSupplier = M('stat_supplier');
        //企业配置表
        $this->modelCompany = M('company_config');
        //供应商配置表
        $this->modelSuppUser = M('supplier_user');
        //商品表
        $this->modelItem = M('sysitem_item');
        //商品销售数量
        $this->modelItemCount = M('sysitem_item_count');
	}
	
	public function getMonthStat()	{
		//SELECT FROM_UNIXTIME(stat_time,'%Y%m') as months,sum(stat_count)as '订单数',sum(stat_paycount) as '支付订单数',sum(stat_aftersale) as '退款订单数',sum(stat_total_fee) as '订单总金额',sum(stat_payed_fee) as '总付款金额',sum(stat_refund_fee) as '总退款金额',sum(stat_cost) as '成本',sum(stat_profit) as '利润' FROM stat_daily GROUP BY months
		//按照stat_time分月查询
		$data = $this->modelDaily->field("FROM_UNIXTIME(stat_time,'%Y/%m') as months,sum(stat_count)as 'count',sum(stat_paycount) as 'paycount',sum(stat_aftersale) as 'aftersale',sum(stat_total_fee) as 'totalFee',sum(stat_payed_fee) as 'payedFee',sum(stat_refund_fee) as 'refundFee',sum(stat_cost) as 'cost',sum(stat_profit) as 'profit'")->group('months')->order('months desc')->select();
		//用sum结合
		$sum = $this->modelDaily->field("sum(stat_count)as 'count',sum(stat_paycount) as 'paycount',sum(stat_aftersale) as 'aftersale',sum(stat_total_fee) as 'totalFee',sum(stat_payed_fee) as 'payedFee',sum(stat_refund_fee) as 'refundFee',sum(stat_cost) as 'cost',sum(stat_profit) as 'profit'")->select();
		return array($data,$sum);
	}

	public function getDailyStat($page = 1 , $start = '' , $end = '')	{
		//判断起始日期
		if($start != '' && $end != ''){
			$condition = 'stat_time >='.strtotime($start).' and stat_time <='.strtotime($end);
			$snum = count($this->modelDaily->where($condition)->select());
			$data = $this->modelDaily->page($page,30)->where($condition)->order('stat_date desc')->select();
			return array($data,$snum);
		}
		//直接查询 30条记录
		$data = $this->modelDaily->page($page,30)->order('stat_date desc')->select();
		return $data;
	}

	public function datatables($table,$where = array()){
		
		$order_num = $where['order'][0]['column'];
		$order_by = $where['order'][0]['dir'];
		$seacrh = $where['search']['value'];
		$order = $where['columns'][$order_num]['data']." ".$order_by;
		if($seacrh != null){
			$select = $this->$table->page(0,$length)->order($order)->where("concat(title,item_id) like '%".$seacrh."%'")->select();
		}else{
			$select = $this->$table->page(0,$length)->order($order)->select();
		}
		$item_count = count($this->$table->field('item_id')->select());
		$data['draw']=$where['draw'];
		$data['recordsTotal']=$item_count;
		$data['recordsFiltered']=count($select);
		$data['data']=$select;
		echo json_encode($data);
		exit;
	}

	public function getGoodsStat($page = 1,$obj = '')	{
		if($obj ==''){
			$obj = 'stat_payed';
		}
		//取出商品统计表item_id字段 根据支付金额进行排序
		$item_id = $this->modelGoods->field('item_id')->page($page,30)->order($obj.' desc')->select();
		//取出商品统计表所有数据 根据支付金额进行排序
		$item_info = $this->modelGoods->page($page,30)->order($obj.' desc')->select();
		//遍历item_id数据 二维 => 一维
		foreach ($item_id as $key => $value){
            $id[]=$value['item_id'];
        }
        //遍历后的一维数组 in 到 sysitem_item 中找出 商品的title
        $item_name = $this->modelItem->field('title')->where('item_id in ('.implode(',', $id).')')->select();
        foreach($id as $key => $value){        	
        	$item_num = $this->modelItemCount->field('item_id,buy_count')->where('item_id = '.$value)->select();
        	if(empty($item_num)){
        		$ary[] = array('item_id'=>$value,'buy_count'=>'0');
        		continue;
        	}
        	$ary[] = array('item_id' => $value,'buy_count'=>$item_num[0]['buy_count']);
        }
        //$item_sale = $this->modelItemCount->field('item_id,buy_count')->where('item_id in ('.implode(',', $id).')')->select();
		//把搜索到的内容添加到item_info数组中
		foreach ($item_info as $key => $value){
			$data[] = array_merge($item_info[$key] , array('title' => $item_name[$key]['title'],'buy_count' => $ary[$key]['buy_count']));
		}
		/*
		数据检验
		var_dump($data);
		exit;*/
		//返回
		return $data;
	}

	public function getCompanyStat($page = 1,$com_id = '1=1'){
		if($com_id != '1=1'){
			$condition = 'com_id ='.$com_id;
		}
		$sum = $this->modelCom->field("sum(stat_count)as 'count',sum(stat_paycount) as 'paycount',sum(stat_aftersale) as 'aftersale',sum(stat_total_fee) as 'total',sum(stat_payed_fee) as 'payed',sum(stat_refund_fee) as 'refund',sum(stat_cost) as 'cost',sum(stat_profit) as 'profit'")->where($condition)->select();
		$data = $this->modelCom->page($page,30)->where($condition)->order('stat_date desc')->select();
		return array($data,$sum);
	}

	public function getSupplierStat($page = 1,$sid = '1=1'){
		if($sid != '1=1'){
			$condition = 'supplier_id ='.$sid;
		}
		$sum = $this->modelSupplier->field("sum(stat_count)as 'count',sum(stat_tradecount) as 'total',sum(stat_cost) as 'cost',sum(stat_profix) as 'profit',sum(stat_saleNum) as 'nums'")->where($condition)->select();
		$data = $this->modelSupplier->page($page,30)->where($condition)->order('stat_date desc')->select();
		return array($data,$sum);
	}

	public function getCompanyId(){
		$com_id = $this->modelCom->field('com_id')->group('com_id')->select();
		foreach ($com_id as $key => $value){
            $cid[]=$value['com_id'];
        }
        return $data = $this->modelCompany->field('com_id,com_name')->where('com_id IN ('.implode(',', $cid).')')->select();
	}

	public function getSupplierId(){
		$supplier_id = $this->modelSupplier->field('supplier_id')->group('supplier_id')->select();
		foreach ($supplier_id as $key => $value){
            $sid[]=$value['supplier_id'];
        }
        return $data = $this->modelSuppUser->field('supplier_id,company_name')->where('supplier_id IN ('.implode(',', $sid).')')->select();
	}
 }