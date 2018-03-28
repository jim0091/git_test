<?php

// 微博Api接口V2
class WeiboApi extends Api
{
    /**
     * ******** 微博首页列表API *********.
     */

    /**
     * 获取全站最新发布微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function public_timeline()
    {
        // return $this->mid;
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 10;
        $where = 'f.is_del=0 and f.is_audit=1';
        // 动态类型
        $type = $this->data['type'];
        if (in_array($type, array(
            'postimage',
            // 'postfile',
            'postvideo',
        ))) {
            $where .= " AND f.type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND f.is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND f.is_repost=1';
        }

        $shield = M('shield')->where(array('uid'=>$this->mid,'type'=>'feed'))->select();
        $shield_uid = array();//屏蔽会员id
        $shield_fid = array();//屏蔽分享id
        foreach($shield as $k => $v){
            !empty($v['suid']) && $shield_uid[] = $v['suid'];
            !empty($v['feed_id']) && $shield_fid[] = $v['feed_id'];
        }

        !empty($shield_uid) && $where .= " AND f.uid NOT IN (".implode(',', $shield_uid).") ";
        !empty($shield_fid) && $where .= " AND f.feed_id NOT IN (".implode(',', $shield_fid).") ";

        !empty($max_id) && $where .= " AND f.feed_id < {$max_id}";
        // $where .= " AND (app='public')";
        $where .= " AND (f.app='public' OR f.app='weiba')";
        $where .= " AND f.type != 'postfile'";

        //$feed_ids = model('Feed')->where($where)->field('feed_id')->limit($count)->order('feed_id DESC')->getAsFieldArray('feed_id');
        //判断用户是否是正常用户组
        $uid = empty($this->user_id) ? $this->mid : $this->user_id;
        $map = array();
        $map['uid'] = $uid;
        $map['user_group_id'] = 3; //正常用户组固定为3，官方管理组固定为7
        $user_group = model('UserGroupLink')->where($map)->find();
        if($user_group){
            $where .= " AND (f.uid = '{$uid}' or k.user_group_id=7) ";
        }
        $tablePrefix = C('DB_PREFIX');
        $feed_ids = model('Feed')->where($where)
            ->table("{$tablePrefix}feed AS f  LEFT JOIN {$tablePrefix}user_group_link AS k ON k.uid = f.uid")
            ->field('f.feed_id')->limit($count)->order('f.feed_id DESC')->getAsFieldArray('feed_id');

        return $this->format_feed($feed_ids);
    }



    /**
     * 广场-获取全站最新发布微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function all_timeline()
    {

        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 10;
        $where = 'f.is_del=0 and f.is_audit=1 and f.is_anonymous=0';
        // 动态类型
        $type = $this->data['type'];
        if (in_array($type, array(
            'postimage',
            // 'postfile',
            'postvideo',
        ))) {
            $where .= " AND f.type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND f.is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND f.is_repost=1';
        }

        $shield = M('shield')->where(array('uid'=>$this->mid,'type'=>'feed'))->select();
        $shield_uid = array();//屏蔽会员id
        $shield_fid = array();//屏蔽分享id
        foreach($shield as $k => $v){
            !empty($v['suid']) && $shield_uid[] = $v['suid'];
            !empty($v['feed_id']) && $shield_fid[] = $v['feed_id'];
        }

        !empty($shield_uid) && $where .= " AND f.uid NOT IN (".implode(',', $shield_uid).") ";
        !empty($shield_fid) && $where .= " AND f.feed_id NOT IN (".implode(',', $shield_fid).") ";

        !empty($max_id) && $where .= " AND f.feed_id < {$max_id}";
        // $where .= " AND (app='public')";
        $where .= " AND (f.app='public' OR f.app='weiba' OR f.app='event')";
        $where .= " AND f.type != 'postfile'";

        //查询48小时内的分享
        $begin = strtotime(date("Y-m-d H:i:s",strtotime("-5 day")));
        $end = strtotime(date("Y-m-d H:i:s",time()));
        $where .= " AND f.publish_time between {$begin} and {$end}";

        //只有第一页显示置顶分享
        if(!empty($max_id)){
            $order = 'f.feed_id desc';
        }else{
            $order = 'f.is_top desc,f.feed_id desc';
        }
        $tablePrefix = C('DB_PREFIX');
        $feed_ids = model('Feed')->where($where)
            ->table("{$tablePrefix}feed AS f")
            //->table("{$tablePrefix}feed AS f  LEFT JOIN {$tablePrefix}user_group_link AS k ON k.uid = f.uid")
            ->field('f.feed_id')->limit($count)->order($order)->getAsFieldArray('feed_id');

        $feed_ids = array_unique($feed_ids);

        return $this->format_feed($feed_ids);
    }


    /**
     * 匿名微博-获取全站最新发布的匿名微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function get_anonymous()
    {
        //判断当前用户是否有匿名，没有则生成
        $userInfo = $this->get_user_info($this->mid);
        if(!$userInfo['anonymous_name']){
            api('User')->create_user_anonymous($this->mid);
        }
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 10;
        $where = 'f.is_del=0 and f.is_audit=1 and f.is_anonymous=1';
        // 动态类型
        $type = $this->data['type'];
        if (in_array($type, array(
            'postimage',
            'postvideo',
        ))) {
            $where .= " AND f.type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND f.is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND f.is_repost=1';
        }

        $shield = M('shield')->where(array('uid'=>$this->mid,'type'=>'feed'))->select();
        $shield_uid = array();//屏蔽会员id
        $shield_fid = array();//屏蔽分享id
        foreach($shield as $k => $v){
            !empty($v['suid']) && $shield_uid[] = $v['suid'];
            !empty($v['feed_id']) && $shield_fid[] = $v['feed_id'];
        }

        !empty($shield_uid) && $where .= " AND f.uid NOT IN (".implode(',', $shield_uid).") ";
        !empty($shield_fid) && $where .= " AND f.feed_id NOT IN (".implode(',', $shield_fid).") ";

        !empty($max_id) && $where .= " AND f.feed_id < {$max_id}";
        $where .= " AND (f.app='public' OR f.app='weiba' OR f.app='event')";
        $where .= " AND f.type != 'postfile'";

        //只有第一页显示置顶分享
        if(!empty($max_id)){
            $order = 'f.feed_id desc';
        }else{
            $order = 'f.is_top desc,f.feed_id desc';
        }

        $tablePrefix = C('DB_PREFIX');
        $feed_ids = model('Feed')->where($where)
            ->table("{$tablePrefix}feed AS f")
            ->field('f.feed_id')->limit($count)->order($order)->getAsFieldArray('feed_id');
        $feed_ids = array_unique($feed_ids);

        return $this->format_feed($feed_ids);
    }


    /**
     * 获取当前用户所关注的用户发布的微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function friends_timeline()
    {
        $tablePrefix = C('DB_PREFIX');
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 10;
        $where = 'is_del=0 and is_audit=1';
        // 动态类型
        $type = $this->data['type'];
        if (in_array($type, array(
            'postimage',
            // 'postfile',
            'postvideo',
        ))) {
            $where .= " AND type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND is_repost=1';
        }
        $where .= " AND type != 'postfile'";
        $max_id && $where .= " AND feed_id < {$max_id}";
        $where .= " AND (app='public')";
        $users_follow = M('user_follow')->where(array('uid'=>$this->mid))->getAsFieldArray('fid');
        !empty($users_follow) && $where .= " AND uid IN (".implode(',', $users_follow).") ";

        $shield = M('shield')->where(array('uid'=>$this->mid,'type'=>'feed'))->select();
        $shield_uid = array();//屏蔽会员id
        $shield_fid = array();//屏蔽分享id
        foreach($shield as $k => $v){
            !empty($v['suid']) && $shield_uid[] = $v['suid'];
            !empty($v['feed_id']) && $shield_fid[] = $v['feed_id'];
        }

        !empty($shield_uid) && $where .= " AND uid NOT IN (".implode(',', $shield_uid).") ";
        !empty($shield_fid) && $where .= " AND feed_id NOT IN (".implode(',', $shield_fid).") ";


        if(empty($users_follow)){
            return array(
                'status' => 0,
                'msg'    => '您的关注列表为空，请添加关注',
            );
        }else{
            $where .= " AND uid IN (".implode(',', $users_follow).") ";
        }

        $feed_ids = model('Feed')->where($where)->field('feed_id')->limit($count)->order('feed_id DESC')->getAsFieldArray('feed_id');

        return $this->format_feed($feed_ids);
    }

    /**
     * 获取当前用户所关注频道分类下的微博 --using.
     *
     * @param
     *        	integer cid 频道ID(可选,0或null为全部)
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 指定频道分类下的微博列表
     */
    public function channels_timeline()
    {
        // 我关注的频道
        $list = D('ChannelFollow', 'channel')->getFollowList($GLOBALS['ts']['mid']);
        if (!$list) {
            return array();
        }
        $cids = getSubByKey($list, 'channel_category_id');

        $tablePrefix = C('DB_PREFIX');
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 10;
        $cid = intval($this->data['cid']);
        $where = 'c.status = 1';
        if ($cid && in_array($cid, $cids)) {
            $where .= ' AND c.channel_category_id = '.intval($cid);
        } else {
            $where .= ' AND c.channel_category_id in ('.implode(',', $cids).')';
        }
        !empty($max_id) && $where .= " AND c.feed_id < {$max_id}";
        $type = $this->data['type'];
        if (in_array($type, array(
            'postimage',
            // 'postfile',
            'postvideo',
        ))) {
            $where .= " AND f.type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND f.is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND f.is_repost=1';
        }
        $where .= " AND (f.app='public')";
        $where .= " AND f.type != 'postfile'";
        $order = 'c.feed_id DESC';
        $sql = 'SELECT distinct c.feed_id FROM `'.$tablePrefix.'channel` c LEFT JOIN `'.$tablePrefix.'feed` f ON c.feed_id = f.feed_id WHERE '.$where.' ORDER BY '.$order.' LIMIT '.$count.'';
        $feed_ids = getSubByKey(D()->query($sql), 'feed_id');

        return $this->format_feed($feed_ids);
    }

    /**
     * 获取某个话题下的微博 --using.
     *
     * @param
     *        	varchar topic_name 话题名称
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	integer type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 话题详情
     */
    public function topic_timeline()
    {
        $topic_name = t($this->data['topic_name']);
        if (!$topic_name) {
            return array(
                'status' => 0,
                'msg'    => '话题名称不能为空',
            );
        }
        $weibo_list = array();
        $topic_detail = D('feed_topic')->where(array(
            'topic_name' => formatEmoji(true, $topic_name),
        ))->find();
        if (!$topic_detail) {
            return array(
                'status' => 1,
                'msg'    => '列表为空',
                'data'   => $weibo_list,
            );
        }
        if ($topic_detail['lock'] == 1) {
            return array(
                'status' => 0,
                'msg'    => '该话题已屏蔽',
            );
        }

        $tablePrefix = C('DB_PREFIX');
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;

        $where = 'f.is_del = 0';
        if (!empty($topic_detail['top_feed'])) {
            $fids = array_filter(explode(',', $topic_detail['top_feed']));
            $map_test['feed_id'] = array(
                'in',
                $fids,
            );
            $test = M('feed')->where($map_test)->field('feed_id')->findAll();
            $fids = array();
            if (!empty($test)) {
                $fids = getSubByKey($test, 'feed_id');
            }
            empty($fids) || $where = 'f.feed_id not in ('.implode(',', $fids).') ';
        }

        $where .= ' AND t.topic_id = '.intval($topic_detail['topic_id']);

        !empty($max_id) && $where .= " AND t.feed_id < {$max_id}";
        $type = $this->data['type'];
        if (in_array($type, array(
            'postimage',
            'postfile',
            'postvideo',
        ))) {
            $where .= " AND f.type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND f.is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND f.is_repost=1';
        }
        $where .= " AND (f.app='public')";
        $where .= " AND f.type != 'postfile'";
        $order = 't.feed_id DESC';
        $sql = 'SELECT t.feed_id FROM `'.$tablePrefix.'feed_topic_link` t LEFT JOIN `'.$tablePrefix.'feed` f ON t.feed_id = f.feed_id WHERE '.$where.' ORDER BY '.$order.' LIMIT '.$count.'';
        $feed_ids = getSubByKey(D()->query($sql), 'feed_id');
        if ($max_id == 0 && !empty($fids)) {
            $feed_ids = array_merge($fids, $feed_ids);
        }
        $feeds = $this->format_feed($feed_ids);
        foreach ($feeds as &$v) {
            if (in_array($v['feed_id'], $fids)) {
                $v['is_top'] = 1;
            } else {
                $v['is_top'] = 0;
            }
        }
        if ($max_id) {
            return array(
                'status' => 1,
                'msg'    => '列表',
                'data'   => $feeds,
            );
        } else {
            $detail['topic_name'] = '#'.$topic_detail['topic_name'].'#';
            $detail['des'] = $topic_detail['des'] ? t($topic_detail['des']) : '';
            $detail['count'] = intval($topic_detail['count']);
            if ($topic_detail['pic']) {
                $attach = model('Attach')->getAttachById($topic_detail['pic']);
                $detail['pic'] = getImageUrl($attach['save_path'].$attach['save_name']);
            } else {
                $detail['pic'] = '';
            }
            // $detail['feeds'] = $feeds;
            return array(
                'status' => 1,
                'msg'    => '列表',
                'detail' => $detail,
                'data'   => $feeds,
            );
        }
    }

    /**
     * 获取推荐最新发布微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条微博ID
     * @param
     *        	integer count 微博条数
     *
     * @return array 微博列表
     */
    public function recommend_timeline()
    {
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 10;
        $recommend_time = intval($this->data['recommend_time']); //推荐时间
        $where = 'is_del=0 and is_audit=1 and is_recommend=1 and is_anonymous=0';
        //!empty($max_id) && $where .= " AND feed_id < {$max_id}";
        !empty($recommend_time) && $where .= " AND recommend_time < {$recommend_time}"; //按照推荐时间倒序
        $where .= " AND type != 'postfile'";

        $shield = M('shield')->where(array('uid'=>$this->mid,'type'=>'feed'))->select();
        $shield_uid = array();//屏蔽会员id
        $shield_fid = array();//屏蔽分享id
        foreach($shield as $k => $v){
            !empty($v['suid']) && $shield_uid[] = $v['suid'];
            !empty($v['feed_id']) && $shield_fid[] = $v['feed_id'];
        }

        !empty($shield_uid) && $where .= " AND uid NOT IN (".implode(',', $shield_uid).") ";
        !empty($shield_fid) && $where .= " AND feed_id NOT IN (".implode(',', $shield_fid).") ";

        //查询48小时内的分享
//        $begin = strtotime(date("Y-m-d H:i:s",strtotime("-2 day")));
//        $end = strtotime(date("Y-m-d H:i:s",time()));
//        $where .= " AND publish_time between {$begin} and {$end}";

        //只有第一页显示置顶分享
        if(!empty($recommend_time)){
            $order = 'recommend_time desc';
        }else{
            $order = 'recommend_top desc,recommend_time desc';
        }
        $lists = model('Feed')->getList($where, $count, $order); //recommend_time 暂时先按发布时间排序
        //$lists = D ( 'ChannelFollow', 'channel' )->getFollowingFeed ( $where, $count );

        $feed_ids = getSubByKey($lists['data'], 'feed_id');

        return $this->format_feed($feed_ids);
    }

    /**
     * 某条微博详细内容 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     *
     * @return array 微博详细信息
     */
    public function weibo_detail()
    {
        $feed_id = intval($this->data['feed_id']);
        $feed_info = model('Cache')->get('feed_info_api_'.$feed_id);
        if (!$feed_info) {
            $feed_info = $this->get_feed_info($feed_id);
            if ($feed_info['is_repost'] == 1) {
                $feed_info['source_info'] = $this->get_source_info($feed_info['app_name'], $feed_info['stable'], $feed_info['sid']);
            } else {
                $feed_info['source_info'] = array();
            }
            model('Cache')->set('feed_info_api_'.$feed_id, $feed_info);
        }
        // 用户信息
        $feed_info['user_info'] = $this->get_user_info($feed_info['uid']);
        // 赞、收藏
        $diggarr = model('FeedDigg')->checkIsDigg($feed_id, $this->mid);
        $feed_info['is_digg'] = $diggarr[$feed_id] ? 1 : 0;
        $feed_info['is_favorite'] = model('Collection')->where('uid='.$GLOBALS['ts']['mid'].' and source_id='.$feed_id)->count();
        if ($this->mid != $feed_info['uid']) {
            $privacy = model('UserPrivacy')->getPrivacy($this->mid, $feed_info['uid']);
            if ($privacy['comment_weibo'] == 1) {
                $feed_info['can_comment'] = 0;
            } else {
                $feed_info['can_comment'] = 1;
            }
        } else {
            $feed_info['can_comment'] = 1;
        }
        $feed_info['comment_info'] = $this->weibo_comments($feed_id, 10);
        $feed_info['digg_info'] = $this->weibo_diggs($feed_id);

        return $feed_info;
    }

    /**
     * 获取指定微博的评论列表 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     * @param
     *        	integer max_id 上次返回的最后一条评论ID
     * @param
     *        	integer count 评论条数
     *
     * @return array 评论列表
     */
    public function weibo_comments($feed_id, $count)
    {
        if (!$feed_id) {
            $feed_id = $this->data['feed_id'];
        }
        $comment_list = array();
        $where = "app='public' and `table`='feed' and to_comment_id=0 and is_del=0 and row_id=".$feed_id; //2017-07-14查询一级评论
        //$where = 'is_del=0 and row_id='.$feed_id;
        if (!$count) {
            $count = $this->count;
            !empty($this->max_id) && $where .= " AND comment_id < {$this->max_id}";
        }
        $comments = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();
        foreach ($comments as $v) {
            switch ($v['type']) {
                case '2':
                    $type = '转发了此微博';
                    break;
                case '3':
                    $type = '分享了此微博';
                    break;
                case '4':
                    $type = '赞了此微博';
                    break;
                default:
                    $type = '评论了此微博';
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
            $comment_info['is_anonymous'] = $v['is_anonymous'];
            /* # 将评论里面的emoji解析 */
            $comment_info['content'] = formatEmoji(false, $comment_info['content']);
            //下级评论回复 2017-07-14 暂时不更新
            $son_comment = $this->getSonComment($comment_info['comment_id']);
            $son_comment_counts = $this->getSonCommentCount($comment_info['comment_id']);
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

        return $comment_list;
    }


    /**
     * @param $to_comment_id
     * @return array|string
     *
     * 查询子评论
     */
    public function getSonComment($to_comment_id='')
    {
        if($to_comment_id){
            $comment_id = $to_comment_id;
        }else{
            $comment_id = $this->data['comment_id'];
        }
        $comment_list = array();
        $where = "app='public' and `table`='feed' and is_del=0 and to_comment_id=" . $comment_id;
        $count = $this->count;
        !empty($this->max_id) && $where .= " AND comment_id < {$this->max_id}";
        $comments = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();
        //$comments = model('Comment')->where($where)->order('comment_id ASC')->findAll();
        if ($comments) {
            foreach ($comments as $v) {
                switch ($v['type']) {
                    case '2':
                        $type = '转发了此微博';
                        break;
                    case '3':
                        $type = '分享了此微博';
                        break;
                    case '4':
                        $type = '赞了此微博';
                        break;
                    default:
                        $type = '评论了此微博';
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
                $comment_info['is_anonymous'] = $v['is_anonymous'];
                /* # 将评论里面的emoji解析 */
                $comment_info['content'] = formatEmoji(false, $comment_info['content']);

                $comment_list[] = $comment_info;
                $son_list = $this->getSonComment($v['comment_id']);
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
    private function getSonCommentCount($to_comment_id='')
    {

        if($to_comment_id){
            $comment_id = $to_comment_id;
        }else{
            $comment_id = $this->data['comment_id'];
        }
        $comment_list = array();
        $where = "app='public' and `table`='feed' and is_del=0 and to_comment_id=" . $comment_id;

        $comments = model('Comment')->field('comment_id')->where($where)->findAll();
        if ($comments) {
            foreach ($comments as $v) {
                $comment_info['comment_id'] = $v['comment_id'];

                $comment_list[] = $comment_info;
                $son_list = $this->getSonCommentCount($v['comment_id']);
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
     * 获取指定微博的赞过的人的列表 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     * @param
     *        	integer max_id 上次返回的最后一条赞的ID
     * @param
     *        	integer count 数量
     *
     * @return array 点赞的用户列表
     */
    public function weibo_diggs($feed_id, $count = 10)
    {
        if (!$feed_id) {
            $feed_id = $this->data['feed_id'];
        }
        $where = 'feed_id='.$feed_id;
        !empty($this->max_id) && $where .= " AND id < {$this->max_id}";
        $digg_list = model('FeedDigg')->where($where)->order('cTime DESC')->limit($count)->findAll();
        if (!$digg_list) {
            return array();
        }
        $follow_status = model('Follow')->getFollowStateByFids($this->mid, getSubByKey($digg_list, 'uid'));
        foreach ($digg_list as $k => $v) {
            $user_info = api('User')->get_user_info($v['uid']);
            $digg_list[$k]['anonymous_name'] = $user_info['anonymous_name'];
            $digg_list[$k]['anonymous_icon'] = $user_info['anonymous_icon'];
            $digg_list[$k]['remark'] = $user_info['remark'];
            $digg_list[$k]['uname'] = $user_info['uname'];
            $digg_list[$k]['intro'] = $user_info['intro'];
            $digg_list[$k]['avatar'] = $user_info['avatar']['avatar_big'];
            $digg_list[$k]['follow_status'] = $follow_status[$v['uid']];
            $privacy = model('UserPrivacy')->getPrivacy($this->mid, $v['uid']);
            $digg_list[$k]['space_privacy'] = $privacy['space'];
            unset($digg_list[$k]['feed_id']);
        }

        return $digg_list;
    }

    /**
     * ******** 微博的操作API *********.
     */

    /**
     * 发布一条微博 --using.
     *
     * @param
     *        	string content 微博内容
     * @param float  $latitude
     *                          纬度
     * @param float  $longitude
     *                          经度
     * @param string $address
     *                          具体地址
     * @param
     *        	integer from 来源(2-android 3-iphone)
     * @param
     *        	string channel_category_id 频道ID(多个频道ID之间用逗号隔开)
     *
     * @return array 状态+提示/数据
     */
    public function post_weibo($datas)
    {
        set_time_limit(0);
        if (!CheckPermission('core_normal', 'feed_post')) {
            return array(
                'status' => 0,
                'msg'    => '您没有权限',
            );
        }

        if ($datas) {
            $this->data['type'] = $datas['type'];
        }
        //检测用户是否被禁言
        if ($isDisabled = model('DisableUser')->isDisableUser($this->mid, 'post')) {
            return array(
                'status' => 0,
                'msg'    => '您已经被禁言了',
            );
        }

        $data['uid'] = $this->mid;
        $data['body'] = $this->data['content'];
        /* 格式化emoji */
        $data['body'] = t(formatEmoji(true, $data['body']));

        if (trim($data['body']) == '') {
            return array(
                'status' => 0,
                'msg'    => '内容不能为空',
            );
        }

        $filterContentStatus = filter_words($data['body']);
        if (!$filterContentStatus['status']) {
            return array(
                'status' => 0,
                'msg'    => '内容中包涵禁止词汇',
            );
        }

        $data['is_anonymous'] = $this->data['is_anonymous'] ? intval($this->data['is_anonymous']) : '0'; //是否匿名标识
        $data['body'] = $filterContentStatus['data'];
        $data['type'] = isset($this->data['type']) ? $this->data['type'] : 'post';
        $data['app'] = 'public';
        $data['app_row_id'] = '0';
        $data['from'] = $this->data['from'] ? intval($this->data['from']) : '0';
        $data['publish_time'] = time();
        // $data ['latitude'] = floatval ( $this->data ['latitude'] );
        // $data ['longitude'] = floatval ( $this->data ['longitude'] );
        $data['address'] = t($this->data['address']);

        /* 经纬度 */
        $data['latitude'] = t($this->data['latitude']);
        $data['longitude'] = t($this->data['longitude']);

        //判断是否先审后发，分享统计用户组和官方用户组不用审核（暂时）
        $userGids = model('UserGroupLink')->getUserGroup($this->mid);
        if (in_array("9", $userGids[$this->mid])||in_array("7", $userGids[$this->mid])){
            $data['is_audit'] = 1;
        }else{
            //普通用户根据系统配置来判断
            $weiboSet = model('Xdata')->get('admin_Config:feed');
            $weibo_premission = $weiboSet['weibo_premission'];
            if (in_array('audit', $weibo_premission) || CheckPermission('core_normal', 'feed_audit') || $filterContentStatus['type'] == 2) {
                $data['is_audit'] = 0;
            } else {
                $data['is_audit'] = 1;
            }
        }

        $feed_id = model('Feed')->data($data)->add();

        // 附件处理
        if (isset($datas['attach_id'])) { // 图片类型
            $attach_id = $datas['attach_id'];
            array_map('intval', $attach_id);
            $data['attach_id'] = $attach_id;
        }
        if (isset($datas['video_id'])) { // 视频类型
            D('video')->where('video_id='.$datas['video_id'])->setField('feed_id', $feed_id);
            // 如果需要转码
            if (D('video_transfer')->where('video_id='.$datas['video_id'])->count()) {
                D('video_transfer')->where('video_id='.$datas['video_id'])->setField('feed_id', $feed_id);
            }
            $data = array_merge($data, $datas);
        }

        $feed_data = D('FeedData')->data(array(
            'feed_id'      => $feed_id,
            'feed_data'    => serialize($data),
            'client_ip'    => get_client_ip(),
            'feed_content' => $data['body'],
        ))->add();

        if ($feed_id && $feed_data) {

            if($data['is_audit'] == 1){
                //CDN缓存预热
                if ($datas['video_id']) {
                    $video_info = D('video')->where('video_id='.$datas['video_id'])->find();
                    $video_path = $video_info['video_path'] ? $video_info['video_path'] : $video_info['video_mobile_path'];
                    $image_path = $video_info['image_path'];
                    $video_config = model('Xdata')->get('admin_Content:video_config');
                    $video_server = $video_config['video_server'] ? $video_config['video_server'] : SITE_URL;
                    $cdn = model('CdnPushObject');
                    $objectPath = $video_server.$video_path."\n";
                    $objectPath .= $video_server.$image_path;
                    $cdn->setObjectPath($objectPath);
                    $cdn->doAction();
                }
                /*if ($datas['type'] == 'postimage') {
                    $attach_map = array();
                    $attach_map['attach_id'] = array('IN', $datas['attach_id']);
                    $imgList = model('Attach')->where($attach_map)->select();
                    $i = 1;
                    $objectPath = '';
                    foreach($imgList as $v){
                        if($i<count($imgList)){
                            $objectPath .= "https://v2.edaocha.net/data/upload/".$v['save_path'].$v['save_name']."\n";
                        }else{
                            $objectPath .= "https://v2.edaocha.net/data/upload/".$v['save_path'].$v['save_name'];
                        }
                        $i++;
                    }
                    $cdn = model('CdnPushObject');
                    $cdn->setObjectPath($objectPath);
                    $cdn->doAction();
                }*/
            }

            /* 更新图片信息 */
            if (isset($datas['attach_id'])) {
                model('Attach')->where(array('attach_id' => array('in', $datas['attach_id'])))->save(array(
                    'app_name' => 'public',
                    'table'    => 'feed',
                    'row_id'   => $feed_id,
                ));
            }

            // 更新最近@的人
            model('Atme')->updateRecentAtForApi($data['body'], $feed_id);
            // 加积分
            model('Credit')->setUserCredit($this->mid, 'add_weibo');
            // Feed数
            model('UserData')->setUid($this->mid)->updateKey('feed_count', 1);
            model('UserData')->setUid($this->mid)->updateKey('weibo_count', 1);
            // 添加到话题
            model('FeedTopic')->addTopic(html_entity_decode($data['body'], ENT_QUOTES, 'UTF-8'), $feed_id, $data['type']);
            // 添加到频道
            $isOpenChannel = model('App')->isAppNameOpen('channel');
            if (!$isOpenChannel) {
                return array(
                    'status'  => 1,
                    'msg'     => '发布成功',
                    'feed_id' => $feed_id,
                );
            }
            // 添加微博到频道中
            $channelId = t($this->data['channel_category_id']);
            // 判断是否有频道绑定该用户
            $bindUserChannel = D('Channel', 'channel')->getCategoryByUserBind($this->mid);
            if (!empty($bindUserChannel)) {
                $channelId = array_merge($bindUserChannel, explode(',', $channelId));
                $channelId = array_filter($channelId);
                $channelId = array_unique($channelId);
                $channelId = implode(',', $channelId);
            }
            // 判断是否有频道绑定该话题
            $content = html_entity_decode($this->data['content'], ENT_QUOTES, 'UTF-8');
            $content = str_replace('＃', '#', $content);
            preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $content, $topics);
            $topics = array_unique($topics[1]);
            foreach ($topics as &$topic) {
                $topic = trim(preg_replace('/#/', '', t($topic)));
            }
            $bindTopicChannel = D('Channel', 'channel')->getCategoryByTopicBind($topics);
            if (!empty($bindTopicChannel)) {
                $channelId = array_merge($bindTopicChannel, explode(',', $channelId));
                $channelId = array_filter($channelId);
                $channelId = array_unique($channelId);
                $channelId = implode(',', $channelId);
            }
            if (!empty($channelId)) {
                // 获取后台配置数据
                $channelConf = model('Xdata')->get('channel_Admin:index');
                // 添加频道数据
                D('Channel', 'channel')->setChannel($feed_id, $channelId, false);
            }

            return array(
                'status'           => 1,
                'msg'              => '发布成功',
                'feed_id'          => $feed_id,
                'is_audit_channel' => intval($channelConf['is_audit']),
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '发布失败',
            );
        }
    }

    /**
     * 发布图片微博 --using.
     *
     * @param file $_FILE
     *                    图片
     * @param
     *        	string content 微博内容
     * @param float  $latitude
     *                          纬度
     * @param float  $longitude
     *                          经度
     * @param string $address
     *                          具体地址
     * @param
     *        	integer from 来源(2-android 3-iphone)
     * @param
     *        	string channel_id 频道ID(多个频道ID之间用逗号隔开)
     *
     * @return array 状态+提示/数据
     */
    public function upload_photo()
    {
        if (!CheckPermission('core_normal', 'feed_post')) {
            return array(
                'status' => 0,
                'msg'    => '您没有权限',
            );
        }
        $d['attach_type'] = 'feed_image';
        $d['upload_type'] = 'image';
        $GLOBALS['fromMobile'] = true;
        $info = model('Attach')->upload($d, $d);
        $data = $this->data;
        if ($info['status']) {
            $data['type'] = 'postimage';
            $data['attach_id'] = getSubByKey($info['info'], 'attach_id');

            return $this->post_weibo($data);
        } else {
            return array(
                'status' => 0,
                'msg'    => '发布失败',
            );
        }
    }

    /**
     * 发布视频微博 --using.
     *
     * @param file $_FILE
     *                    视频
     * @param
     *        	string content 微博内容
     * @param float  $latitude
     *                          纬度
     * @param float  $longitude
     *                          经度
     * @param string $address
     *                          具体地址
     * @param
     *        	integer from 来源(2-android 3-iphone)
     * @param
     *        	string channel_id 频道ID(多个频道ID之间用逗号隔开)
     *
     * @return array 状态+提示/数据
     */
    public function upload_video()
    {
        // return $_FILES;
        if (!CheckPermission('core_normal', 'feed_post')) {
            return array(
                'status' => 0,
                'msg'    => '您没有权限',
            );
        }
        // dump($_REQUEST);exit;
        $info = model('Video')->upload($this->data['from'], $this->data['timeline']);
        if ($info['status']) {
            $data['type'] = 'postvideo';
            $data['video_id'] = intval($info['video_id']);
            $data['video_path'] = t($info['video_path']);
            $data['video_mobile_path'] = t($info['video_mobile_path']);
            $data['video_part_path'] = t($info['video_part_path']);
            $data['image_path'] = t($info['image_path']);
            $data['image_width'] = intval($info['image_width']);
            $data['image_height'] = intval($info['image_height']);
            $data['video_id'] = intval($info['video_id']);
            $data['from'] = intval($this->data['from']);

            return $this->post_weibo($data);
        } else {
            return $info;
        }
    }

    /**
     * 删除一条微博 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     *
     * @return array 状态+提示
     */
    public function del_weibo()
    {
        $feed_id = intval($this->data['feed_id']);
        $feed_mod = model('Feed');
        $feed_info = $feed_mod->get($feed_id);
        $return = model('Feed')->doEditFeed($feed_id, 'delFeed', '', $this->mid);
        // 删除话题相关信息
        $return['status'] == 1 && model('FeedTopic')->deleteWeiboJoinTopic($feed_id);
        // 删除频道关联信息
        D('Channel', 'channel')->deleteChannelLink($feed_id);
        // 删除@信息
        model('Atme')->setAppName('Public')->setAppTable('feed')->deleteAtme(null, $feed_id, null);
        // 删除收藏信息
        model('Collection')->delCollection($feed_id, 'feed');
        if ($feed_info['type'] == 'weiba_post' && $feed_info['app_row_id']) {
            $map['post_id'] = $feed_info['app_row_id'];
            $data['is_del'] = 1;
            M('weiba_post')->where($map)->data($data)->save();
            M('weiba_reply')->where($map)->data($data)->save();
            model('Comment')->where(array('row_id' => $feed_id))->data($data)->save();
        }
        if ($return['status'] == 1) {
            return array(
                'status' => 1,
                'msg'    => '删除成功',
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '删除失败',
            );
        }
    }

    /**
     * 转发一条微博 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     * @param
     *        	string content 转发内容
     * @param float  $latitude
     *                          纬度
     * @param float  $longitude
     *                          经度
     * @param string $address
     *                          具体地址
     * @param
     *        	integer from 来源(2-android 3-iPhone)
     *
     * @return array 状态+提示
     */
    public function repost_weibo()
    {
        if (!CheckPermission('core_normal', 'feed_post')) {
            return array(
                'status' => 0,
                'msg'    => '您没有权限',
            );
        }
        if (!t($this->data['content'])) {
            return array(
                'status' => 0,
                'msg'    => '转发内容不能为空',
            );
        }

        if($this->data['feed_id'] && !isset($this->data['event_id'])){

            $feed_detail = model('Feed')->where('feed_id='.intval($this->data['feed_id']))->field('app,app_row_table,app_row_id')->find();
            $p['app_name'] = isset($feed_detail['app']) ? $feed_detail['app'] : 'public';
            $p['type'] = isset($feed_detail['app_row_table']) ? $feed_detail['app_row_table'] : 'feed';
            $p['sid'] = $feed_detail['app_row_id'] ? intval($feed_detail['app_row_id']) : intval($this->data['feed_id']);
            $p['curid'] = intval($this->data['feed_id']);
            $p['body'] = $this->data['content'];
            $p['from'] = $this->data['from'] ? intval($this->data['from']) : '0';
            $p['curtable'] = 'feed';
            $p['forApi'] = true;
            $p['content'] = '';
            $p['latitude'] = floatval($this->data['latitude']);
            $p['longitude'] = floatval($this->data['longitude']);
            $p['address'] = t($this->data['address']);
            /* # 将emoji编码 */
            $p['body'] = formatEmoji(true, $p['body']);

            $return = model('Share')->shareFeed($p, 'share');
            if ($return['status'] == 1) {
                // 添加积分
                model('Credit')->setUserCredit($this->mid, 'forward_weibo');

                return array(
                    'status'  => 1,
                    'msg'     => '转发成功',
                    'feed_id' => $return['data']['feed_id'],
                );
            } else {
                return array(
                    'status' => 0,
                    'msg'    => '转发失败',
                );
            }
        }elseif($this->data['event_id'] && !isset($this->data['feed_id'])){
            $event_detail = m('event_list')->where(array('del'=>0))->find($this->data['event_id']);
            if(empty($event_detail)){
                return array(
                    'status' => 0,
                    'msg'    => '转发活动不存在',
                );
            }
            $data = array();
            $data['uid'] = $this->mid;
            $data['app'] = 'event';
            $data['type'] = 'repost';
            $data['app_row_id'] = $this->data['event_id'];
            $data['app_row_table'] = 'event_list';
            $data['publish_time'] = time();
            $data['from'] = isset($data['from']) ?: $_REQUEST['from'] ?: getVisitorClient();
            $data['is_del'] = 0;
            $data['is_repost'] = 1;
            $weiboSet = model('Xdata')->get('admin_Config:feed');
            $weibo_premission = $weiboSet['weibo_premission'];
            if (in_array('audit', $weibo_premission) || CheckPermission('core_normal', 'feed_audit') || $filterStatus['type'] == 2) {
                $data['is_audit'] = 0;
            } else {
                $data['is_audit'] = 1;
            }
            $data['content'] = isset($this->data['content']) ? str_replace(SITE_URL, '[SITE_URL]', $this->data['content']) : '';
            $data['body'] = $data['content'];
            if($res = model('Feed')->add($data)){
                $data['content'] = str_replace(chr(31), '', $data['content']);
                $data['body'] = str_replace(chr(31), '', $data['body']);
                // 添加关联数据
                // $feed_data = D('FeedData')->data(array('feed_id'=>$feed_id, 'feed_data'=>serialize($data), 'client_ip'=>get_client_ip(), 'client_port'=>get_client_port(), 'feed_content'=>$data['body']))->add();
                // var_dump($feed_data);exit;
                m('feed_data')
                    ->add(array(
                        'feed_id'      => $res,
                        'feed_data'    => serialize($data),
                        'client_ip'    => get_client_ip(),
                        'client_port'  => get_client_port(),
                        'feed_content' => $data['body'],
                    ));
                return array(
                    'status'  => 1,
                    'msg'     => '转发成功',
                    'feed_id' => $res,
                );
            }else{
                return array(
                    'status' => 0,
                    'msg'    => '转发失败',
                );
            }
        }
    }

    /**
     * 评论一条微博 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     * @param
     *        	integer to_comment_id 评论ID
     * @param
     *        	string content 评论内容
     * @param
     *        	integer from 来源(2-android 3-iPhone)
     *
     * @return array 状态+提示
     */
    public function comment_weibo()
    {
        if (!CheckPermission('core_normal', 'feed_comment')) {
            return array(
                'status' => 0,
                'msg'    => '您没有权限',
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
        $feed_detail = model('Feed')->where('feed_id='.intval($this->data['feed_id']))->find();
        $data['type'] = 1;
        $data['app'] = $feed_detail['app'];
        $data['table'] = 'feed';
        $data['row_id'] = intval($this->data['feed_id']);
        $data['app_uid'] = $feed_detail['uid'];
        $data['content'] = $this->data['content'];
        $data['is_anonymous'] = $feed_detail['is_anonymous'];
        // $data ['from'] = 'feed';
        /* # 将emoji编码 */
        $data['content'] = t(formatEmoji(true, $data['content']));
        if ($this->data['to_comment_id']) {
            $data['to_comment_id'] = intval($this->data['to_comment_id']);
            $data['to_uid'] = model('Comment')->where('comment_id='.intval($this->data['to_comment_id']))->getField('uid');
        }
        if (($data['comment_id'] = model('Comment')->addComment($data, true))) {
            //如果回复的源为微吧，同步评论到相应的帖子
            if ($data['app'] == 'weiba') {
                $weiba_post_detail = M('weiba_post')->where(array('post_id' => $feed_detail['app_row_id']))->find();

                $wr_data['weiba_id'] = intval($weiba_post_detail['weiba_id']);
                $wr_data['post_id'] = intval($weiba_post_detail['post_id']);
                $wr_data['post_uid'] = intval($weiba_post_detail['post_uid']);

                if (!empty($this->data['to_comment_id'])) {
                    $wr_data['to_reply_id'] = intval($this->data['to_comment_id']);
                    $wr_data['to_uid'] = model('Comment')->where('comment_id='.intval($this->data['to_comment_id']))->getField('uid');
                }

                $wr_data['uid'] = $this->mid;
                $wr_data['ctime'] = time();
                $wr_data['content'] = $data['content'];

                $filterContentStatus = filter_words($wr_data['content']);
                if (!$filterContentStatus['status']) {
                    return array(
                        'status' => 0,
                        'msg'    => $filterContentStatus['data'],
                    );
                }
                $wr_data['content'] = t($filterContentStatus['data']);
                $wr_data['reply_id'] = $data['comment_id'];

                D('weiba_reply')->add($wr_data);

                $wp_up['last_reply_uid'] = $this->mid;
                $wp_up['last_reply_time'] = $wr_data['ctime'];
                $wp_up['reply_count'] = array(
                    'exp',
                    'reply_count+1',
                );
                $wp_up['reply_all_count'] = array(
                    'exp',
                    'reply_all_count+1',
                );
                D('weiba_post', 'weiba')->where('post_id = '.$feed_detail['app_row_id'])->save($wp_up);
            }

            return array(
                'status' => 1,
                'msg'    => '评论成功',
                'cid'    => $data['comment_id'],
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '评论失败',
            );
        }
    }

    /**
     * 删除微博评论.
     *
     * @return array
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function delComment()
    {
        $cid = intval($this->data['commentid']);

        /*
         * 验证是否传入了参数是否合法
         */
        if (!$cid or !$this->mid) {
            return array(
                'status'  => 0,
                'message' => '传入的参数不合法',
            );

            /*
             * 判断是否删除成功
             */
        } elseif (model('Comment')->deleteComment(array($cid), $this->mid)) {
            return array(
                'status'  => 1,
                'message' => '删除成功',
            );
        }

        return array(
            'status'  => -1,
            'message' => '删除失败',
        );
    }

    /**
     * 赞某条微博 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     *
     * @return array 状态+提示
     */
    public function digg_weibo()
    {
        $feed_id = intval($this->data['feed_id']);
        $res = model('FeedDigg')->addDigg($feed_id, $this->mid);
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
     * 取消赞某条微博 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     *
     * @return array 状态+提示
     */
    public function undigg_weibo()
    {
        $feed_id = intval($this->data['feed_id']);
        $res = model('FeedDigg')->delDigg($feed_id, $this->mid);
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
     * 赞某条评论 --using.
     *
     * @param
     *        	integer comment_id 评论ID
     *
     * @return array 状态+提示
     */
    public function digg_comment()
    {
        $comment_id = intval($this->data['comment_id']);
        $res = model('CommentDigg')->addDigg($comment_id, $this->mid);
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
     * 取消赞某条评论 --using.
     *
     * @param
     *        	integer comment_id 评论ID
     *
     * @return array 状态+提示
     */
    public function undigg_comment()
    {
        $comment_id = intval($this->data['comment_id']);
        $res = model('CommentDigg')->delDigg($comment_id, $this->mid);
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
     * 收藏一条资源 --using.
     *
     * @param
     *        	integer feed_id 资源ID
     *
     * @return array 状态+提示
     */
    public function favorite_weibo()
    {
        $data['source_table_name'] = 'feed'; // feed
        $data['source_id'] = $this->data['feed_id']; // 140
        $data['source_app'] = 'public'; // public

        if (model('Collection')->addCollection($data)) {
            return array(
                'status' => 1,
                'msg'    => '收藏成功',
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '收藏失败',
            );
        }
    }

    /**
     * 取消收藏 --using.
     *
     * @param
     *        	integer feed_id 资源ID
     *
     * @return array 状态+提示
     */
    public function unfavorite_weibo()
    {
        if (model('Collection')->delCollection($this->data['feed_id'], 'feed')) {
            return array(
                'status' => 1,
                'msg'    => '取消收藏成功',
            );
        } else {
            return array(
                'status' => 0,
                'msg'    => '取消收藏失败',
            );
        }
    }

    /**
     * 举报一条微博 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     * @param
     *        	varchar reason 举报原因
     * @param
     *        	integer from 来源(2-android 3-iphone)
     *
     * @return array 状态+提示
     */
    public function denounce_weibo()
    {
        $feed_id = intval($this->data['feed_id']);
        $feed_uid = model('Feed')->where('is_del=0 and feed_id='.$feed_id)->getField('uid');
        if (!$feed_uid) {
            return array(
                'status' => 0,
                'msg'    => '内容已被删除，举报失败',
            );
        }

        if ($this->data['from'] == 2) {
            $data['from'] = 'Android';
        } elseif ($this->data['from'] == 3) {
            $data['from'] = 'iPhone';
        } else {
            $data['from'] = 'mobile';
        }
        $data['aid'] = $feed_id;
        $data['uid'] = $this->mid;
        $data['fuid'] = $feed_uid;
        if ($isDenounce = model('Denounce')->where($data)->count()) {
            return array(
                'status' => 0,
                'msg'    => L('PUBLIC_REPORTING_INFO'),
            );
        } else {
            $data['content'] = D('feed_data')->where('feed_id='.$feed_id)->getField('feed_content');
            $data['reason'] = t($this->data['reason']);
            $data['source_url'] = '[SITE_URL]/index.php?app=public&mod=Profile&act=feed&feed_id='.$feed_id;
            $data['ctime'] = time();
            if ($id = model('Denounce')->add($data)) {
                // 添加积分
                // model('Credit')->setUserCredit($this->mid, 'report_weibo');
                // model('Credit')->setUserCredit($feed_uid, 'reported_weibo');

                $touid = D('user_group_link')->where('user_group_id=1')->field('uid')->findAll();
                foreach ($touid as $k => $v) {
                    model('Notify')->sendNotify($v['uid'], 'denouce_audit');
                }

                return array(
                    'status' => 1,
                    'msg'    => '举报成功',
                );
            } else {
                return array(
                    'status' => 0,
                    'msg'    => L('PUBLIC_REPORT_ERROR'),
                );
            }
        }
    }

    /**
     * ******** 用户相关微博信息列表API *********.
     */

    /**
     * 用户发的微博 --using.
     *
     * @param int     $user_id
     *                         用户UID
     * @param varchar $uname
     *                         用户名
     * @param int     $max_id
     *                         上次返回的最后一条微博ID
     * @param int     $count
     *                         微博条数
     * @param int     $type
     *                         微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function user_timeline()
    {
        if (empty($this->user_id) && empty($this->data['uname'])) {
            $uid = $this->mid;
        } else {
            if ($this->user_id) {
                $uid = intval($this->user_id);
            } else {
                $uid = model('User')->where(array(
                    'uname' => $this->data['uname'],
                ))->getField('uid');
            }
        }
        // echo $uid ;exit();
        $max_id = intval($this->max_id);
        $count = intval($this->count);
        $type = $this->data['type'];
        $is_anonymous = $this->data['is_anonymous'];
        $where = "uid = '{$uid}' AND is_del = 0";
        if (in_array($type, array(
            'postimage',
            'postfile',
            'postvideo',
        ))) {
            $where .= " AND type='{$type}' ";
        } elseif ($type == 'post') {
            $where .= ' AND is_repost=0';
        } elseif ($type == 'repost') {
            $where .= ' AND is_repost=1';
        }
        !empty($max_id) && $where .= " AND feed_id < {$max_id}";
        if($is_anonymous){
            $where .= ' AND is_anonymous = 1';
        }else{
            $where .= ' AND is_anonymous = 0';
        }
        $feed_ids = model('Feed')->where($where)->field('feed_id')->limit($count)->order('is_top DESC,feed_id DESC')->getAsFieldArray('feed_id');

        return $this->format_feed($feed_ids);
    }


    /**
     * @return array
     *
     * 用户参与的微博
     */
    public function part_in_timeline()
    {
        if (empty($this->user_id) && empty($this->data['uname'])) {
            $uid = $this->mid;
        } else {
            if ($this->user_id) {
                $uid = intval($this->user_id);
            } else {
                $uid = model('User')->where(array(
                    'uname' => $this->data['uname'],
                ))->getField('uid');
            }
        }
        $map = array();
        $map['max_id'] = intval($this->max_id);
        $map['count'] = intval($this->count);
        $map['is_anonymous'] = $this->data['is_anonymous'];
        $map['uid'] = $uid;
        $feed_ids = model('Feed')->part_in_timeline($map);
        $ids = array_column($feed_ids, 'feed_id');//二维转一维
        return $this->format_feed($ids);
    }


    /**
     * 用户收藏的微博 --using.
     *
     * @param
     *        	integer user_id 用户UID
     * @param
     *        	integer max_id 上次返回的最后一条收藏ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	integer type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function user_collections()
    {
        $user_id = $this->user_id ? intval($this->user_id) : $this->mid;
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $type = t($this->data['type']);

        $map['c.uid'] = $user_id;
        // $map ['f.app'] = 'public';
        $map['f.app'] = array('in', array("'public'", "'weiba'"));
        if (in_array($type, array(
            'postimage',
            'postfile',
            'postvideo',
        ))) {
            $map['f.type'] = $type;
        } elseif ($type == 'post') {
            $map['f.is_repost'] = 0;
        } elseif ($type == 'repost') {
            $map['f.is_repost'] = 1;
        }
        $map['c.source_table_name'] = 'feed';
        !empty($max_id) && $map['c.collection_id'] = array(
            'lt',
            $max_id,
        );
        $list = D()->table('`'.C('DB_PREFIX').'feed` AS f LEFT JOIN `'.C('DB_PREFIX').'collection` AS c ON f.`feed_id` = c.`source_id`')->field('c.`source_id`,c.collection_id')->where($map)->order('c.collection_id DESC')->limit($count)->findAll();
        $collection_list = array();
        foreach ($list as $k => $v) {
            // 微博信息
            $feed_info = model('Cache')->get('feed_info_api_'.$v['source_id']);
            if ($feed_info) {
                $r[$k] = $feed_info;
            } else {
                $r[$k] = $this->get_feed_info($v['source_id']);
                if ($r[$k]['is_repost'] == 1) {
                    $r[$k]['source_info'] = $this->get_source_info($r[$k]['app_name'], $r[$k]['stable'], $r[$k]['sid']);
                } else {
                    $r[$k]['source_info'] = array();
                }
                model('Cache')->set('feed_info_api_'.$v['source_id'], $r[$k]);
            }
            // 赞、评论
            $diggarr = model('FeedDigg')->checkIsDigg($v['source_id'], $GLOBALS['ts']['mid']);
            $r[$k]['is_digg'] = t($diggarr[$v['source_id']] ? 1 : 0);
            $r[$k]['is_favorite'] = model('Collection')->where('uid='.$GLOBALS['ts']['mid'].' and source_id='.$v['source_id'])->count();
            if ($this->mid != $feed_info['uid']) {
                $privacy = model('UserPrivacy')->getPrivacy($this->mid, $feed_info['uid']);
                if ($privacy['comment_weibo'] == 1) {
                    $r[$k]['can_comment'] = 0;
                } else {
                    $r[$k]['can_comment'] = 1;
                }
            } else {
                $r[$k]['can_comment'] = 1;
            }
            // 用户信息
            $r[$k]['user_info'] = $this->get_user_info($r[$k]['uid']);
            // 评论
            $r[$k]['comment_info'] = $this->weibo_comments($v['source_id'], 4);
            // 收藏ID
            $r[$k]['collection_id'] = $v['collection_id'];
            $collection_list[] = $r[$k];
        }

        return $collection_list;
    }

    /**
     * ******** 搜索相关的接口API *********.
     */

    /**
     * 按关键字搜索微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条收藏ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar key 关键字
     * @param
     *        	integer type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function weibo_search_weibo()
    {
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $is_anonymous = $this->data['is_anonymous'] ? intval($this->data['is_anonymous']) : 0;
        $key = $this->data['key'];
        $type = t($this->data['type']);
        $search_type = $this->data['search_type']; //搜索热门
        $key = t(trim($key));
        $key = str_ireplace(array(
            '%',
            "'",
            '"',
        ), '', $key);
        if (empty($key)) {
            return array();
        }
        $map['a.is_del'] = 0;
        $map['a.is_audit'] = 1;
        $map['a.is_anonymous'] = $is_anonymous;
        !empty($max_id) && $map['a.feed_id'] = array(
            'lt',
            $max_id,
        );
        $map['b.feed_content'] = array(
            'LIKE',
            '%'.$key.'%',
        );
        if (in_array($type, array(
            'postimage',
            'postfile',
            'postvideo',
        ))) {
            $map['a.type'] = $type;
        } elseif ($type == 'post') {
            $map['a.is_repost'] = 0;
        } elseif ($type == 'repost') {
            $map['a.is_repost'] = 1;
        }
        //热门或者广场
        if($search_type){
            $map['a.is_recommend'] = 1;
        }
        $feed_ids = D()->table('`'.C('DB_PREFIX').'feed` AS a LEFT JOIN `'.C('DB_PREFIX').'feed_data` AS b ON a.`feed_id` = b.`feed_id`')->field('a.`feed_id`')->where($map)->order('a.`feed_id` DESC')->limit($count)->getAsFieldArray('feed_id');

        //搜索统计
        $s_type = $is_anonymous;
        $s_count = $feed_ids?count($feed_ids):0;
        model('KeywordStatistic')->setKeywordStatistic($key,$s_type,$s_count);

        return $this->format_feed($feed_ids);
    }

    /**
     * 按话题搜索微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条收藏ID
     * @param
     *        	integer count 微博条数
     * @param
     *        	varchar key 关键字
     * @param
     *        	integer type 微博类型 'post','repost','postimage','postfile','postvideo'
     *
     * @return array 微博列表
     */
    public function weibo_search_topic()
    {
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $key = $this->data['key'];
        $type = t($this->data['type']);

        $key = t(trim($key));
        $key = trim($key, '#');
        $key = str_ireplace(array(
            '%',
            "'",
            '"',
        ), '', $key);
        if (empty($key)) {
            return array();
        }
        $map['a.is_del'] = 0;
        $map['a.is_audit'] = 1;
        !empty($max_id) && $map['a.feed_id'] = array(
            'lt',
            $max_id,
        );
        $map['b.feed_content'] = array(
            'LIKE',
            '%#'.$key.'#%',
        );
        if (in_array($type, array(
            'postimage',
            'postfile',
            'postvideo',
        ))) {
            $map['a.type'] = $type;
        } elseif ($type == 'post') {
            $map['a.is_repost'] = 0;
        } elseif ($type == 'repost') {
            $map['a.is_repost'] = 1;
        }
        $feed_ids = D()->table('`'.C('DB_PREFIX').'feed` AS a LEFT JOIN `'.C('DB_PREFIX').'feed_data` AS b ON a.`feed_id` = b.`feed_id`')->field('a.`feed_id`')->where($map)->order('a.`feed_id` DESC')->limit($count)->getAsFieldArray('feed_id');

        return $this->format_feed($feed_ids);
    }

    /**
     * 搜索@最近联系人 --using.
     *
     * @param
     *        	varchar key 关键字
     * @param
     *        	integer max_id 上次返回的最后一条用户UID
     * @param
     *        	integer count 用户条数
     *
     * @return array 用户列表
     */
    public function search_at()
    {
        $key = trim(t($this->data['key']));
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;

        $at_list = array();
        if (!$key) {
            if (!$max_id) {
                $map['uid'] = $this->mid;
                $map['key'] = 'user_recentat';
                $users = model('UserData')->where($map)->getField('value');
                $user_list = unserialize($users);
                if ($user_list) {
                    foreach ($user_list as $k => $v) {
                        $at_list[$k] = $v;
                        $intro = model('User')->where('uid='.$v['uid'])->getField('intro');
                        $at_list[$k]['intro'] = $intro ? formatEmoji(false, $intro) : '';
                        $at_list[$k]['avatar'] = $v['avatar_small'];
                    }
                }
            }
        } else {
            $uid_arr = model('User')->where(array(
                'uname' => $key,
            ))->field('uid,uname,intro')->findAll(); // 先搜索和key一致的，优先显示
            if ($uid_arr) {
                $map['uid'] = array(
                    'neq',
                    $uid_arr[0]['uid'],
                );
                !empty($key) && $map['search_key'] = array(
                    'like',
                    '%'.$key.'%',
                );
                if (!$max_id) {
                    $user_list = model('User')->where($map)->field('uid,uname,intro')->order('uid desc')->limit($count - 1)->findAll();
                    $user_list = array_merge($uid_arr, $user_list);
                } else {
                    $map['uid'] = array(
                        array(
                            'lt',
                            $max_id,
                        ),
                        array(
                            'neq',
                            $uid_arr[0]['uid'],
                        ),
                        'AND',
                    );
                    $user_list = model('User')->where($map)->field('uid,uname,intro')->order('uid desc')->limit($count)->findAll();
                }
            } else {
                !empty($max_id) && $map['uid'] = array(
                    'lt',
                    $max_id,
                );
                !empty($key) && $map['search_key'] = array(
                    'like',
                    '%'.$key.'%',
                );
                $user_list = model('User')->where($map)->field('uid,uname,intro')->order('uid desc')->limit($count)->findAll();
            }
            if ($user_list) {
                foreach ($user_list as $k => $v) {
                    $at_list[$k]['uid'] = $v['uid'];
                    $at_list[$k]['uname'] = $v['uname'];
                    $at_list[$k]['remark'] = D('UserRemark')->getRemark($this->mid, $v['uid']);
                    $at_list[$k]['intro'] = $v['intro'] ? formatEmoji(false, $v['intro']) : '';
                    //个人空间隐私权限
                    $privacy = model('UserPrivacy')->getPrivacy($this->mid, $v['uid']);
                    $at_list[$k]['space_privacy'] = $privacy['space'];
                    $avatar = model('Avatar')->init($v['uid'])->getUserAvatar();
                    $at_list[$k]['avatar'] = $avatar['avatar_small'];
                }
            }
        }

        return $at_list;
    }

    /**
     * 搜索话题 --using.
     *
     * @param
     *        	varchar key 关键字
     * @param
     *        	integer max_id 上次返回的最后一条话题ID
     * @param
     *        	integer count 话题条数
     *
     * @return array 话题列表
     */
    public function search_topic()
    {
        $key = formatEmoji(true, trim(t($this->data['key'])));
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;

        !empty($max_id) && $map['topic_id'] = array(
            'lt',
            $max_id,
        );
        !empty($key) && $map['topic_name'] = array(
            'like',
            '%'.$key.'%',
        );
        $map['lock'] = 0;
        $data = model('FeedTopic')->where($map)->field('topic_id,topic_name')->limit($count)->order('topic_id desc')->findAll();
        if ($data) {
            foreach ($data as &$v) {
                $v['topic_name'] = parseForApi($v['topic_name']);
            }

            return $data;
        } else {
            return array();
        }
    }

    /**
     * ******** 用户的相关微博--将合并 @我的、评论我的等等微博列表 *********.
     */

    /**
     * 提到用户的微博 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条atme_id
     * @param
     *        	integer count @条数
     *
     * @return array 提到我的列表
     */
    public function user_mentions()
    {
        model('UserData')->setKeyValue($this->mid, 'unread_atme', 0);
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $where = "uid = '{$this->mid}'";
        !empty($max_id) && $where .= " AND atme_id < {$max_id}";

        $list = D('atme')->where($where)->limit($count)->order('atme_id DESC')->findAll();
        $atme_arr = array();
        foreach ($list as $k => $v) {
            $atme['atme_id'] = $v['atme_id'];
            if ($v['table'] == 'comment') {
                $atme['atme_type'] = 'comment';
                $comment = D('comment')->where('comment_id='.$v['row_id'])->field('row_id,uid,content,ctime')->find();
                $atme['feed_id'] = $comment['row_id'];
                $atme['type'] = 'post';
                $atme['content'] = $comment['content'];
                $atme['ctime'] = $comment['ctime'];
                $atme['from'] = '来自网站';
                $atme['user_info'] = $this->get_user_info($comment['uid']);
                $atme['attach_info'] = array();
                $feed_info = $this->format_feed(array(
                    $comment['row_id'],
                ), 0);
                if (!$feed_info[0] || $feed_info[0]['is_del'] == 1) {
                    unset($atme);
                    continue;
                }
                $atme['feed_info'] = $feed_info[0];
            } else { // 微博
                $atme['atme_type'] = 'feed';
                $feed_info = $this->format_feed(array(
                    $v['row_id'],
                ), 0);
                if (!$feed_info[0] || $feed_info[0]['is_del'] == 1) {
                    unset($atme);
                    continue;
                }
                $atme['feed_id'] = $feed_info[0]['feed_id'];
                $atme['type'] = $feed_info[0]['type'];
                $atme['content'] = $feed_info[0]['content'];
                $atme['ctime'] = $feed_info[0]['publish_time'];
                $atme['from'] = $feed_info[0]['from'];
                $atme['user_info'] = $feed_info[0]['user_info'];
                $atme['attach_info'] = $feed_info[0]['attach_info'];
                $atme['feed_info'] = $feed_info[0]['source_info'];
            }
            $atme_arr[] = $atme;
            unset($atme);
        }

        return $atme_arr;
    }

    /**
     * 与我相关.
     *
     * @param
     *        	integer max_id 上次返回的最后一条atme_id
     * @param`
     *        	integer count @条数
     *
     * @return array 与我相关列表
     */
    public function user_related()
    {
        model('UserData')->setKeyValue($this->mid, 'unread_atme', 0);
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $where = "uid = '{$this->mid}'";
        !empty($max_id) && $where .= " AND row_id < {$max_id}";
        $list = D('atme')->where($where)->limit($count)->order('atme_id DESC')->findAll();

        foreach ($list as $k => $v) {
            if ($v['table'] == 'comment') {
                $comment = D('comment')->where('comment_id='.$v['row_id'])->field('row_id,uid,content,ctime')->find();
                $row_ids[] = $comment['row_id'];
            } else { // 微博
                $row_ids[] = $v['row_id'];
            }
        }
        $feed_info = $this->format_feed($row_ids);

        //剔除已删除数据
        foreach ($feed_info as $k => $v) {
            if (!$v['is_del']) {
                $_feed_info[] = $v;
            }
        }
        if (count($_feed_info) > 0) {
            return $_feed_info;
        } else {
            return array();
        }
    }

    /**
     * 获取当前用户收到的评论 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条comment_id
     * @param
     *        	integer count 评论条数
     *
     * @return array 评论列表
     */
    public function user_comments_to_me()
    {
        $where = " ( (app_uid = '{$this->mid}' or to_uid = '{$this->mid}') and uid != '{$this->mid}' )";
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $is_anonymous = intval($this->data['is_anonymous']); //是否匿名
        $clean = intval($this->data['clean']); //是否清除消息标识
        $where .= ' AND is_del=0';
        if ($this->data['type'] == 'weiba_post') {
            $where .= ' AND app="weiba"';
            model('UserData')->setKeyValue($this->mid, 'unread_comment_weiba', 0);
        } else {
            $where .= ' AND app!="weiba"';
            //实名未读消息和匿名未读消息清零
            if($is_anonymous&&$clean){
                model('UserData')->setKeyValue($this->mid, 'unread_comment', 0);
                model('UserData')->setKeyValue($this->mid, 'anonymous_unread_comment', 0);
                return array(
                    'status' => 1,
                    'msg'    => '',
                );
            }else{
                $msg_count = model('UserData')->setUid($this->mid)->getUserData();
                if($msg_count['anonymous_unread_comment']>0){
                    $unread_comment = $msg_count['unread_comment'] - $msg_count['anonymous_unread_comment'];
                    if($unread_comment>0){
                        model('UserData')->updateKey('unread_comment', $unread_comment, false, $this->mid);
                    }
                }else{
                    model('UserData')->setKeyValue($this->mid, 'unread_comment', 0);
                }
            }
        }
        !empty($max_id) && $where .= " AND comment_id < {$max_id}";

        //匿名评论查询
        if($is_anonymous){
            $where .= " AND is_anonymous = 1";
        }else{
            $where .= " AND is_anonymous = 0";
        }

        $list = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();
        $comment_arr = array();
        foreach ($list as $k => $v) {
            $feed_info = $this->format_feed(array(
                $v['row_id'],
            ), 0);
            if (!$feed_info[0] || $feed_info[0]['is_del'] == 1) {
                unset($comment);
                continue;
            }
            $comment['comment_id'] = $v['comment_id'];
            $comment['feed_id'] = $v['row_id'];
            $comment['type'] = 'post';
            $comment['content'] = formatEmoji(false, $v['content']);
            $comment['ctime'] = $v['ctime'];
            $comment['from'] = '来自网站';
            $comment['user_info'] = $this->get_user_info($v['uid']);
            $comment['attach_info'] = array();
            $comment['feed_info'] = $feed_info[0];

            $comment_arr[] = $comment;
            unset($comment);
        }

        return $comment_arr;
    }

    /**
     * 获取当前用户发出的评论 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条comment_id
     * @param
     *        	integer count 评论条数
     *
     * @return array 评论列表
     */
    public function user_comments_by_me()
    {
        $where = " uid = '{$this->mid}' ";
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $where .= ' AND is_del=0';
        !empty($max_id) && $where .= " AND comment_id < {$max_id}";

        $list = model('Comment')->where($where)->order('comment_id DESC')->limit($count)->findAll();
        $comment_arr = array();
        foreach ($list as $k => $v) {
            $feed_info = $this->format_feed(array(
                $v['row_id'],
            ), 0);
            if (!$feed_info[0] || $feed_info[0]['is_del'] == 1) {
                unset($comment);
                continue;
            }
            $comment['comment_id'] = $v['comment_id'];
            $comment['feed_id'] = $v['row_id'];
            $comment['type'] = 'post';
            $comment['content'] = $v['content'];
            $comment['ctime'] = $v['ctime'];
            $comment['from'] = '来自网站';
            $comment['user_info'] = $this->get_user_info($v['uid']);
            $comment['attach_info'] = array();
            $comment['feed_info'] = $feed_info[0];

            $comment_arr[] = $comment;
            unset($comment);
        }

        return $comment_arr;
    }

    /**
     * 获取当前用户的收到的赞 --using.
     *
     * @param
     *        	integer max_id 上次返回的最后一条digg_id
     * @param
     *        	integer count 赞条数
     *
     * @return array 赞列表
     */
    public function user_diggs_to_me()
    {
        //model('UserData')->setKeyValue($this->mid, 'unread_digg', 0);
        $max_id = $this->max_id ? intval($this->max_id) : 0;
        $count = $this->count ? intval($this->count) : 20;
        $is_anonymous = intval($this->data['is_anonymous']); //是否匿名

        //实名未读消息和匿名未读消息清零
        if($is_anonymous){
            model('UserData')->setKeyValue($this->mid, 'unread_digg', 0);
            model('UserData')->setKeyValue($this->mid, 'anonymous_unread_digg', 0);
        }else{
            $msg_count = model('UserData')->setUid($this->mid)->getUserData();
            if($msg_count['anonymous_unread_digg']>0){
                $unread_digg = $msg_count['unread_digg'] - $msg_count['anonymous_unread_digg'];
                if($unread_digg>0){
                    model('UserData')->updateKey('unread_digg', $unread_digg, false, $this->mid);
                }
            }else{
                model('UserData')->setKeyValue($this->mid, 'unread_digg', 0);
            }
        }

        $map['f.uid'] = $this->mid;
        $map['f.is_del'] = 0;
        !empty($max_id) && $map['d.id'] = array(
            'lt',
            $max_id,
        );
        if($is_anonymous){
            $map['f.is_anonymous'] = 1;
        }
        $tablePrefix = C('DB_PREFIX');
        $list = D()->table("{$tablePrefix}feed AS f RIGHT JOIN {$tablePrefix}feed_digg AS d ON f.feed_id = d.feed_id ")->where($map)->order('d.id desc')->field('d.id as id,d.uid as uid,d.feed_id as feed_id,d.cTime as ctime')->limit($count)->findAll();
        $digg_arr = array();
        foreach ($list as $k => $v) {
            $digg['digg_id'] = $v['id'];
            $feed_info = $this->format_feed(array(
                $v['feed_id'],
            ), 0);
            if (!$feed_info[0] || $feed_info[0]['is_del'] == 1) {
                unset($digg);
                continue;
            }
            $digg['feed_id'] = $v['feed_id'];
            $digg['type'] = 'post';
            $digg['content'] = '赞了这条微博';
            $digg['ctime'] = $v['ctime'];
            $digg['from'] = '来自网站';
            $digg['user_info'] = $this->get_user_info($v['uid']);
            $digg['attach_info'] = array();
            $digg['feed_info'] = $feed_info[0];

            $digg_arr[] = $digg;
            unset($digg);
        }

        return $digg_arr;
    }

    /**
     * ******** 其他公用操作API *********.
     */

    /**
     * 格式化手机端微博 --using.
     *
     * @param
     *        	array feed_ids 微博ID
     *
     * @return array 微博详细信息
     */
    public function format_feed($feed_ids, $show_comment = 1)
    {
        if (count($feed_ids) > 0) {
            $r = array();
            foreach ($feed_ids as $k => $v) {
                // 微博信息
                $feed_info = model('Cache')->get('feed_info_api_'.$v);
                if ($feed_info) {
                    //每个用户的备注信息不同
                    foreach ($feed_info['digg_users'] as $key => &$value) {
                        unset($value['remark']);
                        $value['remark'] = D('UserRemark')->getRemark($this->mid, $value['uid']);
                    }
                    $r[$k] = $feed_info;
                } else {
                    $r[$k] = $this->get_feed_info($v);
                    if (empty($r[$k])) {
                        unset($r[$k]);
                        continue;
                    } else {
                        if ($r[$k]['is_repost'] == 1) {
                            $r[$k]['source_info'] = $this->get_source_info($r[$k]['app_name'], $r[$k]['stable'], $r[$k]['sid']);

                            //转发内容为文件时，不显示
                            if ($r[$k]['source_info']['type'] == 'postfile') {
                                unset($r[$k]);
                                continue;
                            }
                        } else {
                            $r[$k]['source_info'] = array();
                        }
                        model('Cache')->set('feed_info_api_'.$v, $r[$k]);
                    }
                }
                // 用户信息
                $r[$k]['user_info'] = $this->get_user_info($r[$k]['uid']);
                // 赞、收藏
                $diggarr = model('FeedDigg')->checkIsDigg($v, $GLOBALS['ts']['mid']);
                $r[$k]['is_digg'] = $diggarr[$v] ? 1 : 0;
                $r[$k]['is_favorite'] = model('Collection')->where('uid='.$GLOBALS['ts']['mid'].' and source_id='.$v)->count();
                if ($this->mid != $feed_info['uid']) {
                    $privacy = model('UserPrivacy')->getPrivacy($this->mid, $feed_info['uid']);
                    if ($privacy['comment_weibo'] == 1) {
                        $r[$k]['can_comment'] = 0;
                    } else {
                        $r[$k]['can_comment'] = 1;
                    }
                } else {
                    $r[$k]['can_comment'] = 1;
                }
                //检查资讯是否被当前用户举报

                $denounce = M('denounce')->where(array('uid'=>$this->mid,'aid'=>$v))->find();
                $r[$k]['isJubao']  = $denounce ?  1 : 0 ;

                // 评论
                if ($show_comment == 1) {
                    $r[$k]['comment_info'] = $this->weibo_comments($v, 4);
                }

                /* # 地址信息 */
                // $feed = model('Feed')->where('`feed_id` = ' . $v)->field('`latitude`, `longitude`, `address`')->find();
                // $feed['address'] or $feed['address'] = null;
                // $r[$k] = array_merge($r[$k], $feed);
                unset($feed);
            }
            //是否置顶查询
//            foreach ($r as $key => &$value) {
//                unset($value['is_top']);
//                $is_top = D('Feed')->field('is_top')->where('feed_id='.$value['feed_id'])->find();
//                $value['is_top'] = $is_top['is_top'];
//            }
            return array_values($r);
        } else {
            return array();
        }
    }

    /**
     * 获取微博详情 --using.
     *
     * @param
     *        	integer feed_id 微博ID
     * @param
     *        	integer is_source 是否为原微博
     *
     * @return array 微博详细信息
     */
    public function get_feed_info($feed_id)
    {
        $tablePrefix = C('DB_PREFIX');
        // $map['a.is_del'] = 0;
        $map['a.feed_id'] = $feed_id;

        //20150704 手机端不显示文件
        $map['a.type'] = array('neq', 'postfile');

        $feed_info = array();
        $data = model('Feed')->where($map)->table("{$tablePrefix}feed AS a LEFT JOIN {$tablePrefix}feed_data AS b ON a.feed_id = b.feed_id ")->find();
        if (!$data) {
            return array();
        }
        if ($data['is_del'] == 0) {
            $feed_info['status'] = 'no';
            $feed_data = unserialize($data['feed_data']);
            // 微博信息
            $feed_info['feed_id'] = $data['feed_id'];
            $feed_info['uid'] = $data['uid'];
            $feed_info['type'] = $data['type'];
            $feed_info['app_name'] = $data['app'];
            $feed_info['stable'] = $data['app_row_table'];
            $feed_info['sid'] = $data['app_row_id'] ? $data['app_row_id'] : $data['feed_id'];
            $feed_info['is_repost'] = $data['is_repost'];
            $feed_info['publish_time'] = $data['publish_time'];
            $feed_info['recommend_time'] = $data['recommend_time'];
            $feed_info['is_anonymous'] = $data['is_anonymous'];

            /* # 地址信息 */
            $feed_info['latitude'] = $data['latitude'];
            $feed_info['longitude'] = $data['longitude'];
            $feed_info['address'] = $data['address'];
            $feed_info['address'] or $feed_info['address'] = null;

            if ($channel_category_id = D('channel')->where('feed_id='.$data['feed_id'])->getField('channel_category_id')) {
                $feed_info['channel_category_id'] = $channel_category_id;
                $channel_category_name = D('channel_category')->where('channel_category_id='.$channel_category_id)->getField('title');
                $feed_info['channel_category_name'] = $channel_category_name;
                if($channel_category_name=='网站'){
                    $channel_category_name = '奥豆APP';
                }elseif($channel_category_name=='手机'){
                    $channel_category_name = '奥豆APP';
                }
                $from = '来自奥豆APP';
            } else {
                switch ($data['from']) {
                    case 1:
                        $from = '来自奥豆APP';//来自手机  来自iPhone
                        break;
                    case 2:
                        $from = '来自Android';
                        break;
                    case 3:
                        $from = '来自奥豆APP';//来自iPhone
                        break;
                    case 4:
                        $from = '来自iPad';
                        break;
                    case 5:
                        $from = '来自Windows';
                        break;
                    case 6:
                        $from = '来自H5客户端';
                        break;
                    case 0:
                    default:
                        $from = '来自奥豆APP';//来自网站
                        break;
                }
            }
            $feed_info['from'] = $from;
            if (in_array($data['type'], array(
                    'post',
                    'postimage',
                    'postfile',
                    'postvideo',
                )) || stristr($data['type'], 'repost')) {
                $feed_info['content'] = parseForApi($feed_data['body']);
                // $feed_info ['content'] = $feed_data ['body'];
                // $feed_info['content'] = $feed_info['feed_content']; // 调试性代码，因为mysql储存的字节有限，存了不完整的序列化字符串
            } else { // 内容为空，提取应用里的信息
                $source_info = $this->get_source_info($data['app'], $data['app_row_table'], $data['app_row_id']);
                $feed_info['title'] = $source_info['title'];
                $feed_info['content'] = $source_info['content'];
                $feed_info['source_name'] = $source_info['source_name'];
                $feed_info['source_url'] = $source_info['source_url'];
            }
            // 其它信息
            $feed_info['repost_count'] = $data['repost_count'];
            $feed_info['comment_count'] = $data['comment_count'];
            $feed_info['digg_count'] = $data['digg_count'];
            /* # 点赞人数列表 */
            $feed_info['digg_users'] = $this->weibo_diggs($data['feed_id'], 5);
            // 附件处理
            if (!empty($feed_data['attach_id'])) {
                $attach = model('Attach')->getAttachByIds($feed_data['attach_id']);
                foreach ($attach as $ak => $av) {
                    $_attach = array(
                        'attach_id'        => $av['attach_id'],
                        'attach_name'      => $av['name'],
                        'attach_extension' => $av['extension'],
                    );
                    if ($data['type'] == 'postimage') {
                        $_attach['attach_origin'] = getImageUrl($av['save_path'].$av['save_name']);
                        $_attach['attach_origin_width'] = $av['width'];
                        $_attach['attach_origin_height'] = $av['height'];
                        if ($av['width'] > 384 && $av['height'] > 384) {
                            //$_attach['attach_middle'] = getImageUrl($av['save_path'].$av['save_name'], 384, 384, true);
                            $_thumbImage = getThumbImage(UPLOAD_URL.$av['save_path'].$av['save_name'], 384);
                            $_attach['attach_middle'] = UPLOAD_URL.$_thumbImage['src'];
                        } else {
                            $_attach['attach_middle'] = $_attach['attach_origin'];
                        }
                        if ($av['width'] > 220 && $av['height'] > 220) {
                            //$_attach['attach_small'] = getImageUrl($av['save_path'].$av['save_name'], 220, 220, true);
                            $_thumbImage = getThumbImage(UPLOAD_URL.$av['save_path'].$av['save_name'], 220);
                            $_attach['attach_small'] = UPLOAD_URL.$_thumbImage['src'];
                        } else {
                            $_attach['attach_small'] = $_attach['attach_origin'];
                        }
                    }
                    $feed_info['attach_info'][] = $_attach;
                }
            } else {
                $feed_info['attach_info'] = array(
                    'attach_id'        => '',
                    'attach_name'      => '',
                    'attach_extension' => '',
                    'attach_origin'    => '',
                    'attach_middle'    => '',
                    'attach_small'     => '',
                );
            }
            if ($data['type'] == 'postvideo') {
                if ($feed_data['video_id']) {
                    $video_info['host'] = '1';
                    $video_config = model('Xdata')->get('admin_Content:video_config');
                    $video_server = $video_config['video_server'] ? $video_config['video_server'] : SITE_URL;
                    $video_info['video_id'] = $feed_data['video_id'];
                    $video_info['flashimg'] = $video_server.$feed_data['image_path'];
                    $video_info['flash_width'] = $feed_data['image_width'];
                    $video_info['flash_height'] = $feed_data['image_height'];
                    $video_info['flashvar'] = $feed_data['video_path'] ? $video_server.$feed_data['video_path'] : $video_server.$feed_data['video_mobile_path'];
                    $video_info['flashvar_part'] = $video_server.$feed_data['video_part_path'];
                } else {
                    $video_info['host'] = $feed_data['host'];
                    $video_info['flashvar'] = $feed_data['flashvar'];
                    $video_info['source'] = $feed_data['source'];
                    $video_info['flashimg'] = UPLOAD_URL.'/'.$feed_data['flashimg'];
                    $video_info['title'] = $feed_data['title'];
                }
                $feed_info['attach_info'] = $video_info;
            }
        } else {
            $feed_info['is_del'] = 1;
            $feed_info['feed_id'] = $data['feed_id'];
            $feed_info['user_info'] = $this->get_user_info($data['uid']);
            $feed_info['publish_time'] = $data['publish_time'];
        }

        /* # 将emoji代码格式化为emoji */
        $feed_info['content'] = formatEmoji(false, $feed_info['content']);
        $feed_info['is_top'] = $data['is_top']; //是否置顶 0-否  1-是
        return $feed_info;
    }

    /**
     * 获取资源信息 --using.
     *
     * @param
     *        	varchar app 应用名称
     * @param
     *        	integer app_row_table 资源所在表
     * @param
     *        	integer app_row_id 资源ID
     *
     * @return array 资源信息
     */
    private function get_source_info($app, $app_row_table, $app_row_id)
    {
        switch ($app) {
            case 'event':
                $event_info = D('event_list')->find($app_row_id);
                if(empty($event_info) || $event_info['del'] == 1){
                    $source_info['is_del'] = 1;
                }else{
                    $source_info['id'] = $event_info['eid'];
                    $source_info['name'] = $event_info['name'];
                    $source_info['image'] = getImageUrlByAttachId($event_info['image']);
                    $source_info['stime'] = $event_info['stime'];
                    $source_info['etime'] = $event_info['stime'];
                    $event_info['area'] = model('Area')->getAreaById($event_info['area']);
                    $source_info['area'] = $event_info['area']['title'];
                    $event_info['city'] = model('Area')->getAreaById($event_info['city']);
                    $source_info['city'] = $event_info['city']['title'];
                    $source_info['location'] = $event_info['location'];
                    $source_info['manNumber'] = $event_info['manNumber'];
                    $source_info['remainder'] = $event_info['remainder'];
                }
                break;
            case 'weiba':
                $weiba_post = D('weiba_post')->where('post_id='.$app_row_id.' AND is_del = 0')->field('weiba_id,post_uid,title,content')->find();
                if ($weiba_post) {
                    $source_info['user_info'] = $this->get_user_info($weiba_post['post_uid']);
                    $source_info['title'] = $weiba_post['title'];
                    $source_info['content'] = real_strip_tags($weiba_post['content']);
                    $source_info['url'] = 'mod=Weibo&act=weibo_detail&id='.$app_row_id;
                    $source_info['source_name'] = D('weiba')->where('weiba_id='.$weiba_post['weiba_id'])->getField('weiba_name');
                    $source_info['source_url'] = 'api.php?mod=Weiba&act=post_detail&id='.$app_row_id;
                    /* emoji解析 */
                    $source_info['title'] = formatEmoji(false, $source_info['title']);
                    $source_info['content'] = formatEmoji(false, $source_info['content']);
                } else {
                    $source_info['is_del'] = 1;
                }
                break;
            default:
                $tablePrefix = C('DB_PREFIX');
                $map['a.feed_id'] = $app_row_id;
                $map['a.is_del'] = 0;
                $data = model('Feed')->where($map)->table("{$tablePrefix}feed AS a LEFT JOIN {$tablePrefix}feed_data AS b ON a.feed_id = b.feed_id ")->find();
                if ($data['feed_id']) {
                    $source_info['publish_time'] = $data['publish_time'];
                    $source_info['feed_id'] = $app_row_id;
                    $source_info['user_info'] = $this->get_user_info($data['uid']);
                    $source_info['type'] = real_strip_tags($data['type']);
                    $source_info['content'] = real_strip_tags($data['feed_content']);
                    $source_info['content'] = parseForApi($source_info['content']);
                    $source_info['url'] = 'mod=Weibo&act=weibo_detail&id='.$app_row_id;
                    // 附件处理
                    $feed_data = unserialize($data['feed_data']);
                    if (!empty($feed_data['attach_id'])) {
                        $attach = model('Attach')->getAttachByIds($feed_data['attach_id']);
                        foreach ($attach as $ak => $av) {
                            $_attach = array(
                                'attach_id'   => $av['attach_id'],
                                'attach_name' => $av['name'],
                            );
                            if ($data['type'] == 'postimage') {
                                $_attach['attach_origin'] = getImageUrl($av['save_path'].$av['save_name']);
                                $_attach['attach_origin_width'] = $av['width'];
                                $_attach['attach_origin_height'] = $av['height'];
                                if ($av['width'] > 550 && $av['height'] > 550) {
                                    $_attach['attach_small'] = getImageUrl($av['save_path'].$av['save_name'], 550, 550, true);
                                } else {
                                    $_attach['attach_small'] = $_attach['attach_origin'];
                                }
                            }
                            $source_info['attach_info'][] = $_attach;
                        }
                    } else {
                        $source_info['attach_info'] = array();
                    }
                    if ($data['type'] == 'postvideo') {
                        if ($feed_data['video_id']) {
                            $video_config = model('Xdata')->get('admin_Content:video_config');
                            $video_server = $video_config['video_server'] ? $video_config['video_server'] : SITE_URL;
                            $video_info['video_id'] = $feed_data['video_id'];
                            $video_info['flashimg'] = $video_server.$feed_data['image_path'];
                            $video_info['flash_width'] = $feed_data['image_width'];
                            $video_info['flash_height'] = $feed_data['image_height'];
                            if ($feed_data['transfer_id'] && !D('video_transfer')->where('transfer_id='.$feed_data['transfer_id'])->getField('status')) {
                                $video_info['transfering'] = 1;
                            } else {
                                $video_info['flashvar'] = $feed_data['video_mobile_path'] ? $video_server.$feed_data['video_mobile_path'] : $video_server.$feed_data['video_path'];
                                $video_info['flashvar_part'] = $video_server.$feed_data['video_part_path'];
                            }
                        } else {
                            $video_info['host'] = $feed_data['host'];
                            $video_info['flashvar'] = $feed_data['source'];
                            $video_info['source'] = $feed_data['source'];
                            $video_info['flashimg'] = UPLOAD_URL.$feed_data['flashimg'];
                            $video_info['title'] = $feed_data['title'];
                        }
                        $source_info['attach_info'][] = $video_info;
                    }
                } else {
                    $source_info['is_del'] = 1;
                }
                break;
        }

        return $source_info;
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
     * 获取热门话题.
     *
     * @return array
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getHotTopic()
    {
        return model('FeedTopic')->where(array(
            'recommend' => 1,
            'lock'      => 0,
        ))->order('`recommend_time` DESC')
            ->limit(5)
            ->select();
    }

    /**
     * 获取正在进行的话题.
     *
     * @return array
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getNewTopic()
    {
        $max_id = intval($this->data['max_id']);
        $limit = intval($this->data['limit']);
        $where = array(
            'lock' => 0,
        );
        $max_id && $where['topic_id'] = array('lt', $max_id);

        return model('FeedTopic')->where($where)
            ->order('`topic_id` DESC')
            ->limit($limit)
            ->select();
    }

    public function all_topic()
    {
        $max_id = intval($this->data['max_id']);
        $limit = intval($this->data['limit']);

        if (empty($max_id)) {
            $map2['recommend'] = 1;
            $map2['lock'] = 0;
            $res['commends'] = (array) M('feed_topic')->where($map2)->order('recommend_time desc')->limit(5)->findAll();
            empty($res['commends']) || $map['topic_id'] = array(
                'not in',
                getSubByKey($res['commends'], 'topic_id'),
            );
        } else {
            $map['topic_id'] = array(
                'lt',
                $max_id,
            );
        }
        $map['lock'] = 0;
        $res['lists'] = (array) M('feed_topic')->where($map)->order('topic_id desc')->limit($limit)->findAll();
        foreach ($res['lists'] as &$v) {
            $v['topic_name'] = parseForApi($v['topic_name']);
        }

        return $res;
    }

    /**
     * 获取微博限制字数.
     *
     * @return int
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getWeiboStrMaxLength()
    {
        return array(
            'num' => json_decode(json_encode(model('Xdata')->get('admin_Config:feed')), false)->weibo_nums,
        );
    }


    /**
     * @return array
     *
     * 微博（分享）置顶
     */
    public function feed_to_top(){
        //暂时取消用户置顶 2017-11-02
        return array(
            'status' => 1,
            'msg'    => '置顶成功',
        );
        //只能置顶一条
        $uid = $this->mid;
        $f_map = array();
        $f_map['uid'] = $uid;
        $f_map['is_top'] = 1;
        $f_map['is_del'] = 0;
        $f_map['is_audit'] = 1;
        $count = model('Feed')->where($f_map)->count();
        if($count>0){
            return array(
                'status' => 0,
                'msg'    => '只能置顶一条分享哦',
            );
        }
        $feed_id = intval($this->data['feed_id']);
        $map['is_top'] = 1;
        $feed_uid = D('Feed')->where('feed_id='.$feed_id)->save($map);
        if ($feed_uid===false) {
            return array(
                'status' => 0,
                'msg'    => '置顶失败',
            );
        }else{
            return array(
                'status' => 1,
                'msg'    => '置顶成功',
            );
        }
    }

    /**
     * @return array
     *
     * 微博（分享）取消置顶
     */
    public function feed_un_top(){
        $feed_id = intval($this->data['feed_id']);
        $map['is_top'] = 0;
        $feed_uid = D('Feed')->where('feed_id='.$feed_id)->save($map);
        if ($feed_uid===false) {
            return array(
                'status' => 0,
                'msg'    => '取消置顶失败',
            );
        }else{
            return array(
                'status' => 1,
                'msg'    => '取消置顶成功',
            );
        }
    }


}
