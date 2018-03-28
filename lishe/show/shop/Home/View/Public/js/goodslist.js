window.onload = function(){

	//商品左侧导航条
	$(".leftbar").mouseenter(function(){
		$("#channels").show();
	});
	$(".leftbar").mouseleave(function(){
		$("#channels").hide();
	});
	var leftnav = $(".leftbar").click(function(){
		$("#channels").show();
	});
	$("#channels>li").mouseover(function(){
		$(this).children("a").css("color", "#FFF");
	});
	$("#channels>li").mouseout(function(){
		$(this).children("a").removeAttr("style");
	});
	$("#channels>li").each(function(){
		$(this).hover(function(){
			$(this).children(".details").fadeToggle(0);
		});
	});

	//赠送下拉菜单
	$(".send").each(function(){
		$(this).mouseenter(function(){
			$(this).children(".sidebar_hide").fadeIn(100);
			$(this).find(".arrow").addClass("rotate_in");
		});
		$(this).mouseleave(function(){
			$(this).children(".sidebar_hide").stop();
			$(this).children(".sidebar_hide").fadeOut(100);
			$(this).find(".arrow").removeClass("rotate_in");
		})
	});

	//点击品牌关键词
	$(".brandKeyword_right ul a").on("click" ,function(){
		$(this).parent("li").parent('ul').find("a").css('color','#000');
		$(this).css('color','red');
		var brandId = $("#brandId").val();
		var selected_keyword = $(this).html();
		var selectedKeywordId =$(this).attr("value");
		$("#brandId").val(selectedKeywordId);
		if (brandId != selectedKeywordId) {
			$(".category ul li").remove('.brandId');
			$(".category ul").append('<li class="brandId"><a href="javascript:;"><span>'+ selected_keyword +'</span></a></li>');
			itemFind(1);
		};
	});
	//点击属性
	// $(".keyword_right ul a").on("click" ,function(){
	// 	$(this).parent("li").parent('ul').find("a").css('color','#000');
	// 	$(this).css('color','red');
	// 	var selected_keyword = $(this).html();		
	// 	var propId = $(this).attr("value");
	// 	var propValueId = $(this).attr("data");
	// 	var selectedKeywordId =$(".category ul li").attr("value");
	// 	$("#propIds").val();
	// 	if (selectedKeywordId != propValueId) {
	// 		var propIds = 
	// 		$(".category ul li").remove('.'+propValueId);
	// 		$(".category ul").append('<li class="propvalue '+propValueId+'" value="'+propValueId+'"><a href="javascript:;"><span>'+ selected_keyword +'</span></a></li>');
	// 		itemFind();
	// 	};
	// });

	$(".category span").live("click", function(){
		$(this).remove();
	});
	

	//排序按钮样式
	$(".sort ul li").eq(0).addClass('current');
	$(".sort ul li").click(function(){
		$(".sort ul li").removeClass('current');
		$(".sort ul li").removeClass('hover');
		$(this).addClass('current');
		if($(".sortbyprice").attr("class").indexOf("current")==-1){
			$(".sortbyprice").css("background-image","url("+ "/shop/Home/View/Public/images/sort_up.png" +")");
		}
	});
	$(".sort ul li").on("mouseover", function(){
		if($(this).attr("class").indexOf("current")==-1){
			$(this).addClass('hover');
		}
	});
	$(".sort ul li").on("mouseout", function(){
		if($(this).attr("class").indexOf("current")==-1){
			$(this).removeClass('hover');
		}
	});

	var flag = false;
	$(".sortbyprice").on("click", function(){
		if(flag == false){
			$(this).css("background-image","url("+ "/shop/Home/View/Public/images/sort_up_white.png" +")");
			itemFind(1,'price','asc');
			flag = true;
		}else{
			$(this).css("background-image","url("+ "/shop/Home/View/Public/images/sort_down.png" +")");
			itemFind(1,'price','desc');
			flag = false;
		}
	});

	//收起展开
	$(".more_keyword").click(function(){
		if($(this).attr("class").indexOf("unfold")==-1){
			$(this).parents(".keyword").css({"height":"45px", "overflow":"hidden"});
			$(this).parents(".keyword_right").children('ul').css({"height":"34px", "overflow":"hidden"});
			$(this).html("更多");
			$(this).removeClass('fold').addClass('unfold');
		}
		else{
			$(this).parents(".keyword").css({"height":"auto", "overflow":"visible"});
			$(this).parents(".keyword_right").children('ul').css({"height":"auto", "overflow":"visible"});
			$(this).html("收起");
			$(this).removeClass('unfold').addClass('fold');
		}
	});

	//商品名字太长显示省略号
	$(".name").each(function(i){
		var divH = $(this).height();
		var $p = $("p", $(this)).eq(0);
		while ($p.outerHeight() > divH){
			$p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
		}
	});
}