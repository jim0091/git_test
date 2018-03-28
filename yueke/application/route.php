<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'admin/guest/search' => 'Admin/Customer/index_search',//小程序搜索控制器
    'admin/guest/delete' => 'Admin/Customer/deleteGuest',//删除小程序用户数据操作
    'admin/guest' => 'Admin/Customer/index',//小程序用户管理首页
    'admin/login' => 'Admin/Login/index',//登录路由
    'admin/logout' => 'Admin/Login/logout',//登出路由
    'admin/update' => 'Admin/Index/update',//修改个人密码路由
    'admin/updates' => 'Admin/Index/updates',//修改个人密码操作
    'admin/upload' => 'Admin/Index/uploadAvatar',//上传头像
    'admin/manage' => 'Admin/Login/manage',//管理账号页
    'admin/manage/add' => 'Admin/Login/add',//新增管理账号
    'admin/manage/delete' => 'Admin/Login/deleteAdmin',//删除管理账号
    'admin/manage/edit' => 'Admin/Login/editAdmin',//修改管理账号
    'admin/manage/stop' => 'Admin/Login/stopAdmin',//禁用管理账号
    'admin/manage/start' => 'Admin/Login/startAdmin',//开启管理账号
    'admin/manage/details' => 'Admin/Login/detailsAdmin',//账号详情
    'admin/hotel' => 'Admin/Hotel/index',//酒店管理
    'admin/hotel/details' => 'Admin/Hotel/detailsHotel',//酒店详情详情
    'admin/hotel/state' => 'Admin/Hotel/stateHotel',//根据分类查询酒店
    'admin/user/details' => 'Admin/HotelUser/detailsUser',//酒店拥有者详情
    'admin/user/stop' => 'Admin/HotelUser/stopUser',//封闭酒店用户账号
    'admin/user/start' => 'Admin/HotelUser/startUser',//开启酒店用户账号




];
