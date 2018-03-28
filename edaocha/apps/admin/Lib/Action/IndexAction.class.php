<?php

tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

class IndexAction extends AdministratorAction
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $nav = array();
        foreach ($this->navList as $k => $v) {
            array_push($nav, array(
                'name'    => L('PUBLIC_APPNAME_'.strtoupper($k)),
                'appname' => $k,
                'url'     => $v,
            ));
        }
//        dump( $this->user);
        $this->assign('nav', $nav);
        $this->setTitle(L('PUBLIC_SYSTEM_MANAGEMENT'));
        $this->assign('channel', C('admin_channel'));
        $this->assign('menu', C('admin_menu'));
//        dump(C('admin_channel'));
//        dump(C('admin_menu'));
        $this->display();
    }

    /**
     *
     * 指定用户刷粉丝
     *
     */
    public function fans(){
        echo header('Content-Type:text/html;charset=utf-8');
        set_time_limit(0);//无超时
        $uid = $_GET['uid']; //需要刷粉的用户ID
        if(!$uid){
            echo '用户ID参数未传';exit;
        }
        $fans_count = $_GET['fans_count']; //粉丝数量
        if(!$fans_count){
            echo '请传入粉丝数量';exit;
        }
        //查询当前用户注册时间
        $myInfo = D('User')->field('uid,ctime')->where('uid='.$uid)->find();
        if(!$myInfo){
            echo '用户不存在';exit;
        }
        $my_ctime = $myInfo['ctime']==''?time():$myInfo['ctime'];
        //查询当前用户已有的粉丝，并二维转一维
        $map = array();
        $map['fid'] = $uid;
        $my_fans = model('Follow')->field('uid')->where($map)->select();
        $my_fans_ids = array();
        foreach($my_fans as  $v){
            $my_fans_ids[] = $v['uid'];
        }

        //随机查询指定数量用户，排除自己，并二维转一维
        $map = array();
        $map['uid'] = array('neq',$uid);
        $randFansList = D('User')->field('uid')->where($map)->order('RAND()')->limit($fans_count)->select();
        $rand_fans_ids = array();
        foreach($randFansList as  $v){
            $rand_fans_ids[] = $v['uid'];
        }

        //去除两个数组中重复的值
        $my_fans_ids = array_diff($rand_fans_ids,$my_fans_ids);
        //批量添加粉丝
        $succ_count = 0;
        $err_count = 0;
        foreach($my_fans_ids as $v){
            //$res = model('Follow')->doFollow($v, $uid);
            $map = array();
            $map['uid'] = $v;
            $map['fid'] = $uid;
            $start_time = date('Y-m-d H:i:s',$my_ctime);
            $end_time = date('Y-m-d',time());
            $map['ctime'] = randomDate($start_time,$end_time); //关注时间在当前用户注册之后的时间内
            $result = model('Follow')->add($map);
            if($result){
                //更新关注数目
                $data_model = model('UserData');
                $data_model->setUid($v)->updateKeyNew('following_count', 1, true);
                $data_model->setUid($uid)->updateKeyNew('follower_count', 1, true);
                $data_model->setUid($uid)->updateKeyNew('new_folower_count', 1, true);

                $succ_count += 1;
            }else{
                $err_count += 1;
            }
        }

        echo '用户ID【'.$uid.'】成功添加：'.$succ_count.'个粉丝'.'，添加失败：'.$err_count.'个';

    }





    /**
     *
     * 指定用户分享刷点赞
     *
     */
    public function digg(){
        echo header('Content-Type:text/html;charset=utf-8');
        set_time_limit(0);//无超时
        $uid = $_GET['uid']; //需要分享点赞的用户ID
        if(!$uid){
            echo '用户ID参数未传';exit;
        }
        $fans_count = $_GET['fans_count']; //粉丝数量
        if(!$fans_count){
            echo '请传入粉丝数量';exit;
        }else{
            if($fans_count<=5){
                echo '传入粉丝数量不能小于5个';exit;
            }
        }

        //根据当前用户ID查询他48小时内的分享
        $map = array();
        $map['uid'] = $uid;
        $map['is_del'] = 0;
        $map['is_audit'] = 1;
        $begin = strtotime(date("Y-m-d H:i:s",strtotime("-2 day")));
        $end = strtotime(date("Y-m-d H:i:s",time()));
        $map['publish_time'] = array('between',array($begin,$end));
        $feedList = model('Feed')->field('feed_id,publish_time')->where($map)->select();
        if(!$feedList){
            echo '该用户48小时内没有分享';exit;
        }
        foreach($feedList as $v){
            //保证每篇分享的点赞人不一样，而且点赞人数也不一样
            $min = $fans_count - 5;
            $max = $fans_count + 5;
            $res_fans_count = mt_rand($min, $max);  //范围内随机点赞数量
            $map = array();
            $map['fid'] = $uid;
            $my_fans = model('Follow')->field('uid')->where($map)->order('RAND()')->limit($res_fans_count)->select();
            $my_fans_ids = array();
            foreach($my_fans as  $m_v){
                $my_fans_ids[] = $m_v['uid'];
            }
            //根据feed_id查询已经点赞的用户，如果用户在my_fans_ids里面，则需要去除这个用户，避免重复点赞
            $is_digg = model('FeedDigg')->field('uid')->where('feed_id='.$v['feed_id'])->select();
            if($is_digg){
                $is_digg_ids = array();
                foreach($is_digg as  $val){
                    $is_digg_ids[] = $val['uid'];
                }
                //去除两个数组中重复的值
                $is_digg_ids = array_diff($my_fans_ids,$is_digg_ids);
                $diggUsers = $is_digg_ids;
            }else{
                $diggUsers = $my_fans_ids;
            }
            //点赞
            $res = $this->doDigg($v['feed_id'],$v['publish_time'],$diggUsers);
            echo '用户ID【'.$uid.'】的分享，ID为【'.$v['feed_id'].'】的点赞人数：'.count($diggUsers)."<br/>";
            echo '用户ID【'.$uid.'】分享，ID为【'.$v['feed_id'].'】的点赞成功数：'.$res."<br/><br/>";
        }

    }

    private function doDigg($feed_id,$publish_time,$diggUsers){
        $succ_count = 0;
        foreach($diggUsers as $v){
            $map = array();
            $map['uid'] = $v;
            $map['feed_id'] = $feed_id;
            $start_time = date('Y-m-d H:i:s',$publish_time);
            $end_time = date('Y-m-d H:i:s',time());
            $map['cTime'] = randomDate($start_time,$end_time); //点赞时间在分享之后的随机时间内
            $res = model('FeedDigg')->add($map);
            if($res){
                //点赞完成后操作
                $feed = model('Source')->getSourceInfo('feed', $feed_id);
                model('Feed')->where('feed_id='.$feed_id)->setInc('digg_count');
                model('Feed')->cleanCache($feed_id);
                model('UserData')->updateKey('unread_digg', 1, true, $feed['uid']);

                //增加积分
                model('Credit')->setUserCredit($v, 'digg_weibo');
                model('Credit')->setUserCredit($feed['uid'], 'digged_weibo');
                model('FeedDigg')->setDiggCache($v, $feed_id, 'add');

                $succ_count += 1;
            }
        }
        return $succ_count;
    }


    public function test(){
        LogRecord('admin_config', 'editPagekey', array('name' => '方法的中文标题', 'k1' => L('PUBLIC_ADMIN_EDIT_PEIZHI')), true);
        echo 1;
    }
}
