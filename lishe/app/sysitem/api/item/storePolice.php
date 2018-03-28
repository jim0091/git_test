<?php
/**
 * 扣减库存
 * item.store.minus
 */
class sysitem_api_item_storePolice{

    public $apiDescription = "库存报警";

    public function getParams()
    {
        $return['params'] = array(
            'store' => ['type'=>'int','valid'=>'required','description'=>'库存数','example'=>'2','default'=>''],
            'shop_id' => ['type'=>'string','valid'=>'','description'=>'店铺id','example'=>'18'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的商品字段集 item_id','example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
            'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
        );
        return $return;
    }

    public function storePolice($params)
    {
        //$skuPolice = app::get('sysconf')->getConf('trade.sku.police');
        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 40;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $max = 1000000;
        if($pageSize >= 1 && $pageSize < 500 && $pageNo >=1 && $pageSize*$pageNo < $max)
        {
            $limit = $pageSize;
            $start = ($pageNo-1)*$limit;
        }
        if($params['fields'])
        {
            $row = $params['fields'];
        }
        else
        {
            $row = '*';
        }

        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "modified_time desc,list_time desc";
        }
        $filter['store'] = $params['store'];
        $filter['shop_id'] = $params['shop_id'];
        $itemList = kernel::single('sysitem_item_store')->getItemListByStore($row,$filter,$start,$limit,$orderBy);
        $itemCount = kernel::single('sysitem_item_store')->getItemCountByStore($filter);
        $data['list'] = $itemList;
        $data['total_found'] = $itemCount;
        return $data;
        //echo '<pre>';print_r($itemList);exit();
    }

}
