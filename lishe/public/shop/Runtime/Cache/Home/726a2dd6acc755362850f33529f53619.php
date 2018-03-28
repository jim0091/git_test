<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>礼品商城详情页</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<link rel="stylesheet" href="/public/css/details.css">
	<link rel="stylesheet" href="/public/css/address_css.css">
	<script src="/public/js/jQuery.v1.8.3.min.js"></script>
</head>
<body>
	<!-- 顶部导航 -->
	
	<div class="top_nav">
		<div class="wrap">
			<a href="" class="logo1"></a>
			<ul>
				<li class="nav_link index_page"><a href="">礼舍首页</a></li>
				<li class="nav_link current"><a href="">心意商城</a></li>
				<li class="nav_link"><a href="">礼舍帮帮</a></li>
			</ul>
			<ul class="login">
				<li><a href="">会员登录</a></li>
				<li><a href="">商家中心</a></li>
				<li><a href="">企业登录</a></li>
			</ul>
		</div>
	</div>
		
	<!-- 头部搜索 -->
		<div class="head">
		<div class="wrap">
			<a href="" class="logo2"></a>
			<div class="search_bar">
				<input type="text" class="search_text" placeholder="综合搜索">
				<a class="search_button" href=""></a>
				<ul class="search_commend">
					<li><a href="">礼舍自营</a></li>
					<li><a href="">关键词1</a></li>
					<li><a href="">关键词2</a></li>
					<li><a href="">关键词3</a></li>
					<li><a href="">关键词4</a></li>
					<li><a href="">关键词5</a></li>
					<li><a href="">关键词6</a></li>
					<li><a href="">关键词7</a></li>
				</ul>
			</div>
			<div class="my_cart">
				<a class="cart" href="">我的购物车</a>
				<div class="left_arrow"></div>
				<div id="cart_number">2</div>
			</div>
			
			
		<!-- 商品大分类 -->
			<div class="goods_nav">
				<!-- 导航侧边栏 -->
				<div class="leftbar">
					<div class="all_goods">全部商品分类</div>
					<ul id="channels">
						<li><a href="javascript:;">家用电器</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">大家电<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1403">平板电视</a></li>
										<li>
										  <a href="/list.html?cat_id=1404">空调</a></li>
										<li>
										  <a href="/list.html?cat_id=1405">冰箱</a></li>
										<li>
										  <a href="/list.html?cat_id=1406">洗衣机</a></li>
										<li>
										  <a href="/list.html?cat_id=1407">家庭影院</a></li>
										<li>
										  <a href="/list.html?cat_id=1408">DVD</a></li>
										<li>
										  <a href="/list.html?cat_id=1409">迷你音响</a></li>
										<li>
										  <a href="/list.html?cat_id=1410">冷柜/冰吧</a></li>
										<li>
										  <a href="/list.html?cat_id=1411">酒柜</a></li>
										<li>
										  <a href="/list.html?cat_id=1412">家电配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">厨卫大电<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1414">油烟机</a></li>
										<li>
										  <a href="/list.html?cat_id=1415">燃气灶</a></li>
										<li>
										  <a href="/list.html?cat_id=1416">烟灶套装</a></li>
										<li>
										  <a href="/list.html?cat_id=1417">消毒柜</a></li>
										<li>
										  <a href="/list.html?cat_id=1418">洗碗机</a></li>
										<li>
										  <a href="/list.html?cat_id=1419">电热水器</a></li>
										<li>
										  <a href="/list.html?cat_id=1420">燃气热水器</a></li>
										<li>
										  <a href="/list.html?cat_id=1422">嵌入式厨电</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">厨房小电<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1349">电饭煲</a></li>
										<li>
										  <a href="/list.html?cat_id=84">微波炉</a></li>
										<li>
										  <a href="/list.html?cat_id=85">电烤箱</a></li>
										<li>
										  <a href="/list.html?cat_id=86">电磁炉</a></li>
										<li>
										  <a href="/list.html?cat_id=87">电压力锅</a></li>
										<li>
										  <a href="/list.html?cat_id=88">豆浆机</a></li>
										<li>
										  <a href="/list.html?cat_id=90">咖啡机</a></li>
										<li>
										  <a href="/list.html?cat_id=91">面包机</a></li>
										<li>
										  <a href="/list.html?cat_id=92">榨汁机</a></li>
										<li>
										  <a href="/list.html?cat_id=93">料理机</a></li>
										<li>
										  <a href="/list.html?cat_id=94">电饼铛</a></li>
										<li>
										  <a href="/list.html?cat_id=95">养生壶/煎药壶</a></li>
										<li>
										  <a href="/list.html?cat_id=96">酸奶机</a></li>
										<li>
										  <a href="/list.html?cat_id=97">煮蛋器</a></li>
										<li>
										  <a href="/list.html?cat_id=98">电水壶/热水瓶</a></li>
										<li>
										  <a href="/list.html?cat_id=99">电炖锅</a></li>
										<li>
										  <a href="/list.html?cat_id=100">多用途锅</a></li>
										<li>
										  <a href="/list.html?cat_id=101">电烧烤炉</a></li>
										<li>
										  <a href="/list.html?cat_id=102">电热饭盒</a></li>
										<li>
										  <a href="/list.html?cat_id=103">其他厨房电器</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">生活电器<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=104">电风扇</a></li>
										<li>
										  <a href="/list.html?cat_id=105">冷风扇</a></li>
										<li>
										  <a href="/list.html?cat_id=108">吸尘器</a></li>
										<li>
										  <a href="/list.html?cat_id=106">净化器</a></li>
										<li>
										  <a href="/list.html?cat_id=107">扫地机器人</a></li>
										<li>
										  <a href="/list.html?cat_id=109">加湿器</a></li>
										<li>
										  <a href="/list.html?cat_id=110">挂烫机/熨斗</a></li>
										<li>
										  <a href="/list.html?cat_id=111">取暖电器</a></li>
										<li>
										  <a href="/list.html?cat_id=112">插座</a></li>
										<li>
										  <a href="/list.html?cat_id=113">电话机</a></li>
										<li>
										  <a href="/list.html?cat_id=114">净水器</a></li>
										<li>
										  <a href="/list.html?cat_id=115">饮水机</a></li>
										<li>
										  <a href="/list.html?cat_id=116">除湿器</a></li>
										<li>
										  <a href="/list.html?cat_id=117">干衣机</a></li>
										<li>
										  <a href="/list.html?cat_id=118">清洁机</a></li>
										<li>
										  <a href="/list.html?cat_id=119">收/录音机</a></li>
										<li>
										  <a href="/list.html?cat_id=120">其他生活电器</a></li>
										<li>
										  <a href="/list.html?cat_id=121">生活电器配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">个人健康<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1424">剃须刀</a></li>
										<li>
										  <a href="/list.html?cat_id=1425">口腔护理</a></li>
										<li>
										  <a href="/list.html?cat_id=1426">电吹风</a></li>
										<li>
										  <a href="/list.html?cat_id=1427">美容器</a></li>
										<li>
										  <a href="/list.html?cat_id=1428">卷/直发器</a></li>
										<li>
										  <a href="/list.html?cat_id=1429">理发器</a></li>
										<li>
										  <a href="/list.html?cat_id=1430">剃/脱毛器</a></li>
										<li>
										  <a href="/list.html?cat_id=1431">足浴盆</a></li>
										<li>
										  <a href="/list.html?cat_id=1432">健康秤/厨房秤</a></li>
										<li>
										  <a href="/list.html?cat_id=1433">按摩器</a></li>
										<li>
										  <a href="/list.html?cat_id=1434">按摩椅</a></li>
										<li>
										  <a href="/list.html?cat_id=1435">血压计</a></li>
										<li>
										  <a href="/list.html?cat_id=1436">血糖仪</a></li>
										<li>
										  <a href="/list.html?cat_id=1437">体温计</a></li>
										<li>
										  <a href="/list.html?cat_id=1438">计步器/脂肪检测仪</a></li>
										<li>
										  <a href="/list.html?cat_id=1439">其他健康电器</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">五金家装<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1441">电动工具</a></li>
										<li>
										  <a href="/list.html?cat_id=1442">手动工具</a></li>
										<li>
										  <a href="/list.html?cat_id=1443">仪器仪表</a></li>
										<li>
										  <a href="/list.html?cat_id=1444">浴霸/排气扇</a></li>
										<li>
										  <a href="/list.html?cat_id=1445">灯具</a></li>
										<li>
										  <a href="/list.html?cat_id=1446">LED灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1447">洁身器</a></li>
										<li>
										  <a href="/list.html?cat_id=1448">水槽</a></li>
										<li>
										  <a href="/list.html?cat_id=1449">龙头</a></li>
										<li>
										  <a href="/list.html?cat_id=1450">淋浴花洒</a></li>
										<li>
										  <a href="/list.html?cat_id=1451">厨卫五金</a></li>
										<li>
										  <a href="/list.html?cat_id=1452">家具五金</a></li>
										<li>
										  <a href="/list.html?cat_id=1453">门铃</a></li>
										<li>
										  <a href="/list.html?cat_id=1454">电气开关</a></li>
										<li>
										  <a href="/list.html?cat_id=1455">插座</a></li>
										<li>
										  <a href="/list.html?cat_id=1456">电工电料</a></li>
										<li>
										  <a href="/list.html?cat_id=1457">监控安防</a></li>
										<li>
										  <a href="/list.html?cat_id=1458">电线/线缆</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">手机、数码</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">手机通讯<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=165">手机</a></li>
										<li>
										  <a href="/list.html?cat_id=1376">对讲机</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">手机配件<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=178">手机电池</a></li>
										<li>
										  <a href="/list.html?cat_id=1459">移动电源</a></li>
										<li>
										  <a href="/list.html?cat_id=179">蓝牙耳机</a></li>
										<li>
										  <a href="/list.html?cat_id=180">充电器</a></li>
										<li>
										  <a href="/list.html?cat_id=1460">数据线</a></li>
										<li>
										  <a href="/list.html?cat_id=181">手机耳机</a></li>
										<li>
										  <a href="/list.html?cat_id=182">手机支架</a></li>
										<li>
										  <a href="/list.html?cat_id=183">贴膜</a></li>
										<li>
										  <a href="/list.html?cat_id=184">手机存储卡</a></li>
										<li>
										  <a href="/list.html?cat_id=185">保护套</a></li>
										<li>
										  <a href="/list.html?cat_id=186">车载配件</a></li>
										<li>
										  <a href="/list.html?cat_id=187">苹果周边</a></li>
										<li>
										  <a href="/list.html?cat_id=188">创意配件</a></li>
										<li>
										  <a href="/list.html?cat_id=190">手机饰品</a></li>
										<li>
										  <a href="/list.html?cat_id=191">拍照配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">影像摄影<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1462">数码相机</a></li>
										<li>
										  <a href="/list.html?cat_id=1463">单电/微单相机</a></li>
										<li>
										  <a href="/list.html?cat_id=1464">单反相机</a></li>
										<li>
										  <a href="/list.html?cat_id=1465">拍立得</a></li>
										<li>
										  <a href="/list.html?cat_id=1466">运动相机</a></li>
										<li>
										  <a href="/list.html?cat_id=1467">摄像机</a></li>
										<li>
										  <a href="/list.html?cat_id=1468">镜头</a></li>
										<li>
										  <a href="/list.html?cat_id=1469">户外器材</a></li>
										<li>
										  <a href="/list.html?cat_id=1470">影棚器材</a></li>
										<li>
										  <a href="/list.html?cat_id=1471">冲印服务</a></li>
										<li>
										  <a href="/list.html?cat_id=1472">数码相框</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">数码配件<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1474">存储卡</a></li>
										<li>
										  <a href="/list.html?cat_id=1475">读卡器</a></li>
										<li>
										  <a href="/list.html?cat_id=1476">支架</a></li>
										<li>
										  <a href="/list.html?cat_id=1477">滤镜</a></li>
										<li>
										  <a href="/list.html?cat_id=1478">闪光灯/手柄</a></li>
										<li>
										  <a href="/list.html?cat_id=1479">相机包</a></li>
										<li>
										  <a href="/list.html?cat_id=1480">三脚架/云台</a></li>
										<li>
										  <a href="/list.html?cat_id=1481">相机清洁/贴膜</a></li>
										<li>
										  <a href="/list.html?cat_id=1482">机身附件</a></li>
										<li>
										  <a href="/list.html?cat_id=1483">镜头附件</a></li>
										<li>
										  <a href="/list.html?cat_id=1484">电池/充电器</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">影音娱乐<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1486">耳机/耳麦</a></li>
										<li>
										  <a href="/list.html?cat_id=1487">音箱/音响</a></li>
										<li>
										  <a href="/list.html?cat_id=1488">便携/无线音箱</a></li>
										<li>
										  <a href="/list.html?cat_id=1489">收音机</a></li>
										<li>
										  <a href="/list.html?cat_id=1490">麦克风</a></li>
										<li>
										  <a href="/list.html?cat_id=1491">MP3/MP4</a></li>
										<li>
										  <a href="/list.html?cat_id=1492">专业音频</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">智能设备<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=240">智能手环</a></li>
										<li>
										  <a href="/list.html?cat_id=242">智能手表</a></li>
										<li>
										  <a href="/list.html?cat_id=244">智能眼镜</a></li>
										<li>
										  <a href="/list.html?cat_id=246">智能机器人</a></li>
										<li>
										  <a href="/list.html?cat_id=247">运动跟踪器</a></li>
										<li>
										  <a href="/list.html?cat_id=248">健康监测</a></li>
										<li>
										  <a href="/list.html?cat_id=251">智能配饰</a></li>
										<li>
										  <a href="/list.html?cat_id=254">智能家居</a></li>
										<li>
										  <a href="/list.html?cat_id=256">体感车</a></li>
										<li>
										  <a href="/list.html?cat_id=258">无人机</a></li>
										<li>
										  <a href="/list.html?cat_id=259">其他配件</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">电子教育<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1885">学生平板</a></li>
										<li>
										  <a href="/list.html?cat_id=1886">点读机/笔</a></li>
										<li>
										  <a href="/list.html?cat_id=1887">早教益智</a></li>
										<li>
										  <a href="/list.html?cat_id=1888">录音笔</a></li>
										<li>
										  <a href="/list.html?cat_id=1889">电纸书</a></li>
										<li>
										  <a href="/list.html?cat_id=1890">电子词典</a></li>
										<li>
										  <a href="/list.html?cat_id=1891">复读机</a></li>
										</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">电脑、办公</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">电脑整机<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1501">笔记本</a></li>
										<li>
										  <a href="/list.html?cat_id=1502">游戏本</a></li>
										<li>
										  <a href="/list.html?cat_id=1503">平板电脑</a></li>
										<li>
										  <a href="/list.html?cat_id=1504">平板电脑配件</a></li>
										<li>
										  <a href="/list.html?cat_id=1505">台式机</a></li>
										<li>
										  <a href="/list.html?cat_id=1506">一体机</a></li>
										<li>
										  <a href="/list.html?cat_id=1892">服务器/工作站</a></li>
										<li>
										  <a href="/list.html?cat_id=1507">笔记本配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">电脑配件<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1508">CPU</a></li>
										<li>
										  <a href="/list.html?cat_id=1509">主板</a></li>
										<li>
										  <a href="/list.html?cat_id=1510">显卡</a></li>
										<li>
										  <a href="/list.html?cat_id=1511">硬盘</a></li>
										<li>
										  <a href="/list.html?cat_id=1512">SSD固态硬盘</a></li>
										<li>
										  <a href="/list.html?cat_id=1893">内存</a></li>
										<li>
										  <a href="/list.html?cat_id=1513">机箱</a></li>
										<li>
										  <a href="/list.html?cat_id=1514">电源</a></li>
										<li>
										  <a href="/list.html?cat_id=1515">显示器</a></li>
										<li>
										  <a href="/list.html?cat_id=1516">刻录机/光驱</a></li>
										<li>
										  <a href="/list.html?cat_id=1517">声卡/扩展卡</a></li>
										<li>
										  <a href="/list.html?cat_id=1518">散热器</a></li>
										<li>
										  <a href="/list.html?cat_id=1519">装机配件</a></li>
										<li>
										  <a href="/list.html?cat_id=1520">组装电脑</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">外设产品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1521">鼠标</a></li>
										<li>
										  <a href="/list.html?cat_id=1522">键盘</a></li>
										<li>
										  <a href="/list.html?cat_id=1523">键鼠套装</a></li>
										<li>
										  <a href="/list.html?cat_id=1524">网络仪表仪器</a></li>
										<li>
										  <a href="/list.html?cat_id=1525">U盘</a></li>
										<li>
										  <a href="/list.html?cat_id=1526">移动硬盘</a></li>
										<li>
										  <a href="/list.html?cat_id=1527">鼠标垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1528">摄像头</a></li>
										<li>
										  <a href="/list.html?cat_id=1529">线缆</a></li>
										<li>
										  <a href="/list.html?cat_id=1530">手写板</a></li>
										<li>
										  <a href="/list.html?cat_id=1531">硬盘盒</a></li>
										<li>
										  <a href="/list.html?cat_id=1532">电脑工具</a></li>
										<li>
										  <a href="/list.html?cat_id=1533">电脑清洁</a></li>
										<li>
										  <a href="/list.html?cat_id=1534">UPS电源</a></li>
										<li>
										  <a href="/list.html?cat_id=1535">插座</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">游戏设备<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1536">游戏机</a></li>
										<li>
										  <a href="/list.html?cat_id=1537">游戏耳机</a></li>
										<li>
										  <a href="/list.html?cat_id=1538">手柄/方向盘</a></li>
										<li>
										  <a href="/list.html?cat_id=1539">游戏软件</a></li>
										<li>
										  <a href="/list.html?cat_id=1540">游戏周边</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">网络产品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1541">路由器</a></li>
										<li>
										  <a href="/list.html?cat_id=1542">网卡</a></li>
										<li>
										  <a href="/list.html?cat_id=1543">交换机</a></li>
										<li>
										  <a href="/list.html?cat_id=1544">网络存储</a></li>
										<li>
										  <a href="/list.html?cat_id=1545">4G/3G上网</a></li>
										<li>
										  <a href="/list.html?cat_id=1546">网络盒子</a></li>
										<li>
										  <a href="/list.html?cat_id=1547">网络配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">办公设备<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1548">投影机</a></li>
										<li>
										  <a href="/list.html?cat_id=1549">投影配件</a></li>
										<li>
										  <a href="/list.html?cat_id=1550">多功能一体机</a></li>
										<li>
										  <a href="/list.html?cat_id=1551">打印机</a></li>
										<li>
										  <a href="/list.html?cat_id=1552">传真设备</a></li>
										<li>
										  <a href="/list.html?cat_id=1553">验钞/点钞机</a></li>
										<li>
										  <a href="/list.html?cat_id=1554">扫描设备</a></li>
										<li>
										  <a href="/list.html?cat_id=1555">复合机</a></li>
										<li>
										  <a href="/list.html?cat_id=1556">碎纸机</a></li>
										<li>
										  <a href="/list.html?cat_id=1557">考勤机</a></li>
										<li>
										  <a href="/list.html?cat_id=1558">收款/POS机</a></li>
										<li>
										  <a href="/list.html?cat_id=1559">会议音频视频</a></li>
										<li>
										  <a href="/list.html?cat_id=1560">保险柜</a></li>
										<li>
										  <a href="/list.html?cat_id=1561">装订/封装机</a></li>
										<li>
										  <a href="/list.html?cat_id=1807">安防监控</a></li>
										<li>
										  <a href="/list.html?cat_id=1808">办公家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1809">白板</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">文具耗材<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1562">硒鼓/墨粉</a></li>
										<li>
										  <a href="/list.html?cat_id=1563">墨盒</a></li>
										<li>
										  <a href="/list.html?cat_id=1564">色带</a></li>
										<li>
										  <a href="/list.html?cat_id=1565">纸类</a></li>
										<li>
										  <a href="/list.html?cat_id=1566">办公文具</a></li>
										<li>
										  <a href="/list.html?cat_id=1567">学生文具</a></li>
										<li>
										  <a href="/list.html?cat_id=1568">文件管理</a></li>
										<li>
										  <a href="/list.html?cat_id=1569">本册/便签</a></li>
										<li>
										  <a href="/list.html?cat_id=1570">计算器</a></li>
										<li>
										  <a href="/list.html?cat_id=1571">笔类</a></li>
										<li>
										  <a href="/list.html?cat_id=1572">画具画材</a></li>
										<li>
										  <a href="/list.html?cat_id=1573">财会用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1574">刻录碟片/附件</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">家居、家具、家装、厨具</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">厨具<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=430">烹饪锅具</a></li>
										<li>
										  <a href="/list.html?cat_id=431">刀剪菜板</a></li>
										<li>
										  <a href="/list.html?cat_id=432">厨房配件</a></li>
										<li>
										  <a href="/list.html?cat_id=433">水具酒具</a></li>
										<li>
										  <a href="/list.html?cat_id=434">餐具</a></li>
										<li>
										  <a href="/list.html?cat_id=435">茶具/咖啡具</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">家装建材<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1578">灯饰照明</a></li>
										<li>
										  <a href="/list.html?cat_id=1579">厨房卫浴</a></li>
										<li>
										  <a href="/list.html?cat_id=1580">五金工具</a></li>
										<li>
										  <a href="/list.html?cat_id=1581">电工电料</a></li>
										<li>
										  <a href="/list.html?cat_id=1582">墙地面材料</a></li>
										<li>
										  <a href="/list.html?cat_id=1583">装饰材料</a></li>
										<li>
										  <a href="/list.html?cat_id=1584">吸顶灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1585">淋浴花洒</a></li>
										<li>
										  <a href="/list.html?cat_id=1586">开关插座</a></li>
										<li>
										  <a href="/list.html?cat_id=1587">油漆涂料</a></li>
										<li>
										  <a href="/list.html?cat_id=1588">龙头</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">家纺<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=697">床品套件</a></li>
										<li>
										  <a href="/list.html?cat_id=1010">被子</a></li>
										<li>
										  <a href="/list.html?cat_id=1011">枕芯</a></li>
										<li>
										  <a href="/list.html?cat_id=1327">蚊帐</a></li>
										<li>
										  <a href="/list.html?cat_id=1328">凉席</a></li>
										<li>
										  <a href="/list.html?cat_id=1012">毛巾浴巾</a></li>
										<li>
										  <a href="/list.html?cat_id=1325">床单被罩</a></li>
										<li>
										  <a href="/list.html?cat_id=1326">床垫/床褥</a></li>
										<li>
										  <a href="/list.html?cat_id=1009">毯子</a></li>
										<li>
										  <a href="/list.html?cat_id=1329">抱枕靠垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1330">窗帘/窗纱</a></li>
										<li>
										  <a href="/list.html?cat_id=1894">电热毯</a></li>
										<li>
										  <a href="/list.html?cat_id=1332">布艺软饰</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">家具<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1589">卧室家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1590">客厅家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1591">餐厅家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1592">书房家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1593">儿童家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1594">储物家具</a></li>
										<li>
										  <a href="/list.html?cat_id=1595">阳台/户外</a></li>
										<li>
										  <a href="/list.html?cat_id=1596">商业办公</a></li>
										<li>
										  <a href="/list.html?cat_id=1597">床</a></li>
										<li>
										  <a href="/list.html?cat_id=1598">床垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1599">沙发</a></li>
										<li>
										  <a href="/list.html?cat_id=1600">电脑椅</a></li>
										<li>
										  <a href="/list.html?cat_id=1601">衣柜</a></li>
										<li>
										  <a href="/list.html?cat_id=1602">茶几</a></li>
										<li>
										  <a href="/list.html?cat_id=1603">电视柜</a></li>
										<li>
										  <a href="/list.html?cat_id=1604">餐桌</a></li>
										<li>
										  <a href="/list.html?cat_id=1605">电脑桌</a></li>
										<li>
										  <a href="/list.html?cat_id=1606">鞋架/衣帽架</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">灯具<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1607">台灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1608">吸顶灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1609">筒灯射灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1610">LED灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1611">节能灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1612">落地灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1895">五金电器</a></li>
										<li>
										  <a href="/list.html?cat_id=1613">应急灯/手电</a></li>
										<li>
										  <a href="/list.html?cat_id=1614">装饰灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1615">吊灯</a></li>
										<li>
										  <a href="/list.html?cat_id=1616">氛围照明</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">生活日用<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1008">收纳用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1295">雨伞雨具</a></li>
										<li>
										  <a href="/list.html?cat_id=1296">净化除味</a></li>
										<li>
										  <a href="/list.html?cat_id=1297">浴室用品</a></li>
										<li>
										  <a href="/list.html?cat_id=728">洗晒/熨烫</a></li>
										<li>
										  <a href="/list.html?cat_id=1298">缝纫/针织用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1072">清洁工具</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">家装软饰<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1280">桌布/罩件</a></li>
										<li>
										  <a href="/list.html?cat_id=1281">地毯地垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1282">沙发垫套/椅垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1283">装饰字画</a></li>
										<li>
										  <a href="/list.html?cat_id=1284">装饰摆件</a></li>
										<li>
										  <a href="/list.html?cat_id=1285">手工/十字绣</a></li>
										<li>
										  <a href="/list.html?cat_id=1286">相框/照片墙</a></li>
										<li>
										  <a href="/list.html?cat_id=1287">墙贴/装饰贴</a></li>
										<li>
										  <a href="/list.html?cat_id=1288">花瓶/花艺</a></li>
										<li>
										  <a href="/list.html?cat_id=1289">香薰蜡烛</a></li>
										<li>
										  <a href="/list.html?cat_id=1290">节庆饰品</a></li>
										<li>
										  <a href="/list.html?cat_id=1291">钟饰</a></li>
										<li>
										  <a href="/list.html?cat_id=1617">布艺隔断</a></li>
										<li>
										  <a href="/list.html?cat_id=1293">创意家居</a></li>
										<li>
										  <a href="/list.html?cat_id=1294">保暖防护</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">内衣、配饰</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">内衣<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1211">文胸</a></li>
										<li>
										  <a href="/list.html?cat_id=1212">睡衣/家居服</a></li>
										<li>
										  <a href="/list.html?cat_id=1213">男士内裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1214">女士内裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1215">塑身美体</a></li>
										<li>
										  <a href="/list.html?cat_id=1216">文胸套装</a></li>
										<li>
										  <a href="/list.html?cat_id=1217">情侣睡衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1218">吊带/背心</a></li>
										<li>
										  <a href="/list.html?cat_id=1219">少女文胸</a></li>
										<li>
										  <a href="/list.html?cat_id=1220">休闲棉袜</a></li>
										<li>
										  <a href="/list.html?cat_id=1221">商务男袜</a></li>
										<li>
										  <a href="/list.html?cat_id=1222">连裤袜/丝袜</a></li>
										<li>
										  <a href="/list.html?cat_id=1223">美腿袜</a></li>
										<li>
										  <a href="/list.html?cat_id=1224">打底裤袜</a></li>
										<li>
										  <a href="/list.html?cat_id=1225">抹胸</a></li>
										<li>
										  <a href="/list.html?cat_id=1226">内衣配件</a></li>
										<li>
										  <a href="/list.html?cat_id=1227">大码内衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1228">打底衫</a></li>
										<li>
										  <a href="/list.html?cat_id=1344">泳衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1810">秋衣秋裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1811">保暖内衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1812">情趣内衣</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">配饰<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1233">太阳镜</a></li>
										<li>
										  <a href="/list.html?cat_id=1896">光学镜架/镜片</a></li>
										<li>
										  <a href="/list.html?cat_id=1235">男士腰带/礼盒</a></li>
										<li>
										  <a href="/list.html?cat_id=1236">防辐射眼镜</a></li>
										<li>
										  <a href="/list.html?cat_id=1237">老花镜</a></li>
										<li>
										  <a href="/list.html?cat_id=1238">女士丝巾/围巾/披肩</a></li>
										<li>
										  <a href="/list.html?cat_id=1239">男士丝巾/围巾</a></li>
										<li>
										  <a href="/list.html?cat_id=1240">棒球帽</a></li>
										<li>
										  <a href="/list.html?cat_id=1241">遮阳帽</a></li>
										<li>
										  <a href="/list.html?cat_id=1242">鸭舌帽</a></li>
										<li>
										  <a href="/list.html?cat_id=1243">贝雷帽</a></li>
										<li>
										  <a href="/list.html?cat_id=1244">礼帽</a></li>
										<li>
										  <a href="/list.html?cat_id=1897">毛绒帽</a></li>
										<li>
										  <a href="/list.html?cat_id=1246">防晒手套</a></li>
										<li>
										  <a href="/list.html?cat_id=1247">真皮手套</a></li>
										<li>
										  <a href="/list.html?cat_id=1248">围巾/手套/帽子套装</a></li>
										<li>
										  <a href="/list.html?cat_id=1249">遮阳伞</a></li>
										<li>
										  <a href="/list.html?cat_id=1250">女士腰带/礼盒</a></li>
										<li>
										  <a href="/list.html?cat_id=1251">口罩</a></li>
										<li>
										  <a href="/list.html?cat_id=1252">假领</a></li>
										<li>
										  <a href="/list.html?cat_id=1898">毛绒/布面料</a></li>
										<li>
										  <a href="/list.html?cat_id=1899">领带/领结/领带夹</a></li>
										<li>
										  <a href="/list.html?cat_id=1255">耳罩/耳包</a></li>
										<li>
										  <a href="/list.html?cat_id=1256">袖扣</a></li>
										<li>
										  <a href="/list.html?cat_id=1257">钥匙扣</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">个护化妆、清洁用品、宠物</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">面部护肤<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1618">卸妆</a></li>
										<li>
										  <a href="/list.html?cat_id=208">洁面</a></li>
										<li>
										  <a href="/list.html?cat_id=1164">爽肤水</a></li>
										<li>
										  <a href="/list.html?cat_id=1619">乳液面霜</a></li>
										<li>
										  <a href="/list.html?cat_id=1620">精华</a></li>
										<li>
										  <a href="/list.html?cat_id=1621">眼霜</a></li>
										<li>
										  <a href="/list.html?cat_id=1622">防晒</a></li>
										<li>
										  <a href="/list.html?cat_id=1165">面膜</a></li>
										<li>
										  <a href="/list.html?cat_id=1071">剃须</a></li>
										<li>
										  <a href="/list.html?cat_id=1166">套装</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">洗发护发<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1167">洗发</a></li>
										<li>
										  <a href="/list.html?cat_id=1168">护发</a></li>
										<li>
										  <a href="/list.html?cat_id=1169">染发</a></li>
										<li>
										  <a href="/list.html?cat_id=1170">造型</a></li>
										<li>
										  <a href="/list.html?cat_id=1171">假发</a></li>
										<li>
										  <a href="/list.html?cat_id=1172">套装</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">身体护肤<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1173">沐浴</a></li>
										<li>
										  <a href="/list.html?cat_id=1174">润肤</a></li>
										<li>
										  <a href="/list.html?cat_id=1623">精油</a></li>
										<li>
										  <a href="/list.html?cat_id=1175">颈部</a></li>
										<li>
										  <a href="/list.html?cat_id=1176">手足</a></li>
										<li>
										  <a href="/list.html?cat_id=1177">纤体塑形</a></li>
										<li>
										  <a href="/list.html?cat_id=1178">美胸</a></li>
										<li>
										  <a href="/list.html?cat_id=1179">套装</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">口腔护理<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1180">牙膏/牙粉</a></li>
										<li>
										  <a href="/list.html?cat_id=1181">牙刷/牙线</a></li>
										<li>
										  <a href="/list.html?cat_id=1182">漱口水</a></li>
										<li>
										  <a href="/list.html?cat_id=1183">套装</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">女性护理<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1184">卫生巾</a></li>
										<li>
										  <a href="/list.html?cat_id=1185">卫生护垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1186">私密护理</a></li>
										<li>
										  <a href="/list.html?cat_id=1188">脱毛膏</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">香水彩妆<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=730">女士香水</a></li>
										<li>
										  <a href="/list.html?cat_id=1624">男士香水</a></li>
										<li>
										  <a href="/list.html?cat_id=1189">底妆</a></li>
										<li>
										  <a href="/list.html?cat_id=1191">眉笔</a></li>
										<li>
										  <a href="/list.html?cat_id=1625">睫毛膏</a></li>
										<li>
										  <a href="/list.html?cat_id=1626">眼线</a></li>
										<li>
										  <a href="/list.html?cat_id=1627">眼影</a></li>
										<li>
										  <a href="/list.html?cat_id=1192">唇膏/彩</a></li>
										<li>
										  <a href="/list.html?cat_id=1190">腮红</a></li>
										<li>
										  <a href="/list.html?cat_id=1193">美甲</a></li>
										<li>
										  <a href="/list.html?cat_id=1194">美妆工具</a></li>
										<li>
										  <a href="/list.html?cat_id=1195">套装</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">清洁用品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1196">纸品湿巾</a></li>
										<li>
										  <a href="/list.html?cat_id=1070">衣物清洁</a></li>
										<li>
										  <a href="/list.html?cat_id=1069">清洁工具</a></li>
										<li>
										  <a href="/list.html?cat_id=1068">家庭清洁</a></li>
										<li>
										  <a href="/list.html?cat_id=1197">一次性用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1198">驱虫用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1199">皮具护理</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">宠物生活<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1901">水族用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1902">宠物主粮</a></li>
										<li>
										  <a href="/list.html?cat_id=1903">宠物零食</a></li>
										<li>
										  <a href="/list.html?cat_id=1905">猫砂/尿布</a></li>
										<li>
										  <a href="/list.html?cat_id=1906">洗护美容</a></li>
										<li>
										  <a href="/list.html?cat_id=1907">家居日用</a></li>
										<li>
										  <a href="/list.html?cat_id=1908">医疗保健</a></li>
										<li>
										  <a href="/list.html?cat_id=1909">出行装备</a></li>
										<li>
										  <a href="/list.html?cat_id=1910">宠物玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=1911">宠物牵引</a></li>
										<li>
										  <a href="/list.html?cat_id=1912">宠物驱虫</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">鞋靴、箱包、钟表、奢侈品</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">时尚女鞋<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1633">单鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1634">休闲鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1635">帆布鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1636">鱼嘴鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1637">妈妈鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1638">凉鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1639">拖鞋/人字拖</a></li>
										<li>
										  <a href="/list.html?cat_id=1640">布鞋/绣花鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1641">坡跟鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1642">松糕鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1643">防水台</a></li>
										<li>
										  <a href="/list.html?cat_id=1644">高跟鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1645">踝靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1646">内增高</a></li>
										<li>
										  <a href="/list.html?cat_id=1647">女靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1648">马丁靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1649">雪地靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1650">雨鞋/雨靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1651">鞋配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">流行男鞋<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1652">休闲鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1653">商务休闲鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1654">正装鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1655">帆布鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1656">工装鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1657">增高鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1658">拖鞋/人字拖</a></li>
										<li>
										  <a href="/list.html?cat_id=1659">凉鞋/沙滩鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1660">雨鞋/雨靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1661">定制鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1662">男靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1663">传统布鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1664">功能鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1665">鞋配件</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">潮流女包<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1106">单肩包</a></li>
										<li>
										  <a href="/list.html?cat_id=1107">手提包</a></li>
										<li>
										  <a href="/list.html?cat_id=1108">斜挎包</a></li>
										<li>
										  <a href="/list.html?cat_id=1109">双肩包</a></li>
										<li>
										  <a href="/list.html?cat_id=1110">钱包</a></li>
										<li>
										  <a href="/list.html?cat_id=1111">手拿包</a></li>
										<li>
										  <a href="/list.html?cat_id=1112">卡包/零钱包</a></li>
										<li>
										  <a href="/list.html?cat_id=1113">钥匙包</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">精品男包<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1114">商务公文包</a></li>
										<li>
										  <a href="/list.html?cat_id=1115">单肩/斜挎包</a></li>
										<li>
										  <a href="/list.html?cat_id=1116">男士手包</a></li>
										<li>
										  <a href="/list.html?cat_id=1117">双肩包</a></li>
										<li>
										  <a href="/list.html?cat_id=732">男士钱包</a></li>
										<li>
										  <a href="/list.html?cat_id=1118">卡包名片夹</a></li>
										<li>
										  <a href="/list.html?cat_id=1119">钥匙包</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">功能箱包<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1120">拉杆箱</a></li>
										<li>
										  <a href="/list.html?cat_id=1121">拉杆包</a></li>
										<li>
										  <a href="/list.html?cat_id=1122">旅行包</a></li>
										<li>
										  <a href="/list.html?cat_id=1123">电脑包</a></li>
										<li>
										  <a href="/list.html?cat_id=1124">休闲运动包</a></li>
										<li>
										  <a href="/list.html?cat_id=1125">相机包</a></li>
										<li>
										  <a href="/list.html?cat_id=1126">腰包/胸包</a></li>
										<li>
										  <a href="/list.html?cat_id=1127">登山包</a></li>
										<li>
										  <a href="/list.html?cat_id=1128">旅行配件</a></li>
										<li>
										  <a href="/list.html?cat_id=1129">书包</a></li>
										<li>
										  <a href="/list.html?cat_id=1130">妈咪包</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">奢侈品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1666">箱包</a></li>
										<li>
										  <a href="/list.html?cat_id=1667">钱包</a></li>
										<li>
										  <a href="/list.html?cat_id=1668">服饰</a></li>
										<li>
										  <a href="/list.html?cat_id=1669">腰带</a></li>
										<li>
										  <a href="/list.html?cat_id=1670">鞋靴</a></li>
										<li>
										  <a href="/list.html?cat_id=1671">太阳镜/眼镜框</a></li>
										<li>
										  <a href="/list.html?cat_id=1672">饰品</a></li>
										<li>
										  <a href="/list.html?cat_id=1673">配件</a></li>
										<li>
										  <a href="/list.html?cat_id=1674">GUCCI</a></li>
										<li>
										  <a href="/list.html?cat_id=1675">COACH</a></li>
										<li>
										  <a href="/list.html?cat_id=1676">雷朋</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">礼品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1137">火机烟具</a></li>
										<li>
										  <a href="/list.html?cat_id=1138">军刀军具</a></li>
										<li>
										  <a href="/list.html?cat_id=1139">美妆礼品</a></li>
										<li>
										  <a href="/list.html?cat_id=1140">工艺礼品</a></li>
										<li>
										  <a href="/list.html?cat_id=1141">礼盒礼券</a></li>
										<li>
										  <a href="/list.html?cat_id=1142">礼品文具</a></li>
										<li>
										  <a href="/list.html?cat_id=1143">收藏品</a></li>
										<li>
										  <a href="/list.html?cat_id=1883">古董把玩</a></li>
										<li>
										  <a href="/list.html?cat_id=1145">礼品定制</a></li>
										<li>
										  <a href="/list.html?cat_id=1146">创意礼品</a></li>
										<li>
										  <a href="/list.html?cat_id=1147">婚庆用品</a></li>
										<li>
										  <a href="/list.html?cat_id=1149">鲜花绿植</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">珠宝首饰<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1677">黄金</a></li>
										<li>
										  <a href="/list.html?cat_id=1678">K金</a></li>
										<li>
										  <a href="/list.html?cat_id=1679">时尚饰品</a></li>
										<li>
										  <a href="/list.html?cat_id=1680">钻石</a></li>
										<li>
										  <a href="/list.html?cat_id=1681">翡翠玉石</a></li>
										<li>
										  <a href="/list.html?cat_id=1682">银饰</a></li>
										<li>
										  <a href="/list.html?cat_id=1683">水晶玛瑙</a></li>
										<li>
										  <a href="/list.html?cat_id=1684">彩宝</a></li>
										<li>
										  <a href="/list.html?cat_id=1685">铂金</a></li>
										<li>
										  <a href="/list.html?cat_id=1686">木手串/把件</a></li>
										<li>
										  <a href="/list.html?cat_id=1687">珍珠</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">运动户外、钟表</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">运动鞋包<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=901">跑步鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=902">休闲鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=903">篮球鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=907">帆布鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=904">板鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=911">拖鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=905">运动包</a></li>
										<li>
										  <a href="/list.html?cat_id=906">足球鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=908">乒羽网鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=909">训练鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=910">专项运动鞋</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">运动服饰<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=923">T恤</a></li>
										<li>
										  <a href="/list.html?cat_id=916">运动裤</a></li>
										<li>
										  <a href="/list.html?cat_id=921">健身服</a></li>
										<li>
										  <a href="/list.html?cat_id=912">运动套装</a></li>
										<li>
										  <a href="/list.html?cat_id=919">运动背心</a></li>
										<li>
										  <a href="/list.html?cat_id=913">羽绒服</a></li>
										<li>
										  <a href="/list.html?cat_id=914">卫衣/套头衫</a></li>
										<li>
										  <a href="/list.html?cat_id=915">棉服</a></li>
										<li>
										  <a href="/list.html?cat_id=917">夹克/风衣</a></li>
										<li>
										  <a href="/list.html?cat_id=918">运动配饰</a></li>
										<li>
										  <a href="/list.html?cat_id=920">兵羽网服</a></li>
										<li>
										  <a href="/list.html?cat_id=922">毛衫/线衫</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">健身训练<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1708">跑步机</a></li>
										<li>
										  <a href="/list.html?cat_id=1709">健身车/动感单车</a></li>
										<li>
										  <a href="/list.html?cat_id=926">哑铃</a></li>
										<li>
										  <a href="/list.html?cat_id=927">仰卧板/收腹机</a></li>
										<li>
										  <a href="/list.html?cat_id=1710">甩脂机</a></li>
										<li>
										  <a href="/list.html?cat_id=1711">踏步机</a></li>
										<li>
										  <a href="/list.html?cat_id=930">运动护具</a></li>
										<li>
										  <a href="/list.html?cat_id=931">瑜伽舞蹈</a></li>
										<li>
										  <a href="/list.html?cat_id=932">武术搏击</a></li>
										<li>
										  <a href="/list.html?cat_id=1814">综合训练器</a></li>
										<li>
										  <a href="/list.html?cat_id=1815">其他大型器械</a></li>
										<li>
										  <a href="/list.html?cat_id=1816">其他中小型器械</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">骑行运动<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=936">山地车/公路车</a></li>
										<li>
										  <a href="/list.html?cat_id=937">折叠车</a></li>
										<li>
										  <a href="/list.html?cat_id=938">电动车</a></li>
										<li>
										  <a href="/list.html?cat_id=939">平衡车</a></li>
										<li>
										  <a href="/list.html?cat_id=940">其他整车</a></li>
										<li>
										  <a href="/list.html?cat_id=941">骑行装备</a></li>
										<li>
										  <a href="/list.html?cat_id=942">骑行服</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">体育用品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=943">乒乓球</a></li>
										<li>
										  <a href="/list.html?cat_id=944">羽毛球</a></li>
										<li>
										  <a href="/list.html?cat_id=945">篮球</a></li>
										<li>
										  <a href="/list.html?cat_id=946">足球</a></li>
										<li>
										  <a href="/list.html?cat_id=947">轮滑滑板</a></li>
										<li>
										  <a href="/list.html?cat_id=948">网球</a></li>
										<li>
										  <a href="/list.html?cat_id=949">高尔夫</a></li>
										<li>
										  <a href="/list.html?cat_id=950">台球</a></li>
										<li>
										  <a href="/list.html?cat_id=951">排球</a></li>
										<li>
										  <a href="/list.html?cat_id=952">棋牌麻将</a></li>
										<li>
										  <a href="/list.html?cat_id=953">其他</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">户外鞋服<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1689">户外风衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1690">徒步鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1691">T恤</a></li>
										<li>
										  <a href="/list.html?cat_id=1692">冲锋衣裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1693">速干衣裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1694">越野跑鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1695">滑雪服</a></li>
										<li>
										  <a href="/list.html?cat_id=1696">羽绒服/棉服</a></li>
										<li>
										  <a href="/list.html?cat_id=1697">休闲衣裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1913">抓绒衣裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1699">溯溪鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1700">沙滩/凉拖</a></li>
										<li>
										  <a href="/list.html?cat_id=1701">休闲鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1702">软壳衣裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1703">功能内衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1704">军迷服饰</a></li>
										<li>
										  <a href="/list.html?cat_id=1705">登山鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1706">工装鞋</a></li>
										<li>
										  <a href="/list.html?cat_id=1707">户外袜</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">户外装备<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=974">帐篷/垫子</a></li>
										<li>
										  <a href="/list.html?cat_id=981">望远镜</a></li>
										<li>
										  <a href="/list.html?cat_id=978">野餐烧烤</a></li>
										<li>
										  <a href="/list.html?cat_id=979">便携桌椅床</a></li>
										<li>
										  <a href="/list.html?cat_id=973">背包</a></li>
										<li>
										  <a href="/list.html?cat_id=989">户外配饰</a></li>
										<li>
										  <a href="/list.html?cat_id=984">军迷用品</a></li>
										<li>
										  <a href="/list.html?cat_id=975">睡袋/吊床</a></li>
										<li>
										  <a href="/list.html?cat_id=985">救援装备</a></li>
										<li>
										  <a href="/list.html?cat_id=977">户外照明</a></li>
										<li>
										  <a href="/list.html?cat_id=983">旅游用品</a></li>
										<li>
										  <a href="/list.html?cat_id=980">户外工具</a></li>
										<li>
										  <a href="/list.html?cat_id=982">户外仪表</a></li>
										<li>
										  <a href="/list.html?cat_id=976">登山攀岩</a></li>
										<li>
										  <a href="/list.html?cat_id=987">极限户外</a></li>
										<li>
										  <a href="/list.html?cat_id=988">冲浪潜水</a></li>
										<li>
										  <a href="/list.html?cat_id=1914">滑雪装备</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">垂钓用品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=990">鱼竿鱼线</a></li>
										<li>
										  <a href="/list.html?cat_id=991">浮漂鱼饵</a></li>
										<li>
										  <a href="/list.html?cat_id=992">钓鱼桌椅</a></li>
										<li>
										  <a href="/list.html?cat_id=993">钓鱼配件</a></li>
										<li>
										  <a href="/list.html?cat_id=994">钓箱鱼包</a></li>
										<li>
										  <a href="/list.html?cat_id=995">其他</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">游泳用品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=996">泳镜</a></li>
										<li>
										  <a href="/list.html?cat_id=997">泳帽</a></li>
										<li>
										  <a href="/list.html?cat_id=998">游泳包防水包</a></li>
										<li>
										  <a href="/list.html?cat_id=999">男士泳衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1000">女士泳衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1001">比基尼</a></li>
										<li>
										  <a href="/list.html?cat_id=1002">其他</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">钟表<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1003">男表</a></li>
										<li>
										  <a href="/list.html?cat_id=1004">女表</a></li>
										<li>
										  <a href="/list.html?cat_id=1005">儿童表</a></li>
										<li>
										  <a href="/list.html?cat_id=1712">智能手表</a></li>
										<li>
										  <a href="/list.html?cat_id=1713">座钟挂钟</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">汽车用品</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">车载电器<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1342">行车记录仪</a></li>
										<li>
										  <a href="/list.html?cat_id=1343">导航仪</a></li>
										<li>
										  <a href="/list.html?cat_id=777">电源</a></li>
										<li>
										  <a href="/list.html?cat_id=778">电器配件</a></li>
										<li>
										  <a href="/list.html?cat_id=779">净化器</a></li>
										<li>
										  <a href="/list.html?cat_id=780">车载影音</a></li>
										<li>
										  <a href="/list.html?cat_id=781">车载冰箱</a></li>
										<li>
										  <a href="/list.html?cat_id=782">安全预警仪</a></li>
										<li>
										  <a href="/list.html?cat_id=783">倒车雷达</a></li>
										<li>
										  <a href="/list.html?cat_id=784">蓝牙设备</a></li>
										<li>
										  <a href="/list.html?cat_id=785">智能驾驶</a></li>
										<li>
										  <a href="/list.html?cat_id=786">车载电台</a></li>
										<li>
										  <a href="/list.html?cat_id=787">吸尘器</a></li>
										<li>
										  <a href="/list.html?cat_id=788">智能车机</a></li>
										<li>
										  <a href="/list.html?cat_id=789">汽车音响</a></li>
										<li>
										  <a href="/list.html?cat_id=790">车载生活电器</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">美容清洗<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=791">车蜡</a></li>
										<li>
										  <a href="/list.html?cat_id=792">镀晶镀膜</a></li>
										<li>
										  <a href="/list.html?cat_id=793">补漆笔</a></li>
										<li>
										  <a href="/list.html?cat_id=794">玻璃水</a></li>
										<li>
										  <a href="/list.html?cat_id=795">清洁剂</a></li>
										<li>
										  <a href="/list.html?cat_id=796">洗车机</a></li>
										<li>
										  <a href="/list.html?cat_id=797">洗车水枪</a></li>
										<li>
										  <a href="/list.html?cat_id=798">汽车配件</a></li>
										<li>
										  <a href="/list.html?cat_id=799">毛巾掸子</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">汽车装饰<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=800">脚垫</a></li>
										<li>
										  <a href="/list.html?cat_id=801">座垫</a></li>
										<li>
										  <a href="/list.html?cat_id=802">座套</a></li>
										<li>
										  <a href="/list.html?cat_id=803">后备箱垫</a></li>
										<li>
										  <a href="/list.html?cat_id=804">方向盘套</a></li>
										<li>
										  <a href="/list.html?cat_id=731">头枕腰靠</a></li>
										<li>
										  <a href="/list.html?cat_id=805">香水</a></li>
										<li>
										  <a href="/list.html?cat_id=806">空气净化</a></li>
										<li>
										  <a href="/list.html?cat_id=441">功能小件</a></li>
										<li>
										  <a href="/list.html?cat_id=807">车衣</a></li>
										<li>
										  <a href="/list.html?cat_id=808">挂件摆件</a></li>
										<li>
										  <a href="/list.html?cat_id=809">车身装饰件</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">安全自驾<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1918">安全座椅</a></li>
										<li>
										  <a href="/list.html?cat_id=1919">胎压监测</a></li>
										<li>
										  <a href="/list.html?cat_id=1920">充气泵</a></li>
										<li>
										  <a href="/list.html?cat_id=1921">防盗设备</a></li>
										<li>
										  <a href="/list.html?cat_id=1922">应急救援</a></li>
										<li>
										  <a href="/list.html?cat_id=1923">保温箱</a></li>
										<li>
										  <a href="/list.html?cat_id=1924">储物箱</a></li>
										<li>
										  <a href="/list.html?cat_id=1925">自驾野营</a></li>
										<li>
										  <a href="/list.html?cat_id=1926">摩托车装备</a></li>
										<li>
										  <a href="/list.html?cat_id=1927">摩托车</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">母婴、玩具、乐器</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">奶粉<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1715">婴儿奶粉</a></li>
										<li>
										  <a href="/list.html?cat_id=718">孕妈奶粉</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">营养辅食<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1723">米粉/菜粉</a></li>
										<li>
										  <a href="/list.html?cat_id=1724">面条/粥</a></li>
										<li>
										  <a href="/list.html?cat_id=1725">果泥/果汁</a></li>
										<li>
										  <a href="/list.html?cat_id=1726">益生菌/初乳</a></li>
										<li>
										  <a href="/list.html?cat_id=1727">DHA</a></li>
										<li>
										  <a href="/list.html?cat_id=1728">钙铁锌/维生素</a></li>
										<li>
										  <a href="/list.html?cat_id=1729">清火/开胃</a></li>
										<li>
										  <a href="/list.html?cat_id=1730">宝宝零食</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">尿裤湿巾<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1731">婴儿尿裤</a></li>
										<li>
										  <a href="/list.html?cat_id=829">拉拉裤</a></li>
										<li>
										  <a href="/list.html?cat_id=828">成人尿裤</a></li>
										<li>
										  <a href="/list.html?cat_id=826">婴儿湿巾</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">喂养用品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=838">奶嘴奶瓶</a></li>
										<li>
										  <a href="/list.html?cat_id=839">吸奶器</a></li>
										<li>
										  <a href="/list.html?cat_id=841">暖奶消毒</a></li>
										<li>
										  <a href="/list.html?cat_id=842">辅食料理机</a></li>
										<li>
										  <a href="/list.html?cat_id=840">牙胶安抚</a></li>
										<li>
										  <a href="/list.html?cat_id=1739">食物存储</a></li>
										<li>
										  <a href="/list.html?cat_id=843">儿童餐具</a></li>
										<li>
										  <a href="/list.html?cat_id=844">水壶/水杯</a></li>
										<li>
										  <a href="/list.html?cat_id=1740">围兜/防溅衣</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">洗护用品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=830">宝宝护肤</a></li>
										<li>
										  <a href="/list.html?cat_id=835">日常护理</a></li>
										<li>
										  <a href="/list.html?cat_id=831">洗发沐浴</a></li>
										<li>
										  <a href="/list.html?cat_id=1737">洗澡用具</a></li>
										<li>
										  <a href="/list.html?cat_id=833">洗衣液/皂</a></li>
										<li>
										  <a href="/list.html?cat_id=832">理发器</a></li>
										<li>
										  <a href="/list.html?cat_id=1738">婴儿口腔清洁</a></li>
										<li>
										  <a href="/list.html?cat_id=836">座便器</a></li>
										<li>
										  <a href="/list.html?cat_id=837">驱蚊防蚊</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">寝居服饰<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1741">睡袋/抱被</a></li>
										<li>
										  <a href="/list.html?cat_id=1742">家居床品</a></li>
										<li>
										  <a href="/list.html?cat_id=1743">安全防护</a></li>
										<li>
										  <a href="/list.html?cat_id=1744">爬行垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1745">婴儿内衣</a></li>
										<li>
										  <a href="/list.html?cat_id=1746">婴儿礼盒</a></li>
										<li>
										  <a href="/list.html?cat_id=1747">婴儿鞋帽袜</a></li>
										<li>
										  <a href="/list.html?cat_id=1748">婴儿外出服</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">妈妈专区<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1749">防辐射服</a></li>
										<li>
										  <a href="/list.html?cat_id=1750">孕妈装</a></li>
										<li>
										  <a href="/list.html?cat_id=1751">孕妇护肤</a></li>
										<li>
										  <a href="/list.html?cat_id=1752">妈咪包/背婴带</a></li>
										<li>
										  <a href="/list.html?cat_id=1753">待产护理</a></li>
										<li>
										  <a href="/list.html?cat_id=1754">产后塑身</a></li>
										<li>
										  <a href="/list.html?cat_id=1755">文胸/内裤</a></li>
										<li>
										  <a href="/list.html?cat_id=1756">防溢乳垫</a></li>
										<li>
										  <a href="/list.html?cat_id=1757">孕期营养</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">童车童床<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1758">安全座椅</a></li>
										<li>
										  <a href="/list.html?cat_id=847">婴儿推车</a></li>
										<li>
										  <a href="/list.html?cat_id=846">婴儿床</a></li>
										<li>
										  <a href="/list.html?cat_id=1915">婴儿床垫</a></li>
										<li>
										  <a href="/list.html?cat_id=848">餐椅</a></li>
										<li>
										  <a href="/list.html?cat_id=849">学步车</a></li>
										<li>
										  <a href="/list.html?cat_id=850">三轮车</a></li>
										<li>
										  <a href="/list.html?cat_id=851">自行车</a></li>
										<li>
										  <a href="/list.html?cat_id=852">扭扭车</a></li>
										<li>
										  <a href="/list.html?cat_id=853">滑板车</a></li>
										<li>
										  <a href="/list.html?cat_id=854">电动车</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">玩具<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1916">适用年龄</a></li>
										<li>
										  <a href="/list.html?cat_id=890">遥控/电动</a></li>
										<li>
										  <a href="/list.html?cat_id=891">益智玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=892">积木拼插</a></li>
										<li>
										  <a href="/list.html?cat_id=893">动漫玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=894">毛绒布艺</a></li>
										<li>
										  <a href="/list.html?cat_id=895">模型玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=896">健身玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=897">娃娃玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=898">DIY玩具</a></li>
										<li>
										  <a href="/list.html?cat_id=899">创意减压</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">乐器<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1759">钢琴</a></li>
										<li>
										  <a href="/list.html?cat_id=1760">电子琴/电钢琴</a></li>
										<li>
										  <a href="/list.html?cat_id=1761">吉他/尤克里里</a></li>
										<li>
										  <a href="/list.html?cat_id=1762">打击乐器</a></li>
										<li>
										  <a href="/list.html?cat_id=1763">西洋管弦</a></li>
										<li>
										  <a href="/list.html?cat_id=1764">民族乐器</a></li>
										<li>
										  <a href="/list.html?cat_id=1765">乐器配件</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">食品、酒类、生鲜、特产</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">水果蔬菜<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1382">苹果</a></li>
										<li>
										  <a href="/list.html?cat_id=1383">奇异果</a></li>
										<li>
										  <a href="/list.html?cat_id=1384">芒果</a></li>
										<li>
										  <a href="/list.html?cat_id=1395">大樱桃</a></li>
										<li>
										  <a href="/list.html?cat_id=1385">时令水果</a></li>
										<li>
										  <a href="/list.html?cat_id=1399">叶菜类</a></li>
										<li>
										  <a href="/list.html?cat_id=1400">茄果瓜类</a></li>
										<li>
										  <a href="/list.html?cat_id=1770">根茎类</a></li>
										<li>
										  <a href="/list.html?cat_id=1771">鲜菌菇</a></li>
										<li>
										  <a href="/list.html?cat_id=1772">葱姜蒜椒</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">海鲜水产<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1386">虾类</a></li>
										<li>
										  <a href="/list.html?cat_id=1773">鱼类</a></li>
										<li>
										  <a href="/list.html?cat_id=1388">蟹类</a></li>
										<li>
										  <a href="/list.html?cat_id=1389">贝类</a></li>
										<li>
										  <a href="/list.html?cat_id=1390">海参</a></li>
										<li>
										  <a href="/list.html?cat_id=1397">海产干货</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">猪羊牛肉<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1777">牛肉</a></li>
										<li>
										  <a href="/list.html?cat_id=1778">羊肉</a></li>
										<li>
										  <a href="/list.html?cat_id=1779">猪肉</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">禽类蛋品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1785">鸡肉</a></li>
										<li>
										  <a href="/list.html?cat_id=1928">鸭肉</a></li>
										<li>
										  <a href="/list.html?cat_id=1787">蛋类</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">冷冻食饮<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1790">水饺</a></li>
										<li>
										  <a href="/list.html?cat_id=1791">汤圆</a></li>
										<li>
										  <a href="/list.html?cat_id=1792">面点</a></li>
										<li>
										  <a href="/list.html?cat_id=1793">火锅丸串</a></li>
										<li>
										  <a href="/list.html?cat_id=1817">速冻半成品</a></li>
										<li>
										  <a href="/list.html?cat_id=1818">奶酪/黄油</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">中外名酒<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=442">白酒</a></li>
										<li>
										  <a href="/list.html?cat_id=650">葡萄酒</a></li>
										<li>
										  <a href="/list.html?cat_id=651">洋酒</a></li>
										<li>
										  <a href="/list.html?cat_id=652">啤酒</a></li>
										<li>
										  <a href="/list.html?cat_id=653">黄酒/养生酒</a></li>
										<li>
										  <a href="/list.html?cat_id=654">收藏酒/陈年老酒</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">进口食品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=655">牛奶</a></li>
										<li>
										  <a href="/list.html?cat_id=656">饼干蛋糕</a></li>
										<li>
										  <a href="/list.html?cat_id=657">糖果/巧克力</a></li>
										<li>
										  <a href="/list.html?cat_id=658">休闲零食</a></li>
										<li>
										  <a href="/list.html?cat_id=659">冲调饮品</a></li>
										<li>
										  <a href="/list.html?cat_id=660">粮油调味</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">休闲食品<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=661">休闲零食</a></li>
										<li>
										  <a href="/list.html?cat_id=662">坚果炒货</a></li>
										<li>
										  <a href="/list.html?cat_id=663">肉干肉脯</a></li>
										<li>
										  <a href="/list.html?cat_id=664">蜜饯干果</a></li>
										<li>
										  <a href="/list.html?cat_id=665">糖果/巧克力</a></li>
										<li>
										  <a href="/list.html?cat_id=666">饼干蛋糕</a></li>
										<li>
										  <a href="/list.html?cat_id=667">无糖食品</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">地方特产<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1796">新疆</a></li>
										<li>
										  <a href="/list.html?cat_id=1797">四川</a></li>
										<li>
										  <a href="/list.html?cat_id=1798">云南</a></li>
										<li>
										  <a href="/list.html?cat_id=1799">湖南</a></li>
										<li>
										  <a href="/list.html?cat_id=1800">内蒙</a></li>
										<li>
										  <a href="/list.html?cat_id=1801">北京</a></li>
										<li>
										  <a href="/list.html?cat_id=1802">山西</a></li>
										<li>
										  <a href="/list.html?cat_id=1803">福建</a></li>
										<li>
										  <a href="/list.html?cat_id=1804">东北</a></li>
										<li>
										  <a href="/list.html?cat_id=1805">其他</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">茗茶<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=678">铁观音</a></li>
										<li>
										  <a href="/list.html?cat_id=679">普洱</a></li>
										<li>
										  <a href="/list.html?cat_id=680">龙井</a></li>
										<li>
										  <a href="/list.html?cat_id=681">绿茶</a></li>
										<li>
										  <a href="/list.html?cat_id=682">红茶</a></li>
										<li>
										  <a href="/list.html?cat_id=683">乌龙茶</a></li>
										<li>
										  <a href="/list.html?cat_id=684">花草茶</a></li>
										<li>
										  <a href="/list.html?cat_id=685">花果茶</a></li>
										<li>
										  <a href="/list.html?cat_id=686">黑茶</a></li>
										<li>
										  <a href="/list.html?cat_id=687">白茶</a></li>
										<li>
										  <a href="/list.html?cat_id=688">养生茶</a></li>
										<li>
										  <a href="/list.html?cat_id=689">其他茶</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">饮料冲调<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=690">牛奶乳品</a></li>
										<li>
										  <a href="/list.html?cat_id=691">饮料</a></li>
										<li>
										  <a href="/list.html?cat_id=692">饮用水</a></li>
										<li>
										  <a href="/list.html?cat_id=693">咖啡/奶茶</a></li>
										<li>
										  <a href="/list.html?cat_id=694">蜂蜜/柚子茶</a></li>
										<li>
										  <a href="/list.html?cat_id=695">冲饮谷物</a></li>
										<li>
										  <a href="/list.html?cat_id=696">成人奶粉</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">粮油调味<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=698">米面杂粮</a></li>
										<li>
										  <a href="/list.html?cat_id=699">食用油</a></li>
										<li>
										  <a href="/list.html?cat_id=700">调味品</a></li>
										<li>
										  <a href="/list.html?cat_id=701">南北干货</a></li>
										<li>
										  <a href="/list.html?cat_id=702">方便食品</a></li>
										<li>
										  <a href="/list.html?cat_id=703">有机食品</a></li>
									</ul>
								</div>
							</div>
						</li>
						<li><a href="javascript:;">医药保健</a>
							<div class="details">
								<div class="goods up">
									<div class="goodstitle">
										<a href="javascript:;">营养健康<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1838">调节免疫</a></li>
										<li>
										  <a href="/list.html?cat_id=1837">调节三高</a></li>
										<li>
										  <a href="/list.html?cat_id=1839">缓解疲劳</a></li>
										<li>
										  <a href="/list.html?cat_id=1840">美体塑身</a></li>
										<li>
										  <a href="/list.html?cat_id=1841">美容养颜</a></li>
										<li>
										  <a href="/list.html?cat_id=1842">肝肾养护</a></li>
										<li>
										  <a href="/list.html?cat_id=1843">肠胃养护</a></li>
										<li>
										  <a href="/list.html?cat_id=1844">明目益智</a></li>
										<li>
										  <a href="/list.html?cat_id=1845">骨骼健康</a></li>
										<li>
										  <a href="/list.html?cat_id=1846">改善睡眠</a></li>
										<li>
										  <a href="/list.html?cat_id=1847">抗氧化</a></li>
										<li>
										  <a href="/list.html?cat_id=1848">耐缺氧</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">营养成分<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1849">维生素/矿物质</a></li>
										<li>
										  <a href="/list.html?cat_id=1850">蛋白质</a></li>
										<li>
										  <a href="/list.html?cat_id=1851">鱼油/磷脂</a></li>
										<li>
										  <a href="/list.html?cat_id=1852">螺旋藻</a></li>
										<li>
										  <a href="/list.html?cat_id=1853">番茄红素</a></li>
										<li>
										  <a href="/list.html?cat_id=1854">叶酸</a></li>
										<li>
										  <a href="/list.html?cat_id=1855">葡萄籽</a></li>
										<li>
										  <a href="/list.html?cat_id=1856">左旋肉碱</a></li>
										<li>
										  <a href="/list.html?cat_id=1857">辅酶Q10</a></li>
										<li>
										  <a href="/list.html?cat_id=1858">益生菌</a></li>
										<li>
										  <a href="/list.html?cat_id=1859">玛咖</a></li>
										<li>
										  <a href="/list.html?cat_id=1860">膳食纤维</a></li>
										<li>
										  <a href="/list.html?cat_id=1861">牛初乳</a></li>
										<li>
										  <a href="/list.html?cat_id=1862">胶原蛋白</a></li>
										<li>
										  <a href="/list.html?cat_id=1863">大豆异黄酮</a></li>
										<li>
										  <a href="/list.html?cat_id=1864">芦荟提取</a></li>
										<li>
										  <a href="/list.html?cat_id=1865">酵素</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">滋补养生<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1866">阿胶</a></li>
										<li>
										  <a href="/list.html?cat_id=1867">蜂蜜/蜂产品</a></li>
										<li>
										  <a href="/list.html?cat_id=1868">枸杞</a></li>
										<li>
										  <a href="/list.html?cat_id=1870">燕窝</a></li>
										<li>
										  <a href="/list.html?cat_id=1871">海参</a></li>
										<li>
										  <a href="/list.html?cat_id=1904">冬虫夏草</a></li>
										<li>
										  <a href="/list.html?cat_id=1873">人参/西洋参</a></li>
										<li>
										  <a href="/list.html?cat_id=1874">三七</a></li>
										<li>
										  <a href="/list.html?cat_id=1875">鹿茸</a></li>
										<li>
										  <a href="/list.html?cat_id=1876">雪蛤</a></li>
										<li>
										  <a href="/list.html?cat_id=1877">青钱柳</a></li>
										<li>
										  <a href="/list.html?cat_id=1878">石斛/枫斗</a></li>
										<li>
										  <a href="/list.html?cat_id=1879">灵芝/袍子粉</a></li>
										<li>
										  <a href="/list.html?cat_id=1880">当归</a></li>
										<li>
										  <a href="/list.html?cat_id=1881">养生茶饮</a></li>
										<li>
										  <a href="/list.html?cat_id=1882">药食同源</a></li>
									</ul>
								</div>
								<div class="goods">
									<div class="goodstitle">
										<a href="javascript:;">保健器械<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=625">血压计</a></li>
										<li>
										  <a href="/list.html?cat_id=1819">血糖仪</a></li>
										<li>
										  <a href="/list.html?cat_id=1820">血氧仪</a></li>
										<li>
										  <a href="/list.html?cat_id=628">体温计</a></li>
										<li>
										  <a href="/list.html?cat_id=629">体重秤</a></li>
										<li>
										  <a href="/list.html?cat_id=1821">胎心仪</a></li>
										<li>
										  <a href="/list.html?cat_id=1822">呼吸制氧</a></li>
										<li>
										  <a href="/list.html?cat_id=1823">雾化器</a></li>
										<li>
										  <a href="/list.html?cat_id=1824">助听器</a></li>
										<li>
										  <a href="/list.html?cat_id=1825">轮椅</a></li>
										<li>
										  <a href="/list.html?cat_id=1826">拐杖</a></li>
										<li>
										  <a href="/list.html?cat_id=1827">中医保健</a></li>
										<li>
										  <a href="/list.html?cat_id=1828">养生器械</a></li>
										<li>
										  <a href="/list.html?cat_id=1829">理疗仪</a></li>
										<li>
										  <a href="/list.html?cat_id=1830">家庭护理</a></li>
										<li>
										  <a href="/list.html?cat_id=1831">智能健康</a></li>
									</ul>
								</div>
								<div class="goods down">
									<div class="goodstitle">
										<a href="javascript:;">护理护具<span>&gt;</span></a>
									</div>
									<ul>
										<li>
										  <a href="/list.html?cat_id=1832">隐形眼镜</a></li>
										<li>
										  <a href="/list.html?cat_id=1833">护理液</a></li>
										<li>
										  <a href="/list.html?cat_id=643">口罩</a></li>
										<li>
										  <a href="/list.html?cat_id=644">眼罩/耳塞</a></li>
										<li>
										  <a href="/list.html?cat_id=645">跌打损伤</a></li>
										<li>
										  <a href="/list.html?cat_id=646">暖贴</a></li>
										<li>
										  <a href="/list.html?cat_id=647">鼻喉护理</a></li>
										<li>
										  <a href="/list.html?cat_id=648">眼部保健</a></li>
										<li>
										  <a href="/list.html?cat_id=649">美体护理</a></li>
									</ul>
								</div>
							</div>
						</li>
					</ul>
				</div>
				
				<ul id="gift">
					<li class="current"><a href="">首页</a></li>
					<li class="send">
						<a href="" class="send_title">赠送对象<div class="arrow"></div></a>
						<ul class="sidebar_hide"> 
							<li><a href="">女朋友</a></li>
							<li><a href="">男朋友</a></li>
							<li><a href="">单身狗</a></li>
							<li><a href="">闺蜜</a></li>
						</ul>
					</li>
					<li class="send">
						<a href="" class="send_title">赠送情景<div class="arrow"></div></a>
						<ul class="sidebar_hide">
							<li><a href="">中秋节</a></li>
							<li><a href="">国庆节</a></li>
							<li><a href="">七夕节</a></li>
						</ul>
					</li>
					<li><a href="">中秋推荐</a></li>
				</ul>
			</div>
		</div>
	</div>

	<!-- 商品预览 -->
	<div class="wrap" id="goods_info">
		<!-- 商品细分类 -->
		<div class="category">
			<ul>
				<?php if(is_array($resCatList)): $i = 0; $__LIST__ = $resCatList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$rcl): $mod = ($i % 2 );++$i;?><li><a href=""><?php echo ($rcl['cat_name']); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>
		<input type="hidden" id="shopId" name="shop_id" value="<?php echo ($itemInfo['shop_id']); ?>">
		<input type="hidden" id="itemStatus" name="item_status" value="<?php echo (getItemStatus($itemInfo['item_id'])); ?>">
		<!-- 商品详情 -->
		<!-- 左边图片预览 -->
		<div class="details_left">
			<div id="preview_big"></div>
			<div id="preview_small">
				<?php if(is_array($itemInfo['new_list_images'])): $i = 0; $__LIST__ = $itemInfo['new_list_images'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$itemImg): $mod = ($i % 2 );++$i;?><img src="<?php echo ($itemImg); ?>" class="pics"><?php endforeach; endif; else: echo "" ;endif; ?>				
			</div>
			<div class="links">
				<div class="collection">
            		<?php if($uid == 0): ?><span class="">收藏商品</span>
					<?php else: ?>
						<input type="hidden" name="userFav" class="user-fav" value="<?php echo (getUserFav($itemInfo['item_id'],$uid)); ?>">
						<span class="doUserFav">收藏商品</span><?php endif; ?>
				</div>
				<div class="share">分享到：
					<a class="jiatitle" id="wechat" href="javascript:;" onclick="jiathis_sendto('weixin')"; return false><span class="jtico jtico_weixin"></span></a>
					<a class="jiatitle" id="qq" href="javascript:;" onclick="jiathis_sendto('cqq')"; return false><span class="jtico jtico_qq"></span></a>
					<a class="jiatitle" id="qzone" href="javascript:;" onclick="jiathis_sendto('qzone')"; return false><span class="jtico jtico_qzone"></span></a>	
					<a class="jiatitle" id="weibo" href="javascript:;" onclick="jiathis_sendto('tsina')"; return false><span class="jtico jtico_tsina"></span></a>					
				</div>
				<script type="text/javascript" src="/public/js/share/jia.js" charset="utf-8"></script>
			</div>
		</div>
		<!-- 中间 -->
		<div class="details_middle">
			<div class="goods_name">
				<?php echo ($itemInfo['title']); ?>
			</div>
			<table>
				<tr class="benefits">
					<td>福利积分：</td>
					<td class="price"><span><?php echo ($itemInfo['integral']); ?></span>&nbsp;积分</td>
				</tr>
				<tr class="benefits">
					<td>价&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;格：</td>
					<td class="price"><span>￥<?php echo ($itemInfo['newPrice']); ?></span></td>
				</tr>
				<tr class="benefits">
					<td>促销信息：</td>
					<td class="sale"><?php echo ($freepostageLimitMoney); ?></td>
				</tr>
				<!-- <tr class="benefits">
					<td>领&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;券：</td>
					<td>
						<div class="coupon coupon1"></div>
						<div class="coupon coupon2"></div>
						<div class="coupon coupon3"></div>
					</td>
				</tr> -->
				<tr>
					<td>品&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;牌：</td>
					<td class="band"><span><?php echo ($resBrandInfo['brand_name']); ?></span></td>
				</tr>
				<tr>
					<td>配&nbsp;&nbsp;送&nbsp;至：</td>
					<td class="address">
						<ul id="list1" style="margin:10px auto 0 auto;">
							<li id="summary-stock">
								<div class="dd">
									<div id="store-selector">
										<div class="text"><div></div><b></b></div>                   
										<div onclick="$('#store-selector').removeClass('hover')" class="close"></div>
									</div><!--store-selector end-->
									<div id="store-prompt"><strong></strong></div><!--store-prompt end-->
								</div>
							</li>
						</ul>
						<span id="instock">有货</span>
					</td>
				</tr>
				<tr class="choose">
					<td class="products">选择产品：</td>
					<td id="size">
						<?php if(is_array($sKuList)): $i = 0; $__LIST__ = $sKuList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$skvo): $mod = ($i % 2 );++$i; if(empty($skvo['spec_info'])): ?><div class="">默认规格</div>
							<?php else: ?>
								<div>
									<input type="hidden" class="skuId" name="sku_id" value="<?php echo ($skvo['sku_id']); ?>" >
									<input type="hidden" class="skuNum" name="sku_num" value="<?php echo (getItemSku($skvo['sku_id'])); ?>">
									<?php echo ($skvo['spec_info']); ?>
								</div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</td>
				</tr>
			</table>
			<div class="buy">
				<div id="number">
					<input type="text" value="1">
					<div id="btn_add"></div>
					<div id="btn_reduce"></div>
				</div>
				<div id="add_to_cart" class=""></div>
				<div id="outofstock"></div>
				<div id="add_success">
					<div id="closebtn"></div>
					<p>商品成功加入购物车</p>
					<a href="">继续购物</a>
					<a href="" class="checkmycart">我的购物车</a>
				</div>
			</div>
		</div>
		<!-- 右边商家 -->
		<div class="seller">
			<div class="logo3"></div>
			<div class="seller_name">
				<span class="bold">卖家：</span>
				<a href=""><?php $shopInfo = getShopName($itemInfo['shop_id']); echo $shopInfo['shop_name'];?></a>
				<?php if($itemInfo[shop_id] != 10): ?><span class="ours"><?php echo ($shop_type); ?></span><?php endif; ?>
			</div>
			<div class="contact">
				<span class="bold">联系客服：</span>
				<a href="tencent://message/?uin=<?php echo ($shopInfo['qq']); ?>&Site=网站&Menu=yes">
					<?php echo $shopInfo['shopuser_name']; ?>
				</a>
			</div>
			<div class="scores">
				<ul>
					<li class="bold">评分明细</li>
					<li>商品评分：<span>4.8</span>分</li>
					<li>服务评分：<span>4.8</span>分</li>
					<li>配送评分：<span>5.0</span>分</li>
				</ul>
				<ul class="compare">
					<li class="bold">与行业相比</li>
					<li><span>83.63%</span></li>
					<li><span>83.63%</span></li>
					<li><span>100%</span></li>
				</ul>
			</div>
			<div class="store_button">
				<a href="" class="button1">进店逛逛</a>
				<?php if($uid == 0): ?><a href="" class="button2 shopFav">收藏店铺</a>
				<?php else: ?>
					<input type="hidden" name="shopFav" class="shop-fav" value="<?php echo (getShopFav($itemInfo['shop_id'],$uid)); ?>">
					<a href="javascript:void(0);" class="button2 doShopFav">收藏店铺</a><?php endif; ?>
			</div>
		</div>
	</div>

	<!-- 推荐和评价 -->
	<div class="comments">
		<div class="wrap">
			<!-- 推荐 -->
			<div class="recommend">
				<div>
					<ul class="title" id="rec_title">
						<li class="current">相关推荐</li>
						<!-- <li>配件推荐</li> -->
					</ul>
				</div>
				<div id="rec_info">
					<ul class="rec_preview current">
						<?php if(is_array($itemList)): $i = 0; $__LIST__ = $itemList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ilvo): $mod = ($i % 2 );++$i;?><li>
							<a class="rec_pics" href="/shop.php/Info/index/itemId/<?php echo ($ilvo["item_id"]); ?>">
								<img src="<?php echo ($ilvo["image_default_id"]); ?>" width="113" height="113">
							</a>
							<a class="rec_goods_name" href="/shop.php/Info/index/itemId/<?php echo ($ilvo["item_id"]); ?>"><?php echo ($ilvo["title"]); ?></a>
							<p class="rec_price">￥<?php echo sprintf("%.2f",$ilvo['price']); ?></p>
						</li><?php endforeach; endif; else: echo "" ;endif; ?>						
					</ul>
					<!-- <ul class="rec_preview">
						<li>
							<a class="rec_pics" href=""><img src="" width="113" height="113"></a>
							<a class="rec_goods_name" href="">幻想（i-mu）二合一充电数据线电源线 适用苹果iphone6/plus/安卓三星小米华为 Lightning/Microuse 高速蓝色</a>
							<p class="rec_price">￥239.00</p>
						</li>
					</ul> -->
				</div>
			</div>
			<!-- 评价左部 -->
			<div class="comments_left">
				<div class="store">
					<div class="title_left">
						<a href=""><?php echo ($shopInfo['shop_name']); ?></a>
						<a class="contact_qq" href="tencent://message/?uin=<?php echo ($shopInfo['qq']); ?>&Site=网站&Menu=yes"></a>
					</div>
					<div class="store_button">
						<a class="button1" href="">进店逛逛</a>
						<?php if($uid == 0): ?><a href="" class="button2 shopFav">收藏店铺</a>
						<?php else: ?>
							<input type="hidden" name="shopFav" class="shop-fav" value="<?php echo (getShopFav($itemInfo['shop_id'],$uid)); ?>">
							<a href="javascript:void(0);" class="button2 doShopFav">收藏店铺</a><?php endif; ?>
					</div>
				</div>
				<div class="search_inside">
					<div class="title_left">店内搜索</div>
					<input type="text" placeholder="关键字">
					<a href=""></a>
				</div>
				<div>
					<div class="title_left">相关分类</div>
					<div class="category_list">
						<ul>
							<?php if(is_array($catInfo)): $i = 0; $__LIST__ = $catInfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$civo): $mod = ($i % 2 );++$i;?><li><a href=""><?php echo ($civo['cat_name']); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
					</div>
				</div>
				<div>
					<div class="title_left">看了又看</div>
					<div class="see_more">
						<ul>
							<?php if(is_array($browList)): $i = 0; $__LIST__ = $browList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$blvo): $mod = ($i % 2 );++$i;?><li>
									<a class="rec_pics" href="/shop.php/Info/index/itemId/<?php echo ($blvo["item_id"]); ?>">
										<img src="<?php echo ($blvo["img"]); ?>" width="113" height="113">
									</a>
									<a class="rec_goods_name" href="/shop.php/Info/index/itemId/<?php echo ($blvo["item_id"]); ?>"><?php echo ($blvo["title"]); ?></a>
									<p class="rec_price">￥<?php echo sprintf("%.2f",$blvo['price']); ?></p>
								</li><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
					</div>
				</div>
			</div>
			<!-- 评价右部 -->
			<div class="comments_right">
				<div>
					<ul class="title" id="com_title">
						<li class="current">商品详情</li>
						<li class="traderate">用户评价</li>
						<li class="consul">商品咨询</li>
					</ul>
				</div>

				<div id="com_info">
					<!-- 商品详情 -->
					<div id="goods_details" class="current">
						<?php echo ($itemDesc['pc_desc']); ?>
					</div>

					<!-- 用户评价 -->
					<div class="customer_comments">
						<div class="comments_general">
							<div class="rate_score">
								<div><span>100</span>%</div>
								<p>好评率</p>
							</div>
							<div class="progress_bar">
								<p>好评 <progress value="90" max="100"></progress> 90%</p>
								<p>中评 <progress value="10" max="100"></progress> 10%</p>
								<p>差评 <progress value="0" max="100"></progress> 0%</p>
							</div>
							<div class="impression">
								<p>买家印象</p>
								<ul>
									<li>价格实惠</li>
									<li>质量好</li>
									<li>发货快</li>
									<li>包装不错</li>
									<li>服务态度好</li>
									<li>质量一般</li>
								</ul>
							</div>
						</div>
						<div id="all_comments">
							<ul>
								<?php $itemCount = getTableRow('sysitem_item_count','item_id',$itemInfo['item_id']);?>
								<li class="current rateAll">全部(<?php echo ($itemCount['rate_count']); ?>)</li>
								<li class="rateGood">好评(<?php echo ($itemCount['rate_good_count']); ?>)</li>
								<li class="rateNeutral">中评(<?php echo ($itemCount['rate_neutral_count']); ?>)</li>
								<li class="rateBad">差评(<?php echo ($itemCount['rate_bad_count']); ?>)</li>
							</ul>
						</div>
						<input type="hidden" id="newpage" value="1"> 
						<input type="hidden" id="rateVal" value="rateAll"> 
						<input type="hidden" id="rate" value="rateAll"> 
						<div id="comments_content">
							<!-- 全部 -->
							<ul class="current rateAll">								
							</ul>
							<!-- 好评 -->
							<ul class="rateGood">						
							</ul>
							<!-- 中评 -->
							<ul class="rateNeutral">
							</ul>
							<!-- 差评 -->
							<ul class="rateBad">
							</ul>
						</div>
					</div>

					<!-- 商品咨询 -->
					<div class="consult">
						<div id="all_Q">
							<ul>
								<li class="current consulAll">全部咨询(<?php echo (getConsulCount($itemInfo['item_id'],'all')); ?>)</li>
								<li class="consulItem">商品咨询(<?php echo (getConsulCount($itemInfo['item_id'],'item')); ?>)</li>
								<li class="consulStore">库存及配送(<?php echo (getConsulCount($itemInfo['item_id'],'store')); ?>)</li>
								<li class="consulPay">支付问题(<?php echo (getConsulCount($itemInfo['item_id'],'payment')); ?>)</li>
								<li class="consulInv">发票及保修(<?php echo (getConsulCount($itemInfo['item_id'],'invoice')); ?>)</li>
							</ul>
						</div>

						<input type="hidden" id="consulpage" value="1"> 
						<input type="hidden" id="consulVal" value="consulAll"> 
						<input type="hidden" id="consul" value="consulAll"> 
						<div id="all_Q_context">
							<!--全部-->
							<ul class="consult_text current consulAll">								
							</ul>
							<!--商品-->
							<ul class="consult_text consulItem">
							</ul>
							<!--库存及配送-->
							<ul class="consult_text consulStore">
							</ul>
							<!--支付问题-->
							<ul class="consult_text consulPay">
							</ul>
							<!--发票及保修-->
							<ul class="consult_text consulInv">
							</ul>
						</div>
						<form class="input_consult">
							<table>
								<tr>
									<td>声&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</td>
									<td>您可以在购买前对产品包装、颜色、运输、库存等方面进行咨询，我们有专人进行回复！因厂家随时会更新一些产品的包装、颜色、产地等参数，所以该回复仅在当时对提问者有效，其他网友仅供参考！<br>咨询回复的工作时间为&nbsp;周一至周五，9:00至18:00，请耐心等待工作人员回复。
									</td>
								</tr>
								<tr>
									<td>咨询类型：</td>
									<td id="Qtype">
										<label class="current"><input type="radio" class="check" name="question_type" value="item" checked="true">商品咨询</label>
										<label><input type="radio" class="check" name="question_type" value="store" checked="false">库存及配送</label>
										<label><input type="radio" class="check" name="question_type" value="payment" checked="false">支付问题</label>
										<label><input type="radio" class="check" name="question_type" value="invoice" checked="false">发票及保修</label>
									</td>
								</tr>
								<tr class="input_textarea">
									<td>咨询内容：</td>
									<td>
										<textarea id="consul-centent" name="centent" placeholder="请输入您要咨询的问题(5-200字)"></textarea>
										<p id="char_left">还可以输入<span>200</span>字</p>
									</td>
								</tr>
								<tr id="phone">
									<td>联系方式：</td>
									<td>
										<input type="text" id="phonenum" placeholder="请输入您的手机号码">
										<!-- <label class="noname"><input type="checkbox" name="anonymous" value="" checked="false">匿名</label> -->
									</td>
								</tr>
							</table>
							<input type="input" id="submitQ" value="提交" >
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>


	<script type="text/javascript" src="/public/js/event.js"></script>
	<script type="text/javascript" src="/public/js/details.js"></script>
	<script src="/public/js/location.js"></script>
	<!--底部-->
	 <!--底部-->
    <div id="footer">
       <div class="footerdimension">
           <div class="footertop">
               <div class="footertopLeft">
                   <ul>
                      <li class="footerUser">导航</li>
                      <li class="footerMenu"><a href="#">礼舍简介</a></li>
                      <li class="footerMenu"><a href="#">礼舍做什么</a></li>
                      <li class="footerMenu"><a href="#">伙伴说什么</a></li>
                      <li class="footerMenu"><a href="#">心意商城</a></li>
                   </ul>
                   <ul>
                      <li class="footerUser">咨询</li>
                      <li class="footerMenu"><a href="#">服务条款</a></li>
                      <li class="footerMenu"><a href="#">礼舍动态</a></li>
                      <li class="footerMenu"><a href="#">人员招聘</a></li>
                   </ul>
                   <ul>
                      <li class="footerUser">礼舍帮帮</li>
                      <li class="footerMenu"><a href="#">礼舍教学操作指南</a></li>
                      <li class="footerMenu"><a href="#">礼舍使用全攻略</a></li>
                      <li class="footerMenu"><a href="#">常见问题</a></li>
                      <li class="footerMenu"><a href="#">联系我们</a></li>
                   </ul>
                   <ul>
                      <li class="footerUser">加入我们</li>
                      <li class="footerMenu"><a href="#">供应商入驻</a></li>
                      <li class="footerMenu"><a href="#">企业入驻</a></li>
                      <li class="footerMenu"><a href="#">合作伙伴</a></li>
                   </ul>
               </div>
               <div class="footertopRight">
                   <ul>
                     <li class="footerPhone"><img src="/public/images/indexPhone.png">400-883-9916 </li>
                     <li class="footerConnection">联系我们</li>
                   </ul>
                   <ul>
                     <li><img src="/public/images/IndexWx.png"></li>
                   </ul> 
               </div>   
           </div>
           <div style="clear:both;"></div>
           <div class="footerbottom">
              <ul class="footerbottomLeft">
                <li>© 2005-2016 礼舍网 版权所有，并保留所有权利。 ICP备案证书号:粤ICP备15033641号-1</li>
                <li>All rights Reserved.</li>
              </ul>
              <ul class="footerbottomRight">
                <li class="footerbottomRightPhone">全国客服热线：400-883-9916 </li>
                <li class="footerbottomRightTime">周一至周五 9:00-18:00</li>
              </ul>
           </div>
           <div style="clear:both;"></div>
       </div>    
    </div>

	<script type="text/javascript">
		$(document).ready(function(){ 
			var shopId = <?php echo ($itemInfo['shop_id']); ?>;
			var itemStatus = $("#itemStatus").val();
			//加载的时候检查自营商品是否有货
			if (shopId != 10) {
				var skuNum = $("#size").find("div:first input.skuNum").val();
				checkedInven(skuNum);
			};
			if (itemStatus == 0) {
				$('#add_to_cart').hide();
				$("#outofstock").show();				
			};
			var userFav = $(".user-fav").val();
			if (userFav == 1 ) {
				$(".doUserFav").text("取消收藏");
			}else{
				$(".doUserFav").text("收藏商品");
			}
			var shopFav = $(".shop-fav").val();
			if (shopFav == 1) {
				$(".doShopFav").text("取消收藏");
			}else{
				$(".doShopFav").text("收藏店铺");
			}
			
		});
		//选择类型判断库存
		$("#size div").click(function(){
			var athis = $(this);
			var skuNum = athis.children("input.skuNum").val();
			checkedInven(skuNum);
		});	

		//候检自营商品是否有库存
		function checkedInven(skuNum){
			$("#number input").val(1);
			if (skuNum == 0) {
				$('#add_to_cart').hide();
				$("#outofstock").show();
			}else{
				$('#add_to_cart').show();
				$("#outofstock").hide();				
			}
		};

	//加入购物车
    $("#add_to_cart").click(function(){
        //商品id
        var itemId = <?php echo ($itemInfo["item_id"]); ?>;//商品id
        //库存id
        var skuId = $("#size").children(".current").children("input.skuId").val();
        //购买数量
        var quantity = $("#number").children("input")[0].value;
        $.ajax({
            type:"POST",
            url:"/shop.php/Info/addItemCart",
            data:{itemId:itemId,skuId:skuId,quantity:quantity},
            dataType:'text',
            success:function(data){
            	var res = eval(data);
                if (res[0] == 1) {
                    $("#add_success").fadeIn(300);
                }else{
                    alert(res[1]);
                }
            }
        });
    });

    //收藏商品
    $(".collection").click(function(){
    	var itemId = <?php echo ($itemInfo["item_id"]); ?>;//商品id
    	$.ajax({
            type:"POST",
            url:"/shop.php/Info/favGoods",
            data:{itemId:itemId},
            dataType:'text',
            success:function(data){
            	var res = eval(data);
                if (res[0] == 1) {
                	$(".doUserFav").text(res[2]);
                    alert(res[1]);
                }else{
                	$(".doUserFav").text(res[2]);
                    alert(res[1]);
                }
            }
        });
    });

    //收藏店铺
    $(".doShopFav").click(function(){
    	var shopId = <?php echo ($itemInfo["shop_id"]); ?>;
    	$.ajax({
            type:"POST",
            url:"/shop.php/Info/favShop",
            data:{shopId:shopId},
            dataType:'text',
            success:function(data){
            	var res = eval(data);
                if (res[0] == 1) {
                	$(".doShopFav").text(res[2]);
                    alert(res[1]);
                }else{
                	$(".doShopFav").text(res[2]);
                    alert(res[1]);
                }
            }
        });
    });

    //商品评价
    $(".traderate").click(function(){
		pageAjax();	
	});
	$("li.rateAll").click(function(){
		$("#rate").val("rateAll");
		$("#rateVal").val("rateAll");
		$("#newpage").val(1);
		pageAjax();
	});
	$("li.rateGood").click(function(){
		$("#rate").val("rateGood");
		$("#rateVal").val("good");
		$("#newpage").val(1);
		pageAjax();
	});
	$("li.rateNeutral").click(function(){
		$("#rate").val("rateNeutral");
		$("#rateVal").val("neutral");
		$("#newpage").val(1);
		pageAjax();
	});
	$("li.rateBad").click(function(){
		$("#rate").val("rateBad");
		$("#rateVal").val("bad");
		$("#newpage").val(1);
		pageAjax();
	});
	$("#comments_content").delegate('.pages div a.num','click',function(){ 
      	//当前页
      	var nowpage = $(this).children("span").text(); 
        $("#newpage").val(nowpage);    
        pageAjax();
    });
    //下一页
    $("#comments_content").delegate('.pages div a.next','click',function(){
	    var nowpage = $("#newpage").val();
	    $("#newpage").val(nowpage*1+1);
	    pageAjax();
    });
    //上一页
    $("#comments_content").delegate('.pages div .prev','click',function(){
      	var nowpage = $("#newpage").val();
      	$("#newpage").val(nowpage*1-1);
      	pageAjax();
    });
    function pageAjax(){
    	var itemId = <?php echo ($itemInfo['item_id']); ?>;
    	var newPage = $("#newPage").val();
    	var rate = $("#rate").val();
    	var rateVal = $("#rateVal").val();
    	$.ajax({
            type:"GET",
            url:"/shop.php/Info/traderateList",
            data:{itemId:itemId,rateVal:rateVal,p:newPage},
            dataType:'text',
            success:function(data){
            	$("#comments_content ."+rate+" li").remove();
            	$("#comments_content ."+rate).html(data);           
            }
        });
    }

    //商品咨询
    $(".consul").click(function(){
		consulAjax();	
	});
	$("li.consulAll").click(function(){
		$("#consul").val("consulAll");
		$("#consulVal").val("consulAll");
		$("#consulpage").val(1);
		consulAjax();
	});
	$("li.consulItem").click(function(){
		$("#consul").val("consulItem");
		$("#consulVal").val("item");
		$("#consulpage").val(1);
		consulAjax();
	});
	$("li.consulStore").click(function(){
		$("#consul").val("consulStore");
		$("#consulVal").val("store");
		$("#consulpage").val(1);
		consulAjax();
	});
	$("li.consulPay").click(function(){
		$("#consul").val("consulPay");
		$("#consulVal").val("payment");
		$("#consulpage").val(1);
		consulAjax();
	});
	$("li.consulInv").click(function(){
		$("#consul").val("consulInv");
		$("#consulVal").val("invoice");
		$("#consulpage").val(1);
		consulAjax();
	});
    $("#all_Q_context").delegate('ul .pages div a.num','click',function(){ 
      	//当前页
      	var nowpage = $(this).children("span").text(); 
        $("#consulpage").val(nowpage);    
        consulAjax();
    });
    //下一页
    $("#all_Q_context").delegate('ul .pages div a.next','click',function(){
	    var nowpage = $("#consulpage").val();
	    $("#consulpage").val(nowpage*1+1);
	    consulAjax();
    });
    //上一页
    $("#all_Q_context").delegate('ul .pages div .prev','click',function(){
      	var nowpage = $("#consulpage").val();
      	$("#consulpage").val(nowpage*1-1);
      	consulAjax();
    });
    function consulAjax(){
    	var itemId = <?php echo ($itemInfo['item_id']); ?>;
    	var consulPage = $("#consulpage").val();
    	var consul = $("#consul").val();
    	var consulVal = $("#consulVal").val();
    	$.ajax({
            type:"GET",
            url:"/shop.php/Info/consulList",
            data:{itemId:itemId,consulVal:consulVal,p:consulPage},
            dataType:'text',
            success:function(data){
            	$("#all_Q_context ."+consul+" li").remove();
            	$("#all_Q_context ."+consul).html(data);         
            }
        });
    };

    //咨询
    $("#submitQ").click(function(){
    	var consulType = $('#Qtype label input[name="question_type"]:checked ').val();
    	var centent = $("#consul-centent").val();
    	var mobile = $("#phonenum").val();
    	var itemId = <?php echo ($itemInfo['item_id']); ?>;
    	var itemName = "<?php echo ($itemInfo['title']); ?>";
    	var shopId = <?php echo ($itemInfo['shop_id']); ?>;
    	var shopName = "<?php echo ($shopInfo['shop_name']); ?>";
    	if (consulType == '') {
    		alert("请选择问题类型！");
    		return false;
    	};
    	if (centent == '') {
    		alert("请输入问题描述！");
    		return false;    		
    	};
    	if (mobile == '') {
    		alert("请填写您的电话！");
    		return false;    		
    	};
    	if (itemId == '') {
    		alert("商品id不能为空！");
    		return false;     		
    	};
    	if (shopId == '') {
    		alert("店铺id不能为空！");
    		return false;     		
    	};
    	$.ajax({
            type:"POST",
            url:"/shop.php/Info/consulAdd",
            data:{itemId:itemId,itemName:itemName,shopId:shopId,shopName:shopName,mobile:mobile,centent:centent,consulType:consulType},
            dataType:'text',
            success:function(data){
            	var res = eval(data);
            	if (res[0] == 1) {
            		alert(res[1]);
            		$("#consul-centent").val("");
            		$("#phonenum").val("");
            	}else{
            		alert(res[1]);
            	}
            }
        });
    });
	</script>
</body>
</html>