<?php
namespace Home\Model;
use Think\Model;
class StatModel extends Model{

    //构造方法
    public function __construct(){
            //订单主表
            $this->modelTrade = M('systrade_trade');
            //订单子表
            $this->modelOrder = M('systrade_order');
            //商品表
            $this->modelItem = M('sysitem_item');
            //供应商表
            $this->modelSupp = M('supplier_user');
            //日程统计表
            $this->modeldaily = M('stat_daily');
            //商品总统计表
            $this->modelGoods = M('stat_goods');
            //公司统计表
            $this->modelCom = M('stat_company');
            //供应商统计表
            $this->modelSupplier = M('stat_supplier');
            //商品销售数量表
            $this->modelItemCount = M('sysitem_item_count');
            //公司信息配置表
            $this->modelCompany = M('company_config');
    }

     //日常统计
    public function getDaily($start=''){
        $date = date("Y-m-d",$start);
        echo "正在执行".$date;
        $end = $start+86400;
        //条件
        $condition='created_time>='.$start.' and created_time<'.$end ;
        //订单数 金额总数
        $basic = $this->getBasicInfo($condition);
        //商品总价 花费 利润
        $deep = $this->getDeepInfo($condition);
        
        //判断记录是否存在
        if($this->isStay('modeldaily','stat_time = '.$start)){
            $data['stat_count']=$basic['Count'];
            $data['stat_paycount']=$basic['payedCount'];
            $data['stat_refundcount']=$basic['refundCount'];
            $data['stat_total_fee']=$basic['total'];
            $data['stat_payed_fee']=$basic['payed'];
            $data['stat_refund_fee']=$basic['refund'];
            $data['stat_valid_fee']=$basic['payed']-$basic['refund'];
            $data['stat_profit']=$deep['profit'];
            $data['stat_good_fee']=$deep['goodsFee'];
            $data['stat_cost']=$deep['cost'];
            $data['updata_time']=time();
            var_dump($data);
            $this->modeldaily->where('stat_time = '.$start)->save($data);
        }else{
            $data['stat_count']=$basic['Count'];
            $data['stat_paycount']=$basic['payedCount'];
            $data['stat_refundcount']=$basic['refundCount'];
            $data['stat_total_fee']=$basic['total'];
            $data['stat_payed_fee']=$basic['payed'];
            $data['stat_refund_fee']=$basic['refund'];
            $data['stat_valid_fee']=$basic['payed']-$basic['refund'];
            $data['stat_profit']=$deep['profit'];
            $data['stat_good_fee']=$deep['goodsFee'];
            $data['stat_cost']=$deep['cost'];
            $data['create_time']=time();
            $data['stat_date']=$date;
            $data['stat_time']=$start;
            var_dump($data);
            $this->modeldaily->add($data);
        }
        //返回下一天的时间
        return $end;         
    }


    //企业日常统计
    public function getCompanyDaily($start=''){
        $date = date("Y-m-d",$start);
        $end= $start+86400;
        //条件
        $condition='created_time>='.$start.' and created_time<'.$end ;
        $getStay = $this->modelTrade->field('com_id')->where($condition)->select();
        //当天没有任何企业购买
        if (empty($getStay)) {
            return $end;
        }
        foreach ($getStay as $key => $value) {
            //筛选企业id大于0的
            if($value['com_id']>0){
            $comid[]=$value['com_id'];
            }
        }
        //去除重复值
        $comid = array_unique($comid);
        //在搜到的企业id中遍历
        foreach ($comid as $key => $v){
            $condition = 'created_time>='.$start.' and created_time<'.$end.' and com_id = '.$v;
            $companyName = $this->modelCompany->field('com_name')->where('com_id = '.$v)->select();
            $basic = $this->getBasicInfo($condition);
            if($basic['Count'] == 0){
                continue;
            }
            $deep = $this->getDeepInfo($condition);            
            $dition = 'stat_time = '.$start.' and com_id = '.$v;        
            //判断记录是否存在
            if($this->isStay('modelCom',$dition)){
                $data['stat_count']=$basic['Count'];
                $data['stat_paycount']=$basic['payedCount'];
                $data['stat_refundcount']=$basic['refundCount'];
                $data['stat_total_fee']=$basic['total'];
                $data['stat_payed_fee']=$basic['payed'];
                $data['stat_refund_fee']=$basic['refund'];
                $data['stat_valid_fee']=$basic['payed']-$basic['refund'];
                $data['stat_profit']=$deep['profit'];
                $data['stat_good_fee']=$deep['goodsFee'];
                $data['stat_cost']=$deep['cost'];
                $data['updata_time']=time();
                var_dump($data);                
                $this->modelCom->where('stat_time = '.$start.' and com_id = '.$v)->save($data);                
            }else{
                $data['com_id']=$v;
                $data['com_name']=$companyName[0]['com_name'];
                $data['stat_count']=$basic['Count'];
                $data['stat_paycount']=$basic['payedCount'];
                $data['stat_refundcount']=$basic['refundCount'];
                $data['stat_total_fee']=$basic['total'];
                $data['stat_payed_fee']=$basic['payed'];
                $data['stat_refund_fee']=$basic['refund'];
                $data['stat_valid_fee']=$basic['payed']-$basic['refund'];
                $data['stat_profit']=$deep['profit'];
                $data['stat_good_fee']=$deep['goodsFee'];
                $data['stat_cost']=$deep['cost'];
                $data['stat_date']=$date;
                $data['stat_time']=$start;
                $data['create_time']=time();     
                var_dump($data);             
                $this->modelCom->add($data);
            }            
        }
        return $end;
    }

    //供应商日常统计
    public function getSupplierDaily($start=''){
        
        $date = date("Y-m-d",$start);
        echo "当前处理的是".$date;
        $end = $start+86400;
        //条件
        $condition='created_time>='.$start.' and created_time<'.$end ;

        //找出当天 支付过的订单号
        $getTrade = $this->modelTrade->field('tid')->where('payed_fee>0 AND '.$condition)->select();
        //没有记录直接跳转
        if(empty($getTrade)){
            return $end;
        }
        foreach ($getTrade as $key => $value){
            $tid[]=$value['tid'];
        }
        $getSupplier_id = $this->modelOrder->where('tid IN ('.implode(',', $tid).')')->select();

        foreach ($getSupplier_id as $key => $value){
            if($value['supplier_id']>0){
                $sid[] = $value['supplier_id'];   
            }                    
        }
        
        //没有记录直接跳转
        if(empty($sid)){
            return $end;
        }
        $sid = array_unique($sid);
        foreach ($sid as $key => $v) {
            $getAll = $this->modelOrder->where("supplier_id = ".$v." and tid IN (" .implode(',', $tid). ")")->field('sum(price*num) as total,sum(price*num - cost_price*num) as profit,sum(cost_price*num) as cost,sum(num) as num')->select();
            if($v=='0' || $v==''){
                $getSupplierName[0]['company_name'] = '自营商店';
            }else{
                $getSupplierName = $this->modelSupp->field('company_name')->where('supplier_id = '.$v)->select();
            }
            //订单总数
            $count=Count($this->modelOrder->where('supplier_id = '.$v.' and tid IN ('.implode(',', $tid).')')->select());
            //下单总金额
            $tradeCount=$getAll[0]['total'];
            //利润与商品总价
            $profit=$getAll[0]['profit'];
            //成本
            $cost = $getAll[0]['cost'];
            //数量
            $num = $getAll[0]['num'];
            //判断记录是否存在
            if($this->isStay('modelSupplier',"stat_date = '".$date."' and supplier_id = '".$v."'")){
                $data['supplier_id']=$v;
                $data['supplier_name']=$getSupplierName[0]['company_name'];
                $data['stat_count']=$count;
                $data['stat_tradecount']=$getAll[0]['total'];
                $data['stat_cost']=$getAll[0]['cost'];
                $data['stat_profix']=$getAll[0]['profit'];
                $data['stat_saleNum']=$num = $getAll[0]['num'];
                $data['updata_time']=time();
                var_dump($data);
                $this->modelSupplier->where('stat_time = '.$start.' and supplier_id = '.$v)->save($data);
            }else{                
                $data['supplier_id']=$v;
                $data['supplier_name']=$getSupplierName[0]['company_name'];
                $data['stat_count']=$count;
                $data['stat_tradecount']=$getAll[0]['total'];
                $data['stat_cost']=$getAll[0]['cost'];
                $data['stat_profix']=$getAll[0]['profit'];
                $data['stat_saleNum']=$num = $getAll[0]['num'];
                $data['create_time']=time();
                $data['stat_date']=$date;
                $data['stat_time'] = $start;
                var_dump($data);
                $this->modelSupplier->add($data); 
            }    
        }        
        return $end;
    }

    public function getGoods($start = 0){
        //获取全部商品id
        $getItemId = $this->modelItem->field('item_id')->select();
        //二维转一维
        foreach ($getItemId as $key => $value){
            $itemid[]=$value['item_id'];
        }
        //定义跳转
        $jump =0;
        $ToEnd = 0 ;
        //for循环每一个商品
        for($i=$start;$i<count($itemid);$i++){
            if($jump>4){
                return array('do' => $i,'count' => count($itemid));;
            }
            //获取总数
            $basic = $this->getGoodsBasic('item_id = '.$itemid[$i],'item_id');
            $deep = $this->getGoodsDeep('item_id = '.$itemid[$i],'item_id');

            $item_name = $this->modelItem->field('title')->where('item_id = '.$itemid[$i])->select();
            echo '已将《'.$item_name[0]['title'].'》统计完毕<br>';
            if($this->isStay('modelGoods','item_id = '.$itemid[$i])){
                $data['title']=$item_name[0]['title'];
                $data['sold_quantity']=isset($basic['sold_quantity']) ? $basic['sold_quantity'] : 0;
                $data['buy_count']=isset($basic['buy_count']) ? $basic['buy_count'] : 0;
                $data['stat_count']=$basic['Count'];
                $data['stat_paycount']=$basic['payedCount'];
                $data['stat_aftersale']=$basic['refundCount'];
                $data['stat_total']=$basic['total'];
                $data['stat_payed']=$basic['payed'];
                $data['stat_refund']=$basic['refund'];
                $data['stat_money']=$basic['payed']-$basic['refund'];
                $data['stat_profit']=$deep['profit'];
                $data['stat_good_fee']=$deep['goodsFee'];
                $data['stat_cost']=$deep['cost'];
                $data['updata_time']=time();
                $this->modelGoods->where('item_id = '.$itemid[$i])->save($data);
            }else{
                $data['item_id']=$itemid[$i];
                $data['title']=$item_name[0]['title'];
                $data['sold_quantity']=isset($basic['sold_quantity']) ? $basic['sold_quantity'] : 0;
                $data['buy_count']=isset($basic['buy_count']) ? $basic['buy_count'] : 0;
                $data['stat_count']=$basic['Count'];
                $data['stat_paycount']=$basic['payedCount'];
                $data['stat_aftersale']=$basic['refundCount'];
                $data['stat_total']=$basic['total'];
                $data['stat_payed']=$basic['payed'];
                $data['stat_refund']=$basic['refund'];
                $data['stat_money']=$basic['payed']-$basic['refund'];
                $data['stat_profit']=$deep['profit'];
                $data['stat_good_fee']=$deep['goodsFee'];
                $data['stat_cost']=$deep['cost'];
                $data['create_time']=time();               
                $this->modelGoods->add($data);
            }         
            $jump++;
        }
    }

    //判断是否存在
    public function isStay($table,$condition){
        return count($this->$table->where($condition)->select());
    }
    //获取Trade基本相关信息
    public function getBasicInfo($condition = '1=1',$find = 'tid'){
        $tid = count($this->modelTrade->field($find)->where($condition)->select());
        if($tid==0){
            return array('Count'=>0,'payedCount'=>0,'refundCount'=>0,'total'=>0,'payed'=>0,'refund'=>0);
        }
        $payedCount = count($this->modelTrade->where('payed_fee>0 and '.$condition)->field($find)->select());
        $refundCount = count($this->modelTrade->where('refund_fee>0 and '.$condition)->field($find)->select());
        $sum = $this->modelTrade->field('sum(payment) as total,sum(payed_fee) as payed,sum(refund_fee) as refund')->where($condition)->select();
        
        $data['Count'] = $tid;
        $data['payedCount'] = $payedCount;
        $data['refundCount'] = $refundCount;
        $data['total'] = $sum[0]['total'];
        $data['payed'] = $sum[0]['payed'];
        $data['refund'] = $sum[0]['refund'];

        return $data;
    }

    //获取 Trade&Order 利润成本信息
    public function getDeepInfo($condition = '1=1'){
        $getTrade = $this->modelTrade->field('tid')->where('payed_fee>0 AND '.$condition)->select();
        //找不到记录集直接返回
        if(empty($getTrade)){
            return array('profit'=>0,'goodsFee'=>0,'cost'=>0);
        }
        foreach ($getTrade as $key => $value){
            $tid[]=$value['tid'];
        }
        //获取modelOrder 中与供应商结算的金额
        $getOrder = $this->modelOrder->where('tid IN ('.implode(',', $tid).')')->select();
        foreach ($getOrder as $key => $value){
            $profit += ($value['price']-$value['cost_price'])*$value['num'];
            $goods += $value['price'] * $value['num'];
            $cost += $value['cost_price'] * $value['num'];
        }
            return array('profit'=>$profit,'goodsFee'=>$goods,'cost'=>$cost);
    }

    //获取Trade基本相关信息
    public function getGoodsBasic($condition = '1=1',$find = 'tid'){
        $tid = count($this->modelOrder->field($find)->where($condition)->select());
        if($tid==0){
            return array('Count'=>0,'payedCount'=>0,'refundCount'=>0,'total'=>0,'payed'=>0,'refund'=>0);
        }
        $payedCount = count($this->modelOrder->where('payment>0 AND '.$condition)->field('item_id')->select());
        $refundCount = count($this->modelOrder->where('refund_fee>0 AND '.$condition)->field('item_id')->select());        
        $sum = $this->modelOrder->field('sum(price) as total,sum(payment) as payed,sum(refund_fee) as refund')->where($condition)->select();
        $quantity = $this->modelOrder->field('sum(num) as sold_quantity')->where($condition)->select();
        $buy_count = $this->modelOrder->field('sum(num) as count')->where('payment>0 AND '.$condition)->select();
        
        $data['Count'] = $tid;
        $data['payedCount'] = $payedCount;
        $data['refundCount'] = $refundCount;
        $data['total'] = $sum[0]['total'];
        $data['payed'] = $sum[0]['payed'];
        $data['refund'] = $sum[0]['refund'];
        $data['sold_quantity']=$quantity[0]['sold_quantity'];
        $data['buy_count']=$buy_count[0]['count'];

        return $data;
    }

    //获取 Trade&Order 利润成本信息
    public function getGoodsDeep($condition = '1=1'){ 
        $getTrade = $this->modelOrder->field('price,cost_price,num')->where('payment>0 AND '.$condition)->select();      
        //找不到记录集直接返回
        if(empty($getTrade))
        {
            return array('profit'=>0,'goodsFee'=>0,'cost'=>0);
        }
        foreach ($getTrade as $key => $value){
            $profit += ($value['price']-$value['cost_price'])*$value['num'];
            $goods += $value['price'] * $value['num'];
            $cost += $value['cost_price'] * $value['num'];
        }
            return array('profit'=>$profit,'goodsFee'=>$goods,'cost'=>$cost);
    }


}