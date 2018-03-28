<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


return array(
    /**
    |--------------------------------------------------------------------------
    | ShopEx企业账号站点
    |--------------------------------------------------------------------------
    |
    | ShopEx企业账号站点
    |
    */
    'shop_user_enterprise'=>'https://passport.shopex.cn/index.php',
  //'shop_user_enterprise'=>'http://passport.ex-sandbox.com/index.php',

    /**
    |--------------------------------------------------------------------------
    | ShopEx企业账号相关API接口
    |--------------------------------------------------------------------------
    |
    | ShopEx企业账号相关API接口
    |
    */
    'shop_user_enterprise_api'=>'https://passport.shopex.cn/api.php',
  //'shop_user_enterprise_api'=>'http://passport.ex-sandbox.com/api.php',

    /**
    |--------------------------------------------------------------------------
    | ShopEx License标准接口
    |--------------------------------------------------------------------------
    |
    | 包含注册, 申请node_id, info查看等
    |
    */
    'license_center'=>'https://service.shopex.cn/', //证书的正式外网地址.
  //'license_center'=>'http://service.ex-sandbox.com/', //证书的正式外网地址.

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix 节点关系绑定接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 节点关系绑定接口
    |
    */
    'matrix_relation_url' => 'https://www.matrix.ecos.shopex.cn/',
  //'matrix_relation_url' => 'http://sws.ex-sandbox.com/',

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix 异步通信接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 异步通信接口
    |
    */
    'matrix_async_url'=>'https://matrix.ecos.shopex.cn/async',

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix 同步通信接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 同步通信接口
    |
    */
    'matrix_realtime_url'=>'https://matrix.ecos.shopex.cn/sync',

    /**
    |--------------------------------------------------------------------------
    | ShopEx Matrix Service接口
    |--------------------------------------------------------------------------
    |
    | ShopEx Matrix 同步通信接口
    |
    */
    'matrix_service_url'=>'https://matrix.ecos.shopex.cn/service',

    /**
    |--------------------------------------------------------------------------
    | ShopEx sms open接口(外网正式)
    |--------------------------------------------------------------------------
    |
    | ShopEx 短信签名
    |
     */
    'sms_api' => 'https://openapi.shopex.cn/api',

    /**
    |--------------------------------------------------------------------------
    | ShopEx sms open接口(内网测试)
    |--------------------------------------------------------------------------
    |
    | ShopEx 短信签名
    |
    */
    'sms_debug' => "",  //开启测试地址的设置
    'sms_sandbox_api' => 'https://openapi.shopex.cn/api-sandbox',
    
    'cookie_domain' =>'.lishe.cn',
	/**
    |--------------------------------------------------------------------------
    | 一企一舍接口地址 赵尊杰 2016-0601
    |--------------------------------------------------------------------------
    |
    | 
    |
     */
    'lishe_company_url' => 'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',
    'lishe_aoserver_url' => 'http://120.76.159.44:8080/lshe.framework.aoserver/api/',
    'lishe_aoserver_user' => 'user',
    'lishe_aoserver_password' => 'lishe1234',
    'lishe_aoserver_appkey' => '1234567890asdfghjklqwertyuoou',
    'lishe_aoserver_key' => 'lishe_md5_key_56e057f20f883e',
    'lishe_shop_url' => 'http://test.lishe.cn/business/index.php/',
    'company_key' => 'lishe_md5_key_56e057f20f883e',
    'company_empLogin'=>'mallUser/empLogin',//用户登录方法
    'company_register'=>'mallUser/register',//用户注册方法
    'company_isRegister'=>'mallUser/isRegister',//用户状态验证方法
    'company_activateUser'=>'mallUser/activateUser',//用户激活方法
    'company_updatePass'=>'mallUser/updatePass',//修改密码方法
    'company_payOrder'=>'mallPoints/payOrder',//积分支付方法
    'company_refundOrder'=>'mallPoints/refundOrder',//订单退款方法
    'company_recharge'=>'mallPoints/recharge',//积分充值方法
);
