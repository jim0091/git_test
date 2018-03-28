<?php
namespace app\Member\Model;
use think\Model;
use think\Db;
use think\Paginator;


class Rate extends Model
{


    /**
     * @name 找出该酒店所有房型的价格
     * @param $hotel_id
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPrice($hotel_id){
        if(empty($hotel_id)){
            return false;
        }else{
            $where['a.hotel_id'] = $hotel_id;
            $where['b.hotel_id'] = $hotel_id;
            $res = Db::table('room')->alias('a')->where($where)->field('')->join('price b','a.room_type = b.room_type')->select();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }

    /**
     * @nama 找出酒店某个房型的价格
     * @param $hotel_id
     * @param $room_type
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function priceOne($hotel_id,$room_type){
        if(empty($hotel_id) || empty($room_type)){
            return false;
        }else{
            $where['a.hotel_id'] = $hotel_id;
            $where['a.room_type'] = $room_type;
            $where['b.hotel_id'] = $hotel_id;
            $res = Db::table('room')->alias('a')->where($where)->field('')->join('price b','a.room_type = b.room_type')->find();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 增加价格
     * @param $data
     * @return bool
     */
    public function priceAdd($data){
        if(empty($data)){
            return false;
        }else{
            $res = Db::table('price')->insert($data);
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 修改房间价格
     * @param $where
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function priceChange($where,$data){
        if(empty($where)||empty($data)){
            return false;
        }else{
            $result = Db::table('price')->where($where)->update($data);
            if($result){
                return true;
            }else{
                return false;
            }
        }
    }



}