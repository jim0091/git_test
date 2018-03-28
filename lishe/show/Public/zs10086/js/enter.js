 $(function () {
    document.onkeydown = function (event) {
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if (e && e.keyCode == 13) {
            $("#loginBtn").click();  
        	
        }
    };
});