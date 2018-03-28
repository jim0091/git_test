<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
// 应用公共文件
if (!function_exists('apiSuccess')) {
    /**
     * 接口成功返回数据
     */
    function apiSuccess($errcode=0,$msg ='SUCCESS',$data=[] )
    {
        empty($data) && $data = new \stdClass();
        $result = [
            'ret'=>0,
            'errcode'=>$errcode,
            'errstr'  => $msg,
            'data' =>$data,
        ];
        \think\Log::record($result,'request');
        return \think\Response::create($result, 'json', 200);
    }
}

if (!function_exists('apiFail')) {
    /**
     * 接口请求失败返回数据
     */
    function apiFail($errcode=10008,$msg ='Fail',$data=[])
    {
        empty($data) && $data = new \stdClass();
        $result = [
            'ret'=>1,
            'errcode'=>$errcode,
            'errstr'  => $msg,
            'data' =>$data,
        ];
        \think\Log::record($result,'request');
        return \think\Response::create($result, 'json', 200);
    }
}

if (!function_exists('httpGet')) {
    /**
     * curl get请求
     * @param $url
     * @return mixed
     */
    function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
}

if(!function_exists('getNonceStr')) {
    /**
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return string 产生的随机字符串
     */
    function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}