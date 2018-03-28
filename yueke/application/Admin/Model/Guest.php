<?php
namespace  app\Admin\Model;
use think\Model;
use think\Db;

class Guest extends Model{

    /**
     * @name:查找所有小程序端的用户
     * @author:  Blace  2018-03-08
     * @return bool|int|string
     */
    public function findAll($where =[]){
        $res = Db::table('guest')
            ->field('id,user_name,card,phone,sex,age,nick_name,address,email,guest_id,create_time')
            ->where($where)
            ->select();
        $count = 3;
        $list=[];
        if($res){
            foreach ($res as $v) {
                array_push($list, [
                        'id'          => $v['id'],
                        'user_name'   => $v['user_name'],
                        'card'        => $v['card'],
                       ]
                );
            }
           // print_r($list);exit;
            $data = [
                'rel'   => true,
                'msg'   => '读取成功',
                'list'  => $list,
                'count' => $count
            ];
            dump($data);exit;
            return $data;

            //返回数据
        }
        else{
            return false;
        }
    }

    /**
     * @name:根据输入的内容搜索数据
     * @notes:搜索暂定，初步方法前台页面有多个搜索框，如查找ID，根据在
     * @author:  Blace  2018-03-08
     * @return bool|int|string
     */
    public function findSearch($type,$data){
//        $where['guest_id|user_name|card|phone|sex|age|nick_name|address|email']
//            =array('like','%'.$data.'%');
        $res = Db::table('guest')
            ->field('user_name,card,phone,sex,age,nick_name,address,email,guest_id,create_time')
            ->whereLike($type,"%".$data."%")
            ->select();
        if($res){
            return $res;
        }
        else{
            return false;
        }
    }

    /**
     * @name:根据ID删除
     * @author:  Blace  2018-03-08
     * @return bool|int|string
     */
    public function deleteGuest($where = []){
        $res = Db::table('guest')->where($where)->delete();
        if($res){
            return true;
        }
        else{
            return false;
        }
    }

}
