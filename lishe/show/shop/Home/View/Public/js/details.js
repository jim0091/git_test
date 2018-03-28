window.onload = function(){

	//商品左侧导航条
	$(".leftbar").mouseenter(function(){
		$("#channels").show();
	});
	$(".leftbar").mouseleave(function(){
		$("#channels").hide();
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

	//商品图片预览
	var pics_small = document.getElementById("preview_small").getElementsByTagName("img");
	var preview_big = document.getElementById("preview_big");
	if(pics_small[0]){
		pics_small[0].className += " current";
		preview_big.style.backgroundImage = "url("+pics_small[0].src+")";
		for (var i=0; i<pics_small.length; i++){
			pics_small[i].index = i;
			pics_small[i].onmouseover = function(){
				for (var n=0; n<pics_small.length; n++){
					pics_small[n].className = "pics";
					preview_big.style.backgroundImage = "url("+""+")";
				}
				this.className += " current";
				preview_big.style.backgroundImage = "url("+this.src+")";
			}
		}
	}

	//SKU显示/隐藏控制
	$(".skuShow").each(function(){
		var stime = $(this).attr("data-stime");
		var etime = $(this).attr("data-etime");
		var nowtime = Date.parse(new Date())/1000;	
		if (stime != '' && stime != 0) {
			if (etime != '' && etime != 0) {					
				if (stime > nowtime || nowtime > etime) {
					$(this).remove();
				};						
			}else{
				if (stime > nowtime) {
					$(this).remove();
				};
			}
		}else{
			if (etime != '' && etime != 0) {
				if (nowtime > etime) {
					$(this).remove();
				};
			}
		}

	});	
	
	var sizes = $("#size").find("div");
	var defaultSkuId = $("#defaultSkuId").val();
	if(defaultSkuId > 1){
		$("#sku_"+defaultSkuId).addClass('current');
		$("#sku_"+defaultSkuId).click();
	}else{
		$("#size div:first-child").addClass('current');
		$("#size div:first-child").click();
	}
	
	sku_num2 = $("#size div:first-child").find(".skuNum").val();
	sizes.each(function(){
		$(this).click(function(){
			sizes.removeClass('current');
			$(this).addClass('current');
			sku_num2 = $(this).find(".skuNum").val();
		});
	});

	var shopId = $("#shopId").val();
	//商品数量选择
	/*var number = document.getElementById("number").getElementsByTagName("input")[0];*/
	var number = $("#number input");
	var number_plus = document.getElementById("btn_add");
	var number_minus = document.getElementById("btn_reduce");
	var item_id = $("#item_id").val();
    var jd_ids = $("#summary-stock .text div").attr("title");

	number_plus.onclick = function(){
		
		var itemNum = parseInt(number.val());
		var currentSku = $("#size").children(".current");
	    //检查团购限制
	    var skuType = currentSku.attr('sku-type');
	    var acid = currentSku.attr('acid');
	    if (skuType == 4) {
	    	var maxGBTimes = $(".groupBuy.active").eq(0).attr('max-buytimes');
	    	if(maxGBTimes != 0 && itemNum >= maxGBTimes) {
	    		$(".topLoading").hide();
				divPrompt("超过团购购买数限制 ！");	
				return false;
	    	}
	    }else if(skuType == 1){
	    	var maxSBTimes = $("#sku-info-s-"+acid).attr('max-buytimes');
	    	if(maxSBTimes != 0 && itemNum >= maxSBTimes) {
	    		$(".topLoading").hide();
				divPrompt("超过秒杀购买数限制 ！");	
				return false;
	    	}
	    }
		
        $(".topLoading").show();
        //京东商品
		if (shopId == 10) {
			$.ajax({
				url:"http://www.lishe.cn/api.php/Jd/checkJdStock",
				data:{item_id:item_id,jd_ids:jd_ids,num:itemNum},
				contentType:'text/plain',
				type:"POST",
				dataType:"text",
				success:function(retData){
					if(retData != 33){
	                	$(".topLoading").hide();
						divPrompt("超过购买数量！");	
						return false;
					}else{
						itemNum += 1;				
						number.val(itemNum);
	                	$(".topLoading").hide(); 					
					}
				}
			});
		//自营商品
		}else {
			if (sku_num2 <= itemNum) {
                $(".topLoading").hide();
				divPrompt("超过购买数量！");	
				return false;
			};			
			itemNum += 1;		
			number.val(itemNum);	
            $(".topLoading").hide();
		}	
	}
	number_minus.onclick = function(){
		if (parseInt(number.val()) > 1){
			itemNum = parseInt(number.val())-1;		
			number.val(itemNum);
		}
	}

//选择类型判断库存
$("#size div:first-child").trigger("click");
$("#size div").click(function(){
	var itemStatus = $("#itemStatus").val();
	if (itemStatus == 0) {
		return false;
	};
	var athis = $(this);
	var skuNum = athis.children("input.skuNum").val();
	var skuPrice = athis.children("input.skuPrice").val();
	var newSkuPrice = parseFloat(skuPrice).toFixed(2);
	var integral = Math.round(newSkuPrice*100);
	$(".itemBalance").text(integral);
	$(".itemPrice").text("￥"+newSkuPrice);
	checkedInven(skuNum);			
});	

	//推荐选项卡
	
	$("#rec_title li").eq(0).addClass('current');
	$("#rec_info ul").eq(0).addClass('current');
	$("#rec_title li").each(function(){
		$(this).click(function(){
			$("#rec_title li").removeClass('current');
			$("#rec_info ul").removeClass('current');
			$(this).addClass('current');
			$("#rec_info ul").eq($(this).index()).addClass('current');
		});
	});

	//评价选项卡
	$("#com_title li").eq(0).addClass('current');
	$("#com_info>div").eq(0).addClass('current');
	$("#com_title li").each(function(){
		$(this).click(function(){
			$("#com_title li").removeClass('current');
			$("#com_info>div").removeClass('current');
			$(this).addClass('current');
			$("#com_info>div").eq($(this).index()).addClass('current');
		});
	});

	//好中差评价选项卡
	$("#all_comments li").eq(0).addClass('current');
	$("#comments_content ul").eq(0).addClass('current');
	$("#all_comments li").each(function(){
		$(this).click(function(){
			$("#all_comments li").removeClass('current');
			$("#comments_content ul").removeClass('current');
			$(this).addClass('current');
			$("#comments_content ul").eq($(this).index()).addClass('current');
		});
	});

	//商品咨询选项卡
	$("#all_Q li").eq(0).addClass('current');
	$("#all_Q_context ul").eq(0).addClass('current');
	$("#all_Q li").each(function(){
		$(this).click(function(){
			$("#all_Q li").removeClass('current');
			$("#all_Q_context ul").removeClass('current');
			$(this).addClass('current');
			$("#all_Q_context ul").eq($(this).index()).addClass('current');
		});
	});

	//商品咨询剩余字数
	var text_input = document.getElementsByTagName("textarea")[0];
	var char_left = document.getElementById("char_left").getElementsByTagName("span")[0];
	text_input.onkeyup = function(){
		char_left.innerHTML = 200 - text_input.value.length;
	}

	//咨询类型选择
	var Q_checks = document.querySelectorAll(".consult .check");
	var Q_checks_label = document.querySelectorAll("#Qtype label");
	for (var i=0; i<Q_checks.length; i++){
		Q_checks[i].index = i;
		Q_checks[i].onclick = function(){
			for (var n=0; n<Q_checks.length; n++){
				Q_checks[n].setAttribute("checked", "false");
				Q_checks_label[n].className = "";
			}
			Q_checks_label[this.index].className = "current";
			this.setAttribute("checked", "true");
		}
	}


	//电话号码仅数字
	var phonenum = document.getElementById("phonenum");
	phonenum.onkeyup = function(){
		this.value = this.value.replace(/[^0-9-]+/,'');
	}

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

	
	$("#closebtn").click(function(){
		$("#add_success").fadeOut(300);
	});

	//限制推荐商品名字数
	$(".rec_goods_name").each(function(){
		var maxwidth = 24;
		if($(this).text().length>maxwidth){
			$(this).text($(this).text().substring(0,maxwidth));
			$(this).html($(this).html()+"...");
		}
	});
}

	//商品种类过多显示隐藏
	var tdsize = $("#size");
	var moresize = $("#moresize");
	var showmoresize = 0;
	if (tdsize.height() > "184"){
		moresize.show();
		tdsize.css("height", "184px");
		moresize.click(function(){
			if(showmoresize==0){
				tdsize.css("height", "auto");
				showmoresize = 1;
				moresize.html("收起");
			}else{
				tdsize.css("height", "184px");
				showmoresize = 0;
				moresize.html("更多...");
			}
		});
	}else{
		moresize.hide();
	}