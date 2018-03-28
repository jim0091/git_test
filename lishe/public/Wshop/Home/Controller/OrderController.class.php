<?php
namespace Home\Controller;
class OrderController extends CommonController {
    public function __construct(){
        parent::__construct();
        if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        } 
        $this->modelOrder=D('Order');   
    }
    //提交订单页面 20160811 开始
    public function order(){
        $cartIds = rtrim(I('get.itemList','','trim'),',');
        if (!$cartIds) {
            $this->error('请选择需要购买的商品！');
        }       
        //获取购物车勾选商品的信息
        $conditionCart = " and c.user_id=".$this->uid." and c.cart_id in(".$cartIds.")";
        $cartList=$this->modelOrder->getCartList($conditionCart);
        if (!$cartList) {
            $this->error('请选择需要购买的商品！');
        }
        foreach ($cartList as $kCart => $vCart) {
            //店铺ids
            $shopIds[$kCart] = $vCart['shop_id'];
            //商品ids
            $itemIds[$kCart] = $vCart['item_id'];
            //商品skus和itemids
            $skuIds[$kCart] = $vCart['sku_id'];
            $skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
            $skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
            $skuidItemid[$kCart]['num'] = $vCart['quantity'];
            if (!empty($vCart['type'])) {
                $type[$vCart['shop_id']] = $vCart['type'];
            }
            if (!empty($vCart['activity_config_id'])) {
                $aitemId[$vCart['shop_id']] = $vCart['activity_config_id'];
            }
        }
        //查询商品上下架状态
        $condStatus['item_id'] = array('in',$itemIds);
        $itemStatus = $this->modelOrder->getItemStatus($condStatus);
        $shopIdStr=implode(',',$shopIds);
        //查询店铺信息
        $where['shop_id']=array('in',$shopIdStr);
        $shopList=$this->modelOrder->getShopList($where);
        if (!$shopList) {
            $this->error('店铺不存在，请重新选择商品！');
        }
        //查询用户默认地址
        $userAddressInfo = $this->modelOrder->getUserAddress($this->uid);
        if (!userAddressInfo) {
            redirect('Order/chgAddressInfo', 5, '请完善您的地址信息，页面跳转中...');
        }
        $jd_ids = str_replace('/','_',trim(strstr($userAddressInfo['area'],':'),":"));
        //调用接口查询库存
        $skuidItemidJson = json_encode($skuidItemid);
        $data = array(
            'itemsSkus' => $skuidItemidJson,
            'area' => $jd_ids
        );
        $url=C('COMMON_API').'Cart/apiCheckCartStock';       
        $return=$this->requestPost($url,$data);
        $return = trim($return, "\xEF\xBB\xBF");//去除BOM头
        $res = json_decode($return,true); 
        if ($res['result'] != 100) {
            $logoData = array(
                'rel_id' =>'1',
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '查询库存接口失败！',
                'log_time' =>time()
            );            
            $this->orderLog($logoData);
            $this->error("接口通讯失败！");
        }
        //查询库存异常
        if($res['errcode'] > 0){            
            $logoData = array(
                'rel_id' =>'1',
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => $res['msg'],
                'log_time' =>time()
            ); 
            $this->orderLog($logoData);
            $this->error($res['msg']);
        }
        $totalCartCash = 0;
        $totalCartPoints = 0;
        $totalCartQuantity = 0;
        foreach ($cartList as $kCart => $vCart) {
            $cartList[$kCart]['price'] = round($vCart['price'],2);
            $cartList[$kCart]['cash'] = round($vCart['cash'],2);
            $cartList[$kCart]['point'] = $vCart['point'];
            $cartList[$kCart]['goodsTotalPrice'] = round($vCart['price'],2) * $vCart['quantity'];
            $shopTotalPrice += $cartList[$kCart]['goodsTotalPrice'];
            if($vCart['cash'] == 0 && $vCart['point'] == 0){
                $totalCartCash += round($vCart['price'] * $vCart['quantity'], 2);
            }else{
                $totalCartCash += round($vCart['cash'] * $vCart['quantity'], 2);
                $totalCartPoints += $vCart['point'] * $vCart['quantity'];
            }
            $totalCartQuantity += $vCart['quantity'];
            foreach ($shopList as $kShop => $vShop) {
                if ($vShop['shop_id'] == $vCart['shop_id']) {
                    $shopList[$kShop]['totalPrice'] += round($vCart['price'],2) * $vCart['quantity'];
                    if($vCart['cash'] == 0 && $vCart['point'] == 0){
                        $shopList[$kShop]['totalCash'] += round($vCart['price'] * $vCart['quantity'], 2);
                    }else{
                        $shopList[$kShop]['totalCash'] += round($vCart['cash'] * $vCart['quantity'], 2);
                        $shopList[$kShop]['totalPoints'] += $vCart['point'] * $vCart['quantity'];
                    }
                    $shopList[$kShop]['totalWeight'] += $vCart['weight'] * $vCart['quantity']; 
                    $shopList[$kShop]['totalNum'] += $vCart['quantity'];
                }
            }
            foreach ($res['data'] as $kSku => $vSku) {
                if ($kSku == $vCart['sku_id']) {
                    $cartList[$kCart]['store'] = $vSku;
                }
            }
            //检查商品是否已经下架
            foreach ($itemStatus as $kStatus => $vStatus) {
                if ($vStatus['item_id'] == $vCart['item_id']) {
                    $cartList[$kCart]['itemStatus'] = $vStatus['approve_status'];
                }
            }

        }
        //店铺配送方式
        $conditionDlytmpl['shop_id']=array('in',$shopIdStr);
        $dlytmplList = $this->modelOrder->getDlytmpl($conditionDlytmpl);
        $addrDefaultIdArr=explode('_',$jd_ids);
        if (!$dlytmplList) {
            $this->error('店铺配送方式不存在，请重新选择商品！');
        }      
        foreach ($dlytmplList as $kdp => $vtp) {
            $dlytmplList[$vtp['shop_id']] = $vtp;
            $shopDlytmpConf[$vtp['shop_id']] = unserialize($vtp['fee_conf']);
        }

        //包邮信息
        $totalCartDelivery="0.00";
        $conditionFreepost['shop_id'] = array('in',$shopIdStr);     
        $freePostList = $this->modelOrder->getFreePost($conditionFreepost);
        foreach ($freePostList as $kfp => $vfp) {
            $shopFreePost[$vfp['shop_id']] = $vfp;
        }
        foreach ($shopList as $kshop => $vshop) {
            $shopFeeAreaTotal = 0; //初始化
            foreach ($shopDlytmpConf[$vshop['shop_id']] as $key => $val) {
                $shopPressAreaArr=array();
                $shopPressAreaArr=explode(',',$val['area']);
                if(!empty($shopPressAreaArr[0])){               
                    if(in_array($addrDefaultIdArr[0],$shopPressAreaArr)){  //省
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                        $this->makeLog('delivery','2: addrDefaultIdArr:'.$addrDefaultIdArr[0].' area:'.$val['area'].' shopId:'.$vshop['shop_id'].' start_fee'.$val['start_fee'].' totalWeight:'.ceil($vshop['totalWeight']).' start_standard:'.$val['start_standard'].' add_fee:'.$val['add_fee']."\r\n");
                    }
                    if(in_array($addrDefaultIdArr[1],$shopPressAreaArr)){ //市  $addrFeeCity
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                        $this->makeLog('delivery','3: addrDefaultIdArr:'.$addrDefaultIdArr[1].' area:'.$val['area'].' shopId:'.$vshop['shop_id'].' start_fee'.$val['start_fee'].' totalWeight:'.ceil($vshop['totalWeight']).' start_standard:'.$val['start_standard'].' add_fee:'.$val['add_fee']."\r\n");
                    }
                    if(in_array($addrDefaultIdArr[2],$shopPressAreaArr)){ //区  $addrFeeArea
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                        $this->makeLog('delivery',"4: addrDefaultIdArr:".$addrDefaultIdArr[2].' area:'.$val['area'].' shopId:'.$vshop['shop_id']." start_fee".$val['start_fee']." totalWeight:".ceil($vshop['totalWeight'])." start_standard:".$val['start_standard']." add_fee:".$val['add_fee']."\r\n");
                    }   
                }else{
                    if(empty($shopFeeAreaTotal)){
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                        $this->makeLog('delivery','1: addrDefaultIdArr:'.$jd_ids.' area:'.$val['area'].' shopId:'.$vshop['shop_id'].' start_fee'.$val['start_fee'].' totalWeight:'.ceil($vshop['totalWeight']).' start_standard:'.$val['start_standard'].' add_fee:'.$val['add_fee']."\r\n");
                    }
                }
            }
            $shopList[$kshop]['delivery'] = $shopFeeAreaTotal;
            $shopList[$kshop]['template_id']=$dlytmplList[$vshop['shop_id']]['template_id'];
            $this->makeLog('delivery','5: shopId:'.$vshop['shop_id'].' delivery:'.$shopFeeAreaTotal."\r\n");
            $totalCartWeight += $vshop['totalWeight'];
            $shopList[$kshop]['postFree']=round($shopFreePost[$vshop['shop_id']]['limit_money'],2);

            if($shopList[$kshop]['postFree'] > $vshop['totalPrice']){
                //$shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'] + $shopList[$kshop]['delivery'];
                $shopList[$kshop]['totalCash'] = $vshop['totalCash'];// + $shopList[$kshop]['delivery'];
                $shopTotalPrice += $shopList[$kshop]['delivery']; //若不包邮，总价加邮费
                $totalCartDelivery += $shopList[$kshop]['delivery'];
            }else{
                //$shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'];
                $shopList[$kshop]['delivery']=0;
            }
        }
        //得到所有收货地址信息 开始 
        $whereAddr=array(
            'user_id'=>$this->uid,
            'def_addr' => 1
        );
        $defAddrInfo=$this->modelOrder->getDefaultAddressInfo($whereAddr);
        if($defAddrInfo){
            $addrArr=explode(':',$defAddrInfo['area']);
            $defAddrInfo['area']=rtrim($addrArr[0],'/');
            $defAddrInfo['areaID']=rtrim($addrArr[1],'/');
            $this->assign('defAddrInfo',$defAddrInfo);
        }
        $this->assign('totalCartCash',$totalCartCash);
        $this->assign('totalCartPoints',$totalCartPoints);
        $this->assign('cartTotalPrice',$shopTotalPrice);
        $this->assign('totalCartQuantity',$totalCartQuantity);
        $this->assign('totalCartDelivery',$totalCartDelivery);
        $this->assign('totalCartWeight',$totalCartWeight);
        $this->assign('shopList',$shopList);
        $this->assign('shopTotalPrice',$shopTotalPrice); //总价格
        $this->assign('cartList',$cartList);  //购物车信息
        $this->assign('type',$type);//订单类型
        $this->assign('aitemId',$aitemId);//活动id
        $this->display('order');
    }

     public function requestPost($url='', $data=array()) {
        if(empty($url) || empty($data)){
            return false;
        }
        $o="";
        foreach($data as $k=>$v){
            $o.="$k=".$v."&";
        }
        $param=substr($o,0,-1);
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
 

    
}