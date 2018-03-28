window.onload = function(){

	var hours = document.getElementById("hours");
	var minutes = document.getElementById("minutes");
	var seconds = document.getElementById("seconds");
	var EndTime = new Date("2016/09/8 11:00:00");

	function countDown(){
		var NowTime = new Date();
		var during = EndTime.getTime()-NowTime.getTime();
		if(during >=0){
			var h = Math.floor(during/1000/3600);
			var m = Math.floor(during/1000/60%60);
			var s = Math.floor(during/1000%60);
			if(h<10) h = "0" + h;
			if(m<10) m = "0" + m;
			if(s<10) s = "0" + s;
			hours.innerHTML = h;
			minutes.innerHTML = m;
			seconds.innerHTML = s;
		}else{
			//每天11点自动重置
			during = during + 24*60*60*1000;
			var h = Math.floor(during/1000/3600);
			var m = Math.floor(during/1000/60%60);
			var s = Math.floor(during/1000%60);
			if(h<10) h = "0" + h;
			if(m<10) m = "0" + m;
			if(s<10) s = "0" + s;
			hours.innerHTML = h;
			minutes.innerHTML = m;
			seconds.innerHTML = s;
		}
	}

	setInterval(countDown, 1000);
}