<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家基本设置
 */

return array(
    'pc端设置'=>array(
        'site.logo'=>array('type'=>SET_T_IMAGE,'default'=>'http://images.bbc.shopex123.com/images/33/e2/ff/56e438276be7f2d7ae2b7bede423048f6847e906.png','desc'=>'商城Logo','backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95)),
        'site.name'=>array('type'=>SET_T_STR,'vtype'=>'maxLength','default'=>'点此设置您商店的名称','desc'=>'商城名称','javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
        'site.loginlogo'=>array('type'=>SET_T_IMAGE,'default'=>'','desc'=>'登录注册页左侧大图','backend'=>'public','extends_attr'=>array('width'=>200,'height'=>95),'helpinfo'=>'<span class=\'notice-inline\'>图片标准宽度为1190*425</span>'),
    ),
    '交易设置' => array(
        'trade.cancel.spacing.time' => array( 'type'=>SET_T_INT,'default'=>72,'desc'=>'交易关闭间隔时间','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>单位：小时(h)</span>'),
        'trade.finish.spacing.time' => array( 'type'=>SET_T_INT,'default'=>7, 'desc'=>'交易完成间隔时间','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>单位：天(d)</span>'),
    ),
    '积分设置' => array(
        'point.ratio' => array('type'=>SET_T_STR,'default'=>1,'desc'=>'积分换算比率:','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>默认1元 = 1积分</span>'),
        'point.expired.month' => array('type'=>SET_T_STR,'default'=>12,'desc'=>'积分过期月份:','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>默认12【12代表每年的12月最后一天】 </span>'),
        'open.point.deduction' => array('type'=>SET_T_BOOL,'default'=>0,'desc'=>'开启积分抵扣:','vtype'=>'required','helpinfo'=>'<span class=\'notice-inline\'>【积分抵扣开启后，会员下单结算时将可使用积分抵扣订单金额】</span>','class'=>'point_deduction','javascript'=>'$$(".point_deduction").addEvent("click",function(e){if(this.value==0){$$(".point-deduction-setting").getParent("tr").hide();}else{$$(".point-deduction-setting").getParent("tr").show();}});if($$(".point_deduction")[0].getValue() == 1){$$(".point-deduction-setting").getParent("tr").show();}else{$$(".point-deduction-setting").getParent("tr").hide();}'),
        'point.deduction.rate' => array('type'=>SET_T_STR,'default'=>100,'desc'=>'积分抵扣金额比率:','vtype'=>'unsignedint','helpinfo'=>'<span class=\'notice-inline\'> 默认100积分 = 1元 </span>','class'=>'point-deduction-setting'),
        //'point.deduction.max' => array('type'=>SET_T_ENUM,'options'=>array('10'=>'10%','20'=>'20%','30'=>'30%','40'=>'40%','50'=>'50%','60'=>'60%','70'=>'70%','80'=>'80%','90'=>'90%'),'default'=>90,'desc'=>'每单积分抵扣金额上限:','vtype'=>'','helpinfo'=>'<span class=\'notice-inline\'>默认为订单总金额*0.9 </span>','class'=>'point-deduction-setting'),
        'point.deduction.max' => array('type'=>SET_T_INT,'default'=>99,'desc'=>'每单积分抵扣金额上限:','vtype'=>'positive&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'> 1 <= x <=99;默认为订单总金额*0.99 </span>','class'=>'point-deduction-setting'),
    ),
    '基本设置' => array(
        'user.deposit.password.limit'=> array( 'type'=>SET_T_INT,'default'=>5,'desc'=>'预存款支付密码输错','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>次停用</span>'),
        'user.deposit.password.expire'=> array( 'type'=>SET_T_INT,'default'=>3,'desc'=>'预存款支付密码输错次数达到上限后，停用','vtype'=>'required&&unsignedint','helpinfo'=>'<span class=\'notice-inline\'>小时</span>'),
    ),

    #'购物设置'=>array(
    #    'site.buy.target',
    #    'system.money.decimals',
    #    'system.money.operation.carryset',
    #    'site.trigger_tax', //是否开启发票
    #    'site.personal_tax_ratio',
    #    'site.company_tax_ratio',
    #    'site.tax_content',
    #    'site.checkout.zipcode.required.open',
    #    'site.checkout.receivermore.open',
    #    'site.combination.pay',//组合支付
    #    'cart.show_order_sales.type',
    #),
    #'购物显示设置'=>array(
    #    'site.login_type',
    #    'site.register_valide',
    #    'site.login_valide',
    #    'gallery.default_view',
    #    'site.show_mark_price',
    #    'site.market_price',
    #    'site.market_rate',
    #    'selllog.display.switch',
    #    'selllog.display.limit',
    #    'selllog.display.listnum',
    #    'site.save_price',
    #    'goods.show_order_sales.type',
    #    'site.member_price_display',
    #    'site.show_storage',
    #    'goodsbn.display.switch',
    #    'goods.recommend',
    #    'goodsprop.display.position',
    #    'site.isfastbuy_display',
    #    'gallery.display.listnum',
    #    'gallery.display.pagenum',
    #    'gallery.deliver.time',
    #    'gallery.comment.time',
    #    'site.cat.select',
    #    'gallery.display.buynum',
    #    'gallery.display.price',
    #    'gallery.display.tag.goods',
    #    'gallery.display.tag.promotion',
    #    'gallery.display.promotion',
    #    'gallery.display.store_status',
    #    'gallery.store_status.num',
    #    'gallery.display.stock_goods',
    #    'site.imgzoom.show',
    #    'site.imgzoom.width',
    #    'site.imgzoom.height',
    #),
    #'其他设置'=>array(
    #    'system.product.alert.num',
    #    'system.goods.freez.time',
    #),
);

