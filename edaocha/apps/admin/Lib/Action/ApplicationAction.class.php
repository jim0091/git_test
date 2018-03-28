<?php

/* # include base class */
import(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
use Ts\Models as Model;

/**
 * APP 客户端设置.
 *
 * @author Medz Seven <lovevipdsw@vip.qq.com>
 **/
class ApplicationAction extends AdministratorAction
{
    /**
     * 轮播列表设置类型.
     *
     * @var string
     **/
    protected $type = array(
        'false'   => '仅展示',
        'url'     => 'URL地址',
        'weiba'   => '微吧',
        'post'    => '帖子',
        'weibo'   => '微博',
        'topic'   => '话题',
        'channel' => '频道',
        'user'    => '用户',
        'information' => '资讯',
        'event' => '活动'
    );

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 轮播列表.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        $this->pageKeyList = array('title', 'image', 'type', 'data', 'doAction');
        array_push($this->pageTab, array(
            'title'   => '轮播列表',
            'tabHash' => 'index',
            'url'     => U('admin/Application/index'),
        ));
        array_push($this->pageTab, array(
            'title'   => '添加轮播',
            'tabHash' => 'addSlide',
            'url'     => U('admin/Application/addSlide'),
        ));

        $list = D('application_slide')->findPage(20);

        foreach ($list['data'] as $key => $value) {
            // # 参数
            $aid = $value['image'];
            $id = $value['id'];

            $list['data'][$key]['type'] = $this->type[$value['type']];

            // # 添加图片
            $value = '<a href="%s" target="_blank"><img src="%s" width="300px" height="140px"></a>';
            $value = sprintf($value, getImageUrlByAttachId($aid), getImageUrlByAttachId($aid, 300, 140));
            $list['data'][$key]['image'] = $value;

            // # 添加操作按钮
            $value = '[<a href="%s">编辑</a>]&nbsp;-&nbsp;[<a href="%s">删除</a>]';
            $value = sprintf($value, U('admin/Application/addSlide', array('id' => $id, 'tabHash' => 'addSlide')), U('admin/Application/delSlide', array('id' => $id)));
            $list['data'][$key]['doAction'] = $value;
        }

        $this->allSelected = false;

        $this->displayList($list);
    }

    /**
     * 添加|修改 幻灯.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function addSlide()
    {
        $this->pageKeyList = array('title', 'image', 'type', 'data');
        $this->notEmpty = array('title', 'image', 'type');
        array_push($this->pageTab, array(
            'title'   => '轮播列表',
            'tabHash' => 'index',
            'url'     => U('admin/Application/index'),
        ));
        array_push($this->pageTab, array(
            'title'   => '添加轮播',
            'tabHash' => 'addSlide',
            'url'     => U('admin/Application/addSlide'),
        ));

        $this->opt['type'] = $this->type;

        $this->savePostUrl = U('admin/Application/doSlide', array('id' => intval($_GET['id'])));

        $data = array();

        if (isset($_GET['id']) and intval($_GET['id'])) {
            $data = D('application_slide')->where('`id` = '.intval($_GET['id']))->find();
        }

        $this->displayConfig($data);
    }

    /**
     * 添加|修改幻灯数据.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function doSlide()
    {
        list($id, $title, $image, $type, $data) = array($_GET['id'], $_POST['title'], $_POST['image'], $_POST['type'], $_POST['data']);
        list($id, $title, $image, $type, $data) = array(intval($id), t($title), intval($image), t($type), $data);

        if (!in_array($type, array('false', 'url', 'weiba', 'post', 'weibo', 'topic', 'channel', 'user', 'information','event'))) {
            $this->error('跳转类型不正确');
        } elseif (!$title) {
            $this->error('标题不能为空');
        } elseif (!$image) {
            $this->error('必须上传轮播图片');
        } elseif (in_array($type, array('url', 'weiba', 'post', 'weibo', 'topic', 'channel', 'user', 'information','event') and !$data)) {
            $this->error('您设置的跳转类型必须设置类型参数');
        }

        $data = array(
            'title' => $title,
            'image' => $image,
            'type'  => $type,
            'data'  => $data,
        );

        if ($id and D('application_slide')->where('`id` = '.$id)->field('id')->count()) {
            D('application_slide')->where('`id` = '.$id)->save($data);
            S('api_discover_system', null);
            //记录操作日志
            LogRecord('admin_system', 'doSaveSlide', array('name' => $id, 'k1' => '修改发现轮播'), true);
            $this->success('修改成功');
        }
        D('application_slide')->data($data)->add() or $this->error('添加失败');
        //记录操作日志
        LogRecord('admin_system', 'doAddSlide', array('name' => '', 'k1' => '新增发现轮播'), true);
        $this->assign('jumpUrl', U('admin/Application/index'));
        $this->success('添加成功');
    }

    /**
     * 删除幻灯.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function delSlide()
    {
        $id = intval($_GET['id']);
        D('application_slide')->where('`id` = '.$id)->delete();
        S('api_discover_system', null);
        //记录操作日志
        LogRecord('admin_system', 'delSlide', array('name' => $id, 'k1' => '删除发现轮播'), true);
        $this->success('删除成功');
    }

    /*======================== Socket setting start ===========================*/

    /**
     * Socket 服务器设置.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function socket()
    {
        $this->pageKeyList = array('socketaddres');
        array_push($this->pageTab, array(
            'title' => 'Socket服务器地址设置',
            'hash'  => 'socket',
            'url'   => U('admin/Application/socket'),
        ));
        $this->displayConfig();
    }

    /*======================== Socket setting end   ===========================*/

    /*================= Application about setting start ========================*/

    /**
     * 客户端About页面设置.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function about()
    {
        $this->pageKeyList = array('content');
        array_push($this->pageTab, array(
            'title' => '关于我们设置',
            'hash'  => 'about',
            'url'   => U('admin/Application/about'),
        ));
        $this->displayConfig();
    }

    /**
     * 客户端用户协议页面设置.
     *
     * @author bs
     **/
    public function agreement()
    {
        $this->pageKeyList = array('content');
        array_push($this->pageTab, array(
            'title' => '用户协议设置',
            'hash'  => 'agreement',
            'url'   => U('admin/Application/agreement'),
        ));
        $this->displayConfig();
    }
    /**
     * 客户端用户行为规范页面设置.
     *
     * @author bs
     **/
    public function privacy()
    {
        $this->pageKeyList = array('content');
        array_push($this->pageTab, array(
            'title' => '行为规范设置',
            'hash'  => 'privacy',
            'url'   => U('admin/Application/privacy'),
        ));
        $this->displayConfig();
    }

    /*================= Application about setting end   ========================*/

    /*================ Application feedback setting start ======================*/

    /**
     * APP反馈管理.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function feedback()
    {
        $this->pageKeyList = array('user', 'content', 'time', 'doaction');
        array_push($this->pageTab, array(
            'title' => 'APP反馈管理',
            'hash'  => 'feedback',
            'url'   => U('admin/Application/feedback'),
        ));
        $this->allSelected = false;

        /* # 每页显示的条数 */
        $number = 20;

        /* # 反馈类型，app反馈为1 */
        $type = 1;

        /* # 是否按照时间正序排列 */
        $asc = false;

        $list = model('Feedback')->findDataToPageByType($type, $number, $asc);

        foreach ($list['data'] as $key => $value) {
            $data = array();
            $data['content'] = $value['content'];
            $data['user'] = getUserName($value['uid']);
            $data['time'] = friendlyDate($value['cTime']);

            $data['doaction'] = '<a href="'.U('admin/Application/deleteFeedback', array('fid' => $value['id'])).'">[删除反馈]</a>';

            $list['data'][$key] = $data;
        }
        unset($data, $key, $value);

        $this->displayList($list);
    }

    /**
     * 删除反馈.
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function deleteFeedback()
    {
        $fid = intval($_REQUEST['fid']);
        model('Feedback')->delete($fid);
        $this->success('删除成功！');
    }

    /*================ Application feedback setting End   ======================*/

    /**
     * 极光推送
     *
     * @author Medz Seven <lovevipdsw@vip.qq.com>
     **/
    public function jpush()
    {
        $this->pageKeyList = array('key', 'secret');
        array_push($this->pageTab, array(
            'title' => '极光推送设置',
            'hash'  => 'jpush',
            'url'   => U('admin/Application/jpush'),
        ));

        $this->displayConfig();
    }

    //app端直播支付相关配置 bs
    public function ZB_config()
    {
        $this->pageKeyList = array('version', 'cash_exchange_ratio_list');
        $this->pageTab[] = array('title' => '充值配置', 'tabHash' => 'charge', 'url' => U('admin/Config/charge'));
        $this->pageTab[] = array('title' => '直播版充值配置', 'tabHash' => 'ZBcharge', 'url' => U('admin/Config/ZBcharge'));
        array_push($this->pageTab, array(
            'title'   => '提现配置',
            'tabHash' => 'ZB_config',
            'url'     => U('admin/Application/ZB_config'),
        ));

        $this->displayConfig();
    }

    //提现管理
    public function ZB_credit_order()
    {
        $this->pageTab[] = array('title' => '提现记录', 'tabHash' => 'ZB_credit_order', 'url' => U('admin/Application/ZB_credit_order'));
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '批量驳回', 'onclick' => 'admin.setReason()');
        $this->pageKeyList = array('order_number', 'uid', 'uname', 'account', 'gold', 'amount', 'ctime', 'utime', 'status', 'DOACTION');
        $this->searchKey = array('uid', 'order_number', 'account');
        $this->$searchPostUrl = U('admin/Application/ZB_credit_order');
        $this->_listpk = 'order_number';
        if ($_POST) {
            $_POST['uid'] && $map['uid'] = $_POST['uid'];
            $_POST['order_number'] && $map['order_number'] = array('like', '%'.$_POST['order_number'].'%');
            $_POST['account'] && $map['account'] = array('like', '%'.$_POST['account'].'%');
        }
        $list = D('credit_order')->where($map)->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            if ($value['status'] == 0) {
                $value['DOACTION'] = '<a href="'.U('admin/Application/pass', array('number' => $value['order_number'])).'">处理</a> ';
                $value['DOACTION'] .= ' <a href="javascript:;" onclick="admin.setReason(\''.$value['order_number'].'\')">驳回</a>';
            }

            switch ($value['status']) {
                case '0':
                    $value['status'] = '<font color="orange">待处理</font>';
                    break;
                case '1':
                    $value['status'] = '<font color="green">已处理</font>';
                    break;
                case '2':
                    $value['status'] = '<font color="red">已驳回</font>';
                    break;
            }
            $value['ctime'] = date('Y-m-d h:i:s', $value['ctime']);
            $value['utime'] = empty($value['utime']) ? '暂无处理' : date('Y-m-d h:i:s', $value['utime']);
            $value['uname'] = getUserName($value['uid']);
        }
        $this->displayList($list);
    }

    public function pass()
    {
        $return = $this->solveOrder($_GET['number'], 1);
        if ($return['status'] == 0) {
            $this->success($return['message']);
        } else {
            $this->error($return['message']);
        }
    }

    public function setReason()
    {
        $numbers = $_GET['number'];
        $this->assign('numbers', $numbers);
        $this->display();
    }

    public function doSetReason()
    {
        $numbers = explode(',', $_POST['number']);
        foreach ($numbers as $key => $value) {
            if (!empty($value)) {
                $this->solveOrder($value, 2, $_POST['reason']);
            }
        }
        exit(json_encode(array('status' => 1, 'info' => '驳回成功')));
    }

    public function messageSystem()
    {
        $this->pageTab[] = array('title' => 'app推送消息列表', 'tabHash' => 'messageSystem', 'url' => U('admin/Application/messageSystem'));
        $this->pageTab[] = array('title' => '添加app推送消息', 'tabHash' => 'addMessageSystem', 'url' => U('admin/Application/addMessageSystem'));
        $listData = model('MessageSystem')->getMessageSysForAdmin();
        $this->pageKeyList = array('id', 'content', 'ctime', 'type', 'row_id', 'remark');

        $this->displayList($listData);
    }

    public function addMessageSystem()
    {
        $this->pageTab[] = array('title' => 'app推送消息列表', 'tabHash' => 'messageSystem', 'url' => U('admin/Application/messageSystem'));
        $this->pageTab[] = array('title' => '添加app推送消息', 'tabHash' => 'addMessageSystem', 'url' => U('admin/Application/addMessageSystem'));
        $this->opt['type'] = model('MessageSystem')->getType();
        $this->opt['to_uids'] = model('UserGroup')->getHashUsergroup();
        $this->opt['to_uids'][0] = '全部用户';
        ksort($this->opt['to_uids']);
        $this->pageKeyList = array('content', 'type', 'row_id', 'to_uids','to_uid', 'remark');
        $this->savePostUrl = U('admin/Application/doaddMessageSystem');
        $this->displayConfig();
    }

    public function doaddMessageSystem()
    {
        $content = t($_POST['content']);
        $type  = t($_POST['type']);
        $row_id = intval($_POST['row_id']);
        $to_uids = intval($_POST['to_uids']);
        $to_uid  = t($_POST['to_uid']); //推送指定用户ID
        $remark = t($_POST['remark']);
        if(empty($content)){
            $this->error("推送内容不允许为空");
        }

        //判断是否有指定用户
        if($to_uid){
            $to_uids = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$to_uid);//过滤中文逗号以及其他符合
//            dump($to_uids);die;
        }else{
            if($to_uids === 0){
                $to_uids = 'all';
            }else{
                $users = D('user_group_link')->field('uid')->where(array("user_group_id" => $to_uids))->select();
                if(empty($users)){
                    $this->error("该用户组没有用户");
                }
                $to_uids = array();
                foreach ($users as $key => $value) {
                    $to_uids[] = $value['uid'];
                }
                $to_uids = implode(",",$to_uids);
            }
        }
//        dump($to_uids);die;
        $data = array(
            'content' => $content,
            'ctime' => time(),
            'row_id' => $row_id,
            'type' => $type,
            'to_uids' => $to_uids,
            'remark'=> $remark,
        );
        if(model('MessageSystem')->add($data)){
            $this->success('推送成功');
        }
        $this->error("推送失败");
    }

    /**
     * 处理提现.
     */
    private function solveOrder($number, $type, $reason = '')
    {
        $map['order_number'] = $number; //多个以逗号隔开 支持批量
        $save['status'] = intval($type) == 1 ? 1 : 2;
        $save['utime'] = time();
        $orderinfo = Model\CreditOrder::where('order_number', $number)->first();
        if ($orderinfo->status == 0) {
            // dumP($orderinfo->uid);die;
            $do = D('credit_order')->where($map)->save($save); //更新处理时间 处理状态

            if ($do) {
                $uinfo = D('User')->where(array('uid' => $orderinfo->uid))->find();
                if ($type == 1) {
                    $messagecontent = '您的提现申请已被处理，请注意查收';
                    if (!empty($uinfo['phone'])) {
                        D('Sms')->sendMessage($uinfo['phone'], $messagecontent);
                    }
                } else {
                    $messagecontent = '您的提现申请已被驳回，理由是'.$reason;
                    if (!empty($uinfo['phone'])) {
                        D('Sms')->sendMessage($uinfo['phone'], $messagecontent);
                    }

                    $record['cid'] = 0; //没有对应的积分规则
                    $record['type'] = 4; //4-提现
                    $record['uid'] = $orderinfo->uid;
                    $record['action'] = '提现驳回';
                    $record['des'] = '';
                    $record['change'] = '积分<font color="red">+'.$orderinfo->gold.'</font>'; //驳回积分加回来
                    $record['ctime'] = time();
                    $record['detail'] = json_encode(array('score' => '+'.$orderinfo->gold));
                    $record['reason'] = $reason;
                    D('credit_record')->add($record);
                    D('credit_user')->setInc('score', 'uid='.$orderinfo->uid, $orderinfo->gold);
                }

                return array('message' => '操作成功', 'status' => 0);
            } else {
                return array('message' => '操作失败', 'status' => 1);
            }
        }
    }


    //IOS&安卓版本管理
    public function version_manage()
    {
        $_REQUEST['tabHash'] = 'version_manage';
        $this->pageTab[] = array('title' => '版本管理', 'tabHash' => 'version_manage', 'url' => U('admin/Application/version_manage'));
        $this->pageTab[] = array('title' => '历史版本', 'tabHash' => 'history', 'url' => U('admin/Application/history'));
//        $this->pageTab[] = array('title' => '新增版本', 'tabHash' => 'version_add', 'url' => U('admin/Application/version_add'));
        $this->pageKeyList = array('id', 'type', 'version', 'edit_time', 'edit_uid', 'DOACTION');
        $map = array();
        $map['is_old'] = 0;
        $list = D('version')->where($map)->order('id DESC')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            if ($value['type']) {
                $value['type'] = 'IOS';
            }else{
                $value['type'] = '安卓';
            }
            if ($value['update_type']) {
                $value['update_type'] = '强制更新';
            }else{
                $value['update_type'] = '非强制更新';
            }
            $value['edit_time'] = date('Y-m-d H:i:s', $value['edit_time']);
            $value['edit_uid'] = getUserName($value['edit_uid']);
            $value['DOACTION'] = '<a href="'.U('admin/Application/version_add', array('id' => $value['id'])).'">更新版本</a> ';
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    //IOS&安卓版本管理-历史版本
    public function history()
    {
        $_REQUEST['tabHash'] = 'history';
        $this->pageTab[] = array('title' => '版本管理', 'tabHash' => 'version_manage', 'url' => U('admin/Application/version_manage'));
        $this->pageTab[] = array('title' => '历史版本', 'tabHash' => 'history', 'url' => U('admin/Application/history'));
//        $this->pageTab[] = array('title' => '新增版本', 'tabHash' => 'version_add', 'url' => U('admin/Application/version_add'));
        $this->pageKeyList = array('id', 'type','new_version', 'version','update_type', 'edit_time', 'edit_uid', 'DOACTION');


        $map = array();
        $map['is_old'] = 1;
        $list = D('version')->where($map)->order('type DESC,id DESC')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            //最新版查询
            $map = array();
            $map['is_old'] = 0;
            $map['type'] = $value['type'];
            $new_version = D('version')->field('version')->where($map)->find();
            $value['new_version'] = $new_version['version'];
            if ($value['type']) {
                $value['type'] = 'IOS';
            }else{
                $value['type'] = '安卓';
            }
            if ($value['update_type']) {
                $value['update_type'] = '强制更新';
            }else{
                $value['update_type'] = '更新';
            }
            $value['edit_time'] = date('Y-m-d H:i:s', $value['edit_time']);
            $value['edit_uid'] = getUserName($value['edit_uid']);
            $value['DOACTION'] = '<a href="'.U('admin/Application/old_version_update', array('id' => $value['id'],'update_type'=>0)).'">更新</a>'."-".'<a href="'.U('admin/Application/old_version_update', array('id' => $value['id'],'update_type'=>1)).'">强制更新</a>';
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    //版本新增/修改
    public function version_add(){
        // tab选项
        $this->pageTab[] = array('title' => '版本管理', 'tabHash' => 'version_manage', 'url' => U('admin/Application/version_manage'));
        $this->pageTab[] = array('title' => '历史版本', 'tabHash' => 'history', 'url' => U('admin/Application/history'));
//        $this->pageTab[] = array('title' => '新增版本', 'tabHash' => 'version_add', 'url' => U('admin/Application/version_add'));
        if($_GET['id']){
            $info = D('version')->where('id='.$_GET['id'])->find();
            if($info['type']){
                $info['type_name'] = 'IOS';
            }else{
                $info['type_name'] = '安卓';
            }
            //版本图片读取
            $info['old_version_img'] = $info['version_img'];
            //$info['version_img'] = "https://v2.edaocha.net/".$info['version_img'];
            $this->assign('info',$info);
            $this->assign('title','修改版本');
        }else{
            $this->assign('title','新增版本');
        }

        $this->display();
    }




    /**
     *
     * 新增或者修改版本
     *
     */
    public function doAdd(){
        if($_POST['id']){
            set_time_limit(0);
            //最新版查询
            $old_version = str_replace('.', '', $_POST['old_version']);
            $new_version = str_replace('.', '', $_POST['version']);
            if($old_version>=$new_version){
                $this->error('新的版本号要大于老版本号');
            }
            //版本更新图片
            if($_FILES["version_img"]["name"]){
                $version_img_info = pathinfo($_FILES['version_img']['name']);
                $img_ext = $version_img_info['extension'];

                if(!($img_ext=='jpg'||$img_ext=='jpeg'||$img_ext=='png')){
                    $this->error('图片上传失败，后缀名错误');
                }
                if ($_FILES["version_img"]["error"] > 0)
                {
                    $this->error('图片上传失败：.'.$_FILES["version_img"]["error"]);
                }else{
                    $Y = date('Y',time());
                    $md = date('md',time());
                    $h = date('H',time());
                    $img_path = "data/upload/".$Y."/".$md."/".$h."/";
                    if(!file_exists($img_path)){
                        mkdir($img_path, 0777, true);
                    }
                    $img_name = $_FILES["version_img"]["name"];
                    $img_url = $img_path.$img_name;
                    $upload_res = move_uploaded_file($_FILES["version_img"]["tmp_name"],$img_url);
                    if(!$upload_res){
                        $this->error('图片上传失败，请重试');
                    }
                }

            }else{
                $img_url = $_POST['old_version_img'];
            }
            //当前版本改为老版本，并添加新版本
            $map = array();
            $map['id'] = $_POST['id'];
            $map['is_old'] = 1;
            $old_res = D('version')->save($map);
            if($old_res===false){
                $this->error('版本更新失败');
            }
            $map = array();
            $map['type'] = $_POST['type'];
            $map['version'] = $_POST['version'];
            $map['version_title'] = $_POST['version_title'];
            $map['version_img'] = $img_url;
            $map['edit_time'] = time();
            $map['edit_uid'] = $GLOBALS['ts']['mid'];
            if($_POST['type']=='0'){
                $videoinfo = pathinfo($_FILES['file']['name']);
                $video_ext = $videoinfo['extension'];
                if(!($video_ext=='apk')){
                    $this->error('APK上传失败，后缀名错误');
                }
                if ($_FILES["file"]["error"] > 0)
                {
                    $this->error('APK上传失败：.'.$_FILES["file"]["error"]);
                }else{
                    $upload_res = move_uploaded_file($_FILES["file"]["tmp_name"],"/data/wwwroot/aodouimg/download/package/" . $_FILES["file"]["name"]);
                    if(!$upload_res){
                        $this->error('APK上传失败，请重试');
                    }
                }
            }

            //版本号修改
            $res = D('version')->add($map);
            if($res){
                $this->assign('jumpUrl', U('admin/Application/version_manage'));
                $this->success('更新成功');
            }else{
                $this->error('添加失败，请重试');
            }

        }else{
            $map = array();
            $map['type'] = $_POST['type'];
            $map['version'] = $_POST['version'];
            $map['update_type'] = $_POST['update_type'];
            $map['edit_time'] = time();
            $map['edit_uid'] = $GLOBALS['ts']['mid'];
            $res = D('version')->add($map);
            if($res){
                $this->assign('jumpUrl', U('admin/Application/version_manage'));
                $this->success(L('PUBLIC_ADD_SUCCESS'));
            }else{
                $this->error('添加失败，请重试');
            }
        }
    }


    /**
     *
     * 老版本更新方式修改
     *
     */
    public function old_version_update(){
        $id = $_GET['id'];
        $update_type = $_GET['update_type'];
        $map = array();
        $map['id'] = $id;
        $map['update_type'] = $update_type;
        $map['edit_time'] = time();
        $map['edit_uid'] = $GLOBALS['ts']['mid'];
        $res = D('version')->save($map);
        if(!($res===false)){
            $this->assign('jumpUrl', U('admin/Application/history'));
            $this->success('更新成功');
        }else{
            $this->error('更新失败，请重试');
        }
    }


    /**
     *
     * 匿名管理Tab
     *
     */
    private function _initAnonymousTab(){
        $this->pageTab[] = array('title' => '匿名分类', 'tabHash' => 'anonymous_category', 'url' => U('admin/Application/anonymous_category'));
        $this->pageTab[] = array('title' => '匿名管理', 'tabHash' => 'anonymous_manage', 'url' => U('admin/Application/anonymous_manage'));
        $this->pageTab[] = array('title' => '用户匿名管理', 'tabHash' => 'user_anonymous_manage', 'url' => U('admin/Application/user_anonymous_manage'));
        $this->pageTab[] = array('title' => '添加匿名', 'tabHash' => 'anonymous_add', 'url' => U('admin/Application/anonymous_add'));
    }


    /**
     *
     * 匿名分类查询
     *
     */
    public function anonymous_category(){
        $_REQUEST['tabHash'] = 'anonymous_category';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('id', 'nickname','icon','weight', 'DOACTION');

        $this->searchKey = array('id', 'nickname');
        $this->$searchPostUrl = U('admin/Application/anonymous_category');

        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '删除', 'onclick' => "admin.rm_anonymous_category()");
        $this->pageButton[] = array('title' => '添加', 'onclick' => "location.href='".U('admin/Application/anonymous_category_add')."'");

        $map = array();
        if ($_POST) {
            $_POST['id'] && $map['id'] = $_POST['id'];
            $_POST['nickname'] && $map['nickname'] = array('like', '%'.$_POST['nickname'].'%');
        }
        $map['pid'] = 0;
        $map['is_del'] = 0;
        $list = D('anonymous')->where($map)->order('id DESC')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            if ($value['icon']) {
                $pic = D('attach')->where('attach_id='.$value['icon'])->find();
                $pic_url = $pic['save_path'].$pic['save_name'];
                $url = getImageUrl($pic_url);
                $value['icon'] = "<img src='$url' width='50' height='50'/>";
            }
            $value['DOACTION'] = '<a href="'.U('admin/Application/anonymous_category_edit', array('id' => $value['id'])).'">[编辑]</a> - ';
            $value['DOACTION'] .= ' <a href="javascript:;" onclick="admin.rm_anonymous_category(\''.$value['id'].'\')">[删除]</a>';
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    /**
     *
     * 准备添加匿名分类
     *
     */
    public function anonymous_category_add(){
        $_REQUEST['tabHash'] = 'anonymous_category';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('nickname','icon','weight');
        $this->notEmpty = array('nickname','icon');
        $this->savePostUrl = U('admin/Application/do_anonymous_category_add');
        $this->displayConfig();
    }


    /**
     *
     * 添加匿名分类
     *
     */
    public function do_anonymous_category_add(){
        if(!$_POST['nickname']){
            $this->error('请填写分类名称');
        }
        if(!$_POST['icon']){
            $this->error('请上传分类头像');
        }
        $data['nickname'] = t($_POST['nickname']);
        $info = D('anonymous')->where($data)->find();
        if($info){
            $this->error('分类名称已存在，请更换');
        }
        $data['icon'] = $_POST['icon'];
        $data['weight'] = $_POST['weight'];
        $result = D('anonymous')->add($data);
        if ($result) {
            $this->assign('jumpUrl', U('admin/Application/anonymous_category'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }


    /**
     *
     * 准备修改匿名分类
     *
     */
    public function anonymous_category_edit(){
        $_REQUEST['tabHash'] = 'anonymous_category';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('id','nickname','icon','weight');
        $this->notEmpty = array('nickname','icon');
        $anonymous = D('anonymous')->where('id='.intval($_GET['id']))->find();

        $this->savePostUrl = U('admin/Application/do_anonymous_category_edit');
        $this->displayConfig($anonymous);
    }


    /**
     *
     * 修改匿名分类
     *
     */
    public function do_anonymous_category_edit(){
        if(!$_POST['nickname']){
            $this->error('请填写分类名称');
        }
        if(!$_POST['icon']){
            $this->error('请上传分类头像');
        }
        $data['nickname'] = t($_POST['nickname']);
        $data['id'] = array('neq',intval($_POST['id']));
        $info = D('anonymous')->where($data)->find();
        if($info){
            $this->error('分类名称已存在，请更换');
        }
        $save['nickname'] = t($_POST['nickname']);
        $save['icon'] = $_POST['icon'];
        $save['id'] = intval($_POST['id']);
        $save['weight'] = intval($_POST['weight']);
        $result = D('anonymous')->save($save);
        if (!($result===false)) {
            $this->assign('jumpUrl', U('admin/Application/anonymous_category'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }


    /**
     *
     * 匿名分类删除
     *
     */
    public function doRmAnonymousCategory(){
        $p_map['pid'] = $c_map['id'] = $ids = array('in', $_POST['id']);
        if (empty($ids)) {
            exit(json_encode(array('status' => '0', 'info' => '参数错误')));
        }
        //删除分类时，要删除下面的子类以及用户绑定记录
        $anonymous = D('anonymous')->where($c_map)->select();
        if ($anonymous) {
            //查询下面的子类
            $list = D('anonymous')->where($p_map)->select();
            foreach($list as $v){
                $map = array();
                $map['anonymous_id'] = $v['id'];
                $res_del = D('user_anonymous')->where($map)->delete();
                if($res_del){
                    $map = array();
                    $map['id'] = $v['id'];
                    D('anonymous')->where($map)->delete();
                }
            }
            //删除当前分类
            D('anonymous')->where($c_map)->delete();
            $res = array('status' => '1', 'info' => '操作成功');
        } else {
            $res = array('status' => '0', 'info' => '分类不存在或已删除');
        }
        exit(json_encode($res));
    }


    /**
     *
     * 匿名列表查询
     *
     */
    public function anonymous_manage(){
        $_REQUEST['tabHash'] = 'anonymous_manage';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('id', 'nickname','pid','use_count','all_count', 'DOACTION');

        $this->searchKey = array('id','pid', 'nickname');
        $this->$searchPostUrl = U('admin/Application/anonymous_manage');

        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '删除', 'onclick' => "admin.rm_anonymous()");
        $this->pageButton[] = array('title' => '添加', 'onclick' => "location.href='".U('admin/Application/anonymous_add')."'");

        // 匿名分类
        $categoryHash = D('anonymous')->field('id,nickname')->where('pid=0')->select();
        $this->opt['pid'][0] = '请选择匿名分类';
        foreach ($categoryHash as $key => $value) {
            $this->opt['pid'][$value['id']] = $value['nickname'];
        }

        $map = array();
        if($_POST['id']){
            $map['id'] = $_POST['id'];
        }
        if($_POST['nickname']){
            $map['nickname'] = array('like', '%'.$_POST['nickname'].'%');
        }
        if($_POST['pid']){
            $map['pid'] = $_POST['pid'];
        }else{
            $map['pid'] = array('neq','0');
        }

        $map['is_del'] = 0;
        $list = D('anonymous')->where($map)->order('id DESC')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            //根据父类ID查询名称
            $value['pid'] = D('anonymous')->where('id='.$value['pid'])->getField('nickname');
            $value['DOACTION'] = '<a href="'.U('admin/Application/anonymous_edit', array('id' => $value['id'])).'">[编辑]</a> - ';
            $value['DOACTION'] .= ' <a href="javascript:;" onclick="admin.rm_anonymous(\''.$value['id'].'\')">[删除]</a> - ';
            $value['DOACTION'] .= ' <a href="'.U('admin/Application/anonymous_bd_detail', array('anonymous_id' => $value['id'])).'">[查看绑定详情]</a>';
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    /**
     *
     * 准备添加匿名
     *
     */
    public function anonymous_add(){
        $_REQUEST['tabHash'] = 'anonymous_add';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('nickname','pid');
        // 匿名分类
        $categoryHash = D('anonymous')->field('id,nickname')->where('pid=0')->select();
        $this->opt['pid'][0] = '请选择匿名分类';
        foreach ($categoryHash as $key => $value) {
            $this->opt['pid'][$value['id']] = $value['nickname'];
        }
        $this->notEmpty = array('nickname','pid');
        $this->savePostUrl = U('admin/Application/do_anonymous_add');
        $this->displayConfig();
    }


    /**
     *
     * 添加匿名
     *
     */
    public function do_anonymous_add(){
        if(!$_POST['nickname']){
            $this->error('请填写匿名名称');
        }
        if(!$_POST['pid']){
            $this->error('请选择匿名分类');
        }
        $data['nickname'] = t($_POST['nickname']);
        $data['pid'] = intval($_POST['pid']);
        $info = D('anonymous')->where($data)->find();
        if($info){
            $this->error('该名称已存在，请更换');
        }
        $result = D('anonymous')->add($data);
        if ($result) {
            $this->assign('jumpUrl', U('admin/Application/anonymous_manage'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }


    /**
     *
     * 准备修改匿名
     *
     */
    public function anonymous_edit(){
        $_REQUEST['tabHash'] = 'anonymous_manage';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('id','nickname','pid');
        // 匿名分类
        $categoryHash = D('anonymous')->field('id,nickname')->where('pid=0')->select();
        $this->opt['pid'][0] = '请选择匿名分类';
        foreach ($categoryHash as $key => $value) {
            $this->opt['pid'][$value['id']] = $value['nickname'];
        }
        $this->notEmpty = array('nickname','pid');
        $anonymous = D('anonymous')->where('id='.intval($_GET['id']))->find();
        $this->savePostUrl = U('admin/Application/do_anonymous_edit');
        $this->displayConfig($anonymous);
    }


    /**
     *
     * 修改匿名
     *
     */
    public function do_anonymous_edit(){
        if(!$_POST['nickname']){
            $this->error('请填写分类名称');
        }
        if(!$_POST['pid']){
            $this->error('请选择匿名分类');
        }
        $data['nickname'] = t($_POST['nickname']);
        $data['pid'] = intval($_POST['pid']);
        $data['id'] = array('neq',intval($_POST['id']));
        $info = D('anonymous')->where($data)->find();
        if($info){
            $this->error('匿名名称已存在，请更换');
        }
        $save['nickname'] = t($_POST['nickname']);
        $save['pid'] = intval($_POST['pid']);
        $save['id'] = intval($_POST['id']);
        $result = D('anonymous')->save($save);
        if (!($result===false)) {
            $this->assign('jumpUrl', U('admin/Application/anonymous_manage'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }


    /**
     *
     * 匿名删除
     *
     */
    public function doRmAnonymous(){
        $c_map['id'] = $ids = array('in', $_POST['id']);
        if (empty($ids)) {
            exit(json_encode(array('status' => '0', 'info' => '参数错误')));
        }
        //删除匿名时，要删除用户绑定记录
        $anonymous = D('anonymous')->where($c_map)->select();
        if ($anonymous) {
            //查询下面的用户
            foreach($anonymous as $v){
                $map = array();
                $map['anonymous_id'] = $v['id'];
                D('user_anonymous')->where($map)->delete();
            }
            //删除匿名
            D('anonymous')->where($c_map)->delete();
            $res = array('status' => '1', 'info' => '操作成功');
        } else {
            $res = array('status' => '0', 'info' => '匿名不存在或已删除');
        }
        exit(json_encode($res));
    }


    /**
     *
     * 匿名绑定详情
     *
     */
    public function anonymous_bd_detail(){
        $_REQUEST['tabHash'] = 'anonymous_manage';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('id', 'uid','anonymous_id','bd_time');

        $this->searchKey = array('id','uid');
        $this->$searchPostUrl = U('admin/Application/anonymous_bd_detail');

        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");

        $map = array();
        if($_POST['id']){
            $map['a.id'] = $_POST['id'];
        }else{
            $map['a.anonymous_id'] = intval($_GET['anonymous_id']);
        }
        if($_POST['uid']){
            $map['a.uid'] = $_POST['uid'];
        }

        $tablePrefix = C('DB_PREFIX');
        $list = D('user_anonymous')
            ->field('a.id,a.uid,a.anonymous_id,a.bd_time,u.uname,s.nickname')
            ->where($map)
            ->table("{$tablePrefix}user_anonymous AS a LEFT JOIN {$tablePrefix}user AS u ON a.uid = u.uid LEFT JOIN {$tablePrefix}anonymous AS s ON s.id = a.anonymous_id")
            ->order('a.id DESC')->findPage(20);

        foreach ($list['data'] as $key => &$value) {
            //根据父类ID查询名称
            $value['anonymous_id'] = $value['nickname'];
            $value['uid'] = $value['uname']."(ID:".$value['uid'].")";
            $value['bd_time'] = date('Y-m-d H:i:s',$value['bd_time']);
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }


    /**
     *
     * 用户匿名绑定列表
     *
     */
    public function user_anonymous_manage(){
        $_REQUEST['tabHash'] = 'user_anonymous_manage';
        $this->_initAnonymousTab();
        $this->pageKeyList = array('id', 'uid','anonymous_id','bd_time');

        $this->searchKey = array('id','uid','uname','nickname','pid');
        $this->$searchPostUrl = U('admin/Application/user_anonymous_manage');

        // 匿名分类
        $categoryHash = D('anonymous')->field('id,nickname')->where('pid=0')->select();
        $this->opt['pid'][0] = '请选择匿名分类';
        foreach ($categoryHash as $key => $value) {
            $this->opt['pid'][$value['id']] = $value['nickname'];
        }

        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");

        $map = array();
        if($_POST['id']){
            $map['a.id'] = $_POST['id'];
        }
        if($_POST['uid']){
            $map['a.uid'] = $_POST['uid'];
        }
        if($_POST['nickname']){
            $map['s.nickname'] = $_POST['nickname'];
        }
        if($_POST['pid']){
            $map['s.pid'] = $_POST['pid'];
        }
        if($_POST['uname']){
            $map['u.uname'] = t($_POST['uname']);
        }
        $tablePrefix = C('DB_PREFIX');
        $list = D('user_anonymous')
            ->field('a.id,a.uid,a.anonymous_id,a.bd_time,u.uname,s.nickname')
            ->where($map)
            ->table("{$tablePrefix}user_anonymous AS a LEFT JOIN {$tablePrefix}user AS u ON a.uid = u.uid LEFT JOIN {$tablePrefix}anonymous AS s ON s.id = a.anonymous_id")
            ->order('a.id DESC')->findPage(20);

        foreach ($list['data'] as $key => &$value) {
            //根据父类ID查询名称
            $value['anonymous_id'] = $value['nickname'];
            $value['uid'] = $value['uname']."(ID:".$value['uid'].")";
            $value['bd_time'] = date('Y-m-d H:i:s',$value['bd_time']);
        }
        $this->_listpk = 'id';
        $this->displayList($list);
    }

} // END class ApplicationAction extends AdministratorAction
