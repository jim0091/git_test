window.onload = function(){

	//跳转到指定选项卡
	var anchor = window.location.hash.substring(1);
	$(".user_type li").eq(anchor).addClass('active');
	$(".pages").eq(anchor).addClass('current');
	$("body").scrollTop(0);

	$(".user_type li").each(function(){
		$(this).click(function(){
			$(".user_type li").removeClass('active');
			$(this).addClass('active');
			$(".pages").removeClass('current');
			$(".pages").eq($(this).index()).addClass('current');
		});
	});
}