<?php
namespace app\Member\Model;
use think\Model;
use think\Db;
use think\Paginator;


/**
 * 订单
 * Class Indent
 * @package app\Member\Model
 */
class Indent extends Model{

    /**
     * @name 跟据酒店的ID找出酒店的所有订单
     * @param $hotel_id
     * @param int $num
     * @return bool|Paginator
     * @throws \think\exception\DbException
     */
    public function orderAll($hotel_id,$num=10){
        if(empty($hotel_id)){
            return false;
        }else{
            $where['a.hotel_id'] = $hotel_id;
            $where['b.hotel_id'] = $hotel_id;
            $list = Db::table('order')->alias('a')->where($where)->field('')->join('room b','a.room_type = b.room_type')->paginate($num);
            if($list){
                return $list;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 找出某条订单的信息
     * @param $hotel_id
     * @param $order_id 订单ID
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function orderOne($hotel_id,$order_id){
        if(empty($hotel_id)||empty($order_id)){
            return false;
        }else{
            $where['a.hotel_id'] = $hotel_id;
            $where['a.order_id'] = $order_id;
            $where['b.hotel_id'] = $hotel_id;
            $result = Db::table('order')->alias('a')->where($where)->field('')->join('room b','a.room_type = b.room_type')->find();
            if($result){
                return $result;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 跟据条件查询订单
     * @param $where
     * @param int $num
     * @return bool|Paginator
     * @throws \think\exception\DbException
     */
    public function orderSelect($where,$num=10){
        if(empty($where)){
            return false;
        }else{
            $result = Db::table('order')->alias('a')->where($where)->field('')->join('room b','a.room_id = b.room_id')->paginate($num);
            if($result){
                return $result;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 处理订单，改变订单的状态
     * @param $hotel_id
     * @param $order_id
     * @param $order_state
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function checkState($hotel_id,$order_id,$order_state){
        if(empty($hotel_id)||empty($order_id)||empty($order_state)){
            return false;
        }else{
            $where['$order_id'] = $order_id;
            $res = Db::table('order')->where($where)->find();
            if($res['order_state']==$order_state){
                return true;
            }else{
                $data['order_state'] = $order_state;
                $res1 = Db::table('order')->where($where)->update($data);
                if($res1){
                    return true;
                }else{
                    return false;
                }
            }
        }
    }



}