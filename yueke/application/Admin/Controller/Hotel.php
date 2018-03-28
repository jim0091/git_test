<?php
namespace app\Admin\Controller;
use app\Admin\Model\Hotels;


/**
 * 酒店控制器
 * Class Hotel
 * @package app\Member\Controller
 */

class Hotel extends Base
{
    /**
     * @name  后台酒店管理首页
     * @return string
     * @throws \think\Exception
     */
    public function index(){
        return $this->fetch('index');
    }

    public function stateHotel($value){
        $hotel = new Hotels();
        $data = $hotel->findState(['b.state' => $value]);
        if(!$data){
            $this->error('暂无相关信息','/admin/hotel');
        }
        return $this->fetch('hotel',['data'=>$data]);
    }

    /**
     * @name  后台酒店详情页
     * @return string
     * @throws \think\Exception
     */
    public function detailsHotel($id){
        $hotel  = new Hotels();
        $data = $hotel->findById(['a.user_id' => $id]);
        if(!$data){
            $this->error('暂无相关信息','/admin/hotel');
        }
        return $this->fetch('hotel_details',['data'=>$data]);
    }



}
