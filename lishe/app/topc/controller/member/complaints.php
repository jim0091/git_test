<?php
class topc_ctl_member_complaints extends topc_ctl_member {

    /*
     * 显示订单投诉页面
     */
    public function complaintsView()
    {
        $oid = input::get('oid');
        //物流单号判断
        $validator = validator::make(
            [$oid],
            ['numeric']
        );
        if ($validator->fails())
        {
            return $this->splash('error',null,'格式不对!');
        }
        $pagedata['oid'] = $oid;
        $this->action_view = "complaints/view.html";
        return $this->output($pagedata);
    }

    /**
     * 提交订单投诉
     */
    public function complaintsCi()
    {
        try
        {
            $data = input::get();
            $validator = validator::make(
                [$data['complaints_type'],$data['tel'],$data['content']],
                ['required','required|mobile','required'],
                ['投诉类型不能为空!','联系方式不能为空!|联系方式格式不对','问题描述不能为空!']
            );
            $validator->newFails();
            $data['image_url'] = implode(',', $data['image_url']);
            $result = app::get('topc')->rpcCall('trade.order.complaints.create', $data,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topc_ctl_member_trade@tradeList');

        $msg = '投诉提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function detail()
    {
        $data['oid'] = input::get('oid');
        $data['fields'] = 'complaints_id,shop_id,tid,oid,status,tel,image_url,complaints_type,content,memo,buyer_close_reasons,created_time,orders.title,orders.item_id';
        try
        {
            $pagedata = app::get('topc')->rpcCall('trade.order.complaints.info', $data,'buyer');
        }
        catch( LogicException $e)
        {
            $msg = $e->getMessage();
        }

        if( $pagedata['image_url'] )
        {
            $pagedata['image_url'] = explode(',',$pagedata['image_url']);
        }

        $this->action_view = "complaints/detail.html";
        return $this->output($pagedata);
    }

    public function closeComplaints()
    {

        $data['complaints_id'] = input::get('complaints_id');
        $data['buyer_close_reasons'] = input::get('buyer_close_reasons');

        $oid = input::get('oid');

        try
        {
            $pagedata = app::get('topc')->rpcCall('trade.order.complaints.buyer.close', $data,'buyer');
        }
        catch( LogicException $e )
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $url = url::action('topc_ctl_member_complaints@detail',['oid'=>$oid]);
        $msg = '订单投诉撤销成功';
        return $this->splash('success',$url,$msg,true);
    }

}
