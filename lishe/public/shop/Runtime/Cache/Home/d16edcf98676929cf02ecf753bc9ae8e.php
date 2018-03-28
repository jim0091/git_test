<?php if (!defined('THINK_PATH')) exit();?>
	<?php if(is_array($traderateList)): $i = 0; $__LIST__ = $traderateList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tdvo): $mod = ($i % 2 );++$i;?><li>
			<div class="content_left">
				<div class="stars stars-3"></div>
				<p class="time"><?php echo (date('Y-m-d H:i:s',$tdvo['created_time'])); ?></p>
				<p class="goods_size"><?php echo ($tdvo['item_title']); ?></p>
			</div>
			<div class="content_right">
				<p><?php echo ($tdvo['content']); ?></p>
				<?php $imgArry=array(); if(!empty($tdvo['rate_pic'])){ $imgArry = explode(",",$tdvo['rate_pic']);} ?>
				<div class="user_show">
					<?php if(!empty($imgArry)): if(is_array($imgArry)): $i = 0; $__LIST__ = $imgArry;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$iavo): $mod = ($i % 2 );++$i;?><img src="<?php echo ($iavo); ?>" class="pics"><?php endforeach; endif; else: echo "" ;endif; endif; ?>
				</div>

			</div>
			<div class="content_userinfo">
				<p class="content_username"><?php $userInfo=getTableRow('sysuser_account','user_id',$tdvo['user_id']); echo ($userInfo['mobile']); ?></p>
				<p class="content_comefrom">来自礼舍心意商城</p>
			</div>
		</li><?php endforeach; endif; else: echo "" ;endif; ?>
	<div class="pages">
		<?php echo ($pagestr); ?>
	</div>
<style type="text/css">
	.pages a.prev{width: 68px;}
	.pages div a{margin-left: 5px;}
	.pages a.next{width:68px;}
</style>

<script type="text/javascript">
	$(function(){
		$(".pages").children("div").children("a").removeAttr('href');
	});
	
</script>