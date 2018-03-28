<?php
namespace app\Member\Model;
use think\Model;
use think\Db;
use think\Paginator;

/**
 * 评论
 * Class Review
 * @package app\Member\Model
 */
class Review extends Model{

    /**
     * @name 找出酒店的所有订单评论
     * @param $hotel_id
     * @param int $num
     * @return bool|Paginator
     * @throws \think\exception\DbException
     */
    public function allComment($hotel_id,$num = 10){
        if(empty($hotel_id)){
            return false;
        }else{
            $where['b.hotel_id'] = $hotel_id;
            $res = Db::table('comment')->alias('a')->where($where)->field('')->join('order b','a.order_id = b.order_id')->paginate($num);
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 找出评论里房间的类型
     * @param $room_id
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findRoom($room_id){
        if(empty($room_id)){
            return false;
        }else{
            $where['id'] = $room_id;
            $res = Db::table('room')->where($where)->field('')->find();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 跟据评论ID找出某条评论
     * @param $comment_id
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function commentOne($comment_id){
        if(empty($comment_id)){
            return false;
        }else{
            $where['a.comment_id'] = $comment_id;
            $res = Db::table('comment')->alias('a')->where($where)->field('')->join('order b','a.order_id = b.order_id')->find();
            if($res){
                    $where['id'] = $res['room_id'];
                    $res1 = Db::table('room')->where($where)->find();
                    $res['room_type'] = $res1['room_type'];
                    return $res;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 跟据多个条件查询评论
     * @param $where
     * @param int $num
     * @return bool|Paginator
     * @throws \think\exception\DbException
     */
    public function commentSelect($where,$num=10){
        if(empty($where)){
            return false;
        }else{
            $res = Db::table('comment')->alias('a')->where($where)->field('')->join('order b','a.order_id = b.order_id')->paginate($num);
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 审核评论
     * @param $comment_id
     * @param $comment_state
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function checkComment($comment_id,$comment_state){
        if(empty($comment_id) || empty($comment_state)){
            return false;
        }else{
            $where['comment_id'] = $comment_id;
            $data['comment_state'] = $comment_state;
            $res = Db::table('comment')->where($comment_id)->update($data);
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }
}