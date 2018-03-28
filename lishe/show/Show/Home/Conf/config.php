<?php
return array(//系统配置
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
	'ROOT'=>'/',//定义系统的目录
    'API'=>'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',//API接口地址
	'API_KEY'=>'lishe_md5_key_56e057f20f883e',//API Key
//	'IMGSRC'=>'http://www.lishe.cn',  //测试服务器
	'IMGSRC'=>'http://localhost:88',  //本地
	'API_STORE'=>'http://www.lishe.cc/business/index.php/Interface/',//API接口地址
	'IMGSRC'=>'http://www.lishe.cn',  //测试服务器
//	'IMGSRC'=>'http://localhost:88',  //本地
);

?>