<?php

$setting =  array(
    'site.logo'=>array('type'=>SET_T_IMAGE,'default'=>'http://images.bbc.shopex123.com/images/e4/64/42/37eff0aeba30e897184f510c248deebd79cff488.png','desc'=>'商城Logo','backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95)),
    'site.name'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'ONex-B2B2C商城','desc'=>'商城名称','javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
    'sysconf_setting.wap_logo'=>array('type'=>SET_T_IMAGE,'vtype'=>'maxLength','default'=>'http://images.bbc.shopex123.com/images/6f/ca/48/3449cbc3e2b21c505aac507e96500749f4431fc7.png','desc'=>'wap端商城logo','backend'=>'public'),
    'sysconf_setting.wapmac_logo'=>array('type'=>SET_T_IMAGE,'vtype'=>'maxLength','default'=>'http://images.bbc.shopex123.com/images/24/a3/6e/8a701392f5447a26c7a489e39e072b512890c54a.jpg','desc'=>'苹果桌面图标','backend'=>'public'),
    'sysconf_setting.wap_name'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'ONex-B2B2C 移动商城','desc'=>'商城名称','javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
    'sysconf_setting.wap_description'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'<p>© 2013 All rights reserved.</p>','desc'=>'wap端商城底部信息','javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
    'site.loginlogo'=>array('type'=>SET_T_IMAGE,'default'=>'http://images.bbc.shopex123.com/images/75/17/7d/4a3de59f4e8586bf4ae21d6e491950f7d8902636.jpg','desc'=>'登录注册页左侧大图','backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95),'helpinfo'=>'<span class=\'notice-inline\'>图片标准宽度为600*600</span>'),
    'trade.cancel.spacing.time' => array( 'type'=>SET_T_INT,'default'=>72,'desc'=>'交易关闭间隔时间','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>单位：小时(h)</span>'),
    'trade.finish.spacing.time' => array( 'type'=>SET_T_INT,'default'=>7, 'desc'=>'交易完成间隔时间','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>单位：天(d)</span>'),
    'point.ratio' => array('type'=>SET_T_STR,'default'=>1,'desc'=>'积分换算比率:','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>默认1元 = 1积分</span>'),
    'point.expired.month' => array('type'=>SET_T_STR,'default'=>12,'desc'=>'积分过期月份:','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>默认12【12代表每年的12月最后一天】 </span>'),
    'open.point.deduction' => array('type'=>SET_T_BOOL,'default'=>0,'desc'=>'开启积分抵扣:','vtype'=>'required','helpinfo'=>'<span class=\'notice-inline\'>【积分抵扣开启后，会员下单结算时将可使用积分抵扣订单金额】</span>','class'=>'point_deduction','javascript'=>'$$(".point_deduction").addEvent("click",function(e){if(this.value==0){$$(".point-deduction-setting").getParent("tr").hide();}else{$$(".point-deduction-setting").getParent("tr").show();}});if($$(".point_deduction")[0].getValue() == 1){$$(".point-deduction-setting").getParent("tr").show();}else{$$(".point-deduction-setting").getParent("tr").hide();}'),
    'point.deduction.rate' => array('type'=>SET_T_STR,'default'=>100,'desc'=>'积分抵扣金额比率:','vtype'=>'unsignedint','helpinfo'=>'<span class=\'notice-inline\'> 默认100积分 = 1元 </span>','class'=>'point-deduction-setting'),
    //'point.deduction.max' => array('type'=>SET_T_ENUM,'options'=>array('10'=>'10%','20'=>'20%','30'=>'30%','40'=>'40%','50'=>'50%','60'=>'60%','70'=>'70%','80'=>'80%','90'=>'90%'),'default'=>90,'desc'=>'每单积分抵扣金额上限:','vtype'=>'','helpinfo'=>'<span class=\'notice-inline\'>默认为订单总金额*0.9 </span>','class'=>'point-deduction-setting'),
    'point.deduction.max' => array('type'=>SET_T_INT,'default'=>99,'desc'=>'每单积分抵扣金额上限:','vtype'=>'positive&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'> 1 <= x <=99;默认为订单总金额*0.99 </span>','class'=>'point-deduction-setting'),

    'user.deposit.password.expire'=> array( 'type'=>SET_T_INT,'default'=>3,'desc'=>'预存款支付密码停用时间','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>单位：小时(h)</span>'),
    'user.deposit.password.limit'=> array( 'type'=>SET_T_INT,'default'=>5,'desc'=>'预存款支付密码停用错误次数','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>单位：次</span>'),
);

