<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
header("Content-type:text/html;charset=utf-8");
$host=$_SERVER['HTTP_HOST'];
if($host=='hht.lishe.cn'){
	header("Location:http://hht.lishe.cn/b.php/Haihetao");
}elseif($host=='cnpec.lishe.cn'){
	header("Location:http://www.lishe.cn/business/index.php/Cnpec");
}elseif($host=='gd10086.lishe.cn'){
	header("Location:http://www.lishe.cn/business/index.php/Gd10086");
}elseif($host=='cgn.lishe.cn'){
	header("Location:http://www.lishe.cn/business/index.php/Cgn");
}elseif($host=='jf.lishe.cn'){
	header("Location:http://www.lishe.cn/shop.html");
}elseif($host=='admin.lishe.cn'){
	header("Location:http://www.lishe.cn/admin.php");
}elseif($host=='zzgx.lishe.cn'){
	header("Location:http://www.lishe.cn/shop.html");
}
// 应用入口文件
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);
// 定义应用目录
define('APP_PATH','./Show/');
define('BIND_MODULE', 'Home');
define('__APP__','/index.php');
// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单