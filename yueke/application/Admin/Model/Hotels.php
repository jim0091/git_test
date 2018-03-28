<?php
namespace  app\Admin\Model;
use think\Model;
use think\Db;
use think\Session;

class Hotels extends Model{


//    /**
//     * @name:查找所有酒店数据
//     * @return bool
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\ModelNotFoundException
//     * @throws \think\exception\DbException
//     */
//    public function findAll( $where = []){
//        $data = Db::table('hotel')
//            ->alias('a')
//            ->join('user b','a.user_id = b.user_id')
//            ->field('a.cn_name,a.user_id,b.user_name as name')
//            ->where($where)
//            ->order('b.create_time asc')
//            ->select();
//        if($data){
//            return $data;
//        }else{
//           return false;
//        }
//    }

    /**
     * @name:查询相关酒店信息
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findById($where = []){
        $data = Db::table('hotel')
            ->alias('a')
            ->join('user b','a.user_id = b.user_id')
            ->where($where)
            ->field('a.cn_name,a.en_name,a.sh_name,a.blurb,a.address,a.phone,a.room_num,b.user_name as name')
            ->find();
        if($data){
            return $data;
        }else{
            return false;
        }



    }


    /**
     * @name:根据状态查询数据
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findState($where = []){
        $data = Db::table('hotel')
            ->alias('a')
            ->join('user b','a.user_id = b.user_id')
            ->where($where)
            ->field('a.cn_name,a.en_name,a.sh_name,a.blurb,a.address,a.phone,a.room_num,a.user_id,b.user_name as name')
            ->select();
        if ($data) {
            return $data;
        } else {
            return false;
        }



    }


}
