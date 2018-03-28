<?php
return array(
	//'配置项'=>'配置值'
	'SHOP_KEY' =>'lishe',
	'LISHE_URL'=> 'http://test.lishe.cn',
	'COOKIE_DOMAIN' =>'lishe.cn',
	'API_AOSERVER'=>'http://120.76.159.44:8080/lshe.framework.aoserver/api/',//API接口地址
	'API_AOSERVER_USER' => 'user',
    'API_AOSERVER_PASSWORD' => 'lishe1234',
    'API_AOSERVER_APPKEY' => '1234567890asdfghjklqwertyuoou',
    'API_AOSERVER_KEY' => 'lishe_md5_key_56e057f20f883e',
    'API'=>'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',//API接口地址
	'API_KEY'=>'lishe_md5_key_56e057f20f883e',//API Key
	'API_STORE'=>'http://test.lishe.cn/business/index.php/Interface/',//API接口地址
	'JD_SHOP_ID'=>'10',//京东店铺ID

	'COMMON_API' =>'http://test.lishe.cn/api.php/',//新版商城接口
	'JD_SHOP_ID'=>'10',//京东店铺ID	

	//店铺类型
	'FLAG'=>'旗舰店',
	'BRAND'=>'品牌专营店',
	'CAT'=>'类别专营店',
	'SELF'=>'自营店',
	'store'=>'普通店铺',

	'TMPL_PARSE_STRING' =>array(
		'__FUANNA__'      => __ROOT__.'/shop/Home/View/Activity/fuanna/',
		'__VIEW__'=> '/shop/Home/View/',
		'__USER__'=>'/user.php',
		'__LISHE_URL__'=>'http://test.lishe.cn'	
	),
	'DIR_LOG'=>'/home/wwwroot/bbc/public/shop/logs/',

);
