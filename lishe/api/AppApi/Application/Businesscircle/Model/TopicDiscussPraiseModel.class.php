<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈_帖子_讨论_点赞模型];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Model/];
 * +----------------------------------------------------------------------
 * |@Name:			[BusinesscircleTopicDiscussPraiseModel.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-3-14 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2017
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Model;
use Think\Model;
class TopicDiscussPraiseModel extends Model
{
	 
	protected $pk = 'id';
	protected $autoinc = true;
	protected $trueTableName = 'businesscircle_topic_discuss_praise';
	
}