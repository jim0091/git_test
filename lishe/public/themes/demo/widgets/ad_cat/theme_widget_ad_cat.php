<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
function theme_widget_ad_cat($setting)
{
    
    // 获取一级类目ID
    $lv1_id = $setting ['lv1_cat_id'];
    
    // 获取二级类目ids
    $lv2_ids = app::get ('topc')->rpcCall ('category.cat.get.info', array (
            'parent_id' => $lv1_id, 
            'fields' => 'cat_id' 
    ));
    $lv2_ids = array_column ($lv2_ids, 'cat_id');
    
    // 获取三级类目
    $lv3_catList = app::get ('topc')->rpcCall ('category.cat.get.info', array (
            'parent_id' => $lv2_ids, 
            'fields' => 'cat_id,cat_name' 
    ));
    // echo '<pre>';
    // print_r($lv3_catList);
    // echo '</pre>';
    $data ['list'] = $lv3_catList;
    return $data;
}