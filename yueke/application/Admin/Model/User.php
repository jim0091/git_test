<?php
namespace  app\Admin\Model;
use phpDocumentor\Reflection\Types\String_;
use think\Model;
use think\Db;
use think\Session;

class User extends Model{


    /**
     * @name:查找审核申请
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findAll($where = []){
        $res = Db::table('user')
            ->alias('a')
            ->join('hotel b','a.user_id = b.user_id')
            ->field('a.user_id,a.user_name,a.state,a.create_time,b.cn_name')
            ->where($where)
            ->order('a.create_time asc')
            //未审核的排前面，按时间先后
            ->select();
        if($res){
            return $res;
        }else{
           return false;
        }
    }

    /**
     * @name:查询状态为已拒绝的数据
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\exception\DbException
     */
    public function findRefuseState($where = []){
        if(empty($state)){
            return false;
        }
        else {
            $res = Db::table('user')
                ->alias('a')
                ->join('hotel b','a.user_id = b.user_id')
                ->field('a.user_id,a.user_name,a.state,a.create_time,b.cn_name,a.reason')
                ->where($where)
                ->order('a.create_time asc')
                ->select();
            if ($res) {
                return $res;
            } else {
                return false;
            }
        }
    }

    /**
     * @name:根据所选的账号查询数据
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findById($where = []){
        $res = Db::table('user')
            ->alias('a')
            ->join('hotel b','a.user_id = b.user_id')
            ->field('a.user_id,a.user_name,a.phone,a.email,a.state,a.create_time,
                        b.cn_name,b.phone,b.license,b.blurb,b.address')
            ->where($where)
            ->find();
        //查找相对应的用户ID数据
        if ($res) {
            return $res;
        } else {
            return false;
        }

    }

    /**
     * @name:根据所选的账号查询数据
     * @return String
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findByUser($where = []){
        $res = Db::table('user')
            ->field('user_name,phone,email,avatar,state,user_id')
            ->where($where)
            ->find();
        //查找相对应的用户ID数据
        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    /**
     * @name:酒店用户修改·操作
     * @param:$where,$data
     * @return bool|int|string
     */
    public function updateUser($where = [],$data = []){
        $res = Db::table('user')
            ->where($where)
            ->update($data);
        if($res){
            return true;//审核成功
        }else{
            return false;
        }


    }



}
