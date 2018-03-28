<?php if (!defined('THINK_PATH')) exit();?>
<!doctype html>
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
        <div class="crumb-list"><i class="icon-font"></i><a href="index.html">首页</a><span class="crumb-step">&gt;</span><span class="crumb-name">分类管理</span></div>
    </div>
    <div class="search-wrap">
        <div class="search-content">
            <form action="#" method="post">
                <table class="search-tab">
                    <tr>
                        <th width="120">选择分类:</th>
                        <td>
                            <select name="search-sort" id="">
                                <option value="class00">全部</option>
                                <option value="class01">运动健身</option>
                                <option value="class02">旅游</option>
                                <option value="class03">文学艺术</option>
                                <option value="class04">演讲</option>
                                <option value="class05">经济</option>
                                <option value="class06">电影</option>
                                <option value="class07">科技</option>
                                <option value="class08">美食</option>
                            </select>
                        </td>
                        <th width="70">关键字:</th>
                        <td><input class="common-text" placeholder="关键字" name="keywords" value="" id="" type="text"></td>
                        <td><input class="btn btn-primary btn2" name="sub" value="查询" type="submit"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <div class="result-wrap">
        <form name="myform" id="myform" method="post">
            <div class="result-title">
                <div class="result-list">
                    <a href="insert.html"><i class="icon-font"></i>新增商品</a>
                    <a id="batchDel" href="javascript:void(0)"><i class="icon-font"></i>批量删除</a>
                    <a id="updateOrd" href="javascript:void(0)"><i class="icon-font"></i>更新排序</a>
                </div>
            </div>
            <div class="result-content">
                <table class="result-tab" min-width="100%">
                    <tr>
                        <td class="tc" width="5%"></td>
                        <td style="min-width:50px;">序号</td>
                        <td style="min-width:100px;">商品编码</td>
                        <td style="min-width:100px;">商品条码</td>
                        <td style="min-width:150px;">商品名称</td>
                        <td style="min-width:70px;">数量</td>
                        <td style="min-width:70px;">价格</td>
                        <td style="min-width:80px;">金额</td>
                        <td style="min-width:100px;">规格</td>
                        <td style="min-width:50px;">箱包数量</td>

                    </tr>
                    <tr>
                        <td class="tc"><input name="id[]" value="" type="checkbox"></td>
                        <td>01</td>
                        <td>04000089</td>
                        <td>6903148108338</td>
                        <td>飘柔洗发水杏仁长效柔顺滋养家庭装750ml</td>
                        <td>144.00</td>
                        <td>22.89</td>
                        <td>3296.16</td>

                        <td>支</td>
                        <td>750ml*12</td>

                    </tr>
                    <tr>
                        <td class="tc"><input name="id[]" value="" type="checkbox"></td>
                        <td>02</td>
                        <td>04000091</td>
                        <td>6924810800619</td>
                        <td>卡士活菌酸奶720ml</td>
                        <td>36.00</td>
                        <td>15.66</td>
                        <td>563.76</td>

                        <td>支</td>
                        <td>750ml*12</td>

                    </tr>
                </table>
                <div class="list-page">
                    2 条 1/1 页
                </div>
            </div>
        </form>
    </div>
</div>
<!--/main-->
</div>
</body>
</html>