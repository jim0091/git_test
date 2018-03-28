<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_item_activity( &$setting )
{
    if( !$setting['activity'] ) return $setting;

    $params['id'] = implode(',',$setting['activity']);
    // print($params['id']);
    $params['status'] = 'agree';
    $params['fields'] = "title,activity_id,item_id,item_default_image,activity_price,price,activity_tag";
    $data = app::get('desktop')->rpcCall('promotion.activity.item.list',$params);
    $setting['data'] = $data['list'];

    if ($setting['data']) {

        $_tmp_count = count($setting['data']);
        // var_dump($setting['activity']);
        // var_dump($setting['activity'][0]);
        for ($i = 0; $i < $_tmp_count; $i++) {

	    	$activity_id = $setting['data'][$i]['activity_id'];

	    	try
	        {
	            $cativityData = app::get('topc')->rpcCall('promotion.activity.info',array('activity_id'=>$activity_id,'fields'=>'start_time,end_time'));
	        }
	        catch(Exception $e)
	        {
	            $msg = $e->getMessage();
	            //echo '<pre>';print_r($msg);exit();
	            // return $this->splash('error',null,$msg);
	        }

	        $_start_time  = $cativityData['start_time'];
	        $_end_time  = $cativityData['end_time'];
	        $_now_time  = time();

	        if ($_start_time && $_end_time) {

	        	if ($_now_time < $_start_time) {

		        	// 获取当前-开始时间 -> 总秒数
	        		$setting['data'][$i]['times'] = round(($_start_time - $_now_time));
		        }
		        else if ($_now_time > $_start_time && $_now_time < $_end_time) {

		        	// 获取当前-结束时间 -> 总秒数
	        		$setting['data'][$i]['times'] = round(($_end_time - $_now_time));
		        }
		        else {

		        }
	        }
	        
	        $setting['data'][$i]['start_time']  = $_start_time;
	        $setting['data'][$i]['end_time']  = $_end_time;
	        $setting['data'][$i]['now_time']  = $_now_time;
	    }
    }

    // var_dump($setting['data']);

    return $setting;
}
