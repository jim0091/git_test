<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_raiders extends topc_controller
{

    public function __construct($app)
    {
        parent::__construct();
        $this->app = $app;
        $this->setLayoutFlag('raiders');

    }

    //店铺首页
    public function index()
    {
        $raidersId = input::get();
		$raidersId=$raidersId['raidersId'];
		if(!empty($raidersId)){
			$db = app::get('topc')->database();
			$res = $db->executeQuery('select * from raiders_config where id ='.$raidersId.' and isdelete = 0')->fetch();
			$result = $db->executeQuery('select * from raiders_item_config where raiders_id='.$raidersId.' and isdelete = 0')->fetchAll();
			$pagedata['head'] = $res;
			$pagedata['items'] = $result;
	        return $this->page('topc/topic/01.html', $pagedata);
			
		}
		
    }

}


