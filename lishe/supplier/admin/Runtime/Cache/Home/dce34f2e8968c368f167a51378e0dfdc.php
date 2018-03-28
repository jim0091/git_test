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
                        <li><a href="<?php echo U('Home/add_goods');?>"><i class="icon-font">&#xe005;</i>添加商品</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>采购管理</a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo U('Home/order_list');?>"><i class="icon-font">&#xe008;</i>订单列表</a></li>
                        <li><a href="<?php echo U('Home/send_list');?>"><i class="icon-font">&#xe005;</i>发货列表</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>代发管理</a>
                    <ul class="sub-menu">
                        <li><a href="administer-分类管理（标签）.html"><i class="icon-font">&#xe008;</i>代发订单</a></li>
                        <li><a href="administer-公告发布.html"><i class="icon-font">&#xe005;</i>代发商品</a></li>
                        <li><a href="administer-订单查询.html"><i class="icon-font">&#xe006;</i>已发商品</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>数据报表</a>
                    <ul class="sub-menu">
                        <li><a href="administer-分类管理（标签）.html"><i class="icon-font">&#xe008;</i>代发商品报表</a></li>
                        <li><a href="administer-公告发布.html"><i class="icon-font">&#xe005;</i>采购完成统计表</a></li>
                        <li><a href="administer-订单查询.html"><i class="icon-font">&#xe006;</i>采购中统计报表</a></li>
                        <li><a href="administer-订单查询.html"><i class="icon-font">&#xe006;</i>退货统计报表</a></li>
                    </ul>
                </li>
                <li>
                    <a href=" <?php echo U('Login/logout');?>"><i class="icon-font">&#xe018;</i>修改密码</a>
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
                <form action="/jscss/admin/design/add" method="post" id="myform" name="myform" enctype="multipart/form-data">
                    <table class="insert-tab" width="100%">
                        <tbody><tr>
                            <th width="120"><i class="require-red">*</i>分类：</th>
                            <td>
                                <select name="colId" id="catid" class="required">
                                    <option value="18">请选择</option>
                                    <option value="19">运动健身</option>
                                     <option value="20">旅游</option>
                                    <option value="21">文学艺术</option>
                                    <option value="22">演讲</option>
                                    <option value="23">经济</option>
                                    <option value="24">电影</option>
                                    <option value="25">科技</option>
                                    <option value="26">美食</option>
                                </select>
                            </td>
                        </tr>
                            <tr>
                                <th><i class="require-red">*</i>标题：</th>
                                <td>
                                    <input class="common-text required" id="title" name="title" size="50" value="" type="text">
                                </td>
                            </tr>
                            <tr>
                                <th><i class="require-red">*</i>图片：</th>
                                <td><input name="smallimg" id="" type="file"><!--<input type="submit" onclick="submitForm('/jscss/admin/design/upload')" value="上传图片"/>--></td>
                            </tr>
                            <tr>
                                <th><i class="require-red">*</i>视频：</th>
                                <td><input name="vedio" id="" type="file"><!--<input type="submit" onclick="submitForm('/jscss/admin/design/upload')" value="上传视频"/>--></td>
                            </tr>
                            <tr>
                                <th>课程介绍：</th>
                                <td><textarea name="content" class="common-textarea" id="content" cols="30" style="width: 98%;" rows="10"></textarea></td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <input class="btn btn-primary btn6 mr10" value="发布" type="submit">
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