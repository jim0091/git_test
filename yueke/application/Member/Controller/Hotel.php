<?php
namespace app\Member\Controller;
use app\Member\Controller\Common;
use think\Controller;
use think\Request;
use think\Session;
use think\captcha;
use think\Validate;
use think\View;
use app\Member\Model\Pub;

/**
 * @name 酒店
 * Class Hotel
 * @package app\Member\Controller
 */
class Hotel extends Common{


    public function __construct()
    {
        parent::_initialize();
        $this->view = new View();
        $this->hotel = new Pub();
    }


    /**
     * @name 酒店信息首页
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $data = $this->hotel->getHotel($hotel_id);
        $this->assign('data',$data);
        return $this->view->fetch('index');
    }


    /**
     * @name 修改（补充）酒店信息
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changeHotel(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $param = input('post.');
        $rule = [
            'cn_name'     =>'require',
            'address'     =>'require',
            'lat'         =>'float',
            'lng'         =>'float',
        ];
        $msg = [
            'cn_name.require'       =>'酒店中文名称不能为空',
            'address.require'       =>'酒店地址不能为空',
            'lat.float'             =>'酒店经度必须为浮点型',
            'lng.float'             =>'酒店纬度必须为浮点型',
        ];
        $validate = new validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if($error){
            $this->error($error);
        }
        $data['cn_name'] = $param['cn_name'];
        $data['address'] = $param['address'];
        $data['lat'] = $param['lat'];
        $data['lng'] = $param['lng'];
        $res = $this->hotel->hotelChange($hotel_id,$data);
        if($res){
            $this->success('修改酒店信息成功','hotel/index');
        }else{
            $this->error('修改信息失败');
        }
    }


    /**
     * @name 找出酒店下的所有房间类型
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function room(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $data = $this->hotel->getRoom($hotel_id);
//        $this->assign('data',$data);
        return $this->view->fetch('room',['data'=>$data]);
    }

    /**
     * @name 查看某个房型情况
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function typeRoom(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $room_type = input('get.type');
        if(empty($room_type)){
            $this->error('房间类型参数缺失');
        }
        $res = $this->hotel->roomOne($hotel_id,$room_type);
        $this->assign('data',$res);
        return $this->view->fetch('typeRoom');
    }


    /**
     * @name 增加酒店房间类型情况
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addRoom(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $param = input('post.');
        $rule = [
            'room_type'     =>'require',
            'room_detail'   =>'require',
            'good'          =>'require',
            'num'           =>'require',

        ];
        $msg = [
            'room_type.require'     =>'房间类型不能为空',
            'room_detail.require'   =>'房间状态不能为空',
            'good.require'          =>'房间优质情况不能为空',
            'num.require'           =>'房间数量不能为空',

        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $room_type = $param['room_type'];
        $data['hotel_id'] = $hotel_id;
        $data['room_type'] =  $room_type;
        $data['room_detail'] = $param['room_detail'];
        $data['good'] = $param['good'];
        $data['room_id'] = md5(time().rand(10000000,99999999));
        $res = $this->hotel->roomAdd($room_type,$data);
        if($res == 1){
            $this->error('该房间类型已存在，您可以直接去修改');
        }elseif($res==2){
            if(!empty(request()->file('img'))){//如果上传了房型图片
                $files = request()->file('img');
                $filesNum = count($files);
                if($filesNum==1){//如果只上传一张
                    $location = $this->upload($files,'Room');
                    $data1['location'] = $location;
                    $data1['hotel_id'] = $res;
                    $data1['room_type'] = $room_type;
                    $data1['create_time'] = time();
                    $this->hotel->addImg($data1);
                }else{//如果上传多张
                    $arr=array();
                    foreach ($files as $file){
                        $res = $this->upload($file,'Room');
                        array_push($arr,$res);
                    }
                    $actualFilesNum = count($arr);
                    if($filesNum == $actualFilesNum){
                        for($i=0;$i<$actualFilesNum;$i++){
                            $data1['location'] = $arr[$i];
                            $data1['hotel_id'] = $res;
                            $data1['room_type'] = $room_type;
                            $data1['create_time'] = time();
                            $this->hotel->roomImg($data1);
                        }
                    }else{
                        $this->error('上传失败，选择了'.$filesNum.'张图片，实际上传了'.$actualFilesNum.'张图片');
                    }
                }
            }
            $this->success('添加房间类型成功','Hotel/room');
        }else{
            $this->error('添加房间类型失败');
        }
    }


    /**
     * @name 修改房间类型
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changeRoom(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $param = input('post.');
        $where['hotel_id'] = $hotel_id;
        $where['room_type'] = $param['room_type'];
        $data['room_num'] = $param['room_num'];//参数跟据情况传
        $res = $this->hotel->roomChange($where,$data);
        if($res){
            $this->success('房间类型修改成功','Hotel/room');
        }else{
            $this->error('房间类型没有被修改');
        }
    }


    /**
     * @name 删除酒店房型情况
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delRoom(){
        $alogin = session::get('alogin');
        $hotel_id = $alogin['hotel_id'];
        $param = input('get.');
        $room_type = $param['room_type'];
        if(empty($room_type)){
            $this->error('房间类型缺失');
        }
        $res = $this->hotel->roomDel($hotel_id,$room_type);
        if($res){
            $this->success('删除房间类型成功','Hotel/room');
        }else{
            $this->error('删除房间类型失败');
        }
    }


    /**
     * @name 找出酒店下的人员信息
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function member(){
        $alogin = session::get('alogin');
        $user_id = $alogin['user_id'];
        $data =  $this->hotel->getMember($user_id);
        if($data){
            $this->assign('data',$data);
        }
        return $this->view->fetch('member');
    }


    /**
     * @name 酒店添加人员信息页面
     * @return string
     * @throws \think\Exception
     */
    public function addMember(){
        return $this->view->fetch('addMember');
    }

    /**
     * @name 酒店添加人员信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addMemberAction(){
        $alogin = session::get('alogin');
        $user_id = $alogin['user_id'];
        $param = input('post.');
        $rule = [
            'phone'      =>'require',
            'password'   =>'require',
            'user_name'  =>'require',
            'part'       =>'require'

        ];
        $msg = [
            'phone.require'      =>'电话不能为空',
            'password.require'   =>'密码不能为空',
            'user_name.require'  =>'姓名不能为空',
            'part.require'       =>'部门信息不能为空',

        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $phone = $param['phone'];
        $phoneVerify = $this->isPhoneNumber($phone);//验证手机格式是否正确
        if(!$phoneVerify){
            $this->error('请输入正确手机号');
        }
        $findPone = $this->hotel->checkNumber($phone);
        if($findPone){
            $this->error('该手机号已存在');
        }
        $data['member_id'] = md5(time().rand(10000000,99999999));
        $data['create_time'] = time();
        $data['user_id'] = $user_id;
        if(!empty($param['email'])){
            $data['email'] = $param['email'];
        }
        $res = $this->hotel->memberAdd($data);
        if($res){
            $this->success('添加人员成功','hotel/member');
        }else{
            $this->error('添加人员失败');
        }
    }


    /**
     * @name 修改酒店成员信息页面
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editMember(){
        $id = input('get.id');
        $data = $this->hotel->memberOne($id);
        $this->assign('data',$data);
        return $this->view->fetch('editMember');
    }

    /**
     * @name 酒店修改人员信息操作
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function editMemberAction(){
        $param = input('post.');
        $member_id = $param['id'];
        $rule = [
            'email'      =>'email',
            'part'       =>'number',
            'user_name'  =>'require',

        ];
        $msg = [
            'email.email'       =>'邮箱格式不对',
            'part.number'       =>'部门信息不能正确',
            'user_name.require'  =>'用户名不能为空',

        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $data['user_name'] = $param['user_name'];
        $data['email'] = $param['email'];
        $data['part'] = $param['part'];
        $res = $this->hotel->memberEdit($member_id,$data);
        if($res){
            $this->success('修改成员信息成功','hotel/member');
        }else{
            $this->error('修改成员信息失败');
        }
    }
    /**
     * @name 酒店删除成员
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delMember(){
        $id = input('get.id');
        if(empty($id)){
            $this->error('成员id缺失');
        }
        $res = $this->hotel->memberDel($id);
        if($res){
            $this->success('删除成员成功','hotel/member');
        }else{
            $this->error('删除成员失败');
        }
    }

    // 测试的
}