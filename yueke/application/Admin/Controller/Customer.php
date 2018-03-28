<?php
namespace app\admin\controller;
use app\Admin\Model\Guest;


/**
 * 小程序用户控制器
 * Class Customer
 * @package app\Member\Controller
 */
class Customer extends Base
{

    /**
     * @name  默认验证方法
     * @action 判断用户是否有登录
     * @throws \think\Exception
     */

    public function __construct()
    {
        parent::__construct();
        if(!session('id')){
            $this->error('未登录，请先登录','/admin/login');
        }
        else{
            return true;
        }

    }

    /**
     * @name：小程序用户管理首页
     * @method：GET|POST
     * @return array
     */
    public function index()
    {
        $guest = new Guest();
        if($this->request->isPost()){
            $search = input('post.');
            switch ($search['type']){
                case '1':
                    $search_type = 'guest_id';
                    break;
                case '2':
                    $search_type = 'user_name';
                    break;
                case '3':
                    $search_type = 'nick_name';
                    break;
                case '4':
                    $search_type = 'card';
                    break;
                case '5':
                    $search_type = 'phone';
                    break;
                case '6':
                    $search_type = 'address';
                    break;
            }
            $data = $guest->findSearch($search_type,$search['search']);
        }
        //判断是否有搜索post请求
        else{
            $data = $guest->findAll(['state' => 1]);
        }
        if(!$data){
            $this->error('暂无相关内容','/admin/guest');
        }
        //数据处理
        /*$i = 0;
        foreach($data as $key){
            $data[$i]['create_time'] = date('Y-m-d',$key['create_time']);
            switch ($key['sex']){
                case '1':
                    $data[$i]['sex'] = '男';
                    break;
                case '2':
                    $data[$i]['sex'] = '女';
                    break;
            }
            $i++;
        }*/
//dump($data);exit;
        return $this->fetch('user',['data' =>$data]);
    }

    /**
     * @name：删除小程序用户数据
     * @method：GET
     * @return bool
     */
    public function deleteGuest($id){
        $guest = new Guest();
        $data = $guest->deleteGuest(['guest_id' =>$id]);
        if(!$data) {
            $this->error('删除失败...或该号码不存在');
        }
        $this->success('删除成功...','/admin/guest');
    }



    public function check_verify($code, $id = ''){
        $verify = new \think\captcha\Captcha();
        return $verify->check($code, $id);
    }

}
