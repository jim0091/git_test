var gshadow = $(".gshadow")
var ghint = $(".guidehint");


//step25
function gstep25(){
	ghint.hide(); 
	gshadow.show(); 
    var left = ($(window).width() - $(".gstep25").width())/2 + 400; 
    $(".gstep25").css({"top": "455px", "left":left}).show(); 
    $(".active:first-child").css({"position":"relative", "z-index":"1002", "padding":"5px", "background-color":"#FFF"})
}
//step26
function gstep26(){
	ghint.hide(); 
	gshadow.show(); 
    var left = ($(window).width() - $(".gstep26").width())/2 + 400; 
    $(".gstep26").css({"top": "70px", "left":left}).show(); 
    $("#activateMobile").css({"position":"relative", "z-index":"1002"});
}
//step27
function gstep27(){
	$("#activateMobile").removeAttr('style');
	ghint.hide(); 
	gshadow.show(); 
    var left = ($(window).width() - $(".gstep27").width())/2 + 400; 
    $(".gstep27").css({"top": "150px", "left":left}).show(); 
    $("#mobileCode").css({"position":"relative", "z-index":"1002"});
    $("#getMobileCode").css({"position":"relative", "z-index":"1002"});
}
//step28
function gstep28(){
	$("#mobileCode").removeAttr('style');
	$("#getMobileCode").removeAttr('style');
	ghint.hide(); 
	gshadow.show(); 
    var left = ($(window).width() - $(".gstep28").width())/2 + 400; 
    $(".gstep28").css({"top": "190px", "left":left}).show(); 
    $("#activatePassword").css({"position":"relative", "z-index":"1002"});
}
//step29
function gstep29(){
	$("#activatePassword").removeAttr('style');
	ghint.hide(); 
	gshadow.show(); 
    var left = ($(window).width() - $(".gstep29").width())/2 + 400; 
    $(".gstep29").css({"top": "280px", "left":left}).show(); 
    $("#activatePasswordRe").css({"position":"relative", "z-index":"1002"});
}
//step30
function gstep30(){
	$("#activatePasswordRe").removeAttr('style');
	ghint.hide(); 
	gshadow.show(); 
    var left = ($(window).width() - $(".gstep30").width())/2 + 400; 
    $(".gstep30").css({"top": "360px", "left":left}).show(); 
    $("#activateBtn").css({"position":"relative", "z-index":"1002"});
}

/*关闭按钮*/
$(".closebtn").click(function(){
	$(this).parent().hide();
	gshadow.hide();
	var url = $(this).attr('url');
	setTimeout("window.location.href='"+url+"'",400);
});