/*       navFixed       */
/*  author : Jasin Yip  */
/*  version: 1.0.2      */

$.fn.navFixed = function(){
	var $_this = $(this),
		_offsetTop = $_this.offset().top,
		_topPosition = $(document).scrollTop(),
		_navPosition = $_this.prev().outerHeight(true);
		_top = $_this.css("margin-top");
	_if();

	$(document).scroll( function() {
		_topPosition = $(document).scrollTop();
		_if();
	});

	$(window).resize(function(){ 
		_navPosition = $_this.prev().outerHeight(true);
		_if();
	}); 

	function _if(){	
		if (_topPosition >= _offsetTop){
			$_this.css("position", "fixed");
			$_this.css("top", 20+'px');
		}else{
			$_this.css("position", "relative");
			$_this.css("top", 0 + "px");
		}
	}
}