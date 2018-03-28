<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_deposit_deposit
{

    /**
     * 获取会员的预存款数值
     *
     * @params userId int 会员id
     *
     * @return deposit fload 会员的预存款
     *
     */
    public function get($userId)
    {
        $deposit = app::get('sysuser')->model('user_deposit')->getRow('deposit', ['user_id'=>$userId]);
        $deposit = $deposit['deposit'] ? $deposit['deposit'] : ['deposit' => 0] ;
        return $deposit;

    }

    /**
     * 变更预存款接口（目前仅用于后台调整预存款的数值，可增可减）
     *
     * $params userId int 会员id
     * @params operator string 操作员
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function update($userId, $operator, $fee, $memo)
    {
        $money = abs($fee);
        if($fee > 0)
        {
            return $this->add($userId, $operator, $money, $memo);
        }
        elseif($fee < 0)
        {
            return $this->dedect($userId, $operator, $money, $memo);
        }
        else
        {
            return true;
        }
    }

    /**
     * 会员充值接口
     *
     * @params userId int 会员id
     * @params operator string 操作员
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function add($userId, $operator, $fee, $memo)
    {
        $this->checkUserId($userId);
        logger::info("User deposit add : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");

        $db = app::get('sysuser')->database();
        $result = $db->executeUpdate('UPDATE sysuser_user_deposit SET deposit = deposit + ? WHERE user_id = ?', [$fee, $userId]);
        if(!$result)
        {
            $userDepost = ['user_id' => $userId, 'deposit' => $fee];
            app::get('sysuser')->model('user_deposit')->save($userDepost);
        }

        kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $memo, 'add');

        return true;
    }

    /**
     * 会员企业币接口 -- 一企一舍发放积分
     *
     * @params userId int 会员id
     * @params operator string 操作员
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function updateWithYQYS($userId, $operator, $fee, $memo)
    {

        $userId = userAuth::id();
        // echo($userId);
        $this->checkUser($userId);

        $deposit = $this->get($userId);
        // var_dump($deposit);

        if (0 == $deposit['deposit']) {
            
            logger::info("User deposit yqys add : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");

            $db = app::get('sysuser')->database();
            $result = $db->executeUpdate('UPDATE sysuser_user_deposit SET deposit = ? WHERE user_id = ?', [$fee, $userId]);
            if(!$result)
            {
                $userDepost = ['user_id' => $userId, 'deposit' => $fee];
                app::get('sysuser')->model('user_deposit')->save($userDepost);
                throw new LogicException(app::get('sysuser')->_('同步失败 add'));
            }

            // kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $memo, 'add');
        }
        else if ($deposit) {

            logger::info("User deposit yqys update : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");

            $db = app::get('sysuser')->database();
            $result = $db->executeUpdate('UPDATE sysuser_user_deposit SET deposit = ? WHERE user_id = ?', [$fee, $userId]);
            if(!$result)
            {
                logger::info("User deposit yqys update failed : [userId:{$userId},operator:{$operator},fee:{$fee},memo:${memo}]");
                throw new LogicException(app::get('sysuser')->_('同步失败 update'));
            }

            // kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $memo, 'expense');
        }

        return true;
    }

    /**
     * 会员扣费接口
     *
     * @params userId int 会员id
     * @params operator string 操作用户的账号/手机号码
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function dedect($userId, $operator, $orderNumber, $fee, $memo)
    {
        $this->checkUser($userId);
        logger::info("User deposit dedect : [userId:{$userId},operator:{$operator},fee:{$fee},memo:{$memo}]");
        //新版积分支付接口，赵尊杰 2016-06-01
        $payFee=$fee*100;
    	$sign=md5('orderno='.$orderNumber.'&phoneNum='.$operator.'&pointsAmount='.$payFee.'&pointsType=1'.config::get('link.company_key'));
    	$data=array(
    		'phoneNum'=>$operator,
    		'orderno'=>$orderNumber,
    		'pointsAmount'=>$payFee,
    		'pointsType'=>1,
    		'sign'=>$sign
    	);
    	$url = config::get('link.lishe_company_url').config::get('link.company_payOrder');
		$res = kernel::single('base_httpclient')->post($url,$data);
		@file_put_contents('/data/www/b2b2c/public/business/logs/pointPay/pointPay_'.date('YmdH').'.txt','param:'.json_encode($data).' return:'.$res."\n",FILE_APPEND);
		$reMsg=json_decode($res,TRUE);
		if($reMsg['result']==100){
			if($reMsg['errcode']>0){
				$msg='支付失败：'.$reMsg['msg'];
				throw new LogicException($msg);
			}else{
				//更新本地积分，记录日志
        		app::get('sysuser')->database()->executeUpdate('UPDATE sysuser_user_deposit SET deposit=deposit-?,balance=balance-?,commonAmount=commonAmount-? WHERE user_id=?', [$fee,$payFee,$payFee,$userId]);
        		app::get('ectools')->database()->executeUpdate('UPDATE ectools_payments SET trade_no=?,ls_trade_no=? WHERE payment_id=?',[$reMsg['data']['info']['transno'],$reMsg['data']['info']['transno'],$orderNumber]);
        		$rows=app::get('ectools')->database()->executeQuery('SELECT tid FROM ectools_trade_paybill where payment_id='.$orderNumber)->fetchAll();
        		foreach($rows as $row){
	                $tid[] = $row['tid'];
	            }
	            app::get('systrade')->database()->executeUpdate('UPDATE systrade_trade SET transno='.$reMsg['data']['info']['transno'].' WHERE tid IN ('.implode(',',$tid).')');
        		kernel::single('sysuser_data_deposit_log')->addLog($userId, $operator, $fee, $memo, 'expense');
        		return true;
			}
		}
		else
		{
			$msg='支付失败：接口通讯失败';
			throw new LogicException('支付失败，请重新尝试！');
		}        
    }

    private function checkUser($userId)
    {
        $this->checkUserId($userId);
        return true;
    }

    private function checkUserId($userId)
    {
        if(! $userId > 0)
            throw new LogicException('会员id格式不正确!');

        return true;
    }
}

