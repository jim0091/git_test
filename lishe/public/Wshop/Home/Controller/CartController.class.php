<?php
namespace Home\Controller;
class CartController extends CommonController {
    public function __construct(){
    parent::__construct();
        if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }
        $this->cartModel = D('Cart');    
    }
    //日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    } 

    // 购物车页面 
    public function cart(){
        //获取购物车信息
        $conditionCart = "and c.user_id=".$this->uid;        
        $cartList=$this->cartModel->getCartList($conditionCart);
        if (!$cartList) {
            $this->display('cart');
            exit();         
        }
        foreach ($cartList as $kCart => $vCart) {
            //店铺ids
            $shopIds[$kCart] = $vCart['shop_id'];
            //商品ids
            $itemIds[$kCart] = $vCart['item_id'];
            //商品skus和itemids
            //$skuIds[$kCart] = $vCart['sku_id'];
            $aconfigIds[$kCart] = $vCart['activity_config_id'];
            $skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
            $skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
            $skuidItemid[$kCart]['num'] = $vCart['quantity'];
        } 
        //查询活动限购数量
        unset($condition);
        unset($field);
        $condition['activity_config_id'] = array('in',implode(',',$aconfigIds));
        $field = "activity_config_id,max_join_num";
        $aconfigList = $this->cartModel->getAconfigList($condition,$field); 
        $shopIdStr=implode(',',$shopIds);
        //查询店铺信息
        $where['shop_id']=array('in',$shopIdStr);
        $shopInfo=$this->cartModel->getShopList($where);
        //查询商品上下架状态
        $condStatus['item_id'] = array('in',$itemIds);
        $itemStatus = $this->cartModel->getItemStatus($condStatus);
        //查询用户默认地址
        $userAddressInfo = $this->cartModel->getUserAddress($this->uid);
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

        foreach($cartList as $keyCart=>$valCart){
            $cartList[$keyCart]['price']=round($valCart['price'],2);
            $cartList[$keyCart]['cash']=round($valCart['cash'],2);
            $cartList[$keyCart]['point']=round($valCart['point'],2);
            if($valCart['cash'] > 0 && $valCart['point'] > 0){
                $cartList[$keyCart]['payType'] = 1;
            }elseif($valCart['cash'] == 0 && $valCart['point'] == 0){
                $cartList[$keyCart]['payType'] = 2;
            }elseif($valCart['cash'] > 0){
                $cartList[$keyCart]['payType'] = 3;
            }else{
                $cartList[$keyCart]['payType'] = 4;
            }

            $cartList[$keyCart]['goodsTotalPrice'] = round($valCart['price'],2) * $valCart['quantity'];
            //检查商品是否已经下架
            foreach ($itemStatus as $kStatus => $vStatus) {
                if ($vStatus['item_id'] == $valCart['item_id']) {
                    $cartList[$keyCart]['itemStatus'] = $vStatus['approve_status'];
                }
            }
            if ($valCart['disable'] == 1) {
                $cartList[$keyCart]['itemStatus'] = 'instock';
            }
            foreach ($res['data'] as $kSku => $vSku) {
                if ($kSku == $valCart['sku_id']) {
                    $cartList[$keyCart]['isFreez'] = $vSku;
                }
            }
            //检查限购数量
            if ($aconfigList) {
                foreach ($aconfigList as $key => $value) {
                    if ($value['activity_config_id'] == $valCart['activity_config_id']) {
                        if ($value['max_join_num'] < $valCart['quantity'] && $value['max_join_num'] != 0) {
                            $cartList[$keyCart]['maxNum'] = 1;
                        }
                    }
                }               
            }
            $totalPrice += $cartInfo[$keyCart]['goodsTotalPrice'];
        }
        $this->assign('totalPrice',$totalPrice); //总价格
        $this->assign('cartInfo',$cartList);  //购物车信息
        $this->assign('shopInfo',$shopInfo);
        $this->display('cart');
    }

    //删除购物车商品
    public function deleteCartId(){
        $cartId=I('post.cartId',0,'intval');
        if(!empty($cartId)){
            $condition=array(
                'cart_id'=>$cartId,
                'user_id'=>$this->uid
                );
            $delRes=$this->cartModel->delCartItems($condition);
            if($delRes){
                echo json_encode(array(1,'删除成功！'));
                exit;
            }else{
                echo json_encode(array(1,'删除失败，请重试！'));
                exit;
            }
        }else{
            echo json_encode(array(0,'删除失败，请重试！'));
            exit();
        }
    }

    public function getSelectPrice(){
        $cartIds=rtrim(I('get.cartIds','','trim'),',');
        if (!$cartIds) {
            echo json_encode(array(0,"系统繁忙，请重试！"));
            exit();
        }
        $condition = " and c.user_id =".$this->uid." and c.cart_id IN (".$cartIds.")";
        $cartList = $this->cartModel->getCartList($condition);
        if (!$cartList) {
            echo json_encode(array(0,"系统繁忙，请重试！"));
            exit();            
        }
        /*foreach($cartList as $key=>$value){
            $selectTotalNum += $value['quantity'];
            $selectTotalPrice += round($value['price'],2) * intval($value['quantity'])*100;
        } */
        //混合支付 by zhangxiaobo 20170405
        $selectTotalNum = 0;
        $selectTotalPrice = 0;
        $selectTotalPoints = 0;
        foreach($cartList as $key=>$value){
            $selectTotalNum += $value['quantity'];
            if($value['cash'] > 0 && $value['point'] > 0){
                $selectTotalPrice += round($value['cash'] * $value['quantity'], 2);
                $selectTotalPoints += $value['point'] * $value['quantity'];
            }elseif($value['cash'] == 0 && $value['point'] == 0){
                $selectTotalPrice += round($value['price'] * $value['quantity'], 2);
            }elseif($value['cash'] > 0){
                $selectTotalPrice += round($value['cash'] * $value['quantity'], 2);
            }else{
                $selectTotalPoints += $value['point'] * $value['quantity'];
            }
        }
        echo json_encode(array(1,$selectTotalNum,round($selectTotalPrice,2),$selectTotalPoints));
        exit;
    }

    // 购物车中货品数量的增加/减少 start 20160718 
    public function opCart(){
        $op = I('get.op','','trim');
        $cartId = I('get.cartId',0,'intval'); 
        $num = I('get.num',0,'intval'); //当前货品的购买数量
        $sku_id=I('get.sku_id',0,'intval');
        $conditionCart=array(
          'cart_id'=>$cartId,
          'user_id'=>$this->uid
          );

        $cartItemInfo = $this->cartModel->getCartIteminfo(" and cart_id = ".$cartId." and user_id=".$this->uid);

        $addressInfo = $this->cartModel->getDefaultAddressInfo(array("user_id"=>$this->uid,"def_addr"=>1));
        if (!$addressInfo) {
            echo  json_encode(array(0,'请先添加默认地址！'));
            exit;
        }
        $jd_ids = str_replace('/','_',trim(strstr($addressInfo['area'],':'),":"));
        if($op=='dec'){    //表货品数目减少
            $cartNumDec=$this->cartModel->decCartNum($conditionCart);
            if ($cartItemInfo['shop_id'] == C('JD_SHOP_ID')) {         
                $decNum = $num-1;
                $getUrl = C('COMMON_API')."Jd/checkJdStock/item_id/".$cartItemInfo['item_id']."/jd_ids/".$jd_ids."/num/".$decNum;
                $jdSku = file_get_contents($getUrl);
                $jdSku = trim($jdSku, "\xEF\xBB\xBF");//去除BOM头 
                if ($jdSku != 33) {
                    echo  json_encode(array(1,'库存不足！',34));
                    exit;
                }else{
                    echo  json_encode(array(1,'库存充足！',33));
                    exit;
                }
            }else{
                $noFreez = $cartItemInfo['store']-$cartItemInfo['freez'];
                //注意判断的时候购买数量要减1
                if($num - 1 > $noFreez || $noFreez < 1){
                    echo  json_encode(array(1,'库存不足！',34));
                    exit;
                }else{
                    echo  json_encode(array(1,'库存充足！',33));
                    exit;
                }
            }

        }elseif($op=='inc'){  //表货品数目增加  
            //查询活动商品购买限制
            $skuInfo = $this->cartModel->getSkuInfo('sku_id='.$sku_id,'sku_id,activity_config_id');
            unset($condition);
            unset($field);
            $condition['activity_config_id'] = $skuInfo['activity_config_id'];
            $field = 'activity_config_id,max_join_num';
            $aconfigInfo = $this->cartModel->getAconfigInfo($condition,$field);
            if ($num+1 > $aconfigInfo['max_join_num'] && $aconfigInfo['max_join_num'] != 0) {
                echo  json_encode(array(1,'超过限制购买数量！',34));
                exit;
            }       
            if ($cartItemInfo['shop_id'] == C('JD_SHOP_ID')) {         
                $addNum = $num+1;
                $getUrl = C('COMMON_API')."Jd/checkJdStock/item_id/".$cartItemInfo['item_id']."/jd_ids/".$jd_ids."/num/".$addNum;
                $jdSku = file_get_contents($getUrl);
                $jdSku = trim($jdSku, "\xEF\xBB\xBF");//去除BOM头
                if ($jdSku != 33) {
                    //库存不足
                    echo  json_encode(array(1,'库存不足！',34));
                    exit;
                }else{
                    echo  json_encode(array(1,'库存充足！',33));       
                }
            }else{
                $noFreez = $cartItemInfo['store']-$cartItemInfo['freez'];
                //注意判断的时候购买数量要加1
                if($num + 1 > $noFreez || $noFreez < 1){
                    echo  json_encode(array(1,'库存不足！',34));
                    exit;
                }else{
                    echo  json_encode(array(1,'库存充足！',33));                    
                }
            }            
            $cartNumDec=$this->cartModel->addCartNum($conditionCart); 
        }   
    }
}