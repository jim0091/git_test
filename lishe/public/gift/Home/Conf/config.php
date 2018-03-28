<?php
return array(
	//'配置项'=>'配置值'
	'URL_MODEL'            => 1, //URL模式
	/* SESSION 和 COOKIE 配置 */
	'SESSION_PREFIX' => 'gift_', //session前缀
	'COOKIE_PREFIX'  => 'gift_', // Cookie前缀 避免冲突
	/*API配置*/
	'API'=>'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',//API接口地址
	'API_KEY'=>'lishe_md5_key_56e057f20f883e',//API Key
	'API_URL'=>'http://test.lishe.cn/api.php',//API接口地址
	/*加密密钥*/
	'KEY'=>'sadg@yu2~6*54dg&#a%^',
	/* 模板相关配置 */
	'TMPL_PARSE_STRING' => array(
			//'__STATIC__' => __ROOT__ . '/Public/static',
			'__IMG__'    => '/public/gift/images',
			'__CSS__'    => '/public/gift/css',
			'__JS__'     => '/public/gift/js',
	),
	//微信配置
	'WEIXIN_APPID' => 'wx76ffa4c15fe721bf',
	'WEIXIN_APPSECRET' => '329f7fcc56eeba22e2b14f29fdd9e807',
);