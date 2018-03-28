<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>管理员中心</title>
    <link rel="stylesheet" type="text/css" href="/Public/admin/css/common.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/admin/css/main.css"/>
    <script type="text/javascript" src="/Public/admin/js/libs/modernizr.min.js"></script>
    <script type="text/javascript" src="/Public/layui/layui.js"></script>

</head>
<body>

<div class="container clearfix">
    <div class="sidebar-wrap">
        <div class="sidebar-title" style="font-size:16px;">
            <h1>礼舍供应商管理系统</h1>
        </div>
        <div class="sidebar-content">
            <ul class="sidebar-list">
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>商品管理</a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo U('Home/index');?>"><i class="icon-font">&#xe008;</i>商品列表</a></li>
                        <!--li><a href="<?php echo U('Home/add_goods');?>"><i class="icon-font">&#xe005;</i>添加商品</a></li-->
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>采购管理</a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo U('Home/orderList');?>"><i class="icon-font">&#xe008;</i>待审核订单</a></li>
                        <li><a href="<?php echo U('Home/orderList');?>"><i class="icon-font">&#xe008;</i>待发货订单</a></li>
                        <li><a href="<?php echo U('Home/orderList');?>"><i class="icon-font">&#xe008;</i>已发货订单</a></li>
                        <!--li><a href="<?php echo U('Home/send_list');?>"><i class="icon-font">&#xe005;</i>发货列表</a></li-->
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>代发管理</a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo U('Order/index');?>"><i class="icon-font">&#xe008;</i>代发订单</a></li>
                        <li><a href="<?php echo U('Order/waitSendIndex');?>"><i class="icon-font">&#xe005;</i>待发商品</a></li>
                        <li><a href="<?php echo U('Order/sendedIndex');?>"><i class="icon-font">&#xe006;</i>已发商品</a></li>
                    </ul>
                </li>
                <!--li>
                    <a href="#"><i class="icon-font">&#xe003;</i>数据报表</a>
                    <ul class="sub-menu">
                        <li><a href="administer-分类管理（标签）.html"><i class="icon-font">&#xe008;</i>代发商品报表</a></li>
                        <li><a href="administer-公告发布.html"><i class="icon-font">&#xe005;</i>采购完成统计表</a></li>
                        <li><a href="administer-订单查询.html"><i class="icon-font">&#xe006;</i>采购中统计报表</a></li>
                        <li><a href="administer-订单查询.html"><i class="icon-font">&#xe006;</i>退货统计报表</a></li>
                    </ul>
                </li-->
                <li>
                    <a href=" <?php echo U('Home/editPasswdView');?>"><i class="icon-font">&#xe018;</i>修改密码</a>
                </li>
                <li>
                    <a href=" <?php echo U('Login/logout');?>"><i class="icon-font">&#xe018;</i>退出登录</a>
                </li>
            </ul>
        </div>
    </div>
    <!--/sidebar-->
    <div class="main-wrap">

        <div class="crumb-wrap">
            <div class="crumb-list"><i class="icon-font"></i><a href="/jscss/admin/design/">首页</a><span class="crumb-step">&gt;</span><a class="crumb-name" href="/jscss/admin/design/">公告发布</a><span class="crumb-step">&gt;</span><span>新增公告</span></div>
        </div>
        <div class="result-wrap">
            <div class="result-content">
                <form action="<?php echo U('Home/editPasswd');?>" method="post" id="myform" name="myform" enctype="multipart/form-data">
                    <h1 style="">修改密码</h1>
                    <table class="insert-tab" width="100%">
                        <tbody>
                        <tr>
                            <th width="120"><i class="require-red">*</i>请输入旧密码：</th>
                            <td>
                                <input class="common-text required" id="oldPasswd" name="oldPasswd" size="50" value="" placeholder="请输入旧密码" type="text">
                            </td>
                        </tr>
                        <tr>
                            <th width="120"><i class="require-red">*</i>请输入新密码：</th>
                            <td>
                                <input class="common-text required" id="newPasswd" name="newPasswd" size="50" value="" placeholder="请输入新密码" type="text">
                            </td>
                        </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <input class="btn btn-primary btn6 mr10" value="确认修改" type="submit">
                                    <input class="btn btn6" onclick="history.go(-1)" value="返回" type="button">
                                </td>
                            </tr>
                        </tbody></table>
                </form>
            </div>
        </div>

    </div>
    <!--/main-->
</div>
</body>
</html>