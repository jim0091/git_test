var Web =  {
	
	Recource: {
		serverSysURL: 'http://120.76.159.44:8080/lshe.framework.protocol.http/api/sys/',
		serverURL: 'http://120.76.159.44:8080/lshe.framework.protocol.http/api/',
	},
	Method: {
		
		/* Common - Ajax request */
		ajax:function(method, param) {
			var defaultParam = {
				param:{},
				safe:false,
				data:{
					format:"json"
				},
				url:Web.Recource.serverURL,
				success:function(){},
				fail:function(){},
				error:function(){}
			};
			$.extend(true, defaultParam, param);
			
			var cipher;
			if(defaultParam.safe){
				cipher = Crypto.generateCipher();
				defaultParam.data = {
					encrypt_data:cipher.encrypt($.param(param.data)),
					encrypt_source:"javascript",
					encrypt_flag:Crypto.encryptFlag,
					format:"json"
				};
			}
			
			$.ajax({
				type: "post",
				data:defaultParam.data,
				async:defaultParam.async,
				url: defaultParam.url + method,
				dataType: "jsonp",
				jsonp: "callback",
				success: function(data){
					console.log(data);
					//Web.Method.loadCheck(data);
					if(param.safe){
						data = eval("("+decodeURIComponent(cipher.decrypt(data).replace(/\+/g, '%20'))+")");
					}
					console.log(data);
					if(data.result == 0 || data.result == 1){
						defaultParam.success(data.data?data.data:data, defaultParam.param);
					}else if(data.result == 5 && data.errcode==511){
						$.confAlert({
							size:"sm",
							context:"您没有此操作的权限",
							noButton:false
						})
					}else if(data.result ==3 && (data.errcode == 501||data.errcode == 502)&& method.indexOf("getUserInfo") == -1){
						$.confAlert({
							size:"sm",
							context:data.msg,
							noButton:false
						})
					}else if(data.result==5){
						$.confAlert({
							size:"sm",
							context:data.msg,
							noButton:false
						})					
					}else if(data.result == -1){
						$.confAlert({
							size:"sm",
							context:"服务器异常,请联系管理员或稍后再试",
							noButton:false
						})
					}else{
						defaultParam.fail(data, defaultParam.param);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					if(defaultParam.error)
						defaultParam.error(XMLHttpRequest, textStatus, errorThrown, defaultParam.param);
				}
			});
		}
	}
}

var init_public_key = function() {
	
	Web.Method.ajax("getPublicKey", {
		url:Web.Recource.serverSysURL,
		success:function(data){
			Crypto.setRSAPublicKey(data.info.n);
			Crypto.encryptFlag = data.info.id;
		},
		fail:function(data){}
	});
}