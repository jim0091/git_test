<?php
return array(//数据库配置
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'bbc', // 数据库名
    'DB_USER'   => 'bbc', // 用户名
    'DB_PWD'    => 'bbc@1234', // 密码
    'DB_PORT'   => 3306, // 端口
	'DB_CHARSET' => 'utf8',
	'DB_PREFIX'=>'',
	'DB_PARAMS'=>array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
);
?>
