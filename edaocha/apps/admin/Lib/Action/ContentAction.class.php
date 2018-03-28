<?php

//+----------------------------------------------------------------------
// | Sociax [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://www.thinksns.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: jason <yangjs17@yeah.net>
// +----------------------------------------------------------------------
//

/**
 * 内容管理.
 +------------------------------------------------------------------------------
 *
 * @author    jason <yangjs17@yeah.net>
 *
 * @version   1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
tsload(SRC_PATH.'/vendor/PHPExcel.php');
class ContentAction extends AdministratorAction
{
    public $pageTitle = array();
    //TODO  要移位置
    public $from = array(0 => '网站', 1 => '手机网页版', 2 => 'android', 3 => 'iphone');


    /**
     * @param int $isRec
     * @param int $is_audit
     * @param int $count
     * @param null $order
     *
     * 动态列表
     */
    public function feed($isRec = 0, $is_audit = 1,$count = 20,$order = null)
    {
        //搜索区别
        $_POST['rec'] = $isRec = isset($_REQUEST['rec']) ? t($_REQUEST['rec']) : $isRec;
        if (!$isRec) {
            $_POST['is_audit'] = $isRec = isset($_REQUEST['is_audit']) ? t($_REQUEST['is_audit']) : $isRec;
        }

        $this->pageKeyList = array('feed_id', 'uid', 'uname', 'data', 'publish_time', 'type', 'from','is_recommend','is_anonymous','channel', 'DOACTION');
        $this->searchKey = array('feed_id','feed_content','channel',array('ctime','ctime1'), 'uid', 'type', 'rec');
        $this->opt['type'] = array('0' => L('PUBLIC_ALL_STREAM'), 'post' => L('PUBLIC_ORDINARY_WEIBO'), 'repost' => L('PUBLIC_SHARE_WEIBO'), 'postimage' => L('PUBLIC_PICTURE_WEIBO'), 'postfile' => L('PUBLIC_ATTACHMENT_WEIBO'));    //TODO 临时写死
        $channel = D('channel_category')->where('pid=0')->select();
        foreach ($channel as $k =>$v){
            $this->opt['channel'][$v['channel_category_id']] = $v['title'];
        }
        $this->opt['channel']['999'] = '未指定频道';
        $this->opt['channel']['0'] = "不限";
//        dump($this->opt['channel']);
        $this->pageTab[] = array('title' => L('PUBLIC_DYNAMIC_MANAGEMENT'), 'tabHash' => 'list', 'url' => U('admin/Content/feed'));
        $this->pageTab[] = array('title' => '待审列表', 'tabHash' => 'unAudit', 'url' => U('admin/Content/feedUnAudit'));
        $this->pageTab[] = array('title' => L('PUBLIC_RECYCLE_BIN'), 'tabHash' => 'rec', 'url' => U('admin/Content/feedRec'));
        $this->pageTab[] = array('title' => '热门动态', 'tabHash' => 'hot', 'url' => U('admin/Content/feedHot'));
        $this->pageTab[] = array('title' => '匿名动态', 'tabHash' => 'anonymous', 'url' => U('admin/Content/get_anonymous'));

        $this->pageButton[] = array('title' => L('PUBLIC_DYNAMIC_SEARCH'), 'onclick' => "admin.fold('search_form')");
        if ($isRec == 0 && $is_audit == 1) {
//            $this->pageKeyList = array('feed_id', 'uid', 'uname', 'data', 'publish_time', 'type', 'from','is_recommend','is_anonymous', 'channel','DOACTION');
            $this->pageButton[] = array('title' => L('PUBLIC_DYNAMIC_DELETE'), 'onclick' => "admin.ContentEdit('','delFeed','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_DYNAMIC')."')");
            $this->pageButton[] = array('title' => '推荐到热门', 'onclick' => "admin.ContentEdit('','auditAndRecommendFeed','".'推荐到热门'."','".L('PUBLIC_DYNAMIC')."')");
        } elseif ($isRec == 0 && $is_audit == 0) {
            $this->pageButton[] = array('title' => '通过', 'onclick' => "admin.ContentEdit('','auditFeed','".'通过'."','".L('PUBLIC_DYNAMIC')."')");
            $this->pageButton[] = array('title' => '通过并推荐到热门', 'onclick' => "admin.ContentEdit('','auditAndRecommendFeed','".'通过并推荐到热门'."','".L('PUBLIC_DYNAMIC')."')");
            $this->pageButton[] = array('title' => '删除', 'onclick' => "admin.ContentEdit('','delFeed','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_DYNAMIC')."')");
        } elseif ($isRec == 2 && $is_audit == 2) {
//            $this->pageKeyList = array('feed_id', 'uid', 'uname', 'data', 'publish_time', 'type', 'from','is_recommend','is_anonymous', 'channel','DOACTION');
            $this->pageButton[] = array('title' => L('PUBLIC_DYNAMIC_DELETE'), 'onclick' => "admin.ContentEdit('','delFeed','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_DYNAMIC')."')");
            $this->pageButton[] = array('title' => '取消热门', 'onclick' => "admin.ContentEdit('','unFeedhot','".'取消热门'."','".L('PUBLIC_DYNAMIC')."')");
            $this->pageButton[] = array('title' => '推送到视频频道', 'onclick' => "admin.sweepHot()");
        }else {
            $this->pageButton[] = array('title' => L('PUBLIC_REMOVE_COMPLETELY'), 'onclick' => "admin.ContentEdit('','deleteFeed','".L('PUBLIC_REMOVE_COMPLETELY')."','".L('PUBLIC_DYNAMIC')."')");
        }

        $isRec == 1 && $_REQUEST['tabHash'] = 'rec';
        $isRec == 2 && $_REQUEST['tabHash'] = 'hot';
        $isRec == 3 && $_REQUEST['tabHash'] = 'anonymous';
        $is_audit == 0 && $_REQUEST['tabHash'] = 'unAudit';
        $this->assign('pageTitle', $isRec ? L('PUBLIC_RECYCLE_BIN') : L('PUBLIC_DYNAMIC_MANAGEMENT'));
        $map['a.is_del'] = $isRec == 1 ? 1 : 0;

        //热门动态条件
        if($isRec==2&&$is_audit==2){
            $map['a.is_recommend'] = 1;
            $map['a.is_audit'] = 1;
        }

        //匿名动态条件
        if($isRec==3&&$is_audit==1){
            $map['a.is_anonymous'] = 1;
            $map['a.is_audit'] = 1;
        }elseif($isRec==0&&$is_audit==1){
            $map['a.is_anonymous'] = 0;
        }

        if (!$isRec) {
            $map['a.is_audit'] = $is_audit == 1 ? 1 : 0;
        }
        !empty($_POST['feed_id']) && $map['a.feed_id'] = array('in', explode(',', $_POST['feed_id']));
        !empty($_POST['uid']) && $map['a.uid'] = array('in', explode(',', $_POST['uid']));
        !empty($_POST['type']) && $map['a.type'] = t($_POST['type']);
        !empty($_POST['feed_content']) && $map['b.feed_content'] = array('LIKE', '%'.t($_POST['feed_content']).'%');
//        !empty($_POST['channel']) && $map['c.channel_category_id'] = intval($_POST['channel']);
        if(!empty($_POST['channel'])){
            if($_POST['channel'] == 999){
                $map['c.channel_category_id'] =array('exp','is null');
            }else{
                $map['c.channel_category_id'] = intval($_POST['channel']);
            }
        }
        if (!empty($_POST['ctime'])) {
            if (!empty($_POST['ctime'][0]) && !empty($_POST['ctime'][1])) {
                // 时间区间条件
                $map['publish_time'] = array(
                    'BETWEEN',
                    array(
                        strtotime($_POST['ctime'][0]),
                        strtotime($_POST['ctime'][1]),
                    ),
                );
            } elseif (!empty($_POST['ctime'][0])) {
                // 时间大于条件
                $map['publish_time'] = array(
                    'GT',
                    strtotime($_POST['ctime'][0]),
                );
            } elseif (!empty($_POST['ctime'][1])) {
                // 时间小于条件
                $map['publish_time'] = array(
                    'LT',
                    strtotime($_POST['ctime'][1]),
                );
            }
        }
        $listData = model('Feed')->getListAdmin($map, $count,$order);
//        dump($listData['data'][0]['channel']);
        foreach ($listData['data'] as &$v) {
            if($v['is_anonymous']){
                $v['uname'] = $v['user_info']['anonymous_name'];
            }else{
                $v['uname'] = $v['user_info']['space_link'];
            }
            $v['type'] = $this->opt['type'][$v['type']];
            $v['from'] = $this->from[$v['from']];
            if($isRec == 3 && $is_audit == 1){
                $v['data'] = '<div style="width:500px;line-height:22px" model-node="feed_list" class="feed_list">'.$v['body'];
            }else{
                $v['data'] = '<div style="width:500px;line-height:22px" model-node="feed_list" class="feed_list">'.$v['body'].'  <a target="_blank" href="'.U('public/Profile/feed', array('feed_id' => $v['feed_id'], 'uid' => $v['uid'])).'">'.L('PUBLIC_VIEW_DETAIL').'&raquo;</a></div>';
            }
            $v['publish_time'] = date('Y-m-d H:i:s', $v['publish_time']);
            //$v['DOACTION'] = $isRec==0 ? "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"delFeed\",\"".L('PUBLIC_STREAM_DELETE')."\",\"".L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE')."</a>"
            //							:"<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"feedRecover\",\"".L('PUBLIC_RECOVER')."\",\"".L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_RECOVER')."</a>";
            $res= D("channel")->where('feed_id='.$v['feed_id'])->select();
            if($res){
                $v['channel']="";
                foreach ($res as &$value){
                    $map1['pid'] = 0;
                    $map1['channel_category_id'] = $value['channel_category_id'];
                    $category = D('channel_category')->where($map1)->find();
                    $v['channel'].= $category['title'];
                    $v['channel'].='<br/>';
                }
                $v['channel'].='<span onclick="admin.changeChannel('.$v['feed_id'].')">修改频道</span>';
            }else{
                $v['channel'].= "未指定频道";
                $v['channel'].='<br/>';
                $v['channel'].='<span onclick="admin.changeChannel('.$v['feed_id'].')">修改频道</span>';
            }
            if ($isRec == 0 && $is_audit == 1) {
                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"auditAndRecommendFeed\",\"".'推荐到热门'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'推荐到热门'.'</a>&nbsp;|&nbsp;'."<a  href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"delFeed\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
                if($v['is_top']){
                    $v['DOACTION'] .= "&nbsp;|&nbsp;<a href='javascript:void(0)' onclick='admin.ContentTop({$v['feed_id']},\"contentTop\",\"".'取消广场置顶'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'取消广场置顶'.'</a>';
                }else{
                    $v['DOACTION'] .= "&nbsp;|&nbsp;<a href='javascript:void(0)' onclick='admin.ContentTop({$v['feed_id']},\"contentTop\",\"".'广场置顶'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'广场置顶'.'</a>';
                }
            } elseif ($isRec == 0 && $is_audit == 0) {
                //$v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"auditFeed\",\"".'通过'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'通过'.'</a>&nbsp;|&nbsp;'."<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"delFeed\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"auditFeed\",\"".'通过'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'通过'.'</a>&nbsp;|&nbsp;'."<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"auditAndRecommendFeed\",\"".'通过并推荐到热门'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'通过并推荐到热门'.'</a>&nbsp;|&nbsp;'."<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"delFeed\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
            }elseif ($isRec == 2 && $is_audit == 2) {
                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"unFeedhot\",\"".'取消热门'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'取消热门'.'</a>&nbsp;|&nbsp;'."<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"delFeed\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
                if($v['recommend_top']){
                    $v['DOACTION'] .= "&nbsp;|&nbsp;<a href='javascript:void(0)' onclick='admin.ContentRecommendTop({$v['feed_id']},\"content_recommend_top\",\"".'取消热门置顶'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'取消热门置顶'.'</a>';
                }else{
                    $v['DOACTION'] .= "&nbsp;|&nbsp;<a href='javascript:void(0)' onclick='admin.ContentRecommendTop({$v['feed_id']},\"content_recommend_top\",\"".'热门置顶'.'","'.L('PUBLIC_DYNAMIC')."\")'>".'热门置顶'.'</a>';
                }
            }elseif ($isRec == 3 && $is_audit == 1) {
//                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"delFeed\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
                $v['DOACTION'] = '<a href="'.U('admin/content/delContent',array('feed_id' => $v['feed_id'])).'">'.L('PUBLIC_STREAM_DELETE').'</a>';
            }else {
                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['feed_id']},\"feedRecover\",\"".L('PUBLIC_RECOVER').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_RECOVER').'</a>';
            }

            $v['is_recommend'] = $v['is_recommend'] == '1'?'热门':'非热门';
            $v['is_anonymous'] = $v['is_anonymous'] == '1'?'匿名分享':'实名分享';
        }
        $this->_listpk = 'feed_id';
        $this->displayList($listData);
    }

    //匿名动态删除
    public  function delContent(){
        // 初始化用户列表管理菜单
        // 列表key值 DOACTION表示操作
        $this->pageTab[] = array('title' => '匿名动态删除','url' => U('admin/content/get_anonymous'));
        $this->pageKeyList = array('feed_id','reason');
        $reason = D("feed_andel_reason")->select();
        foreach ($reason as $k => $v) {
            $this->opt['reason'][$v['reason_id']] = $v['reason'];
        }
        $feed_id = intval($_REQUEST['feed_id']);
        $map['feed_id'] = $feed_id;
        $feedInfo = D('feed')->where($map)->find();
        if (!$feedInfo) {
            $this->error(L('PUBLIC_GET_INFORMATION_FAIL'));
        }
//        $this->assign('pageTitle', '11122');
        $this->savePostUrl = U('admin/content/del_anonymous');
        $this->displayConfig($feedInfo);
    }

    //匿名动态删除
    public  function del_anonymous(){
        $feed_id = intval($_POST['feed_id']);
        if (!$feed_id) {
            $this->error('非法操作');
        }
        $feedInfo = D('feed')->where('feed_id='.$feed_id)->find();
        $to_uid = $feedInfo['uid'];//推送到指定用户的ID
//        dump($to_uid);
        $reason_id = intval($_POST['reason']);
        if (!$reason_id) {
            $this->error('请选择删除原因');
        }
//       dump($reason_id);
        $find = D('feed_andel_reason')->where('reason_id='.$reason_id)->find();
        $content = $find['reason'];//推送的内容
        $ctime = time();//推送时间
        $type = 'system';//推送分类默认为系统推送
        $row_id = '0';//关联ID默认为0
        $data = array(
            'content' => $content,
            'ctime' => $ctime,
            'row_id' => $row_id,
            'type' => $type,
            'to_uids' => $to_uid,
            );
        $res1 = D('feed')->where('feed_id='.$feed_id)->delete();//删除这条匿名动态
        $res2 = D('message_system')->add($data);//添加到推送
        if($res1 && $res2){
            $this->assign('jumpUrl', U('admin/content/get_anonymous'));
            $this->success('删除成功');
        }
    }

    //待审列表
    public function feedUnAudit()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_feed';
        $this->searchPageKey = 'S_'.$this->pageKey;

        $this->feed(0, 0, 5);
    }

    //回收站
    public function feedRec()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_feed';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->feed(1);
    }

    //热门动态
    public function feedHot()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_feed';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->feed(2,2,20,'a.recommend_top DESC, a.recommend_time DESC');
    }

    //匿名动态
    public function get_anonymous()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_feed';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->feed(3,1,20);
    }

    //恢复
    public function feedRecover()
    {
        $return = model('Feed')->doEditFeed($_POST['id'], 'feedRecover', L('PUBLIC_RECOVER'));
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_RECOVERY_FAILED');
        } else {
            $return['data'] = L('PUBLIC_RECOVERY_SUCCESS');
        }
        echo json_encode($return);
        exit();
    }

    //分享通过审核
    public function auditFeed()
    {
        $return = model('Feed')->doAuditFeed($_POST['id']);
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_ERROR');
        } else {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_SUCCESS');
        }
        echo json_encode($return);
        exit();
    }

    //分享通过并推荐到热门
    public function auditAndRecommendFeed(){
        $return = model('Feed')->doAuditAndRecommendFeed($_POST['id']);
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_ERROR');
        } else {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_SUCCESS');
        }
        echo json_encode($return);
        exit();
    }

    //假删除
    public function delFeed()
    {
        $return = model('Feed')->doEditFeed($_POST['id'], 'delFeed', L('PUBLIC_STREAM_DELETE'));
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_DELETE_FAIL');
        } else {
            $return['data'] = L('PUBLIC_DELETE_SUCCESS');
            //记录操作日志
            $feed_ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'delFeed', array('name' => $feed_ids, 'k1' => '删除到回收站'), true);
        }
        echo json_encode($return);
        exit();
    }

    //真删除
    public function deleteFeed()
    {
        $return = model('Feed')->doEditFeed($_POST['id'], 'deleteFeed', L('PUBLIC_REMOVE_COMPLETELY'));
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_REMOVE_COMPLETELY_FAIL');
        } else {
            $return['data'] = L('PUBLIC_REMOVE_COMPLETELY_SUCCESS');
            //记录操作日志
            $feed_ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'deleteFeed', array('name' => $feed_ids, 'k1' => '彻底删除'), true);
        }
        echo json_encode($return);
        exit();
    }

    //取消热门动态
    public function unFeedhot(){
        $feed_id = $_POST['id'];
        $map['feed_id'] = is_array($feed_id) ? array('IN', $feed_id) : intval($feed_id);
        $save['is_recommend'] = 0;
        $res = D('Feed')->where($map)->save($save);
        if($res===false){
            $return['status'] = 0;
            $return['data'] = '取消热门动态失败';
        }else{
            $return['status'] = 1;
            $return['data'] = '取消热门动态成功';
        }
        echo json_encode($return);
        exit();
    }


    /**
     *
     * 广场置顶/取消置顶
     *
     */
    public function contentTop(){
        $feed_id = $_POST['id'];
        $map['feed_id'] = is_array($feed_id) ? array('IN', $feed_id) : intval($feed_id);
        $info = D('Feed')->field('is_top')->where($map)->find();
        if($info['is_top']){
            $save['is_top'] = 0;
        }else{
            $save['is_top'] = 1;
        }
        $res = D('Feed')->where($map)->save($save);
        if($res===false){
            $return['status'] = 0;
            $return['data'] = '操作失败';
        }else{
            $return['status'] = 1;
            $return['data'] = '操作成功';
        }

        model('Feed')->cleanCache($feed_id);
        echo json_encode($return);
        exit();
    }


    /**
     *
     * 热门置顶/取消置顶
     *
     */
    public function content_recommend_top(){
        $feed_id = $_POST['id'];
        $map['feed_id'] = is_array($feed_id) ? array('IN', $feed_id) : intval($feed_id);
        $info = D('Feed')->field('recommend_top')->where($map)->find();
        if($info['recommend_top']){
            $save['recommend_top'] = 0;
        }else{
            $save['recommend_top'] = 1;
        }
        $res = D('Feed')->where($map)->save($save);
        if($res===false){
            $return['status'] = 0;
            $return['data'] = '操作失败';
        }else{
            $return['status'] = 1;
            $return['data'] = '操作成功';
        }

        model('Feed')->cleanCache($feed_id);
        echo json_encode($return);
        exit();
    }


    /**
     * 评论管理.
     *
     * @param int $isRec 是否是回收站列表
     * @param int $is_audit 是否是通过审核
     * @param int $is_anonymous 是否是匿名
     *
     * @return array 相关数据
     */
    public function comment($isRec = 0, $is_audit = 1,$is_anonymous = 0)
    {
        // 搜索区别
        $_POST['rec'] = $isRec = isset($_REQUEST['rec']) ? t($_REQUEST['rec']) : $isRec;

        $this->pageKeyList = array('comment_id', 'uid', 'app_uid', 'source_type', 'content', 'ctime','is_anonymous', 'client_type', 'DOACTION');
        $this->searchKey = array('comment_id', 'uid', 'app_uid');

        $this->pageTab[] = array('title' => '评论管理', 'tabHash' => 'list', 'url' => U('admin/Content/comment'));
        $this->pageTab[] = array('title' => '匿名评论管理', 'tabHash' => 'anonymous_list', 'url' => U('admin/Content/anonymous_list'));
        $this->pageTab[] = array('title' => '待审评论列表', 'tabHash' => 'unAudit', 'url' => U('admin/Content/commentUnAudit'));
        $this->pageTab[] = array('title' => L('PUBLIC_RECYCLE_BIN'), 'tabHash' => 'rec', 'url' => U('admin/Content/commentRec'));

        $this->pageButton[] = array('title' => L('PUBLIC_SEARCH_COMMENT'), 'onclick' => "admin.fold('search_form')");
        if ($isRec == 0 && $is_audit == 1) {
            $this->pageButton[] = array('title' => L('PUBLIC_DELETE_COMMENT'), 'onclick' => "admin.ContentEdit('','delComment','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_STREAM_COMMENT')."')");
        } elseif ($isRec == 0 && $is_audit == 0) {
            $this->pageButton[] = array('title' => '通过', 'onclick' => "admin.ContentEdit('','auditComment','".'通过'."','".L('PUBLIC_DYNAMIC')."')");
            $this->pageButton[] = array('title' => '删除', 'onclick' => "admin.ContentEdit('','delComment','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_DYNAMIC')."')");
        } else {
            $this->pageButton[] = array('title' => L('PUBLIC_REMOVE_COMPLETELY'), 'onclick' => "admin.ContentEdit('','deleteComment','".L('PUBLIC_REMOVE_COMPLETELY')."','".L('PUBLIC_STREAM_COMMENT')."')");
        }

        $isRec == 1 && $_REQUEST['tabHash'] = 'rec';
        $is_audit == 0 && $_REQUEST['tabHash'] = 'unAudit';
        $this->assign('pageTitle', $isRec ? L('PUBLIC_RECYCLE_BIN') : '评论管理');
        $map['is_del'] = $isRec == 1 ? 1 : 0;
        $map['is_anonymous'] = $is_anonymous;
        if (!$isRec) {
            $map['is_audit'] = $is_audit == 1 ? 1 : 0;
        }
        !empty($_POST['comment_id']) && $map['comment_id'] = array('in', explode(',', $_POST['comment_id']));
        !empty($_POST['uid']) && $map['uid'] = array('in', explode(',', $_POST['uid']));
        !empty($_POST['app_uid']) && $map['app_uid'] = array('in', explode(',', $_POST['app_uid']));
        $listData = model('Comment')->getCommentList($map, 'comment_id desc', 20);

        foreach ($listData['data'] as &$v) {
            $v['uid'] = $v['user_info']['space_link'];
            $v['app_uid'] = $v['sourceInfo']['source_user_info']['space_link'];
            $v['source_type'] = "<a href='{$v['sourceInfo']['source_url']}' target='_blank'>".$v['sourceInfo']['source_type'].'</a>';
            $v['content'] = '<div style="width:400px">'.$v['content'].'</div>';
            $v['client_type'] = $this->from[$v['client_type']];
            $v['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
            $v['DOACTION'] = $isRec == 0 ? "<a href='".$v['sourceInfo']['source_url']."' target='_blank'>".L('PUBLIC_VIEW')."</a> <a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"delComment\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_STREAM_COMMENT')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>'
                : "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"CommentRecover\",\"".L('PUBLIC_RECOVER').'","'.L('PUBLIC_STREAM_COMMENT')."\")'>".L('PUBLIC_RECOVER').'</a>';
            if ($isRec == 0 && $is_audit == 1) {
                $v['DOACTION'] = "<a href='".$v['sourceInfo']['source_url']."' target='_blank'>".L('PUBLIC_VIEW')."</a> <a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"delComment\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_STREAM_COMMENT')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
            } elseif ($isRec == 0 && $is_audit == 0) {
                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"auditComment\",\"".'通过'.'","'.L('PUBLIC_STREAM_COMMENT')."\")'>".'通过'.'</a>&nbsp;|&nbsp;'."<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"delComment\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_DYNAMIC')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
            } else {
                $v['DOACTION'] = "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"CommentRecover\",\"".L('PUBLIC_RECOVER').'","'.L('PUBLIC_STREAM_COMMENT')."\")'>".L('PUBLIC_RECOVER').'</a>';
            }
            $v['is_anonymous'] = $v['is_anonymous'] == '1'?'匿名评论':'实名评论';
        }
        $this->_listpk = 'comment_id';
        $this->displayList($listData);
    }

    //匿名评论列表
    public function anonymous_list(){
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_comment';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->comment(0, 1, 1);
    }

    //待审列表
    public function commentUnAudit()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_comment';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->comment(0, 0);
    }

    //回收站
    public function commentRec()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_comment';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->comment(1);
    }

    //恢复
    public function commentRecover()
    {
        echo json_encode(model('Comment')->doEditComment($_POST['id'], 'commentRecover', '恢复成功'));
    }

    //评论通过审核
    public function auditComment()
    {
        $return = model('Comment')->doAuditComment($_POST['id']);
        if ($return['status'] == 0) {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_ERROR');
        } else {
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_SUCCESS');
        }
        echo json_encode($return);
        exit();
    }

    //假删除
    public function delComment()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'delComment', array('name' => $ids, 'k1' => '删除评论到回收站'), true);
        }
        echo json_encode(model('Comment')->doEditComment($_POST['id'], 'delComment', '删除成功'));
    }

    //真删除
    public function deleteComment()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'deleteComment', array('name' => $ids, 'k1' => '彻底删除评论'), true);
        }
        echo json_encode(model('Comment')->doEditComment($_POST['id'], 'deleteComment', '评论彻底删除成功'));
    }

    /**
     * 私信管理列表.
     *
     * @param int $isRec [description]
     */
    public function message($isRec = 0)
    {
        // 搜索区别
        $_POST['rec'] = $isRec = isset($_REQUEST['rec']) ? t($_REQUEST['rec']) : $isRec;
        // 列表字段配置
        $this->pageKeyList = array('message_id', 'fuid', 'from_uid', 'mix_man', 'content', 'mtime', 'DOACTION');
        // 搜索字段配置
        $this->searchKey = array('from_uid', 'mix_man', 'content');
        // Tab标签配置
        $this->pageTab[] = array('title' => L('PUBLIC_PRIVATE_MESSAGE_MANAGEMENT'), 'tabHash' => 'list', 'url' => U('admin/Content/message'));
        $this->pageTab[] = array('title' => L('PUBLIC_RECYCLE_BIN'), 'tabHash' => 'rec', 'url' => U('admin/Content/messageRec'));
        // 批量操作按钮配置
        $this->pageButton[] = array('title' => L('PUBLIC_MASSAGE_SEARCH'), 'onclick' => "admin.fold('search_form')");
        if ($isRec == 0) {
            $this->pageButton[] = array('title' => L('PUBLIC_MASSAGE_DEL'), 'onclick' => "admin.ContentEdit('','delMessage','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_PRIVATE_MESSAGE')."');");
        } else {
            $this->pageButton[] = array('title' => L('PUBLIC_REMOVE_COMPLETELY'), 'onclick' => "admin.ContentEdit('','deleteMessage','".L('PUBLIC_REMOVE_COMPLETELY')."','".L('PUBLIC_PRIVATE_MESSAGE')."')");
        }
        $isRec == 1 && $_REQUEST['tabHash'] = 'rec';
        $this->assign('pageTitle', $isRec ? L('PUBLIC_RECYCLE_BIN') : L('PUBLIC_PRIVATE_MESSAGE_MANAGEMENT'));
        // 未删除的
        $map['a.is_del'] = ($isRec == 1) ? 1 : 0;
        !empty($_POST['from_uid']) && $map['a.from_uid'] = intval($_POST['from_uid']);
        !empty($_POST['mix_man']) && $map['c.member_uid'] = intval($_POST['mix_man']);
        !empty($_POST['content']) && $map['a.content'] = array('like', '%'.t($_POST['content']).'%');
        $map['b.type'] = array('neq', 3);
        // 获取列表信息
        $listData = model('Message')->getDetailList($map);
        // 整理列表数据
        foreach ($listData['data'] as &$v) {
            $uids = explode('_', $v['min_max']);
            $map = array();
            $map['uid'] = array('in', $uids);
            $uname = model('User')->where($map)->getHashList('uid', 'uname');

            $v['mix_man'] = implode(',', $uname);

            if ($v['fuid'] == '1') {
                $v['fuid'] = L('PUBLIC_SYSTEM');
            } else {
                $v['fuid'] = $uname[$v['fuid']];
            }

            if ($v['from_uid'] == '1') {
                $v['from_uid'] = L('PUBLIC_SYSTEM');
            } else {
                $v['from_uid'] = $uname[$v['from_uid']];
            }

            $v['content'] = '<div style="width:500px">'.getShort($v['content'], 120, '...').'</div>'; // 截取120字
            $v['mtime'] = date('Y-m-d H:i:s', $v['mtime']);
            $v['DOACTION'] = $isRec == 0 ? "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['message_id']},\"delMessage\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_PRIVATE_MESSAGE')."\");'>".L('PUBLIC_STREAM_DELETE').'</a>'
                                        : "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['message_id']},\"MessageRecover\",\"".L('PUBLIC_RECOVER').'","'.L('PUBLIC_PRIVATE_MESSAGE')."\")'>".L('PUBLIC_RECOVER').'</a>';
        }
        // 设置操作主键
        $this->_listpk = 'message_id';
        $this->displayList($listData);
    }

    //回收站
    public function messageRec()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_message';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->message(1);
    }

    //恢复
    public function messageRecover()
    {
        echo json_encode(model('Message')->doEditMessage($_POST['id'], 'messageRecover', L('PUBLIC_RECOVER')));
    }

    //假删除
    public function delMessage()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'delMessage', array('name' => $ids, 'k1' => '删除私信到回收站'), true);
        }
        echo json_encode(model('Message')->doEditMessage($_POST['id'], 'delMessage', L('PUBLIC_STREAM_DELETE')));
    }

    //真删除
    public function deleteMessage()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'deleteMessage', array('name' => $ids, 'k1' => '彻底删除私信'), true);
        }
        echo json_encode(model('Message')->doEditMessage($_POST['id'], 'deleteMessage', L('PUBLIC_REMOVE_COMPLETELY')));
    }

    public function attach($isRec = 0)
    {
        $this->_listpk = 'attach_id';
        //搜索区别
        $_POST['rec'] = $isRec = isset($_REQUEST['rec']) ? t($_REQUEST['rec']) : $isRec;

        $this->pageKeyList = array('attach_id', 'name', 'size', 'uid', 'ctime', 'from', 'DOACTION');
        $this->searchKey = array('attach_id', 'name', 'from');

        $this->opt['from'] = array_merge(array('-1' => L('PUBLIC_ALL_STREAM')), $this->from);
        $this->pageTab[] = array('title' => L('PUBLIC_FILE_MANAGEMENT'), 'tabHash' => 'list', 'url' => U('admin/Content/attach'));
        $this->pageTab[] = array('title' => L('PUBLIC_RECYCLE_BIN'), 'tabHash' => 'rec', 'url' => U('admin/Content/attachRec'));

        $this->pageButton[] = array('title' => L('PUBLIC_FILE_STREAM_SEARCH'), 'onclick' => "admin.fold('search_form')");
        if ($isRec == 0) {
            $this->pageButton[] = array('title' => L('PUBLIC_FILE_STREAM_DEL'), 'onclick' => "admin.ContentEdit('','delAttach','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_FILE_STREAM')."');");
        } else {
            $this->pageButton[] = array('title' => L('PUBLIC_REMOVE_COMPLETELY'), 'onclick' => "admin.ContentEdit('','deleteAttach','".L('PUBLIC_REMOVE_COMPLETELY')."','".L('PUBLIC_FILE_STREAM')."')");
        }

        $isRec == 1 && $_REQUEST['tabHash'] = 'rec';
        $this->assign('pageTitle', $isRec ? L('PUBLIC_RECYCLE_BIN') : L('PUBLIC_FILE_MANAGEMENT'));
        $map['is_del'] = $isRec == 1 ? 1 : 0;    //未删除的
        !empty($_POST['attach_id']) && $map['attach_id'] = array('in', explode(',', $_POST['attach_id']));
        $_POST['from'] > 0 && $map['from'] = intval($_POST['from'] - 1);
        !empty($_POST['name']) && $map['name'] = array('like', '%'.t($_POST['name']).'%');

        $listData = model('Attach')->getAttachList($map, '*', 'attach_id desc', 10);

        //$listData = model('Comment')->getCommentList($map,'comment_id desc',20);
        $image = array('png', 'jpg', 'gif', 'jpeg', 'bmp');

        foreach ($listData['data'] as &$v) {
            $user = model('User')->getUserInfo($v['uid']);
            $v['uid'] = $user['space_link'];
            $v['name'] = in_array($v['extension'], $image)
                            ? '<a href="'.U('widget/Upload/down', array('attach_id' => $v['attach_id'])).'">'.
                                "<img src='".getImageUrl($v['save_path'].$v['save_name'], 225)."' width='100'><br/>{$v['name']}</a>"
                            : '<a href="'.U('widget/Upload/down', array('attach_id' => $v['attach_id'])).'">'.$v['name'].'</a>';
            $v['size'] = byte_format($v['size']);
            $v['from'] = $this->from[$v['from']];
            $v['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
            $v['DOACTION'] = $isRec == 0 ? "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['attach_id']},\"delAttach\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_FILE_STREAM')."\");'>".L('PUBLIC_STREAM_DELETE').'</a>'
                                        : "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['attach_id']},\"AttachRecover\",\"".L('PUBLIC_RECOVER').'","'.L('PUBLIC_FILE_STREAM')."\")'>".L('PUBLIC_RECOVER').'</a>';
        }
        $this->displayList($listData);
    }

    //回收站
    public function attachRec()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_attach';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->attach(1);
    }

    //恢复
    public function attachRecover()
    {
        echo json_encode(model('Attach')->doEditAttach($_POST['id'], 'attachRecover', L('PUBLIC_RECOVER')));
    }

    //假删除
    public function delAttach()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'delAttach', array('name' => $ids, 'k1' => '删除附件到回收站'), true);
        }
        echo json_encode(model('Attach')->doEditAttach($_POST['id'], 'delAttach', L('PUBLIC_STREAM_DELETE')));
    }

    //真删除
    public function deleteAttach()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'deleteAttach', array('name' => $ids, 'k1' => '彻底删除附件'), true);
        }
        echo json_encode(model('Attach')->doEditAttach($_POST['id'], 'deleteAttach', L('PUBLIC_REMOVE_COMPLETELY')));
    }

    //TODO 临时放着 后面要移动到messagemodel中

    /**
     * 视频管理.
     */
    public function video($is_del = 0)
    {
        $this->_listpk = 'video_id';
        //搜索区别
        $_POST['is_del'] = $isRec = isset($_REQUEST['is_del']) ? t($_REQUEST['is_del']) : $isRec;

        $this->pageKeyList = array('video_id', 'name', 'size', 'uid', 'ctime', 'from', 'DOACTION');
        $this->searchKey = array('video_id','from');
        $this->opt['from'] = array_merge(array('-1' => L('PUBLIC_ALL_STREAM')), $this->from);
        $this->pageTab[] = array('title' => '视频列表', 'tabHash' => 'list', 'url' => U('admin/Content/video'));
        $this->pageTab[] = array('title' => L('PUBLIC_RECYCLE_BIN'), 'tabHash' => 'rec', 'url' => U('admin/Content/videoRec'));
        $this->pageTab[] = array('title' => '视频配置', 'tabHash' => 'video_config', 'url' => U('admin/Content/video_config'));

        $this->pageButton[] = array('title' => L('PUBLIC_FILE_STREAM_SEARCH'), 'onclick' => "admin.fold('search_form')");
        if ($is_del == 0) {
            $this->pageButton[] = array('title' => L('PUBLIC_FILE_STREAM_DEL'), 'onclick' => "admin.ContentEdit('','delVideo','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_FILE_STREAM')."');");
        } else {
            $this->pageButton[] = array('title' => L('PUBLIC_REMOVE_COMPLETELY'), 'onclick' => "admin.ContentEdit('','deleteVideo','".L('PUBLIC_REMOVE_COMPLETELY')."','".L('PUBLIC_FILE_STREAM')."')");
        }

        $is_del == 1 && $_REQUEST['tabHash'] = 'rec';
        $this->assign('pageTitle', $is_del ? L('PUBLIC_RECYCLE_BIN') : L('视频管理'));
        $map['is_del'] = $is_del == 1 ? 1 : 0;    //未删除的
        !empty($_POST['video_id']) && $map['video_id'] = array('in', explode(',', $_POST['video_id']));
        $_POST['from'] > 0 && $map['from'] = intval($_POST['from'] - 1);
        !empty($_POST['name']) && $map['name'] = array('like', '%'.t($_POST['name']).'%');
        // $listData = model('Attach')->getAttachList($map,'*','attach_id desc',10);
        $listData = D('video')->where($map)->order('ctime DESC')->findPage(20);

        foreach ($listData['data'] as &$v) {
            $user = model('User')->getUserInfo($v['uid']);
            $v['uid'] = $user['space_link'];
            $v['name'] = $v['image_path'] ? '<a target="_blank" href="'.SITE_URL.$v['video_path'].'">'.
                                "<img src='".SITE_URL.$v['image_path']."' width='100'><br/>{$v['name']}</a>"
                            : '<a target="_blank" href="'.SITE_URL.$v['video_path'].'">'.$v['name'].'</a>';
            $v['size'] = byte_format($v['size']);
            $v['from'] = $this->from[$v['from']];
            $v['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
            $v['DOACTION'] = $is_del == 0 ? "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['video_id']},\"delVideo\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_FILE_STREAM')."\");'>".L('PUBLIC_STREAM_DELETE').'</a>'
                                        : "<a href='javascript:void(0)' onclick='admin.ContentEdit({$v['video_id']},\"VideoRecover\",\"".L('PUBLIC_RECOVER').'","'.L('PUBLIC_FILE_STREAM')."\")'>".L('PUBLIC_RECOVER').'</a>';
        }
        $this->displayList($listData);
    }

    //回收站
    public function videoRec()
    {
        $this->pageKey = APP_NAME.'_'.MODULE_NAME.'_video';
        $this->searchPageKey = 'S_'.$this->pageKey;
        $this->video(1);
    }

    //恢复
    public function videoRecover()
    {
        echo json_encode(model('Video')->doEditVideo($_POST['id'], 'videoRecover', L('PUBLIC_RECOVER')));
    }

    //假删除
    public function delvideo()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'delvideo', array('name' => $ids, 'k1' => '删除视频到回收站'), true);
        }
        echo json_encode(model('Video')->doEditVideo($_POST['id'], 'delVideo', L('PUBLIC_STREAM_DELETE')));
    }

    //真删除
    public function deletevideo()
    {
        if($_POST['id']){
            //记录操作日志
            $ids = is_array($_POST['id']) ? implode(',',$_POST['id']) : $_POST['id'];
            LogRecord('admin_system', 'deletevideo', array('name' => $ids, 'k1' => '彻底删除视频'), true);
        }
        echo json_encode(model('Video')->doEditVideo($_POST['id'], 'deleteVideo', L('PUBLIC_REMOVE_COMPLETELY')));
    }

    //视频配置
    public function video_config()
    {
        $this->assign('pageTitle', L('视频配置'));
        $this->pageTab[] = array('title' => '视频列表', 'tabHash' => 'list', 'url' => U('admin/Content/video'));
        $this->pageTab[] = array('title' => L('PUBLIC_RECYCLE_BIN'), 'tabHash' => 'rec', 'url' => U('admin/Content/videoRec'));
        $this->pageTab[] = array('title' => '视频配置', 'tabHash' => 'video_config', 'url' => U('admin/Content/video_config'));
        $data = model('Xdata')->get('admin_Content:video_config');
        // dump($data);exit;
        $this->pageKeyList = array('ffmpeg_path', 'video_server', 'video_ext', 'video_size', 'video_transfer_async');
        $this->opt['video_transfer_async'] = array(0 => '否', 1 => '是');
        $this->savePostUrl = U('admin/Content/do_video_config');
        $this->displayConfig($data);
    }
    //下载用户
    public function download_user()
    {
        $this->assign('pageTitle', L('下载用户'));
        $data = M('check_download')->findPage(20);

        foreach ($data['data'] as &$value){
            $value['ctime'] = date('Y-m-d H:i',$value['ctime']);
        }
        $this->pageKeyList = array('phone', 'ctime');
        $this->displayList($data);
    }
    public function do_video_config()
    {
        $list = $_POST['systemdata_list'];
        $key = $_POST['systemdata_key'];
        $key = $list.':'.$key;
        $value['ffmpeg_path'] = $_POST['ffmpeg_path'];
        $value['video_server'] = $_POST['video_server'];
        $value['video_ext'] = $_POST['video_ext'];
        $value['video_size'] = $_POST['video_size'];
        $value['video_transfer_async'] = $_POST['video_transfer_async'];
        $res = model('Xdata')->put($key, $value);
        if ($res) {
            LogRecord('admin_system', 'do_video_config', array('name' => '', 'k1' => '视频配置'), true);
            $this->success(L('PUBLIC_ADMIN_OPRETING_SUCCESS'));
        } else {
            $this->error(L('PUBLIC_ADMIN_OPRETING_ERROR'));
        }
    }

    /**
     * 举报管理.
     */
    public function denounce($map)
    {
        $_GET['id'] && $map['id'] = array('in', explode(',', t($_GET['id'])));
        $_GET['uid'] && $map['uid'] = array('in', explode(',', t($_GET['uid'])));
        $_GET['fuid'] && $map['fuid'] = array('in', explode(',', t($_GET['fuid'])));
        $_GET['from'] && $map['from'] = t($_GET['from']);
        $map['state'] = $_GET['state'] ? $_GET['state'] : '0';
        $data = model('Denounce')->getFromList($map);
        $data['state'] = $map['state'];
        $this->assign($data);
        if (is_array($map) && count($map) == '1') {
            unset($map);
        }
        $this->assign($_GET);
        $this->assign('id', t($_GET['id']));
        $this->assign('uid', t($_GET['uid']));
        $this->assign('fuid', t($_GET['fuid']));
        $this->assign('from', t($_GET['from']));
        $this->assign('isSearch', empty($map) ? '0' : '1');
        $this->display('denounce');
    }

    /**
     * 删除举报回收站内容.
     *
     * @return int 是否删除成功
     */
    public function doDeleteDenounce()
    {
        // 判断参数
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        if($_POST['ids']){
            if($_POST['state']){
                //记录操作日志
                $ids = is_array($_POST['ids']) ? implode(',',$_POST['ids']) : $_POST['ids'];
                LogRecord('admin_system', 'doDeleteDenounce', array('name' => $ids, 'k1' => '彻底删除举报内容'), true);
            }else{
                //记录操作日志
                $ids = is_array($_POST['ids']) ? implode(',',$_POST['ids']) : $_POST['ids'];
                LogRecord('admin_system', 'doDelDenounce', array('name' => $ids, 'k1' => '删除举报内容到回收站'), true);
            }

        }

        $data[] = L('PUBLIC_CONTENT_REPORT_DELETE');
        $map['id'] = array('in', t($_POST['ids']));
        $data[] = model('Denounce')->where($map)->findAll();
        // todo 记录知识
        echo model('Denounce')->deleteDenounce(t($_POST['ids']), intval($_POST['state'])) ? '1' : '0';
    }

    /**
     * 撤销举报内容.
     *
     * @return int 是否撤销成功
     */
    public function doReviewDenounce()
    {
        // 判断参数
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        if($_POST['ids']){
            //记录操作日志
            $ids = is_array($_POST['ids']) ? implode(',',$_POST['ids']) : $_POST['ids'];
            LogRecord('admin_system', 'doReviewDenounce', array('name' => $ids, 'k1' => '撤销举报内容'), true);
        }
        $data[] = L('PUBLIC_CONTENT_REPORT_REVOKE');
        $map['id'] = array('in', t($_POST['ids']));
        $data[] = model('Denounce')->where($map)->findall();
        //todo 记录知识
        echo model('Denounce')->reviewDenounce(t($_POST['ids'])) ? '1' : '0';
    }

    /**
     * 话题管理.
     */
    public function topic()
    {
        $this->assign('pageTitle', '话题管理');
        // 设置列表主键
        $this->_listpk = 'topic_id';
        $this->pageTab[] = array('title' => '话题管理', 'tabHash' => 'list', 'url' => U('admin/Content/topic'));
        $this->pageTab[] = array('title' => '推荐话题', 'tabHash' => 'recommendTopic', 'url' => U('admin/Content/topic', array('recommend' => 1)));
        $this->pageTab[] = array('title' => '添加话题', 'tabHash' => 'addTopic', 'url' => U('admin/Content/addTopic'));
        $this->pageButton[] = array('title' => '搜索话题', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '批量屏蔽', 'onclick' => "admin.setTopic(3,'',0)");
        $this->searchKey = array('topic_id', 'topic_name', 'lock');
        $this->searchPostUrl = U('admin/Content/topic', array('tabHash' => $_REQUEST['tabHash'], 'recommend' => $_REQUEST['recommend']));
        $this->opt['recommend'] = array('0' => L('PUBLIC_SYSTEMD_NOACCEPT'), '1' => '是', '2' => '否');
        $this->opt['essence'] = array('0' => L('PUBLIC_SYSTEMD_NOACCEPT'), '1' => '是', '2' => '否');
        $this->opt['lock'] = array('0' => L('PUBLIC_SYSTEMD_NOACCEPT'), '1' => '是', '2' => '否');
        $this->pageKeyList = array('topic_id', 'topic_name', 'note', 'domain', 'des', 'pic', 'topic_user', 'outlink', 'DOACTION');
        //dump($_POST);exit;
        $listData = model('FeedTopicAdmin')->getTopic('', $_REQUEST['recommend']);
        foreach ($listData['data'] as $k => &$v) {
            $v['note'] = "<div style='width:400px; border:0; margin:0; padding:0;'>".$v['note'].'</div>';
        }
        //dump($listData);exit;
        $this->displayList($listData);
    }

    /**
     * 添加话题.
     */
    public function addTopic()
    {
        $this->assign('pageTitle', '添加话题');
        $this->pageTab[] = array('title' => '话题管理', 'tabHash' => 'list', 'url' => U('admin/Content/topic'));
        $this->pageTab[] = array('title' => '推荐话题', 'tabHash' => 'recommendTopic', 'url' => U('admin/Content/topic', array('recommend' => 1)));
        $this->pageTab[] = array('title' => '添加话题', 'tabHash' => 'addTopic', 'url' => U('admin/Content/addTopic'));
        $this->pageKeyList = array('topic_name', 'note', 'domain', 'des', 'pic', 'topic_user', 'outlink', 'recommend');
        $topic['domain'] = SITE_URL.'/topics/'.'<input type="text" value="" name="domain" id="form_domain">';
        $this->opt['recommend'] = array('1' => '是', '0' => '否');
        //$this->opt['essence'] = array('1'=>'是','0'=>'否');
        $this->notEmpty = array('topic_name', 'note');
        // 表单URL设置
        $this->savePostUrl = U('admin/Content/doAddTopic');
        $this->onsubmit = 'admin.topicCheck(this)';
        $this->onload[] = "$('#search_uids').val('');";
        $this->displayConfig($topic);
    }

    /**
     * 执行添加话题.
     */
    public function doAddTopic()
    {
        t($_POST['topic_name']) == '' && $this->error('话题名称不能为空');
        t($_POST['note']) == '' && $this->error('话题注释不能为空');
        $map['topic_name'] = t($_POST['topic_name']);
        if (model('FeedTopic')->where($map)->find()) {
            $this->error('此话题已存在');
        }
        if ($_POST['domain'] != '') {
            $map1['domain'] = t($_POST['domain']);
            if (model('FeedTopic')->where($map1)->find()) {
                $this->error('此话题域名已存在');
            }
        }
        if (h(t($_POST['outlink'])) != '') {
            $res = preg_match('/^(?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*)/', h($_POST['outlink']));
            if (!$res) {
                $this->error('外链格式错误');
            }
        }
        $res = model('FeedTopicAdmin')->addTopic($_POST);
        if ($res) {
            $this->assign('jumpUrl', U('admin/Content/topic'));
            $this->success(L('PUBLIC_ADD_SUCCESS'));
        } else {
            $this->error(model('FeedTopicAdmin')->getError());
        }
    }

    /**
     * 设置话题为推荐、精华或屏蔽.
     *
     * @return array 操作成功状态和提示信息
     */
    public function setTopic()
    {
        if (empty($_POST['topic_id'])) {
            $return['status'] = 0;
            $return['data'] = '';
            echo json_encode($return);
            exit();
        }
        switch (intval($_POST['type'])) {
            case '1':
                $field = 'recommend';
                break;
            case '2':
                $field = 'essence';
                break;
            case '3':
                $field = 'lock';
                break;
        }
        if (intval($_POST['value']) == 1) {
            $value = 0;
        } else {
            $value = 1;
        }
        !is_array($_POST['topic_id']) && $_POST['topic_id'] = array($_POST['topic_id']);
        $map['topic_id'] = array('in', $_POST['topic_id']);
        $result = model('FeedTopic')->where($map)->setField($field, $value);
        if (!$result) {
            $return['status'] = 0;
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_ERROR');
        } else {
            $return['status'] = 1;
            $return['data'] = L('PUBLIC_ADMIN_OPRETING_SUCCESS');
            model('Cache')->set('feed_topic_recommend', null);
        }
        echo json_encode($return);
        exit();
    }

    /**
     * 编辑话题.
     */
    public function editTopic()
    {
        $this->assign('pageTitle', '编辑话题');
        $this->pageTab[] = array('title' => '话题管理', 'tabHash' => 'list', 'url' => U('admin/Content/topic'));
        $this->pageTab[] = array('title' => '推荐话题', 'tabHash' => 'recommendTopic', 'url' => U('admin/Content/topic', array('recommend' => 1)));
        $this->pageTab[] = array('title' => '添加话题', 'tabHash' => 'addTopic', 'url' => U('admin/Content/addTopic'));
        $this->pageTab[] = array('title' => '编辑话题', 'tabHash' => 'editTopic', 'url' => U('admin/Content/editTopic', array('topic_id' => intval($_GET['topic_id']), 'tabHash' => 'editTopic')));
        $this->pageKeyList = array('topic_id', 'topic_name', 'note', 'domain', 'des', 'pic', 'topic_user', 'outlink', 'recommend');
        $this->opt['recommend'] = array('1' => '是', '0' => '否');
        //$this->opt['essence'] = array('1'=>'是','0'=>'否');
        $topic = model('FeedTopic')->where('topic_id='.intval($_GET['topic_id']))->find();
        if ($topic['pic']) {
            $pic = D('attach')->where('attach_id='.$topic['pic'])->find();
            $pic_url = $pic['save_path'].$pic['save_name'];
            $topic['pic_url'] = getImageUrl($pic_url);
        }
        $topic['domain'] = SITE_URL.'/topics/'.'<input type="text" value="'.$topic['domain'].'" name="domain" id="form_domain">';
        $this->notEmpty = array('note');
        $this->savePostUrl = U('admin/Content/doEditTopic');
        $this->onsubmit = 'admin.topicCheck(this)';
        $this->displayConfig($topic);
    }

    /**
     * 执行编辑话题.
     */
    public function doEditTopic()
    {
        //$_POST['name']=="" && $this->error('话题名称不能为空');
        $_POST['note'] == '' && $this->error('话题注释不能为空');
        //$map['topic_id'] = array('neq', $_POST['topic_id']);
        //$map['name'] = t($_POST['name']);
        //if(model('FeedTopic')->where($map)->find()) $this->error('此话题已存在');
        if ($_POST['domain'] != '') {
            $map1['topic_id'] = array('neq', $_POST['topic_id']);
            $map1['domain'] = t($_POST['domain']);
            if (model('FeedTopic')->where($map1)->find()) {
                $this->error('此话题域名已存在');
            }
        }
        if (h(t($_POST['outlink'])) != '') {
            $res = preg_match('/^(?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*)/', h($_POST['outlink']));
            if (!$res) {
                $this->error('外链格式错误');
            }
        }
        //$data['name'] = t($_POST['name']);
        $data['note'] = t($_POST['note']);
        $data['domain'] = t($_POST['domain']);
        $data['des'] = h($_POST['des']);
        $data['pic'] = t($_POST['pic']);
        $data['topic_user'] = t($_POST['topic_user']);
        $data['outlink'] = t($_POST['outlink']);
        $data['recommend'] = intval($_POST['recommend']);
        if ($data['recommend'] == 1) {
            if (!D('feed_topic')->where('topic_id='.intval($_POST['topic_id']))->getField('recommend_time')) {
                $data['recommend_time'] = time();
            }
        } else {
            if (D('feed_topic')->where('topic_id='.intval($_POST['topic_id']))->getField('recommend_time')) {
                $data['recommend_time'] = 0;
            }
        }
        $data['essence'] = intval($_POST['essence']);
        $res = D('feed_topic')->where('topic_id='.intval($_POST['topic_id']))->save($data);
        if ($res !== false) {
            $this->assign('jumpUrl', U('admin/Content/topic'));
            $this->success(L('PUBLIC_SYSTEM_MODIFY_SUCCESS'));
        } else {
            $this->error(D('feed_topic')->getError());
        }
    }

    /**
     * 模板管理页面.
     */
    public function template()
    {
        $this->assign('pageTitle', '模板管理');

        $this->pageTab[] = array('title' => '模板管理', 'tabHash' => 'template', 'url' => U('admin/Content/template'));
        $this->pageTab[] = array('title' => '添加模板', 'tabHash' => 'upTemplate', 'url' => U('admin/Content/upTemplate'));

        $this->pageButton[] = array('title' => '添加模板', 'onclick' => "location.href='".U('admin/Content/upTemplate')."'");
        $this->pageButton[] = array('title' => '删除模板', 'onclick' => 'admin.delTemplate()');

        $this->pageKeyList = array('tpl_id', 'name', 'alias', 'title', 'body', 'lang', 'type', 'type2', 'is_cache', 'ctime', 'DOACTION');
        // 获取模板数据
        $listData = model('Template')->getTemplate();
        foreach ($listData['data'] as &$value) {
            $value['is_cache'] = ($value['is_cache'] == 1) ? '是' : '否';
            $value['ctime'] = date('Y-m-d H:i:s', $value['ctime']);
            $value['DOACTION'] = '<a href="'.U('admin/Content/upTemplate', array('tpl_id' => $value['tpl_id'])).'">编辑</a>&nbsp;-&nbsp;<a href="javascript:;" onclick="admin.delTemplate('.$value['tpl_id'].')">删除</a>';
        }

        $this->displayList($listData);
    }

    /**
     * 添加/编辑模板页面.
     */
    public function upTemplate()
    {
        $_REQUEST['tabHash'] = 'upTemplate';
        $this->pageTab[] = array('title' => '模板管理', 'tabHash' => 'template', 'url' => U('admin/Content/template'));
        if (isset($_GET['tpl_id'])) {
            $this->assign('pageTitle', '编辑模板');
            $this->pageTab[] = array('title' => '编辑模板', 'tabHash' => 'upTemplate', 'url' => U('admin/Content/upTemplate', array('tpl_id' => intval($_GET['tpl_id']))));
        } else {
            $this->assign('pageTitle', '添加模板');
            $this->pageTab[] = array('title' => '添加模板', 'tabHash' => 'upTemplate', 'url' => U('admin/Content/upTemplate'));
        }

        $this->pageKeyList = array('tpl_id', 'name', 'alias', 'title', 'body', 'lang', 'type', 'type2', 'is_cache');
        $this->opt['is_cache'] = array('否', '是');

        $this->notEmpty = array('name', 'lang');
        $this->onsubmit = 'admin.checkTemplate(this)';

        // 获取信息
        $detail = array();
        if (isset($_GET['tpl_id'])) {
            $tplId = intval($_GET['tpl_id']);
            $detail = model('Template')->getTemplateById($tplId);
        }

        $this->savePostUrl = !empty($detail) ? U('admin/Content/doSaveTemplate') : U('admin/Content/doAddTemplate');

        $this->displayConfig($detail);
    }

    public function doAddTemplate()
    {
        $data['name'] = t($_POST['name']);
        $data['alias'] = t($_POST['alias']);
        $data['title'] = t($_POST['title']);
        $data['body'] = t($_POST['body']);
        $data['lang'] = t($_POST['lang']);
        $data['type'] = t($_POST['type']);
        $data['type2'] = t($_POST['type2']);
        $data['is_cache'] = intval($_POST['is_cache']);
        $result = model('Template')->addTemplate($data);
        if ($result) {
            $this->assign('jumpUrl', U('admin/Content/template'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function doSaveTemplate()
    {
        $tplId = intval($_POST['tpl_id']);
        $data['name'] = t($_POST['name']);
        $data['alias'] = t($_POST['alias']);
        $data['title'] = t($_POST['title']);
        $data['body'] = t($_POST['body']);
        $data['lang'] = t($_POST['lang']);
        $data['type'] = t($_POST['type']);
        $data['type2'] = t($_POST['type2']);
        $data['is_cache'] = intval($_POST['is_cache']);
        $result = model('Template')->upTemplate($tplId, $data);
        if ($result) {
            $this->assign('jumpUrl', U('admin/Content/template'));
            $this->success('编辑成功');
        } else {
            $this->error('编辑失败');
        }
    }

    public function doDelTemplate()
    {
        $tplId = intval($_POST['id']);
        $result = array();
        if (empty($tplId)) {
            $result['status'] = 0;
            $result['data'] = '删除失败';
            exit(json_encode($result));
        }
        // 删除指定模板
        $res = model('Template')->delTemplate($tplId);
        if ($res) {
            $result['status'] = 1;
            $result['data'] = '删除成功';
        } else {
            $result['status'] = 0;
            $result['data'] = '删除失败';
        }
        exit(json_encode($result));
    }



    /**
     *
     * 分享统计管理
     *
     */
    public function feed_manage()
    {
        //查询分享统计用户组
        $u_map = array();
        if($_GET['uid']){
            $this->assign('get_uid', $_GET['uid']);
            $u_map['u.uid'] = array('in', explode(',', t($_GET['uid'])));
        }
        if($_GET['uname']){
            $this->assign('uname', $_GET['uname']);
            $u_map['u.uname'] = array('in', explode(',', "'".t($_GET['uname'])."'"));
        }
        if($_GET['date']){
            $this->assign('date', $_GET['date']);
        }else{
            $this->assign('date', date('Y-m',time()));
        }
        $u_map['ul.user_group_id'] = 9;
        $users = model('UserGroupLink')->getFeedManage($u_map);

        $now = date('Y-m',time());
        $date = $_GET['date']==''?$now:$_GET['date'];
        $y = date('m',strtotime($date));
        //表格头部
        $count = date("t",strtotime($date));
        $title_list = array();
        for($i=1;$i<$count+1;$i++){
            if($i<10){
                $d = $y.'/0'.$i;
            }else{
                $d = $y.'/'.$i;
            }
            $title_list[] = $d;
        }
        $def_title = array('用户ID','用户名');
        $title_merge = array_merge($def_title,$title_list);
        foreach($users['data'] as $k => &$v){
            $v['detail'] = $this->getFeedDetail($v['uid'],$date);
        }
        $this->assign('title_merge', $title_merge);
        $this->assign('users', $users);
        $this->display('feed_manage');
    }


    /**
     * @param $uid
     * @param $date
     * @return array
     *
     * 用户月详情
     */
    private function getFeedDetail($uid,$date){
        $map = array();
        $map['is_del'] = 0;
        $map['uid'] = $uid;
        //获取本月第一天与最后一天
        $firstday = date('Y-m-01', strtotime($date));
        $firsttime = strtotime(date('Y-m-01', strtotime($date)));
        $lastday = strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")).' 23:59:59');
        $map['publish_time'] = array('between',array($firsttime,$lastday));
        $listData = model('Feed')->field('feed_id,publish_time,is_recommend')->where($map)->select();
        //根据当月总天数循环
        $count = date("t",strtotime($date));
        $month_count = 0;
        $detail_list = array();
        for($i=1;$i<$count+1;$i++){
            if($i<10){
                $d = '0'.$i;
            }else{
                $d = $i;
            }
            $day = date('Y-m-'.$d, strtotime($date));
            $arr = array();
            $arr['day'] = $i;
            $day_count = $this->getDayCount($listData,$day);
            $month_count += $day_count;
            $arr['day_count'] = $day_count;
            $arr['hot_count'] = $this->getHotCount($listData,$day);
            $arr['month_count'] = $month_count;
            $detail_list[] = $arr;
        }
        return $detail_list;
    }


    /**
     * @param $arr
     * @param $vale
     * @return bool
     *
     * 记录天总数
     */
    private function getDayCount($arr,$vale){
        $count = 0;
        foreach($arr as $val){
            $publish_time = date('Y-m-d', $val['publish_time']);
            if($vale == $publish_time){
                $count += 1;
            }
        }
        return $count;
    }


    /**
     * @param $arr
     * @param $vale
     * @return bool
     *
     * 记录天推荐总数
     */
    private function getHotCount($arr,$vale){
        $count = 0;
        foreach($arr as $val){
            $publish_time = date('Y-m-d', $val['publish_time']);
            if($vale == $publish_time&&$val['is_recommend']){
                $count += 1;
            }
        }
        return $count;
    }


    /**
     *
     * 特定用户添加
     *
     */
    public function feed_manage_add()
    {
        $uid = $_POST['uid'];
        $uname = $_POST['uname'];
        if($uid==''&&$uname==''){
            echo 0;exit;
        }

        $map = array();
        if($uid){
            $map['uid'] = $uid;
        }
        if($uname){
            $map['uname'] = $uname;
        }

        $userInfo = D('User')->field('uid')->where($map)->find();
        if($userInfo){
            //将用户设置为特定用户
            $u_map = array();
            $u_map['uid'] = $userInfo['uid'];
            $u_map['user_group_id'] = 9;
            $UserGroupLink = D('UserGroupLink')->field('uid')->where($u_map)->find();
            if($UserGroupLink){
                echo 1;
            }else{
                $users = D('UserGroupLink')->add($u_map);
                if($users){
                    echo 1;exit;
                }else{
                    echo 0;exit;
                }
            }

        }else{
            echo 0;exit;
        }
    }


    /**
     *
     * 特定用户添加
     *
     */
    public function comment_digg_manage_add()
    {
        $uid = $_POST['uid'];
        $uname = $_POST['uname'];
        if($uid==''&&$uname==''){
            echo 0;exit;
        }

        $map = array();
        if($uid){
            $map['uid'] = $uid;
        }
        if($uname){
            $map['uname'] = $uname;
        }

        $userInfo = D('User')->field('uid')->where($map)->find();
        if($userInfo){
            //将用户设置为特定用户
            $u_map = array();
            $u_map['uid'] = $userInfo['uid'];
            $u_map['user_group_id'] = 10;
            $UserGroupLink = D('UserGroupLink')->field('uid')->where($u_map)->find();
            if($UserGroupLink){
                echo 1;
            }else{
                $users = D('UserGroupLink')->add($u_map);
                if($users){
                    echo 1;exit;
                }else{
                    echo 0;exit;
                }
            }

        }else{
            echo 0;exit;
        }
    }


    /**
     *
     * 取消该用户为特定用户
     *
     */
    public function doDeleteFeedManage(){
        // 判断参数
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        //记录操作日志
        LogRecord('admin_system', 'doDeleteFeedManage', array('name' => $_POST['ids'], 'k1' => '取消用户为特定用户'), true);
        $map = array();
        $map['uid'] = array('in', t($_POST['ids']));
        $map['user_group_id'] = 9;
        echo D('UserGroupLink')->where($map)->delete() ? '1' : '0';
    }


    /**
     *
     * 取消该用户为特定用户
     *
     */
    public function doDeleteCommentManage(){
        // 判断参数
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        //记录操作日志
        LogRecord('admin_system', 'doDeleteCommentManage', array('name' => $_POST['ids'], 'k1' => '取消用户为评论点赞特定用户'), true);
        $map = array();
        $map['uid'] = array('in', t($_POST['ids']));
        $map['user_group_id'] = 10;
        echo D('UserGroupLink')->where($map)->delete() ? '1' : '0';
    }

    //群聊管理
    public function message_list()
    {
        $this->searchKey = array('list_id', 'from_uid', 'title');
        $this->pageButton[] = array('title' => '搜索群聊', 'onclick' => "admin.fold('search_form')");
        $map = array();
        $this->searchPostUrl = U('admin/Content/message_list', array('tabHash' => $_REQUEST['tabHash']));
        !empty($_POST['list_id']) && $map['list_id'] = array('in', explode(',', $_POST['list_id']));
        !empty($_POST['from_uid']) && $map['from_uid'] = array('in', explode(',', $_POST['from_uid']));
        !empty($_POST['title']) && $map['title'] = array('like', '%'.t($_POST['title']).'%');
        $listData =  model('Message')->getMessageListForAdmin($map,$orderby,20);
        $this->pageKeyList = array('list_id', 'from_uid', 'title', 'member_num', 'mtime', 'DOACTION');
        $this->pageTab[] = array('title' => '群聊列表', 'tabHash' => 'message_list', 'url' => U('admin/content/message_list'));
        $this->pageTab[] = array('title' => '热门群聊', 'tabHash' => 'message_list_hot', 'url' => U('admin/content/message_list_hot'));
        
        $this->displayList($listData);
    }

    //热门群聊
    public function message_list_hot()
    {
        $this->searchKey = array('list_id', 'from_uid', 'title');
        $this->pageButton[] = array('title' => '搜索群聊', 'onclick' => "admin.fold('search_form')");
        $map = array('isHot' => 1);
        $this->searchPostUrl = U('admin/Content/message_list_hot', array('tabHash' => $_REQUEST['tabHash']));
        !empty($_POST['list_id']) && $map['list_id'] = array('in', explode(',', $_POST['list_id']));
        !empty($_POST['from_uid']) && $map['from_uid'] = array('in', explode(',', $_POST['from_uid']));
        !empty($_POST['title']) && $map['title'] = array('like', '%'.t($_POST['title']).'%');
        $listData =  model('Message')->getMessageListForAdmin($map,' display_order ASC',20);
        $this->pageKeyList = array('list_id', 'from_uid', 'title', 'member_num', 'mtime','display_order', 'DOACTION');
        $this->pageTab[] = array('title' => '群聊列表', 'tabHash' => 'message_list', 'url' => U('admin/content/message_list'));
        $this->pageTab[] = array('title' => '热门群聊', 'tabHash' => 'message_list_hot', 'url' => U('admin/content/message_list_hot'));
        
        $this->displayList($listData);
    }


    public function editMessageList()
    {
        $list_id = intval($_GET['list_id']);
        empty($list_id) && $this->error('请选择正确的群聊');

        $detail = model('Message')->getMessageListInfo($list_id);
        empty($detail) && $this->error('请选择正确的群聊');
        $this->pageTab[] = array('title' => '群聊列表', 'tabHash' => 'message_list', 'url' => U('admin/content/message_list'));
        $this->pageTab[] = array('title' => '热门群聊', 'tabHash' => 'message_list_hot', 'url' => U('admin/content/message_list_hot'));

        $user_info = model('User')->getUserInfo($detail['from_uid']);
        $detail['from_uid'] = $user_info['uname'];
        $this->pageKeyList = array('list_id', 'from_uid', 'title', 'max_num', 'content', 'isHot', 'display_order');
        $this->savePostUrl = U('admin/Content/doSaveMessageList',array('list_id' => $list_id));
        $this->displayConfig($detail);
    }

    public function doSaveMessageList()
    {
        $list_id = intval($_GET['list_id']);
        empty($list_id) && $this->error('请选择正确的群聊');

        $data['title'] = t($_POST['title']);
        $data['content'] = t($_POST['content']);
        $data['isHot'] = intval($_POST['isHot']);
        $data['max_num'] = intval($_POST['max_num']);
        $data['display_order'] = intval($_POST['display_order']);
        D('message_list')->where(array('list_id' => $list_id))->save($data);

        $this->success('编辑成功');
    }


    /**
     *
     * 评论，转发，点赞统计管理
     *
     */
    public function comment_digg_manage()
    {
        //查询分享统计用户组
        $u_map = array();
        //$_GET['uid'] = 18;
        if($_GET['uid']){
            $this->assign('get_uid', $_GET['uid']);
            $u_map['u.uid'] = array('in', explode(',', t($_GET['uid'])));
        }
        if($_GET['uname']){
            $this->assign('uname', $_GET['uname']);
            $u_map['u.uname'] = array('in', explode(',', "'".t($_GET['uname'])."'"));
        }
        if($_GET['date']){
            $this->assign('date', $_GET['date']);
        }else{
            $this->assign('date', date('Y-m',time()));
        }
        $u_map['ul.user_group_id'] = 10;
        $users = model('UserGroupLink')->getFeedManage($u_map);

        $now = date('Y-m',time());
        $date = $_GET['date']==''?$now:$_GET['date'];
        $y = date('m',strtotime($date));
        //表格头部
        if(empty($_GET['date'])||$_GET['date']==$now){
            $count = intval(date("d",time()));
        }else{
            $count = intval(date("t",strtotime($date)));
        }

        $title_list = array();
        for($i=1;$i<$count+1;$i++){
            if($i<10){
                $d = $y.'/0'.$i;
            }else{
                $d = $y.'/'.$i;
            }
            $title_list[] = $d;
        }
        $def_title = array('用户ID','用户名');
        $title_merge = array_merge($def_title,$title_list);
        foreach($users['data'] as $k => &$v){
            $v['detail'] = $this->getCommentDiggDetail($v['uid'],$date);
        }
        $this->assign('title_merge', $title_merge);
        $this->assign('users', $users);
        $this->display('comment_digg_manage');
    }



    /**
     * @param $uid
     * @param $date
     * @return array
     *
     * 用户评论转发点赞月详情
     */
    private function getCommentDiggDetail($uid,$date){
        //获取本月第一天与最后一天
        $firstday = date('Y-m-01', strtotime($date));
        $firsttime = strtotime(date('Y-m-01', strtotime($date)));
        $lastday = strtotime(date('Y-m-d', strtotime("$firstday +1 month -1 day")).' 23:59:59');

        //查询时间段内，用户总评论
        $map = array();
        $map['is_del'] = 0;
        $map['uid'] = $uid;
        $map['ctime'] = array('between',array($firsttime,$lastday));
        $commentList = model('Comment')->field('comment_id,ctime')->where($map)->select();

        //查询时间段内，用户总转发
        $zf_map = array();
        $zf_map['is_del'] = 0;
        $zf_map['uid'] = $uid;
        $zf_map['is_repost'] = 1;
        $zf_map['publish_time'] = array('between',array($firsttime,$lastday));
        $feedList = model('Feed')->field('feed_id,publish_time')->where($zf_map)->select();

        //获取时间段内，用户总点赞
        $digg_map = array();
        $digg_map['uid'] = $uid;
        $digg_map['cTime'] = array('between',array($firsttime,$lastday));
        $diggList = model('FeedDigg')->field('id,cTime')->where($digg_map)->select();

        //根据当月总天数循环
        $now = date('Y-m',time());
        if(empty($date)||$date==$now){
            $count = intval(date("d",time()));
        }else{
            $count = intval(date("t",strtotime($date)));
        }

        $detail_list = array();
        for($i=1;$i<$count+1;$i++){
            if($i<10){
                $d = '0'.$i;
            }else{
                $d = $i;
            }
            $day = date('Y-m-'.$d, strtotime($date));
            $arr = array();
            $arr['day'] = $i;
            $arr['day_comment_count'] = $this->get_day_comment_count($commentList,$day);
            $arr['day_repost_count'] = $this->get_day_repost_count($feedList,$day);
            $arr['day_digg_count'] = $this->get_day_digg_count($diggList,$day);

            $detail_list[] = $arr;
        }
        return $detail_list;
    }


    /**
     * @param $arr
     * @param $vale
     * @return int
     *
     * 计算每天评论条数
     */
    private function get_day_comment_count($arr,$vale){
        $count = 0;
        foreach($arr as $val){
            $publish_time = date('Y-m-d', $val['ctime']);
            if($vale == $publish_time){
                $count += 1;
            }
        }
        return $count;
    }


    /**
     * @param $arr
     * @param $vale
     * @return int
     *
     * 计算每天转发条数
     */
    private function get_day_repost_count($arr,$vale){
        $count = 0;
        foreach($arr as $val){
            $ctime = date('Y-m-d', $val['publish_time']);
            if($vale == $ctime){
                $count += 1;
            }
        }
        return $count;
    }


    /**
     * @param $arr
     * @param $vale
     * @return int
     *
     * 计算每天点赞条数
     */
    private function get_day_digg_count($arr,$vale){
        $count = 0;
        foreach($arr as $val){
            $ctime = date('Y-m-d', $val['cTime']);
            if($vale == $ctime){
                $count += 1;
            }
        }
        return $count;
    }


    /**
     * @throws PHPExcel_Exception
     *
     * 分享统计导出
     */
    public function feed_manage_export(){
        set_time_limit(0);
        $u_map = array();
        if($_GET['uid']){
            $u_map['u.uid'] = array('in', explode(',', t($_GET['uid'])));
        }
        if($_GET['uname']){
            $u_map['u.uname'] = array('in', explode(',', "'".t($_GET['uname'])."'"));
        }
        $u_map['ul.user_group_id'] = 9;
        $users = model('UserGroupLink')->getFeedManageAll($u_map);

        $now = date('Y-m',time());
        $date = $_GET['date']==''?$now:$_GET['date'];
        $y = date('m',strtotime($date));
        //表格头部
        $count = date("t",strtotime($date));
        $title_list = array();
        for($i=1;$i<$count+1;$i++){
            if($i<10){
                $d = $y.'/0'.$i;
            }else{
                $d = $y.'/'.$i;
            }
            $title_list[] = $d;
        }
        $def_title = array('用户ID','用户名','类型');
        $title_merge = array_merge($def_title,$title_list);

        $day_count_list = array();
        $hot_count_list = array();
        $month_count_list = array();
        foreach($users as $k => &$v){
            $details = $this->getFeedDetail($v['uid'],$date);
            $d_val = array();
            $h_val = array();
            $m_val = array();
            $d_val['uid'] = $h_val['uid'] = $m_val['uid'] = $v['uid'];
            $d_val['uname'] = $h_val['uname'] = $m_val['uname'] = $v['uname'];
            $d_val['type'] = '今日分享';
            $h_val['type'] = '今日推荐';
            $m_val['type'] = '本月累计';
            $i = 0;
            foreach($details as $p){
                $d_val['day_count'.$i] = $p['day_count'];
                $h_val['hot_count'.$i] = $p['hot_count'];
                $m_val['month_count'.$i] = $p['month_count'];
                $i++;
            }
            $day_count_list[] = $d_val;
            $hot_count_list[] = $h_val;
            $month_count_list[] = $m_val;
        }
        $data = array_merge($day_count_list,$hot_count_list,$month_count_list);
        $uids = array();
        foreach ($data as $user) {
            $uids[] = $user['uid'];
        }
        array_multisort($uids, SORT_DESC, $data);

        //创建对象
        $excel = new PHPExcel();
        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH');
        //表头数组
        $tableheader = $title_merge;//array('结算日期','结算单号','服务结算金额','支付结算金额','结算金额','实际结算金额','结算状态');
        //dump($tableheader);exit;
        //工作表设置
        $excel->setActiveSheetIndex( 0 );
        $excel->getActiveSheet()->freezePane('A2');
        $excel->getActiveSheet()->getStyle( 'A1:AH1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $excel->getActiveSheet()->getStyle( 'A1:AH1')->getFill()->getStartColor()->setARGB('#0096e5');
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $objActSheet = $excel->getActiveSheet ();
        $objActSheet->getColumnDimension('A')->setAutoSize(true);  //设置单元格宽度自适应
        $objActSheet->getColumnDimension('B')->setAutoSize(true);
        //设置列格式为，文本格式
        $excel->getActiveSheet()->setTitle('Simple');
        $excel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objActSheet->mergeCells('C:D');  //合并单元格
        //填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }

        //填充表格信息
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }
        $check_time = '分享统计.xls';
        //IE浏览器下面中文乱码解决办法
        $userBrowser = $_SERVER['HTTP_USER_AGENT'];
        if ( preg_match( '/MSIE/i', $userBrowser ) ) {
            $check_time = urlencode($check_time);
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$check_time");
        header("Content-Transfer-Encoding:binary");

        $write->save('php://output');
    }



    /**
     *
     * 评论，转发，点赞统计导出
     *
     */
    public function comment_digg_manage_export()
    {
        set_time_limit(0);
        //查询分享统计用户组
        $u_map = array();
        //$_GET['uid'] = 18;
        if($_GET['uid']){
            $u_map['u.uid'] = array('in', explode(',', t($_GET['uid'])));
        }
        if($_GET['uname']){
            $u_map['u.uname'] = array('in', explode(',', "'".t($_GET['uname'])."'"));
        }
        $u_map['ul.user_group_id'] = 10;
        $users = model('UserGroupLink')->getFeedManageAll($u_map);

        $now = date('Y-m',time());
        $date = $_GET['date']==''?$now:$_GET['date'];
        $y = date('m',strtotime($date));
        //表格头部
        if(empty($_GET['date'])||$_GET['date']==$now){
            $count = intval(date("d",time()));
        }else{
            $count = intval(date("t",strtotime($date)));
        }
        $title_list = array();
        for($i=1;$i<$count+1;$i++){
            if($i<10){
                $d = $y.'/0'.$i;
            }else{
                $d = $y.'/'.$i;
            }
            $title_list[] = $d;
        }

        $def_title = array('用户ID','用户名','类型');
        $title_merge = array_merge($def_title,$title_list);

        $day_comment_list = array();
        $day_repost_list = array();
        $day_digg_list = array();
        foreach($users as $k => &$v){
            $details = $this->getCommentDiggDetail($v['uid'],$date);
            $d_val = array();
            $h_val = array();
            $m_val = array();
            $d_val['uid'] = $h_val['uid'] = $m_val['uid'] = $v['uid'];
            $d_val['uname'] = $h_val['uname'] = $m_val['uname'] = $v['uname'];
            $d_val['type'] = '今日评论总数';
            $h_val['type'] = '今日转发总数';
            $m_val['type'] = '今日点赞总数';
            $i = 0;
            foreach($details as $p){
                $d_val['day_comment_count'.$i] = $p['day_comment_count'];
                $h_val['day_repost_count'.$i] = $p['day_repost_count'];
                $m_val['day_digg_count'.$i] = $p['day_digg_count'];
                $i++;
            }
            $day_comment_list[] = $d_val;
            $day_repost_list[] = $h_val;
            $day_digg_list[] = $m_val;
        }
        $data = array_merge($day_comment_list,$day_repost_list,$day_digg_list);
        $uids = array();
        foreach ($data as $user) {
            $uids[] = $user['uid'];
        }
        array_multisort($uids, SORT_DESC, $data);

        //创建对象
        $excel = new PHPExcel();
        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH');
        //表头数组
        $tableheader = $title_merge;
        //工作表设置
        $excel->setActiveSheetIndex( 0 );
        $excel->getActiveSheet()->freezePane('A2');
        $excel->getActiveSheet()->getStyle( 'A1:AH1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $excel->getActiveSheet()->getStyle( 'A1:AH1')->getFill()->getStartColor()->setARGB('#0096e5');
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $objActSheet = $excel->getActiveSheet ();
        $objActSheet->getColumnDimension('A')->setAutoSize(true);  //设置单元格宽度自适应
        $objActSheet->getColumnDimension('B')->setAutoSize(true);
        //设置列格式为，文本格式
        $excel->getActiveSheet()->setTitle('Simple');
        $excel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objActSheet->mergeCells('C:D');  //合并单元格
        //填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }

        //填充表格信息
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }
        $check_time = '评论点赞统计.xls';
        //IE浏览器下面中文乱码解决办法
        $userBrowser = $_SERVER['HTTP_USER_AGENT'];
        if ( preg_match( '/MSIE/i', $userBrowser ) ) {
            $check_time = urlencode($check_time);
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename=$check_time");
        header("Content-Transfer-Encoding:binary");

        $write->save('php://output');

    }


    /**
     *
     * 奥豆视频初始化Tab
     *
     */
    private function _initAodouVideoTab(){
        $this->pageTab[] = array('title' => '视频列表', 'tabHash' => 'aodou_video', 'url' => U('admin/Content/aodou_video'));
        $this->pageTab[] = array('title' => '剧集列表', 'tabHash' => 'aodou_video_data', 'url' => U('admin/Content/aodou_video_data'));
        $this->pageTab[] = array('title' => '上传剧集', 'tabHash' => 'aodou_video_data_add', 'url' => U('admin/Content/aodou_video_data_add'));
    }


    /**
     *
     * 奥豆视频-视频列表
     *
     */
    public function aodou_video(){
        $_REQUEST['tabHash'] = 'aodou_video';
        $this->_initAodouVideoTab();
        $this->pageKeyList = array('id', 'title','image_path','comment_count','digg_count','part_count','more', 'DOACTION');

        $this->searchKey = array('id','title');
        $this->$searchPostUrl = U('admin/Content/aodou_video');

        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '删除', 'onclick' => "admin.rm_aodou_video()");
        $this->pageButton[] = array('title' => '添加', 'onclick' => "location.href='".U('admin/Content/aodou_video_add')."'");

        $map = array();
        if($_POST['id']){
            $map['id'] = $_POST['id'];
        }
        if($_POST['title']){
            $map['title'] = array('like', '%'.$_POST['title'].'%');
        }

        $map['is_del'] = 0;
        $list = D('aodou_video')->where($map)->order('id DESC')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            if ($value['image_path']) {
                $pic = D('attach')->where('attach_id='.$value['image_path'])->find();
                $pic_url = $pic['save_path'].$pic['save_name'];
                $url = getImageUrl($pic_url);
                $value['image_path'] = "<img src='$url' width='160' height='90'/>";
            }
            $value['more'] = '<a href="javascript:;" onclick="admin.aodou_video_more('.$value['id'].');">[更多信息]</a>';
            $value['DOACTION'] = '<a href="'.U('admin/Content/aodou_video_edit', array('id' => $value['id'])).'">[编辑]</a> - ';
            $value['DOACTION'] .= ' <a href="javascript:;" onclick="admin.rm_aodou_video(\''.$value['id'].'\')">[删除]</a> - ';
            $value['DOACTION'] .= ' <a href="'.U('admin/Content/aodou_video_data', array('video_id' => $value['id'])).'">[剧集详情]</a>';
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    /**
     *
     * 奥豆视频-更多信息
     *
     */
    public function aodouVideoMore(){
        $id = $_GET['id'];
        $this->notEmpty = $this->pageKeyList = array('play_type', 'update_type', 'abstract');
        $aodou_video = D('aodou_video')->where('id='.$id)->find();
        $this->assign('aodou_video',$aodou_video);
        $this->display('aodouVideoMore');
    }


    /**
     *
     * 准备添加视频
     *
     */
    public function aodou_video_add(){
        $_REQUEST['tabHash'] = 'aodou_video';
        $this->_initAodouVideoTab();
        $this->pageKeyList = array('title','image_path','play_type','update_type','abstract');

        $this->notEmpty = array('title','image_path','abstract');
        $this->savePostUrl = U('admin/Content/do_aodou_video_add');
        $this->displayConfig();
    }


    /**
     *
     * 添加视频
     *
     */
    public function do_aodou_video_add(){
        if(!$_POST['title']){
            $this->error('请填写视频标题');
        }
        if(!$_POST['image_path']){
            $this->error('请选择上传视频封面图');
        }
        if(!$_POST['abstract']){
            $this->error('请填写视频简介');
        }
        $data['title'] = t($_POST['title']);
        $info = D('aodou_video')->where($data)->find();
        if($info){
            $this->error('该视频名称已存在，请更换');
        }
        $data['image_path'] = t($_POST['image_path']);
        $data['play_type'] = t($_POST['play_type']);
        $data['update_type'] = t($_POST['update_type']);
        $data['abstract'] = t($_POST['abstract']);

        $result = D('aodou_video')->add($data);
        if ($result) {
            //更新缓存
            S('api_discover_system', null);
            $this->assign('jumpUrl', U('admin/Content/aodou_video'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }


    /**
     *
     * 准备修改奥豆视频
     *
     */
    public function aodou_video_edit(){
        $_REQUEST['tabHash'] = 'aodou_video';
        $this->_initAodouVideoTab();
        $this->pageKeyList = array('id','title','image_path','play_type','update_type','abstract');
        $this->notEmpty = array('title','image_path','abstract');
        $aodou_video = D('aodou_video')->where('id='.intval($_GET['id']))->find();

        $this->savePostUrl = U('admin/Content/do_aodou_video_edit');
        $this->displayConfig($aodou_video);
    }


    /**
     *
     * 修改奥豆视频
     *
     */
    public function do_aodou_video_edit(){
        if(!$_POST['title']){
            $this->error('请填写视频标题');
        }
        if(!$_POST['image_path']){
            $this->error('请选择上传视频封面图');
        }
        if(!$_POST['abstract']){
            $this->error('请填写视频简介');
        }
        $data['title'] = t($_POST['title']);
        $data['id'] = array('neq',intval($_POST['id']));
        $info = D('aodou_video')->where($data)->find();
        if($info){
            $this->error('视频名称已存在，请更换');
        }
        $save['title'] = t($_POST['title']);
        $save['image_path'] = t($_POST['image_path']);
        $save['play_type'] = t($_POST['play_type']);
        $save['update_type'] = t($_POST['update_type']);
        $save['abstract'] = t($_POST['abstract']);
        $save['id'] = intval($_POST['id']);
        $result = D('aodou_video')->save($save);
        if (!($result===false)) {
            //更新缓存
            S('api_discover_system', null);
            $this->assign('jumpUrl', U('admin/Content/aodou_video'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }


    /**
     *
     * 删除奥豆视频
     *
     */
    public function doRmVideo(){
        $c_map = array();
        $c_map['id'] = array('in', $_POST['id']);
        if (empty($c_map['id'])) {
            exit(json_encode(array('status' => '0', 'info' => '参数错误')));
        }
        //查询下面是否有剧集，有则全部删除并删除视频文件
        $d_map = array();
        $d_map['video_id'] = $c_map['id'];
        $result = D('aodou_video_data')->field('id,video_path,image_path')->where($d_map)->select();
        if($result){
            foreach($result as $v){
                if($v['video_path']){
                    $video_url = SITE_PATH.$v['video_path'];
                    if(file_exists($video_url)){
                        unlink($video_url);
                    }
                }
                if($v['image_path']){
                    $image_url = SITE_PATH.$v['image_path'];
                    if(file_exists($image_url)){
                        unlink($image_url);
                    }
                }
                D('aodou_video_data')->where('id='.$v['id'])->delete();
            }
        }
        D('aodou_video')->where($c_map)->delete();
        //更新缓存
        S('api_discover_system', null);
        $res = array('status' => '1', 'info' => '操作成功');
        exit(json_encode($res));
    }


    /**
     *
     * 奥豆视频-剧集列表
     *
     */
    public function aodou_video_data(){
        $_REQUEST['tabHash'] = 'aodou_video_data';
        $this->_initAodouVideoTab();
        $this->pageKeyList = array('id','title','data_title','parts', 'video_name','video_size','extension','pv','ctime', 'DOACTION');

        $this->searchKey = array('id','title');
        $this->$searchPostUrl = U('admin/Content/aodou_video_data');

        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '删除', 'onclick' => "admin.rm_aodou_video_data()");
        $this->pageButton[] = array('title' => '上传剧集', 'onclick' => "location.href='".U('admin/Content/aodou_video_data_add')."'");

        $map = array();
        if($_POST['id']){
            $map['a.id'] = $_POST['id'];
        }
        if($_POST['title']){
            $map['b.title'] = array('like', '%'.$_POST['title'].'%');
        }
        if($_GET['video_id']){
            $map['b.id'] = $_GET['video_id'];
        }

        $map['a.is_del'] = 0;
        $map['b.is_del'] = 0;
        //$list = D('aodou_video')->where($map)->order('id DESC')->findPage(20);
        $tablePrefix = C('DB_PREFIX');
        $list = D('aodou_video_data')
            ->field('a.id,b.title,a.data_title,a.parts,a.uid,a.video_name,a.video_size,a.extension,a.video_path,a.pv,a.ctime')
            ->where($map)
            ->table("{$tablePrefix}aodou_video_data AS a LEFT JOIN {$tablePrefix}aodou_video AS b ON a.video_id = b.id")
            ->order('a.id DESC')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            $value['video_name'] = $value['image_path'] ? '<a target="_blank" href="'.SITE_URL.$value['video_path'].'">'.
                "<img src='".SITE_URL.$value['image_path']."' width='100'><br/>{$value['video_name']}</a>"
                : '<a target="_blank" href="'.SITE_URL.$value['video_path'].'">'.$value['video_name'].'</a>';
            $value['video_size'] = byte_format($value['video_size']);
            $value['ctime'] = date('Y-m-d H:i:s', $value['ctime']);
            $value['DOACTION'] = '<a href="'.U('admin/Content/aodou_video_data_edit', array('id' => $value['id'])).'">[编辑]</a> - ';
            $value['DOACTION'] .= ' <a href="javascript:;" onclick="admin.rm_aodou_video_data(\''.$value['id'].'\')">[删除]</a>';
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    /**
     *
     * 单集准备修改
     *
     */
    public function aodou_video_data_edit(){
        $_REQUEST['tabHash'] = 'aodou_video_data';
        $this->_initAodouVideoTab();
        // 视频列表
        $video_list = D('aodou_video')->field('id,title')->where('is_del=0')->select();
        $this->assign('video_list',$video_list);
        $id = intval($_GET['id']);
        $data_info = D('aodou_video_data')->where('id='.$id)->find();
        if($data_info['image_path']){
            $data_info['image_path'] = SITE_URL.$data_info['image_path'];
        }
        $this->assign('data_info',$data_info);
        $this->display();
    }


    /**
     *
     * 单集修改
     *
     */
    public function do_aodou_video_data_edit(){
        set_time_limit(0);
        if(!$_POST['video_id']){
            $this->error('选择所属视频');
        }
        if(!$_POST['parts']){
            $this->error('请填写集数');
        }
        //单集封面图判断
        if($_FILES['image_path']['name']){
            $data_image_path = pathinfo($_FILES['image_path']['name']);
            $img_ext = $data_image_path['extension'];
            $allowExts =  array('jpg','jpeg','png');
            $img_uploadCondition =  in_array(strtolower($img_ext), $allowExts, true);
            if ($img_uploadCondition) {
                $savePath = SITE_PATH.$this->_getSavePath();
                $filename = uniqid();    //文件名称
                $image_name = $filename.'.'.$img_ext;
                if (@move_uploaded_file($_FILES['image_path']['tmp_name'], $savePath.'/'.$image_name)) {
                    $result['image_path'] = $this->_getSavePath().'/'.$image_name;
                    if ($image_info = getimagesize($savePath.'/'.$image_name)) {
                        $result['image_width'] = $image_info[0];
                        $result['image_height'] = $image_info[1];
                    }
                }else{
                    $this->error('封面图上传失败');
                }
            }else{
                $this->error('封面图格式有误，请重新上传');
            }
        }
        //单集视频判断
        if ($_FILES['file']['name']) {
            $videoinfo = pathinfo($_FILES['file']['name']);
            $video_ext = $videoinfo['extension'];
            $allowExts =  array('mp4');
            $uploadCondition = in_array(strtolower($video_ext), $allowExts, true);
            if(!$uploadCondition){
                $this->error('视频格式有误，请重新上传');
            }
            //判断该视频是否已经上传过了
            $map = array();
            $map['video_id'] = intval($_POST['video_id']);
            $map['parts'] = intval($_POST['parts']);
            $map['video_name'] = t($_FILES['file']['name']);
            $count = D('aodou_video_data')->where($map)->count();
            if($count){
                $this->error('这一集已经存在了，请检查');
            }
            $video_config = model('Xdata')->get('admin_Content:video_config');
            $savePath = SITE_PATH.$this->_getSavePath(); //网页视频文件夹
            $sourceSavePath = $savePath.'/source';  //源文件文件夹
            $partSavePath = $savePath.'/part';  //视频片段文件夹
            if (!file_exists($sourceSavePath)) {
                mkdir($sourceSavePath, 0777, true);
            }
            if (!file_exists($partSavePath)) {
                mkdir($partSavePath, 0777, true);
            }
            $filename = uniqid();    //文件名称
            $image_name = $filename.'.jpg';
            $video_source_name = $filename.'.'.$video_ext;   //源视频名称
            $video_name = $filename.'.mp4';   //视频名称

            if (@move_uploaded_file($_FILES['file']['tmp_name'], $sourceSavePath.'/'.$video_source_name)) {
                //上传视频到源视频文件夹
                if (PATH_SEPARATOR == ':') {  //Linux
                    $ffmpegpath = $video_config['ffmpeg_path'];
                } else {     //Windows
                    $ffmpegpath = SITE_PATH.$video_config['ffmpeg_path'];
                }
                //获取时长
                $result['timeline'] = $this->get_video_timeline($ffmpegpath, $sourceSavePath.'/'.$video_source_name);
                //截图
                if(!$result['image_path']){
                    $this->get_video_image($ffmpegpath, $sourceSavePath.'/'.$video_source_name, $savePath.'/'.$image_name);
                    $result['image_path'] = $this->_getSavePath().'/'.$image_name;
                    if ($image_info = getimagesize($savePath.'/'.$image_name)) {
                        $result['image_width'] = $image_info[0];
                        $result['image_height'] = $image_info[1];
                    }
                }
                $result['video_name'] = t($_FILES['file']['name']);
                $result['video_size'] = intval($_FILES['file']['size']);
                $result['extension'] = $video_ext;
                $result['video_path'] = $this->_getSavePath().'/source/'.$video_name;

                //CDN缓存预热
                $video_config = model('Xdata')->get('admin_Content:video_config');
                $video_server = $video_config['video_server'] ? $video_config['video_server'] : SITE_URL;
                $cdn = model('CdnPushObject');
                $objectPath = $video_server.$result['video_path']."\n";
                $objectPath .= $video_server.$result['image_path'];
                $cdn->setObjectPath($objectPath);
                $cdn->doAction();
            } else {
                $this->error('视频上传失败');
            }
        }

        $result['id'] = intval($_POST['id']);
        $result['video_id'] = intval($_POST['video_id']);
        $result['parts'] = intval($_POST['parts']);
        $result['uid'] = $GLOBALS['ts']['mid'];
        $result['data_title'] = t($_POST['data_title']);
        $data_id = D('aodou_video_data')->save($result);
        if (!($data_id===false)) {
            //更新缓存
            S('api_video_data_'.intval($_POST['video_id']).'_'.intval($_POST['parts']),null);
            $this->assign('jumpUrl', U('admin/Content/aodou_video_data'));
            $this->success('修改成功');
        }else{
            $this->error('视频修改失败');
        }
    }



    /**
     *
     * 准备添加视频
     *
     */
    public function aodou_video_data_add(){
        $_REQUEST['tabHash'] = 'aodou_video_data_add';
        $this->_initAodouVideoTab();

        // 视频列表
        $video_list = D('aodou_video')->field('id,title')->where('is_del=0')->select();
        $this->assign('video_list',$video_list);
        $this->display();
    }

    //上传临时文件
    private function _getSavePath()
    {
        $savePath = '/data/video/'.date('Y/md');
        $fullPath = SITE_PATH.$savePath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        return $savePath;
    }


    /**
     * @param $ffmpegpath
     * @param $input
     * @return bool|string
     *
     * 获取视频时长
     */
    public function get_video_timeline($ffmpegpath, $input)
    {
        if (!file_exists($input)) {
            return false;
        }
        if (!$ffmpegpath) {
            $data = model('Xdata')->get('admin_Content:video_config');
            if (PATH_SEPARATOR == ':') {  //Linux
                $ffmpegpath = $data['ffmpeg_path'];
            } else {     //Windows
                $ffmpegpath = SITE_PATH.$data['ffmpeg_path'];
            }
        }
        $command = "$ffmpegpath -i ".$input." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//";
        $timeline = exec($command);
        $time_arr = explode(':', $timeline);
        $timeline = $time_arr[0] * 3600 + $time_arr[1] * 60 + intval($time_arr[2]);

        return $timeline;
    }


    /**
     * @param $ffmpegpath
     * @param $input
     * @param $output
     * @param string $fromdurasec
     * @return bool
     *
     * 获取视频图片
     */
    public function get_video_image($ffmpegpath, $input, $output, $fromdurasec = '01')
    {
        if (!file_exists($input)) {
            return false;
        }
        if (!$ffmpegpath) {
            $data = model('Xdata')->get('admin_Content:video_config');
            if (PATH_SEPARATOR == ':') {  //Linux
                $ffmpegpath = $data['ffmpeg_path'];
            } else {     //Windows
                $ffmpegpath = SITE_PATH.$data['ffmpeg_path'];
            }
        }

        $command = "$ffmpegpath -i $input -an -ss 00:00:$fromdurasec -r 1 -vframes 1 -f mjpeg -y $output";
        exec($command);
    }

    /**
     * @param $ffmpegpath
     * @param $input
     * @param $output
     * @param string $begin_second
     * @param string $end_second
     * @return bool
     *
     * 获取视频前5秒，视频片段
     */
    public function get_video_part($ffmpegpath, $input, $output, $begin_second = '01', $end_second = '05')
    {
        if (!file_exists($input)) {
            return false;
        }
        if (!$ffmpegpath) {
            $data = model('Xdata')->get('admin_Content:video_config');
            if (PATH_SEPARATOR == ':') {  //Linux
                $ffmpegpath = $data['ffmpeg_path'];
            } else {     //Windows
                $ffmpegpath = SITE_PATH.$data['ffmpeg_path'];
            }
        }
        $command = "$ffmpegpath -ss 00:00:$begin_second -i $input -t 00:00:$end_second $output";
        exec($command);
    }

    /**
     *
     * 添加视频
     *
     */
    public function do_aodou_video_data_add(){
        set_time_limit(0);
        if(!$_POST['video_id']){
            $this->error('选择所属视频');
        }
        if(!$_POST['parts']){
            $this->error('请填写集数');
        }
        //单集封面图判断
        if($_FILES['image_path']['name']){
            $data_image_path = pathinfo($_FILES['image_path']['name']);
            $img_ext = $data_image_path['extension'];
            $allowExts =  array('jpg','jpeg','png');
            $img_uploadCondition =  in_array(strtolower($img_ext), $allowExts, true);
            //如果视频上传正确.
            if ($img_uploadCondition) {
                $savePath = SITE_PATH.$this->_getSavePath();
                $filename = uniqid();    //文件名称
                $image_name = $filename.'.'.$img_ext;
                if (@move_uploaded_file($_FILES['image_path']['tmp_name'], $savePath.'/'.$image_name)) {
                    $result['image_path'] = $this->_getSavePath().'/'.$image_name;
                    if ($image_info = getimagesize($savePath.'/'.$image_name)) {
                        $result['image_width'] = $image_info[0];
                        $result['image_height'] = $image_info[1];
                    }
                }else{
                    $this->error('封面图上传失败');
                }
            }else{
                $this->error('封面图有误，请重新上传');
            }
        }
        //单集视频判断
        if ($_FILES['file']['name']) {
            $videoinfo = pathinfo($_FILES['file']['name']);
            $video_ext = $videoinfo['extension'];
            $allowExts =  array('mp4');
            $uploadCondition = in_array(strtolower($video_ext), $allowExts, true);
            if(!$uploadCondition){
                $this->error('视频格式有误，请重新上传');
            }
            //判断该视频是否已经上传过了
            $map = array();
            $map['video_id'] = intval($_POST['video_id']);
            $map['parts'] = intval($_POST['parts']);
            $map['video_name'] = t($_FILES['file']['name']);
            $count = D('aodou_video_data')->where($map)->count();
            if($count){
                $this->error('这一集已经存在了，请检查');
            }
            $video_config = model('Xdata')->get('admin_Content:video_config');
            $savePath = SITE_PATH.$this->_getSavePath(); //网页视频文件夹
            $sourceSavePath = $savePath.'/source';  //源文件文件夹
            $partSavePath = $savePath.'/part';  //视频片段文件夹
            if (!file_exists($sourceSavePath)) {
                mkdir($sourceSavePath, 0777, true);
            }
            if (!file_exists($partSavePath)) {
                mkdir($partSavePath, 0777, true);
            }
            $filename = uniqid();    //文件名称
            $image_name = $filename.'.jpg';
            $video_source_name = $filename.'.'.$video_ext;   //源视频名称
            $video_name = $filename.'.mp4';   //视频名称
            //记录日志
            //$file = fopen("log.txt","a+");
            //fwrite($file,"URL地址：".$sourceSavePath.'/'.$video_source_name."\t\n");
            if (@move_uploaded_file($_FILES['file']['tmp_name'], $sourceSavePath.'/'.$video_source_name)) {
                //上传视频到源视频文件夹
                if (PATH_SEPARATOR == ':') {  //Linux
                    $ffmpegpath = $video_config['ffmpeg_path'];
                } else {     //Windows
                    $ffmpegpath = SITE_PATH.$video_config['ffmpeg_path'];
                }
                //获取时长
                $result['timeline'] = $this->get_video_timeline($ffmpegpath, $sourceSavePath.'/'.$video_source_name);
                //截图
                if(!$result['image_path']){
                    $this->get_video_image($ffmpegpath, $sourceSavePath.'/'.$video_source_name, $savePath.'/'.$image_name);
                    $result['image_path'] = $this->_getSavePath().'/'.$image_name;
                    if ($image_info = getimagesize($savePath.'/'.$image_name)) {
                        $result['image_width'] = $image_info[0];
                        $result['image_height'] = $image_info[1];
                    }
                }

                //截取视频前5秒
                //$this->get_video_part($ffmpegpath, $sourceSavePath.'/'.$video_name, $partSavePath.'/'.$video_name);

                $result['video_id'] = intval($_POST['video_id']);
                $result['parts'] = intval($_POST['parts']);
                $result['video_name'] = t($_FILES['file']['name']);
                $result['video_size'] = intval($_FILES['file']['size']);
                $result['extension'] = $video_ext;
                $result['video_path'] = $this->_getSavePath().'/source/'.$video_name;

                $result['ctime'] = time();
                $result['uid'] = $GLOBALS['ts']['mid'];
                $result['is_audit'] = 1;
                $result['data_title'] = t($_POST['data_title']);
                $data_id = D('aodou_video_data')->add($result);
                if ($data_id) {
                    //视频总集数增加
                    $map = array();
                    $map['id'] = intval($_POST['video_id']);
                    $map['part_count'] = array('exp','part_count+1');
                    D('aodou_video')->save($map);
                    //CDN缓存预热
                    $video_config = model('Xdata')->get('admin_Content:video_config');
                    $video_server = $video_config['video_server'] ? $video_config['video_server'] : SITE_URL;
                    $cdn = model('CdnPushObject');
                    $objectPath = $video_server.$result['video_path']."\n";
                    $objectPath .= $video_server.$result['image_path'];
                    $cdn->setObjectPath($objectPath);
                    $cdn->doAction();
                    $this->assign('jumpUrl', U('admin/Content/aodou_video_data'));
                    $this->success('上传成功');
                }else{
                    $this->error('视频添加失败');
                }
            } else {
                $this->error('视频上传失败');
            }
        }else{
            $this->error('请上传视频文件');
        }
    }



    /**
     *
     * 删除奥豆视频
     *
     */
    public function doRmVideoData(){
        $c_map = array();
        $c_map['id'] = array('in', $_POST['id']);
        if (empty($c_map['id'])) {
            exit(json_encode(array('status' => '0', 'info' => '参数错误')));
        }
        //查询下面是否有剧集，有则全部删除并删除视频文件
        $result = D('aodou_video_data')->field('id,video_path,image_path,video_id,parts')->where($c_map)->select();
        if($result){
            foreach($result as $v){
                if($v['video_path']){
                    $video_url = SITE_PATH.$v['video_path'];
                    if(file_exists($video_url)){
                        unlink($video_url);
                    }
                }
                if($v['image_path']){
                    $image_url = SITE_PATH.$v['image_path'];
                    if(file_exists($image_url)){
                        unlink($image_url);
                    }
                }
                D('aodou_video_data')->where('id='.$v['id'])->delete();
                //总集数累减
                $save = array();
                $save['id'] = $v['video_id'];
                $save['part_count'] = array('exp','part_count - 1');
                D('aodou_video')->save($save);
                //更新缓存
                S('api_video_data_'.intval($result['video_id']).'_'.intval($result['parts']),null);
            }
        }
        $res = array('status' => '1', 'info' => '操作成功');
        exit(json_encode($res));
    }

    /**
     *
     * 修改内容频道
     *
     */
    public function channelChange(){
        $feed_id = intval($_POST['feed_id']);
        if (empty($feed_id)) {
            $this->ajaxReturn('','内容不存在',0);
        } else {
            $channel = intval($_POST['channel']);
            if(is_numeric($channel)){
                if($channel != 0){
                    $where['feed_id'] = $feed_id;
                    $res = D('channel')->where($where)->find();
                    if($res){
                        $data['channel_category_id'] = $channel;
                        $res1=D('channel')->where($where)->save($data);
                    }else{
                        $data['feed_id'] = $feed_id;
                        $data['channel_category_id'] = $channel;
                        $res1=D('channel')->add($data);
                    }
                    if($res1){
                        $res= D("channel")->where('feed_id='.$feed_id)->find();
                        $html = '<select id="change_' . $feed_id . '" onchange=admin.channel(' . $feed_id . ')>';
                        $channel = D('channel_category')->select();
                        foreach ($channel as $k => $value) {
                            if ($res['channel_category_id'] == $value['channel_category_id']) {
                                $html.= '<option value="'. $value['channel_category_id'].'" selected="selected">';
                            } else {
                                $html .= '<option value="'.$value['channel_category_id'].'" >';
                            }
                            $html.= $value['title'];
                            $html.= '</option>';
                        }
                        $html.= '<option value="0">';
                        $html.= "未指定频道";
                        $html.= '</option>';
                        $html.= '</select>';
                        $this->ajaxReturn($html,'修改频道成功',1);
                    }else{
                        $this->ajaxReturn('','频道没有变化',0);
                    }
                }else{
                    $where['feed_id'] = $feed_id;
                    $res2 = D('channel')->where($where)->delete();
                    if($res2){
                        $html ='<select id="change_'.$feed_id .'"onchange=admin.channel('.$feed_id .')>';
                        $channel = D('channel_category')->select();
                        foreach ($channel as $k =>$value){
                            $html.= '<option value="'.$value['channel_category_id'].'" >';
                            $html.= $value['title'];
                            $html.= '</option>';
                        }
                        $html.= '<option value="0" selected="selected">';
                        $html.= "未指定频道";
                        $html.= '</option>';
                        $html.= '</select>';
                        $this->ajaxReturn($html,'修改频道成功',1);
                    }else{
                        $this->ajaxReturn('','频道没有变化',0);
                    }
                }
            }else{
                $this->ajaxReturn('','请选择正确频道',0);
            }
        }
    }
    public function sweepHot(){
        $map['a.is_recommend'] = 1;
        $map['a.is_audit'] = 1;
        $map['a.type'] = 'postvideo';
        $channel = D('channel_category')->where('title="视频"')->find();
        $map['b.channel_category_id'] = array('neq',$channel['channel_category_id']);
        $list = model('Feed')->getHostlist($map);
        $num = $list['count'];
        $i = 0;//
        $c = 0;//
        foreach ($list['data'] as &$value){
            $where['feed_id'] = $value['feed_id'];
            $where['channel_category_id']=$channel['channel_category_id'];
            $find = D('channel')->where($where)->find();
            if(!$find) {
                $data['uid'] = $value['uid'];
                $size = D('video')->where('feed_id='.$value['feed_id'])->find();
                if($size){
                    $data['width'] = $size['image_width'];
                    $data['height'] = $size['image_height'];
                }
                $data['feed_id'] = $value['feed_id'];
                $data['channel_category_id'] = $channel['channel_category_id'];
                $res = D('channel')->add($data);
                if($res){
                    $i++;
                }
            }else{
                $c++;
            }
        }
        if(($i+$c) == $num){
            $this->success('推送成功');
        }else{
            $this->error('推送失败');
        }
    }

    /**
     * 修改频道
     */
    public function changeChannel()
    {
        $list = D('channel_category')->select();
        $this->assign('list', $list);
        $feed_id = intval($_GET['feed_id']);
        $channelInfo = D('channel')->where('feed_id='.$feed_id)->select();
        $channel = array();
        foreach ($channelInfo as $value){
            $channel[] = $value['channel_category_id'];
        }
        $this->assign('channel', $channel);
        $this->assign('feed_id',$feed_id);
        $this->display('changeChannel');
    }

    /**
     * 修改频道
     */
    public function editChannel()
    {
        $feed_id = intval($_POST['feed_id']);
        $channel = $_POST['channel'];
        $i = 0;
        $m = 0;
        $feed_ids = D('channel')->where('feed_id='.$feed_id)->select();
        $cateArr = array();
        //删除原先不在当前选择下的频道
        if($feed_ids){
            foreach ($feed_ids as $k){
                $cateArr[] = $k['channel_category_id'];
            }
            $arr1 = array_diff($cateArr,$channel);
            if($arr1){
                foreach ($arr1 as &$v){
                    $where1['feed_id'] = $feed_id;
                    $where1['channel_category_id'] = $v;
                    D('channel')->where($where1)->delete();
                    $m++;
                }
            }
        }
        //把新增的频道增加进去
        foreach ($feed_ids as $k){
            $cateArr[] = $k['channel_category_id'];
        }
        $arr2 = array_diff($channel,$cateArr);
        $find = D('feed')->where('feed_id='.$feed_id)->find();
        if($find['type'] =='postvideo'){
            $size = D('video')->where('feed_id='.$feed_id)->find();
            if($size){
                $data['width'] = $size['image_width'];
                $data['height'] = $size['image_height'];
            }
        }
        $data['uid'] = $find['uid'];
        $data['feed_id'] = $feed_id;
        foreach ($arr2 as &$v){
            $data['channel_category_id'] = $v;
            D('channel')->add($data);
            $i++;
        }
        if($i ==0 && $m==0){
            $return['status'] = 0;
            $return['data'] = '没有频道被修改';
        }elseif($i!=0 || $m!=0){
            $return['status'] = 1;
            $return['data'] = '修改成功';
        }else{
            $return['status'] = 0;
            $return['data'] = '修改失败';
        }
        echo json_encode($return);
        exit();

    }

}
