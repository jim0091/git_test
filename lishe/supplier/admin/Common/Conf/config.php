<?php
return array(//数据库配置
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '120.76.99.157', // 服务器地址
    'DB_NAME'   => 'b2b2c', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'lishe_bbc@1234', // 密码
    'DB_PORT'   => 3306, // 端口
	'DB_CHARSET' => 'utf8',
	'DB_PREFIX'=>'',
    'ERROR_PAGE' =>'/Public/error.html',

   // 'ERROR_PAGE' =>'http://www.wangzi.love/',
 //   'TMPL_L_DELIM'          => '<{',   //模板引擎普通标签开始标记
 //   'TMPL_R_DELIM'          => '}>',      //模板引擎普通标签结束标记
	'DB_PARAMS'=>array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
);

