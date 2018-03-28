<?php
class sysuser_api_user_deposit_pay{
    public $apiDescription = "用户付款接口";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'password' => ['type'=>'string','valid'=>'required', 'description'=>'会员预存款支付密码','default'=>'','example'=>''],
            'fee' => ['type'=>'float', 'valid'=>'required|numeric|min:0', 'description'=>'消费金额', 'default'=>'', 'example'=>'100.2'],
            'memo' => ['type'=>'string', 'valid'=>'', 'description'=>'备注信息','default'=>'', 'example'=>'前端消费，订单号：1111111，支付单号：222222222'],
        );
        return $return;
    }

    public function doPay($params){

        //组织数据，userId
        $userId = $params['user_id'];
        if(!$params['user_id'])
        {
            $userId = $params['oauth']['account_id'];
        }

        //确认密码是否正确
        $password = $params['password'];
        $deposit = kernel::single('sysuser_data_deposit_password')->checkPassword($userId, $password);
        if(!$deposit){
            throw new LogicException(app::get('sysuser')->_('支付密码错误！'));
        }

        //组织数据
        $account = $params['account'];
        $fee = $params['fee'];
        $orderNumber = $params['orderNumber'];
        $memo = $params['memo'];

        $deposit = kernel::single('sysuser_data_deposit_deposit')->dedect($userId, $account, $orderNumber, $fee, $memo);

        return ['result'=>true];
    }
}

