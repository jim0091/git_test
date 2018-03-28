<?php
namespace app\Member\Controller;
use app\Member\Controller\Common;
use think\Controller;
use think\Request;
use think\Session;
use think\captcha;
use think\Validate;
use think\View;
use app\Member\Model\Rate;


class Price extends Common
{

    protected $view;
    public function __construct()
    {
        parent::_initialize();
        $this->view = new View();
        $this->price = new Rate();
    }


    /**
     * @name 房价首页页面
     * @return string
     * @throws \think\Exception
     */
    public function index(){
        $alogin = sesson::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $list = $this->price->getPrice($hotel_id);
        return $this->view->fetch('index',['list'=>$list]);
    }


    /**
     * @name 找出某个房型的价格
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
   public function typePrice(){
       $alogin = sesson::get('alogin');
       $hotel_id = $alogin['hotel_id'];
       $room_type = input('get.type');
       if(empty($room_type)){
           $this->error('房间类型缺失');
       }
       $data = $this->price->priceOne($hotel_id,$room_type);
       $this->assign('data',$data);
       return $this->view->fetch('typePrice');
   }


    /**
     * @name 增加房间价格
     */
    public function addPrice(){
        $alogin = sesson::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $param = input('post.');
        $rule = [
            'room_type'     =>'require',
            'price1'         =>'require',//字段目前不确定
            'time1'          =>'require',

        ];
        $msg = [
            'room_type.require'     =>'房间类型不能为空',
            'price1.require'        =>'房间价格不能为空',
            'time1.require'         =>'时间不能为空',
        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $room_type = $param['room_type'];
        $data['hotel_id'] = $hotel_id;
        $data['room_type'] = $room_type;
        $data['price1'] = $param['price1'];
        $data['time1'] = $param['time1'];
        $res = $this->price->priceAdd($data);
        if($res){
            $this->success('房间价格添加成功','Price/index');
        }else{
            $this->error('房间价格添加失败');
        }
    }


    /**
     * @name 修改房间价格
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changePrice(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $param = input('post.');
        $where['hotel_id'] = $hotel_id;
        $where['room_type'] = $param['room_type'];
        $data['price1'] = $param['price1'];//参数跟据情况传
        $data['time1'] = $param['time1'];
        $res = $this->price->priceChange($where,$data);
        if($res){
            $this->success('房间价格成功','Price/index');
        }else{
            $this->error('房间价格没有被修改');
        }
    }

}