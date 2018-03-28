<?php
class sysuser_api_user_deposit_setPassword{
    public $apiDescription = "修改预存款密码的接口（不需要旧密码）";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'会员id','default'=>'','example'=>''],
            'password' => ['type'=>'string','valid'=>'required', 'description'=>'会员预存款支付密码','default'=>'','example'=>'']
        );
        return $return;
    }

    public function setPassword($params)
    {

        $userId = $params['user_id'];
        $password = $params['password'];

        $deposit = kernel::single('sysuser_data_deposit_password')->setPassword($userId, $password);

        return ['result'=>true];
    }
}

