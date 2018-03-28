<?php
namespace  app\Admin\Model;
use think\Model;
use think\Db;

class Admin extends Model{


    /**
     * @name:判断手机号是否已被注册
     * @author:  Blace  2018-03-08
     * @param $phone
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isPhone($where = []){
        $res = Db::table('admin')->field('phone')->where($where)->find();
        if($res){
            return false;
        }else{
           return true;
        }
    }


    /**
     * @name:判断用户名是否已被注册
     * @author:  Blace  2018-03-08
     * @param $username
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isUser($where = []){
        $res = Db::table('admin')->field('user_name')->where($where)->find();
        if($res){
            return false;
        }else{
            return true;
        }
    }


    /**
     * @name:新增
     * @author:  Blace  2018-03-08
     * @param:$data
     * @return bool|int|string
     */
    public function addAdmin( $data = []){
        $res = Db::table('admin')->insert($data);
        if($res){
            return true;//添加成功，返回主键ID
        }else{
            return false;
        }


    }
    /**
     * @name:删除
     * @author:  Blace  2018-03-08

     * @return bool
     */
    public function deleteAdmin($where = []){
        if(empty($id)){
            return false;
        }else{
            $res = Db::table('admin')->where($where)->delete();
            if($res){
                return true;//删除成功
            }else{
                return false;
            }
        }
    }

    /**
     * @name:修改admin表的内容
     * @author:  Blace  2018-03-08
     * @param:$id
     * @return bool
     */
    public function updateAdmin($where =[],$data =[]){
        $res = Db::table('admin')
            ->where($where)
            ->update($data);
        if($res){
            return true;//修改成功
        }else{
            return false;
        }


    }


    /**
     * @name:查找所有管理员账号,除去自己账号
     * @author:  Blace  2018-03-08
     * @param:$data
     * @return bool|int|string
     */
    public function findAdmin($id){
        $data = Db::table('admin')
            ->field('admin_id,user_name,state,role')
            ->where('admin_id','<>',$id)
            ->select();
        //管理所有账号，可以删除，修改密码，不包括自己已登录的账号；
        if($data){
            return $data;
            //返回数据
        }
        else{
            return false;
        }
    }

    /**
     * @name:根据adminID查数据
     * @author:  Blace  2018-03-08
     * @param:$data
     * @return bool|int|string
     */
    public function findById($where =[]){
        $data = Db::table('admin')
            ->field('user_name,admin_id,phone,state,role,create_time,avatar')
            ->where($where)
            ->find();
        //根据ID查询数据
        if(!$data){
            return false;
            //返回数据
        }
        return $data;


    }

    /**
     * @name:判断是否为超级管理员
     * @author:  Blace  2018-03-08
     * @param:$data
     * @return bool|int|string
     */
    public function checkRole($where = []){
        $res = Db::table('admin')
            ->field('role')
            ->where($where)
            ->find();
        if($res){
            if ($res['role']==2) {
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }




    }



    /**
      * @name:登录时判断用户名和密码是否匹配
     * @param: $account
     * @author:  Blace  2018-03-08
     * @return bool
     */
    public function checkAccount($where = []){
            $res = Db::table('admin')
                ->field('password,state,user_name,admin_id')
                ->where($where)
                ->find();
            //使用账号登录
            if($res){
                return $res;
            }else{
                return false;
            }

    }

    /**
     * @name:使用手机号码进行登录
     * @param: $phone
     * @author:  Blace  2018-03-13
     * @return bool
     */
    public function checkPhone($where = []){
        $res = Db::table('admin')
            ->field('password,state,user_name,admin_id')
            ->where($where)
            ->find();
        //判断是否使用账号或者手机登录
        if($res){
            return $res;
        }else{
            return false;
        }


    }
}
