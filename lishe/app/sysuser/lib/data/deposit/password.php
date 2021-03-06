<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_data_deposit_password
{

    /**
     *
     * 判断会员是否有密码
     *
     * @params int userId 会员id
     * @return bool true有会员密码，false无会员密码
     */
    public function hasPassword($userId)
    {
        $deposit = app::get('sysuser')->model('user_deposit')->getRow('password', ['user_id'=>$userId]);
        $password = $deposit['password'];
        if($password == '')
            return false;
        return true;
    }

    /**
     *
     * 检查会员的密码是否正确
     *
     * @params int userId 会员id
     * @password string password 会员密码
     * @return bool true密码正确，false密码错误
     *
     */
    public function checkPassword($userId,$password,$type='')
    {
        //检查数据安全
        if(empty($password)){
            throw new \LogicException(app::get('sysuser')->_('请输入密码!'));
            return false;
        }
        $deposit = app::get('sysuser')->model('user_deposit')->getRow('password', ['user_id'=>$userId]);
        if(!pam_encrypt::check($password, $deposit['password']))
        {
            $msg = kernel::single('sysuser_data_passwordLocker')->checkError($userId, 'deposit_password');
            if($type=='e-card'){
				return $msg;
			}else{
				throw new \LogicException($msg);
			}            
        }
        kernel::single('sysuser_data_passwordLocker')->clean($userId, 'deposit_password');
        return true;
    }

    /**
     * 设置密码
     *
     * @params int userId 会员Id
     * @password string password 会员密码
     * @return bool true密码成功
     *
     */
    public function setPassword($userId,$password){
        $password = pam_encrypt::make($password);
        $userDeposit = [
            'user_id' => $userId,
            'password' => $password,
            ];
        kernel::single('sysuser_data_passwordLocker')->clean($userId, 'deposit_password');
        $flag = app::get('sysuser')->model('user_deposit')->save($userDeposit);
        if(!$flag)
        {
            throw new RuntimeException(app::get('支付密码保存失败!'));
        }
        return true;
    }

    /**
     *
     * 修改密码接口
     *
     * @params int userId 会员Id
     * @password string password 会员密码
     * @return bool true密码成功
     *
     */
    public function changePassword($userId, $oldPassword, $newPassword){
        $this->checkPassword($userId, $oldPassword);
        $this->setPassword($userId, $newPassword);
        return true;
    }

    /**
     *
     * 清空密码接口
     *
     * @params int userId 会员Id
     * @return bool true密码成功
     *
     */
    public function resetPassword($userId){
        if(!$userId > 0)
        {
            throw new RuntimeException(app::get('用户Id格式错误!'));
        }
        $userDeposit = [
            'user_id' => $userId,
            'password' => '',
            ];
        $flag = app::get('sysuser')->model('user_deposit')->save($userDeposit);
        if(!$flag)
        {
            throw new RuntimeException(app::get('预存款密码保存失败!'));
        }
        return true;
    }


}


