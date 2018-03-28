window.onload = function(){
	var auto = false;
	var autoLogin = document.getElementsByTagName("label")[0];
	var radioActive = document.getElementById("auto-active");
	autoLogin.onclick = function(){
		if(auto==false){
			radioActive.style.display = "block";
			auto = true;
		}else{
			radioActive.style.display = "none";
			auto = false;
		}
	}
}