<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toputil_ctl_vcode {

    //验证码组件调用
    public function gen_vcode()
    {
        $key = input::get('key');
        $len = intval(input::get('len'));
        $key = $key ? $key : 'vcode';
        $len = $len ? $len : 4;
        $vcode = kernel::single('base_vcode');
        if( input::get('height') && input::get('width') )
        {
            $vcode->setPicSize(input::get('height'), input::get('width') );
        }
        $vcode->length($len);
        $vcode->verify_key($key);
        $vcode->display();
    }
    
    public function check_vcode()
    {	
    	$remsg=0;
        $key = input::get('key');
        $code = trim(input::get('vcode'));
        if(!empty($key) && !empty($code))
        {
            $vcode = kernel::single('base_vcode');
            if($vcode->verify($key,$code)===TRUE)
            {
				$remsg=1;	
			}
        }
        echo $remsg;
    }

}

