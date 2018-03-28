<?php
namespace app\Admin\Controller;
use app\Admin\Model\User;

/**
 * 酒店控制器
 * Class Hotel
 * @package app\Member\Controller
 */

class HotelUser extends Base
{
    /**
     * @name  酒店用户管理首页
     * @return string
     * @throws \think\Exception
     */
    public function index(){
        return $this->fetch('hotel');
    }


    /**
     * @name  后台酒店详情页
     * @return string
     * @throws \think\Exception
     */
    public function detailsUser($id){
        $user = new User();
        $data = $user->findByUser(['user_id' => $id]);
        if(!$data){
            $this->error('暂无相关信息','/admin/hotel');
        }
        return $this->fetch('user_details',['data'=>$data]);
    }

    /**
     * @name：禁用账号
     * @method：GET
     * @return bool
     */
    public function stopUser($id){
        $user = new User();
        $data = $user->updateUser(['user_id' => $id],['state' => 4]);
        if(!$data) {
            $this->error('禁用失败...');
        }
        $this->success('禁用成功...');
    }

    /**
     * @name：开启账号
     * @method：GET
     * @return bool
     */
    public function startUser($id){
        $user = new User();
        $data  = $user->updateUser(['user_id' => $id],['state' =>3]);
        if(!$data){
            $this->error('开启失败');
        }
        $this->success('开启成功...');
    }





}
