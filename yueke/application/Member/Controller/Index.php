<?php
namespace app\Member\Controller;
use app\Member\Controller\Common;
use think\Controller;
use think\Request;
use think\Session;
use think\captcha;
use think\Validate;
use think\View;
use app\Member\Model\Login;
use app\Member\Controller\Sms;

class Index extends Common
{
    protected $view;
    public function __construct()
    {
        parent::_initialize();
        $this->view = new View();
        $this->index = new Login();
    }

    /**
     * @name  会员后台首页
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
//        $aa = session::get('alogin');
//        $this->assign('aa',$aa);
        return $this->view->fetch('index');
    }

    /**
     * @name 会员后台注册页面
     * @return string
     * @throws \think\Exception
     */
    public  function register(){

        return $this->view->fetch('register');
    }

    /**
     * 获取手机验证码
     * @return bool|string
     * @throws \think\Exception
     */
    public function getCode(){
        $phone = input('post.phone');
        if(empty($phone)){
            return false;
        }else{
            $sms = new Sms();
            $type = 3;
            $data = $sms->sms($phone,$type,'');
            if($data['return_code'] == 'SUCCESS'){
               session::set($phone.'BizId',$data['BizId']);
               exit;
            }else{
               $this->error('验证码发送失败');
            }

        }
    }


    /**
     * 手机号注册，先填手机号
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function phoneRegister(){
        $param = input('post.');
        $rule = [
            'phone'      =>'require',
            'code'       =>'require',
        ];
        $msg = [
            'password.require'   =>'电话号码不能为空',
            'code.require'       =>'验证码不能为空',

        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $phone = $param['phone'];
        $code = $param['code'];
        $BizId = session::get($phone.'BizId');
        $checkCode = $this->index->code($BizId);
        if($checkCode){
            if($code!=$checkCode['auth_code']){
                $this->error('验证码错误');
            }
        }else{
            $this->error('验证码不存在');
        }
        $phoneVerify =  $this->isPhoneNumber($phone);//验证手机格式是否正确
        if(!$phoneVerify){
            $this->error('请输入正确手机号');
        }
        $isPhone = $this->index->isPhone($phone);//判断手机号是否被注册
        if($isPhone){
            $this->error('该手机号已注册，请直接登陆');
        }else{
            $data['phone'] = $phone;
            $data['user_id'] = md5(time().rand(10000000,99999999));
            $data['create_time'] = time();
            $res = $this->index->phoneAdd($data);
            if($res){
                session::set('phone',$phone);//设置
                $this->success('申请成功，请补充相关信息','index/message');
            }else{
                $this->error('申请失败');
            }
        }
    }

    /**
     * @name 补充信息页面
     * @return string
     * @throws \think\Exception
     */
    public function message(){
        return $this->view->fetch('message');
    }


    /**
     * 补充信息操作
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function messageAction(){
        $param = input('post.');
        $rule = [
            'password'   =>'require',
            'rassword'  =>'require',
            'user_name'  =>'require',
            'cn_name'    =>'require',
            'license'    =>'require',
            'address'    =>'require',
            'lat'        =>'float',
            'lng'        =>'float',

        ];
        $msg = [
            'password.require'   =>'密码不能为空',
            'rpassword.require'  =>'确认密码不能为空',
            'user_name.require'  =>'姓名不能为空',
            'cn_name.require'    =>'酒店中文名称不能为空',
            'license.require'    =>'营业执照不能为空',
            'address.require'    =>'地址不能为空',
            'lat.float'          =>'酒店经度必须为浮点型',
            'lng.float'          =>'酒店纬度必须为浮点型',

        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $data['cn_name'] = $param['cn_name'];
        $data['license'] = $param['license'];
        $data['address'] = $param['address'];
        $data['lat'] = $param['lat'];
        $data['lng'] = $param['lng'];
        $res = $this->index->addHotel($data);
        if($res){
            if(!empty(request()->file('img'))){//酒店外观图
                $files = request()->file('img');
                $filesNum = count($files);
                if($filesNum==1){//如果只上传一张
                    $location = $this->upload($files,'Hotel');
                    $data2['location'] = $location;
                    $data2['hotel_id'] = $res;
                    $data2['create_time'] = time();
                    $this->index->hotelImg($data2);
                }else{//如果上传多张
                    $arr=array();
                    foreach ($files as $file){
                        $location = $this->upload($file,'Hotel');
                        array_push($arr,$location);
                    }
                    $actualFilesNum = count($arr);
                    if($filesNum == $actualFilesNum){
                        for($i=0;$i<$actualFilesNum;$i++){
                            $data2['location'] = $arr[$i];
                            $data2['hotel_id'] = $res;
                            $data2['create_time'] = time();
                            $this->index->hotelImg($data2);
                        }
                    }else{
                        $this->error('上传失败，选择了'.$filesNum.'张图片，实际上传了'.$actualFilesNum.'张图片');
                    }
                }
            }
            //更新个人信息
            $phone = session::get('phone');
            $password = $param['password'];
            $rpassword = $param['rpassword'];
            if(sha1($password) != sha1($rpassword)){
                $this->error('两次密码不一致');
            }else{
                $data1['password'] = sha1($password);
                $data1['user_name'] = $param['user_name'];
                $data1['hotel_id'] = $res;//酒店的ID
                if(!empty(request()->file('avatar'))){
                    $file = request()->file('avatar');
                    $data1['avatar'] = $this->upload($file,'Avatar');
                }
                $result = $this->index->updateUser($phone,$data1);
                if($result){
                    $this->success('信息添加成功，请登录','index/login');
                }else{
                    $this->error('用户信息补充失败');
                }
            }
        }else{
            $this->error('信息补充失败');
        }
    }

    /**
     * @name 会员后台登陆页面
     * @return string
     * @throws \think\Exception
     */
    public function login(){
        return $this->view->fetch('login');
    }

    /**
     * @name 会员后台登陆判断
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function loginAction(){
        $param = input('post.');
        $rule = [
            'phone'  =>'require',
            'password' =>'require',
            'code'     =>'require',
        ];
        $msg = [
            'phone.require'  =>'用户名/手机号不能为空',
            'password.require' =>'密码不能为空',
            'code.require'     =>'验证码不能为空',
        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $code = $param['code'];
        $captcha = new \think\captcha\Captcha();
        if(!$captcha->check($code)){
            $this->error('验证码错误');
        }
        $account = $param['phone'];
        $res = $this->index->checkAccount($account);//判断手机号是否存在
        if($res){
            $password = $param['password'];
            $shpassword = sha1($password);
            $result = $this->index->checkPassword($account,$shpassword);
            if($result){
                if($result['state']==2){
                    session::set('alogin',$result);//记录用户登录信息session
                    $this->success('登陆成功','index/index');
                }elseif($result['state']==1){
                    $this->error('账号待审中');
                }elseif($result['state']==3){
                    $this->error('账号审核未通过');
                }elseif($result['state']==4){
                    $this->error('账号异常，请联系相关工作人员');
                }
            }else{
                $anomaly = Session::get('anomaly');
                if($anomaly)
                {
                    Session::set('anomaly',$anomaly+1);
                }else{
                    Session::set('anomaly',1);
                }
                if($anomaly >= 3)
                {
                    $this->index->checkState($account);
                    $this->error('账号异常，请联系相关工作人员');
                }else{
                    $left = 3-$anomaly;
                    $this->error('密码错误，您还有'.$left.'次机会');
                }
            }
        }else{
            $this->error('手机号不存在');
        }
    }

    /**
     * 退出登录操作
     */
    public function out(){
        session(null);
        $this->success('退出登录成功','index/login');
    }



    /**
     * @name 找回密码页面
     * @return string
     * @throws \think\Exception
     */
    public function findPassword(){
        return $this->view->fetch('findPassword');
    }


    /**
     * @name 手机号找回密码
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function findPasswordAction(){
        $param = input('post.');
        $rule = [
            'phone'      =>'require',
            'password'   =>'require',
            'rpassword'  =>'require',
            'check_code'  =>'require',
        ];
        $msg = [
            'phone.require'      =>'电话不能为空',
            'password.require'   =>'密码不能为空',
            'rpassword.require'  =>'确认密码不能为空',
            'check_code.require'  =>'手机验证码不能为空',
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
        $res = $this->index->checkAccount($phone);
        if($res){
            $password = $param['password'];
            $rpassword = $param['rpassword'];
            if(sha1($password) != sha1($rpassword)){
                $this->error('两次输入的密码不一致');
            }else{
                $change = $this->index->changePassword($phone,sha1($password));
                if($change){
                    $this->success('修改密码成功,请登录','index/login');
                }else{
                    $this->error('修改密码失败');
                }
            }
        }else{
            $this->error('您的手机号还没注册');
        }
    }

    /**
     * @name 知道原密码来修改密码
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function changePassword(){
        $message = session::get('alogin');
        $phone = $message['phone'];
        $param = input('post.');
        $rule = [
            'password'      =>'require',
            'npassword'     =>'require',
            'rpassword'     =>'require',
        ];
        $msg = [
            'password.require'   =>'原密码不能为空',
            'npassword.require'  =>'新密码不能为空',
            'rpassword.require'  =>'确认密码不能为空',
        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error)) {
            $this->error($error);
        }
        $password = $param['password'];
        $res = $this->index->checkPassword($phone,$password);
        if($res){
            $npassword = $param['npassword'];
            $rpassword = $param['rpassword'];
            if(sha1($npassword) != sha1($rpassword) ){
                $this->error('新密码和确认密码不一致');
            }else{
                $result = $this->index->changePassword($phone,$npassword);
                if($result){
                    session(null);//退出清空session
                    $this->success('修改密码成功,请重新登录','index/login');
                }else{
                    $this->error('修改密码失败');
                }
            }
        }else{
            $this->error('原密码错误');
        }
    }


}
