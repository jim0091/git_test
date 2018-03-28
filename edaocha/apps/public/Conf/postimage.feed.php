<p>{$body|t|htmtocode|replaceUrl}</p>
<div class="feed_img_lists" >
    <ul class="small">

        <php>
            $attachCount=count($attachInfo);
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
            }
        </php>
        <volist name='attachInfo' id='vo'>
            <li rel="{$vo.attach_id}" {$style}>
                <a href="javascript:void(0);" onclick="core.weibo.showBigImage({$feedid}, {$i})" >
                    <img class="imgicon" src='{$vo[$imgmode]}'>
                    <!--共有{$attachCount}张图片-->
                </a>
            </li>
        </volist>
    </ul>
</div>