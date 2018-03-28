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
class Information extends Controller
{
    public function getInformationNavAction()
    {
        $def = array(array('id'=>0,'isDel'=>0,'name'=>'全部','rank'=>0));
        $navs = M('information_cate')->select();
        $navs = array_merge($def,$navs);
        $this->__json__($navs);
    }
    public function getInformationListAction()
    {
        $limit = Common::getInput('limit');
        if(!$limit){
            $limit = 10;
        }
        list($max, $min, $cid) = Common::getInput(array('max', 'min','cid'));
        $map = array();
        $map['status'] = '1';
        $map['isDel'] = '0';
        !empty($cid) && $map['cid'] = $cid;
        $min && $map['id'] = array('LT',$min);
        $max && $map['id'] = array('GT',$min);
        $informations = M('information_list')->where($map)->order(' id DESC')->limit($limit)->select();
        foreach ($informations as $key => $value) {
            $informations[$key]['logo'] = getImageUrlByAttachId($value['logo'],213,160);
            $informations[$key]['ctime'] = date("Y-m-d",$value['ctime']);
            if(mb_strlen($informations[$key]['subject'],'utf-8')>23){
                $informations[$key]['subject'] = mb_substr($value['subject'],0,23,'utf-8').'...';
            }
        }
        $informations = empty($informations) ? array() : $informations;
        $this->__json__($informations);
    }

    public function getInformationInfoAction()
    {
        $id = Common::getInput('id');
        $info = M('information_list')->find($id);
        if (!$info || $info['isDel'] == 1) {
            $this->error('活动不存在或被删除!', true);
        }
        $information = array();
        $information['subject'] = $info['subject'];
        $information['name'] = $info['created_name'];
        $information['ctime'] = date("Y-m-d",$info['ctime']);
        $information['copyfrom'] = $info['copyfrom'];

        $patterns = array('/src="/');
        $replacements = array('src="https://v2.edaocha.net');
        $info['content'] = preg_replace($patterns, $replacements, $info['content']);
        $information['content'] = $info['content'];

        $this->__json__($information);
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
