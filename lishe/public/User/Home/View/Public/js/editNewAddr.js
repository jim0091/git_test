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
		var patrn = /^1(3|4|5|7|8)\d{9}$/;
		if(!(patrn.test(phone))){
			alert("请填写正确的电话或手机号码格式!");
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
	var refer = $("#refer").val();
	var APP = $("#app").val();
	$.post(APP+"/Address/editUserAddrInfo", param, function(res){
		var data = eval(res);
		if(data[0] == 1 ){
			if (refer != '') {
				window.location.href=refer;
			}else{
				alert(data[1]);
			}         	
		}else{
			alert(data[1]);
		}
	});
});
