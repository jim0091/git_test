<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[banner配置];								@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Conf/];
 * +----------------------------------------------------------------------
 * |@Name:			[config_banner.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2016-1-1 14:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@Overtime:	2016
 * +----------------------------------------------------------------------
 *  */
return array (
		'APP_Home_Lunbo' =>array(
				'bannerList'=>array(
						//三个轮播图
						0=>array(
								"item_id"=>"53754",
								"figure_id"=>"22",
								"shuffling_id"=> "5",
								"banner_img"=> C('LSAPPAPI_PUBLIC')."Public/Home/Banner/lunbo1.png",
								"the_color"=> null,
								"refer"=> "www.baidu.com",
								"status"=> "1",
								"order_sort"=> "0",
								"is_delete"=> "0",
								"cate_id"=> "1",
								"created_time"=> "1479204376",
								"modifyine_time"=> "1479204376"
								),
						1=>array(
								"item_id"=>"37171",
								"figure_id"=> "999",
								"shuffling_id"=>"5",
								"banner_img"=> C('LSAPPAPI_PUBLIC')."Public/Home/Banner/lunbo2.png",
								"the_color"=> null,
								"refer"=> "www.google.com",
								"status"=> "1",
								"order_sort"=> "0",
								"is_delete"=> "0",
								"cate_id"=> "1",
								"created_time"=> "1479204377",
								"modifyine_time"=>null
								),
						2=>array(
								"item_id"=>"51766",
								"figure_id"=> "999",
								"shuffling_id"=>"5",
								"banner_img"=> C('LSAPPAPI_PUBLIC')."Public/Home/Banner/lunbo3.png",
								"the_color"=> null,
								"refer"=> "www.google.com",
								"status"=> "1",
								"order_sort"=> "0",
								"is_delete"=> "0",
								"cate_id"=> "1",
								"created_time"=> "1479204377",
								"modifyine_time"=>null
						),
					)
				
				),
		
		'APP_Home_Floor' =>array(
				0 =>array(
						'item_id'=>'30511',
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/laxiang.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '30508,8731,8732,30512,30510,30507,12324',
				),
				1 =>array(
						'item_id'=>'54164',
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/meishi1.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '52978,52931,52979,50582,45022,13195,8810',
				)
		),
		
		//分类1：家用电器
		//图片文案：家用电器，智能管家—给你的生活找个伴儿
		'APP_CateConfig1'=>array(
				0 =>array(
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/appbanner1.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '53923,957,11477,43017,53959,53908,47986',
				),
				1 => '53925,53909,53903,53848,47464,37456,37427,35822,32959,11481',
				
				),
		
		//分类2：家居家纺
		//图片文案：家居家纺，居家必备—打造有爱温馨家
		'APP_CateConfig2'=>array(
				0 =>array(
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/appbanner2.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '32570,37174,54989,19817,32572,32562,19928',
				),
				1 => '37196,37193,32561,32560,29844,19842,19833,19822,11649',
		
		),
		
		
		//养生秘笈
		//图片文案：美味滋补，养生秘笈—呵护您与家人的健康
		
		'APP_CateConfig3'=>array(
				0 =>array(
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/appbanner3.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '53756,53744,53757,54230,54227,54186,54169',
				),
				1 => '53753,54167,54229,54228,48520,55881,54176,54174,54173,50678',
		
		),
		
		
		//箱包鞋服
		//图片文案：箱包户外，活力绽放—聚集时尚气质装备
		'APP_CateConfig4'=>array(
				0 =>array(
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/appbanner5.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '53320,35573,35567,35617,19212,41759,48349',
				),
				1 => '3489,40419,19099,19213,29609,19214,39928,29656,29649,35632',
		
		),
		
		
		//分类5：	个护清洁    
		//图片文案：个护清洁，净化助手—精挑细选的亲肤之物
		
		'APP_CateConfig5'=>array(
				0 =>array(
						'index_id' =>'20',
						'name' =>'IOS测试',
						'status' =>'1',
						'link' =>'' ,
						'title' =>'',
						'abstract' =>'',
						'content' =>'',
						'icon' => C('LSAPPAPI_PUBLIC').'Public/Home/Banner/appbanner4.png',
						'gift_pic' => null,
						'order_sort' =>'0',
						'type' =>'3',
						'cate_id' =>'1',
						'is_delete' =>'0',
						'items' => '54974,48373,17953,51055,18036,54998,51032',
				),
				1 => '48370,55559,40834,18002,12580,29664,54966,12587,51033,18044',
		
		),
		
		
		
		
);