<?php

namespace Apps\Information\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Information\Common;
use AdministratorAction            as Controller;
use Apps\Information\Model\Cate    as CateModel;
use Apps\Information\Model\Subject as SubjectModel;
use Apps\Information\Model\Top     as TopModel;

Common::import(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

/**
 * 资讯后台管理控制器
 *
 * @package Apps\Information\Controller\Admin
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Admin extends Controller
{
    /*=================================非Action区域========================================*/
    /**
     * 初始化操作
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function _initialize()
    {
        parent::_initialize();
        Common::setHeader('text/html', 'utf-8');
         $this->pageTitle['index'] = '分类列表';
        $this->pageTitle['subjectList'] = '文章列表';
        $this->pageTitle['subjectCheck'] = '审核文章';
        $this->pageTitle['subjectUncheck'] = '未通过文章';
        $this->pageTitle['subjectRecycle'] = '回收站';
        $this->pageTitle['comment'] = '文章评论';
    }
    /**
     * 添加tab菜单
     *
     * @param string $hash  方法的名称
     * @param string $url   点击跳转地址，非绝对地址，而是U函数需要构造的内容
     * @param string $name  描述名称
     * @param array  $param url拓展参数
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _addTab($hash, $url, $name = '', array $param = array())
    {
        if (!$name) {
            $name = $this->pageTitle[$hash];
        }
        array_push($this->pageTab, array(
            'title' => $name,
            'tabHash' => $hash,
            'url' => U($url, $param),
        ));
    }
    /**
     * 公用TAB切换菜单
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _commonTab()
    {
        /* index */
        $this->_addTab('index', 'Information/Admin/index');
        /* 主体列表 */
        $this->_addTab('subjectList', 'Information/Admin/subjectList','',array('status' => 1));
        /* 审核文章 */
        $this->_addTab('subjectCheck', 'Information/Admin/subjectList','',array('status' => 0));
        /* 未通过文章 */
        $this->_addTab('subjectUncheck', 'Information/Admin/subjectList','',array('status' => 2));
        /* 回收站 */
        $this->_addTab('subjectRecycle', 'Information/Admin/subjectList','',array('status' => 3));
        /* 文章评论 */
        $this->_addTab('comment', 'Information/Admin/comment');
    }

/*=================================Action区域===========================================*/

    /**
     * 常规配置
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function config()
    {
        $this->_commonTab();
        $this->pageKeyList = array('hotTime', 'commentHotTime', 'guide');
        $this->displayConfig();
    }

    /**
     * 资讯分类列表
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        $this->_commonTab();
        $this->pageKeyList = array('id', 'name', 'rank', 'action');
        $this->searchKey = array('id', 'name');

        /* # 排序保存按钮 */
        array_push($this->pageButton, array(
            'title' => '保存',
            'id' => 'information-submit',
            'data' => array(
                'uri' => U('Information/Admin/saveCateRank'),
            ),
        ));
        /* 搜索按钮 */
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'information-search',
        ));
        /* 删除 */
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'information-delete',
            'data' => array(
                'uri' => U('Information/Admin/deleteCate', array('ids' => '__IDS__')),
            ),
        ));
        array_push($this->pageButton, array(
            'title' => '添加',
            'id' => 'information-add',
            'data' => array(
                'url' => U('Information/Admin/cateAdd', array('tabHash' => 'index')),
            ),
        ));

        list($id, $name) = Common::getInput(array('id', 'name'), 'get');

        $data = CateModel::getInstance()->getAdmin4Rank($id, $name);
        // var_dump($data, CateModel::getInstance());exit;
        foreach ($data['data'] as $key => $value) {
            unset($value['isDel']);
            $value['rank'] = '<input id="information-ranks" type="number" value="'.$value['rank'].'" data-id="'.$value['id'].'" min="0" step="1">';
            $value['action'] = '<a href="'.U('Information/Admin/cateAdd', array('id' => $value['id'])).'">[修改名称]</a>&nbsp-&nbsp;<a href="'.U('Information/Admin/deleteCate', array('ids' => $value['id'])).'">[删除分类]</a>';

            $data['data'][$key] = $value;
        }

        /* 添加js */
        array_push($this->appJsList, 'js/admin.cate.js');

        $this->displayList($data);
    }

    /**
     * 添加分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function cateAdd()
    {
        $id = Common::getInput('id', 'get');
        $id = intval($id);
        $this->_commonTab();
        $this->notEmpty = $this->pageKeyList = array('name');
        $this->submitAlias = '添加';
        $id && $this->submitAlias = '修改';
        $this->savePostUrl = U('Information/Admin/saveCate', array('id' => $id));
        $this->displayConfig(CateModel::getInstance()->setId($id)->getInfoById());
    }

    /**
     * 保存分类数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function saveCate()
    {
        list($id, $name) = Common::getInput(array('id', 'name'));
        CateModel::getInstance()->setId($id)->setName($name);

        /* # 如果存在，则修改分类名称 */
        if (CateModel::getInstance()->hasById() && !($status = CateModel::getInstance()->changeName())) {
            $this->error(CateModel::getInstance()->getError());

        /* # 添加分类，是否失败 */
        } elseif (!$status && !CateModel::getInstance()->add()) {
            $this->error(CateModel::getInstance()->getError());
        }
        if($id){
            //记录操作日志
            LogRecord('admin_system', 'saveCate', array('name' => $id, 'k1' => '修改资讯分类'), true);
        }else{
            //记录操作日志
            LogRecord('admin_system', 'addCate', array('name' => $name, 'k1' => '新增资讯分类'), true);
        }
        $this->assign('jumpUrl', U('Information/Admin/index'));
        $this->success('操作成功！');
    }

    /**
     * 保存分类等级设置
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function saveCateRank()
    {
        ob_end_clean();
        ob_start();
        $ranks = Common::getInput('ranks', 'post');
        $ranks = explode(',', $ranks);
        foreach ($ranks as $value) {
            $value = explode('=', $value);
            if ($value[0]) {
                CateModel::getInstance()->setId($value[0])->setRank($value[1])->changeRankById();
            }
        }
        ob_end_flush();
        exit;
    }

    /**
     * 删除分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function deleteCate()
    {
        $del_id = $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        if (!CateModel::getInstance()->delete($ids)) {
            $this->error(CateModel::getInstance()->getError());
        }
        //记录操作日志
        LogRecord('admin_system', 'deleteCate', array('name' => $del_id, 'k1' => '删除资讯分类'), true);
        $this->success('删除成功');
    }

    /**
     * 文章列表
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function subjectList()
    {
        $this->_commonTab();
        $this->pageKeyList = array('id', 'cate', 'subject', 'tag', 'author', 'zreditor', 'created_name', 'ctime', 'hits', 'more', 'action');
        $this->searchKey = array('id', 'cid', 'tag', 'abstract', 'created_name', 'zreditor', 'copyfrom', 'author', 'ip', 'subject', 'stime', 'etime',array('ctime','ctime1'));


        $status = Common::getInput('status');
        /* 搜索按钮 */
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'subject-search',
        ));
        /* # 批量删除 */
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'subject-delete',
            'data' => array(
                'uri' => U('Information/Admin/subjectDel', array('ids' => '__IDS__')),
            ),
        ));

        /* # 批量放入回收站 */
        if($status != 3){
            array_push($this->pageButton, array(
                'title' => '放入回收站',
                'id' => 'subject-empty',
                'data' => array(
                    'uri' => U('Information/Admin/subjectStatus', array('ids' => '__IDS__','status' => 3)),
                ),
            ));
        }

        if($status == 0 || $status == 2){
            array_push($this->pageButton, array(
                'title' => '审核文章',
                'id' => 'subject-check',
                'data' => array(
                    'uri' => U('Information/Admin/subjectStatus', array('ids' => '__IDS__','status' => 1)),
                ),
            ));
        }

        if($status == 3){
            array_push($this->pageButton, array(
                'title' => '还原并审核',
                'id' => 'subject-reduction',
                'data' => array(
                    'uri' => U('Information/Admin/subjectStatus', array('ids' => '__IDS__','status' => 1)),
                ),
            ));
        }
        /* # 添加文字 */
        array_push($this->pageButton, array(
            'title' => '添加',
            'id' => 'subject-add',
            'data' => array(
                'url' => U('Information/Admin/addSubject', array('tabHash' => 'subjectList')),
            ),
        ));
        /* 添加JS */
        array_push($this->appJsList, 'js/admin.subject.js');

        $cates = array(0 => '全部');
        foreach (CateModel::getInstance()->get4Rank() as $cate) {
            $cates[$cate['id']] = $cate['name'];
        }
        $this->opt['cid'] = $cates;
        unset($cate, $cates);
        $this->opt['isTop'] = array(
            '0' => '不限',
            '1' => '推荐',
        );
        $this->opt['isPre'] = array(
            '0' => '不限',
            '1' => '预发布',
        );

        /* # 搜索提交地址 */
        $this->searchPostUrl = U('Information/Admin/subjectList', array('tabHash' => 'subjectList','status' => $status));
        list($id, $cid, $tag, $abstract, $created_name, $zreditor, $copyfrom, $author, $ip, $subject, $stime ,$etime,$ctime) = Common::getInput(array('id', 'cid', 'tag', 'abstract', 'created_name', 'zreditor', 'copyfrom', 'author', 'ip', 'subject', 'stime', 'etime','ctime'));
        $data = SubjectModel::getInstance()->getAdminData(20, $id, $cid, $tag, $abstract, $created_name, $zreditor, $copyfrom, $author, $ip, $subject, $stime, $etime, $status,$ctime);
//        dump($data);
        foreach ($data['data'] as $key => $value) {
            $value['cate'] = CateModel::getInstance()->setId($value['cid'])->getInfoById();
            $value['cate'] = $value['cate']['name'];
            $value['subject'] = '<a href="'.U('Information/Index/read',array('id' => $value['id'])).'" target="_blank">'.$value['subject'].'</a>';
            $value['ctime'] = date('Y-m-d H:i:s', $value['ctime']);
            $value['author'] = getUserName($value['author']).'(UID:'.$value['author'].')';
            $value['more'] = '<a href="javascript:;" onclick="admin.subjectMore('.$value['id'].');">[更多]</a>';
            $value['action'] = '';
            $value['action'] .= '<a href="'.U('Information/Admin/addSubject', array('id' => $value['id'], 'tabHash' => 'subjectList')).'">[编辑]</a>&nbsp;-&nbsp;';
            $value['action'] .= '<a href="'.U('Information/Admin/subjectStatus', array('ids' => $value['id'],'status'=>3)).'">[放入回收站]</a>';
            $data['data'][$key] = $value;
        }
        // var_dump($data);exit;

        $this->displayList($data);
    }

    /**
     * 取消推荐文章
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function unSubjectTop()
    {
        $id = intval(Common::getInput('id'));
        if (TopModel::getInstance()->setSid($id)->delBySid()) {
            TopModel::getInstance()->cleanTable();
            $this->success('执行成功');
        }
        $this->error('执行失败');
    }

    /**
     * 设置推荐文章
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function subjectTop()
    {
        $id = intval(Common::getInput('id'));
        SubjectModel::getInstance()->setId($id);
        /* # 检查是否不存在 */
        if (!SubjectModel::getInstance()->has()) {
            $this->error('当前主题不存在');

        /* # 检查是预发布主题 */
        } elseif (SubjectModel::getInstance()->hasIsPre()) {
            $this->error('当前主题资讯是投稿预发布状态，无法推荐，请先正是发布后推荐！');
        }

        $data = SubjectModel::getInstance()->getSubject();

        $this->_commonTab();
        $this->notEmpty = $this->pageKeyList = array('title', 'image');
        $this->savePostUrl = U('Information/Admin/saveSubjectTop', array('id' => $id));
        $this->displayConfig();
    }

    /**
     * 保存推荐数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function saveSubjectTop()
    {
        $id = intval(Common::getInput('id'));
        list($title, $image) = Common::getInput(array('title', 'image'), 'post');
        $image = intval($image);
        if (TopModel::getInstance()->setTitle($title)->setImage($image)->setSid($id)->add()) {
            $this->assign('jumpUrl', U('Information/Admin/subjectList', array('tabHash' => 'subjectList')));
            $this->success('设置推荐成功');
        }
        $this->error(TopModel::getInstance()->getError());
    }

    /**
     * 删除主题数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function subjectDel()
    {
        $del_id = $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        if (!SubjectModel::getInstance()->delete($ids)) {
            $this->error(SubjectModel::getInstance()->getError());
        }
        //记录操作日志
        LogRecord('admin_system', 'subjectDel', array('name' => $del_id, 'k1' => '删除资讯'), true);
        $this->success('删除成功');
    }

    /**
     * 放入回收站
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function subjectStatus()
    {
        $del_id = $ids = Common::getInput('ids');
        $status = Common::getInput('status');
        $ids = explode(',', $ids);
        if (!SubjectModel::getInstance()->subjectStatus($ids,$status)) {
            $this->error(SubjectModel::getInstance()->getError());
        }
        //记录操作日志
        LogRecord('admin_system', 'subjectStatus', array('name' => $del_id, 'k1' => '删除资讯到回收站'), true);
        $this->success('操作成功');
    }


    /**
     * 发布预发布的主题
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function postSubjectPre()
    {
        $id = Common::getInput('id');
        if (SubjectModel::getInstance()->setId($id)->setIsPre(false)->update()) {
            $this->success('发布成功');
        }
        $this->error(SubjectModel::getInstance()->getError());
    }

    /**
     * 主题信息
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function addSubject()
    {
        $id = Common::getInput('id');
        $setTk = $_GET['setTk'];
        $setTk && $this->opt['setTk'] = $setTk;
        $this->_commonTab();
        $this->pageKeyList = array('cid', 'isLink', 'linkurl', 'subject', 'logo', 'abstract', 'created_name','zreditor','copyfrom','isYuan','isYuan_2','tag','content','content_more','status','notice','ctime','hits');
        $this->notEmpty = array('cid', 'isLink', 'linkurl', 'subject', 'logo', 'abstract', 'created_name','zreditor','copyfrom','isYuan','isYuan_2','tag','content','status','notice','ctime');
        $this->savePostUrl = U('Information/Admin/saveSubject', array('id' => $id));

        $cates = array();
        foreach (CateModel::getInstance()->get4Rank() as $cate) {
            $cates[$cate['id']] = $cate['name'];
        }
        $this->opt['cid'] = $cates;
        $this->opt['isYuan'] = array(0=>'否',1=>'是');
        $this->opt['isYuan_2'] = array(0=>'否',1=>'是');
        $this->opt['status'] = array(0=>'待审',1=>'通过',2=>'拒绝');
        $this->opt['tk']['url'] = 'Information/Admin/addSubject';
        unset($cate, $cates);
        $this->displayConfig(SubjectModel::getInstance()->setId($id)->getSubject());
    }

    /**
     * 文章更多信息展示
     *
     * @author Seven Du <383621328@qq.com>
     **/
    public function subjectMore()
    {
        $id = Common::getInput('id');
        $this->notEmpty = $this->pageKeyList = array('subject', 'created_name', 'ctime', 'rtime', 'zreditor');
        $subject = SubjectModel::getInstance()->setId($id)->getSubject();
        $this->assign('subject',$subject);
        $this->display('Admin/subjectMore');
    }

    /**
     * 保存主题数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function saveSubject()
    {
        list($cid, $isLink, $linkurl, $subject, $logo, $abstract, $created_name, $zreditor, $copyfrom, $isYuan, $isYuan_2, $tag, $content, $status, $notice, $ctime, $hits, $clear_link,$introduce_length,$thumb_no) = Common::getInput(array('cid', 'isLink', 'linkurl', 'subject', 'logo', 'abstract', 'created_name','zreditor','copyfrom','isYuan','isYuan_2','tag','content','status','notice', 'ctime', 'hits','clear_link','introduce_length','thumb_no'),'post');
        //清除外部链接
        if($clear_link){
            $content = Common::clear_link($content);
        }
        //截取内容前几个字符到摘要
        if($introduce_length){
            $abstract = Common::subArticle($content,$introduce_length);
        }
        //设置内容第几张图片做为封面图
        if($thumb_no){
            $thumb_no = $thumb_no-1;
            $logo_url = Common::save_remote($content,$thumb_no);
            $urls = explode("/",$logo_url);
            $imgs = explode(".",$urls[7]);

            $map = array();
            $map['attach_type'] = 'admin_image';
            $map['uid'] = $this->mid;
            $map['ctime'] = strtotime(date('Y-m-d H:i:s',time()));
            $map['name'] = $urls[7];
            $map['type'] = 'image/'.$imgs[1];
            $map['extension'] = $imgs[1];
            $map['save_path'] = $urls[4].'/'.$urls[5].'/'.$urls[6].'/';
            $map['save_name'] = $urls[7];
            $res = M('attach')->add($map);
            $logo = $res;
        }

        $id = Common::getInput('id');
        SubjectModel::getInstance()->setId($id)
            ->setCate($cid)
            ->setLinkurl($isLink,$linkurl)
            ->setLogo($logo)
            ->setSubject($subject)
            ->setAbstract($abstract)
            ->setField(array('created_name' => $created_name, 'zreditor' => $zreditor, 'copyfrom' => $copyfrom, 'tag' => $tag, 'notice' => $notice, 'hits' => $hits))
            ->setRadio(array('isYuan' => $isYuan, 'isYuan_2' => $isYuan_2))
            ->setStatus($status)
            ->setCTime($ctime)
            ->setTag($tag)
            ->setContent($content)
            ->setHits($hits)
            ->setRTime();
        if ($id && SubjectModel::getInstance()->setAuthor($this->mid,false)->update()) {
            //记录操作日志
            LogRecord('admin_system', 'saveSubject', array('name' => $id, 'k1' => '修改资讯'), true);
            $this->success('更新主题成功');
        } elseif (SubjectModel::getInstance()->setAuthor($this->mid,true)->add()) {
            $this->assign('jumpUrl', U('Information/Admin/subjectList', array('tabHash' => 'subjectList','status'=>0)));
            //记录操作日志
            LogRecord('admin_system', 'addSubject', array('name' => $subject, 'k1' => '新增资讯'), true);
            $this->success('添加主题成功');
        }
        $this->error(SubjectModel::getInstance()->getError());
    }


    /**
     *
     * 文章评论
     *
     * @author zhl <zhuhailin@edaocha.com>
     **/
    public function comment()
    {
        $this->_commonTab();
        $this->pageKeyList = array('comment_id', 'uid', 'app_uid', 'source_type', 'content', 'ctime', 'client_type', 'DOACTION');
        $this->searchKey = array('comment_id', 'uid', 'app_uid');
        $this->pageButton[] = array('title' => L('PUBLIC_SEARCH_COMMENT'), 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => L('PUBLIC_DELETE_COMMENT'), 'onclick' => "admin.ContentEdit('','delComment','".L('PUBLIC_STREAM_DELETE')."','".L('PUBLIC_STREAM_COMMENT')."')");
        $map = array();
        $map['is_del'] =  0;
        $map['app'] = 'Information';
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
            $v['DOACTION'] =  "<a href='".$v['sourceInfo']['source_url']."' target='_blank'>".L('PUBLIC_VIEW')."</a> <a href='javascript:void(0)' onclick='admin.ContentEdit({$v['comment_id']},\"delComment\",\"".L('PUBLIC_STREAM_DELETE').'","'.L('PUBLIC_STREAM_COMMENT')."\")'>".L('PUBLIC_STREAM_DELETE').'</a>';
        }
        $this->_listpk = 'comment_id';
        $this->displayList($listData);
    }
} // END class Admin extends Controller
class_alias('Apps\Information\Controller\Admin', 'AdminAction');
