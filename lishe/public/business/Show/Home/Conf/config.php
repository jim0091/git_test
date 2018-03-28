<?php
return array(
	'URL_CASE_INSENSITIVE'=>false,
	//'MODULE_DENY_LIST' => array(),
	'MODULE_ALLOW_LIST' => array('Home'),
	'DEFAULT_MODULE' => 'Home',	
	'SHOW_PAGE_TRACE'=>false,  // 显示页面Trace信息
	'DB_FIELDTYPE_CHECK'=>true,  // 开启字段类型验证
	'UPLOAD_IMAGE_MAX_SIZE'=>5242880,//2Mb,允许上传图片的最大尺寸(单位byte)		
	'LOG_RECORD' => false,//开启日志记录
	'SESSION_OPTIONS'=>array('expire'=>3600*24),
	'COOKIE_DOMAIN' =>'lishe.co',
	'COOKIE_EXPIRE'=> 3600*24,
	'VAR_PAGE'=>'p', //分页变量
	'SESSION_AUTO_START' =>true,
	'CACHE_DEFAULT_TIME'=> 12*3600, //默认缓存时间
	'URL_CASE_INSENSITIVE'=>FALSE, //url不区分大小写
	'ROOT'=>'/business/',//定义系统的目录
	
	'API_AOSERVER'=>'http://120.76.159.44:8080/lshe.framework.aoserver/api/',//API接口地址
	'API_AOSERVER_USER' => 'user',
    'API_AOSERVER_PASSWORD' => 'lishe1234',
    'API_AOSERVER_APPKEY' => '1234567890asdfghjklqwertyuoou',
    'API_AOSERVER_KEY' => 'lishe_md5_key_56e057f20f883e',
    
    'API'=>'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',//API接口地址
	'API_KEY'=>'lishe_md5_key_56e057f20f883e',//API Key
	'JD_PRICE_DISCOUNT'=>98,//京东产品折扣，整数
	'JD_PROFIT_RATE'=>15,//京东产品利润率，整数
	'JD_API'=>'http://120.76.159.44:8080/lshe.framework.aoserver/api/jd/product/list',//获取京东产品详细，分页每次显示50条
	'JD_SHOP_ID'=>'10',//京东店铺ID
	'JD_IMG_PATH'=>'http://test.lishe.cn/business/',//保存在数据库字段里的图片地址
	'JD_UPLOAD'=>'./Upload/',//抓取京东图片保存的目录文件
	'INDEX_ACTIVITY'=>array(
		'end'=>'2016-08-30',
		'item'=>array(
			'8440' => array(
				'img'=>'http://120.76.102.64/business/Upload/jdImages/2246628/167734.jpg_l.jpg',
				'title'=>'大朴(DAPU) 240根纯棉四件套',
				'price'=>41160
			),
			'5345' => array(
				'img'=>'http://120.76.102.64/business/Upload/jdImages/1950561/142072.jpg_l.jpg',
				'title'=>'惠氏冬季婴幼儿洗浴护肤精华系列',
				'price'=>11662
			),

		)
		
	),


	'ACTIVITY'=>array(
		0=>array(
		    'type' => 'eat',
			'banner'=>'/business/Show/Home/View/Activity/fifth/images/0.png',
			'summery'=>'柴米油盐酱醋茶，再忙的生活节奏里，厨柜的一袋米，一瓶油，一盒茶叶，一瓶酱和醋，足以让幸福感爆棚，礼舍给你更多幸福的选择！',
			'item'=>array(
				'8821'=> array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/001.jpg',
					'title'=>'青怡庄园-龙眼干380g',
					'price'=>3600
				),
				'1053'=> array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/002.jpg',
					'title'=>'妙韵堂福鼎白茶白牡丹',
					'price'=>48804
				),
				'7094'=> array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/003.jpg',
					'title'=>'名物五谷杂粮礼包',
					'price'=>5500
				),
				'8499' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/004.jpg',
					'title'=>'帖木儿梅乐干红750ml',
					'price'=>39800
				),
				'8751' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/005.jpg',
					'title'=>'耘珂手工玫瑰姜母茶110g',
					'price'=>4900
				),
				'8753' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/006.jpg',
					'title'=>'农艺家百香果甜酒酿280g',
					'price'=>3900
				),
				'6788' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/007.jpg',
					'title'=>'墨兰50g盒装 浙江明前龙井茶',
					'price'=>12000
				),
                '8742' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/008.jpg',
					'title'=>'呼伦贝尔高品级压榨芥花油1.8L',
					'price'=>5600
				),
				'8741' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/009.jpg',
					'title'=>'益绿香油粘米5kg',
					'price'=>5700
				),
				'7112' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/010.jpg',
					'title'=>'爱维力橙花蜂蜜1kg',
					'price'=>22800
				),
			    '3278' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/011.jpg',
					'title'=>'良品铺子糕点肉松饼整箱',
					'price'=>5184
				),
				'1240' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/012.jpg',
					'title'=>'安溪清香型铁观音360g',
					'price'=>16464
				),
				'1086' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/013.jpg',
					'title'=>'东北黒蜂 椴树冰天雪蜜礼盒',
					'price'=>25382
	            ),
				'2753' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/014.jpg',
					'title'=>'坚果炒货巴旦木美国扁桃仁',
					'price'=>8722
				),
				'2425' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/015.jpg',
					'title'=>'紫薯紫米八宝粥280g×12罐',
					'price'=>4508
				),	
			)
		),
		1=>array(
		    'type' => 'bed',
			'banner'=>'/business/Show/Home/View/Activity/fifth/images/1.png',
			'summery'=>'吹惯了办公楼的空调，睡惯了办公室的椅子，是否总是期待卧室中一条暖暖的毛毯，一床轻柔的丝被，窝着，躺着，听爱人讲工作的趣事，这是我们想要的幸福，更是礼舍的坚持！',
			'item'=>array(
				'7160' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/101.jpg',
					'title'=>'南极人 Nanjiren冰丝席三件套 幸运草',
					'price'=>5780
				),
				'9192' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/102.jpg',
					'title'=>'摩亚冰丝席三件套 牡丹盛世',
					'price'=>9700
				),
				'8820' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/103.jpg',
					'title'=>'富安娜家纺 桑蚕丝清凉被',
					'price'=>45000
				),
				'8841' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/104.jpg',
					'title'=>'富安娜家纺 天丝夏被空调被',
					'price'=>29900
				),
				'8829' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/105.jpg',
					'title'=>'富安娜家纺 法兰绒毛毯',
					'price'=>12000
				),
				'3093' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/106.jpg',
					'title'=>'100%柞蚕丝春秋单人被',
					'price'=>57722
				),
				'3087' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/107.jpg',
					'title'=>'100%羊毛春秋双人被子',
					'price'=>44982
				),
                '2781' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/108.jpg',
					'title'=>'九孔春秋被子玉色',
					'price'=>9702
				),
				'8842' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/109.jpg',
					'title'=>'富安娜家纺 慢回弹枕芯',
					'price'=>15900
				),
				'8796' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/110.jpg',
					'title'=>'富安娜家纺 决明子木枕芯一对',
					'price'=>13800
				),
			    '2858' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/111.jpg',
					'title'=>'环保印花亲肤春秋被子',
					'price'=>14602
				),
				'1330'=> array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/112.jpg',
					'title'=>'欧罗巴高级全棉缎档浴巾',
					'price'=>4890
				),
				'945'=> array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/113.jpg',
					'title'=>'洁丽雅纯棉舒适浴巾',
					'price'=>8800
	            ),
				'8459' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/114.jpg',
					'title'=>'珊瑚绒毯MRT-002',
					'price'=>38800
				),
				'2440' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/115.jpg',
					'title'=>'抗菌暖绒单人冬被子',
					'price'=>9702
                ),
			)
		),
		2=>array(
			'type' => 'appliance',
 			'banner'=>'/business/Show/Home/View/Activity/fifth/images/2.png',
			'summery'=>'总想约上三五好友共享美餐，却不愿太多手忙脚乱的厨房，礼舍期望给你井然有序的生活，精致的小家电，煲一锅好汤 ，煮一壶好茶，享受与好友的午后。',
			'item'=>array(
				'8465' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/201.jpg',
					'title'=>'edei宜阁自动酸奶机',
					'price'=>16900
				),
				'877' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/202.jpg',
					'title'=>'康佳乐叮堡蒸蛋器',
					'price'=>10800
				),
				'900' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/203.jpg',
					'title'=>'康佳鲜果乐料理机',
					'price'=>25800
				),
				'8476' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/204.jpg',
					'title'=>'edei宜阁婴儿辅食电炖锅',
					'price'=>36800
				),
				'817' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/205.jpg',
					'title'=>'康佳黑客食王电饼铛',
					'price'=>29900
				),
				'844' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/206.jpg',
					'title'=>'康佳爱满家豆浆机',
					'price'=>52800
				),
				'922' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/207.jpg',
					'title'=>'康佳水之梦美颜养生壶',
					'price'=>39600
				),
				'959' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/208.jpg',
					'title'=>'康佳花样年华电磁炉',
					'price'=>33900
				),
				'809' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/209.jpg',
					'title'=>'康佳双层功夫烤王烧烤炉',
					'price'=>38800
				),
				'1776' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/210.jpg',
					'title'=>'玻璃电热水壶1.7L',
					'price'=>13622
				),
				'820' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/211.jpg',
					'title'=>'康佳宝宝炖盅DG-6208',
					'price'=>10600
				),
				'3241' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/212.jpg',
					'title'=>'美的微波炉烤箱',
					'price'=>58702
				),
				'915' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/213.jpg',
					'title'=>'康佳精彩烤箱KX-5178',
					'price'=>57500
				),
				'2513' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/214.jpg',
					'title'=>'飞利浦多功能豆浆机',
					'price'=>36162
				),
				'808' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/215.jpg',
					'title'=>'康佳果汁源榨汁机DZ803',
					'price'=>73600
	            ),
			)
		),
		3=>array(
			'type' => 'appliance',
			'banner'=>'/business/Show/Home/View/Activity/fifth/images/3.png',
			'summery'=>'工作后放下手中的文件夹，别当键盘侠，挑几件称心的厨具，享受煎炒蒸炖 和锅碗瓢盆和鸣的幸福慢时光，是否准备好和礼舍共享美味？',
			'item'=>array(
				'1027' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/301.jpg',
					'title'=>'山田烧陶瓷餐具套装',
					'price'=>12642
				),
				'1046' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/302.jpg',
					'title'=>'十八子作不锈钢七件套刀',
					'price'=>35084
				),
				'843' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/303.jpg',
					'title'=>'康佳蒸香味电蒸笼',
					'price'=>22800
				),
				'841' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/304.jpg',
					'title'=>'康佳锦绣之家电压力锅',
					'price'=>44800
				),
				'984' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/305.jpg',
					'title'=>'康宁0.8L透明玻璃汤锅',
					'price'=>42924
				),
				'8705' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/306.jpg',
					'title'=>'萨顿 朗动炫彩厨具五件套',
					'price'=>15800
				),
				'890' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/307.jpg',
					'title'=>'苏康铁韵茶具铁壶岩肌螃蟹',
					'price'=>40040
				),
				'8691' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/308.jpg',
					'title'=>'赫曼德 厨具五件套',
					'price'=>8800
				),
				'1005' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/309.jpg',
					'title'=>'28头骨瓷餐具碗碟套装',
					'price'=>28322
				),
				'1083' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/310.jpg',
					'title'=>'佳佰30CM三层钢炒锅',
					'price'=>29302
				),
				'7126' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/311.jpg',
					'title'=>'TI LIVING纯钛BB锅奶锅',
					'price'=>189000
				),
				'2561' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/312.jpg',
					'title'=>'佳佰塑料保鲜盒3件套',
					'price'=>4802
                ),
				'2119' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/313.jpg',
					'title'=>'苏泊尔无油烟炒锅',
					'price'=>48902
				),
				'7131' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/314.jpg',
					'title'=>'TI LIVING大号纯钛汤锅',
					'price'=>252000
			    ),
				'992' => array(
					'img'=>'/business/Show/Home/View/Activity/fifth/images/315.jpg',
					'title'=>'30头陶瓷餐具套装碗碟盘',
					'price'=>29302	
				),	
			)
		),
	)	
);