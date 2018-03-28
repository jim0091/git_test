<?php
return array(
	/*业务逻辑相关配置*/
	'VSHOP'=> 5, //虚拟店铺，礼包交易trade表虚拟shop_id		
	//'配置项'=>'配置值'
	'KEY' => '^faoQ%P6p#V+@~HmAK47$', // 加密密钥，切记不可改动，否则数据无法解密
	/*API配置*/
	'API'=>'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',//API接口地址
	'API_KEY'=>'lishe_md5_key_56e057f20f883e',//API Key
	'API_URL'=>'http://test.lishe.cn/api.php',//API接口地址
	/* 模板相关配置 这里最好先解决入口文件问题*/
	'TMPL_PARSE_STRING' => array(
			'__STATIC__' => '/public/statics',
			'__IMG__'    => '/public/bag/images',
			'__CSS__'    => '/public/bag/css',
			'__JS__'     => '/public/bag/js',
	),
);