<?php
function theme_widget_custom_suspension(&$setting) {

    $objItem = kernel::single('sysitem_item_info');
    $rows = 'item_id,title,price,image_default_id';
    $setting['item'] = $objItem->getItemInfo(array('item_id'=>$setting['item_select']), $rows);
    $setting['defaultImg'] = app::get('image')->getConf('image.set');

    // 查询余额 
    $userId = userAuth::id();

    //$setting['is_login'] = 0;

    /*	echo('Happy Life~');
    if ($userId) {
    	echo('Happy Life');
    	//$setting['is_login'] = 1;

	    $deposit = app::get('topc')->rpcCall('user.deposit.getInfo', ['user_id'=>$userId, 'with_log'=>'false']);

	    if ($deposit) {
		    $tmp_deposit = $deposit['deposit'];
		    $deposit['deposit'] = $tmp_deposit * 100;
		    $deposit['price'] = $tmp_deposit;

		    $setting['deposit'] = $deposit;
		}
    }*/

    return $setting;
}

?>
