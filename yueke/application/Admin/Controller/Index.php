<?php
namespace app\Admin\Controller;
use think\Session;
use think\Validate;
use app\Admin\Model\Admin;

/**
 * 首页控制器
 * Class Index
 * @package app\Member\Controller
 */
class Index extends Base
{
    /**
     * @name  后台首页
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $admin = new Admin();
        $addUrl = "";
        if($admin->checkRole(['admin_id' =>Session::get('id')])==true){
            $addUrl = "<a href=\"/admin/manage\"<div>账号管理</div></a>";
        }
        //只有为超级管理员才能看到账号管理这一块
        return $this->fetch('indexs',['addUrl'=>$addUrl]);

    }

    /**
     * @name:修改密码显示页面
     * @author:  Blace  2018-03-08
     * @return html
     */
    public function update(){
        $username = Session::get('user');
        if ($this->request->isPost()){
            $data = input('post.');
            $admin = new Admin();
            $rule = [
                'repassword'=>'confirm:password'

            ];
            $msg = [
                'repassword.confirm'=>'两次密码输入不一致，请重新输入'
            ];
            $validate = new Validate($rule,$msg);
            $validate->check($data);
            $error = $validate->getError();
            if(!empty($error))
            {
                $this->error($error);
            }
            if($admin->updatePassword($username,$data['password'])){
                session(null);//退出清空session
                $this->success('修改密码成功！请重新登录','/admin/login');
            }
            else{
                $this->error('修改密码失败，请重新尝试','/admin/index');
            }
        }
        return $this->fetch('update_password',['username'=>$username]);
    }

    /**
     * @name:上传头像
     * @author:  Blace  2018-03-08
     * @return html
     */
    public function uploadAvatar(){
        if($this->request->isPost()){
            $admin = new Admin();
            $file = request()->file('image');
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS .'uploads');
                $avatar = $info->getSaveName();
                if($admin->updateAdmin(['admin_id' => Session::get('id')],['avatar' => $avatar])){
                    $this->success('头像上传成功','/admin');
                }
                else{
                    $this->error('头像上传失败','/admin');
                }
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        return $this->fetch('upload_avatar');
    }


    /**
     * @name:修改密码
     * @author:  Blace  2018-03-08
     * @return html
     */
    public function updates(){
        $data = input('post.');
        $id = Session::get('id');
        $admin = new Admin();
        $rule = [
            'repassword'=>'confirm:password'

        ];
        $msg = [
            'repassword.confirm'=>'两次密码输入不一致，请重新输入'
        ];
        $validate = new Validate($rule,$msg);
        $validate->check($data);
        $error = $validate->getError();
        if(!empty($error))
        {
            $this->error($error);
        }
        if($admin->updateAdmin(['user_name' => Session::get('user')],['password' =>sha1($data['password'])])){
            session(null);//退出清空session
            $this->success('修改密码成功！请重新登录','/admin/login');
        }
        else{
            $this->error('修改密码失败，请重新尝试','/admin/index');
        }


    }

}
