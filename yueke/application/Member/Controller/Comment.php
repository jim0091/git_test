<?php
namespace app\Member\Controller;
use think\Controller;
use app\Member\Controller\Common;
use think\Request;
use think\Session;
use think\captcha;
use think\Validate;
use think\View;
use app\Member\Model\Review;

/**
 * 评论
 * Class Comment
 * @package app\Member\Controller
 */
class Comment extends Common{
    public function __construct( )
    {
        parent::_initialize();
        $this->view = new View();
        $this->comment = new Review();
    }

    /**
     * @name 评论首页
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function index(){
        $alogin = session::get('alogin');
//        dump($alogin);die;
        $hotel_id = $alogin['hotel_id'];
        $comment = $this->comment->allComment($hotel_id);
        if($comment){
            foreach ($comment as  &$value){
                $comment['comment_time'] = date('Y-m-d H:i:s',$value['comment_time']);
                $room_id= $value['room_type'];
                $room = $this->comment->findRoom($room_id);
                $comment['room_type'] = $room['room_type'];
                switch ($value['comment_state']){
                    case '1':
                        $comment['comment_state'] = '未审核';
                        break;
                    case '2':
                        $comment['comment_state'] = '审核通过';
                        break;
                    case '3':
                        $comment['comment_state'] = '审核未通过';
                        break;
                };
            }
            $page = $comment->render();
            $this->assign('list',$comment);
            $this->assign('page',$page);
        }
        return $this->view->fetch('index');
    }

    /**
     * @name 跟据评论ID查看评论详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findComment(){
        $comment_id = input('post.comment_id');
        if(empty($comment_id)){
            $res['msg'] = "评论ID缺失";
            $res['code'] = "0";
            echo json_encode($res);
            exit();
        }else{
            $data = $this->comment->commentOne($comment_id);
            if($data){
                $res['data'] = $data;
                $res['msg'] = "评论信息查找成功";
                $res['code'] = "1";
                echo json_encode($res);
                exit();
            }else{
                $res['msg'] = "评论信息不存在";
                $res['code'] = "0";
                echo json_encode($res);
                exit();
            }
        }
    }


    /**
     * @nama 跟据多个条件查询评论
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function selectComment(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $where['b.hotel_id'] = $hotel_id;
        $parm = input('post.');
        if(!empty($parm['state'])){
            $where['a.comment_state'] = $parm['state'];
        }
        if(!empty($parm['j_time']) && !empty($parm['l_time'])){
            $where['a.time'] = array('lt',$parm['j_time']);
            $where['a.time'] = array('gt',$parm['l_time']);
        }elseif(!empty($parm['j_time'])){
            $where['a.time'] = array('lt',$parm['j_time']);
        }elseif (!empty($parm['l_time'])){
            $where['a.time'] = array('gt',$parm['l_time']);
        }
        if(!empty($parm['comment'])){
            $where['a.comment'] =  array('LIKE', '%'.($parm['state']).'%');
        }
        $data = $this->comment->commentSelect($where);
        if($data){
            foreach ($data as &$value) {
                $data['comment_time'] = date('Y-m-d H:i:s', $value['comment_time']);
                $room_id = $value['room_id'];
                $room = $this->comment->findRoom($room_id);
                $data['room_type'] = $room['room_type'];
                switch ($value['comment_state']) {
                    case '1':
                        $data['comment_state'] = '未审核';
                        break;
                    case '2':
                        $data['comment_state'] = '审核通过';
                        break;
                    case '3':
                        $data['comment_state'] = '审核未通过';
                        break;
                };
                $page = $data->render();
                $this->assign('page', $page);
                $this->assign('data', $data);
            }
        }
        return $this->view->fetch('index');
    }


    /**
     * @name 跟据评论状态查询评论(1.未审核,2.审核通过,3.审核未通过)
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function stateComment(){
        $alogin = Session::get('alogin');
        $hotel_id= $alogin['hotel_id'];
        $comment_state = input('get.state');
        if(empty($comment_state)){
            $this->error('评论状态缺失');
        }else{
            $where['a.comment_state'] = $comment_state;
            $where['b.hotel_id'] = $hotel_id;
            $data = $this->comment->commentSelect($where);
            if($data){
                foreach ($data as &$value) {
                    $data['comment_time'] = date('Y-m-d H:i:s', $value['comment_time']);
                    $room_id = $value['room_id'];
                    $room = $this->comment->findRoom($room_id);
                    $data['room_type'] = $room['room_type'];//将订单中的房间信息赋过去
                    switch ($value['comment_state']) {
                        case '1':
                            $data['comment_state'] = '未审核';
                            break;
                        case '2':
                            $data['comment_state'] = '审核通过';
                            break;
                        case '3':
                            $data['comment_state'] = '审核未通过';
                            break;
                    };
                    $page = $data->render();
                    $this->assign('page', $page);
                    $this->assign('data', $data);
                }
            }
            return $this->view->fetch('index');
        }

    }


    /**
     * @name 评论审核操作
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function dealComment(){
        $comment_id = input('post.comment_id');
        if(empty($comment_id)){
            $res['msg'] = "评论ID缺失";
            $res['code'] = "0";
            echo json_encode($res);
            exit();
        }
        $comment_state = input('post.state');
        if(empty($comment_state)){
            $res['msg'] = "评论状态缺失";
            $res['code'] = "0";
            echo json_encode($res);
            exit();
        }
        $result = $this->comment->checkComment($comment_id,$comment_state);
        if($result){
            $res['msg'] = "评论审核成功";
            $res['code'] = "1";
            echo json_encode($res);
            exit();
        }else{
            $res['msg'] = "评论审核失败";
            $res['code'] = "0";
            echo json_encode($res);
            exit();
        }
    }

}