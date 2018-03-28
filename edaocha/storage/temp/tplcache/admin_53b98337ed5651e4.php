<p><?php echo (replaceurl(htmtocode(t($body)))); ?></p>
<div class="feed_img_lists" >
    <ul class="small">

        <?php $attachCount=count($attachInfo);
            switch($attachCount){
            case '1' :
            $style = 'style="width:205px;height:auto"';
            $imgmode = 'attach_middle_box';
            break;
            case '2' :
            $style = 'style="width:240px;height:240px"';
            $imgmode = 'attach_middle_box';
            break;
            case '3' :
            $style = 'style="width:120px;height:120px;overflow:hidden;"';
            $imgmode = 'attach_small';
            break;
            case '4' :
            $style = 'style="width:240px;height:240px;overflow:hidden;"';
            $imgmode = 'attach_middle_box';
            break;
            case '5' :
            $style = 'style="width:120px;height:120px;overflow:hidden;"';
            $imgmode = 'attach_small';
            break;
            case '6' :
            $style = 'style="width:120px;height:120px;overflow:hidden;"';
            $imgmode = 'attach_small';
            break;
            case '7' :
            $style = 'style="width:120px;height:120px;overflow:hidden;"';
            $imgmode = 'attach_small';
            break;
            case '8' :
            $style = 'style="width:120px;height:120px;overflow:hidden;"';
            $imgmode = 'attach_small';
            break;
            case '9' :
            $style = 'style="width:120px;height:120px;overflow:hidden;"';
            $imgmode = 'attach_small';
            break;
            } ?>
        <?php if(is_array($attachInfo)): ?><?php $i = 0;?><?php $__LIST__ = $attachInfo?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li rel="<?php echo ($vo["attach_id"]); ?>" <?php echo ($style); ?>>
                <a href="javascript:void(0);" onclick="core.weibo.showBigImage(<?php echo ($feedid); ?>, <?php echo ($i); ?>)" >
                    <img class="imgicon" src='<?php echo ($vo[$imgmode]); ?>'>
                    <!--共有<?php echo ($attachCount); ?>张图片-->
                </a>
            </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    </ul>
</div>