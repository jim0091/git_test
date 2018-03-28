<?php

namespace App\H5\Controller;

use App\H5\Common;
use App\H5\Base\Controller;
use App\H5\Model;

/**
 * 分享相关控制器.
 *
 * @author Seven Du <lovveipdsw@outlook.com>
 **/
class Event extends Controller
{
    public function getEventListAction()
    {
        list($max, $min) = Common::getInput(array('max', 'min'));
        $map = array();
        $map['del'] = '0';
        $min && $map['eid'] = array('LT',$min);
        $max && $map['eid'] = array('GT',$min);
        $events = M('event_list')->where($map)->order(' eid DESC')->limit(10)->select();
        foreach ($events as $key => $value) {
            $events[$key]['image'] = getImageUrlByAttachId($value['image']);
            $time = time();
            $events[$key]['status'] = $time > $value['etime'] ? 2 : ($time < $value['stime'] ?  0 : 1);
            $events[$key]['etime'] = date("Y-m-d",$value['etime']);
            $events[$key]['stime'] = date("Y-m-d",$value['stime']);
        }
        $events = empty($events) ? array() : $events;
        $this->__json__($events);
    }

    public function getEventInfoAction()
    {
        $eventId = Common::getInput('event_id');
        $info = M('event_list')->where(array('name'=>'111'))->find($eventId);
        if (!$info || $info['del'] == 1) {
            $this->error('活动不存在或被删除!', true);
        }
        $event = array();
        
        $event['eventId'] = $info['eid'];
        $event['name'] = $info['name'];
        $event['image'] = array(getImageUrlByAttachId($info['image']));

        $info['area'] = model('Area')->getAreaById($info['area']);
        $info['area'] = $info['area']['title'];
        $info['city'] = model('Area')->getAreaById($info['city']);
        $info['city'] = $info['city']['title'];
        $event['location'] = $info['area']." ".$info['city']." ".$info['location'];
        $event['time'] = date('Y.m.d', $info['stime']).'-'.date('Y.m.d', $info['etime']);
        $event['price']  = $info['price'] == '0.00' ?  '免费' : $info['price'].'元';
        $event['sponsor'] = $info['sponsor'];
        $event['content'] = $info['content'];

        $event['starcount'] = array('boy'=>0,'girl'=>0);
        $lists  = M('event_enrollment')->where(array('eid'=>$eventId))->select();
        foreach ($lists as $key => $value) {
            if($value['sex'] == 1){
                $event['starcount']['boy']++;
            }else{
                $event['starcount']['girl']++;
            }
        }

        $event['commentNum'] = M('comment')->where(array('app' => 'Event', 'row_id' => $eventId, 'is_del' => 0))->count();
        $commentlist = M('comment')->where(array('row_id' => $eventId, 'app' => 'Event', 'to_comment_id' => 0, 'is_del' => 0))->order()->limit(3)->select();
        $event['comment'] = array();
        foreach($commentlist as $key => $value){
            $userinfo = model('User')->getUserInfo($value['uid']);
            $event['comment'][$key]['userface'] = $userinfo['avatar_big'];
            $event['comment'][$key]['name'] = $userinfo['uname'];
            $event['comment'][$key]['time'] = friendlyDate($value['ctime']);
            $event['comment'][$key]['from'] = getFromClient($value['client_type']);
            $event['comment'][$key]['diggCount'] = $value['digg_count'];
            $event['comment'][$key]['content'] = $value['content'];
            $event['comment'][$key]['tabcomment'] = $this->getTabComent($value['comment_id']);
        }
        
        $this->__json__($event);
    }

    private function getTabComent($comment_id,$back = true){
        $re = array();
        $num = 3;
        if($comment = M('comment')->where(array('app' => 'Event', 'is_del' => 0, 'to_comment_id' => $comment_id))->limit($num)->select()){
            foreach($comment as  $v){                
                if(strpos($v['content'], '：')){
                    $v['content'] = explode('：',$v['content']);
                    $v['content'] = $v['content'][1];
                }
                $re[] = array('uname' =>getUserName($v['uid']),'toname' => getUserName($v['to_uid']),'content' => $this->getcontent($v['content']));
                if($comment1 = M('comment')->where(array('app' => 'Event', 'is_del' => 0, 'to_comment_id' => $comment['comment_id']))->limit($num)->select()){
                    foreach($comment1 as $v1){
                        $re[] = array('uname' =>getUserName($v['uid']),'toname' => getUserName($v['to_uid']),'content' => $this->getcontent($v['content']));
                        if($comment2 = M('comment')->where(array('app' => 'Event', 'is_del' => 0, 'to_comment_id' => $comment1['comment_id']))->limit($num)->select()){
                            foreach ($comment2 as $v2) {
                                $re[] = array('uname' =>getUserName($v['uid']),'toname' => getUserName($v['to_uid']),'content' => $this->getcontent($v['content']));
                            }
                        }
                    }
                }
            }
           /* if(!$back){
                return $comment['content'];
            }else{
                $re[] = $comment['content'];
                do{
                    $C = $this->getTabComent($comment['comment_id'],false);
                    !empty($C) && $re[] = $c['']
                }while();
            }*/
        }
        if(count($re) > 3){
            $re = array_slice($re,0,3);
        }
        return $re;
    }

    private function getcontent($content){        
        if(strpos($content, '：')){
            $content = explode('：',$content);
            return $content[1];
        }else{
            return $content;
        }
    }
  
} // END class Feed extends Controller
