$(".saveBtn").click(function(){
	if($(".recName").val() == null || $(".recName").val() == ''){
		alert("请填写收件人姓名");
		$(".recName").focus();
		return false;
	} 
	if($("#province").val() == null || $("#province").val() == 0){
    	alert("请选择您所在的省份");
    	$("#province").focus();
    	return false;
	}
	if($("#wxCity").val() == null || $("#wxCity").val() == 0){
        alert("请选择您所在的市");
    	$("#wxCity").focus();
    	return false;
	}
	if($("#selBar").val() == null || $("#selBar").val() == 0){
		alert("请选择你所在的区");
		$("#selBar").focus();
		return false;
	}
	if($("#wxArea option").size() > 0){
    	 if($("#wxArea").val() == null || $("#wxArea").val() == 0){ 
	        alert("请选择你所在的街道");
			$("#wxArea").focus();
			return false;
        }
	}
	if($(".recAddr").val() == null || $(".recAddr").val() == ''){
		alert("请填写详细地址");
		$(".recAddr").focus();
		return false;
	}
	if($(".recCode").val() != '' && $(".recCode").val() != null){
		var zipcode = $(".recCode").val();
		var code = /[1-9]\d{5}(?!\d)/;
		if(!code.test(zipcode)){
			alert("请填写正确的邮编格式，例如518000");
			$(".recCode").focus();
			return false;
		}
	}
	if($(".recTel").val() == null || $(".recTel").val() == ''){
		alert("请填写联系电话");
		$(".recTel").focus();
		return false;
	}else{
		var phone = $(".recTel").val();
		var patrn = /^((\+?86)|((\+86)))?\d{3,4}-\d{7,8}(-\d{3,4})?$/;
		var validateReg = /^((\+?86)|((\+86)))?1\d{10}$/;
		if(patrn.test(phone) || validateReg.test(phone)){
			
		}else{
			alert("请填写正确的电话或手机号码格式");
			$(".recTel").focus();
			return false;
		}
	}
	var isDefault = 0;
	if($("#setDefAddr").attr("class") == "ckdAddr"){
		isDefault = 0;
	}else{
		isDefault = 1;
	}
	var data = {
		"consignee":$(".recName").val(),
		"province":$("#province option:selected").text(),
		"province_id":$("#province option:selected").val(),
		"city":$("#wxCity option:selected").text(),
		"city_id":$("#wxCity option:selected").val(),
		"area":$("#selBar option:selected").text(),
		"area_id":$("#selBar option:selected").val(),
		"town":$("#wxArea option:selected").text(),
		"town_id":$("#wxArea option:selected").val(),
		"address":$(".recAddr").val(),
		"zipcode":$(".recCode").val(),
		"mobile":$(".recTel").val(),
		"isDefault":isDefault
	};
	$.get("/business/wshop.php/Order/saveNewAddr",data,function(info){
		 var getCurrParam=$('#getCurrParam').val();
		 if(info=="addSuccess"){
         	 if(getCurrParam != ''){
		 		window.location=getCurrParam;
		 	}else{
				window.location="/business/wshop.php/Order/addAddr";
         	}
		 }else if(info=='addFailed'){
		 	alert('添加失败,请检查选择的四级地区！');
		 	return false;
		 }
	});
});

$(".resetBtn").click(function(){
	$(".recName").val("");
	$("#selBar").val("");
	$(".recAddr").val("");
	$(".recCode").val("");
	$(".recTel").val("");
});


//用户手动选择默认地址
$("#setDefAddr").click(function(){
	if($(this).attr("class")=="ckdAddr"){
		$(this).attr("class","defAddr");
	}else{
		$(this).attr("class","ckdAddr");
	}
});