<?php
return array(

	//'MODULE_DENY_LIST' => array(),
	'MODULE_ALLOW_LIST' => array('Home'),
	'DEFAULT_MODULE' => 'Home',
	'SHOW_PAGE_TRACE'=>false,  // 显示页面Trace信息
	'DB_FIELDTYPE_CHECK'=>true,  // 开启字段类型验证
	'UPLOAD_IMAGE_MAX_SIZE'=>5242880,//2Mb,允许上传图片的最大尺寸(单位byte)
	'LOG_RECORD' => false,//开启日志记录
//	'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/404.html',
	'SESSION_AUTO_START' =>true,
	'API_SF'=>'http://120.76.159.44:8080/lshe.framework.aoserver/api/sf/',//顺丰接口地址
	'DIR_LOG'=>'/home/wwwroot/bbc/public/business/logs/',
	'URL_CASE_INSENSITIVE'=>FALSE //url不区分大小写


	
	

);