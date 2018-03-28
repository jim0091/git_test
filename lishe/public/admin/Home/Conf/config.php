<?php
return array(
	'URL_CASE_INSENSITIVE'=>false,
	//'MODULE_DENY_LIST' => array(),
	'MODULE_ALLOW_LIST' => array('Home'),
	'DEFAULT_MODULE' => 'Home',	
	'SHOW_PAGE_TRACE'=>false,  // 显示页面Trace信息
	'DB_FIELDTYPE_CHECK'=>true,  // 开启字段类型验证
	'UPLOAD_IMAGE_MAX_SIZE'=>5242880,//2Mb,允许上传图片的最大尺寸(单位byte)		
	'LOG_RECORD' => false,//开启日志记录
	'SESSION_OPTIONS'=>array('expire'=>3600*24),
	'COOKIE_DOMAIN' =>'lishe.co',
	'COOKIE_EXPIRE'=> 3600*24,
	'VAR_PAGE'=>'p', //分页变量
	'SESSION_AUTO_START' =>true,
	'CACHE_DEFAULT_TIME'=> 12*3600, //默认缓存时间
	'URL_CASE_INSENSITIVE'=>FALSE, //url不区分大小写
	'ROOT'=>'/admin/',//定义系统的目录
	'API_AOSERVER'=>'http://120.76.159.44:8080/lshe.framework.aoserver/api/',//API接口地址
	'API_AOSERVER_USER' => 'user',
    'API_AOSERVER_PASSWORD' => 'lishe1234',
    'API_AOSERVER_APPKEY' => '1234567890asdfghjklqwertyuoou',
    'API_AOSERVER_KEY' => 'lishe_md5_key_56e057f20f883e',
    
    'API'=>'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',//API接口地址
	'API_KEY'=>'lishe_md5_key_56e057f20f883e',//API Key
	'JD_PRICE_DISCOUNT'=>98,//京东产品折扣，整数
	'JD_PROFIT_RATE'=>15,//京东产品利润率，整数
	'JD_SHOP_ID'=>'10',//京东店铺ID
	'JD_IMG_PATH'=>'http://test.lishe.cn/business/',//保存在数据库字段里的图片地址
	'JD_UPLOAD'=>'./Upload/',//抓取京东图片保存的目录文件
	'SHOP_API'=>'localhost:88/api.php/',
	'COMMON_API'=>'http://test.lishe.cn/api.php/',
	'DIR_LOG'=>'/home/wwwroot/bbc/public/admin/logs/',
	'TMPL_PARSE_STRING' =>array(
		'__ADMIN__'      => __ROOT__.'/admin/admin_2.3.1/',		
		'__LISHE__'      => 'http://localhost:88',		//域名
	),
	//商城导航分类文件路径
	'MALLCAT' => '/home/wwwroot/bbc/public/shop/Home/View/Public/cat.html',  //心意商城
	'COMPANYMALLCAT' => '/home/wwwroot/show/shop/Home/View/Public/cat.html', //企业商城
	'MALLINDEXCAT' => '/home/wwwroot/bbc/public/shop/Home/View/Public/oldCat.html', //商城首页
	'API_SF'=>'http://120.76.159.44:8080/lshe.framework.aoserver/api/sf/',

	'INDEX_TEMP'=>'shop/Home/View/Index/',
	
	'SITE_CFG'=>array(
		'title'=>'礼舍商城后台管理系统',
		'company'=>'深圳市礼舍科技有限公司',
		'icp'=>'ICP备案证书号:粤ICP备12076725号-2 ',
		'telphone'=>'合作电话：400-883-9916',
		'address'=>'广东深圳市南山区高新中路科研9号比克科技大厦2001B'		
	),
	
	//订单黄色预警日期范围
	'YELLOWWWARNINGLIMITTIME' => array(
		'START' => 2,
		'END'   => 5
	),
	//订单红色预警日期范围大于
	'REDWARNINGLIMITTIME' => 5,
	//正常订单日期范围小于
	'NORMALLIMITTIME' => 2,

	'OUTPUT_TAX'=>'0.17',//销项税
);