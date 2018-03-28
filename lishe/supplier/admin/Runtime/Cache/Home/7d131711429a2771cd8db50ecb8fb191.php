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
                        <!--li><a href="<?php echo U('Home/add_goods');?>"><i class="icon-font">&#xe005;</i>添加商品</a></li-->
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>采购管理</a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo U('Home/orderList');?>"><i class="icon-font">&#xe008;</i>待审核订单</a></li>
                        <li><a href="<?php echo U('Home/waitSend');?>"><i class="icon-font">&#xe008;</i>待发货订单</a></li>
                        <li><a href="<?php echo U('Home/sended');?>"><i class="icon-font">&#xe008;</i>已发货订单</a></li>
                        <li><a href="<?php echo U('Home/storage');?>"><i class="icon-font">&#xe008;</i>已入库订单</a></li>
                        <!--li><a href="<?php echo U('Home/send_list');?>"><i class="icon-font">&#xe005;</i>发货列表</a></li-->
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="icon-font">&#xe003;</i>代发管理</a>
                    <ul class="sub-menu">
                        <li><a href="<?php echo U('Order/index');?>"><i class="icon-font">&#xe008;</i>全部订单</a></li>
                        <li><a href="<?php echo U('Order/waitSendIndex');?>"><i class="icon-font">&#xe005;</i>待发订单</a></li>
                        <li><a href="<?php echo U('Order/sendedIndex');?>"><i class="icon-font">&#xe006;</i>已发订单</a></li>
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
<script type="text/javascript" src="/Public/admin/lib/jquery/1.9.1/jquery.min.js"></script>
<div class="main-wrap">

    <div class="crumb-wrap">
        <div class="crumb-list"><i class="icon-font"></i><a href="index.html">首页</a><span class="crumb-step">&gt;</span><span class="crumb-name">待审核订单</span></div>
    </div>
    <div class="search-wrap">
        <div class="search-content">
            <form action="#" method="post">
                <table class="search-tab">
                    <!--tr>
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
                    </tr-->
                </table>
            </form>
        </div>
    </div>
    <div class="result-wrap">
        <form name="myform" id="myform" method="post">

            <div class="result-content">
                <table class="result-tab" min-width="100%">
                    <tr>
                        <td class="tc" width="5%"></td>
                        <td style="min-width:50px;">订单编号</td>
                        <td style="min-width:100px;">结算方式</td>
                        <td style="min-width:100px;">单品数量</td>
                        <td style="min-width:150px;">总金额</td>
                        <td style="min-width:70px;">仓库</td>
                        <td style="min-width:70px;">备注</td>
                        <td style="min-width:80px;">建立人</td>
                        <td style="min-width:100px;">建立时间</td>
                        <td style="min-width:50px;">操作</td>
                    </tr>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>
                        <td class="tc"><input name="id[]" value="" type="checkbox"></td>
                        <td><?php echo ($val['order_id']); ?></td>
                        <td><?php echo ($val['settlement_method']); ?></td>
                        <td><?php echo ($val['skuCount']); ?></td>
                        <td><?php echo ($val['prices']); ?></td>
                        <td><?php echo ($warehouses[$val['warehouse_id']]['name']); ?></td>
                        <td><?php echo ($val['remarks']); ?></td>
                        <td><?php echo ($val['build_people']); ?></td>
                        <td><?php echo ($val['build_time']); ?></td>
                        <td><a href="javascript::" onclick="layer_show('查看','<?php echo U('Home/editOrder/',array('order_id'=>$val['order_id']));?>','1','800','500')">详情 </a><a href="javascript::" onclick="checkOrder('<?php echo ($val['order_id']); ?>','1')"> 审核通过 </a><a onclick="checkOrder('<?php echo ($val['order_id']); ?>','-1')" href="javascript::"> 不通过 </a></td>

                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                </table>
                <div class="list-page">
                    <?php echo ($page); ?>
                </div>
            </div>
        </form>
    </div>
</div>
<!--/main-->
</div>
</body>
<script>
    layui.use('layer', function(){
    });
    function layer_show(title,url,w,h){
        if (title == null || title == '') {
            title=false;
        };
        if (url == null || url == '') {
            url="404.html";
        };
        if (w == null || w == '') {
            w=800;
        };
        if (h == null || h == '') {
            h=($(window).height() - 50);
        };
        layer.open({
            type: 2,
            area: ['800px', '500px'],
            fix: false, //不固定
            maxmin: true,
            shade:0.4,
            title: title,
            content: url
        });
    }
    function checkOrder(id,status){

        layer.confirm('确认审核？', {
            btn: ['确认','取消'] //按钮
        }, function(){

            layer.prompt({title: '如有需要请填写备注，管理员会查看的~', formType: 2}, function(text){
                var data={'order_id':id,"status":status,"remarks":text};
                $.post("<?php echo U('Home/checkOrder/');?>",data,function(res){
                    if(res=="1"){
                        layer.msg("成功");
                    }else{
                        layer.msg("审核失败");
                    }
                    window.location.reload();
                });
            });
        }, function(){
            layer.msg("取消");
        });
    }

</script>
</html>