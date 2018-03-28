window.onload = function(){
	

	var category = window.location.hash.substring(1,9);
	var anchor = window.location.hash.substring(9);
	if(category == "companys"){
		$(".companys").addClass('current');
		$(".companys .pages").eq(anchor).addClass('current');
		$(".companys>li").eq(anchor).addClass('active');
		$("#companys").addClass('active');
	}
	else if(category == "customer"){
		$(".customers").addClass('current');
		$(".customers .pages").eq(anchor).addClass('current');
		$(".customers>li").eq(anchor).addClass('active');
		$("#customers").addClass('active');
	}else{
		$(".companys").addClass('current');
		$(".companys .pages").eq(0).addClass('current');
		$(".companys>li").eq(0).addClass('active');
		$("#companys").addClass('active');
	}


	//个人企业选项卡
	$(".user_type li").each(function(){
		$(this).click(function(){
			$(".user_type li").removeClass('active');
			$(this).addClass('active');
			$(".content>div .pages").removeClass('current');
			$(".category>ul>li").removeClass('active');
		});
	});
	$("#companys").click(function(){
		$(".content>div").removeClass('current');
		$(".content>div .pages").removeClass('current');
		$(".category>ul").removeClass('current');
		$(".tags p").removeClass('current');
		$(".companys").addClass('current');
		$(".companys li").eq(0).addClass('active');
		$(".companys .pages").eq(0).addClass('current');
		$(".tags .companys").addClass('current'); 
	});
	$("#customers").click(function(){
		$(".content>div").removeClass('current');
		$(".content>div .pages").removeClass('current');
		$(".category>ul").removeClass('current');
		$(".tags p").removeClass('current');
		$(".customers").addClass('current');
		$(".customers li").eq(0).addClass('active');
		$(".customers .pages").eq(0).addClass('current');
		$(".tags .customers").addClass('current');
	});

	//四项选择卡
	$(".category ul li").each(function(){
		$(this).click(function(){
			$(".category ul li").removeClass('active');
			$(this).addClass('active');
			$(".content>.current .pages").removeClass('current');
			$(".content>.current .pages").eq($(this).index()).addClass('current');
		});
	});


}