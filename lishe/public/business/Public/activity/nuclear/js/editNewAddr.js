$(".saveBtn").click(function(){
	if($(".recName").val() == null || $(".recName").val() == ''){
		alert("请填写收件人姓名");
		$(".recName").focus();
		return false;
	}
	if($("#province").val() == null || $("#province").val() == '' || $("#province").val() == '0'){
		alert("请选择你所在的省");
		$("#province").focus();
		return false;
	}
	if($("#wxCity").val() == null || $("#wxCity").val() == '' || $("#wxCity").val() == '0'){
		alert("请选择你所在的市");
		$("#wxCity").focus();
		return false;
	}
	if($("#selBar").val() == null || $("#selBar").val() == '' || $("#selBar").val() == '0'){
		alert("请选择你所在的区");
		$("#selBar").focus();
		return false;
	}
	if($("#wxArea").is(":hidden")==false){
	if($("#wxArea").val() == null || $("#wxArea").val() == '' || $("#wxArea").val() == '0'){
		alert("请选择你所在的街道");
		$("#wxArea").focus();
		return false;
	}
    }else{
    	 $("#wxArea option:selected").text('');
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
			alert("请填写正确的邮编格式");
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
	if($("#setDefAddr").prop("checked")==true){
		isDefault = 1;
	}else{
		isDefault = 0;
	}
	var param = {
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
		"address_id":$("#aid").val(),
		"isDefault":isDefault
	};
	$.post("/business/index.php/Activity/editUserAddrInfo", param, function(data){
		// alert(data);
		 if(data=="addrModSuccess"){
         	// window.history.back();
         	window.history.go(-1);
         	// location.href = document.referrer;
		 }else if(data=="modFailed"){
		 	alert('请检查，选择的地区是否正确！');
		 	return false;
		 }
	});
});
