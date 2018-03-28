<?php
/**
 * 后台，关键词搜索统计管理控制器.
 *
 * @author zhl
 *
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class KeywordStatisticAction extends AdministratorAction
{

    private function _initPageTab(){
        $this->pageTab[] = array('title' => '关键词搜索列表', 'tabHash' => 'index', 'url' => U('admin/KeywordStatistic/index'));
    }

    /**
     * 邀请码管理 - 邀请码列表.
     */
    public function index()
    {
        $_REQUEST['tabHash'] = 'index';
        // tab选项
        $this->_initPageTab();
        $this->pageKeyList = array('id', 'keyword', 'type','result', 'all_search','month_search','week_search','day_search', 'DOACTION');
        $this->searchKey = array('id', 'keyword');
        $this->pageButton[] = array('title' => '搜索', 'onclick' => "admin.fold('search_form')");
        $this->pageButton[] = array('title' => '删除', 'onclick' => 'admin.delKeyword()');

        $map = array();
        !empty($_POST['id']) && $map['id'] = intval($_POST['id']);
        !empty($_POST['keyword']) && $map['keyword'] = t($_POST['keyword']);

        $list = model('KeywordStatistic')->getList($map,20);
        foreach ($list['data'] as &$value) {
            switch ($value['type'])
            {
                case 1:
                    $value['type'] = '匿名动态';
                    break;
                case 2:
                    $value['type'] = '用户';
                    break;
                case 3:
                    $value['type'] = '活动';
                    break;
                case 4:
                    $value['type'] = '文章';
                    break;
                default:
                    $value['type'] = '实名动态';
            }

            $value['DOACTION'] = ' <a href="javascript:;" onclick="admin.delKeyword(\''.$value['id'].'\')">[删除]</a>';
        }

        $this->_listpk = 'id';
        $this->allSelected = true;
        $this->displayList($list);
    }


    /**
     *
     * 删除关键词
     *
     */
    public function doDelKeyword(){
        $c_map = array();
        $c_map['id'] = array('in', $_POST['id']);
        if (empty($c_map['id'])) {
            exit(json_encode(array('status' => '0', 'info' => '参数错误')));
        }
        $res = model('KeywordStatistic')->where($c_map)->delete();
        if(!$res){
            $this->error('删除失败，请重试');
        }else{
            $this->assign('jumpUrl', U('admin/KeywordStatistic/index'));
            $this->success('删除成功');
        }
    }


}
