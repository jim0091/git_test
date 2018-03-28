<?php
	function getAllUrl(){
		return 'http://'.$_SERVER ['HTTP_HOST'].$_SERVER["REQUEST_URI"];//得到当前的网址
	}