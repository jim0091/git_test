<?php if (!defined('THINK_PATH')) exit();?>
	<?php if(is_array($consulList)): $i = 0; $__LIST__ = $consulList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$clvo): $mod = ($i % 2 );++$i;?><li>
			<p class="content_username"><?php echo ($clvo['author']); ?></p>
			<p class="time"><?php echo (date('Y-m-d H:i:s',$tdvo['created_time'])); ?></p>
			<div>
				<p class="consult_Q">咨询内容：<?php echo ($clvo['content']); ?></p>
				<div>
					<?php $replyInfo = getTableRow('sysrate_consultation','be_reply_id',$clvo['consultation_id']);?>
					<?php if(!empty($replyInfo)): ?><p class="consult_A">礼舍回复：<?php echo ($replyInfo['content']); ?></p>
						<p class="time"><?php echo (date('Y-m-d H:i:s',$replyInfo['created_time'])); ?></p><?php endif; ?>

				</div>
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