$(document).ready(function () {
	bindFunc();
	// testFunc();
	
	// init();
});

/*
var Web = function() {
	
	var DOMAIN = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/',
		Recource = {
			serverSysURL: DOMAIN + 'sys/getPublicKey',
			serverLoginURL: DOMAIN + 'mall/login',
			serverURL: DOMAIN + 'mall/login',
		};
}
*/

var Web =  {
	
	// DOMAIN: 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/',
	Recource: {
		serverSysURL: 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/sys/getPublicKey',
		// serverLoginURL: 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/login',
		serverLoginURL: 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/empLogin',
		serverURL: 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/login',
	},
	// Method = {
		
		/* Common - Ajax request */
		ajax: function(method, param) {
			var defaultParam = {
				param:{},
				safe:true,
				data:{
					format:"json"
				},
				url:Web.Recource.serverLoginURL,
				success:function(){},
				fail:function(){},
				error:function(){}
			};
			$.extend(true, defaultParam, param);
			
			var cipher;
			if(defaultParam.safe){
				
				param.data = {
					'login_name': 'admin',
					'login_pwd': '123456'
				};
				
				cipher = Crypto.generateCipher();
				console.log($.param(param.data));
				//console.log(cipher.encrypt($.param(param.data)));
				defaultParam.data = {
					encrypt_data:cipher.encrypt($.param(param.data)),
					encrypt_source:"javascript",
					encrypt_flag:Crypto.encryptFlag,
					format:"json",
					'login_name': 'admin',
					'login_pwd': '123456'
				};
			}
			
			console.log('defaultParam -- : ');
			console.log(defaultParam);
			console.log(defaultParam.data);
			
			$.ajax({
				type: "Post",
				dataType: "jsonp",
				url: Web.Recource.serverLoginURL,
				data: defaultParam.data,
				beforeSend: function(){
					console.log("正在加载中……");
				},
				success: function(_data){
					
					console.log(_data);
					if(_data != null && _data.trim().length > 0) {
						// getData(data);
					}	
					else {
						console.log('响应失败！', '确定');
					}
				},
				complete: function(){
					console.log("加载完成!");
				},
				headers: {
					"Access-Control-Allow-Origin": "*",
					"Access-Control-Allow-Headers":"X-Requested-With"
				}
			});
		}
	// }
}

var init = function() {
	
	console.log('web recource serverLoginURL -- : ' +　Web.Recource.serverSysURL);// 获取用户名/密码、
	
	var _tmp_data = {
		'login_name': 'admin',
		'login_pwd': '123456'
	};
	
	//加载RSA公钥
	Web.ajax("getPublicKey", {
		url:Web.Recource.serverSysURL,
		success:function(data){
			
			console.log('success data -- : ' + data);
			
			Crypto.setRSAPublicKey(data.info.n);
			Crypto.encryptFlag = data.info.id;
		},
		fail:function(data){
			console.log('fail data -- : ' + data);
		}
	});
}

var getLoginData = function(e) {
		// console.log(e);
	
	/*
	var addH = function(_this, o) {
		
		_this.append(o);
	};
	*/
	
	// var jsonData = eval('(' + e + ')');
	var jsonData = e;
	
	console.log("json -- :");
	console.log("==========================================");
	console.log(jsonData);
	console.log("==========================================");
	console.log('result -- : ' + jsonData['result']);
	
	var result = jsonData['result'];
	
	switch(result) {
		
	case 0:{
		
		var data = jsonData['data'];
		
		if (data == undefined || data.length <= 0 || data == null) {
			
			alert('data nil');
			
			break;
		}

		var _info = data['info'];

		Crypto.setRSAPublicKey(_info['n']);
		Crypto.encryptFlag = _info['id'];
		
		// init();

		// var ajaxaUrl = '/api/mall/login';
		var ajaxaUrl = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/empLogin';
		// var ajaxaUrl = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/sys/getPublicKey';
		console.log(ajaxaUrl);	

		// 获取用户名/密码 test
		var login_name = $('input[name=\'login_name\']').val();
		var login_pwd = $('input[name=\'login_pwd\']').val();
		
		var cipher = Crypto.generateCipher();

		var _tmp_data = {
			'login_name': login_name,
			'login_pwd': login_pwd,
			'chiper_flag': 'javascript'
		};
		
		// ajaxaUrl += '?encrypt_data=' + cipher.encrypt($.param(_tmp_data)) + '&encrypt_source=javascript&encrypt_flag=' + Crypto.encryptFlag + '&format=json&login_name=' + login_name + '&login_pwd=' + login_pwd;
		ajaxaUrl += '?encrypt_data=' + encodeURI(cipher.encrypt($.param(_tmp_data))) + '&encrypt_source=javascript&encrypt_flag=' + Crypto.encryptFlag + '&format=json';
		console.log('url -- : ' + ajaxaUrl);
		
		var data = {
			'encrypt_data': cipher.encrypt($.param(_tmp_data)),
			'eencrypt_source': "javascript",
			'eencrypt_flag': Crypto.encryptFlag,
			'format': "json",
			'login_name': login_name,
			'login_pwd': login_pwd,
			'chiper_flag': 'javascript'
		}
		
		$.ajax({
			type: "POST",
			url: ajaxaUrl,
			dataType: "jsonp",
			data: _tmp_data,
			beforeSend: function(){
				console.log("正在加载中……");
			},
			success: function(data){
				
				console.log(data);
				if(null != data/* && data.trim().length > 0*/) {
					// getDataWithPublicKey(data);
				}	
				else {
					console.log('响应失败！', '确定');
				}
			},
			complete: function(){
				console.log("加载完成!");
			}
		});	

		
		break;
	}

	default : {
		alert('fail');
	}
	}
};

var getDataWithPublicKey = function(e) {
	
	// console.log(e);
	
	/*
	var addH = function(_this, o) {
		
		_this.append(o);
	};
	*/
	
	var jsonData = eval('(' + e + ')');
	
	console.log("json -- :");
	console.log("==========================================");
	console.log(jsonData);
	console.log("==========================================");
	console.log(jsonData['result']);
	
	var result = jsonData['result'];
	
	switch(result) {
		
	case 0:{
		
		var data = jsonData['data'];
		
		if (data == undefined || data.length <= 0 || data == null) {
			
			alert('data nil');
			
			break;
		}

		var _info = data['info'];

		Crypto.setRSAPublicKey(_info['n']);
		Crypto.encryptFlag = _info['id'];
		
		// init();

		// var ajaxaUrl = '/api/mall/login';
		// var ajaxaUrl = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/mall/empLogin';
		var ajaxaUrl = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/sys/getPublicKey';
		console.log(ajaxaUrl);	

		// 获取用户名/密码 test
		var login_name = $('input[name=\'login_name\']').val();
		var login_pwd = $('input[name=\'login_pwd\']').val();
		
		var cipher = Crypto.generateCipher();

		var _tmp_data = {
			'login_name': login_name,
			'login_pwd': login_pwd,
			'chiper_flag': 'javascript'
		};
		
		// ajaxaUrl += '?encrypt_data=' + cipher.encrypt($.param(_tmp_data)) + '&encrypt_source=javascript&encrypt_flag=' + Crypto.encryptFlag + '&format=json&login_name=' +login_name + '&login_pwd=' + login_pwd;
		ajaxaUrl += '?encrypt_data=' + cipher.encrypt($.param(_tmp_data)) + '&encrypt_source=javascript&encrypt_flag=' + Crypto.encryptFlag + '&format=json';
		console.log('url -- : ' + ajaxaUrl);
		
		var data = {
			'encrypt_data': cipher.encrypt($.param(_tmp_data)),
			'eencrypt_source': "javascript",
			'eencrypt_flag': Crypto.encryptFlag,
			'format': "json",
			'login_name': login_name,
			'login_pwd': login_pwd,
			'chiper_flag': 'javascript'
		}
		
		$.ajax({
			type: "POST",
			url: ajaxaUrl,
			dataType: "jsonp",
			data: _tmp_data,
			beforeSend: function(){
				console.log("正在加载中……");
			},
			success: function(data){
				
				// console.log(data);
				if(null != data/* && data.trim().length > 0*/) {
					getLoginData(data);
				}	
				else {
					console.log('响应失败！', '确定');
				}
			},
			complete: function(){
				console.log("加载完成!");
			}
		});	

		
		break;
	}

	default : {
		alert('fail');
	}
	}
};

var ajaxFunc = function() {
	
	// 异步加载获取用户手机号
	// var uid = jQuery.url.param("uid") == null ? "" : jQuery.url.param("uid"); // jQuery.url.js 获取请求参数
	// var loginkey = jQuery.url.param("loginkey") == null ? "" : jQuery.url.param("loginkey"); // jQuery.url.js 获取请求参数
	
	var ajaxaUrl = '/api/sys/getPublicKey';
	// var ajaxaUrl = 'http://120.76.43.74:8080/lshe.framework.protocol.http/api/sys/getPublicKey';
	console.log(ajaxaUrl);	
	
	$.ajax({
		type: "GET",
		url: ajaxaUrl,
		// dataType: "jsonp",
		beforeSend: function(){
			console.log("正在加载中……");
		},
		success: function(data){
			
			console.log(data);
			if(data != null/* && data.trim().length > 0*/) {
				getDataWithPublicKey(data);
			}	
			else {
				console.log('响应失败！', '确定');
			}
		},
		complete: function(){
			console.log("加载完成!");
		}
	});
}

var bindFunc = function() {

	$('input[name=\'login\']').bind({

		'click': function() {
			ajaxFunc();
		}
	});
}

var jsonp = function(url, data, callback) {
  var xhr = $.getJSON(url + '?jsoncallback=?', data, callback);
 
  // request failed
  xhr.fail(function(jqXHR, textStatus, ex) {
    /*
     * in ie 8, if service is down (or network occurs an error), the arguments will be:
     * 
     * testStatus: 'parsererror'
     * ex.description: 'xxxx was not called' (xxxx is the name of jsoncallback function)
     * ex.message: (same as ex.description)
     * ex.name: 'Error'
     */
    alert('failed');
  });
 
  // ie 8+, chrome and some other browsers
  var head = document.head || $('head')[0] || document.documentElement; // code from jquery
  var script = $(head).find('script')[0];
  script.onerror = function(evt) {
    alert('error');
 
    // do some clean
 
    // delete script node
    if (script.parentNode) {
      script.parentNode.removeChild(script);
    }
    // delete jsonCallback global function
    var src = script.src || '';
    var idx = src.indexOf('jsoncallback=');
    if (idx != -1) {
      var idx2 = src.indexOf('&');
      if (idx2 == -1) {
        idx2 = src.length;
      }
      var jsonCallback = src.substring(idx + 13, idx2);
      delete window[jsonCallback];
    }
  };
}

/*
var testFunc = function() {

	// var _info = data['info'];

	// Crypto.setRSAPublicKey(_info['n']);
	// Crypto.encryptFlag = _info['id'];
	Crypto.setRSAPublicKey('100331302017397359046338257705749070431691826092950136952460694209414681022281631147425937027028894146796517600193848443075498709065423364916066396860239508353539681585974376519938663484475903531157571679068148073441590737727741086260728853613970232518138790616585742792717060980457849274112774087581618837777');
	Crypto.encryptFlag = 5;

	var ajaxaUrl = '/api/mall/login';
	console.log(ajaxaUrl);

	// 获取用户名/密码、
	// var login_name = $('input[name=\'login_name\']').val();
	// var login_pwd = $('input[name=\'login_pwd\']').val();
	var login_name = 'admin';
	var login_pwd = '123456';
	
	var cipher = Crypto.generateCipher();

	var _tmp_data = {
		'login_name': login_name,
		'login_pwd': login_pwd
	};

	console.log('cipher.encrypt -- :');
	console.log(cipher.encrypt($.param(_tmp_data)));
	console.log('cipher.encryptFlag -- :');
	console.log(Crypto.encryptFlag);

	$.ajax({
		type: "Post",
		url: ajaxaUrl,
		data: {
			'encrypt_data': cipher.encrypt($.param(_tmp_data)),
			'eencrypt_source': "javascript",
			'eencrypt_flag': Crypto.encryptFlag,
			'eformat': "json",
			'login_name': login_name,
			'login_pwd': login_pwd
		},
		beforeSend: function(){
			console.log("正在加载中……");
		},
		success: function(data){
			
			console.log(data);
			if(data != null && data.trim().length > 0) {
				// getDataWithPublicKey(data);
			}	
			else {
				console.log('响应失败！', '确定');
			}
		},
		complete: function(){
			console.log("加载完成!");
		}
	});
}
*/