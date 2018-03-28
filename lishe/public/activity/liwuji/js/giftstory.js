window.onload = function(){
	waterfall("index_main", "index_box");
}

function waterfall(parent, box){
	var oParent = document.getElementById(parent);
	var boxArr = new Array();
	var oBoxs = document.querySelectorAll("." + box);
	for(var i=0; i<oBoxs.length; i++){
		boxArr.push(oBoxs[i]);
	}
	var hArr = [];
	for(var i=0; i<oBoxs.length; i++){
		if(i<2){
			hArr.push(oBoxs[i].offsetHeight);
		}else{
			var minH = Math.min.apply(null, hArr);
			var index = getMinhIndex(hArr, minH);
			oBoxs[i].style.position = "absolute";
			oBoxs[i].style.top = minH + "px";
			oBoxs[i].style.left = oBoxs[index].offsetLeft + "px";
			hArr[index] += oBoxs[i].offsetHeight;
		}
	}
	var maxH = Math.max.apply(null, hArr) + 20;
	var wrapper = document.querySelectorAll(".outer_wrap")[0];
	wrapper.style.height = maxH + "px";
}

function getMinhIndex(arr, val){
	for(var i in arr){
		if(arr[i]==val){
			return i;
		}
	}
}

var likeArr = new Array;
var likes = document.querySelectorAll(".like");
for(var i=0; i<likes.length; i++){
	likes[i].index = i;
	likes[i].liked = false;
	likeArr.push(likes[i].liked);
	likes[i].addEventListener("touchstart", function(){
		if(likeArr[this.index] == false){
			this.querySelectorAll(".heart")[0].style.backgroundImage = "url(./images/heart_red.png)";
			var new_liked_num = parseInt(this.querySelectorAll(".like_num")[0].innerHTML);
			this.querySelectorAll(".like_num")[0].innerHTML = new_liked_num + 1;
			likeArr[this.index] = true;
		}
		else{
			this.querySelectorAll(".heart")[0].style.backgroundImage = "url(./images/heart_gray.png)";
			var new_liked_num = parseInt(this.querySelectorAll(".like_num")[0].innerHTML);
			this.querySelectorAll(".like_num")[0].innerHTML = new_liked_num - 1;
			likeArr[this.index] = false;
		}
	}, false);
}

var follow = document.getElementById("follow");
var followed = false;
if(follow){
	follow.addEventListener("touchstart", function(){
		if(followed==false){
			follow.style.backgroundImage = "url(./images/followed.png)";
			followed = true;
		}else{
			follow.style.backgroundImage = "url(./images/follow.png)";
			followed = false;
		}
	}, false);
}

var swipewrapper = document.getElementById("swipe-wrapper");
var imgHeight = $(".swipe-slide.active").height();
if(swipewrapper){
	swipewrapper.style.height = imgHeight + "px";
}