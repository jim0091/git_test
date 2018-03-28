<?php

namespace Apps\Event\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Event\Common\BaseController as Controller;
use Apps\Event\Common;
use Apps\Event\Model\Cate;
use Apps\Event\Model\Event;

/**
 * 活动前台入口控制器
 *
 * @package Apps\Event\Controller\Index
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Index extends Controller
{
    /**
     * 活动首页
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        array_push($this->appJsList, '/js/index.js');
        list($cid, $area, $time, $wd) = Common::getInput(array('cid', 'area', 'time', 'wd'));
        list($cid, $area) = array(intval($cid), intval($area));

        /* 分类 */
        $this->assign('cates', Cate::getInstance()->getAll());

        /* 数据库不必重复的地区 */
        $this->assign('areas', Event::getInstance()->getArea());

        /* 列表数据 */
        $this->assign('list', Event::getInstance()->getList($cid, $area, $wd, $time));
        // var_dump(Event::getInstance());exit;

        /* 右侧 */
        $this->__RIGHT__();

        $this->display();
    }

    /**
     *
     * 活动列表查询
     *
     */
    public function event_list(){
        $data = Event::getInstance()->getList();
        foreach ($data['data'] as $key => &$v) {
            $v['image'] = getImageUrlByAttachId($v['image']) ?: '';
            if($v['etime'] <= time()){
                $v['e_status'] = '2';//已结束
            }elseif($v['stime'] <= time() && $v['etime'] > time()){
                $v['e_status'] = '1';//进行中
            }else{
                $v['e_status'] = '0';//未开始
            }
            /* 分类 */
            $v['cate'] = Cate::getInstance()->getById($v['cid']);
            $v['cate'] = $v['cate']['name'];
        }
        $this->assign('list', $data);
        $this->display();
    }


    /**
     *
     *
     * 活动详情查询
     *
     */
    public function event_info(){
        $id = intval($_GET['eid']);
        if (!$id or !($data = Event::getInstance()->get($id)) or $data['del']) {
            $this->error('您访问的活动不存在，或者已经被删除！');
        }
        /* 地区 */
        $data['area'] = model('Area')->getAreaById($data['area']);
        $data['area'] = $data['area']['title'];
        $data['city'] = model('Area')->getAreaById($data['city']);
        $data['city'] = $data['city']['title'];

        /* 分类 */
        $data['cate'] = Cate::getInstance()->getById($data['cid']);
        $data['cate'] = $data['cate']['name'];


        $data['image'] = getImageUrlByAttachId($data['image']) ?: '';

        //图片附件
        if (!empty($data['attach'])) {
            $attachids = explode(',', $data['attach']);
            foreach ($attachids as $key => $value) {
                $attach[] = getAttachUrlByAttachId($value);
            }
            $data['attach'] = $attach;
        }

        //视频附件
        if (!empty($data['video'])) {
            $videoids = explode(',', $data['video']);
            foreach ($videoids as $key2 => $value2) {
                $videoinfo = D('video')->where(array('video_id'=>$value2))->find();
                $videodata = array(
                    'url' => SITE_URL.$videoinfo['video_mobile_path'],
                    'imgurl' => SITE_URL.$videoinfo['image_path'],
                );

                $video[] = $videodata;
                $data['video'] = $video;
            }
        }

        if($data['etime'] < time()){
            $this->assign('ended', 1);
        }else{
            $this->assign('ended', 0);
        }
        //活动参加人数
        $data['u_number'] = $data['manNumber'] - $data['remainder'];

        $this->assign('data', $data);

        $this->display();
    }

} // END class Index extends Controller
class_alias('Apps\Event\Controller\Index', 'IndexAction');
