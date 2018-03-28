$(function(){

	//左侧导航条碰顶固定
	var navH = $(".help_center").offset().top;
	$(window).scroll(function(){
		var scroH = $(this).scrollTop();
		if(scroH >= navH){
			$(".help_center").css({"position":"fixed", "top":0});
		}else{
			$(".help_center").css({"position":"static"});
		}
	});

	//跳转到指定选项卡
	var anchor = window.location.hash.substring(1);
	$(".left_nav li a").eq(anchor).addClass('current');
	$(".pages").eq(anchor).addClass('current');


	$(".left_nav li").each(function(){
		$(this).click(function(){
			$(window).scrollTop(0);
			$(".left_nav li a").removeClass('current');
			$(this).children('a').addClass('current');
			$(".pages").removeClass('current');
			$(".pages").eq($(this).index()).addClass('current');
		});
	});
});