<?php

namespace Apps\Event\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Event\Common;
use AdministratorAction   as Controller;
use Apps\Event\Model\Cate as CateModel;
use Apps\Event\Model\Event;
use Apps\Event\Model\Enrollment;

Common::import(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

/**
 * 活动后台管理控制器
 *
 * @package Apps\Event\Controller\Admin
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

        $this->pageTitle['index'] = '分类管理';
        $this->pageTitle['event'] = '活动管理';
        $this->pageTitle['audit_event'] = '待审列表';
        $this->pageTitle['del_event'] = '回收站';
        // $this->pageTitle['config']       = '常规配置';
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
        /* 分类管理 */
        $this->_addTab('index', 'Event/Admin/index');
        $this->_addTab('event', 'Event/Admin/event&is_audit=1');
        $this->_addTab('audit_event', 'Event/Admin/audit_event&is_audit=0');
        $this->_addTab('del_event', 'Event/Admin/del_event&del=1');
    }

/*=================================Action区域===========================================*/

    /**
     * 删除活动 - 管理员删除
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delEvent()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        $e_name = '';
        if($ids){
            foreach($ids as $v){
                $info = Event::getInstance()->get($v);
                $e_name .= $info['name'].'，';
            }
        }
        if (Event::getInstance()->adminDel($ids)) {
            //记录操作日志
            LogRecord('admin_system', 'adminDelEvent', array('name' => $e_name, 'k1' => '删除活动'), true);
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }


    /**
     * 删除活动彻底删除 - 管理员删除
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function deleteEvent()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        $e_name = '';
        if($ids){
            foreach($ids as $v){
                $info = Event::getInstance()->get($v);
                $e_name .= $info['name'].'，';
            }
        }
        if (Event::getInstance()->adminDelete($ids)) {
            //记录操作日志
            LogRecord('admin_system', 'adminDelete', array('name' => $e_name, 'k1' => '删除活动彻底删除'), true);
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }



    /**
     * 恢复活动 - 管理员恢复
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function recoverEvent()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        $e_name = '';
        if($ids){
            foreach($ids as $v){
                $info = Event::getInstance()->get($v);
                $e_name .= $info['name'].'，';
            }
        }

        if (Event::getInstance()->adminRecover($ids)) {
            //记录操作日志
            LogRecord('admin_system', 'adminRecoverEvent', array('name' => $e_name, 'k1' => '恢复活动'), true);
            $this->success('恢复成功');
        }
        $this->error('恢复失败');
    }


    /**
     * 通过活动 - 管理员通过
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function auditEvent()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        $e_name = '';
        if($ids){
            foreach($ids as $v){
                $info = Event::getInstance()->get($v);
                $e_name .= $info['name'].'，';
            }
        }
        if (Event::getInstance()->adminAuditEvent($ids)) {
            //记录操作日志
            LogRecord('admin_system', 'adminAuditEvent', array('name' => $e_name, 'k1' => '审核通过活动'), true);
            $this->success('通过成功');
        }
        $this->error('通过失败');
    }

    /**
     * 设置活动为推荐
     * @Author   Wayne[qiaobin@zhiyicx.com]
     * @DateTime 2016-12-30T15:10:38+0800
     * @return   [type]                     [description]
     */
    public function doRecommend()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        if (Event::getInstance()->adminRecommend($ids)) {
            $this->success('置顶成功');
        }
        $this->error('置顶失败');
    }

    /**
     * 取消推荐
     * @Author   Wayne[qiaobin@zhiyicx.com]
     * @DateTime 2016-12-30T15:10:38+0800
     * @return   [type]                     [description]
     */
    public function doUnRecommend()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        if (Event::getInstance()->adminUnRecommend($ids)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    /**
     * 活动管理
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function event()
    {
        $this->_listpk = 'eid';
        $this->_commonTab();
        array_push($this->appJsList, '/js/event.admin.js?v='.time());
        $this->pageKeyList = array('eid', 'name', 'time', 'location', 'manNumber', 'price', 'cate', 'user', 'action');
        $this->searchKey = array('eid', 'name', 'stime', 'etime', 'uid');
        $this->searchPostUrl = U('Event/Admin/event&is_audit=1',array('tabHash'=>'event'));

        /*搜索*/
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'search',
        ));
        /* 删除按钮 */
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'delete',
            'data' => array(
                'uri' => U('Event/Admin/delEvent', array('ids' => '__IDS__')),
            ),
        ));

        $this->displayList(Event::getInstance()->getAdminList());
    }

    /**
     * 待审列表
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function audit_event()
    {
        $this->_listpk = 'eid';
        $this->_commonTab();
        array_push($this->appJsList, '/js/event.admin.js?v='.time());
        $this->pageKeyList = array('eid', 'name', 'time', 'location', 'manNumber', 'price', 'cate', 'user', 'action');
        $this->searchKey = array('eid', 'name', 'stime', 'etime', 'uid');
        $this->searchPostUrl = U('Event/Admin/audit_event&is_audit=0',array('tabHash'=>'audit_event'));

        /*搜索*/
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'search',
        ));
        /* 删除按钮 */
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'delete',
            'data' => array(
                'uri' => U('Event/Admin/delEvent', array('ids' => '__IDS__')),
            ),
        ));
        /* 通过审核 */
        array_push($this->pageButton, array(
            'title' => '通过',
            'id' => 'audit_event',
            'data' => array(
                'uri' => U('Event/Admin/auditEvent', array('ids' => '__IDS__')),
            ),
        ));

        $this->displayList(Event::getInstance()->getAdminList());
    }


    /**
     * 回收站
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function del_event()
    {
        $this->_listpk = 'eid';
        $this->_commonTab();
        array_push($this->appJsList, '/js/event.admin.js?v='.time());
        $this->pageKeyList = array('eid', 'name', 'time', 'location', 'manNumber', 'price', 'cate', 'user', 'action');
        $this->searchKey = array('eid', 'name', 'stime', 'etime', 'uid');
        $this->searchPostUrl = U('Event/Admin/del_event&del=1',array('tabHash'=>'del_event'));

        /*搜索*/
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'search',
        ));
        /* 彻底删除按钮 */
        array_push($this->pageButton, array(
            'title' => '彻底删除',
            'id' => 'delete',
            'data' => array(
                'uri' => U('Event/Admin/deleteEvent', array('ids' => '__IDS__')),
            ),
        ));

        $this->displayList(Event::getInstance()->getAdminList());
    }

    /**
     * 分类管理
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        $this->_commonTab();
        array_push($this->appJsList, '/js/cate.admin.js');
        $this->pageKeyList = array('cid', 'name', 'leval', 'action');
        $this->_listpk = 'cid';
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'delete',
            'data' => array(
                'uri' => U('Event/Admin/delCate', array('ids' => '__IDS__')),
            ),
        ));
        array_push($this->pageButton, array(
            'title' => '添加',
            'id' => 'add',
            'data' => array(
                'uri' => U('Event/Admin/AddCate', array('tabHash' => 'index')),
            ),
        ));
        $this->displayList(CateModel::getInstance()->getAdminList());
    }

    /**
     * 添加分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function AddCate()
    {
        $id = intval(Common::getInput('id'));
        $this->_commonTab();
        $this->notEmpty = $this->pageKeyList = array('name', 'leval');
        $this->savePostUrl = U('Event/Admin/DoAddCate', array('id' => $id));
        $this->displayConfig(CateModel::getInstance()->getById($id));
    }

    /**
     * 处理添加/编辑分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function DoAddCate()
    {
        $id = Common::getInput('id');
        list($name, $leval) = Common::getInput(array('name', 'leval'), 'post');
        if (!$id || !CateModel::getInstance()->hasById($id)) {
            if (!CateModel::getInstance()->add($name, $leval)) {
                $this->error(CateModel::getInstance()->getError());
            }

        } elseif (!CateModel::getInstance()->update($id, $name, $leval)) {
            $this->error(CateModel::getInstance()->getError());
        }
        if($id){
            //记录操作日志
            LogRecord('admin_system', 'DoSaveCate', array('name' => $id, 'k1' => '修改活动分类'), true);
        }else{
            //记录操作日志
            LogRecord('admin_system', 'DoAddCate', array('name' => $name, 'k1' => '新增活动分类'), true);
        }
        $this->success('操作成功');
    }


    /**
     * 删除分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delCate()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        $ids = array_filter($ids);
        CateModel::getInstance()->delCate($ids);
        //记录操作日志
        $del_id = is_array($ids) ? implode(',',$ids) : $ids;
        LogRecord('admin_system', 'delCate', array('name' => $del_id, 'k1' => '删除活动分类'), true);
        $this->success('操作成功');
    }


    /**
     *
     * 获取活动报名详情
     *
     */
    public function event_enrollment(){
        $_REQUEST['tabHash'] = 'event';
        $eid = Common::getInput('eid');
        $this->_commonTab();
        array_push($this->appJsList, '/js/event.admin.js?v='.time());
        $this->pageKeyList = array('uid', 'name', 'num', 'phone', 'note', 'time', 'aduit');
        $this->searchKey = array('uid', 'name', 'phone');
        $this->searchPostUrl = U('Event/Admin/event_enrollment&eid='.$eid,array('tabHash'=>'event'));

        /*搜索*/
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'search',
        ));
//        /* 删除按钮 */
//        array_push($this->pageButton, array(
//            'title' => '删除',
//            'id' => 'delete',
//            'data' => array(
//                'uri' => U('Event/Admin/delEvent', array('ids' => '__IDS__')),
//            ),
//        ));
        $map = array();
        $map['eid'] = $eid;
        !empty($_POST['uid']) && $map['uid'] = intval($_POST['uid']);
        !empty($_POST['name']) && $map['name'] = t($_POST['name']);
        !empty($_POST['phone']) && $map['phone'] = t($_POST['phone']);
        $this->displayList(Enrollment::getInstance()->get_event_enrollment($map));
    }

} // END class Admin extends Controller
class_alias('\Apps\Event\Controller\Admin', 'AdminAction');
