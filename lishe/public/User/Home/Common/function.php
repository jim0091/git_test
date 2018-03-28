<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: awen 
// +----------------------------------------------------------------------


if (!defined('USER_COMMON_COMMON_PHP')) {    

    /**
    *+----------------------------------------------------------
    * 字符串截取，支持中文和其他编码
    *+----------------------------------------------------------
    * @static
    * @access public
    *+----------------------------------------------------------
    * @param string $str 需要转换的字符串
    * @param string $start 开始位置
    * @param string $length 截取长度
    * @param string $charset 编码格式
    * @param string $suffix 截断显示字符
    *+----------------------------------------------------------
    * @return string
    *+----------------------------------------------------------
    */
    function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
        if(function_exists("mb_substr")){
            if($suffix){
                return mb_substr($str, $start, $length, $charset)."...";
            }else{
                return mb_substr($str, $start, $length, $charset);
            }
        }elseif(function_exists('iconv_substr')) {
            if($suffix){
                return iconv_substr($str,$start,$length,$charset)."...";
            }else{
                return iconv_substr($str,$start,$length,$charset);
            }
        }
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix){ 
            return $slice."...";
        }else{
            return $slice;
        }
    }
    
    //根据三级分类id获取分类详细信息
    function getCatInfo($catId){
        $catModel = M('syscategory_cat');
        if(empty($catId)) {    
            return false;
        }
        $catInfoS = $catModel->where('cat_id ='.$catId)->field('cat_id,parent_id,cat_name')->find();
        if (empty($catInfoS['parent_id'])) {
            return false;
        }
        $catInfo = $catModel->where('cat_id ='.$catInfoS['parent_id'])->field('cat_id,parent_id,cat_name')->find();
        if (empty($catInfo['parent_id'])) {
            return false;
        }  
        $catInfoB = $catModel->where('cat_id ='.$catInfo['parent_id'])->field('cat_id,parent_id,cat_name')->find(); 
        $newCatInfo = array_merge_recursive(array($catInfoB),array($catInfo),array($catInfoS));         
        if (!$newCatInfo) {
            return false;
        }else{
            return $newCatInfo;
        }
    }

    //根据id获取品牌信息
    function getBrandInfo($brandId){
        if (empty($brandId)) {
            return false;
        }
        $brandInfo = M("syscategory_brand")->where('brand_id ='.$brandId)->find();
        if (!$brandInfo) {
            return false;
        }
        return $brandInfo;
    }
    //根据库存id获取库存数量
    function getItemSkuStore($itemSkuId){
        if (empty($itemSkuId)) {
            return false;
        }
        $itemSkuStoreInfo = M('sysitem_sku_store')->where('sku_id ='.$itemSkuId)->find();
        if (!$itemSkuStoreInfo) {
            return false;
        }
        return $itemSkuStoreInfo;
    }
    //根据库存id获取库存表信息
    function getItemSku($itemSkuId){
        if (empty($itemSkuId)) {
            return false;
        }
        $itemSkuInfo = M('sysitem_sku')->where('sku_id ='.$itemSkuId)->find();
        if (!$itemSkuInfo) {
            return false;
        }
        return $itemSkuInfo;
    }
    //检查商品状态
    function getItemStatus($itemId){  
        if (empty($itemId)) {
            return false;
        }      
        $itemStatus = M('sysitem_item_status')->where('item_id='.$itemId)->find();
        if ($itemStatus['approve_status'] == "instock") {
            return 0;
        }else{
            return 1;
        }
    }
    //检查商品是否已收藏(1已经收藏)
    function getUserFav($itemId,$userId){
        if (empty($itemId) || empty($userId)) {
            return 0;
        }
        $userFavInfo = M('sysuser_user_fav')->where('item_id ='.$itemId." and user_id=".$userId)->find();
        if ($userFavInfo) {
            return 1;
        }else{
            return 0;
        }
    }
    //根据shop_id查询店铺信息
    function getShopInfo($shopId){
        if (empty($shopId)) {
            return 0;
        }
        $shopInfo = M('sysshop_shop')->where("shop_id =".$shopId)->find();
        if ($shopInfo) {
            return $shopInfo;
        }else{
            return 0;
        }
    }
    //检查店铺是否收藏
    function getShopFav($shopId,$userId){
        if (empty($shopId) || empty($userId)) {
            return 0;
        }
        $shopFavInfo = M('sysuser_shop_fav')->where('shop_id ='.$shopId." and user_id=".$userId)->find();
        if ($shopFavInfo) {
            return 1;
        }else{
            return 0;
        }
    }
    //根据表和id查询详细信息
    function getTableRow($model,$idName,$id,$field=""){
        if (empty($model) || empty($id) || empty($idName)) {
            return 0;
        }
        $tableInfo = M($model)->where($idName ."=". $id)->field($field)->find();
        if ($tableInfo) {
            return $tableInfo;
        }else{
            return 0;
        }

    }
    //根据条件和表查询数据集
    function getTableList($model,$where,$field="",$order=""){
        if (empty($model) || empty($where)) {
            return 0;
        }
        $tableList = M($model)->where($where)->field($field)->order($order)->select();
        if ($tableList) {
            return $tableList;
        }else{
            return 0;
        }
    }
    //根据提交统计咨询表数据
    function getConsulCount($id,$consulType){
        if (empty($id) || empty($consulType)) {
            return 0;
        }
        if ($consulType == 'all') {
            $condition = 'be_reply_id = 0 and item_id ='.$id;
        }else{
            $condition = 'be_reply_id = 0 and item_id ='.$id.' and consultation_type ="'.$consulType.'"';
        }        
        $count = M('sysrate_consultation')->where($condition)->count();
        if ($count) {
            return $count;
        }else{
            return 0;
        }

    }
    //校验京东商品库存jd_ids：格式（19_1607_3155）
    function checkJdStock($itemId,$jd_ids){
        if (empty($itemId) || empty($jd_ids)) {
            return 0;
        }
        $where['item_id'] = $itemId;
        $res = M('sysitem_item')->field('jd_sku,item_id')->where($where)->find();
        if($res['jd_sku']>0){       
            $sku[]=array(
                'id'=>$res['item_id'],
                'num'=>1
            );
            $data=array(
                'items'=>json_encode($sku),
                'area'=>trim($jd_ids,'_')
            );
            $result = requestPost(C('COMMON_API').'Jd/checkJdStock',$data);
            $retArr = json_decode($result,true);
            $stock=array('status'=>$retArr['data'][$itemId],'msg'=>$retArr['msg']);
        }else{
            $stock=array('status'=>33,'msg'=>'有货');
        }       
        return $stock['status'];
    }

    //模拟提交
    function requestPost($url='', $data=array()) {
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

    //校验地址，防止京东商品无法下单
    function checkJdAddress($jdIds){
        if (empty($jdIds)) {
            return false;
        }
        $addressArr =  explode("/",trim($jdIds,'/'));
        if (!is_array($addressArr)) {
            return false;
        }
        if (count($addressArr) < 4) {
            $count = M('site_area')->where('jd_pid='.$addressArr[2])->find();
            if ($count) {
                //有四级地址，返回false
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }        
    }
    //查看购物车数量
    function getCartCount($userId){
        if (empty($userId)) {
            return 0;
        }
        $cartModel = M('systrade_cart');
        $cartCount = $cartModel->where('user_id ='.$userId)->count();
        if ($cartCount) {
            return $cartCount;
        }else{
            return 0;
        }

    }
    
    /**
     * 生成api的sign签名或校对sign签名<br/>
     * 当op='CREATE'时，可以直接生成签名参数<br/>
     * 当op='CHECK'时，此参数为值应该包含sign字段和生成sign的其他字段用于校验，一般可以直接使用$_POST
     * @param array $param 签名参数
     * @param string $option CREATE，生成签名；CHECK，签名校验
     * @return boolean
     */
    function apiSign($param = array(), $option  = 'CREATE'){
    	if(empty($param) || !is_array($param)){
    		return false;
    	}
    	
    	$targetSign = '';
    	if($option != 'CREATE' && $option != 'CHECK'){
    		return false;
    	}
    	//是否是校验操作	
    	if($option == 'CHECK'){
    		//判断sign字段
    		if(empty($param['sign'])){
    			return false;
    		}
    		//获取sign字段
    		$targetSign = $param['sign'];
    		unset($param['sign']);
    	}
    	//排序，按ASCII升序
    	ksort($param, SORT_REGULAR);
    	$signStr = '';
    	//拼接成 a=123&b=123格式
    	array_walk($param, function($value, $key) use (&$signStr){
    		$signStr .= "$key=$value&";
    	});
    	if(empty($signStr)){
    		return false;
    	}
    	//删除最后一个‘&’，并加上key
    	$signStr = rtrim($signStr, '&') . C('API_KEY');
    	//返回值
    	$sign = md5($signStr);
    	if($option == 'CREATE'){
    		return $sign;
    	}else if($option == 'CHECK'){
    		return $sign == $targetSign; //返回校验结果
    	}else{
    		return false;
    	}
    }
    //退换货状态
    function orderStatusLastReturn($serviceStatus){
        switch($serviceStatus){
            case "NO_APPLY":
                $serviceStatus="无操作";
                break;  
            case "CANCEL_APPLY":
                $serviceStatus="取消售后申请";
                break;                  
            case "WAIT_EARLY_PROCESS":
                $serviceStatus="待初审";
                break;                      
            case "WAIT_PROCESS":
                $serviceStatus="待审核";
                break;
            case "SELLER_REFUSE":
                $serviceStatus="商家拒绝";
                break;
            case "REFUND_PROCESS":
                $serviceStatus="待退款";
                break;              
            case "SUCCESS":
                $serviceStatus="已完成";
                break;
            case "WAIT_BUYER_SEND_GOODS":
                $serviceStatus="等待用户回寄";
                break;              
            case "WAIT_SELLER_CONFIRM_GOODS":
                $serviceStatus="等待商家收货";
                break;  
            case "WAIT_REFUND":
                $serviceStatus="待退款(商家已收到货)";
                break;                  
            case "SELLER_SEND_GOODS":
                $serviceStatus="商家已回寄";
                break; 
            case 'TRADE_CLOSED_BY_SYSTEM':
                $serviceStatus="已取消（系统）";
                break; 
            case 'TRADE_CLOSED_BY_USER':
                $serviceStatus="已取消（用户）";
                break; 
            case 'TRADE_CLOSED_BY_ADMIN':
                $serviceStatus="已取消（管理员）";
                break; 
            case 'WAIT_BUYER_PAY':
                $serviceStatus="待付款";
                break; 
            case 'WAIT_SELLER_SEND_GOODS':
                $serviceStatus="待发货";
                break; 
            case 'WAIT_BUYER_CONFIRM_GOODS':
                $serviceStatus="待收货";
                break; 
            case 'WAIT_COMMENT':
                $serviceStatus="待评论";
                break; 
            case 'TRADE_FINISHED':
                $serviceStatus="已完成";
                break; 
            case 'IN_STOCK':
                $serviceStatus="备货中";
                break;    
        }       
        return $serviceStatus;
    } 

}

//防止重复定义
define('USER_COMMON_COMMON_PHP', 1);