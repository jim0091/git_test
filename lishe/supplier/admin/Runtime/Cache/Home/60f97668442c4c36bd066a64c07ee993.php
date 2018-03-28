<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<!--[if lt IE 9]>
<script type="text/javascript" src="/Public/admin/lib/html5.js"></script>
<script type="text/javascript" src="/Public/admin/lib/respond.min.js"></script>
<script type="text/javascript" src="/Public/admin/lib/PIE_IE678.js"></script>
<![endif]-->
<link href="/Public/admin/css/H-ui.min.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/css/H-ui.admin.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/lib/icheck/icheck.css" rel="stylesheet" type="text/css" />
<link href="/Public/admin/lib/Hui-iconfont/1.0.1/iconfont.css" rel="stylesheet" type="text/css" />
<!--[if IE 6]>
<script type="text/javascript" src="/Public/admin/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>基本设置</title>
</head>
<body>
<style>
	.tabBar span.current{
		background-color: #5EB95E;	
	}
	.tabBar {
    	border-bottom: 2px solid #5EB95E;
	}
	.table-bg thead th {
		background-color: #5EB95E;	
		color: white;
	}
</style>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 商品管理 <span class="c-gray en">&gt;</span> 商品详情  <a class="btn btn-success radius r mr-20" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="pd-20">
		<div id="tab-system" class="HuiTab">
			<div class="tabBar cl"><span>基本信息</span><span>sku</span></div>
			<div class="tabCon">
			 <table class="table table-border table-bordered table-bg mt-20">
			    <thead>
			      <tr>
			        <th colspan="3" scope="col">商品基本信息</th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td width="200">商品号</td>
			        <td><span ><?php echo ($item['sitem_id']); ?></span></td>
			      </tr>
				  <tr>
					  <td>商品标题</td>
					  <td><?php echo ($item['title']); ?></td>
				  </tr>
				  <tr>
			        <td>所属供应商</td>
			        <td><?php echo ($supplier_user['company_name']); ?></td>
			      </tr>
			      <tr>
			        <td>所属店铺</td>
			        <td><?php echo ($shop['shop_name']); ?></td>
			      </tr>
			      <tr>
			        <td>品类</td>
			        <td><?php echo ($cat['cat_name']); ?></td>
			      </tr>
				  <tr>
					  <td>品牌</td>
					  <td><?php echo ($brand['brand_name']); ?></td>
				  </tr>
				  <tr>
			        <td>主条码</td>
			        <td><?php echo ($item['bn']); ?></td>
			      </tr>
			      <tr>
			        <td>条形码</td>
			        <td><?php echo ($item['barcode']); ?></td>
			      </tr>
			      <tr>			      
			      <tr>
			        <td>商品价格(￥)</td>
			        <td><?php echo ($item['mkt_price']); ?></td>
			      </tr>
				  <tr>
					  <td>进货价(￥)</td>
					  <td><?php echo ($item['cost_price']); ?></td>
				  </tr>
				  <tr>

					  <td>京东销售价(￥)</td>
					  <td><?php echo ($item['jd_price']); ?></td>
				  </tr>
				  <tr>
					  <td>毛利率(%)</td>
					  <td><?php echo ($item['profit_rate']); ?></td>
				  </tr>
				  <tr>
					  <td>商品重量(kg)</td>
					  <td><?php echo ($item['weight']); ?></td>
				  </tr>
				  <tr>
					  <td>商品长度(mm)</td>
					  <td><?php echo ($item['length']); ?></td>
				  </tr>
				  <tr>
					  <td>商品宽度(mm)</td>
					  <td><?php echo ($item['width']); ?></td>
				  </tr>
				  <tr>
					  <td>商品高度(mm)</td>
					  <td><?php echo ($item['height']); ?></td>
				  </tr>
				  <tr>
					  <td>商品最后修改时间</td>
					  <td><?php echo ($item['modified_time']); ?></td>
				  </tr>
				  <!--tr>
					  <td>是否违规</td>
					  <td> <?php if($item['violation'] == 1 ): ?>是
						  <?php else: ?>
						  否<?php endif; ?>
					  </td>
				  </tr-->
				</tbody>
		      </table>
		      


			 <table class="table table-border table-bordered table-bg mt-20" style="display: none;">
			    <thead>
			      <tr>
			        <th colspan="2" scope="col">售后信息</th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td width="200">售后</td>
			        <td>
						<?php if(in_array(($info["order_status"]), explode(',',"REFUND,RETURN,EXCHANGE,REPAIR"))): ?><span class="label label-danger radius">
						<?php else: ?>
							<?php if(in_array(($info["order_status"]), explode(',',"CANCEL_REFUND,CANCEL_RETURN,CANCEL_EXCHANGE,CANCEL_REPAIR"))): ?><span class="label radius">
								<?php else: ?>
								<span><?php endif; endif; ?>
						<?php echo ($info["serviceStatus"]); ?>
						</span>
			        </td>
			      </tr>
			      <tr>
			      	<td>退款金额：</td>
			      	<td>￥<?php echo ($info["refund_fee"]); ?></td>
			      </tr>
			      <tr>
			      	<td>操作退款备注：</td>
			      	<td><?php echo ($info["refund_mark"]); ?></td>
			      </tr>
			      <tr>
			      	<td>退款时间：</td>
			      	<td>
			      		<?php if(!empty($info["refund_time"])): echo (date("Y-m-d H:i:s",$info['refund_time'])); endif; ?>
			      	</td>
			      </tr>
			    </tbody>
		    </table>

			 <table class="table table-border table-bordered table-bg mt-20" style="display: none;">
			    <thead>
			      <tr>
			        <th colspan="2" scope="col">收货人信息</th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td width="200">收货人</td>
			        <td><span><?php echo ($info["receiver_name"]); ?></span></td>
			      </tr>
			      <tr>
			        <td>收货人手机号</td>
			        <td><span><?php echo ($info["receiver_mobile"]); ?></span></td>
			      </tr>
			      <tr>
			        <td>收货地区</td>
			        <td><span><?php echo ($info["receiver_state"]); echo ($info["receiver_city"]); echo ($info["receiver_district"]); ?></span></td>
			      </tr>
			      <tr>
			        <td>收货地址</td>
			        <td><span><?php echo ($info["receiver_address"]); ?></span></td>
			      </tr>
			      <tr>
			      	<td>自提备注</td>
			      	<td><span><?php echo ($info["ziti_memo"]); ?></span></td>
			      </tr>
			    </tbody>
			  </table>

			 <table class="table table-border table-bordered table-bg mt-20" style="display: none;">
			    <thead>
			      <tr>
			        <th colspan="5" scope="col">物流</th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <th>发货单号</th>
			        <th align="center">物流单号</th>
			        <th>配送方式</th>
			        <th>建立日期</th>
			        <th>状态</th>
			      </tr>
			      <?php if(is_array($info["express"])): $i = 0; $__LIST__ = $info["express"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$express): $mod = ($i % 2 );++$i;?><tr>
				        <td><?php echo ($express["delivery_id"]); ?></td>
				        <td>
				        	<?php if(!empty($express["logi_no"])): $logNOs=explode(',',$express['logi_no']); ?>
					        	<?php if(is_array($logNOs)): $i = 0; $__LIST__ = $logNOs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$no): $mod = ($i % 2 );++$i;?><a href="javascript:void(0)" onclick="member_edit('物流进度信息','<?php echo U('Home/Order/expressProgress',array('logiNo'=>$no,'shop_id'=>$info['shop_id']));?>','4','500','400')">
										<span class="label label-success radius" style="margin-top: 10px;"><?php echo ($no); ?></span>
									</a>
									<br /><?php endforeach; endif; else: echo "" ;endif; endif; ?>
				        </td>
				        <td><?php echo ($express["logi_name"]); ?></td>
				        <td>
				        	<?php if(!empty($express["t_begin"])): echo (date("Y-m-d H:i:s",$express['t_begin'])); endif; ?>
				        </td>
				        <td><?php echo ($express["status"]); ?></td>
				      </tr><?php endforeach; endif; else: echo "" ;endif; ?>
			    </tbody>
			  </table>

			 <table class="table table-border table-bordered table-bg mt-20" style="display: none;">
			    <thead>
			      <tr>
			        <th colspan="2" scope="col">订单发票信息</th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td width="200">是否需要发票</td>
			        <td><span><?php echo ($info["need_invoice"]); ?></span></td>
			      </tr>
			      <tr>
			        <td width="200">发票抬头</td>
			        <td><span><?php echo ($info["invoice_name"]); ?></span></td>
			      </tr>
			      <tr>
			        <td width="200">发票类型</td>
			        <td><span><?php echo ($info["invoice_type"]); ?></span></td>
			      </tr>
			      <tr>
			        <td width="200">发票内容</td>
			        <td><span><?php echo ($info["invoice_main"]); ?></span></td>
			      </tr>
			    </tbody>
			  </table>


			</div>

			<div class="tabCon">
				<?php if(is_array($sku)): $i = 0; $__LIST__ = $sku;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><table class="table table-border table-bordered table-bg mt-20">
					<thead>
					<tr>
						<th colspan="3" scope="col"><?php echo ($list['title']); ?></th>
					</tr>
					</thead>
					<tbody>

					<tr>
						<td width="200">skuID</td>
						<td><span><?php echo ($list['ssku_id']); ?></span></td>
					</tr>
					<tr>
						<td width="200">商品名称</td>
						<td><span><?php echo ($list['title']); ?></span></td>

					</tr>
					<tr>
						<td>主编码</td>
						<td><?php echo ($list['bn']); ?></td>
					</tr>
					<tr>
						<td>价格(￥)</td>
						<td><?php echo ($list['price']); ?></td>
					</tr>
					<tr>
						<td>进货价(￥)</td>
						<td><?php echo ($list['cost_price']); ?></td>
					</tr>
					<tr>
						<td>建议零售价(￥)</td>
						<td><?php echo ($list['mkt_price']); ?></td>
					</tr>
					<tr>
						<td>条形码</td>
						<td><?php echo ($list['barcode']); ?></td>
					</tr>
					<tr>
						<td>尺寸(mm)</td>
						<td><?php echo ($list['size']); ?></td>
					</tr>
					<tr>
						<td>重量(kg)</td>
						<td><?php echo ($list['weight']); ?></td>
					</tr>

					<!--tr>
						<td>sku销售属性</td>
						<td><?php echo ($list['properties']); ?></td>
					</tr-->
					<tr>
						<td>物品描述</td>
						<td><?php echo ($list['spec_info']); ?></td>
					</tr>
					<!--tr>
						<td>物品详情</td>
						<td><?php echo ($list['spec_desc']); ?></td>
					</tr-->
					<tr>
						<td>状态</td>
						<td><?php echo ($list['status']); ?></td>
					</tr>
					<tr>
						<td>商家设置外部ID</td>
						<td><?php echo ($list['outer_id']); ?></td>
					</tr>
					<tr>
						<td>创建时间</td>
						<td><?php echo ($list['created_time']); ?></td>
					</tr>


					</tbody>
				</table><?php endforeach; endif; else: echo "" ;endif; ?>
			</div>				

			
			<div class="tabCon">
			 <table class="table table-border table-bordered table-bg mt-20">
			    <thead>
			      <tr>
			        <th colspan="2" scope="col">会员信息</th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td>用户ID</td>
			        <td><?php echo ($info["user_id"]); ?></td>
			      </tr>			    	
			      <tr>
			        <td>用户姓名</td>
			        <td><?php echo ($info["userInfo"]["name"]); ?></td>
			      </tr>
			      <tr>
			        <td>企业名称/ID</td>
			        <td><?php echo ($info["companyName"]); ?>/<?php echo ($info["com_id"]); ?></td>
			      </tr>			     
			      <tr>
			        <td>出生日期</td>
			        <td><?php echo ($info["userInfo"]["birthday"]); ?></td>
			      </tr>			     
			      <tr>
			        <td>手机</td>
			        <td><?php echo ($info["userInfo"]["mobile"]); ?></td>
			      </tr>			     
			      <tr>
			        <td>性别</td>
			        <td><?php echo ($info["userInfo"]["sex"]); ?></td>
			      </tr>		
			      <tr>
			        <td>注册日期</td>
			        <td><?php echo ($info["userInfo"]["regtime"]); ?></td>
			      </tr>	
			      <tr>
			        <td>注册IP</td>
			        <td><?php echo ($info["userInfo"]["reg_ip"]); ?></td>
			      </tr>				      
			    </tbody>
		      </table>
			</div>
			<div class="tabCon">
			 <table class="table table-border table-bordered table-bg mt-20">
			    <thead>
			      <tr>
			        <th colspan="2" scope="col"><?php echo ($info["shopinfo"]["shop_name"]); ?></th>
			      </tr>
			    </thead>
			    <tbody>
			      <tr>
			        <td width="200">店铺名称</td>
			        <td><span ><?php echo ($info["shopinfo"]["shop_name"]); ?></span></td>
			      </tr>
			      <tr>
			        <td>店铺描述</td>
			        <td><?php echo ($info["shopinfo"]["shop_descript"]); ?></td>
			      </tr>
			      <tr>
			        <td>店铺类型</td>
			        <td><?php echo ($info["shopinfo"]["shop_type"]); ?></td>
			      </tr>
			      <tr>
			        <td>店主姓名</td>
			        <td><?php echo ($info["shopinfo"]["shopuser_name"]); ?></td>
			      </tr>				      
			      <tr>
			        <td>开店时间</td>
			        <td><?php echo ($info["shopinfo"]["open_time"]); ?></td>
			      </tr>			      
			      <tr>
			        <td>店铺所在地区</td>
			        <td><?php echo ($info["shopinfo"]["shop_area"]); echo ($info["shopinfo"]["shop_addr"]); ?></td>
			      </tr>				      
			    </tbody>
			  </table>
			</div>
			<div class="tabCon">
			 <table class="table table-border table-bordered table-bg mt-20">
			    <thead>
			      <tr>
			        <th colspan="2" scope="col">其他信息</th>
			      </tr>
			    </thead>
			    <tbody>
			        <td  width="200">商品重量</td>
			        <td><?php echo ($info["total_weight"]); ?></td>
			      </tr>			      
			      <tr>
			        <td>买家返点积分</td>
			        <td><?php echo ($info["obtain_point_fee"]); ?></td>
			      </tr>	
			      <tr>
			        <td>卖家手工调整金额</td>
			        <td>￥<?php echo ($info["adjust_fee"]); ?></td>
			      </tr>				      
			      <tr>
			        <td>来源平台</td>
			        <td><?php echo ($info["trade_from"]); ?></td>
			      </tr>	
			      <tr>
			        <td>ip地址</td>
			        <td><?php echo ($info["ip"]); ?></td>
			      </tr>	
			      <tr>
			        <td>最后更新时间</td>
			        <td><?php echo ($info["modified_time"]); ?></td>
			      </tr>				      
			    </tbody>
			  </table>				
			</div>
			<div class="tabCon">
				<form action="<?php echo U('Home/Order/addMemo');?>" method="post" >
				<div style="background-color: #5EB95E;color: white;margin-top: 20px;padding: 8px;margin-bottom: 30px;">
					备注信息
				</div>	
					<input type="hidden" value="<?php echo ($info["tid"]); ?>" name="tid"/>
				    <div class="row cl">
				      <label class="form-label col-2">备注：</label>
				      <div class="formControls col-8">
				        <textarea name="shopMemo" cols="" rows="" class="textarea" placeholder="添加备注" datatype="*10-100" dragonfly="true" nullmsg="备注不能为空！"><?php echo ($info["shop_memo"]); ?></textarea>
				      </div>
				    </div>
					<?php  $applFundId=array_search('addmemo', $nodeAction); if($applFundId){ if($nodeController[$applFundId]=="order" || $roleId==0){ ?>						    
				    <div class="row cl" style="margin-top: 10px;">
				      <div class="col-9 col-offset-2">
				        <input class="btn btn-primary radius" value="&nbsp;&nbsp;保存&nbsp;&nbsp;" type="submit">
				      </div>
				    </div>
					<?php } } ?>					    
				    
				  </form>
			</div>			
			
		</div>
		<!--<div class="row cl">
			<div class="col-10 col-offset-2">
				<button onClick="article_save_submit();" class="btn btn-primary radius" type="submit"><i class="Hui-iconfont">&#xe632;</i> 保存</button>
				<button onClick="layer_close();" class="btn btn-default radius" type="button">&nbsp;&nbsp;取消&nbsp;&nbsp;</button>
			</div>
		</div>-->
	<div id="open_div" style="background: #fff;width: 350px;height:150px;border-top:#eee;display: none;">
		<div class="row cl" style="margin:30px auto;margin-left: 30px;">
			<label class="form-label col-3">修改的值：</label>
			<div class="formControls col-8">
				<input type="text" id="input_box" class="input-text" value="" placeholder="" id="weight" name="weight">
			</div>
		</div>
		<div class="row cl" style="margin:30px auto;margin-left: 130px;">
			<input class="btn btn-primary radius" id="update_btn" type="button" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
		</div>
	</div>
	<div id="sku_div" style="background: #fff;width: 350px;height:150px;border-top:#eee;display: none;">
		<div class="row cl" style="margin:30px auto;margin-left: 30px;">
			<label class="form-label col-3">修改的值：</label>
			<div class="formControls col-8">
				<input type="text" id="input_sku" class="input-text" value="" placeholder="" >
			</div>
		</div>
		<div class="row cl" style="margin:30px auto;margin-left: 130px;">
			<input class="btn btn-primary radius" id="update_sku" type="button" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
		</div>
	</div>
	<div id="open_div_supplier" style="background: #fff;width: 350px;height:150px;border-top:#eee;display: none;">
		<div class="row cl" style="margin:30px auto;margin-left: 10px;">
			<label class="form-label col-3">修改的值：</label>
			<div class="formControls col-4">
				<select name="supplier_id"  id="supplier_id">
					<?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?><option value="<?php echo ($user['supplier_id']); ?>" <?php if(($supplier_user['supplier_id']) == $user['supplier_id']): ?>selected="selected"<?php endif; ?>
								><?php echo ($user['company_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</div>
		<div class="row cl" style="margin:30px auto;margin-left: 130px;">
			<input class="btn btn-primary radius" id="update_btn_supplier" type="button" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
		</div>
	</div>
	<div id="open_div_shop" style="background: #fff;width: 350px;height:150px;border-top:#eee;display: none;">
		<div class="row cl" style="margin:30px auto;margin-left: 10px;">
			<label class="form-label col-3">修改的值：
			</if></label>
			<div class="formControls col-4">
				<select name="supplier_id"  id="shop_id">
					<?php if(is_array($shopList)): $i = 0; $__LIST__ = $shopList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$shops): $mod = ($i % 2 );++$i;?><option value="<?php echo ($shops['shop_id']); ?>" <?php if(($shop['shop_id']) == $shops['shop_id']): ?>selected="selected"<?php endif; ?>
						><?php echo ($shops['shop_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</div>
		<div class="row cl" style="margin:30px auto;margin-left: 130px;">
			<input class="btn btn-primary radius" id="update_btn_shop" type="button" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
		</div>
	</div>
	<div id="open_div_cat" style="background: #fff;width: 350px;height:150px;border-top:#eee;display: none;">
		<div class="row cl" style="margin:30px auto;margin-left: 10px;">
			<label class="form-label col-3">修改的值：</label>
			<div class="formControls col-4">
				<select name="supplier_id"  id="cat_select_1">
					<?php if(is_array($users)): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?><option value="<?php echo ($user['supplier_id']); ?>"><?php echo ($user['company_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</div>
		<div class="row cl" style="margin:30px auto;margin-left: 130px;">
			<input class="btn btn-primary radius" id="update_btn_cat" type="button" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
		</div>
	</div>

</div>
<script type="text/javascript" src="/Public/admin/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/Public/admin/lib/Validform/5.3.2/Validform.min.js"></script>
<script type="text/javascript" src="/Public/admin/lib/layer/1.9.3/layer.js"></script>
<script type="text/javascript" src="/Public/admin/lib/icheck/jquery.icheck.min.js"></script>
<script type="text/javascript" src="/Public/admin/js/H-ui.js"></script>
<script type="text/javascript" src="/Public/admin/js/H-ui.admin.js"></script>
<script type="text/javascript">
	layer.config({
		extend: 'extend/layer.ext.js'
	});
$(function(){
	$('.skin-minimal input').iCheck({
		checkboxClass: 'icheckbox-blue',
		radioClass: 'iradio-blue',
		increaseArea: '20%'
	});
	$.Huitab("#tab-system .tabBar span","#tab-system .tabCon","current","click","0");
});
/*用户-编辑*/
function member_edit(title,url,id,w,h){
	layer_show(title,url,w,h);
}	
//同步京东订单前取消同步指定商品
$(".cancel").on('click',function(){
	var oid=$(this).attr('data');
	var tid="<?php echo ($info["tid"]); ?>";
	$(this).before("<span class='label radius'  title='同步京东订单时将不会同步该商品'>已取消</span>");
	$(this).hide();
	$.ajax({
		type:"get",
		url:"<?php echo U('Home/Order/cancelSyncGoods');?>",
		data:'oid='+oid+'&tid='+tid,
		success:function(data){
			if(data){
			//取消成功
				alert("取消成功,该商品将会进行申请退款！");
			}else{
			//取消失败
				alert("取消该条商品不用于同步失败!");
			}
		}
	});	
});
//重新同步订单到京东
$(".getSyncOrder").click(function(){
	var tid=$(this).attr("data");
	$.get("/business/api.php/Interface/syncOrder/tid/"+tid,function(data){     
		var msg=eval("("+data+")");
		if(parseInt(msg['code']) > 0){
			$('#syncOrder').text(msg['code']);
			$(".getSyncOrder").remove();
		}else{
			alert(msg['msg']);
		}
	});
});
	var fields_name = "";
	var goods_id = 0;
//修改字段
function edit_field(id,field_name){
	layer.open({
		type: 1,
		//skin: 'layui-layer-demo', //样式类名
		closeBtn: 1, //不显示关闭按钮
		shift: 2,
		shadeClose: true, //开启遮罩关闭
		content: $('#open_div')
	});
	var input_box=$('#input_box');
	var data={"sitem_id":id,"fields_name":field_name};
	$.post("<?php echo U('Supplier/getGoodsInfo','','');?>",data,function(text){
		input_box.val(text);
	});
	fields_name=field_name;
	goods_id=id;
}
	//修改
function editSelect(id,field_name,div_name){
	layer.open({
		type: 1,
		//skin: 'layui-layer-demo', //样式类名
		closeBtn: 1, //不显示关闭按钮
		shift: 2,
		shadeClose: true, //开启遮罩关闭
		content: $('#'+div_name)
	});
	var input_box=$('#input_box');
	var data={"sitem_id":id,"fields_name":field_name};
	$.post("<?php echo U('Supplier/getGoodsInfo','','');?>",data,function(text){
		input_box.val(text);
	});
	fields_name=field_name;
	goods_id=id;
}
var skuId="";
var skuFieldName="";
function editSKU(id,field_name){
	layer.open({
		type: 1,
		//skin: 'layui-layer-demo', //样式类名
		closeBtn: 1, //不显示关闭按钮
		shift: 2,
		shadeClose: true, //开启遮罩关闭
		content: $('#sku_div')
	});
	var input_box=$('#input_sku');
	var data={"ssku_id":id,"fields_name":field_name};
	$.post("<?php echo U('Supplier/getSkuInfo','','');?>",data,function(text){
		input_box.val(text);
	});

	skuId=id;
	skuFieldName=field_name;
}
	$('#update_sku').click(function(){
		var input_sku=$('#input_sku').val();

		var data={"ssku_id":skuId,"fields_name":skuFieldName,"content":input_sku};
		$.post("<?php echo U('Supplier/editSku','','');?>",data, function (text) {
			switch (text){
				case "1":
					layer.msg("修改成功");
					break;
				default :
					layer.msg("修改失败");
			}
		});
		//layer.closeAll(	);
		window.location.reload();
	});






$('#update_btn').click(function(){
	var input_box=$('#input_box').val();
	var data={"sitem_id":goods_id,"fields_name":fields_name,"content":input_box};
	$.post("<?php echo U('Supplier/modifyGoods','','');?>",data, function (text) {
		switch (text){
			case "1":
				layer.msg("修改成功");
				break;
			default :
				layer.msg("修改失败");
		}
	});
	//layer.closeAll(	);
	window.location.reload();
});
$('#update_btn_supplier').click(function(){
	var input_box=$('#supplier_id').val();
	var data={"sitem_id":goods_id,"fields_name":fields_name,"content":input_box};
	$.post("<?php echo U('Supplier/modifyGoods','','');?>",data, function (text) {
		switch (text){
			case "1":
				layer.msg("修改成功");
				break;
			default :
				layer.msg("修改失败");
		}
	});
	//layer.closeAll(	);
	window.location.reload();
});
	$('#update_btn_shop').click(function(){
		var input_box=$('#shop_id').val();
		var data={"sitem_id":goods_id,"fields_name":fields_name,"content":input_box};
		$.post("<?php echo U('Supplier/modifyGoods','','');?>",data, function (text) {
			switch (text){
				case "1":
					layer.msg("修改成功");
					break;
				default :
					layer.msg("修改失败");
			}
		});
		//layer.closeAll(	);
		window.location.reload();
	});
function delSKU(id){

	var data={"ssku_id":id};
	$.post("<?php echo U('Supplier/delSku','','');?>",data, function (text) {
		alert(text);
		switch (text){
			case "1":
				layer.msg("修改成功");
				break;
			default :
				layer.msg("修改失败");
		}
	});
	//layer.closeAll(	);
	window.location.reload();
}

</script>
</body>
</html>