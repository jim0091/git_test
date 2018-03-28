<?php
namespace app\Admin\Controller;
use think\Controller;
/**
 * 公用控制器
 * Class Base
 * @package app\Member\Controller
 */
class Base extends Controller
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
}
