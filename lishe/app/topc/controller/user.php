<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_user extends topc_controller
{
    public function index() 
    {
        return $this->page('topc/user/index.html');
    }

    public function getPublicKey()
    {

        $data=array(); 
        $data=http_build_query($data);
        
        $opts=array(   
            'http'=>array(
            'method'=>'POST',
            'header'=>"Content-type: application/x-www-form-urlencoded/r/n",
            "Content-Length: ".strlen($data)."/r/n",
            'content'=>$data
            )
        );
        
        $context=stream_context_create($opts);

        $html=file_get_contents('http://120.76.43.74:8080/lshe.framework.protocol.http/api/sys/getPublicKey',false,$context);

        $json = json_encode($html);
        $jsonArr = json_decode($json);

        // header('Content-type:text/json'); 
        header('Access-Control-Allow-Origin:*');
        echo $jsonArr;
    }

    public function mallLogin()
    {   

        $params = input::get();
        // $params = $objLibFilter->decode($postdata);
        // echo '<pre>';print_r($params);exit();
        $encrypt_data = $params['encrypt_data'];
        $encrypt_source = $params['encrypt_source'];
        $encrypt_flag = $params['encrypt_flag'];
        $format = $params['format'];

        $login_name = $params['login_name'];
        $login_pwd = $params['login_pwd'];

        $data=array('login_name'=>$login_name, 'login_pwd'=>$login_pwd, 'encrypt_data'=>$encrypt_data, 'encrypt_source'=>$encrypt_source, 'encrypt_flag'=>$encrypt_flag, 'format'=>$format); 
        // $data=array();
        $data=http_build_query($data);
        
        $opts=array(   
            'http'=>array(
            'method'=>'POST',
            // 'header'=>"Content-type: application/x-www-form-urlencoded/r/n",
            // "Content-Length: ".strlen($data)."/r/n",
            'content'=>$data
            )
        );
        $context=stream_context_create($opts);

        // $tmpUrl = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/empLogin?encrypt_data=' . $encrypt_data . '&encrypt_source=' . $encrypt_source . '&encrypt_flag=' . $encrypt_flag . '&format=' . $format . '&login_name=' . $login_name . '&login_pwd=' . $login_pwd;
        // $html=file_get_contents('http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/empLogin?login_name='.$login_name.'&login_pwd='.$login_pwd,false,$context);
        $html=file_get_contents('http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/empLogin',false,$context);

        $json = json_encode($html);
        $jsonArr = json_decode($json);

        // header('Content-type:text/json');

        echo $json;
    }
}
