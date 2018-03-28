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
	'COOKIE_DOMAIN' =>'lishe.cn',
	'COOKIE_EXPIRE'=> 3600*24,
	'VAR_PAGE'=>'p', //分页变量
	'SESSION_AUTO_START' =>true,
	'CACHE_DEFAULT_TIME'=> 12*3600, //默认缓存时间
	'URL_CASE_INSENSITIVE'=>FALSE, //url不区分大小写
	'ROOT'=>'/business/',//定义系统的目录
	'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/404.html',
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
	'JD_UPLOAD'=>'./Upload/',//抓取京东图片保存的目录文件
	'TOURL'=>'http://test.lishe.cn',  //跳转地址
	'LISHE_URL'=>'http://www.lishe.cn',
	'IMGSRC'=>'http://www.lishe.cn',
	'TMPL_PARSE_STRING' =>array(
		'__ITEM_URL__'      =>'http://www.lishe.cn',
		'__USER__'=>'/user.php',
		'__ZZGX__'=>'http://zzgx.lishe.cn',
	),

);