$(function(){
	$(".selectBox").click(function(){
		$(".selectBox").removeClass("ckdBox").addClass("addAddrBox");
		$(this).removeClass("addAddrBox").addClass("ckdBox");
		var aid=$(this).find("input[name='addressId[]']").val();
		$('#aid').val(aid);
	});
});
//用户手动选择默认地址

//默认地址选中
$(document).ready(function(){
	$("ul").each(function(){
		if($(this).find(".default").val() == 1){
			$(this).parent().find(".setDefAddr").removeClass("ckdAddr").addClass("defAddr");
			$(this).parent().find(".defAddr").html("<span></span>默认地址");
		}
	});
});

//判断用户是否选择默认地址
$(".backlast").click(function(){
	 $.post("/business/wshop.php/Order/defaultAddrInfo","",function(msg){
	     if(msg =='0'){
           alert('您还没有选择默认的收货地址');
	     }
	 });
	 
});

