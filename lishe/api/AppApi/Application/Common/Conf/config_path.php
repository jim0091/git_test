<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[服务路径配置];								@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Conf/];
 * +----------------------------------------------------------------------
 * |@Name:			[config_path.php];	
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
		'LSAPPAPI_DOMAIN_NAME'=>'http://localhost:8082',//本地环境
		//'LSAPPAPI_DOMAIN_NAME'=>'http://api.lishe.cn',//测试环境+生产环境
		
		'LSAPPAPI_URL'=>'http://localhost:8082/index.php',//本地环境——礼舍AppApi接口的绝对路径地址+入口文件
		//'LSAPPAPI_URL'=>'http://api.lishe.cn/appapi.php',//测试环境——礼舍AppApi接口的绝对路径地址+入口文件
		//'LSAPPAPI_URL'=>'http://api.lishe.cn/newapi.php',//生产环境——礼舍AppApi接口的绝对路径地址+入口文件
		
		'LSAPPAPI_PUBLIC' =>'http://localhost:8082/',//本地环境
		//'LSAPPAPI_PUBLIC' =>'http://api.lishe.cn/NewApi/',//测试环境
		//'LSAPPAPI_PUBLIC' =>'http://api.lishe.cn/AppApi/',//生产环境
		'TMPL_PARSE_STRING' => array(//模板替换常量
				'__PUBLIC__'    	=>__ROOT__.'/Public',//本地环境
				//'__PUBLIC__'    	=>__ROOT__.'/NewApi/Public',//测试环境
				//'__PUBLIC__'    	=>__ROOT__.'/AppApi/Public',//生产环境
				
				'__APP__'     		=>'http://localhost:8082/index.php',//本人开发测试使用的地址
				//'__APP__'     	=>'http://api.lishe.cn/appapi.php',//团队开发测试使用的地址
				//'__APP__'    		=>'http://api.lishe.cn/newapi.php',//生产地址，上生产的时候不需要配置，注释或删除此行代码、
				
				'__ADMIN__IMG__'    =>'http://api.lishe.cn/AppAdmin/',
		),
		'ROOT_PUBLIC'=>'./Public/',//本地开发
		//'ROOT_PUBLIC'=>'./NewApi/Public/',//测试环境(团队开发)
		//'ROOT_PUBLIC'=>'./AppApi/Public/',//生产环境、本地开发
		
		
		'IMAGES_PREFIX'=>'http://localhost:8082/Public/',//本地环境
		//'IMAGES_PREFIX'=>'http://api.lishe.cn/NewApi/Public/',//测试环境
		//'IMAGES_PREFIX'=>'http://api.lishe.cn/AppApi/Public/',//生产环境
		
		
		
		
		
		
		
		
		
		
		
		
		
		
);