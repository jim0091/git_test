window.onload = function(){
	var curIndex = 0;
	$(".imgList li:gt(0)").hide();

	function autoPlay(){
		if(curIndex < $(".imgList li").length-1){
			curIndex ++;
		}
		else{
			curIndex = 0;
		}
		changeTo(curIndex);
	}
	var autoChange = setInterval(autoPlay, 3000);

	$(".banner").mouseover(function(){
		clearInterval(autoChange);
	});
	$(".banner").mouseout(function(){
		autoChange = setInterval(autoPlay, 3000);
	});

	$(".indexList").find("li").each(function(item){
		$(this).click(function(){
			changeTo(item);
			curIndex = item;  
		});
	});

	function changeTo(num){
		$(".imgList li").eq(num).fadeIn("slow").siblings("li").fadeOut("slow");
		$(".indexList li").eq(num).addClass("indexCur").siblings("li").removeClass("indexCur");
	}
}

