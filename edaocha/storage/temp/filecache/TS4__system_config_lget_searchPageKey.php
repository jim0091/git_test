<?php
return array (
  'S_admin_Content_comment' => 
  array (
    'key' => 
    array (
      'comment_id' => 'comment_id',
      'uid' => 'uid',
      'app_uid' => 'app_uid',
    ),
    'key_name' => 
    array (
      'comment_id' => '评论ID',
      'uid' => '评论者ID',
      'app_uid' => '作者ID',
    ),
    'key_type' => 
    array (
      'comment_id' => 'text',
      'uid' => 'text',
      'app_uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'comment_id' => '多个id之间用英文的","隔开',
      'uid' => '多个id之间用英文的","隔开',
      'app_uid' => '多个id之间用英文的","隔开',
    ),
    'key_javascript' => 
    array (
      'comment_id' => '',
      'uid' => '',
      'app_uid' => '',
    ),
  ),
  'S_admin_Home_invatecount' => 
  array (
    'key' => 
    array (
      'inviter_uid' => 'inviter_uid',
      'receiver_uid' => 'receiver_uid',
    ),
    'key_name' => 
    array (
      'inviter_uid' => '邀请人ID',
      'receiver_uid' => '被邀请人ID',
    ),
    'key_type' => 
    array (
      'inviter_uid' => 'text',
      'receiver_uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'inviter_uid' => '',
      'receiver_uid' => '',
    ),
    'key_javascript' => 
    array (
      'inviter_uid' => '',
      'receiver_uid' => '',
    ),
  ),
  'S_admin_Home_invateTop' => 
  array (
    'key' => 
    array (
      'inviter_uid' => 'inviter_uid',
    ),
    'key_name' => 
    array (
      'inviter_uid' => '邀请人ID',
    ),
    'key_type' => 
    array (
      'inviter_uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'inviter_uid' => '',
    ),
    'key_javascript' => 
    array (
      'inviter_uid' => '',
    ),
  ),
  'S_admin_Content_message' => 
  array (
    'key' => 
    array (
      'from_uid' => 'from_uid',
      'mix_man' => 'mix_man',
      'content' => 'content',
    ),
    'key_name' => 
    array (
      'from_uid' => '私信发送者ID',
      'mix_man' => '私信成员ID',
      'content' => '私信内容',
    ),
    'key_type' => 
    array (
      'from_uid' => 'text',
      'mix_man' => 'text',
      'content' => 'text',
    ),
    'key_tishi' => 
    array (
      'from_uid' => '',
      'mix_man' => '',
      'content' => '',
    ),
    'key_javascript' => 
    array (
      'from_uid' => '',
      'mix_man' => '',
      'content' => '',
    ),
  ),
  'S_admin_Home_logs' => 
  array (
    'key' => 
    array (
      'uname' => 'uname',
      'app_name' => 'app_name',
      'ctime' => 'ctime',
      'isAdmin' => 'isAdmin',
      'keyword' => 'keyword',
    ),
    'key_name' => 
    array (
      'uname' => '用户帐号',
      'app_name' => '操作详情',
      'ctime' => '时间范围',
      'isAdmin' => '知识类型',
      'keyword' => '查询关键字',
    ),
    'key_type' => 
    array (
      'uname' => 'text',
      'app_name' => 'select',
      'ctime' => 'date',
      'isAdmin' => 'checkbox',
      'keyword' => 'text',
    ),
    'key_tishi' => 
    array (
      'uname' => '',
      'app_name' => '',
      'ctime' => '',
      'isAdmin' => '',
      'keyword' => '',
    ),
    'key_javascript' => 
    array (
      'uname' => '',
      'app_name' => 'admin.selectLog(this.value)',
      'ctime' => '',
      'isAdmin' => '',
      'keyword' => '',
    ),
  ),
  'S_admin_Home_tag' => 
  array (
    'key' => 
    array (
      'name' => 'name',
      'table' => 'table',
    ),
    'key_name' => 
    array (
      'name' => '标签名',
      'table' => '标签类型',
    ),
    'key_type' => 
    array (
      'name' => 'text',
      'table' => 'select',
    ),
    'key_tishi' => 
    array (
      'name' => '',
      'table' => '',
    ),
    'key_javascript' => 
    array (
      'name' => '',
      'table' => '',
    ),
  ),
  'S_admin_User_online' => 
  array (
    'key' => 
    array (
      'uid' => 'uid',
      'uname' => 'uname',
      'email' => 'email',
      'sex' => 'sex',
      'user_group' => 'user_group',
      'ctime' => 'ctime',
    ),
    'key_name' => 
    array (
      'uid' => 'UID',
      'uname' => '用户昵称',
      'email' => 'Email',
      'sex' => '性别',
      'user_group' => '用户组',
      'ctime' => '注册时间',
    ),
    'key_type' => 
    array (
      'uid' => 'text',
      'uname' => 'text',
      'email' => 'text',
      'sex' => 'radio',
      'user_group' => 'select',
      'ctime' => 'date',
    ),
    'key_tishi' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'sex' => '',
      'user_group' => '',
      'ctime' => '',
    ),
    'key_javascript' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'sex' => '',
      'user_group' => '',
      'ctime' => '',
    ),
  ),
  'S_admin_User_dellist' => 
  array (
    'key' => 
    array (
      'uid' => 'uid',
      'uname' => 'uname',
      'email' => 'email',
      'sex' => 'sex',
      'user_group' => 'user_group',
      'user_category' => 'user_category',
      'ctime' => 'ctime',
    ),
    'key_name' => 
    array (
      'uid' => '用户ID',
      'uname' => '用户帐号',
      'email' => 'Email',
      'sex' => '性别',
      'user_group' => '用户组',
      'user_category' => '',
      'ctime' => '注册时间',
    ),
    'key_type' => 
    array (
      'uid' => 'text',
      'uname' => 'text',
      'email' => 'text',
      'sex' => 'radio',
      'user_group' => 'select',
      'user_category' => 'text',
      'ctime' => 'date',
    ),
    'key_tishi' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'sex' => '',
      'user_group' => '',
      'user_category' => '',
      'ctime' => '',
    ),
    'key_javascript' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'sex' => '',
      'user_group' => '',
      'user_category' => '',
      'ctime' => '',
    ),
  ),
  'S_weiba_Admin_postList' => 
  array (
    'key' => 
    array (
      'post_id' => 'post_id',
      'title' => 'title',
      'post_uid' => 'post_uid',
      'recommend' => 'recommend',
      'digest' => 'digest',
      'top' => 'top',
      'weiba_id' => 'weiba_id',
    ),
    'key_name' => 
    array (
      'post_id' => '帖子ID',
      'title' => '帖子标题',
      'post_uid' => '发帖人ID',
      'recommend' => '是否推荐',
      'digest' => '是否精华',
      'top' => '是否置顶',
      'weiba_id' => '所属微吧',
    ),
    'key_type' => 
    array (
      'post_id' => 'text',
      'title' => 'text',
      'post_uid' => 'text',
      'recommend' => 'radio',
      'digest' => 'radio',
      'top' => 'radio',
      'weiba_id' => 'select',
    ),
    'key_tishi' => 
    array (
      'post_id' => '',
      'title' => '',
      'post_uid' => '',
      'recommend' => '',
      'digest' => '',
      'top' => '',
      'weiba_id' => '',
    ),
    'key_javascript' => 
    array (
      'post_id' => '',
      'title' => '',
      'post_uid' => '',
      'recommend' => '',
      'digest' => '',
      'top' => '',
      'weiba_id' => '',
    ),
  ),
  'S_weiba_Admin_postRecycle' => 
  array (
    'key' => 
    array (
      'post_id' => 'post_id',
      'title' => 'title',
      'post_uid' => 'post_uid',
      'weiba_id' => 'weiba_id',
    ),
    'key_name' => 
    array (
      'post_id' => '帖子ID',
      'title' => '帖子标题',
      'post_uid' => '发帖人ID',
      'weiba_id' => '所属微吧',
    ),
    'key_type' => 
    array (
      'post_id' => 'text',
      'title' => 'text',
      'post_uid' => 'text',
      'weiba_id' => 'select',
    ),
    'key_tishi' => 
    array (
      'post_id' => '',
      'title' => '',
      'post_uid' => '',
      'weiba_id' => '',
    ),
    'key_javascript' => 
    array (
      'post_id' => '',
      'title' => '',
      'post_uid' => '',
      'weiba_id' => '',
    ),
  ),
  'S_admin_Config_getInviteAdminList' => 
  array (
    'key' => 
    array (
      'invite_type' => 'invite_type',
    ),
    'key_name' => 
    array (
      'invite_type' => '邀请类型',
    ),
    'key_type' => 
    array (
      'invite_type' => 'radio',
    ),
    'key_tishi' => 
    array (
      'invite_type' => '',
    ),
    'key_javascript' => 
    array (
      'invite_type' => '',
    ),
  ),
  'S_admin_Content_topic' => 
  array (
    'key' => 
    array (
      'topic_id' => 'topic_id',
      'topic_name' => 'topic_name',
      'recommend' => 'recommend',
      'essence' => 'essence',
      'lock' => 'lock',
    ),
    'key_name' => 
    array (
      'topic_id' => '话题ID',
      'topic_name' => '话题名称',
      'recommend' => '是否推荐',
      'essence' => '是否精华',
      'lock' => '是否屏蔽',
    ),
    'key_type' => 
    array (
      'topic_id' => 'text',
      'topic_name' => 'text',
      'recommend' => 'radio',
      'essence' => 'radio',
      'lock' => 'radio',
    ),
    'key_tishi' => 
    array (
      'topic_id' => '',
      'topic_name' => '',
      'recommend' => '',
      'essence' => '',
      'lock' => '',
    ),
    'key_javascript' => 
    array (
      'topic_id' => '',
      'topic_name' => '',
      'recommend' => '',
      'essence' => '',
      'lock' => '',
    ),
  ),
  'S_admin_Medal_userMedal' => 
  array (
    'key' => 
    array (
      'user' => 'user',
      'medal' => 'medal',
    ),
    'key_name' => 
    array (
      'user' => '用户',
      'medal' => '勋章',
    ),
    'key_type' => 
    array (
      'user' => 'user',
      'medal' => 'select',
    ),
    'key_tishi' => 
    array (
      'user' => '',
      'medal' => '',
    ),
    'key_javascript' => 
    array (
      'user' => '',
      'medal' => '',
    ),
  ),
  'S_admin_Content_attach' => 
  array (
    'key' => 
    array (
      'attach_id' => 'attach_id',
      'name' => 'name',
      'from' => 'from',
    ),
    'key_name' => 
    array (
      'attach_id' => '附件ID',
      'name' => '附件名称',
      'from' => '来源类型',
    ),
    'key_type' => 
    array (
      'attach_id' => 'text',
      'name' => 'text',
      'from' => 'text',
    ),
    'key_tishi' => 
    array (
      'attach_id' => '多个id之间用英文的","隔开',
      'name' => '',
      'from' => '',
    ),
    'key_javascript' => 
    array (
      'attach_id' => '',
      'name' => '',
      'from' => '',
    ),
  ),
  'S_tipoff_Admin_index' => 
  array (
    'key' => 
    array (
      'tipoff_id' => 'tipoff_id',
      'content' => 'content',
      'uid' => 'uid',
      'status' => 'status',
    ),
    'key_name' => 
    array (
      'tipoff_id' => '爆料ID',
      'content' => '内容',
      'uid' => '发布者',
      'status' => '状态',
    ),
    'key_type' => 
    array (
      'tipoff_id' => 'text',
      'content' => 'text',
      'uid' => 'text',
      'status' => 'select',
    ),
    'key_tishi' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
    'key_javascript' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
  ),
  'S_tipoff_Admin_open' => 
  array (
    'key' => 
    array (
      'tipoff_id' => 'tipoff_id',
      'content' => 'content',
      'uid' => 'uid',
      'status' => 'status',
    ),
    'key_name' => 
    array (
      'tipoff_id' => '爆料ID',
      'content' => '内容',
      'uid' => '发布者',
      'status' => '状态',
    ),
    'key_type' => 
    array (
      'tipoff_id' => 'text',
      'content' => 'text',
      'uid' => 'text',
      'status' => 'select',
    ),
    'key_tishi' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
    'key_javascript' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
  ),
  'S_tipoff_Admin_bonus' => 
  array (
    'key' => 
    array (
      'tipoff_id' => 'tipoff_id',
      'content' => 'content',
      'uid' => 'uid',
    ),
    'key_name' => 
    array (
      'tipoff_id' => '爆料ID',
      'content' => '内容',
      'uid' => '发布者',
    ),
    'key_type' => 
    array (
      'tipoff_id' => 'text',
      'content' => 'text',
      'uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
    ),
    'key_javascript' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
    ),
  ),
  'S_tipoff_Admin_recycle' => 
  array (
    'key' => 
    array (
      'tipoff_id' => 'tipoff_id',
      'content' => 'content',
      'uid' => 'uid',
      'status' => 'status',
    ),
    'key_name' => 
    array (
      'tipoff_id' => '爆料ID',
      'content' => '内容',
      'uid' => '发布者',
      'status' => '状态',
    ),
    'key_type' => 
    array (
      'tipoff_id' => 'text',
      'content' => 'text',
      'uid' => 'text',
      'status' => 'select',
    ),
    'key_tishi' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
    'key_javascript' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
  ),
  'S_tipoff_Admin_archive' => 
  array (
    'key' => 
    array (
      'tipoff_id' => 'tipoff_id',
      'content' => 'content',
      'uid' => 'uid',
      'status' => 'status',
    ),
    'key_name' => 
    array (
      'tipoff_id' => '爆料ID',
      'content' => '内容',
      'uid' => '发布者',
      'status' => '状态',
    ),
    'key_type' => 
    array (
      'tipoff_id' => 'text',
      'content' => 'text',
      'uid' => 'text',
      'status' => 'select',
    ),
    'key_tishi' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
    'key_javascript' => 
    array (
      'tipoff_id' => '',
      'content' => '',
      'uid' => '',
      'status' => '',
    ),
  ),
  'S_admin_Config_lang' => 
  array (
    'key' => 
    array (
      'key' => 'key',
      'appname' => 'appname',
      'filetype' => 'filetype',
      'content' => 'content',
    ),
    'key_name' => 
    array (
      'key' => '语言KEY',
      'appname' => '应用名称',
      'filetype' => '文件类型',
      'content' => '语言内容',
    ),
    'key_type' => 
    array (
      'key' => 'text',
      'appname' => 'text',
      'filetype' => 'radio',
      'content' => 'text',
    ),
    'key_tishi' => 
    array (
      'key' => '',
      'appname' => '',
      'filetype' => '',
      'content' => '',
    ),
    'key_javascript' => 
    array (
      'key' => '',
      'appname' => '',
      'filetype' => '',
      'content' => '',
    ),
  ),
  'S_admin_User_pending' => 
  array (
    'key' => 
    array (
      'uid' => 'uid',
      'uname' => 'uname',
      'email' => 'email',
      'sex' => 'sex',
      'user_group' => 'user_group',
      'user_category' => 'user_category',
      'ctime' => 'ctime',
    ),
    'key_name' => 
    array (
      'uid' => 'UID',
      'uname' => '用户名',
      'email' => 'Email',
      'sex' => '性别',
      'user_group' => '用户组',
      'user_category' => '用户标签',
      'ctime' => '注册时间',
    ),
    'key_type' => 
    array (
      'uid' => 'text',
      'uname' => 'text',
      'email' => 'text',
      'sex' => 'radio',
      'user_group' => 'hidden',
      'user_category' => 'select',
      'ctime' => 'date',
    ),
    'key_tishi' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'sex' => '',
      'user_group' => '',
      'user_category' => '',
      'ctime' => '',
    ),
    'key_javascript' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'sex' => '',
      'user_group' => '',
      'user_category' => '',
      'ctime' => '',
    ),
  ),
  'S_weiba_Admin_weibaAdminAudit' => 
  array (
    'key' => 
    array (
      'follower_uid' => 'follower_uid',
      'weiba_name' => 'weiba_name',
    ),
    'key_name' => 
    array (
      'follower_uid' => '用户ID',
      'weiba_name' => '微吧名称',
    ),
    'key_type' => 
    array (
      'follower_uid' => 'text',
      'weiba_name' => 'text',
    ),
    'key_tishi' => 
    array (
      'follower_uid' => '',
      'weiba_name' => '',
    ),
    'key_javascript' => 
    array (
      'follower_uid' => '',
      'weiba_name' => '',
    ),
  ),
  'S_vtask_Admin_index' => false,
  'S_vtask_Admin_open' => false,
  'S_vtask_Admin_bonus' => false,
  'S_vtask_Admin_recycle' => false,
  'S_vtask_Admin_archive' => false,
  'S_weiba_Admin_index' => 
  array (
    'key' => 
    array (
      'weiba_id' => 'weiba_id',
      'weiba_name' => 'weiba_name',
      'weiba_cate' => 'weiba_cate',
      'uid' => 'uid',
      'admin_uid' => 'admin_uid',
      'recommend' => 'recommend',
    ),
    'key_name' => 
    array (
      'weiba_id' => '微吧ID',
      'weiba_name' => '微吧名称',
      'weiba_cate' => '微吧分类',
      'uid' => '创建者UID',
      'admin_uid' => '圈主UID',
      'recommend' => '是否推荐',
    ),
    'key_type' => 
    array (
      'weiba_id' => 'text',
      'weiba_name' => 'text',
      'weiba_cate' => 'select',
      'uid' => 'text',
      'admin_uid' => 'text',
      'recommend' => 'radio',
    ),
    'key_tishi' => 
    array (
      'weiba_id' => '',
      'weiba_name' => '',
      'weiba_cate' => '',
      'uid' => '',
      'admin_uid' => '',
      'recommend' => '',
    ),
    'key_javascript' => 
    array (
      'weiba_id' => '',
      'weiba_name' => '',
      'weiba_cate' => '',
      'uid' => '',
      'admin_uid' => '',
      'recommend' => '',
    ),
  ),
  'S_ask_Admin_index' => 
  array (
    'key' => 
    array (
      'title' => 'title',
      'department_id' => 'department_id',
      'status' => 'status',
      'ctime' => 'ctime',
      'order' => 'order',
    ),
    'key_name' => 
    array (
      'title' => '问答标题',
      'department_id' => '部门',
      'status' => '状态',
      'ctime' => '发表时间',
      'order' => '结果排序',
    ),
    'key_type' => 
    array (
      'title' => 'text',
      'department_id' => 'department',
      'status' => 'radio',
      'ctime' => 'date',
      'order' => 'select',
    ),
    'key_tishi' => 
    array (
      'title' => '',
      'department_id' => '',
      'status' => '',
      'ctime' => '',
      'order' => '',
    ),
    'key_javascript' => 
    array (
      'title' => '',
      'department_id' => '',
      'status' => '',
      'ctime' => '',
      'order' => '',
    ),
  ),
  'S_Information_Admin_index' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'name' => 'name',
    ),
    'key_name' => 
    array (
      'id' => '分类ID',
      'name' => '分类名称',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'name' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'name' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'name' => '',
    ),
  ),
  'S_Information_Admin_comment' => 
  array (
    'key' => 
    array (
      'comment_id' => 'comment_id',
      'uid' => 'uid',
      'app_uid' => 'app_uid',
    ),
    'key_name' => 
    array (
      'comment_id' => '评论ID',
      'uid' => '评论人ID',
      'app_uid' => '啊',
    ),
    'key_type' => 
    array (
      'comment_id' => 'text',
      'uid' => 'text',
      'app_uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'comment_id' => '',
      'uid' => '',
      'app_uid' => '',
    ),
    'key_javascript' => 
    array (
      'comment_id' => '',
      'uid' => '',
      'app_uid' => '',
    ),
  ),
  '' => NULL,
  'S_Event_Admin_event' => 
  array (
    'key' => 
    array (
      'eid' => 'eid',
      'name' => 'name',
      'stime' => 'stime',
      'etime' => 'etime',
      'cid' => 'cid',
      'uid' => 'uid',
      'audit' => 'audit',
    ),
    'key_name' => 
    array (
      'eid' => '活动ID',
      'name' => '活动名称',
      'stime' => '开始时间',
      'etime' => '结束时间',
      'cid' => '分类',
      'uid' => '用户',
      'audit' => '是否需要审核',
    ),
    'key_type' => 
    array (
      'eid' => 'text',
      'name' => 'text',
      'stime' => 'date',
      'etime' => 'date',
      'cid' => 'select',
      'uid' => 'user',
      'audit' => 'radio',
    ),
    'key_tishi' => 
    array (
      'eid' => '',
      'name' => '',
      'stime' => '',
      'etime' => '',
      'cid' => '',
      'uid' => '',
      'audit' => '',
    ),
    'key_javascript' => 
    array (
      'eid' => '',
      'name' => '',
      'stime' => '',
      'etime' => '',
      'cid' => '',
      'uid' => '',
      'audit' => '',
    ),
  ),
  'S_admin_Config_sensitive' => 
  array (
    'key' => 
    array (
      'word' => 'word',
      'sensitive_category' => 'sensitive_category',
    ),
    'key_name' => 
    array (
      'word' => '敏感词',
      'sensitive_category' => '分类',
    ),
    'key_type' => 
    array (
      'word' => 'text',
      'sensitive_category' => 'select',
    ),
    'key_tishi' => 
    array (
      'word' => '',
      'sensitive_category' => '',
    ),
    'key_javascript' => 
    array (
      'word' => '',
      'sensitive_category' => '',
    ),
  ),
  'S_Event_Admin_audit_event' => 
  array (
    'key' => 
    array (
      'eid' => 'eid',
      'name' => 'name',
      'stime' => 'stime',
      'etime' => 'etime',
      'uid' => 'uid',
    ),
    'key_name' => 
    array (
      'eid' => '活动ID',
      'name' => '标题',
      'stime' => '开始时间',
      'etime' => '结束时间',
      'uid' => '发布人',
    ),
    'key_type' => 
    array (
      'eid' => 'text',
      'name' => 'text',
      'stime' => 'date',
      'etime' => 'date',
      'uid' => 'user',
    ),
    'key_tishi' => 
    array (
      'eid' => '',
      'name' => '',
      'stime' => '',
      'etime' => '',
      'uid' => '',
    ),
    'key_javascript' => 
    array (
      'eid' => '',
      'name' => '',
      'stime' => '',
      'etime' => '',
      'uid' => '',
    ),
  ),
  'S_Event_Admin_del_event' => 
  array (
    'key' => 
    array (
      'eid' => 'eid',
      'name' => 'name',
      'stime' => 'stime',
      'etime' => 'etime',
      'uid' => 'uid',
    ),
    'key_name' => 
    array (
      'eid' => '活动ID',
      'name' => '标题',
      'stime' => '开始时间',
      'etime' => '结束时间',
      'uid' => '发布人',
    ),
    'key_type' => 
    array (
      'eid' => 'text',
      'name' => 'text',
      'stime' => 'date',
      'etime' => 'date',
      'uid' => 'user',
    ),
    'key_tishi' => 
    array (
      'eid' => '',
      'name' => '',
      'stime' => '',
      'etime' => '',
      'uid' => '',
    ),
    'key_javascript' => 
    array (
      'eid' => '',
      'name' => '',
      'stime' => '',
      'etime' => '',
      'uid' => '',
    ),
  ),
  'S_admin_RegisterCode_index' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'code' => 'code',
      'uid' => 'uid',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'code' => '邀请码',
      'uid' => '用户ID',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'code' => 'text',
      'uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'code' => '',
      'uid' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'code' => '',
      'uid' => '',
    ),
  ),
  'S_admin_LuckDraw_index' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'title' => 'title',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'title' => '标题',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'title' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'title' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'title' => '',
    ),
  ),
  'S_admin_LuckDraw_takeList' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'uid' => 'uid',
      'money' => 'money',
      'type' => 'type',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'uid' => '提现人ID',
      'money' => '提现金额',
      'type' => '提现状态',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'uid' => 'text',
      'money' => 'text',
      'type' => 'select',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'uid' => '',
      'money' => '',
      'type' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'uid' => '',
      'money' => '',
      'type' => '',
    ),
  ),
  'S_admin_Application_anonymous_category' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'nickname' => 'nickname',
    ),
    'key_name' => 
    array (
      'id' => '分类ID',
      'nickname' => '分类名称',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'nickname' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'nickname' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'nickname' => '',
    ),
  ),
  'S_admin_Application_anonymous_manage' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'pid' => 'pid',
      'nickname' => 'nickname',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'pid' => '匿名分类',
      'nickname' => '匿名名称',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'pid' => 'select',
      'nickname' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'pid' => '',
      'nickname' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'pid' => '',
      'nickname' => '',
    ),
  ),
  'S_admin_Application_anonymous_bd_detail' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'uid' => 'uid',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'uid' => '用户ID',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'uid' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'uid' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'uid' => '',
    ),
  ),
  'S_admin_Content_aodou_video' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'title' => 'title',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'title' => '视频名称',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'title' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'title' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'title' => '',
    ),
  ),
  'S_admin_Content_aodou_video_data' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'title' => 'title',
    ),
    'key_name' => 
    array (
      'id' => '剧集id',
      'title' => '视频标题',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'title' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'title' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'title' => '',
    ),
  ),
  'S_admin_Application_user_anonymous_manage' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'uid' => 'uid',
      'uname' => 'uname',
      'nickname' => 'nickname',
      'pid' => 'pid',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'uid' => '用户ID',
      'uname' => '用户名称',
      'nickname' => '匿名名称',
      'pid' => '匿名分类',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'uid' => 'text',
      'uname' => 'text',
      'nickname' => 'text',
      'pid' => 'select',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'uid' => '',
      'uname' => '',
      'nickname' => '',
      'pid' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'uid' => '',
      'uname' => '',
      'nickname' => '',
      'pid' => '',
    ),
  ),
  'S_admin_KeywordStatistic_index' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'keyword' => 'keyword',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'keyword' => '关键词',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'keyword' => 'text',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'keyword' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'keyword' => '',
    ),
  ),
  'S_Information_Admin_subjectList' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'cid' => 'cid',
      'tag' => 'tag',
      'abstract' => 'abstract',
      'created_name' => 'created_name',
      'zreditor' => 'zreditor',
      'copyfrom' => 'copyfrom',
      'author' => 'author',
      'ip' => 'ip',
      'subject' => 'subject',
      'stime' => 'stime',
      'etime' => 'etime',
      'ctime' => 'ctime',
    ),
    'key_name' => 
    array (
      'id' => '主题ID',
      'cid' => '主题分类',
      'tag' => '标签',
      'abstract' => '',
      'created_name' => '作者',
      'zreditor' => '责任编辑',
      'copyfrom' => '',
      'author' => '创建者UID',
      'ip' => '',
      'subject' => '标题',
      'stime' => '',
      'etime' => '',
      'ctime' => '发布时间',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'cid' => 'select',
      'tag' => 'text',
      'abstract' => 'text',
      'created_name' => 'text',
      'zreditor' => 'text',
      'copyfrom' => 'text',
      'author' => 'text',
      'ip' => 'text',
      'subject' => 'text',
      'stime' => 'text',
      'etime' => 'text',
      'ctime' => 'date',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'cid' => '',
      'tag' => '',
      'abstract' => '',
      'created_name' => '',
      'zreditor' => '',
      'copyfrom' => '',
      'author' => '',
      'ip' => '',
      'subject' => '',
      'stime' => '',
      'etime' => '',
      'ctime' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'cid' => '',
      'tag' => '',
      'abstract' => '',
      'created_name' => '',
      'zreditor' => '',
      'copyfrom' => '',
      'author' => '',
      'ip' => '',
      'subject' => '',
      'stime' => '',
      'etime' => '',
      'ctime' => '',
    ),
  ),
  'S_admin_User_state' => 
  array (
    'key' => 
    array (
      'state' => 'state',
    ),
    'key_name' => 
    array (
      'state' => '备注',
    ),
    'key_type' => 
    array (
      'state' => 'text',
    ),
    'key_tishi' => 
    array (
      'state' => '',
    ),
    'key_javascript' => 
    array (
      'state' => '',
    ),
  ),
  'S_admin_RegisterCode_getCodeBindList' => 
  array (
    'key' => 
    array (
      'id' => 'id',
      'code' => 'code',
      'uid' => 'uid',
      'bind_time' => 'bind_time',
    ),
    'key_name' => 
    array (
      'id' => '编号',
      'code' => '邀请码',
      'uid' => '绑定人ID',
      'bind_time' => '绑定时间',
    ),
    'key_type' => 
    array (
      'id' => 'text',
      'code' => 'text',
      'uid' => 'text',
      'bind_time' => 'date',
    ),
    'key_tishi' => 
    array (
      'id' => '',
      'code' => '',
      'uid' => '',
      'bind_time' => '',
    ),
    'key_javascript' => 
    array (
      'id' => '',
      'code' => '',
      'uid' => '',
      'bind_time' => '',
    ),
  ),
  'S_admin_User_index' => 
  array (
    'key' => 
    array (
      'uid' => 'uid',
      'uname' => 'uname',
      'email' => 'email',
      'mobile' => 'mobile',
      'state' => 'state',
      'sex' => 'sex',
      'user_group' => 'user_group',
      'user_category' => 'user_category',
      'ctime' => 'ctime',
      'ltime_h' => 'ltime_h',
      'ltime_m' => 'ltime_m',
      'ltime_s' => 'ltime_s',
      'stime_h' => 'stime_h',
      'stime_m' => 'stime_m',
      'stime_s' => 'stime_s',
      'province' => 'province',
      'city' => 'city',
      'vsource' => 'vsource',
      'code' => 'code',
    ),
    'key_name' => 
    array (
      'uid' => 'UID',
      'uname' => '用户名',
      'email' => 'Email',
      'mobile' => '',
      'state' => '备注',
      'sex' => '性别',
      'user_group' => '用户组',
      'user_category' => '标签',
      'ctime' => '注册时间',
      'ltime_h' => '开始时间_时',
      'ltime_m' => '开始时间_分',
      'ltime_s' => '开始时间_秒',
      'stime_h' => '结束时间_时',
      'stime_m' => '结束时间_时',
      'stime_s' => '结束时间_时',
      'province' => '选择省',
      'city' => '选择市',
      'vsource' => '采集来源1',
      'code' => '邀请码',
    ),
    'key_type' => 
    array (
      'uid' => 'text',
      'uname' => 'text',
      'email' => 'text',
      'mobile' => 'text',
      'state' => 'text',
      'sex' => 'radio',
      'user_group' => 'select',
      'user_category' => 'select',
      'ctime' => 'date',
      'ltime_h' => 'select',
      'ltime_m' => 'select',
      'ltime_s' => 'select',
      'stime_h' => 'select',
      'stime_m' => 'select',
      'stime_s' => 'select',
      'province' => 'select',
      'city' => 'select',
      'vsource' => 'select',
      'code' => 'radio',
    ),
    'key_tishi' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'mobile' => '',
      'state' => '',
      'sex' => '0',
      'user_group' => '',
      'user_category' => '',
      'ctime' => '',
      'ltime_h' => '',
      'ltime_m' => '',
      'ltime_s' => '',
      'stime_h' => '',
      'stime_m' => '',
      'stime_s' => '',
      'province' => '',
      'city' => '',
      'vsource' => '',
      'code' => '',
    ),
    'key_javascript' => 
    array (
      'uid' => '',
      'uname' => '',
      'email' => '',
      'mobile' => '',
      'state' => '',
      'sex' => '',
      'user_group' => '',
      'user_category' => '',
      'ctime' => '',
      'ltime_h' => '',
      'ltime_m' => '',
      'ltime_s' => '',
      'stime_h' => '',
      'stime_m' => '',
      'stime_s' => '',
      'province' => '',
      'city' => '',
      'vsource' => '',
      'code' => '',
    ),
  ),
  'S_admin_Content_feed' => 
  array (
    'key' => 
    array (
      'feed_id' => 'feed_id',
      'feed_content' => 'feed_content',
      'channel' => 'channel',
      'ctime' => 'ctime',
      'uid' => 'uid',
      'type' => 'type',
      'rec' => 'rec',
    ),
    'key_name' => 
    array (
      'feed_id' => '动态ID',
      'feed_content' => '动态内容',
      'channel' => '频道',
      'ctime' => '发布时间',
      'uid' => '用户ID',
      'type' => '动态类型',
      'rec' => '回收站',
    ),
    'key_type' => 
    array (
      'feed_id' => 'text',
      'feed_content' => 'text',
      'channel' => 'select',
      'ctime' => 'date',
      'uid' => 'text',
      'type' => 'select',
      'rec' => 'hidden',
    ),
    'key_tishi' => 
    array (
      'feed_id' => '多个id之间用英文的","隔开',
      'feed_content' => '',
      'channel' => '',
      'ctime' => '',
      'uid' => '多个id之间用英文的","隔开',
      'type' => '',
      'rec' => '',
    ),
    'key_javascript' => 
    array (
      'feed_id' => '',
      'feed_content' => '',
      'channel' => '',
      'ctime' => '',
      'uid' => '',
      'type' => '',
      'rec' => '',
    ),
  ),
);
?>