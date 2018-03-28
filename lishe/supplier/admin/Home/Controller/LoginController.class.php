<?php
namespace Home\Controller;
class LoginController extends CommonController {

    public function index(){
    	header("Location:http://sp.lishe.cn");
        //$this->display('login');
   }
    public function home(){

        var_dump($_SESSION);
        echo '1';
        phpinfo();
    }

    public function check_user(){
        if($_POST){
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            if(empty($username) or empty($password)){
                echo json_encode(array(-1,'用户名和密码不能为空！'));
                exit;
            }

            $uclogin=D('Admin')->adminLogin($username,$password);

            if(empty($uclogin['supplier_id'])){
                echo json_encode(array(-2,'用户不存在！'));
                exit;
            }else{
                $password=md5($password);
                if($uclogin['password']!=$password){
                    echo json_encode(array(-3,'密码不正确！'));
                    exit;
                }
                if($uclogin['status']<=0){
                    echo json_encode(array(-4,'账户被禁用！'));
                    exit;
                }
                $account=array(
                    'uid'=>$uclogin['supplier_id'],
                    'companyName'=>$uclogin['company_name'],
                    'username'=>$username,
                );

//                if($uclogin['role_id']==0){
//                    session('roleName','超级管理员');
//                }else{
//                    $role=D('Admin')->getThisRoleInfo($uclogin['role_id']);
//                    session('roleName',$role['name']);
//                }
                session('adminAccount',json_encode($account));
                echo json_encode(array($uclogin['supplier_id'],'登录成功！'));
                exit;
            }
        }else{
            echo json_encode(array(-100,'超过权限！'));
        }
    }
    public function logout(){
        session(null);
        cookie(null);
        header('location:/');
    }


}