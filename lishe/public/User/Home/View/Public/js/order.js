window.onload = function(){
	$("all_orders li").eq(0).addClass('current');

	$(".all_orders li").each(function(){
		$(this).click(function(){
			$(".all_orders li").removeClass('current');
			$(this).addClass('current');
		});
	});

	$("#all").click(function(){
		$(".orders").css("display", "block");
	});
	$("#topay").click(function(){
		$(".orders").css("display", "none");
		$(".topay").css("display", "block");
	});
	$("#waitsend").click(function(){
		$(".orders").css("display", "none");
		$(".waitsend").css("display", "block");
	});
	$("#tosign").click(function(){
		$(".orders").css("display", "none");
		$(".tosign").css("display", "block");
	});
	$("#write_review").click(function(){
		$(".orders").css("display", "none");
		$(".write_review").css("display", "block");
	});
	$(".close_window").click(function(){
		$(".shadow").hide();
		$(".thx-window").hide();
	});
}