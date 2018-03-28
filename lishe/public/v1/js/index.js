$(window).ready(function() {
	//解决方案滑过样式
	$(".Inservices>ul").mouseenter(function(){
		$(this).find(".Inbtn a").css({"background-color":"#FF4138", "color":"#FFF"});
	});
	$(".Inservices>ul").mouseleave(function(){
		$(this).find(".Inbtn a").css({"background-color":"#FFF", "color":"#FF4138"});
	});
	$(".Inpt").parent().mouseenter(function(){
		$(this).find(".Inpt a").css("background-image", "url(" + "/v1/images/inndexflh.png" + ")");
	});
	$(".Inpt").parent().mouseleave(function(){
		$(this).find(".Inpt a").css("background-image", "url(" + "/v1/images/inndexfl.png" + ")");
	});
	$(".Inpt2").parent().mouseenter(function(){
		$(this).find(".Inpt2 a").css("background-image", "url(" + "/v1/images/inndexfl2h.png" + ")");
	});
	$(".Inpt2").parent().mouseleave(function(){
		$(this).find(".Inpt2 a").css("background-image", "url(" + "/v1/images/inndexfl2.png" + ")");
	});
	$(".Inpt3").parent().mouseenter(function(){
		$(this).find(".Inpt3 a").css("background-image", "url(" + "/v1/images/inndexfl3h.png" + ")");
	});
	$(".Inpt3").parent().mouseleave(function(){
		$(this).find(".Inpt3 a").css("background-image", "url(" + "/v1/images/inndexfl3.png" + ")");
	});

	//合作伙伴动画特效
	$(window).scroll(function(){
		if($(".shortages").offset().top - $(document).scrollTop() < 200){
			$(".partner1").css({"top": "30px", "opacity":"1"});
			$(".partner2").css({"left": "10px", "opacity":"1"});
			$(".partner3").css({"left": "790px", "opacity":"1"});
			$(".partner4").css({"left": "70px", "opacity":"1"});
			$(".partner5").css({"left": "720px", "opacity":"1"});
			$(".slogan1").css({"left": "50px", "opacity":"1"});
			$(".slogan2").css({"left": "730px", "opacity":"1"});
			$(".slogan3").css({"top": "680px", "opacity":"1"});
		}
	});
});