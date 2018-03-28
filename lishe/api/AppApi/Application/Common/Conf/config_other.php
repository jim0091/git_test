<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[其它配置];								@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Conf/];
 * +----------------------------------------------------------------------
 * |@Name:			[config_service.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2016-7-20 11:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@Overtime:	2016
 * +----------------------------------------------------------------------
 *  */
return array (
//'配置项'=>'配置值'
	'ORDER_LIST_CONFIG'	=>	array(
			array(
					'title'=>'待付款',//标题
					'type'=>1,//1为接口，2为web
					'url'=>C('LSAPPAPI_URL').'/Home/User/orderList/status',//跳转接口地址
					'number'=>0,//数量
					'status'=>'WAIT_BUYER_PAY'
			),array(
					'title'=>'代发货',
					'type'=>1,//1为接口，2为web
					'url'=>C('LSAPPAPI_URL').'/Home/User/orderList/status',//跳转接口地址
					'number'=>0,//数量
					'status'=>'WAIT_SELLER_SEND_GOODS'
			),array(
					'title'=>'待收货',
					'type'=>1,//1为接口，2为web
					'url'=>C('LSAPPAPI_URL').'/Home/User/orderList/status',//跳转接口地址
					'number'=>0,
					'status'=>'WAIT_BUYER_CONFIRM_GOODS'
			),array(
					'title'=>'已完成',
					'type'=>1,//1为接口，2为web
					'url'=>C('LSAPPAPI_URL').'/Home/User/orderList/status',//跳转接口地址
					'number'=>0,
					'status'=>'WAIT_COMMENT'
			),array(
					'title'=>'退货/款',
					'type'=>1,//1为接口，2为web
					'url'=>C('LSAPPAPI_URL').'/Home/User/orderList/status',//跳转接口地址
					'number'=>0//数量
			)
	),
	'USERINFO_LIST_CONFIG'	=>	array(
			array(
					'title'=>'企业圈',//标题
					'type'=>1,//1为接口，2为web
					'url'=>'http://google.com',//跳转接口地址
			),array(
					'title'=>'我的资产',
					'type'=>1,//1为接口，2为web
					'url'=>'http://google.com',//跳转接口地址
			),array(
					'title'=>'我的福利',
					'type'=>1,//1为接口，2为web
					'url'=>'http://google.com',//跳转接口地址
			),array(
					'title'=>'地址管理',
					'type'=>1,//1为接口，2为web
					'url'=>'http://google.com',//跳转接口地址
			),array(
					'title'=>'意见和反馈',
					'type'=>1,//1为接口，2为web
					'url'=>'http://google.com',//跳转接口地址
			),array(
					'title'=>'关于礼舍',
					'type'=>1,//1为接口，2为web
					'url'=>'http://google.com',//跳转接口地址
			)
	),
	
	'INDEX_TEIM_LIST'=>array(
			array(
					'title'=>'全部',
					'type'=>1,
					'url'=> C('LSAPPAPI_URL')."/Home/Index/index",
					'item_url'=>C('LSAPPAPI_URL').'/Home/Index/item_list/page/1/count/20/cat/50'
			),array(
					'title'=>'家用电器',
					'type'=>2,
					'url'=>C('LSAPPAPI_URL')."/Home/Index/index/cate_id/2",
					'item_url'=>C('LSAPPAPI_URL').'/Home/Index/index/item_list/page/1/count/20/cat/50'
			),array(
					'title'=>'家居家纺',
					'type'=>2,
					'url'=>C('LSAPPAPI_URL')."/Home/Index/index/cate_id/3",
					'item_url'=>C('LSAPPAPI_URL').'/Home/Index/index/item_list/page/1/count/20/cat/50'
			),array(
					'title'=>'养生秘笈',
					'type'=>2,
					'url'=>C('LSAPPAPI_URL')."/Home/Index/index/cate_id/4",
					'item_url'=>C('LSAPPAPI_URL').'/Home/Index/index/item_list/page/1/count/20/cat/48'
			),array(
					'title'=>'箱包鞋服',
					'type'=>2,
					'url'=>C('LSAPPAPI_URL')."/Home/Index/index/cate_id/5",
					'item_url'=>C('LSAPPAPI_URL').'/Home/Index/index/item_list/page/1/count/20/cat/55'
			),array(
					'title'=>'个护清洁',
					'type'=>2,
					'url'=>C('LSAPPAPI_URL')."/Home/Index/index/cate_id/6",
					'item_url'=>C('LSAPPAPI_URL').'/Home/Index/index/item_list/page/1/count/20/cat/45'
			)
	)
);
