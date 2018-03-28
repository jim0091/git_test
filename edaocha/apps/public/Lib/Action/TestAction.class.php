<?php

class TestAction extends Action
{
    public function _initialize()
    {
        header('Content-Type:text/html; charset=UTF8');
    }

    public function show()
    {
        $demo = model('SensitiveWord')->checkedContent();
        dump($demo);
    }

    public function testGit()
    {
        var_dump('Git Svn!!!2313');
    }

    public function upBug()
    {
        set_time_limit(0);
        // user list
        $sql = 'select a.uid, a.user_group_id from ts_user_group_link as a left join ts_user_group_link as b on a.uid = b.uid and a.user_group_id = b.user_group_id where a.id != b.id order by a.id asc;';
        $list = D()->query($sql);
        $uids = getSubByKey($list, 'uid');
        $uids = array_unique($uids);
        foreach ($uids as $uid) {
            $map['uid'] = $uid;
            $map['user_group_id'] = 5;
            model('UserGroupLink')->where($map)->delete();

            model('UserGroupLink')->add($map);
        }
        dump('OK');
        // $list = model('User')->find
        // $demo = model('User')->getUserInfo($this->mid);
        // dump($demo);
    }

    public function mylove()
    {
        $str = 'alipay_jilu:;atme:;attach:;attach_t:;blog:;blog_category:;channel:;channel_follow:;check_info:;collection:;comment:app_uid;comment:;comment:to_uid;credit_user:;denounce:;denounce:fuid;develop:;diy_page:;diy_widget:;document:deleteUid;document_attach:;document_draft:;document_lock:;event:;event_photo:;event_user:;feed:;feedback:;find_password:;group:;group_atme:;group_attachment:;group_comment:app_uid;group_comment:;group_comment:to_uid;group_feed:;group_invite_verify:;group_log:;group_member:;group_post:;group_topic:;group_user_count:;invite_code:inviter_uid;invite_code:receiver_uid;login:;login_logs:;login_record:;medal_user:;message_content:from_uid;message_list:from_uid;message_member:member_uid;notify_email:;notify_message:;online:;online_logs:;online_logs_bak:;poppk:cUid;poppk_vote:;poster:;sitelist_site:;survey_answer:;task_receive:;task_user:;template_record:;tipoff:;tipoff:bonus_uid;tipoff_log:;tips:;user_app:;user_blacklist:;user_category_link:;user_change_style:;user_count:;user_credit_history:;user_data:;user_department:;user_follow:;user_follow_group:;user_follow_group_link:;user_group_link:;user_official:;user_online:;user_privacy:;user_profile:;user_verified:;vote:;vote_user:;vtask:assigner_uid;vtask:deal_uid;vtask_log:;vtask_process:assigner_uid;vtask_process:deal_uid;weiba:;weiba:admin_uid;weiba_apply:follower_uid;weiba_apply:manager_uid;weiba_favorite:;weiba_favorite:post_uid;weiba_follow:follower_uid;weiba_log:;weiba_post:post_uid;weiba_post:last_reply_uid;weiba_reply:post_uid;weiba_reply:;weiba_reply:to_uid;x_article:;x_logs:';
        $arr = explode(';', $str);
        foreach ($arr as $v) {
            $info = explode(':', $v);
            $table = C('DB_PREFIX').$info[0];
            $field = empty($info[1]) ? 'uid' : $info[1];

            $sql = 'DELETE FROM '.$table.' WHERE '.$field.' NOT IN (SELECT uid FROM ts_user) ';
            M()->execute($sql);
        }

/* 		$sql = "SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.`COLUMNS` WHERE TABLE_SCHEMA='uat_sociax' AND COLUMN_NAME LIKE '%uid%' AND DATA_TYPE='int'";
        $list = M()->query($sql);
        $str = '';
        foreach ($list as $vo){
            $str .= str_replace('ts_','', $vo['TABLE_NAME']).':';
            if($vo['COLUMN_NAME']=='uid'){
                $str .= ';';
            }else{
                $str .= $vo['COLUMN_NAME'].';';
            }

        }
        echo($str); */
    }

    public function testvideo()
    {

        // $links[] = 'http://v.youku.com/v_show/id_XNTI0OTUwNTE2.html?f=19030254';

        // $links[] = 'http://v.ku6.com/special/show_6596857/btfthNTHWhWZ-On6Z-JghQ...html';

        // $links[] = 'http://www.tudou.com/albumplay/309GI6wJ3dQ/Z023ClGghoU.html';

        // $links[] = 'http://www.tudou.com/programs/view/FAaiuw1_hdo/?fr=rec2';

        // $links[] = 'http://www.tudou.com/listplay/yDXsqh6PHmU.html';

        // $links[] = 'http://douwan.tudou.com/?code=givrrwzNd04';

        // $links[] = 'http://tv.sohu.com/20130306/n367915244.shtml';

        //$links[] = 'http://v.qq.com/cover/4/4m5fr32fb43kdhq.html?vid=d0012l06ejo';

        // $links[] = 'http://www.iqiyi.com/dianshiju/20130302/5dd726a96ce14ee3.html';

        //$links[] = 'http://video.sina.com.cn/p/news/w/v/2013-03-19/075662183493.html';

        $links[] = 'http://www.yinyuetai.com/video/628991';

        foreach ($links as $link) {
            $result = model('Video')->getVideoInfo($link);
            dump($result);
        }
    }

    public function avatar()
    {
        $info = model('Avatar')->init($this->mid)->getUserAvatar();
        dump($info);
    }

    public function refreshWeibaFeed()
    {
        set_time_limit(0);
        $sql = "update ts_feed set app_row_table='weiba_post' where app='weiba' AND type='weiba_post' AND app_row_table='feed'";
        $result = D()->execute($sql);
        if (!$result) {
            dump('update weiba_post : false');
            echo '<hr />';
        }

        $sql = "select * from ts_feed where app='weiba' AND type='repost' AND app_row_table='feed' LIMIT 100";
        $result = D()->query($sql);
        if ($result) {
            dump('update weiba_repost : ');
            dump($result);
            echo '<hr />';
        } else {
            dump('update weiba_repost : OK');
        }
        // $sql = "update ts_feed as a set a.app_row_table='weiba_post',a.app='weiba',a.type='weiba_repost',a.app_row_id=(select app_row_id from ts_feed as b where b.feed_id=a.app_row_id) where app='weiba' AND type='repost';";
        // $result = D()->execute($sql);
        // dump($result);
        // dump(D()->getLastSql());
    }

    public function getTopic()
    {
    }

    public function feed()
    {
        // dump(model('Feed')->getNodeList());
        // echo '<hr />';
        // $feed_template = simplexml_load_file(SITE_PATH.'/apps/public/Conf/post.feed.php');
        // dump($feed_template);
        $actor = '刘晓庆';
        $feedIds = array(37);
        $res = model('Feed')->getFeeds($feedIds);
        dump($res);
    }

    public function cloudimage()
    {
        $this->display();
    }

    //下面是一些测试函数
    public function credit()
    {
        dump(model('Credit')->getUserCredit(14983));
        // model('Credit')->setUserCredit(14983,'weibo_add');
        // model('Credit')->setUserCredit(14983,'weibo_add');
        // model('Credit')->setUserCredit(14983,'weibo_add');
        // dump(model('Credit')->getUserCredit(14983));
    }

    public function hello()
    {
        echo '服务器当前时间:',date('Y-m-d H:i:s');
        dump('hello world');
        echo 11111111;
    }

    public function t()
    {
        dump(model('UserPrivacy')->getPrivacy(10000, 14983));
    }

    public function at($data)
    {
        $html = '@{uid=14983|yangjiasheng}';
        echo parse_html($html);
    }

    public function mail()
    {
        dump(model('Mail')->send_email('yangjs17@yeah.net', 'ttt', 'xxxxxxxx'));
    }

    public function cut()
    {
        getThumbImage('./data/upload/2012/0604/19/4fcc9b2f67d34.jpg', '300', 'auto', false);
        echo '<img src="./data/upload/2012/0604/19/4fcc9b2f67d34_300_auto.jpg">';
        echo 11;
    }

    public function data()
    {
        $nums = 1000; //测试数
        $add = array();
        $add['version'] = 1;
        $add['language'] = 'all';
        $add['source_faq_id'] = 0;
        $add['creator_uid'] = $this->mid;
        $add['create_time'] = time();
        $add['comment_count'] = 0;
        for ($i = 0; $i < $nums; $i++) {
            $add['status'] = rand(0, 2);
            $add['active'] = rand(0, 1);
            $add['category_id'] = rand(1, 4);
            $add['tags'] = '测试tag'.$i;
            $add['question_cn'] = '这里是测试的问题中文'.$i;
            $add['question_en'] = 'here is  test question english'.$i;
            $add['answer_cn'] = '这里是测试的答案'.$i;
            $add['answer_en'] = 'here is test answer english'.$i;
            D('')->table('sociax_support')->add($add);
        }
    }

    public function initdata()
    {
        D('PublicSearch', 'public')->initData();
    }

    public function tsearch()
    {
        D('PublicSearch', 'public')->search();
    }

    public function findLang()
    {

        // - app -
        $filePath[] = SITE_PATH.'/apps/public';
        $filePath[] = SITE_PATH.'/apps/support';
        $filePath[] = SITE_PATH.'/apps/contact';
        $filePath[] = SITE_PATH.'/apps/admin';
        $filePath[] = SITE_PATH.'/apps/task';

        $filelist = array();

        foreach ($filePath as $v) {
            $filelist[$v] = $this->getDir($v);
        }

        $findLang = array();

        foreach ($filelist as $vlist) {
            foreach ($vlist as $v) {
                $ext = substr($v, strrpos($v, '.') + 1, strlen($v));
                $data = file_get_contents($v);

                if ($ext == 'php' || $ext == 'js') {
                    $data = preg_replace("!((/\*)[\s\S]*?(\*/))|(//.*)!", '', $data); //去掉注释里的中文
                }
                preg_match_all('/([\x{4e00}-\x{9fa5}])+/u', $data, $result);
                if (!empty($result[0])) {
                    $findLang[$v] = $result[0];
                }
            }
        }
        dump($findLang);
    }

    public function getDir($dir, $list = array())
    {
        $dirs = new Dir($dir);
        $dirs = $dirs->toArray();
        foreach ($dirs as $v) {
            if ($v['isDir']) {
                $list = $this->getDir($v['pathname'], $list);
            } elseif ($v['isFile'] && in_array($v['ext'], array('php', 'html', 'js'))) {
                $list[] = $v['pathname'];
            } else {
                continue;
            }
        }

        return $list;
    }

    //下面是一些demo
    public function demo()
    {
        $this->display();
    }

    public function getImageInfo()
    {
        $data = getImageInfo('2012/1110/18/509e2b90e7a89.jpeg');
        dump($data);
    }

    public function doupload()
    {
        dump($_POST);
    }

    public function dig()
    {
        $data = model('Tips')->findAll();
        $count0 = 0;
        $count1 = 0;
        foreach ($data as $value) {
            $value['type'] == 1 ? $count1++ : $count0++;
        }

        $this->assign('count0', $count0);
        $this->assign('count1', $count1);
        $this->assign('data', $data);
        $this->display();
    }

    public function tree()
    {
        $category = array(
            array('id' => 1, 'name' => 'A1', 'pid' => 0),
            array('id' => 2, 'name' => 'A2', 'pid' => 1),
            array('id' => 3, 'name' => 'A3', 'pid' => 1),
            array('id' => 4, 'name' => 'A4', 'pid' => 2),
            array('id' => 5, 'name' => 'A5', 'pid' => 4),
            array('id' => 6, 'name' => 'A6', 'pid' => 3),
        );

        print_r($this->_tree($category));
    }

    public function _tree($data)
    {

            //所有节点的子节点
            $child = array();
            //hash缓存数组
            $hash = array();
        foreach ($data as $dv) {
            $hash[$dv['id']] = $dv;
            $tree[$dv['id']] = $dv;
            !isset($child[$dv['id']]) && $child[$dv['id']] = array();
            $tree[$dv['id']]['_child'] = &$child[$dv['id']];
            $child[$dv['pid']][] = &$tree[$dv['id']];
        }

        return $child[0];
    }

    public function plot()
    {
        $this->display();
    }

    public function autotag()
    {
        //需要提取的文本
        $text = ' 这里asxasx C++ ,test我也有很多T恤衣服！！！';
        //获取model
        $tagX = model('Tag');
        //设置text
        $tagX->setText($text);
        //获取前10个标签
        $result = $tagX->getTop(10);

        echo '<pre>';
        echo 'text:',$text;
        echo '<br/>结果:',$result;
        echo '
		
		---使用举例---
		
		核心php实现 
		( ajax 请求地址： public/Index/getTags  参数：text(内容)，limit:提取的标签个数 ）

		//需要提取的文本
		$text = "这里asxasx C++ ,test我也有很多T恤衣服！！！";
		//获取model
		$tagX = model("Tag");
		//设置text
		$tagX->setText($text);
		//获取前10个标签
		$result = $tagX->getTop(10);';
        echo '</pre>';
    }

    /**
     * 生成语言文件.
     */
    public function createLangPhpFile()
    {
        set_time_limit(0);
        // 判断文件夹路径是否存在
        if (!file_exists(LANG_PATH)) {
            mkdir(LANG_PATH, 0777);
        }
        $data = model('Lang')->where($map)->order('lang_id asc')->findAll();
        $fileName = LANG_PATH.'/langForLoadUpadte.php';
        // 权限处理
        $fp = fopen($fileName, 'w+');
        $fileData = "<?php\n";
        $fileData .= "return array(\n";
        foreach ($data as $val) {
            $val['zh-cn'] = htmlspecialchars($val['zh-cn'], ENT_QUOTES);
            $val['en'] = htmlspecialchars($val['en'], ENT_QUOTES);
            $val['zh-tw'] = htmlspecialchars($val['zh-tw'], ENT_QUOTES);
            $content[] = "'{$val['key']}-{$val['appname']}-{$val['filetype']}'=>array(0=>'{$val['zh-cn']}',1=>'{$val['en']}',2=>'{$val['zh-tw']}',)";
        }
        $fileData .= implode(",\n", $content);
        $fileData .= "\n);";
        fwrite($fp, $fileData);
        fclose($fp);
        unset($fileData);
        unset($content);
        @chmod($fileName, 0775);
    }

    /**
     * 获取生成的语言文件内容.
     */
    public function getzLang()
    {
        $a = include LANG_PATH.'/langForLoadUpadte.php';
        dump($a);
    }

    public function initLang()
    {
        model('Lang')->createCacheFile('public', 0);
        model('Lang')->createCacheFile('public', 1);
        model('Lang')->createCacheFile('channel', 0);
        model('Lang')->createCacheFile('channel', 1);
    }

    public function initLangPHP()
    {
        $lang = include CONF_PATH.'/lang/ask_zh-cn2.php';
        $sql = 'insert into sociax_lang (`key`,`appname`,`filetype`,`zh-cn`) VALUES ';
        foreach ($lang as $k => $v) {
            $k = trim($k);
            $v = trim($v);
            $sqlArr[] = " ('{$k}','ASK','0','{$v}') ";
        }
        $sql = $sql.implode(',', $sqlArr).';';
        D('')->query($sql);
        echo  model()->getError();
        echo '<br/>',$sql,'<br/>';
    }

    // public function initLangJS(){
    // 	$lang = include(CONF_PATH.'/lang/public_zh-cn.js');
    // 	$sql = "insert into sociax_lang (`key`,`appname`,`filetype`,`zh-cn`) VALUES ";
    // 	foreach($lang as $k=>$v){
    // 		$sqlArr[] =" ('{$k}','PUBLIC','1','{$v}') ";
    // 	}
    // 	$sql = $sql.implode(',', $sqlArr).';';
    // 	D('')->query($sql);
    // }

    public function editLang()
    {
        // $lang = include(CONF_PATH.'/lang/public_en.js-');
        // foreach($lang as $k=>$v){
        // 	$map = $save = array();
        // 	$map['filetype'] = 1;
        // 	$map['key'] = $k;
        // 	$save['en'] = $v;
        // 	D('')->table('sociax_lang')->where($map)->save($save);
        // }

        // $lang = include(CONF_PATH.'/lang/public_zh-cn.js-');
        // foreach($lang as $k=>$v){
        // 	$map = $save = array();
        // 	$map['filetype'] = 1;
        // 	$map['key'] = $k;
        // 	$save['zh-cn'] = $v;
        // 	D('')->table('sociax_lang')->where($map)->save($save);
        // }

        // $lang = include(CONF_PATH.'/lang/public_zh-tw.js');
        // foreach($lang as $k=>$v){
        // 	$map = $save = array();
        // 	$map['filetype'] = 1;
        // 	$map['key'] = $k;
        // 	$save['zh-tw'] = $v;
        // 	D('')->table('sociax_lang')->where($map)->save($save);
        // }

        $lang = include CONF_PATH.'/lang/ask_en.php';
        foreach ($lang as $k => $v) {
            $map = $save = array();
            $map['filetype'] = 0;
            $map['key'] = $k;
            $save['en'] = $v;
            D('')->table('sociax_lang')->where($map)->save($save);
        }

        $lang = include CONF_PATH.'/lang/ask_zh-tw.php';
        foreach ($lang as $k => $v) {
            $map = $save = array();
            $map['filetype'] = 0;
            $map['key'] = $k;
            $save['zh-tw'] = $v;
            D('')->table('sociax_lang')->where($map)->save($save);
        }
    }

    public function langEdit()
    {
        $data = include CONF_PATH.'/lang/tt.php';
        foreach ($data as $k => $v) {
            $key = trim($k);
            $value = trim($v);
            $map['key'] = $key;
            $save['en'] = $value;
            model('Lang')->where($map)->save($save);
        }
    }

    public function updateCategorySort()
    {
        $stable = t($_GET['t']);
        !empty($stable) && model('CategoryTree')->setTable($stable)->updateSort();
    }

    public function tt()
    {
        // $file = fopen(CONF_PATH.'/lang/t.php','r');
        // while(!feof($file)){
        // 	$d = fgets($file,'4096');
        // 	$data[] = trim($d)."',";
        // }
        // fclose($file);
        // //生成文件
        // $fileData = implode("\n",$data);
        // $fp = fopen(CONF_PATH.'/lang/tt.php','w+');
        // fwrite($fp, $fileData);
        // fclose($fp);
    }

    public function followTest()
    {
        model('Friend')->_getRelatedUserFromFriend(11860);
        model('Friend')->_getNewRelatedUser();
    }

    public function testSVn()
    {
        dump('CESHI');
    }

    public function testUserData()
    {
        model('UserData')->updateUserData();
    }

    public function addChannelData()
    {
        set_time_limit(0);
        $body = array(
                    '秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，秋天去哪里看落叶，',
                    '随手拍花朵，夏天森林公园行',
                    '这个焦点怎么没有乱。。。',
                    '春季一定要吃的家常菜 - 知识 - 这个菜炒出来卖相不好看，所以怎么拍的好看是个难题，点缀了红辣椒、葱丝和香菜叶之后总算能见人了。至于香椿和鸡蛋的比例，随个人喜欢，可以香椿多，也可以鸡蛋多，这个看个人啦',
                    '部发表于18世纪的法国传奇小说，种下了魔力的种子，从文字到影像穿越时空开花结果。从法国宫廷、美国校园到韩国贵族，再到中国老上海，历经数个电影版本 的演绎，萦绕在《危险关系》戏里戏外的故事，自然生长，如镜子般折射时代、国别、名利场，一次次赋予古老的原著新鲜的生命力。本周四，章子怡、',
                    '分享图片',
                );
        $attach = array(2352, 2351, 2350, 2349);
        $data['content'] = '';
        $type = 'postimage';
        $app = 'public';
        for ($i = 0; $i < 150; $i++) {
            $data['body'] = $body[rand(0, 5)];
            $data['attach_id'] = $attach[rand(0, 4)];
            $result = model('Feed')->put($this->uid, $app, $type, $data);
            $add['channel_category_id'] = 13;
            $add['feed_id'] = $result['feed_id'];
            D('channel')->add($add);
        }
    }

    // 刷新图片数据
    public function updateImgData()
    {
        set_time_limit(0);
        $data = D('channel')->findAll();
        foreach ($data as $value) {
            $feedInfo = model('Feed')->get($value['feed_id']);
            if ($feedInfo['type'] == 'postimage') {
                $feedData = unserialize($feedInfo['feed_data']);
                $imgAttachId = is_array($feedData['attach_id']) ? $feedData['attach_id'][0] : $feedData['attach_id'];
                $attach = model('Attach')->getAttachById($imgAttachId);
                $path = UPLOAD_PATH.'/'.$attach['save_path'].$attach['save_name'];
                $imageInfo = getimagesize($path);
                $up['height'] = intval(ceil(195 * $imageInfo[1] / $imageInfo[0]));
                $up['width'] = 195;
                D('channel')->where('feed_id='.$value['feed_id'])->save($up);
            }
        }
    }

    /**
     * 插入Ts2.8用户信息.
     */
    public function insertTsUser()
    {
        set_time_limit(0);
        // 获取插入用户数据
        $data = D('old_user')->field('email, password, sex, uname')->findAll();

        foreach ($data as $value) {
            $user['uname'] = $value['uname'];
            $salt = rand(11111, 99999);
            $user['login_salt'] = $salt;
            $user['login'] = $value['email'];
            $user['email'] = $value['email'];
            $user['password'] = md5($value['password'].$salt);
            $user['ctime'] = time();
            $user['first_letter'] = getFirstLetter($value['uname']);
            $user['sex'] = ($value['sex'] == 0) ? 1 : 2;
            $user['is_audit'] = 1;
            $user['is_active'] = 1;
            // 添加用户
            $result = model('User')->add($user);
            // 添加用户组
            model('UserGroupLink')->domoveUsergroup($result, 3);
        }
    }

    public function testpiny()
    {
        $unames = model('User')->field('uid,uname')->findAll();
        foreach ($unames as $u) {
            if (preg_match('/[\x7f-\xff]+/', $u['uname'])) {
                model('User')->setField('search_key', $u['uname'].' '.model('PinYin')->Pinyin($u['uname']), 'uid='.$u['uid']);
            } else {
                model('User')->setField('search_key', $u['uname'], 'uid='.$u['uid']);
            }
        }
    }

    public function testSort()
    {
        dump(111);
        model('CategoryTree')->setTable('area')->updateSort();
        dump(222);
    }

    public function testrm()
    {
        dump(111);
        model('CategoryTree')->setTable('area')->rmTreeCategory();
        dump(222);
    }

    public function testfeed()
    {
        $feedInfo = model('Feed')->get(5210);
        dump($feedInfo);
    }

    public function updateChannelUid()
    {
        set_time_limit(0);
        $channels = D('Channel', 'channel')->findAll();
        foreach ($channels as $value) {
            $feedInfo = model('Feed')->get($value['feed_id']);
            $data['uid'] = $feedInfo['uid'];
            D('channel')->where('feed_channel_link_id='.$value['feed_channel_link_id'])->save($data);
        }
    }

    public function testChannel()
    {
        $channels = D('ChannelApi', 'channel')->getAllChannel();
        dump($channels);
    }

    public function testnav()
    {
        $topNav = model('Navi')->getTopNav();
        dump($topNav);
        dump($GLOBALS['ts']['site_top_nav']);
        $bottomNav = model('Navi')->getBottomNav();
        $this->assign('site_top_nav', $topNav);
        $this->assign('site_bottom_nav', $bottomNav);
    }

    public function testTime()
    {
        dump(time());
        dump(friendlyDate(time()));
    }

    public function updataStorey()
    {
        $map['data'] = array('neq', 'N;');
        $commentlist = D('comment')->where($map)->findAll();
        foreach ($commentlist as $v) {
            $data = unserialize($v['data']);
            if ($data['storey']) {
                D('comment')->where('comment_id='.$v['comment_id'])->setField('storey', $data['storey']);
            }
        }
    }

    /**
     * Google翻译API.
     *
     * @return [type] [description]
     */
    private function translatorGoogleAPI($text, $tl = 'zh-CN', $sl = 'auto', $ie = 'UTF-8')
    {
        $ch = curl_init('http://translate.google.cn/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "&hl=zh-CN&sl={$sl}&ie={$ie}&tl={$tl}&text=".urlencode($text));
        $html = curl_exec($ch);
        preg_match('#<span id=result_box class="short_text">(.*?)</span></div>#', $html, $doc);

        return strip_tags($doc['1'], '<br>');
    }

    public function translateLang()
    {
        set_time_limit(0);
        $sql = 'SELECT `lang_id` ,`zh-cn` FROM `'.C('DB_PREFIX')."lang` WHERE  appname = 'PUBLIC' AND `en` = '==**==' AND `zh-tw` LIKE '%ts3/apps/%' ORDER BY lang_id ASC;";
        $data = D()->query($sql);
        foreach ($data as $value) {
            $en = $this->translatorGoogleAPI($value['zh-cn'], 'en');
            $tw = $this->translatorGoogleAPI($value['zh-cn'], 'zh-TW');
            $insert_sql = 'UPDATE `'.C('DB_PREFIX')."lang` SET `en` = '".$en."', `zh-tw` = '".$tw."' WHERE `lang_id` = '".$value['lang_id']."' LIMIT 1;";
            // dump($insert_sql);
            D()->execute($insert_sql);
            // dump($en);
            // dump($tw);
        }
    }

    public function upUserData()
    {
        set_time_limit(0);
        $p = isset($_GET['p']) ? intval($_GET['p']) : 1;
        $count = 10;
        $limit = ($p - 1) * $count.', '.$count;
        $list = model('User')->limit($limit)->getAsFieldArray('uid');

        if (empty($list)) {
            dump('OK');
            exit;
        } else {
            foreach ($list as $uid) {
                if (empty($uid)) {
                    continue;
                }

                // 总分享数目和未假删除的分享数目
                $sql = 'SELECT count(feed_id) as total, SUM(is_del) as delSum FROM '.C('DB_PREFIX').'feed where uid ='.$uid;
                $vo = M()->query($sql);
                $res['feed_count'] = intval($vo[0]['total']);
                $res['weibo_count'] = $res['feed_count'] - intval($vo[0]['delSum']);
                // 收藏数目
                $sql = 'SELECT count(collection_id) as total FROM '.C('DB_PREFIX').'collection where uid ='.$uid;
                $vo = M()->query($sql);
                $res['favorite_count'] = intval($vo[0]['total']);

                // 关注数目
                $sql = 'SELECT count(follow_id) as total FROM '.C('DB_PREFIX').'user_follow where uid ='.$uid;
                $vo = M()->query($sql);
                $res['following_count'] = intval($vo[0]['total']);

                // 粉丝数目
                $sql = 'SELECT count(follow_id) as total FROM '.C('DB_PREFIX').'user_follow where fid ='.$uid;
                $vo = M()->query($sql);
                $res['follower_count'] = intval($vo[0]['total']);

                $map['uid'] = $uid;
                $map['key'] = array('in', array('feed_count', 'weibo_count', 'favorite_count', 'following_count', 'follower_count'));
                M('UserData')->where($map)->delete();

                $sql = 'INSERT INTO '.C('DB_PREFIX').'user_data (`uid`,`key`,`value`) values';
                $data['uid'] = $uid;
                $k = 0;
                foreach ($res as $key => $val) {
                    if ($k == 0) {
                        $sql .= " ($uid,'$key','$val')";
                    } else {
                        $sql .= " , ($uid,'$key','$val')";
                    }
                    $k++;
                }
                $rr = M()->execute($sql);
                dump($sql);
                $sql = '';
                // 清掉该用户的缓存
                model('Cache')->rm('UserData_'.$uid);

                if ($rr) {
                    echo $uid.' -- done -- <br />';
                } else {
                    echo  $uid.' -- error -- <br />';
                }
            }

            $p += 1;
            echo '<script>window.location.href="'.U('public/Test/upUserData', array('p' => $p)).'";</script>';
        }
    }

    /**
     * 转移2.8头像为3.0头像地址
     */
    public function upFacePath()
    {
        set_time_limit(0);
        $p = isset($_GET['p']) ? intval($_GET['p']) : 1;
        $count = 1000;
        $limit = ($p - 1) * $count.', '.$count;
        $list = model('User')->limit($limit)->getAsFieldArray('uid');

        if (empty($list)) {
            dump('OK');
            exit;
        } else {
            foreach ($list as $uid) {
                if (empty($uid)) {
                    continue;
                }

                $oldPath = UPLOAD_PATH.'/avatar/'.$uid.'/big.jpg';
                if (!file_exists($oldPath)) {
                    continue;
                }

                // $uid = 10045;
                $path = UPLOAD_PATH.'/avatar'.model('Avatar')->convertUidToPath($uid);
                $this->_createFolder($path);

                $res = copy($oldPath, $path.'/original.jpg');
                echo $oldPath.'  =>  '.$path.'/original.jpg'.'  '.$res.'<br/>';
            }

            $p += 1;
            echo '<script>window.location.href="'.U('public/Test/upFacePath', array('p' => $p)).'";</script>';
        }
    }

    /**
     * 创建多级文件目录.
     *
     * @param string $path 路径名称
     */
    private function _createFolder($path)
    {
        if (!is_dir($path)) {
            $this->_createFolder(dirname($path));
            mkdir($path, 0777, true);
        }
    }

    /**
     * 生成后台菜单配置文件.
     *
     * @return [type] void
     */
    public function createSystemConfigPhpFile()
    {
        set_time_limit(0);
        // 判断文件夹路径是否存在
        if (!file_exists(LANG_PATH)) {
            mkdir(LANG_PATH, 0777);
        }
        $data = D('system_config')->findAll();
        // foreach($data as &$val) {
        // 	$val['value'] = unserialize($val['value']);
        // 	$content[] = "'{$val['key']}'=>{$val['value']}";
        // }
        // dump($data);exit;
        $fileName = LANG_PATH.'/system_config.php';
        // 权限处理
        $fp = fopen($fileName, 'w+');
        $fileData = "<?php\n";
        $fileData .= "return array(\n";
        foreach ($data as $val) {
            $val['value'] = unserialize($val['value']);
            $arr = 'array(';
            if ($val['value']['key']) {
                $arr .= '\'key\'=>array(';
                foreach ($val['value']['key'] as $k0 => $v0) {
                    $arr .= '\''.$k0.'\'=>\''.htmlspecialchars($v0, ENT_QUOTES).'\',';
                }
                $arr .= '),';
            }
            if ($val['value']['key_name']) {
                $arr .= '\'key_name\'=>array(';
                foreach ($val['value']['key_name'] as $k1 => $v1) {
                    $arr .= '\''.$k1.'\'=>\''.htmlspecialchars($v1, ENT_QUOTES).'\',';
                }
                $arr .= '),';
            }
            if ($val['value']['key_hidden']) {
                $arr .= '\'key_hidden\'=>array(';
                foreach ($val['value']['key_hidden'] as $k2 => $v2) {
                    $arr .= '\''.$k2.'\'=>\''.htmlspecialchars($v2, ENT_QUOTES).'\',';
                }
                $arr .= '),';
            }
            if ($val['value']['key_type']) {
                $arr .= '\'key_type\'=>array(';
                foreach ($val['value']['key_type'] as $k3 => $v3) {
                    $arr .= '\''.$k3.'\'=>\''.htmlspecialchars($v3, ENT_QUOTES).'\',';
                }
                $arr .= '),';
            }
            if ($val['value']['key_default']) {
                $arr .= '\'key_default\'=>array(';
                foreach ($val['value']['key_default'] as $k4 => $v4) {
                    $arr .= '\''.$k4.'\'=>\''.htmlspecialchars($v4, ENT_QUOTES).'\',';
                }
                $arr .= '),';
            }
            if ($val['value']['key_tishi']) {
                $arr .= '\'key_tishi\'=>array(';
                foreach ($val['value']['key_tishi'] as $k5 => $v5) {
                    $arr .= '\''.$k5.'\'=>\''.htmlspecialchars($v5, ENT_QUOTES).'\',';
                }
                $arr .= '),';
            }
            if ($val['value']['key_javascript']) {
                $arr .= '\'key_javascript\'=>array(';
                foreach ($val['value']['key_javascript'] as $k6 => $v6) {
                    $arr .= '\''.$k6.'\'=>\''.htmlspecialchars($v6, ENT_QUOTES).'\',';
                }
                $arr .= ')';
            }
            $arr .= ')';
            $content[] = "'{$val['key']}-{$val['list']}'=>".$arr;
        }
        $fileData .= implode(",\n", $content);
        $fileData .= "\n);";
        fwrite($fp, $fileData);
        fclose($fp);
        unset($fileData);
        unset($content);
        @chmod($fileName, 0775);
    }

    /**
     * 获得后台菜单配置文件.
     *
     * @return [type] void
     */
    public function getzLang1()
    {
        $a = include LANG_PATH.'/langForLoadUpadte.php';
        dump($a);
    }

    public function test_ffmpeg()
    {
        $ffmpegpath = '/usr/local/bin/ffmpeg/ffmpeg';
        $input = SITE_PATH.'/data/video/2014/0526/5382f24121cb6.mp4';
        // // $output = SITE_PATH.'/data/video/2014/0422/535662ba3394a0.mp4';
        // dump($input);
        // dump($output);
        // $ffmpegpath = SITE_PATH.'/ffmpeg.exe';
        set_time_limit(0);
        $command = "$ffmpegpath -i ".$input." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//";
        // $command = 'E:\xampp\htdocs\t4\ffmpeg.exe -i E:\xampp\htdocs\t4\data\video\2014\0526\source\5382eba8561cb.mp4';
        // $command = "/usr/local/bin/ffmpeg/ffmpeg -y -i /home/wwwroot/dev.thinksns.com/t4/data/video/2014/0523/source/537ec589ce479.mp4 -vcodec libx264 /home/wwwroot/dev.thinksns.com/t4/data/video/2014/0523/537ec589ce479.mp4";
        dump($command);
        dump(exec($command));
    }

    public function credit_rules()
    {
        dump(model('Credit')->getCreditRules());
    }

    public function info()
    {
        $c = model('Credit')->setUserCredit($this->mid, 'user_login', '1', array('user' => '<a href="http://www.baidu.com">呵呵</a>', 'content' => '<a href="http://www.google.com">hh</a>'));
    }


    public function test_code(){
        $register_code = t($_GET['register_code']); // 注册邀请码判断
        if($register_code){
            //判断邀请码的有效性和正确性
            $map = array();
            $map['code'] = $register_code;
            $codeInfo = model('RegisterCode')->where($map)->find();
            if($codeInfo){
                if($codeInfo['is_audit']==0||$codeInfo['is_del']==1){
                    dump('register_code not error');
                }else{
                    if($codeInfo['id']){
                        $map = array();
                        $map['code_id'] = $codeInfo['id'];
                        $map['uid'] = 999;
                        $map['bind_time'] = time();
                        model('RegisterCode')->codeBind($map);
                    }
                    dump('register_code OK');
                }
            }else{
                dump('register_code not fond');
            }
        }else{
            dump('not register_code');
        }
    }


    public function test_mo(){
        $num = 1011;
        if($num%1011==0){
            echo '获取188元';
        }elseif($num%101==0){
            echo '获取18.8元';
        }else{
            echo '获取0.88元';
        }

    }

    public function test_luck(){
        //清空用户表
        $map = array();
        $map['all_income'] = array('gt',0);
        $save = array();
        $save['all_income'] = 0;
        $save['accountmny'] = 0;
        $save['ali_account'] = null;
        $res = model('User')->where($map)->save($save);
    }


    public function test_luck_by_uid(){
        //清空用户表
        $map = array();
        $map['uid'] = array('in','17903,17806');
        $save = array();
        $save['all_income'] = 0;
        $save['accountmny'] = 0;
        $save['ali_account'] = null;
        $res = model('User')->where($map)->save($save);
    }

    public function test_income_by_uid(){
        $res = model('IncomeMny')->where('uid in(17903,17806)')->delete();
        echo model('IncomeMny')->getlastsql();
    }

    public function test_income(){
        $res = model('IncomeMny')->where('1=1')->delete();
        echo model('IncomeMny')->getlastsql();
    }

    public function test_draw(){
        $map = array();
        $map['id'] = 1;
        $map['hasNum'] = 0;
        $map['useMoney'] = 0;
        $res = model('LuckDraw')->save($map);
        echo $res;
    }

    public function upload(){
        $this->display();
    }


    public function test_post_img(){
        $attach_map = array();
        $attach_map['attach_id'] = array('IN', '228058,228057');
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
        $res = $cdn->doAction();
        echo $res;
    }


    /**
     *
     * 安卓APK下载
     *
     */
    public function down_apk()
    {
        $url="https://v1.edaocha.net/download/package/aodou_1.0.1.apk";
        //文件的类型
        header('Content-type: application/vnd.andriod');
        //下载显示的名字
        header('Content-Disposition: attachment; filename="aodou.apk"');
        readfile("$url");
        exit();
    }


    public function test_ss(){
        $str = '1.0.1';
        $str1 = '1.0.2';
        $res = str_replace('.', '', $str);
        $res1 = str_replace('.', '', $str1);
        echo $res."/////////////".$res1;
        if($res<$res1){
            echo 'OK';
        }else{
            echo 'FALSE';
        }
    }


    public function update_version(){
        $map = array();
        $map['id'] = 1;
        $map['version'] = '1.0.2';
        $map['update_type'] = '0';
        $map['edit_time'] = time();
        $map['edit_uid'] = $GLOBALS['ts']['mid'];
        $res = D('version')->save($map);
        echo $res;
    }


    public function test_d(){

        $ids="25.23.24";
        dump($ids);
        $ids = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$ids);
        dump($ids);
        //$topicids = explode("," ,$tids);


        //$ids = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$ids);
    }


    public function test_permission_node(){
        $map = array();
        //$map['id'] = array('in','436,437,438,439,440,441,442,443,444,445,446,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,');
        $map['id'] = array('between',array('436','546'));
        $list = D('permission_node')->where($map)->select();
        foreach($list as $v){
            $save_map = array();
            $save_map['id'] = $v['id'];
            $save_map['rule'] = "admin_".$v['rule'];
            D('permission_node')->save($save_map);
        }
    }



    public function test_permission_node_1(){
        $list = D('permission_node')->select();
        foreach($list as $v){
            $sql = "UPDATE `cy_permission_node` SET sort=".$v['id']."  WHERE id=".$v['id'];
            D('permission_node')->query($sql);
            dump(D('permission_node')->getlastsql());
        }
    }


    public function test_weight(){
        $map = array();
        $map['a.uid'] = 25;
        $tablePrefix = C('DB_PREFIX');
        $user_anonymous = D('user_anonymous')
            ->field('b.nickname,c.icon')
            ->where($map)
            ->table("{$tablePrefix}user_anonymous AS a LEFT JOIN {$tablePrefix}anonymous AS b ON b.id=a.anonymous_id LEFT JOIN {$tablePrefix}anonymous AS c ON c.id=b.pid")
            ->find();
        echo D('user_anonymous')->getlastsql();
        dump($user_anonymous);
    }


    public function test_time(){
        $now = strtotime("-1 Minute");
        $bd_time = '1508681314';
        if($now>$bd_time){
            echo 'ok'."<br/>";
        }else{
            echo 'no'."<br/>";
        }
        echo '当前时间：'.date('Y-m-d H:i:s',time())."<br/>";
        echo '减去36小时后的时间'.date('Y-m-d H:i:s',$now)."<br/>";
        echo '用户绑定匿名名称的时间：'.date('Y-m-d H:i:s',$bd_time)."<br/>";
    }


    public function add_anonymous(){
        set_time_limit(0);//无超时
        //$names = array('张清','杨志','徐宁','索超','戴宗','刘唐','李逵','史进','穆弘','雷横','李俊','阮小二','张横','阮小五','张顺','阮小七','杨雄','石秀','解珍','解宝','燕青','朱武','黄信','孙立','宣赞','郝思文','韩滔','彭玘','单廷圭','魏定国','萧让','裴宣','欧鹏','邓飞','燕顺','杨林','凌振','蒋敬','吕方','郭盛','安道全','皇甫端','王英','扈三娘','鲍旭','樊瑞','孔明','孔亮','项充','李衮','金大坚','马麟','童威','童猛','孟康','侯健','陈达','杨春','郑天寿','陶宗旺','宋清','乐和','龚旺','丁得孙','穆春','曹正','宋万','杜迁','薛永','李忠','周通','汤隆','杜兴','邹渊','邹润','朱贵','朱富','施恩','蔡福','蔡庆','李立','李云','焦挺','石勇','孙新','顾大嫂','张青','孙二娘','王定六','郁保四','白胜','时迁','段景住','唐僧','孙悟空','猪八戒','沙僧','小白龙','金角大王','银角大王','红孩儿','牛魔王','白骨精','至尊宝','紫霞仙子','贾宝玉','林黛玉','薛宝钗','刘备','刘禅','关羽','张飞','赵云','诸葛亮','徐遮','法正','马良','黄忠','马超','魏延','姜维','曹操','夏侯惇','张辽','徐晃','张郃','于禁','乐进','典韦','许褚','荀彧','荀攸','贾诩','郭嘉','程昱','司马懿','司马昭','司马师','孙权','孙策','孙坚','周瑜','鲁肃','吕蒙','陆逊','甘宁','太史慈','黄盖','张昭','诸葛瑾','刘协','董卓','吕布','貂蝉','公孙瓒','袁术','袁绍','刘表','刘璋');
//        $names = array(
//            '超人','绿巨人','石头人','钢力士','野蛮龙','镭射眼','惊奇女士','原子队长','蜘蛛侠','绿箭侠','苍穹射手','鹰眼','钢铁侠','夜枭','蝙蝠侠','闪电侠','霹雳火','耶路撒冷蜘蛛','火星猎人','风暴女','再生侠','神盾指挥官','死侍','康斯坦丁','惩罚者','地狱男爵','猫女','雷神','金刚狼','黑武士','宙斯','赫拉','波塞冬','哈得斯','德墨忒耳','阿瑞斯','雅典娜','阿波罗','太阳神','阿佛洛狄忒','赫尔墨斯','阿耳忒弥斯','赫淮斯托斯','加菲猫','流氓兔','柯南','蜡笔小新','樱木花道','卡卡西','蓝精灵','小熊维尼','樱桃小丸子','米老鼠','唐老鸭','龙猫','麦兜','机器猫','一休哥','阿童木','皮卡丘','阿拉蕾','史努比','皮诺曹','兔八哥','白雪公主','小樱','史瑞克','大力水手','咸蛋超人','星矢','紫龙','冰河','忍者神龟','芭比','高达','大脸猫','大头儿子','小头爸爸','海尔兄弟','黑猫警长','葫芦娃','喜羊羊','灰太狼','猩红收割者','战争女神','祖安狂人','扭曲树精','战争之王','钢铁大使','光辉女郎','众星之子','琴瑟仙女','探险家','末日使者','荒漠屠夫','古拉加斯','虚空行者','风暴之怒','迅捷斥候','发条魔灵','德玛西亚皇子','金属大师','雪人骑士','海洋之灾','麦林炮手','刀锋意志','嗜血猎手','赏金猎人','英勇投弹手','复仇焰魂','暗影之拳','天启者','盲僧','狂暴之心','德玛西亚之力','寒冰射手','蛮族之王','宝石骑士','邪恶小法师','武器大师','暗夜猎手','堕落天使','虚空先知','时光守护者','机械公敌','诅咒巨魔','策士统领','魔蛇之拥','黑暗之女','皮城女警','寡妇制造者','审判天使','虚空恐惧','狂战士','哨兵之殇','蒸汽机器人','大发明家','卡牌大师','熔岩巨兽','沙漠死神','深渊巨口','无极剑圣','德邦总管','狂野女猎手','牛头酋长','披甲龙龟','首领之傲','暮光之眼','流浪法师','冰晶凤凰','不祥之刃','野兽之灵','亡灵勇士','恶魔小丑','诡术妖姬','永恒梦魇','复仇之魂','众神之王','魅惑魔女','变体精灵','水晶室女','流浪剑客','娜迦海妖','撼地神牛','隐形刺客','秀逗魔导师','德鲁依','月之骑士','矮人狙击手','巨魔战将','暗影萨满','刚背兽','熊猫酒仙','半人马酋长','龙骑士','敌法师','黑暗游侠','全能骑士','沉默术士','树精卫士','光之守卫','熊战士','食人魔法师','幻影长矛手','先知','山岭巨人','月之女祭司','炼金术士','圣堂刺客','神灵武士','风暴之灵','仙女龙','发条地精','蛇发女妖','暗夜魔王','地穴刺客','鱼人守卫','虚空假面','冥界亚龙','复仇电魂','死亡先知','剧毒术士','半人猛犸','死灵飞龙','混沌骑士','育母蜘蛛','幻影刺客','遗忘法师','潮汐猎人','裂魂人','影魔','沙王','斧王','黑曜毁灭者','黑暗贤者','草剃京','二阶堂红丸','大门五郎','特里·伯加德','安迪·伯加德','东丈','坂崎良','坂崎琢磨','哈迪伦','拉尔夫','克拉克','镇元斋','坂崎百合','不知火舞','哈维','洛奇·古洛巴','金家潘','蔡宝奇'
//        );

//        $names = array(
//            '景天','飞蓬','龙阳','唐雪见','夕瑶','龙葵','徐长卿','林业平','顾留芳','紫萱','邪剑仙','重楼','花楹','许茂山','云霆','万玉枝','水碧','溪风','清微','范明','齐天磊','李玉湖','袁不屈','杜冰雁','昌平公主','沙平威','柯世昭','何以琛','赵默笙','路远风','何以玫','萧筱','向恒','顾行红','陶忆静','文敏','佟心樱','赵清源','裴芳梅','美婷','白子画','花千骨','夏紫熏','杀阡陌','单春秋','笙箫默','霓漫天','落十一','糖宝','马尔泰·若曦','马尔泰·若兰','郭络罗·明慧','玉檀','司音','白浅','素素','白凤九','东华帝君','墨渊','夜华','折颜','白真','离镜','玄女','素锦','阿离','织越','央措','桑籍','连宋','少辛','迷谷','姬无命','郭芙蓉','赛貂蝉','佟湘玉','祝无双','姬无病','杨慧兰','郭巨侠','白展堂','李大嘴','包大仁','莫小贝','吕秀才','小结巴','宁采臣','十二少','十三妹','步惊云','聂风','阮玲玉','宋子豪','白素贞','许仙','小青','法海','周星星','刘月娥','山鸡','陈浩南','大天二','肥猫','霸王花','南海十三郎','陈真','周淮安','陈家驹','叶问','吴国豪','凌凌漆','苏丽珍','如花','唐伯虎','金镶玉','邱莫言','阿郎','聂小倩','黄飞鸿','陈永仁','小马哥'
//        );

//        $names = array(
//            '李秋水','无崖子','天山童姥','段誉','萧峰','枯荣大师','丁春秋','段延庆','慕容复','苏星河','神山上人','九翼道人','段正淳','叶二娘','岳老三','云中鹤','摘星子','全冠清','阿朱','阿紫','阿碧','木婉清','王语嫣','张无忌','殷素素','谢逊','张三丰','燕南天','西门吹雪','燕十三','夜帝','李寻欢','楚留香','陆小凤','江小鱼','萧十一郎','阿飞','花无缺','方世玉','周芷若','胡斐','血刀老祖','郭靖','黄蓉','洪七公','一灯大师','韦小宝','令狐冲','任我行','风清扬','岳不群','任莹莹','杨过','欧阳锋梦琪','忆柳','之桃','慕青','问兰','尔岚','元香','初夏','沛菡','傲珊','曼文','乐菱','痴珊','恨玉','惜文','香寒','新柔','语蓉','海安','夜蓉','涵柏','水桃','醉蓝','春儿','语琴','从彤','傲晴','语兰','又菱','碧彤','元霜','怜梦','紫寒','妙彤','曼易','南莲','紫翠','雨寒','易烟','如萱','若南','寻真','晓亦','向珊','慕灵','以蕊','寻雁','映易','雪柳','孤岚','笑霜','海云','凝天','沛珊','寒云','冰旋','宛儿','绿真','盼儿','晓霜','碧凡','夏菡','曼香','若烟','半梦','雅绿','冰蓝','灵槐','平安','书翠','翠风','香巧','代云','梦曼','幼翠','友巧','听寒','梦柏','醉易','访旋','亦玉','凌萱','访卉','怀亦','笑蓝','春翠','靖柏','夜蕾','冰夏','梦松','书雪','乐枫','念薇','靖雁','寻春','恨山','从寒','忆香','觅波','静曼','凡旋','以亦','念露','芷蕾','千兰','新波','代真','新蕾','雁玉','冷卉','紫山','千琴','恨天','傲芙','盼山','怀蝶','冰兰','山柏','翠萱','恨松','问旋','从南','白易','问筠','如霜','半芹','丹珍','冰彤','亦寒','寒雁','怜云','寻文','乐丹','翠柔','谷山','之瑶','冰露','尔珍','谷雪','乐萱','涵菡','海莲','傲蕾','青槐','冬儿','易梦','惜雪','宛海','之柔','夏青','亦瑶','妙菡','春竹','痴梦','紫蓝','晓巧','幻柏','元风','冰枫','访蕊','南春','芷蕊','凡蕾','凡柔','安蕾','天荷','书兰','雅琴','书瑶','春雁','从安','夏槐','念芹','怀萍','代曼','幻珊','谷丝','秋翠','白晴','海露','代荷','含玉','书蕾','听白','访琴','灵雁','秋春','雪青','乐瑶','含烟','涵双','平蝶','雅蕊','傲之','灵薇','绿春','含蕾','从梦','从蓉','初丹','听兰','听蓉','语芙','夏彤','凌瑶','忆翠','幻灵','怜菡','紫南','依珊','妙竹','访烟','怜蕾','映寒','冰萍','惜霜','凌香','雁卉','迎梦','元柏','代萱','紫真','千青','凌寒','紫安','寒安','怀蕊','秋荷','涵雁','以山','凡梅','盼曼','翠彤','谷冬','新巧','冷安','千萍','冰烟','雅阳','友绿','南松','诗云','飞风','寄灵','书芹','幼蓉','以蓝','笑寒','忆寒','秋烟','芷巧','水香','映之','醉波','幻莲','夜山','芷卉','向彤','小玉','幼南','凡梦','尔曼','念波','迎松','青寒','笑天','涵蕾','碧菡','映秋','盼烟','忆山','以寒','寒香','小凡','代亦','梦露','映波','友蕊','寄凡','雁枫','水绿','曼荷','笑珊','寒珊','谷南','慕儿','夏岚','友儿','小萱','紫青','妙菱','冬寒','曼柔','语蝶','青筠','夜安','觅海','问安','晓槐','雅山','访云','翠容'
//        );

//        $names = array(
//            '炼气','开光','胎息','辟谷','金丹','合体','筑基','旋照','融合','心动','灵寂','元婴','出窍','分神','度劫','大乘','空冥','寂灭','大成','渡劫','散仙','天仙','金仙','大罗金仙','九天玄仙','罗天上仙','极仙','绝仙','仙君','仙帝','神人','灵神','天神','神王','神皇','天尊','主宰','闻道期','开光期','灵智期','消融期','神动期','元婴期','出窍期','灵虚期','玄灵期','渡劫成仙','哈士奇','藏獒','贵宾','松狮','牧羊犬','吉娃娃','秋田犬','博美','柴犬','大丹','斗牛犬','萨摩耶','八哥犬','金毛','雪橇犬','依米花','香雪兰','宫人草','紫苑','矢车菊','爱尔兰风铃草','白英','风信子','白烛葵','仙客来','雏菊','枯萎的叶子','勿忘我','天竺葵','紫阳花','爱丽丝','山谷的百合花','玫瑰花蕾','金鱼草','紫罗兰','鸢尾','青兰','睡莲','海紫苑','嘉德利亚兰','吊钟花','樱花','桔梗','天堂鸟','三色堇','迷迭香','欧石楠','文心兰','虞美人','秋海棠','晚香玉','夹竹桃','紫馨兰','梦莱菊','出云花','绕心玫','星辰花'
//        );
//
//        foreach($names as $v){
//            $data = array();
//            $data['nickname'] = $v;
//            $data['pid'] = 5;
//            $result = D('anonymous')->add($data);
//            echo $result."<br/>";
//        }
    }


    public function test_dz(){
        $res = model('UserData')->updateKey('unread_digg', 1, true, '25');
        echo $res;
    }


    public function KeywordStatistic(){
        $res = model('KeywordStatistic')->clean_month();
        echo $res;
    }

}
