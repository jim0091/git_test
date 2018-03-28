<?php if (!defined('THINK_PATH')) exit();?>

    <!--/sidebar-->
<script type="text/javascript" src="/Public/js/My97DatePicker/WdatePicker.js"></script> 
    
<div class="main-wrap">

    <div class="crumb-wrap">
        <div class="crumb-list"><i class="icon-font"></i><a href="index.html">首页</a><span class="crumb-step">&gt;</span><span class="crumb-name">待发订单</span></div>
    </div>
    <div class="search-wrap">
        <div class="search-content" style="padding: 0px 30px;">
		<form action="<?php echo U('Order/waitSendIndex');?>" method="get" id="serach">
			<input  type="hidden" class="tools-input" id="execlType" name="execlType" value="" >							
			<div class="product-tools">
				<div class="search">
					<label>支付时间：</label>
					<label>
				<input type="text" onfocus="WdatePicker()" value="<?php echo ($searchData["startTime"]); ?>" name="startTime" id="datemin" class="input-text Wdate" style="width:120px;">
				-
				<input type="text" onfocus="WdatePicker()" value="<?php echo ($searchData["endTime"]); ?>" name="endTime" id="datemax" class="input-text Wdate" style="width:120px;">

					<label>售后：</label>
					<label><select class="select-box" name="service" style="width:80px;">
					<?php if(!empty($searchData["service"])): ?><option value="<?php echo ($searchData["service"]["type"]); ?>"><?php echo ($searchData["service"]["name"]); ?></option><?php endif; ?>						
					<option value="0">全部</option>
					<option value="NO_APPLY">无售后</option>
					<option value="REFUND">退款</option>
					<option value="CANCEL_REFUND">取消退款</option>
					<option value="RETURN">退货</option>
					<option value="CANCEL_RETURN">取消退货</option>
					<option value="EXCHANGE">换货</option>
					<option value="CANCEL_EXCHANGE">取消换货</option>
					<option value="REPAIR">维修</option>
					<option value="CANCEL_REPAIR">取消维修</option>
					</select>
					</label>						
					<label>订单号：</label>
					<label>
					<input type="text" name="tid"  placeholder="订单号" style="width:150px" class="input-text" value="<?php echo ($searchData["tid"]); ?>" >
					</label>							
					<label>商品：</label>
					<label>
					<input type="text" name="goods"  placeholder="商品名称" style="width:150px" class="input-text" value="<?php echo ($searchData["goods"]); ?>" >
					<button onclick="serach()" class="btn btn-success radius" ><i  class="Hui-iconfont"></i> 搜索</button>						
					</label>
				</div>
			</div>
			</form>	            
        </div>
    </div>
    <div class="result-wrap">
            <div class="result-title">
                <div class="result-list">
                	<strong style="float: left;color: #5a98de;">共<?php echo ($number); ?>条</strong>
                </div>
            </div>
            <div class="result-content">
                <table class="result-tab" min-width="100%">
                    <tr>
						<td align="center" width="10%">订单号</td>
						<td align="center">商品名称/规格/价格/数量</td>
						<td align="center" width="45%">收货信息</td>
						<td align="center" width="12%">下单/支付时间</td>
						<td align="center" width="8%">订单状态</td>
						<td align="center" width="10%">操作</td>
                    </tr>
					<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$order): $mod = ($i % 2 );++$i;?><tr style="line-height: 25px;">
						<td class="checkboxs">
							<span style="cursor:pointer;"><?php echo ($order["tid"]); ?></span>	
						</td>
						<td align="left">
							<?php if(is_array($order["items"])): $i = 0; $__LIST__ = $order["items"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if($i != 1): ?><div style="border-bottom: 1px #ddd solid;">&nbsp;</div><?php endif; ?>
								<div class="col-12" style="padding-top: 5px;">
								<div class="col-2" style="float: left;padding-right: 10px;">
									<img src="<?php echo ($item["pic_path"]); ?>_t.<?php echo end(explode('.',$item['pic_path']));?>" width="60px" style="max-height: 60px;overflow: hidden;"  />
								</div>
								<div class="col-10">
									<?php if($item["disabled"] == 1): ?><img src="/Public/images/quxiao.png"  title="通过取消商品的方式使其退款，无需发货！"/><?php endif; ?>
									<?php if($item["status"] == 'IN_STOCK'): ?><img src="/Public/images/beihuo.png"  width="15px" title="该商品已备货"/>
									<?php elseif($item["status"] == 'WAIT_BUYER_CONFIRM_GOODS'): ?>
										<img src="/Public/images/fahuo.png"  width="20px" title="该商品已发货"/><?php endif; ?>
									<?php if($item["aftersales_status"] != 'NO_APPLY'): ?><img src="/Public/images/shouhou.png"  width="20px" title="<?php echo ($order["serviceStatus"]); ?>"/><?php endif; ?>
										<?php echo ($item["title"]); ?>
									<br />
								      <?php if(!empty($$vo["spec_nature_info"])): ?>规格：<strong style="color: #5a98de;"><?php echo ($item["spec_nature_info"]); ?></strong><br /><?php endif; ?>
									价格：<strong style="color: #5a98de;">￥<?php echo ($item["cost_price"]); ?></strong>
									数量：<strong style="color: #5a98de;"><?php echo ($item["num"]); ?>件</strong>
								</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</td>
						<td align="left">
							<?php echo ($order["receiver_name"]); ?><br/>
							<?php echo ($order["receiver_mobile"]); ?><br />
							<?php echo ($order['receiver_state']); echo ($order['receiver_city']); echo ($order['receiver_district']); echo ($order['receiver_address']); ?>
						</td>
						<td align="center">
							<?php if(!empty($order["created_time"])): echo (date("Y-m-d H:i:s",$order['created_time'])); endif; ?>
							<br />
							<?php if(!empty($order["pay_time"])): echo (date("Y-m-d H:i:s",$order['pay_time'])); ?>
							<?php else: ?>
								无<?php endif; ?>
							
						</td>
						<td align="center">
							<span>
								<?php echo ($order["orderStatus"]); ?>
							</span>
							<br/>
							
							<?php if(in_array(($order["order_status"]), explode(',',"REFUND,RETURN,EXCHANGE,REPAIR"))): ?><strong  style="margin-top: 10px;color: #5a98de;">
							<?php else: ?>
								<?php if(in_array(($order["order_status"]), explode(',',"CANCEL_REFUND,CANCEL_RETURN,CANCEL_EXCHANGE,CANCEL_REPAIR"))): ?><span style="margin-top: 10px; color: #666;">
									<?php else: ?>
									<span><?php endif; endif; ?>
							<?php echo ($order["serviceStatus"]); ?>
							</strong>
							<?php
 foreach($order['items'] as $key=>$value){ if($value['aftersales_status'] == 'NO_APPLY'){ $order['sendGoods']=1; }} if(empty($order['sendGoods'])){ ?>							
							<?php if(in_array(($order["status"]), explode(',',"WAIT_SELLER_SEND_GOODS,IN_STOCK"))): ?><br />
								<samll style="color: #eb4f38;">无需发货</samll><?php endif; ?>	
							<?php
 } ?>
						</td>
						
						<td align="center">
							<!--详情-->
							<a href="javascript::" onclick="layer_show('订单:<?php echo ($order["tid"]); ?>订单详情','<?php echo U('Order/detail/',array('tid'=>$order['tid']));?>','1','800','500')">						 	
								<span class="label label-success radius overbtn">详情</span>
							</a>							
							<!--详情-->
							
								
							<!--备货-->
							<?php  foreach($order['items'] as $key=>$value){ if($value['status'] == 'WAIT_SELLER_SEND_GOODS' && $order['shop_id'] !=10){ if(in_array($value['aftersales_status'],array('NO_APPLY','CANCEL_APPLY','SELLER_REFUSE'))){ ?>
						 		<br />		
								<a href="javascript::" onclick="layer_show('订单:<?php echo ($order["tid"]); ?>选择备货','<?php echo U('Order/toStock/',array('tid'=>$order['tid']));?>','1','800','500')">						 	
									<span class="label label-success radius  overbtn" data="<?php echo ($order["tid"]); ?>" style="margin-top: 10px;">选择备货</span>	
								</a>								
							<!--备货-->
							<?php  break; } } } ?>
							<!--发货-->
							<?php  foreach($order['items'] as $key=>$value){ if($value['sendnum'] == 0 && $value['disabled']==0){ if(in_array($value['aftersales_status'],array('NO_APPLY','CANCEL_APPLY','SELLER_REFUSE'))){ ?>
						 		<br />		
									<?php if(in_array(($order["status"]), explode(',',"WAIT_BUYER_CONFIRM_GOODS,IN_STOCK"))): ?><a href="javascript::" onclick="layer_show('订单:<?php echo ($order["tid"]); ?>发货','<?php echo U('Order/sendGoods/',array('tid'=>$order['tid']));?>','1','800','500')">						 	
											<span class="label label-success radius overbtn" style="margin-top: 10px;">发货</span>
										</a><?php endif; ?>	
							<?php  break; } } } ?>
							<!--发货-->	
						</td>
					</tr><?php endforeach; endif; else: echo "" ;endif; ?>					

                </table>
                <div class="list-page">
                    <?php echo ($page); ?>
                </div>
            </div>
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