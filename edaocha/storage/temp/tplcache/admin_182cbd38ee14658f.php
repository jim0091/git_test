<dl class="pop-area">
  <dt class="mb10" style="overflow:hidden;*zoom:1;"><div class="l">选择频道：</div>
    <div class="r">
        <?php if(is_array($list)): ?><?php $i = 0;?><?php $__LIST__ = $list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><input type="checkbox" name="channel" value="<?php echo ($vo["channel_category_id"]); ?>"  <?php if(in_array($vo['channel_category_id'],$channel)){ ?>checked<?php } ?>/><?php echo ($vo["title"]); ?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    </div>
  </dt>
  <dd style="text-align:center;margin:15px 0 0 ">
        <input type="button" class="btn_b" onclick="editChannel()" value="确定" />
        <input type="button" class="btn_w ml10" onclick="cancel()" value="取消" />
  </dd>
  <div class="clear"></div>
</dl>

<script type="text/javascript">
// 编辑内容
function editChannel() {
    var feed_id = "<?php echo ($feed_id); ?>";
    var checkID = [];
    $("input[name='channel']:checked").each(function(i){
        checkID.push(this.value)
    });
	if(checkID == '') {
        ui.error('请选择频道');
        return false;
    }
	$.post("<?php echo U('admin/Content/editChannel');?>", {channel:checkID,feed_id:feed_id}, function(msg){
		  admin.ajaxReload(msg);
	},'json');
}
// 关闭弹窗
function cancel() {
	ui.box.close();
}
</script>