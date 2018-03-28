<?php
namespace app\Member\Model;
use think\Model;
use think\Db;


class Pub extends Model{

    /**
     * @name 获取酒店的基本信息
     * @param $hotel_id
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHotel($hotel_id){
        if(empty($hotel_id)){
            return false;
        }else{
            $where['id'] = $hotel_id;
            $res = Db::table('hotel')->field('')->where($where)->find();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }


    /**
     * @name  修改酒店信息
     * @param $hotel_id
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function hotelChange($hotel_id,$data){
        if(empty($hotel_id)||empty($data)){
            return false;
        }else{
            $where['hotel_id'] = $hotel_id;
            $result = Db::table('hotel')->where($where)->update($data);
            if($result){
                return true;
            }else{
                return false;
            }
        }
    }
    /**
     * @name 查询酒店下的房间类型
     * @param $hotel_id
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRoom($hotel_id){
        if(empty($hotel_id)){
            return false;
        }else{
            $where['hotel_id'] = $hotel_id;
            $res = Db::table('room')->where($where)->field('')->group('room_type')->select();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }

    /**@name 查看某个房型具体情况
     * @param $hotel_id
     * @param $room_type
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function roomOne($hotel_id,$room_type){
        if(empty($hotel_id) || empty($room_type)){
            return false;
        }else{
            $where['hotel_id'] = $hotel_id;
            $where['room_type'] = $room_type;
            $res = Db::table('room')->where($where)->field('')->find();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 增加酒店房型
     * @param $room_type
     * @param $data
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function roomAdd($room_type,$data){
        if(empty($room_type)||empty($data)){
            return false;
        }else{
            $where['room_type'] = $room_type;
            $res = Db::table('room')->where($where)->find();
            if($res){
                return 1;
            }else{
                $result = Db::table('room')->insert($data);
                if($result){
                    return 2;
                }else{
                    return false;
                }
            }
        }
    }


    /**
     * @name 添加房型的图片
     * @param $data
     * @return bool
     */
    public function addImg($data){
        if(empty($data)){
            return false;
        }else{
            $res = Db::table('hotel_img')->insert($data);
            if($res){
                return true;//添加成功，返回主键ID
            }else{
                return false;
            }
        }
    }

    /**
     * @name 删除酒店房型
     * @param $hotel_id
     * @param $room_type
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function roomDel($hotel_id,$room_type){
        if(empty($hotel_id)||empty($room_type)){
            return false;
        }else{
            $where['hotel_id'] = $hotel_id;
            $where['room_type'] = $room_type;
            $res = Db::table('room')->where($where)->delete();
            if($res){
                $img = $this->delImg($where);//删除对应的房型图片
                if($img){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }


    /**
     * @name 删除酒店房型图片
     * @param $where
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public  function delImg($where){
        if(empty($where)){
            return false;
        }else{
            $res = Db::table('hotel_img')->where($where)->delete();//删除对应的房型图片
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }



    /**
     * $name 修改房间类型情况
     * @param $where
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function roomChange($where,$data){
        if(empty($where)||empty($data)){
            return false;
        }else{
            $result = Db::table('room')->where($where)->update($data);
            if($result){
                return true;
            }else{
                return false;
            }
        }
    }



    /**
     * 找出酒店下的对应人员
     * @param $user_id
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMember($user_id){
        if(empty($user_id)){
            return false;
        }else{
            $where['user_id'] = $user_id;
            $res = Db::table('member')->where($where)->field('')->select();
            if($res){
                return $res;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 在member表判断手机号是否存在
     * @param $phone
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkNumber($phone){
        if(empty($phone)){
            return false;
        }else{
            $where['phone'] = $phone;
            $res = Db::table('member')->where($where)->find();
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 跟据ID找出酒店下某个成员信息
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function memberOne($id){
        if(empty($id)){
            return false;
        }else{
            $where['id'] = $id;
            $res = Db::table('member')->where($where)->field('')->find();
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 酒店添加自己的管理人员
     * @param $data
     * @return bool
     */
    public function memberAdd($data){
        if(empty($data)){
            return false;
        }else{
            $result = Db::table('member')->insert($data);
            if($result){
                return true;
            }else{
                return false;
            }
        }
    }


    /**
     * @name 酒店修改成员信息
     * @param $id
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function memberEdit($id,$data){
        if(empty($id)||empty($data)){
            return false;
        }else{
            $where['id'] = $id;
            $res = Db::table('member')->where($where)->update($data);
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * @name 酒店删除成员
     * @param $id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function memberDel($id){
        if(empty($id)){
            return false;
        }else{
            $where['id'] = $id;
            $res = Db::table('member')->where($where)->delete();
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }
}