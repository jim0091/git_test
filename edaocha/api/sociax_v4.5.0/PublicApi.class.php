<?php

use Apps\Event\Model\Cate;
use Apps\Event\Model\Event;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * 公开api接口.
 *
 * @author Medz Seven <lovevipdsw@vip.qq.com>
 **/
class PublicApi extends Api
{
    public function getAreaAll()
    {
        return Capsule::table('area')->get();
    }

    /**
     * 按照层级获取地区列表.
     *
     * @request int     $pid     地区ID
     *
     * @param bool $notsort 是否不排序，默认排序
     *
     * @return array
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getArea()
    {
        $pid = intval($this->data['pid']);
        $pid or
        $pid = 0;

        isset($this->data['notsort']) or
        $notsort = false;
        $notsort = (bool) $this->data['notsort'];

        $list = model('Area')->getAreaList($pid);

        if ($notsort) {
            return $list;
        }

        $areas = array();
        foreach ($list as $area) {
            $pre = getShortPinyin($area['title'], 'utf-8', '#');

            /* 多音字处理 */
            if ($area['title'] == '重庆') {
                $pre = 'C';
            }

            if (!isset($areas[$pre]) or !is_array($areas[$pre])) {
                $areas[$pre] = array();
            }
            array_push($areas[$pre], $area);
        }
        ksort($areas);

        return $areas;
    }

    /**
     * 获取application幻灯数据.
     *
     * @return array
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function getSlideShow()
    {
        $list = D('application_slide')->field('`title`, `image`, `type`, `data`')->select();

        foreach ($list as $key => $value) {
            $value['image'] = getImageUrlByAttachId($value['image']);
            $list[$key] = $value;
        }

        return $list;
    }

    /**
     * 获取关于我们HTML信息.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function showAbout()
    {
        ob_end_clean();
        ob_start();
        header('Content-Type:text/html;charset=utf-8');
        echo '<!DOCTYPE html>',
        '<html lang="zh">',
        '<head><title>关于我们</title></head>',
        '<body>',
        json_decode(json_encode(model('Xdata')->get('admin_Application:about')), false)->content,
        '</body>',
        '</html>';
        ob_end_flush();
        exit;
    }

    /**
     * 获取用户协议HTML信息.
     *
     * @author bs
     **/
    public function showAgreement()
    {
        ob_end_clean();
        ob_start();
        header('Content-Type:text/html;charset=utf-8');
        echo '<!DOCTYPE html>',
        '<html lang="zh">',
        '<head><title>用户协议</title></head>',
        '<body>',
        json_decode(json_encode(model('Xdata')->get('admin_Application:agreement')), false)->content,
        '</body>',
        '</html>';
        ob_end_flush();
        exit;
    }

    /**
     * 发现.
     *
     * @return array
     *
     * @author hhh <missu082500@163.com>
     **/
    public function discover()
    {
        $open_arr = !empty($this->data['needs']) ? explode(',', t($this->data['needs'])) : array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12');
        $type = !empty($this->data['type']) ? t($this->data['type']) : 'system';
        $list = S('api_discover_'.$type);

        if (!$list) {
            $list = array();

            // 轮播图
            if (in_array('1', $open_arr)) {
                $banners = $this->getSlideShow();
                $list['banner'] = $banners ? $banners : array();
            }

            // 微吧
            if (in_array('2', $open_arr)) {
                $wmap['recommend'] = 1;
                $wmap['status'] = 1;
                $wmap['is_del'] = 0;
                $weiba_recommend = D('Weiba')->where($wmap)->limit(8)->findAll();

                $weiba_id = getSubByKey($weiba_recommend, 'weiba_id');
                $followStatus = api('Weiba')->getFollowStateByWeibaids($this->mid, $weiba_id);
                foreach ($weiba_recommend as $k => $v) {
                    $weiba_recommend[$k]['logo'] = getImageUrlByAttachId($v['logo'], 200, 200) ?: '';
                    $weiba_recommend[$k]['following'] = $followStatus[$v['weiba_id']]['following'];
                    if ($v['new_day'] != date('Y-m-d', time())) {
                        $weiba_recommend[$k]['new_count'] = 0;
                        api('Weiba')->setNewcount($v['weiba_id'], 0);
                    }
                    $weiba_recommend[$k]['notify'] = $v['notify'] ?: '';
                    $weiba_recommend[$k]['info'] = $v['info'] ?: '';
                    $weiba_recommend[$k]['title'] = formatEmoji(false, $weiba_recommend[$k]['title']) ?: '';
                    $weiba_recommend[$k]['content'] = formatEmoji(false, $weiba_recommend[$k]['content']) ?: '';
                }
                $list['weibas'] = $weiba_recommend ? $weiba_recommend : array();
            }

            // 话题
            if (in_array('3', $open_arr)) {
                $tmap['recommend'] = 1;
                $tmap['lock'] = 0;
                $topic_recommend = D('FeedTopic')->where($tmap)->order('count desc')->limit(8)->field('topic_id,topic_name,pic')->findAll();

                foreach ($topic_recommend as $key => $value) {
                    if ($value['pic'] != null) {
                        $topic_recommend[$key]['pic'] = getImageUrlByAttachId($value['pic'], 100, 100);
                    } else {
                        $topic_recommend[$key]['pic'] = '';
                    }
                }
                $list['topics'] = $topic_recommend ? $topic_recommend : array();
            }

            //频道
            if (in_array('4', $open_arr)) {
                $cmap['pid'] = 0;
                $channel_recommend = D('ChannelCategory')->where($cmap)->order('sort asc')->limit(8)->field('channel_category_id,title,ext')->findAll();

                foreach ($channel_recommend as $key => $value) {
                    $serialize = unserialize($value['ext']);
                    if ($serialize['attach'] != '') {
                        $channel_recommend[$key]['pic'] = getImageUrlByAttachId($serialize['attach'], 100, 100);
                    } else {
                        $channel_recommend[$key]['pic'] = '';
                    }
                    unset($channel_recommend[$key]['ext']);
                }
                $list['channels'] = $channel_recommend ? $channel_recommend : array();
            }

            //资讯
            if (in_array('5', $open_arr)) {
                $tconf = model('Xdata')->get('Information_Admin:config');
                $hotTime = intval($tconf['hotTime']);
                if ($hotTime > 0) {
                    $hotTime = 60 * 60 * 24 * $hotTime;
                    $hotTime = time() - $hotTime;
                    $imap['ctime'] = array('gt', intval($hotTime));
                }

                $imap['isPre'] = 0;
                $imap['isDel'] = 0;
                $information_recommend = D('InformationList')->where($imap)->order('hits desc')->limit(8)->field('id,subject,content')->findAll();

                foreach ($information_recommend as $key => $value) {
                    preg_match_all('/\<img(.*?)src\=\"(.*?)\"(.*?)\/?\>/is', $value['content'], $image);
                    $image = $image[2];
                    if ($image && is_array($image) && count($image) >= 1) {
                        $image = $image[array_rand($image)];
                        if (!preg_match('/https?\:\/\//is', $image)) {
                            $image = parse_url(SITE_URL, PHP_URL_SCHEME).'://'.parse_url(SITE_URL, PHP_URL_HOST).'/'.$image;
                        }
                    }
                    $information_recommend[$key]['pic'] = !empty($image) ? $image : '';
                    $information_recommend[$key]['url'] = sprintf('%s/api.php?mod=Information&act=reader&id=%d', SITE_URL, intval($value['id']));
                    unset($information_recommend[$key]['content']);
                }
                $list['information'] = $information_recommend ? $information_recommend : array();
            }

            //找人
            if (in_array('6', $open_arr)) {
                $user = model('RelatedUser')->getRelatedUser(8);
                $user_list = array();

                foreach ($user as $k => $v) {
                    $user_list[$k]['uid'] = $v['userInfo']['uid'];
                    $user_list[$k]['uname'] = $v['userInfo']['uname'];
                    $user_list[$k]['remark'] = $v['userInfo']['remark'] ? $v['userInfo']['remark'] : '';
                    $user_list[$k]['avatar'] = $v['userInfo']['avatar_big'];
                    $privacy = model('UserPrivacy')->getPrivacy($this->mid, $v['userInfo']['uid']);
                    $user_list[$k]['space_privacy'] = $privacy['space'];
                }
                $list['users'] = $user_list;
            }

            //附近的人
            if (in_array('7', $open_arr)) {
                $users = api('FindPeople')->around();

                foreach ($users['data'] as $key => $value) {
                    if (!empty($value)) {
                        $findp['uid'] = $value['uid'];
                        $findp['uname'] = $value['username'];
                        $findp['remark'] = $value['remark'] ? $value['remark'] : '';
                        $findp['avatar'] = $value['avatar'];
                        $privacy = model('UserPrivacy')->getPrivacy($this->mid, $value['uid']);
                        $findp['space_privacy'] = $privacy['space'];
                        $fpeople[] = $findp;
                    }
                }
                $list['near_users'] = $fpeople ? $fpeople : array();
            }

            //积分商城
            if (in_array('8', $open_arr)) {
                $db_prefix = C('DB_PREFIX');
                $gifts = M('')->field('gift.id, gift.image, gift.name')
                    ->table("{$db_prefix}gift AS gift LEFT JOIN {$db_prefix}gift_log AS log ON gift.id=log.gid")
                    ->where('gift.isDel = 0')
                    ->group('gid')
                    ->order('COUNT(`gid`) DESC')
                    ->limit(8)
                    ->findAll();

                foreach ($gifts as $key => &$value) {
                    $value['image'] = getImageUrlByAttachId($value['image']) ?: '';
                }
                $list['gifts'] = $gifts ? $gifts : array();
            }

            //活动
            if (in_array('11', $open_arr)) {
                $num = 5; //随机五条
                $data = Event::getInstance()->getRightEvent($num);
                foreach ($data as $key => &$value) {
                    $_event = $value;
                    $_event['area'] = model('Area')->getAreaById($_event['area']);
                    $_event['area'] = $_event['area']['title'];
                    $_event['city'] = model('Area')->getAreaById($_event['city']);
                    $_event['city'] = $_event['city']['title'];
                    $_event['image'] = getImageUrlByAttachId($_event['image']) ?: '';
                    $_event['cate'] = Cate::getInstance()->getById($_event['cid']);
                    $_event['cate'] = $_event['cate']['name'];
                    $event[] = $_event;
                }

                $list['event'] = $event ? $event : array();
            }

            //奥豆视频
            if (in_array('12', $open_arr)) {
                $map = array();
                $map['is_del'] = 0;
                $data = D('aodou_video')->field('id as video_id,title,image_path,part_count')->where($map)->order('id DESC')->select();
                foreach ($data as $key => &$value) {
                    if ($value['image_path']) {
                        $pic = D('attach')->where('attach_id='.$value['image_path'])->find();
                        $pic_url = $pic['save_path'].$pic['save_name'];
                        $value['image_path'] = getImageUrl($pic_url); //封面图url
                        $value['image_width'] = $pic['width']; //封面图宽度
                        $value['image_height'] = $pic['height']; //封面图高度
                    }
                }

                $list['aodou_video'] = $data;
            }

            S('api_discover_'.$type, $list, 1800);
        }

        //直播
        if (in_array('9', $open_arr)) {
            $lives_url = 'http://zbtest.zhibocloud.cn/stream/getList';
            $lives_rs = file_get_contents($lives_url);
            $lives_rs = json_decode($lives_rs, true);

            if ($lives_rs['data']) {
                foreach ($lives_rs['data'] as $key => $value) {
                    if ($key > 8) {
                        break;
                    }

                    $userInfo = api('User')->get_user_info($uid);
                    $user_info['uid'] = (string) $userInfo['uid'];
                    $user_info['uname'] = $userInfo['uname'];
                    $user_info['sex'] = $userInfo['sex'];
                    $user_info['intro'] = $userInfo['intro'] ? formatEmoji(false, $userInfo['intro']) : '';
                    $user_info['location'] = $userInfo['location'] ? $userInfo['location'] : '';
                    $user_info['avatar'] = (object) array($userInfo['avatar']['avatar_big']);
                    $user_info['gold'] = intval($userInfo['user_credit']['credit']['score']['value']);
                    $user_info['fans_count'] = intval($userInfo['user_data']['follower_count']);
                    $user_info['is_verified'] = 0;
                    $user_info['usid'] = $value['user']['usid'];
                    $credit_mod = M('credit_user');
                    $credit = $credit_mod->where(array('uid' => $uid))->find();
                    $user_info['zan_count'] = $credit['zan_remain'];
                    $user_info['live_time'] = $credit['live_time'];
                    $res = model('Follow')->getFollowStateByFids($this->mid, intval($uid));
                    $user_info['is_follow'] = $res[$uid]['following'];
                    /* # 获取用户封面 */
                    $cover = D('user_data')->where('`key` LIKE "application_user_cover" AND `uid` = '.$v)->field('value')->getField('value');
                    $user_info['cover'] = $cover ? (object) array($cover) : (object) array();
                    $value['user'] = $user_info;

                    $icon = $value['stream']['icon'];
                    $value['stream']['icon'] = $icon ? (object) $icon : (object) array();
                    $lives[] = $value;
                }
                $list['lives'] = $lives;
            } else {
                $list['lives'] = array();
            }
        }

        //极铺商品
        if (in_array('10', $open_arr)) {
            $jipu_url = 'http://www.jipushop.com/Api/tsGoods';
            $goods_rs = file_get_contents($jipu_url);
            $goods_rs = json_decode($goods_rs, true);

            $list['jipu_goods'] = $goods_rs;
        }

        return $list;
    }

    public function test()
    {
        $result = array();
        $data = array(
            array('id'=>1, 'name'=>'张三', '性别'=>'男'),
            array('id'=> 2, 'name'=>'李四', '性别'=>'女'),
            array('id'=> 3, 'name'=>'王二', '性别'=>'女'),
            array('id'=> 4, 'name'=>'麻子', '性别'=>'男'),
        );
        $result = array_map(function ($item) {
            return array(
                'id'   => $item['id'],
                'name' => $item['name'],
                'sex'  => $item['性别'] == '男' ? 1 : 2,
            );
        }, $data);

        dump($result);
        die;
    }


    /**
     * 查询奥豆视频单集详情
     *
     * @return array
     *
     * @author zhl
     **/
    public function get_video_data()
    {
        $video_id = intval($this->data['video_id']); //视频id
        if(!$video_id){
            return array('status' => 0, 'msg' => '参数错误');
        }
        $parts = intval($this->data['parts'])==''?1:intval($this->data['parts']); //第几集，默认第一集

        $video_info = S('api_video_data_'.$video_id.'_'.$parts);
        if(!$video_info){
            //视频详情
            $map = array();
            $map['a.video_id'] = $video_id;
            $map['a.parts'] = $parts;
            $map['a.is_del'] = 0;
            $tablePrefix = C('DB_PREFIX');
            $video_info = D('aodou_video_data')
                ->field('a.id as video_data_id,a.image_path,a.image_width,a.image_height,a.video_path,
            b.title,b.play_type,b.update_type,b.abstract,b.part_count')
                ->where($map)
                ->table("{$tablePrefix}aodou_video_data AS a LEFT JOIN {$tablePrefix}aodou_video AS b ON a.video_id = b.id")
                ->find();
            if(!$video_info){
                return array('status' => 0, 'msg' => '视频不存在或已删除');
            }
            $video_info['image_path'] = "https://v2.edaocha.net".$video_info['image_path'];
            $video_info['video_path'] = "https://v1.edaocha.net".$video_info['video_path'];
            S('api_video_data_'.$video_id.'_'.$parts,$video_info);
        }
        //播放次数累加
        $save = array();
        $save['id'] = $video_info['video_data_id'];
        $save['pv'] = array('exp','pv+1');
        D('aodou_video_data')->save($save);
        //赞详情
        $map = array();
        $map['video_data_id'] = $video_info['video_data_id'];
        $map['uid'] = $this->mid;
        $digg_info = D('aodou_video_data_digg')->where($map)->count();
        if($digg_info){
            $video_info['digg'] = 1;
        }else{
            $video_info['digg'] = 0;
        }

        //评论查询
        $comment_list = array();
        $where = "app='public' and `table`='aodou_video_data' and to_comment_id=0 and is_del=0 and row_id=".$video_info['video_data_id']; //查询一级评论
        $count = $this->count;
        !empty($this->max_id) && $where .= " AND comment_id < {$this->max_id}";
        $comments = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();
        foreach ($comments as $v) {
            switch ($v['type']) {
                case '2':
                    $type = '转发了此剧集';
                    break;
                case '3':
                    $type = '分享了此剧集';
                    break;
                case '4':
                    $type = '赞了此剧集';
                    break;
                default:
                    $type = '评论了此剧集';
                    break;
            }
            $comment_info['type'] = $type;
            $comment_info['user_info'] = $this->get_user_info($v['uid']);
            $comment_info['comment_id'] = $v['comment_id'];
            $comment_info['content'] = parse_remark($v['content']);
            $comment_info['ctime'] = $v['ctime'];
            $comment_info['digg_count'] = $v['digg_count'];
            $diggarr = model('CommentDigg')->checkIsDigg($v['comment_id'], $GLOBALS['ts']['mid']);
            $comment_info['is_digg'] = t($diggarr[$v['comment_id']] ? 1 : 0);

            /* # 将评论里面的emoji解析 */
            $comment_info['content'] = formatEmoji(false, $comment_info['content']);
            //下级评论回复
            $son_comment = $this->getVideoSonComment($comment_info['comment_id']);
            $son_comment_counts = $this->getVideoSonCommentCount($comment_info['comment_id']);
            //$comment_info['son_comment'] = $son_comment;
            //最多返回两条
            $new_son_comment = array();
            if($son_comment>0){
                $son_comment_count = count($son_comment_counts);
                $i = 1;
                foreach($son_comment as $v1){
                    if($i<3){
                        $new_son_comment[] = $v1;
                    }else{
                        break;
                    }
                    $i++;
                }
            }else{
                $son_comment_count = 0;
            }
            $comment_info['son_comment'] = $new_son_comment;
            $comment_info['son_comment_count'] = $son_comment_count;

            $comment_list[] = $comment_info;
        }

        $video_info['comment_list'] = $comment_list;

        $video_info['status'] = 1;
        return $video_info;

    }


    /**
     * @param $to_comment_id
     * @return array|string
     *
     * 查询子评论
     */
    public function getVideoSonComment($to_comment_id='')
    {
        if($to_comment_id){
            $comment_id = $to_comment_id;
        }else{
            $comment_id = $this->data['comment_id'];
        }
        $comment_list = array();
        $where = "app='public' and `table`='aodou_video_data' and is_del=0 and to_comment_id=" . $comment_id;
        $count = $this->count;
        !empty($this->max_id) && $where .= " AND comment_id < {$this->max_id}";
        $comments = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();
        if ($comments) {
            foreach ($comments as $v) {
                switch ($v['type']) {
                    case '2':
                        $type = '转发了此剧集';
                        break;
                    case '3':
                        $type = '分享了此剧集';
                        break;
                    case '4':
                        $type = '赞了此剧集';
                        break;
                    default:
                        $type = '评论了此剧集';
                        break;
                }
                $comment_info['type'] = $type;
                $comment_info['user_info'] = $this->get_user_info($v['uid']);
                $comment_info['comment_id'] = $v['comment_id'];
                $comment_info['to_comment_id'] = $v['to_comment_id'];
                $comment_info['content'] = parse_remark($v['content']);
                $comment_info['ctime'] = $v['ctime'];
                $comment_info['digg_count'] = $v['digg_count'];
                $diggarr = model('CommentDigg')->checkIsDigg($v['comment_id'], $GLOBALS['ts']['mid']);
                $comment_info['is_digg'] = t($diggarr[$v['comment_id']] ? 1 : 0);

                /* # 将评论里面的emoji解析 */
                $comment_info['content'] = formatEmoji(false, $comment_info['content']);

                $comment_list[] = $comment_info;
                $son_list = $this->getVideoSonComment($v['comment_id']);
                if($son_list){
                    foreach($son_list as $v1){
                        $comment_list[] = $v1;
                    }
                }
            }
            $sort = array(
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field'     => 'comment_id',       //排序字段
            );
            $arrSort = array();
            foreach($comment_list AS $uniqid => $row){
                foreach($row AS $key=>$value){
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if($sort['direction']){
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $comment_list);
            }
            return $comment_list;
        } else {
            return '';
        }
    }


    /**
     * @param $to_comment_id
     * @return array|string
     *
     * 查询子评论总条数
     */
    private function getVideoSonCommentCount($to_comment_id='')
    {

        if($to_comment_id){
            $comment_id = $to_comment_id;
        }else{
            $comment_id = $this->data['comment_id'];
        }
        $comment_list = array();
        $where = "app='public' and `table`='aodou_video_data' and is_del=0 and to_comment_id=" . $comment_id;

        $comments = model('Comment')->field('comment_id')->where($where)->findAll();
        if ($comments) {
            foreach ($comments as $v) {
                $comment_info['comment_id'] = $v['comment_id'];

                $comment_list[] = $comment_info;
                $son_list = $this->getVideoSonCommentCount($v['comment_id']);
                if($son_list){
                    foreach($son_list as $v1){
                        $comment_list[] = $v1;
                    }
                }
            }

            return $comment_list;
        } else {
            return '';
        }
    }

    /**
     * 评论奥豆视频单集
     *
     * @return bool
     *
     * @author zhl
     **/
    public function comment_video_data(){
        if (!intval($this->data['video_data_id'])) {
            return array(
                'status' => 0,
                'msg'    => '评论对象不能为空',
            );
        }
        if (!t($this->data['content'])) {
            return array(
                'status' => 0,
                'msg'    => '评论内容不能为空',
            );
        }
        //检测用户是否被禁言
        if ($isDisabled = model('DisableUser')->isDisableUser($this->mid, 'post')) {
            return array(
                'status' => 0,
                'msg'    => '您已经被禁言了',
            );
        }
        $data = array();
        $data['type'] = 1;
        $data['app'] = 'public';
        $data['table'] = 'aodou_video_data';
        $data['row_id'] = intval($this->data['video_data_id']);
        $data['content'] = $this->data['content'];
        /* # 将emoji编码 */
        $data['content'] = t(formatEmoji(true, $data['content']));
        if ($this->data['to_comment_id']) {
            $data['to_comment_id'] = intval($this->data['to_comment_id']);
            $data['to_uid'] = model('Comment')->where('comment_id='.intval($this->data['to_comment_id']))->getField('uid');
        }
        $res = model('Comment')->addComment($data);
        if ($res) {
            return array(
                'status' => 1,
                'msg'    => '评论成功',
                'cid'    => $res,
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '评论失败',
            );
        }
    }


    /**
     * 获取用户信息 --using.
     *
     * @param
     *        	integer uid 用户UID
     *
     * @return array 用户信息
     */
    private function get_user_info($uid)
    {
        $user_info_whole = api('User')->get_user_info($uid);
        $user_info['uid'] = $user_info_whole['uid'];
        $user_info['uname'] = $user_info_whole['uname'];
        $user_info['remark'] = $user_info_whole['remark'];
        $user_info['avatar']['avatar_middle'] = $user_info_whole['avatar']['avatar_small'];
        $user_info['user_group'] = $user_info_whole['user_group'];
        $user_info['anonymous_name'] = $user_info_whole['anonymous_name']; //匿名名称
        $user_info['anonymous_icon'] = $user_info_whole['anonymous_icon']; //匿名头像
        /* 关注状态 */
        $user_info['follow_state'] = model('Follow')->getFollowState($this->mid, $uid);

        // 用户隐私设置
        $privacy = model('UserPrivacy')->getPrivacy($this->mid, $uid);
        $user_info['space_privacy'] = $privacy['space'];

        return $user_info;
    }


    /**
     * 赞某个剧集 --using.
     *
     * @param
     *        	integer video_data_id 剧集ID
     *
     * @return array 状态+提示
     */
    public function digg_video_data()
    {
        $video_data_id = intval($this->data['video_data_id']);
        $map = array();
        $map['uid'] = $this->mid;
        $map['video_data_id'] = $video_data_id;
        $count = D('aodou_video_data_digg')->where($map)->count();
        if($count){
            return array(
                'status' => 0,
                'msg'    => '你已经赞过',
            );
        }
        $map['ctime'] = time();
        $res = D('aodou_video_data_digg')->add($map);
        if ($res) {
            return array(
                'status' => 1,
                'msg'    => '操作成功',
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '操作失败',
            );
        }
    }

    /**
     * 取消赞某个剧集 --using.
     *
     * @param
     *        	integer video_data_id 剧集ID
     *
     * @return array 状态+提示
     */
    public function undigg_video_data()
    {
        $video_data_id = intval($this->data['video_data_id']);
        $map = array();
        $map['uid'] = $this->mid;
        $map['video_data_id'] = $video_data_id;
        $count = D('aodou_video_data_digg')->where($map)->count();
        if(!$count){
            return array(
                'status' => 0,
                'msg'    => '取消赞失败，您可以已取消过赞信息',
            );
        }
        $res = D('aodou_video_data_digg')->where($map)->delete();
        if ($res) {
            return array(
                'status' => 1,
                'msg'    => '操作成功',
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '操作失败',
            );
        }
    }

} // END class PublicApi extends Api
