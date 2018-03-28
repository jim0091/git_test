<?php
class sysuser_data_passwordLocker{

    private function getExpire($type, $returnHour = false)
    {
        if($type == 'deposit_password')
        {
            $expire = app::get('sysconf')->getConf('user.deposit.password.expire');
        }
        if($returnHour)
        {
            return $expire ? $expire : 3;
        }
        return $expire ? $expire * 3600 : 10800;
    }

    private function getLimit($type)
    {
        if($type == 'deposit_password')
        {
            $limit = (int)app::get('sysconf')->getConf('user.deposit.password.limit');
            return $limit ? $limit : 10;
        }
        return 10;
    }

    private function genKey($userId, $type)
    {
        return 'password-lock_' . $type . '_' . $userId;
    }

    //增加1
    public function incr($userId, $type, $times = 1)
    {
        $times = $this->read($userId, $type);
        $times += 1;
        $this->write($userId, $type, $times);
        return $times;
    }

    public function clean($userId, $type)
    {
        return $this->write($userId, $type, 0);
    }

    //array(
    //    'time' => 超时时间
    //    'value' => 次数
    //)
    public function read($userId, $type)
    {
        $key = $this->genKey($userId, $type);
        base_kvstore::instance('password_lock')->fetch($key, $value);
        if($value['time'] > time())
            return $value['value'];
        return 0;
    }

    public function write($userId, $type, $times)
    {
        $expire = $this->getExpire($type);
        $key = $this->genKey($userId, $type);
        $value = [
                'time' => time() + $expire,
                'value' => $times,
            ];
        base_kvstore::instance('password_lock')->store($key, $value, $expire);

        return true;
    }

    public function check($userId, $type)
    {
        $limit = $this->getLimit($type);
        $times = $this->read($userId, $type);
        if($times < $limit)
        {
            return true;
        }
        else{
            throw new LogicException('因为密码输入错误次数太多。请' . $this->getExpire($type, true) . '小时后再试，或找回密码');
        }
    }

    public function checkError($userId, $type)
    {
        $limit=$this->getLimit($type);
        $times=$this->incr($userId, $type);
        $resTimes =$limit-$times;
        $this->read($userId, $type);
        if($resTimes>0){
            $msg = '密码错误！'.$resTimes.'次错误后将会停用！';
        }else{
            $msg='您的账号被停用，请'.$this->getExpire($type, true) . '小时后再试或找回密码';
        }
        return $msg;
    }
}
