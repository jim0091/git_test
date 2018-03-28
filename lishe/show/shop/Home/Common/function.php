<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: awen 
// +----------------------------------------------------------------------


if (!defined('USER_COMMON_COMMON_PHP')) {    
    
    //根据三级分类id获取分类详细信息
    function getCatInfo($catId,$level){
        $catModel = M('syscategory_cat');
        if(empty($catId)) {    
            return array();
        }
		$newCatInfo = array();
		$field = 'cat_id,parent_id,cat_name';
    	$map = array(
			'cat_id' => $catId
		);		
        $newCatInfo[$level] = $catModel->where($map)->field($field)->find();
        if (!empty($newCatInfo[$level]['parent_id'])) {
        	$map = array(
				'cat_id' => $newCatInfo[$level]['parent_id']
			);
	        $newCatInfo[$level-1]  = $catModel->where($map)->field($field)->find();
        }
        if (!empty($newCatInfo[$level-1]['parent_id'])) {
         	$map = array(
				'cat_id' => $newCatInfo[$level-1]['parent_id']
			);       	
	        $newCatInfo[$level-2]  = $catModel->where($map)->field($field)->find(); 
        } 
		ksort($newCatInfo,1);
        if (!$newCatInfo) {
            return array();
        }else{
            return array_filter($newCatInfo);
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

}

//防止重复定义
define('USER_COMMON_COMMON_PHP', 1);