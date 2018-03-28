// JavaScript Document
function login(){
	var userName=$("#userName").val();
	var password=$("#userPassword").val();
	if(userName.length<1){
		$('#loginMsg').text('请输入您的用户名！');
		$("#loginUserName").focus();
		setTimeout(function(){$('#loginMsg').text('');},3000);
		return false;
	}
	if(userName.length<4){
		$('#loginMsg').text('您的登录名长度不合规则！');
		$("#loginUserName").focus();
		setTimeout(function(){$('#loginMsg').text('');},3000);
		return false;
	}
	if(password.length<1){
		$('#loginMsg').text('请输入您的登录密码！');
		$("#loginUserPassword").focus();
		setTimeout(function(){$('#loginMsg').text('');},3000);
		return false;
	}
	if(password.length<4){
		$('#loginMsg').text('您的登录密码长度不合规则！');
		$("#loginUserPassword").focus();
		setTimeout(function(){$('#loginMsg').text('');},3000);
		return false;
	}
	$("#loginSubmit").attr({"disabled":"disabled"});
	$('#loginMsg').text('正在登录，请稍后...');
	var postdata = {
		userName:userName,
		password:password
	};
	$.ajax({
		type: "POST",
		url:"./?mod=user&ac=login",
		dataType:"text",
		cache:false,
		data: postdata,
		success: function(msg){
			var msgArr=eval(msg);
			if(msgArr[0]==1){
				$('#loginMsg').text('登录成功!');
				window.location = "./user.html";
			}else{
				$('#loginMsg').text('登录失败：'+msgArr[1]+'!');
				return false;
			}
		},
		error: function (){
			$('#loginMsg').text("未知原因登录失败，请联系客服！");
			$("#loginSubmit").removeAttr("disabled");//将按钮可用
		}
	});
}