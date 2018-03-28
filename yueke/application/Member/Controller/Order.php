<?php
namespace app\Member\Controller;
use think\Controller;
use app\Member\Controller\Common;
use think\Request;
use think\Session;
use think\captcha;
use think\Validate;
use think\View;
use app\Member\Model\Indent;


/**
 * @name 订单
 * Class Order
 * @package app\Member\Controller
 */
class Order extends Common{
    protected $view;
    public function __construct()
    {
        parent::_initialize();
        $this->view = new View();
        $this->order = new Indent();

    }


    /**
     * @name 订单首页
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $alogin = Session::get('alogin');
        $hotel_id= $alogin['hotel_id'];
        $list = $this->order->orderAll($hotel_id);
        if($list){
            foreach ($list as &$value){
                $list['join_time'] = date('Y-m-d',$value['join_time']);
                $list['leave_time'] = date('Y-m-d',$value['leave_time']);
                $list['order_time'] = date('m-d H:i',$value['order_time']);
                switch ($value['order_state']){
                    case '1':
                        $list['order_state'] = '待处理';
                        break;
                    case '2':
                        $list['order_state'] = '已接单';
                        break;
                    case '3':
                        $list['order_state'] = '已拒单';
                        break;
                    case '4':
                        $list['order_state'] = '已取消';
                        break;
                    case '5':
                        $list['order_state'] = '已完成';
                        break;
                };
            }
            $page = $list->render();
            $this->assign('list',$list);
            $this->assign('page',$page);
        }
        return $this->view->fetch('index');
    }


    /**
     * @name ajax请求返回某条订单的信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findOrder(){
        $alogin = Session::get('alogin');
//        dump($alogin);
        $hotel_id= $alogin['hotel_id'];
        $order_id = input('post.order_id');
        if(empty($order_id)){
            $res['data'] = "";
            $res['msg'] = "订单ID缺失";
            $res['code'] = "0";
            echo json_encode($res);
            exit();
        }
        $data = $this->order->orderOne($hotel_id,$order_id);
        if($data){
            $res['data'] = $data;
            $res['msg'] = "订单信息查找成功";
            $res['code'] = "1";
            echo json_encode($res);
            exit();
        }else{
            $res['data'] = "";
            $res['msg'] = "订单信息不存在";
            $res['code'] = "0";
            echo json_encode($res);
            exit();
        }

    }


    /**
     * @name 根据条件查询订单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function selectOrder(){
        $alogin = Session::get('alogin');
        $hotel_id= $alogin['hotel_id'];
//        $where['a.hotel_id'] = $hotel_id;
        $parm = input('post.');
        if(!empty($parm['state'])){
            $where['a.order_state'] = $parm['state'];
        }
        if(!empty($parm['order_id'])){
            $where['a.order_id'] = $parm['order_id'];
        }
        if(!empty($parm['name'])){
            $where['b.name'] = $parm['name'];
        }
        if(!empty($parm['j_time']) && !empty($parm['l_time'])){
            $where['a.join_time'] = array('lt',$parm['j_time']);
            $where['a.leave_time'] = array('gt',$parm['l_time']);
        }elseif(!empty($parm['j_time'])){
            $where['a.join_time'] = array('lt',$parm['j_time']);
        }elseif (!empty($parm['l_time'])){
            $where['a.leave_time'] = array('gt',$parm['l_time']);
        }
        if(!empty($parm['room_type'])){
            $where['b.room_type'] = $parm['room_type'];
        }
        if(!empty($parm['confirm'])){
            $where['a.confirm'] =  array('LIKE', '%'.($parm['state']).'%');
        }
        $where['b.hotel_id'] = $hotel_id;
        $data = $this->order->orderSelect($where);
        if($data){
            $page = $data->render();
            $this->assign('page',$page);
            $this->assign('data',$data);
        }

        return $this->view->fetch('index');
    }

    /**
     * @name 跟据订单状态查询订单(1.待处理,2.已接单，3.进行中，4.已取消,5.已完成)
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function stateOrder(){
        $alogin = Session::get('alogin');
        $hotel_id= $alogin['hotel_id'];
        $order_state = input('get.state');
        if(empty($order_state)){
            $this->error('订单状态缺失');
        }else{
           $where['order_state']  = $order_state;
            $where['hotel_id'] =$hotel_id;
            $list = $this->order->orderSelect($where);
            if($list){
                foreach ($list as &$value) {
                    $list['join_time'] = date('Y-m-d', $value['join_time']);
                    $list['leave_time'] = date('Y-m-d', $value['leave_time']);
                    $list['order_time'] = date('m-d H:i', $value['order_time']);
                    switch ($value['order_state']) {
                        case '1':
                            $list['order_state'] = '待处理';
                            break;
                        case '2':
                            $list['order_state'] = '已接单';
                            break;
                        case '3':
                            $list['order_state'] = '进行中';
                            break;
                        case '4':
                            $list['order_state'] = '已取消';
                            break;
                        case '5':
                            $list['order_state'] = '已完成';
                            break;
                    };
                }
                $page = $list->render();
                $this->assign('page',$page);
                $this->assign('data',$list);
            }
            return $this->view->fetch('stateOrder');
        }
    }

    /**
     * @name 处理订单，改变订单的状态
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function dealOrder(){
        $alogin = Session::get('alogin');
        $hotel_id= $alogin['hotel_id'];
        $parm = input('post.');
        $order_id = $parm['order_id'];
        if(empty($order_id)){
            $res['msg'] = "订单ID缺失";
            $res['code'] = "0";
            echo json_encode($res);
            exit;
        }else{
            $order_state = $parm['order_state'];
            if(empty($order_state)){
                $res['msg'] = "请选择定订单状态";
                $res['code'] = "0";
                echo json_encode($res);
                exit;
            }
            $result = $this->order->checkState($hotel_id,$order_id,$order_state);
            if($result){
                $res['msg'] = "订单处理成功";
                $res['code'] = "1";
                echo json_encode($res);
                exit;
            }else{
                $res['msg'] = "订单处理失败";
                $res['code'] = "0";
                echo json_encode($res);
                exit;
            }
        }
    }

}

