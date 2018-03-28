<eq name='body' value=''> 分享分享 </eq> 
{$body|t|htmtocode|replaceUrl}

<div class="comment-box">
	<p>@{$sourceInfo['source_user_info']['uname']}</p>
	<p class="desc">{:msubstr(t($sourceInfo['source_content']),0,100)}</p>
	<php>if(!empty($sourceInfo['attach'])):</php>
		{* 附件分享 *}
		<eq name='sourceInfo.feedType' value='postfile'>
			<!--<ul class="feed_file_list">
				<volist name='sourceInfo.attach' id='vo'>
				<li>
					<a href="{:U('widget/Upload/down',array('attach_id'=>$vo['attach_id']))}" class="current right" target="_blank"><i class="ico-down"></i></a>
					<i class="ico-{$vo.extension}-small"></i>
					<a href="{:U('widget/Upload/down',array('attach_id'=>$vo['attach_id']))}">{$vo.attach_name}</a>
					<span class="tips">({$vo.size|byte_format})</span>
				</li>
				</volist>			
			</ul>-->
		</eq>

		{* 图片分享 *}
		<eq name='sourceInfo.feedType' value='postimage'>
			<div class="feed_img" rel='small' >
				<ul class="small">
	                <volist name='sourceInfo.attach' id='vo'>
	                	<li style="width:120px;"><a href="javascript:void(0)" onclick="core.weibo.showBigImage('{$sourceInfo['feed_id']}', {$i})"><img src="{$vo.attach_small}"></a></li>
	                </volist>
				</ul>
			</div>
		</eq>

		<php>else:</php>
			{* 视频分享 *}
			<eq name='sourceInfo.feedType' value='postvideo'>
				<div class="feed_img" id="video_mini_show_{$feedid}_{$sourceInfo['feed_id']}" style="margin-bottom:20px;">
					  <a href="javascript:void(0);" <php>if(!$sourceInfo['transfering']){</php>onclick="switchVideo({$sourceInfo['feed_id']},{$feedid},'open','{$sourceInfo.host}','{$sourceInfo.flashvar}','{:strpos($sourceInfo['flashimg'], '://')?$sourceInfo['flashimg']:getImageUrl($sourceInfo['flashimg'], 150, 100),'490','490'}')"<php>}</php> >
					    <img src="{:strpos($sourceInfo['flashimg'], '://')?$sourceInfo['flashimg']:getImageUrl($sourceInfo['flashimg'])}" style="width:100%;overflow:hidden;" data-medz-name="user-outside-video"  onerror="javascript:var default_img = THEME_URL + '/image/video_bk.png';$(this).attr('src',default_img);">
					  </a>
					  <div class="video_play" ><a href="javascript:void(0);" <php>if(!$sourceInfo['transfering']){</php>onclick="switchVideo({$sourceInfo['feed_id']},{$feedid},'open','{$sourceInfo.host}','{$sourceInfo.flashvar}','{$sourceInfo.flashimg}','490','490')"<php>}</php> ></a>
					  </div>
				</div>
				<div class="feed_quote" style="display:none;margin-bottom:20px;" id="video_show_{$feedid}_{$sourceInfo['feed_id']}">
				  <div class="q_tit">
				    <img class="q_tit_l" onclick="switchVideo({$sourceInfo['feed_id']},{$feedid},'open','{$sourceInfo.host}','{$sourceInfo.flashvar}','{$sourceInfo.flashimg}','490','490')" src="__THEME__/image/zw_img.gif" />
				  </div>
				  <div class="q_con"> 
				    <p style="margin:0;margin-bottom:5px" class="cGray2 f12">
				    <a href="javascript:void(0)" onclick="switchVideo({$sourceInfo['feed_id']},{$feedid},'close')"><i class="ico-pack-up"></i>收起</a>
				    
				    </p>
				    <div id="video_content_{$feedid}_{$sourceInfo['feed_id']}"></div>
				  </div>
				  <!--<div class="q_btm"><img class="q_btm_l" src="__THEME__/image/zw_img.gif" /></div>-->
				</div>
			</eq>
			<eq name='sourceInfo.feedType' value='post'>
			</eq>

		<php>endif;</php>
		<div class="info">{$sourceInfo['publish_time']|friendlyDate} {:getFromClient($sourceInfo['from'])} <span><i class="count"></i>点赞</span><span><i class="comment"></i>评论</span><span><i class="report"></i>转发</span></div>
</div>