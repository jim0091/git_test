<?php
namespace app\admin\controller;

use app\Admin\Model\Admin;
use think\Validate;
use think\Session;
use think\Controller;

/**
 * 登录控制器
 * Class index
 * @package app\Member\Controller
 */
class Login extends Controller
{

    /**
     * @name：后台登陆
     * @param: $username
     * @param :$shpassword
     * @return bool
     */
    public function index()
    {
        if(Session::get("id")) {
            $this->error('您已登录过...正在返回首页','/admin/index/index');
        }
        else{
            $admin =new Admin;
            if ($this->request->isPost()) {
                $param = input('post.');
                $rule = [
                    'user'=>'require|min:6|max:20',
                    'password'=>'require',
                ];
                $msg = [
                    'user.require'=>'用户名不能为空',
                    'user.mix'=>'用户名长度不能少于6位有效字符',
                    'user.max'=>'用户名长度不能超过20位字符',

                    'password.require'=>'密码不能为空',
                ];
                $validate = new Validate($rule,$msg);
                $validate->check($param);
                $error = $validate->getError();
                if(!empty($error))
                {
                    $this->error($error,'/admin/login');
                }

                $verify = $param['verify'];
                if(!$this->check_verify($verify))
                {
                    $this->error('验证码错误','/admin/login');
                }
                //验证验证码
                if(preg_match("/1[3458]{1}\d{9}$/",$param['user'])){
                    $data = $admin->checkPhone(['phone' =>$param['user']]);
                    //通过手机号查询
                }
                else{
                    $data = $admin->checkAccount(['user_name' => $param['user']]);
                    //通过用户名查询
                }

                if(empty($data)){
                    $this->error('登录账号不存在，请重新输入!','/admin/login');
                }
                //查询账号是否存在
                if ($data['state']==2) {
                    $this->error('账号异常，请联系管理人员!','/admin/login');
                }
                //判断账号是否异常
                $shpassword = sha1($param['password']);
                //sha1加密
                if($data['password'] != $shpassword){
                    $anomaly = Session::get('anomaly');
                    if($anomaly)
                    {
                        Session::set('anomaly',$anomaly+1);
                    }else{
                        Session::set('anomaly',1);
                    }
                    if($anomaly==3){
                        $admin->updateAdmin(['user_name' => $data['user_name']],['state' => 2]);
                        Session::delete('anomaly');
                        $this->error('密码错误达到三次，该账号已被封禁','/admin/login');
                    }
                    $this->error('登录账号或密码错误，请重新输入','/admin/login');
                }
                else{
                    Session::set("user", $data['user_name']);
                    Session::set("id", $data['admin_id']);
                    $this->success('登录成功，正在进入系统...','/admin/index/index' );
                }
            }
            return $this->fetch('login', ['title' => '用户登录']);


        }

    }

    /**
     * @name：后台退出
     * @param: $username
     * @param :$shpassword
     * @return bool
     */
    public function logout(){
        if(Session::get("id")) {
            session('user',null);//退出清空session
            session('id',null);//退出清空session

            return $this->success('退出成功', '/admin/login', 3);//跳转到登录页面
        }else{
            return $this->error('您还未登录', '/admin/login',3);//跳转到登录页面

        }
    }

    /**
     * @name：权限判断
     * @return bool
     */
    public function role(){
        $admin = new Admin();
        $id = Session::get('id');
        if($admin->checkRole(['admin_id' => $id])){
            return true;
        }else{
            return false;
        }


    }

    /**
     * @name：查找所有账号,管理账号页
     * @return bool
     */
    public function manage(){
        $admin = new Admin();
        if($this->role()){
            $id = Session::get('id');
            $data = $admin->findAdmin($id);
            if(!$data) {
                $this->error('暂无内容');
            }

            return $this->fetch('account_index',['title' => '账号管理','data' =>$data ]);
        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');


    }

    /**
     * @name：查找某个账号所有内容
     * @return bool
     */
    public function detailsAdmin($id){
        $admin = new Admin();
        if($this->role()){
            $data = $admin->findById(['admin_id'=>$id]);
            if(!$data) {
                $this->error('暂无内容');
            }
            $data['create_time']=date('Y-m-d H:i:s',$data['create_time']);
            //时间戳转换
            switch ($data['state']){
                case '1':
                    $data['state'] = '正常';
                    break;
                case '2':
                    $data['state'] = '禁用';
                    break;
            }
            return $this->fetch('account_details',['data' =>$data ]);
        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');


    }
    /**
     * @name：删除账号
     * @method：GET
     * @return bool
     */
    public function deleteAdmin($id){
        $admin = new Admin();
        if($this->role()){
            $data = $admin->deleteAdmin(['admin_id' => $id]);
            if(!$data) {
                $this->error('删除失败...或该号码不存在');
            }
            $this->success('删除成功...','/admin/manage');

        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');


    }

    /**
     * @name：修改管理信息
     * @method：Post
     * @return bool
     */
    public function editAdmin($id){
        if($this->role()) {
            $admin = new Admin();
            $data = $admin->findById(['admin_id' => $id]);
            //根据ID查数据
            if(!$data){
                $this->error('非法请求');
            }
            if ($this->request->isPost()) {
                $param = input('post.');
                $rule = [
                    'user'=>'require|max:20|min:6',
                    'phone'=>'regex:/^1[34578]\d{9}$/',

                ];
                $msg = [
                    'user.max'=>'用户名最长不超过20位',
                    'user.min'=> '用户名最少为6位',
                    'phone.regex'=>'请输入正确的手机号码',
                ];
                $validate = new Validate($rule,$msg);
                $validate->check($param);
                $error = $validate->getError();
                if(!empty($error))
                {
                    $this->error($error);
                }

                if(!$admin->isUser(['user_name' => $param['user']])&&$param['user']!=$data['user_name']){
                    $this->error('用户名已存在');
                }
                //验证用户名是否被注册过

                    if(!$admin->isPhone(['phone' => $param['phone']])&&$param['phone']!=$data['phone']){
                        $this->error('手机号码已存在');
                    }
                    //验证手机号是否被注册过
                $params = [
                    'user_name' => $param['user'],
                    'phone' => $param['phone'],
                ];
                if($param['password']){
                    //如果发起修改密码的请求
                    $rule = [
                        'repassword'=>'require|confirm:password'

                    ];
                    $msg = [
                        'repassword.require'=>'确认密码不能为空',
                        'repassword.confirm'=>'两次密码输入不一致，请重新输入'
                    ];
                    $validate = new Validate($rule,$msg);
                    $validate->check($param);
                    $error = $validate->getError();
                    if(!empty($error))
                    {
                        $this->error($error);
                    }
                    $params['password'] = sha1($param['password']);
                    if($admin->updateAdmin(['admin_id' =>$id ],$params)){
                        $this->success('修改成功','/admin/manage');
                    }
                    else{
                        $this->error('修改失败','/admin/manage');
                    }
                }//如果密码有改动的话，这进行密码改动及判定
                else{
                    if($admin->updateAdmin(['admin_id' => $id ],$params)){
                        $this->success('修改成功','/admin/manage');
                    }
                    else{
                        $this->error('修改失败','/admin/manage');
                    }
                }//密码无改动则修改信息。
            }else{
                return $this->fetch('update',['data'=>$data]  );
            }


        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');

    }

    /**
     * @name：管理账号添加
     * @param: $data
     * @return bool
     */
    public function add(){
        $admin = new Admin();
        if($this->role()){
            if ($this->request->isPost()) {
                $param = input('post.');
                $rule = [
                    'user'=>'require|max:20|min:6',
                    'phone'=>'regex:/^1[34578]\d{9}$/',
                    'password' =>'regex:/^[0-9a-z]$/isU',
                    'repassword'=>'require|confirm:password'

                ];
                $msg = [
                    'user.max'=>'用户名最长不超过20位',
                    'user.min'=> '用户名最少为6位',
                    'password.regex'=>'密码需为字母加数字组成',
                    'phone.regex'=>'请输入正确的手机号码',
                    'repassword.confirm'=>'两次密码输入不一致，请重新输入'
                ];
                //验证字段不为空交给前端
                $validate = new Validate($rule,$msg);
                $validate->check($param);
                $error = $validate->getError();
                if(!empty($error))
                {
                    $this->error($error,'/admin/login');
                }
                //验证
                if(!$admin->isUser(['user_name' => $param['user']])){
                    $this->error('用户名已存在');
                }
                //验证用户名是否被注册过
                if(!$admin->isPhone(['phone' => $param['phone']])){
                    $this->error('手机号码已存在');
                }
                //验证手机号是否被注册过
                if(!$this->check_verify($param['verify']))
                {
                    $this->error('验证码错误','/admin/login');
                }
                $password = sha1($param['password']);
                $time = strtotime(date('y-m-d h:i:s',time()));
                $params = [
                    'user_name' => $param['user'],
                    'password' => $password,
                    'phone' => $param['phone'],
                    'role' => $param['role'],
                    'state' => 1,
                    'create_time' => $time,
                    'admin_id' => md5(time().rand(10000000,999999999))
                ];
                if($admin->addAdmin($params)){
                    $this->success('添加成功...','/admin/manage');
                };
            }
            else{
                return $this->fetch('add', ['title' => '用户添加']);
            }

        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');

    }

    /**
     * @name：禁用账号
     * @method：GET
     * @return bool
     */
    public function stopAdmin($id){
        $admin = new Admin();
        if($this->role()){
            $data = $admin->updateAdmin(['admin_id' => $id],['state' => 2]);
            if(!$data) {
                $this->error('禁用失败...');
            }
            $this->success('禁用成功...','/admin/manage');
        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');
    }

    /**
     * @name：开启账号
     * @method：GET
     * @return bool
     */
    public function startAdmin($id){
        $admin = new Admin();
        if($this->role()){
            $data = $admin->updateAdmin(['admin_id' => $id],['state' => 1]);
            if(!$data) {
                $this->error('开启失败...');
            }
            $this->success('开启成功...','/admin/manage');
        }
        $this->error("您没有权限进行此操作...正在返回首页",'/admin/index');
    }


    public function check_verify($code, $id = ''){
        $verify = new \think\captcha\Captcha();
        return $verify->check($code, $id);
    }

}
