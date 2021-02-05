<?php
function load_redirect(){
	if(session_exists('redirect')){
		$url = gsession('redirect');
		unset($_SESSION['redirect']);
		rediriger_vers($url);
		exit();
	}
}
function save_redirect(){
	$url =  $_SERVER['REQUEST_URI'];
	$_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
}
?>