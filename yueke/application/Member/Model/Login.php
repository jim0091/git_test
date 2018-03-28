<?php
namespace  app\Member\Model;
use  think\Model;
use  think\Db;

/**登陆注册
 * Class Login
 * @package app\Member\Model
 */
class Login extends Model{


    /**
     * @name 从数据库里找验证码
     * @param $BizId
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function code($BizId){
        if(empty($BizId)){
            return false;
        }else{
            $where['BizId'] = $BizId;
            $res = Db::table('sms')->field('auth_code')->find();
            if($res){
                return $res;
            }else{
                return false;
            }
        }

    }
    /**
     * @name:判断手机号是否已被注册
     * author:  wh  2018-03-05
     * @param $PhoneNumber
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isPhone($PhoneNumber){
        $where['phone'] = $PhoneNumber;
        $res = Db::table('user')->where($where)->find();
        if($res){
            return true;
        }else{
           return false;
        }
    }


    /**
     * @name 插入注册时的电话
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function phoneAdd($data){
        if(empty($data)){
            return false;
        }else{
            $res = Db::table('user')->insert($data);
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }


    /**
     * @name:注册时新增酒店信息
     * @author:  wh  2018-03-05
     * @param:$data
     * @return bool|int|string
     */
    public function addHotel($data){
        if(empty($data)){
            return false;
        }else{
            $res = Db::table('hotel')->insert($data);
            if($res){
                return $res;//添加成功，返回主键ID
            }else{
                return false;
            }
        }
    }


    /**
     * @name 添加酒店的图片
     * @param $data
     * @return bool|int|string
     */
    public function hotelImg($data){
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
     * @name 补充个人信息
     * @param $phone
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateUser($phone,$data){
        if(empty($phone)||empty($data)){
            return false;
        }else{
            $where['phone'] = $phone;
          $res = Db::table('user')->where($where)->update($data);
          if($res){
              return true;
          }else{
              return false;
          }
        }
    }


    /**
     * @name:判断电话是否存在
     * @author:  wh  2018-03-05
     * @param $where
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkAccount($account){
        if(empty($account)){
            return false;
        }else{
            $where['phone'] = $account;
            $res = Db::table('user')->where($where)->find();//先在user表里面判断
            if($res){
                return true;
            }else{//user表里面不存在的话，再去member里面判断
                $where1['phone'] = $account;
                $res1 = Db::table('member')->where($where1)->find();
                if($res1){
                    return true;
                }else{
                    return false;
                }
            }
        }
    }

    /**
      * @name:登录时判断用户名和密码是否匹配
     * @param: $account
     * @param :$shpassword
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkPassword($account,$shpassword){
        if(empty($account) || empty($shpassword)){
            return false;
        }else {
            $where['phone'] = $account;
            $findUserPhone = Db::table('user')->where($where)->find();
            if($findUserPhone) {
                $where['password'] = $shpassword;
                $res = Db::table('user')->where($where)->find();
                if($res) {
                    return $res;
                }else{
                    return false;
                }
            }else{
                $where['password'] = $shpassword;
                $res1 = Db::table('member')->where($where)->find();
                if($res1){
                    $where1['user_id'] = $res1['user_id'];
                    $result = Db::table('user')->where($where1)->find();
                    $res1['state'] = $result['state'];
                    if( $res1['state'] == 2){
                        $res1['hotel_id'] = $result['hotel_id'];
                        return $res1;
                    }else{
                        return $res1;
                    }
                }else{
                    return false;
                }
            }
        }
    }

    /**
     * @name 登录连续错误三次，将主表状态改为异常
     * @param $account
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function checkState($account){
        if(empty($account)){
            return false;
        }else{
            $where['phone']=$account;
            $res = Db::table('user')->where($where)->find();
            if($res){
                if($res['state'] != 4 ){
                    $data['state'] = 4;
                    Db::table('user')->where($where)->update($data);
                }
            }else{
                $result = Db::table('member')->where($where)->find();
                $where1['user_id'] = $result['user_id'];
                $find =  Db::table('user')->where($where1)->find();
                if($find['state'] != 4){
                    $data['state'] = 4;
                    Db::table('user')->where($where)->update($data);
                }
            }
        }
    }

    /**
     * @name 修改密码
     * @param $phone
     * @param $password
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function changePassword($phone,$password){
        if(empty($phone)||empty($password)){
            return false;
        }else{
            $where['phone'] = $phone;
            $findPhone = Db::table('user')->where($where)->find();
            if($findPhone){
                if($findPhone['password'] == $password){
                    return true;
                }else{
                    $data['password'] = $password;
                    $data['update_time'] = time();
                    $change = Db::table('user')->where($where)->update($data);
                    if($change){
                        return true;
                    }else{
                        return false;
                    }
                }
            }else{
                $findPhone1 = Db::table('member')->where($where)->find();
                if($findPhone1){
                    if($findPhone1['password'] == $password){
                        return true;
                    }else{
                        $data['password'] = $password;
                        $data['update_time'] = time();
                        $change = Db::table('member')->where($where)->update($data);
                        if($change){
                            return true;
                        }else{
                            return false;
                        }
                    }
                }else{
                    return false;
                }
            }
        }
    }




}
