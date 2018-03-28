<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_list extends topc_controller {

    /**
     * 每页搜索多少个商品
     */
    public $limit = 20;

    /**
     * 最多搜索前100页的商品
     */
    public $maxPages = 100;


    public function index()
    {
        $this->setLayoutFlag('gallery');
        $objLibFilter = kernel::single('topc_item_filter');
        $postdata = input::get();
        $params = $objLibFilter->decode($postdata);
        //echo '<pre>';print_r($params);exit();
        $params['use_platform'] = '0,1';
        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }
        //如果不是从分类进入，并且没有关键字搜索则不能进入列表页
        $params['search_keywords'] = htmlspecialchars(trim($params['search_keywords']));
        /*
        if( empty($params['cat_id']) && empty($params['search_keywords']) )
        {
            return redirect::back();
        }
        */

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        $searchParams['fields'] = 'item_id,shop_id,title,image_default_id,price,promotion';
        //$itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        try
        {
            $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        //检测是否有参加团购活动
        if($itemsList['list'])
        {

            $_tmp_count = count($itemsList['list']);

            for ($i = 0; $i < $_tmp_count; $i++) {
                $itemsList['list'][$i]['price_deposit'] = round($itemsList['list'][$i]['price'] * 100);

                $shop_type = 0;

                if($itemsList['list'][$i]['shop_id'])
                {
                    $objDataShop = kernel::single('sysshop_data_shop');
                    $row ="shop_type";
                    if($params['fields'])
                    {
                        $row = $params['fields'];
                    }
                    $filter = array(
                        'shop_id' => $itemsList['list'][$i]['shop_id'],
                    );

                    //$shopData = $objDataShop->getShopInfo($row,$filter);
                    
                    if ($filter['shop_id']==5) {

                        //if ('self' == $shopData['shop_type']) {
                            // $shop_type = 1;
                            $itemsList['list'][$i]['shop_type'] = 1;
                        //}
                    }
                }

                // $itemsList['list'][$i]['shop_type'] = $shop_type;
            }
            // var_dump($itemsList['list']);
            
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                    $itemsList['list'][$value['item_id']]['activity_price_deposit'] = round($value['activity_price'] * 100);
                    $itemsList['list'][$value['item_id']]['price_deposit'] = round($itemsList['list'][$value['item_id']]['price'] * 100);
                }
            }
        }

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$params);
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;
        $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
        $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数

        //已有的搜索条件
        $tmpFilter = $params;
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //分页
        $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);
        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;
        return $this->page('topc/list/index.html', $pagedata);
    }

    public function integral_average()
    {
        $this->setLayoutFlag('gallery');
        $objLibFilter = kernel::single('topc_item_filter');
        $postdata = input::get();
        $params = $objLibFilter->decode($postdata);
        //echo '<pre>';print_r($params);exit();
        $params['use_platform'] = '0,1';
        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }
        //如果不是从分类进入，并且没有关键字搜索则不能进入列表页
        $params['search_keywords'] = htmlspecialchars(trim($params['search_keywords']));
        /*
        if( empty($params['cat_id']) && empty($params['search_keywords']) )
        {
            return redirect::back();
        }
        */

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        $searchParams['fields'] = 'item_id,shop_id,title,image_default_id,price,promotion';
        //$itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        try
        {
            $itemsList = app::get('topc')->rpcCall('item.search.integral',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        //检测是否有参加团购活动
        if($itemsList['list'])
        {

            $_tmp_count = count($itemsList['list']);

            for ($i = 0; $i < $_tmp_count; $i++) {
                $itemsList['list'][$i]['price_deposit'] = round($itemsList['list'][$i]['price'] * 100);

                $shop_type = 0;

                if($itemsList['list'][$i]['shop_id'])
                {
                    $objDataShop = kernel::single('sysshop_data_shop');
                    $row ="shop_type";
                    if($params['fields'])
                    {
                        $row = $params['fields'];
                    }
                    $filter = array(
                        'shop_id' => $itemsList['list'][$i]['shop_id'],
                    );

                    //$shopData = $objDataShop->getShopInfo($row,$filter);
                    
                    if ($filter['shop_id']==5) {

                        //if ('self' == $shopData['shop_type'] && $filter['shop_id']==5) {
                            // $shop_type = 1;
                            $itemsList['list'][$i]['shop_type'] = 1;
                        //}
                    }
                }

                // $itemsList['list'][$i]['shop_type'] = $shop_type;
            }
            // var_dump($itemsList['list']);
            
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                    $itemsList['list'][$value['item_id']]['activity_price_deposit'] = round($value['activity_price'] * 100);
                    $itemsList['list'][$value['item_id']]['price_deposit'] = round($itemsList['list'][$value['item_id']]['price'] * 100);
                }
            }
        }

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$params);
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;
        $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
        $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数

        //已有的搜索条件
        $tmpFilter = $params;
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //分页
        $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);
        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;
        return $this->page('topc/list/integral_average.html', $pagedata);
    }

    public function integral_average_old()
    {
        $this->setLayoutFlag('gallery');
        $objLibFilter = kernel::single('topc_item_filter');
        $postdata = input::get();
        $params = $objLibFilter->decode($postdata);
        //echo '<pre>';print_r($params);exit();
        $params['use_platform'] = '0,1';
        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }
        //如果不是从分类进入，并且没有关键字搜索则不能进入列表页
        $params['search_keywords'] = htmlspecialchars(trim($params['search_keywords']));
        /*
        if( empty($params['cat_id']) && empty($params['search_keywords']) )
        {
            return redirect::back();
        }
        */

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        $searchParams['fields'] = 'item_id,title,image_default_id,price,promotion';
        //$itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        try
        {
            $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        // deposit 积分均价
        $_integration = $params['integration'];
        $_integration_min = $params['integration_min'];
        $_integration_max = $params['integration_max'];

        $_total_found = 0;
        $_tmp_itemsList_list = array();

        //检测是否有参加团购活动
        if($itemsList['list'])
        {

            $_tmp_count = count($itemsList['list']);

            for ($i = 0; $i < $_tmp_count; $i++) {
                $itemsList['list'][$i]['price_deposit'] = round($itemsList['list'][$i]['price'] * 100);
            }

            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                    $itemsList['list'][$value['item_id']]['activity_price_deposit'] = round($value['activity_price'] * 100);
                    $itemsList['list'][$value['item_id']]['price_deposit'] = round($itemsList['list'][$value['item_id']]['price'] * 100);
                }
            }

            $_total_found = $itemsList['total_found'];

            // for ($i = 0; $i < $_tmp_count; $i++) {
            foreach($itemsList['list'] as $key=>$value) 
            {
                $_tmp_price_deposit = 0;

                $_activity_price_deposit = $itemsList['list'][$value['item_id']]['activity_price_deposit'];

                if (!isset($_activity_price_deposit)) {
                    $_tmp_price_deposit = $itemsList['list'][$value['item_id']]['price_deposit'];
                }
                else {
                    $_tmp_price_deposit = $_activity_price_deposit;
                }

                if (!isset($_integration)) {

                    if (!isset($_integration_max) && !isset($_integration_min)) {

                        if ($_tmp_price_deposit != 500) {

                            $_total_found = $_total_found - 1;

                            continue;                        
                        }

                    }
                    else if (isset($_integration_max) && !isset($_integration_min)) {

                        if ($_tmp_price_deposit > $_integration_max) {

                            $_total_found = $_total_found - 1;

                            continue;
                        }
                    }
                    else if (!isset($_integration_max) && isset($_integration_min)) {

                        if ($_tmp_price_deposit < $_integration_min) {

                            $_total_found = $_total_found - 1;

                            continue;
                        }
                    }
                    else if (isset($_integration_max) && isset($_integration_min)) {

                        if (false != $_integration_max && false != $_integration_min) {

                            if ($_tmp_price_deposit > $_integration_max || $_tmp_price_deposit < $_integration_min) {

                                $_total_found = $_total_found - 1;

                                continue;
                            }
                        }
                        else if (false == $_integration_max && false != $_integration_min) {

                            if ($_tmp_price_deposit < $_integration_min) {

                                $_total_found = $_total_found - 1;

                                continue;
                            }
                        }
                        else if (false != $_integration_max && false == $_integration_min) {

                            if ($_tmp_price_deposit > $_integration_max) {

                                $_total_found = $_total_found - 1;

                                continue;
                            }
                        }
                    }
                }
                else if (isset($_integration)) {

                    $_tmp_min = $_integration - 20;
                    $_tmp_max = $_integration + 20;

                    if ($_tmp_price_deposit > $_tmp_max || $_tmp_price_deposit < $_tmp_min) {

                        $_total_found = $_total_found - 1;

                        continue;
                    }
                }

                // add -> tmp lst
                $_tmp_itemsList_list[$value['item_id']] = $itemsList['list'][$value['item_id']];
            }
        }

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$params);
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;
        /*
        $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
        $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数
        */
        // echo($_total_found);
        $pagedata['items'] = $_tmp_itemsList_list;//根据条件搜索出的商品
        $pagedata['count'] = $_total_found; //根据条件搜索到的总数

        //已有的搜索条件
        $tmpFilter = $params;
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //分页
        $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);
        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;
        return $this->page('topc/list/integral_average.html', $pagedata);
    }

    /**
     * 积分均价页
     */
    /*
    public function integral_average()
    {
        return $this->page('topc/list/integral_average.html');
    }
    */

    /**
     * 迷礼圈首页
     */
    public function circle()
    {
        return $this->page('topc/list/circle.html');
        
        /*
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('活动首页'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('activity_index-circle');
        return $this->page();
        */
    }

    /**
     * 迷礼圈专题页
     */
    public function circle_detail()
    {
        return $this->page('topc/list/circle_detail.html');
    }

    /**
     * 将post过的数据转换为搜索需要的参数
     *
     * @param array $params
     */
    private function __preFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['pages'] >=1 || $params['pages'] <= 100) ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;

        $searchParams['approve_status'] = 'onsale';
        $searchParams['buildExcerpts'] = true;

        if( $searchParams['cat_id'] && is_array($searchParams['cat_id']) )
        {
            $searchParams['cat_id'] = implode(',', $searchParams['cat_id']);
        }

        if( $searchParams['brand_id'] && is_array($searchParams['brand_id']) )
        {
            $searchParams['brand_id'] = implode(',', $searchParams['brand_id']);
        }

        if( $searchParams['prop_index'] && is_array($searchParams['prop_index']) )
        {
            $searchParams['prop_index'] = implode(',', $searchParams['prop_index']);
        }

        //排序
        if( !$params['orderBy'] )
        {
            $params['orderBy'] = 'sold_quantity desc';
        }
        $searchparams['orderBy'] = $params['orderBy'];

        return $searchParams;
    }

    /**
     * 分页处理
     * @param int $current 当前页
     * @return int $total  总页数
     * @return array $filter 查询条件
     *
     * @return $pagers
     */
    private function __pages($current, $total, $filter)
    {
        //处理翻页数据
        $current = ($current || $current <= 100 ) ? $current : 1;
        $filter['pages'] = time();

        if( $total > 0 ) $totalPage = round($total/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_list@index',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

}

