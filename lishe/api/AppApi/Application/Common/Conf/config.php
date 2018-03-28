<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[公共配置];								@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Conf/];
 * +----------------------------------------------------------------------
 * |@Name:			[config.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2016-1-14 14:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>		@Overtime:	2016
 * +----------------------------------------------------------------------
 *  */
return array(
	//'配置项'=>'配置值'
	'URL_CASE_INSENSITIVE' =>true,
	'LOAD_EXT_CONFIG' => 'config_db,config_service,config_path,config_params,config_banner,config_other',//加载配置文件
	'URL_MODEL'=>2,			//REWRITE模式,隐藏入口文件 index.php
	'DEFAULT_MODULE'        =>  'Home', // 默认模块
	'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
	'DEFAULT_ACTION'        =>  'index', // 默认操作名称
	'MODULE_DENY_LIST'      =>  array('Common','Runtime'), // 禁止访问的模块列表
	'MODULE_ALLOW_LIST'     =>  array('Index','Home','Businesscircle','CommonApi'),    // 允许访问的模块列表
	'DB_PARAMS'    			=>  array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),//默认的数据库驱动类设置了 字段名强制转换为小写，如果你的数据表字段名采用大小写混合方式必须加上这项配置
);


















