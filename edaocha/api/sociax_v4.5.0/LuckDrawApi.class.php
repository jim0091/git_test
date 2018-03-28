<?php

// 抽奖Api接口V2
class LuckDrawApi extends Api
{


    /**
     * @return array
     *
     * 检查用户是否可以抽奖
     */
    public function check_luck_draw(){
        if ($this->uid) {
            $uid = intval($this->uid);
        } else {
            $uid = $this->mid;
        }
        //防止用户重复请求
        if($_SESSION['is_luck_draw']==1){
            return array(
                'status' => 0,
                'msg'    => '您已经参与抽奖了，下次再来吧',
            );
        }

        //判断参与人数是否已经到达预定人数
        $file = fopen("lock.txt","a+");
        $fileNum = fgets($file);
        if($fileNum>=10117){
            return array(
                'status' => 0,
                'msg'    => '本次活动已经结束',
            );
        }

        //测试人员无限制次数
        $test = array(25,18194,17623,18211,18190,17994);
        if(in_array($uid,$test)){
            return array(
                'status' => 1,
                'msg'    => '',
            );
        }

        //判断抽奖活动是否已经结束
        $now = time();
        $start_time = 1506513600;
        if($now<$start_time){
            return array(
                'status' => 0,
                'msg'    => '抽奖活动还未开始',
            );
        }
        $end_time = 1507132799; //TODO 暂时写死
        if($now>$end_time){
            return array(
                'status' => 0,
                'msg'    => '抽奖活动已经结束，下次再来吧',
            );
        }

        //判断用户是否已经抽奖
        $is_luck = model('IncomeMny')->checkIsLuck($uid);
        if(!$is_luck){
            return array(
                'status' => 0,
                'msg'    => '您已经参与抽奖了，下次再来吧',
            );
        }

        return array(
            'status' => 1,
            'msg'    => '',
        );
    }


    /**
     * @return array
     *
     * 抽奖接口
     */
    public function luck_draw(){
        if ($this->uid) {
            $uid = intval($this->uid);
        } else {
            $uid = $this->mid;
        }
        if($_SESSION['is_luck_draw']==1){
            return array(
                'status' => 0,
                'msg'    => '您已经参与抽奖了，下次再来吧',
            );
        }else{
            $_SESSION['is_luck_draw'] = 1;
        }

        //判断当前用户位数，参与抽奖
        $file = fopen("lock.txt","a+");
        if (flock($file,LOCK_EX|LOCK_NB))
        {
            //读取文件中参与的人数值
            $fileNum = fgets($file);
            if($fileNum){
                ftruncate($file,0); // 将文件截断到给定的长度
                rewind($file); // 倒回文件指针的位置
                $num = intval($fileNum) + 1;  //当前用户的位数
                //fwrite($file,$num);
            }else{
                $num = 1;
                //fwrite($file,$num);  //所有完成后再累加
            }
            $m_one = array(50,100,200,300,400,500,700,1000,1500,2000,2500,3000,3500,4000,5000,6000,7000);
            $m_two = array(8,28,48,68,88,108,208,308,408,508,608,708,808,908,1008,1108,1208,1308,1408,1508,
                1608,1708,1808,1908,2008,2108,2208,2308,2408,2508,2608,2708,2808,2908,3008,3108,3208,3308,
                3408,3508,3608,3708,3808,3908,4008,4108,4208,4308,4408,4508,4608,4708,4808,4908,5008,5108,
                5208,5308,5408,5508,5608,5708,5808,5908,6008,6108,6208,6308,6408,6508,6608,6708,6808,6908,
                7008,7108,7208,7308,7408,7508,7608,7708,7808,7908,8008,8108,8208,8308,8408,8508,8608,8708,
                8808,8908,9008,9208,9408,9608,9808,10008
            );
            if(in_array($num, $m_one)){
                $money = '188';
            }elseif(in_array($num, $m_two)){
                $money = '18.8';
            }else{
                $money = '0.88';
            }
            $M = M();
            $M->startTrans(); //开启事务
            //修改抽奖表参与人数与金额累加
            $map = array();
            $map['hasNum'] = array('exp','hasNum+1');
            $map['id'] = 1; //TODO 抽奖表ID暂时写死
            $map['useMoney'] = array('exp','useMoney+'.$money);
            $luck_res = model('LuckDraw')->save($map);
            if($luck_res===false){
                unset($_SESSION['is_luck_draw']);
                $M->rollback();
                return array(
                    'status' => 0,
                    'msg'    => '4001',
                );
            }
            //流水表添加
            $map = array();
            $map['out_trade_no'] = $this->build_order_no();
            $map['money'] = $money;
            $map['type'] = 0;
            $map['uid'] = $uid;
            $map['create_time'] = time();
            $map['table'] = 'luck_draw';
            $map['luck_draw_id'] = 1; //TODO 抽奖表ID暂时写死
            $income_res = model('IncomeMny')->add($map);
            if(!$income_res){
                unset($_SESSION['is_luck_draw']);
                $M->rollback();
                return array(
                    'status' => 0,
                    'msg'    => '4002',
                );
            }
            //用户表修改
            $map = array();
            $map['uid'] = $uid;
            $map['all_income'] = array('exp','all_income+'.$money);
            $map['accountmny'] = array('exp','accountmny+'.$money);
            $user_res = model('User')->save($map);
            //echo model('User')->getlastsql();
            if($user_res===false){
                unset($_SESSION['is_luck_draw']);
                $M->rollback();
                return array(
                    'status' => 0,
                    'msg'    => '4003',
                );
            }
            fwrite($file,$num);  //所有完成后参与人数再累加
            $M->commit();  //提交事务
            flock($file,LOCK_UN); //释放锁
        }else{
            unset($_SESSION['is_luck_draw']);
            return array(
                'status' => 99,
                'msg'    => '系统繁忙，请重试',
            );
        }
        fclose($file); //关闭文件
        return array(
            'status' => 1,
            'msg'    => $money,
        );
    }



    /**
     *
     * 用户钱包查询接口
     *
     */
    public function query_money(){
        if ($this->uid) {
            $uid = intval($this->uid);
        } else {
            $uid = $this->mid;
        }

        $map = array();
        $map['uid'] = $uid;
        $info = model('User')->field('accountmny')->where($map)->find();

        if($info['accountmny']>0){
            $type = 0;  //可提现
        }else{
            //判断用户是否有提现中的流水
            $map = array();
            $map['uid'] = $uid;
            $map['type'] = 1;
            $map['take_state'] = 1;
            $count = model('IncomeMny')->where($map)->count();
            if($count>0){
                $type = 1;  //提现中
            }else{
                $type = 2;  //已提现
            }
        }
        return array(
            'status' => 1,
            'type' => $type,
            'msg'    => $info['accountmny'],
        );
    }


    /**
     *
     * 用户支付宝账号绑定
     *
     */
    public function bind_ali_account(){
        if ($this->uid) {
            $uid = intval($this->uid);
        } else {
            $uid = $this->mid;
        }
        $ali_account = $_REQUEST['ali_account'];
        if(!$ali_account){
            return array(
                'status' => 0,
                'msg'    => '请填写支付宝账号',
            );
        }

        //判断支付宝唯一性
        $map = array();
        $map['ali_account'] = $ali_account;
        $count = model('User')->where($map)->count();
        if($count>0){
            return array(
                'status' => 0,
                'msg'    => '该支付宝账号已被绑定',
            );
        }

        $map = array();
        $map['uid'] = $uid;
        $map['ali_account'] = $ali_account;
        $res = model('User')->save($map);
        if($res===false){
            return array(
                'status' => 0,
                'msg'    => '绑定失败',
            );
        }else{
            return array(
                'status' => 1,
                'msg'    => '绑定成功',
            );
        }
    }


    /**
     *
     *
     * 用户提现接口
     *
     */
    public function to_cash(){
        if ($this->uid) {
            $uid = intval($this->uid);
        } else {
            $uid = $this->mid;
        }
        //用户余额判断
        /*$money = $_REQUEST['money'];
        if(!$money){
            return array(
                'status' => 0,
                'msg'    => '请填写提现金额',
            );
        }*/
        $map = array();
        $map['uid'] = $uid;
        $info = model('User')->field('accountmny,ali_account')->where($map)->find();
        if($info['accountmny']<=0){
            return array(
                'status' => 0,
                'msg'    => '账户余额不足',
            );
        }
        if(empty($info['ali_account'])){
            return array(
                'status' => 0,
                'msg'    => '提现账号未设置',
            );
        }
        //用户余额修改
        $map = array();
        $map['uid'] = $uid;
        $map['accountmny'] = array('exp','accountmny-'.$info['accountmny']);
        $res = model('User')->save($map);
        if(!$res){
            return array(
                'status' => 0,
                'msg'    => '4001',
            );
        }
        $map = array();
        $map['out_trade_no'] = $this->build_order_no();
        $map['money'] = $info['accountmny'];//$money;
        $map['type'] = 1;
        $map['take_state'] = 1;
        $map['uid'] = $uid;
        $map['create_time'] = time();
        $map['table'] = 'luck_draw';
        $map['luck_draw_id'] = 1;  //TODO 抽奖表ID暂时写死
        $res = model('IncomeMny')->add($map);
        if($res){
            return array(
                'status' => 1,
                'msg'    => '提现申请成功',
            );
        }else{
            return array(
                'status' => 0,
                'msg'    => '提现申请失败',
            );
        }
    }


    //生成唯一订单号
    private function build_order_no(){
        return date('ymdHi').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}
