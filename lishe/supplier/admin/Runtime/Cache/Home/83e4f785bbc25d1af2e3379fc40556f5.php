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
<div class="main-wrap">

    <div class="crumb-wrap">
        <div class="crumb-list"><i class="icon-font"></i><a href="index.html">首页</a><span class="crumb-step">&gt;</span><span class="crumb-name">商品列表</span></div>
    </div>
    <div class="search-wrap">
        <div class="search-content">
            <form action="<?php echo U('Home/goodsList');?>" method="get">
                <table class="search-tab">
                    <tr>
                        <th width="120" style="font-size:14px;">分类筛选:</th>
                        <td>
                            <select name="cat_id_1" id="" style="font-size:12px;">
                                <option value="">全部分类</option>
                                <?php if(is_array($catList)): $i = 0; $__LIST__ = $catList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cat): $mod = ($i % 2 );++$i;?><option value="<?php echo ($cat['cat_id']); ?>"
                                        <?php if(($cat['cat_id']) == $cat_id_1): ?>selected='selected'<?php endif; ?>
                                        ><?php echo ($cat['cat_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </td>
                        <th width="70" style="font-size:14px;">关键字:</th>
                        <td><input class="common-text" style="height:20px;font-size:12px;" placeholder="关键字" value="<?php echo ($like); ?>" name="like" type="text"></td>
                        <td><input class="btn btn-primary btn2" name="sub" value="查询" type="submit"></td>
                    </tr>
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
                        <td style="min-width:50px;">商品ID</td>
                        <td style="min-width:100px;">所属商铺</td>
                        <td style="min-width:100px;">所属分类</td>
                        <td style="min-width:150px;">商品名称</td>
                        <td style="min-width:70px;">品牌</td>
                        <td style="min-width:70px;">供货价</td>
                        <td style="min-width:70px;">市场价</td>
                        <td style="min-width:80px;">实际销售价</td>
                        <td style="min-width:100px;">毛利率</td>
                        <td style="min-width:50px;">状态</td>
                        <td style="min-width:50px;">操作</td>
                    </tr>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><tr>
                        <td class="tc"><input name="id[]" value="" type="checkbox"></td>
                        <td style="font-size:12px;"><?php echo ($val['sitem_id']); ?></td>
                        <td><?php echo ($shopList[$val['shop_id']]['shop_name']); ?></td>
                        <td><?php echo ($cats[$val['cat_id']]['cat_name']); ?></td>
                        <td><?php echo ($brandList[$val['brand_id']]['brand_name']); ?></td>
                        <td><?php echo ($val['title']); ?></td>
                        <td><?php echo ($val['cost_price']); ?></td>
                        <td><?php echo ($val['mkt_price']); ?></td>
                        <td><?php echo ($val['price']); ?></td>
                        <td><?php echo ($val['profit_rate']); ?></td>
                        <?php if(($val['status'] == 1)): ?><td style='color:#679e33;'>正常</td>
                            <?php else: ?>
                            <td style='color:#f13f40;'>删除</td><?php endif; ?>
                        <td><a href="javascript::" onclick="layer_show('查看','<?php echo U('Home/goodsDetail/',array('sitem_id'=>$val['sitem_id']));?>','1','800','500')">详情 </a><!--a href="javascript::"> 删除 </a--></td>
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
    function goodsDetail(goodsId){
        layer.open({
            title: "aaa", content: "bbb"
        });
    }

</script>
</html>