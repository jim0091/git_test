$(document).ready(function() {
	$("#singinBtn").attr("disabled", false);
	$("#signUpBtn").attr("disabled", false);
	$("#activateBtn").attr("disabled", false);
	$("#getPassBtn").attr("disabled", false);
	
	$("#userName").focus();
	$("#singinBtn").click(function(){
		var userName=$("#userName").val();
		var password=$("#password").val();
		if(userName==""){
			$('#singinMsg').text('*请输入您的用户名！');
			$('#singinMsg').show();
			$("#userName").focus();
			$("#userName").parent().addClass('wrong');
			$("#userName").after("<div class='close'></div>");
			return false;
		}
		if(password==""){
			$('#singinMsg').text('*请输入您的登录密码！');
			$('#singinMsg').show();
			$("#password").focus();
			$("#password").parent().addClass('wrong');
			return false;
		}
		
		if(checkSpecialStr(password)==true){
			$('#singinMsg').text('*登录密码不能含有特殊字符！');
			$('#singinMsg').show();
			$("#password").focus();
			$("#password").parent().addClass('wrong');
			return false;
		}
		
		$("#singinBtn").attr("disabled", true);
		$('#singinMsg').text('登录中，请稍后...');
		$('#singinMsg').show();
		
		urls="/shop.php/Sign/sign";
		$.ajax({
			type: "POST",
			url:urls,
			dataType:"text",
			cache:false,
			data: {
				userName:userName,
				password:password
			},
			success: function(data){
				var msg=eval(data);
				if(msg[0]>0){					
					var refer=msg[2];
					if(refer==''){
						var refer=$("#refer").val();
					}
					if(refer==''){
						refer="http://www.lishe.cn";	
					}
					window.location.href=refer;
				}else{
					$('#singinMsg').text('*'+msg[1]+'！');
					$('#singinMsg').show();
					$("#userName").focus();
					$("#singinBtn").attr("disabled", false);
				}
			},
			error: function () {
				$('#singinMsg').text('*登录失败，请检查浏览器设置！');
				$('#singinMsg').show();
				$("#userName").focus();
			}
		});
	});

	
	//提交注册
	$('#signUpBtn').click(function(){
		var mobile=$("#signupMobile").val();
		var password=$("#signupPassword").val();
		var rePassword=$("#signupPasswordRe").val();
		var reg=/^0?1[3|4|5|7|8][0-9]\d{8}$/;
		if (!reg.test(mobile)) {
			$('#signUpMsg').text('*手机号码格式不正确！');
			$('#signUpMsg').show();
			$("#signupMobile").focus();
			$("#signupMobile").parent().addClass('wrong');
			$("#signupMobile").after("<div class='close'></div>");
			return false;
		}
		var imgCheckCode=$("#imgCheckCode").val();
		if(imgCheckCode.length<4){
			$('#signUpMsg').text('*图片验证码不正确！');
			$('#signUpMsg').show();
			$("#imgCheckCode").focus();
			$("#imgCheckCode").parent().addClass('wrong');
			getImgCode();
			return false;
		}
		if(password.length<6){
			$('#signUpMsg').text('*登录密码不能少于6个字符！');
			$('#signUpMsg').show();
			$("#signupPassword").focus();
			$("#signupPassword").parent().addClass('wrong');
			return false;
		}
		if(password!=rePassword){			
			$('#signUpMsg').text('*登录密码和确认密码不相符！');
			$('#signUpMsg').show();
			$("#signupPasswordRe").focus();
			$("#signupPasswordRe").parent().addClass('wrong');
			return false;
		}
		if(checkSpecialStr(password)==true){
			$('#signUpMsg').text('*登录密码不能含有特殊字符！');
			$('#signUpMsg').show();
			$("#signupPassword").focus();
			$("#signupPassword").parent().addClass('wrong');
			return false;
		}		
		if($('#signupAgree').is(":checked")==false){
			$('#signUpMsg').text('*请勾选“我已阅读并同意 《礼舍网服务协议》”！');
			$('#signUpMsg').show();
			return false;
		}
		
		$("#signUpBtn").attr("disabled", true);
		$('#signUpMsg').text('注册中，请稍后...');
		$('#signUpMsg').show();
		
		urls="/shop.php/Sign/signup";
		$.ajax({
			type: "POST",
			url:urls,
			dataType:"text",
			cache:false,
			data: {
				mobile:mobile,
				password:password,
				imgCode:imgCheckCode
			},
			success : function (result,status){
				var data=eval(result);
				if(data[0]==100){
					$('#register').hide();
					$('#reg_success').show();
					var refer=$("#refer").val();
					if(refer==''){
						refer=msg[2];
					}
					if(refer==''){
						refer="http://www.lishe.cn";	
					}
					setTimeout("window.location.href='"+refer+"'",2000);
					
				}else{
					if(data[0]==2){
						getImgCode();
					}
					$('#signUpMsg').text('*'+data[1]+'！');
					$('#signUpMsg').show();
					$("#signUpBtn").attr("disabled", false);
				}
			},
			error: function () {
				$('#signUpMsg').text('*注册失败，请检查浏览器设置！');
				$('#signUpMsg').show();
				$("#signUpBtn").attr("disabled", false);
			}
		});
	});
	
	//仿刷新：检测是否存在cookie
    if($.cookie("captcha")){
        var count=$.cookie("captcha");
        var btn=$('#getCode');
        btn.val(count+'秒后重新获取').attr('disabled',true).css('cursor','not-allowed');
        var resend = setInterval(function(){
            count--;
            if (count > 0){
                btn.val(count+'秒后重新获取').attr('disabled',true).css('cursor','not-allowed');
                $.cookie("captcha",count,{path:'/',expires:(1/86400)*count});
            }else{
                clearInterval(resend);
                btn.val("获取验证码").removeClass('disabled').removeAttr('disabled style');
            }
        }, 1000);
    }
    
    //点击发送验证码
    $('#getMobileCode').click(function(){      	        	
		var mobile=$("#activateMobile").val();
		var reg=/^0?1[3|4|5|7|8][0-9]\d{8}$/;
		if (!reg.test(mobile)) {
			$('#activateMsg').text('*手机号码格式不正确！');
			$('#activateMsg').show();
			$("#activateMobile").focus();
			$("#activateMobile").parent().addClass('wrong');
			$("#activateMobile").after("<div class='close'></div>");
			return false;
		}
		$('#activateMsg').text('*正在请求发送验证码...');
		$('#activateMsg').show();
		$.get("/shop.php/Sign/checkActivate/mobile/"+mobile, function(result){
			var data=eval(result);
			if(data[0]<=0){
				$('#activateMsg').text('*'+data[1]+'！');
				$('#activateMsg').show();
				$("#activateMobile").focus();
				return false;
			}else{
				$('#activateMsg').text('*验证码发送成功！');
				$("#mobileCode").focus();
				var btn=$('#getMobileCode');
	            var count=60;
	            var resend=setInterval(function(){
	                count--;
	                if (count>0){
	                    btn.val(count+"秒后重新获取");
	                    $.cookie("captcha",count,{path:'/',expires:(1/86400)*count});
	                }else{
	                    clearInterval(resend);
	                    btn.val("获取验证码").removeAttr('disabled');
	                }
	            },1000);
	            btn.attr('disabled',true);
			}
	    });
    		    
    });
	
	//提交激活
	$('#activateBtn').click(function(){ 
		var mobile=$("#activateMobile").val();
		var password=$("#activatePassword").val();
		var rePassword=$("#activatePasswordRe").val();
		var mobileCode=$("#mobileCode").val();
		var reg=/^0?1[3|4|5|7|8][0-9]\d{8}$/;
		if (!reg.test(mobile)) {
			$('#activateMsg').text('*手机号码格式不正确！');
			$('#activateMsg').show();
			$("#activateMobile").focus();
			$("#activateMobile").parent().addClass('wrong');
			$("#activateMobile").after("<div class='close'></div>");
			return false;
		}
		if(mobileCode.length<6){
			$('#activateMsg').text('*手机验证码不正确！');
			$('#activateMsg').show();
			$("#mobileCode").focus();
			$("#mobileCode").parent().addClass('wrong');
			$("#mobileCode").after("<div class='close'></div>");
			return false;
		}
		if(password.length<6){
			$('#activateMsg').text('*登录密码不能少于6个字符！');
			$('#activateMsg').show();
			$("#activatePassword").focus();
			$("#activatePassword").parent().addClass('wrong');
			return false;
		}
		if(password!=rePassword){
			$('#activateMsg').text('*登录密码和确认密码不相符！');
			$('#activateMsg').show();
			$("#activatePasswordRe").focus();
			$("#activatePasswordRe").parent().addClass('wrong');
			return false;
		}
		if(checkSpecialStr(password)==true){
			$('#activateMsg').text('*登录密码不能含有特殊字符！');
			$('#activateMsg').show();
			$("#activatePassword").focus();
			$("#activatePassword").parent().addClass('wrong');
			return false;
		}
		if($('#activateAgree').is(":checked")==false){
			$('#activateMsg').text('*请勾选“我已阅读并同意 《礼舍网服务协议》”！');
			$('#activateMsg').show();
			return false;
		}
		//需要提交的数据
		var data = {mobile:mobile, password:password, checkCode:mobileCode};
		
		//从acTask控制器跳转过来的
		var isAcTask = false;
		var flag = $("#fromAcTask").val();
		if(typeof(flag) != "undefined" && flag == 'acTask'){
			data.from = 'acTask';
			isAcTask = true;
		}
		
		$("#activateBtn").attr("disabled", true);
		$('#activateMsg').text('激活中，请稍后...');
		$('#activateMsg').show();
		urls="/shop.php/Sign/activate";
		$.ajax({
			type: "POST",
			url:urls,
			dataType:"text",
			cache:false,
			data:data,
			success : function (result,status){
				var data=eval(result);
				if(data[0]==100){
					$('#activation').hide();
					$('#act_success').show();
					if(isAcTask){//acTask页面的
						$(".guidehint").hide();
						$(".gshadow").show();
						$(".finishq").show();
					}else{
						var refer = $("#refer").val();
						//if(refer==''){
						//	refer=msg[2];//无意义代码,msg未定义变量
						//}
						if(refer == ""){
							refer="http://www.lishe.cn";	
						}
						//alert("refer:"+refer);
						setTimeout("window.location.href='"+refer+"'",2000);
					}
				}else{
					$('#activateMsg').text('*'+data[1]+'！');
					$('#activateMsg').show();
					$("#activateBtn").attr("disabled", false);
				}
			},
			error: function (){
				$('#activateMsg').text('*激活失败，请检查浏览器设置！');
				$('#activateMsg').show();
				$("#activateBtn").attr("disabled", false);
			}
		});
	});


    //重置密码点击发送验证码
    $('#getPassCheckCode').click(function(){      	        	
		var mobile=$("#getPassMobile").val();
		var reg=/^0?1[3|4|5|7|8][0-9]\d{8}$/;
		if (!reg.test(mobile)) {
			$('#getPassMsg').text('*手机号码格式不正确！');
			$('#getPassMsg').show();
			$("#getPassMobile").focus();
			$("#getPassMobile").parent().addClass('wrong');
			$("#getPassMobile").after("<div class='close'></div>");
			return false;
		}
		$('#getPassMsg').text('*正在请求发送验证码...');
		$('#getPassMsg').show();
		$.get("/shop.php/Sign/getPassCheckCode/mobile/"+mobile, function(result){
			var data=eval(result);
			if(data[0]<=0){
				$('#getPassMsg').text('*'+data[1]+'！');
				$('#getPassMsg').show();
				$("#getPassMobile").focus();
				return false;
			}else{
				$('#getPassMsg').text('*验证码发送成功！');
				$("#getPassMobileCode").focus();
				var btn=$('#getPassCheckCode');
	            var count=60;
	            var resend=setInterval(function(){
	                count--;
	                if (count>0){
	                    btn.val(count+"秒后重新获取");
	                    $.cookie("captcha",count,{path:'/',expires:(1/86400)*count});
	                }else{
	                    clearInterval(resend);
	                    btn.val("获取验证码").removeAttr('disabled style');
	                }
	            },1000);
	            btn.attr('disabled',true);
			}
	    });    		    
    });
    
	//提交重置密码
	$('#getPassBtn').click(function(){ 
		var mobile=$("#getPassMobile").val();
		var password=$("#getPassPassword").val();
		var rePassword=$("#getPassPasswordRe").val();
		var mobileCode=$("#getPassMobileCode").val();
		var reg=/^0?1[3|4|5|7|8][0-9]\d{8}$/;
		if (!reg.test(mobile)) {
			$('#getPassMsg').text('*手机号码格式不正确！');
			$('#getPassMsg').show();
			$("#getPassMobile").focus();
			$("#getPassMobile").parent().addClass('wrong');
			$("#getPassMobile").after("<div class='close'></div>");
			return false;
		}
		if(password.length<6){
			$('#getPassMsg').text('*登录密码不能少于6个字符！');
			$('#getPassMsg').show();
			$("#getPassPasswordRe").focus();
			$("#getPassPasswordRe").parent().addClass('wrong');
			return false;
		}
		if(password!=rePassword){
			$('#getPassMsg').text('*登录密码和确认密码不相符！');
			$('#getPassMsg').show();
			$("#getPassPasswordRe").focus();
			$("#getPassPasswordRe").parent().addClass('wrong');
			return false;
		}
		if(checkSpecialStr(password)==true){
			$('#getPassMsg').text('*登录密码不能含有特殊字符！');
			$('#getPassMsg').show();
			$("#getPassPasswordRe").focus();
			$("#getPassPasswordRe").parent().addClass('wrong');
			return false;
		}
		$("#getPassBtn").attr("disabled", true);
		$('#getPassMsg').text('正在重置密码，请稍后...');
		$('#getPassMsg').show();
		urls="/shop.php/Sign/getPassWord";
		$.ajax({
			type: "POST",
			url:urls,
			dataType:"text",
			cache:false,
			data: {
				mobile:mobile,
				password:password,
				checkCode:mobileCode
			},
			success : function (result,status){
				var data=eval(result);
				if(data[0]==100){
					$('#find_password').hide();
					$('#reset_success').show();
					setTimeout("window.location.reload();",2000);
				}else{
					$('#getPassMsg').text('*'+data[1]+'！');
					$('#getPassMsg').show();
					$("#getPassBtn").attr("disabled", false);
				}
			},
			error: function (){
				$('#getPassMsg').text('*重置密码失败，请检查浏览器设置！');
				$('#getPassMsg').show();
				$("#getPassBtn").attr("disabled", false);
			}
		});
	});
		
	//注册登录激活切换
	$(".newuser").click(function(){
		$(this).parents(".signin>div").fadeOut(300, function(){
			$(".signin").removeClass('shorter').addClass('larger');
			$("#register").fadeIn(500);
			$("#signupMobile").focus();
		});
	});
	$(".quickactivate").click(function(){
		$(this).parents(".signin>div").fadeOut(200, function(){
			$(".signin").removeClass('shorter').addClass('larger');
			$("#activation").fadeIn(500);
			$("#activateMobile").focus();
		});
	});
	$(".loginnow").click(function(){
		$(this).parents(".signin>div").fadeOut(300, function(){
			$(".signin").removeClass('larger').addClass('shorter')
			$("#signin").fadeIn(500);
			$("#userName").focus();
		});
	});
	$(".forgetpassword").click(function(){
		$(this).parents(".signin>div").fadeOut(300, function(){
			$(".signin").removeClass('shorter').addClass('larger')
			$("#find_password").fadeIn(500);
			$("#getPassMobile").focus();
		});
	});

	$(document).on("click", ".close", function(){
		$(this).prev().val("");
	});
	
	

/*	//获取验证码
	$("#getcode").attr("disabled", true);
	var code = $("#getcode");
	$("#phonenumber").keyup(function() {
		var phonelength = $(this).val().length;
		if(phonelength != 11){
			code.attr("disabled", true);
		}else{
			code.attr("disabled", false);
		}
	});

	$("#getcode").click(function(e){

		e.stopPropagation();
		counttime($(this));
	});*/

});

function checkSpecialStr(checkStr){
    var specialStr = RegExp(/[(\ )(\~)(\!)(\@)(\#)(\$)(\%)(\^)(\&)(\*)(\()(\))(\-)(\_)(\+)(\=)(\[)(\])(\{)(\})(\|)(\\)(\;)(\:)(\')(\")(\,)(\.)(\/)(\<)(\>)(\?)(\)]+/);
    if(specialStr.test(checkStr)) return true; 
    return false; 
}

function getImgCode(){
	var captcha_img = $('#captcha-container').find('img')  
	var verifyimg = captcha_img.attr("src"); 
	captcha_img.attr('title', '点击刷新验证码');  
	if(verifyimg.indexOf('/?')==0){
        captcha_img.attr("src", verifyimg+'/?random='+Math.random());  
    }else{  
        captcha_img.attr("src", verifyimg.replace(/\?.*$/,'')+'?random'+Math.random());  
    }
}
/*//验证码倒计时60秒
var countdown = 60;
function counttime(obj){	
	if(countdown==0){
		obj.attr("disabled", false);
		obj.val("获取验证码");
		countdown = 60;
		return;
	}else{
		obj.attr("disabled", true);
		obj.val(countdown + "s");
		countdown--;
	}
	setTimeout(function(){
		counttime(obj)
	},1000);
}
*/